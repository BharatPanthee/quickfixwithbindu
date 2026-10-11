<?php
namespace QuickFix;
defined('ABSPATH') || exit;

final class Jobs {
    public static function enqueue(string $kind, string $dedupe, array $payload, ?string $at = null): void {
        global $wpdb; $table = Store::table('jobs'); $now = Store::now();
        Store::query($wpdb->prepare("INSERT INTO $table (kind,dedupe_key,payload,available_at,created_at,updated_at) VALUES (%s,%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE dedupe_key=dedupe_key", $kind, $dedupe, wp_json_encode($payload), $at ?: $now, $now, $now));
    }
    public static function deletion(array $user, array $data, string $key): array {
        global $wpdb; Input::only($data, ['confirm','apple_authorization_code']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Explicitly confirm account deletion.'); }
        $claims = $user['_claims']; if (time() - $claims['auth_time'] > 300) { throw new Fault('reauthentication_required', 'Sign in again before deleting your account.', 401); }
        Firebase::credentials(); $hash = Firebase::identity_hash($user['firebase_uid']);
        return Store::idem('user:' . $user['id'], 'deletion', $key, $data, static function () use ($user, $hash, $wpdb, $data) {
            $current = Store::row('users', (string) $user['id'], true);
            if ($current['status'] === 'deleted') { return ['status' => 'deleted']; }
            $providers = json_decode($current['providers'], true) ?: [];
            if (in_array('apple.com', $providers, true)) {
                if (!empty($data['apple_authorization_code'])) { Apple::associate($user, $data['apple_authorization_code']); }
                if (!$wpdb->get_var($wpdb->prepare('SELECT user_id FROM ' . Store::table('provider_tokens') . " WHERE user_id=%s AND provider='apple.com'", $user['id'])) && !has_filter('quickfix_revoke_provider_tokens')) { throw new Fault('apple_authorization_required', 'Reauthenticate with Apple and submit the authorization code before requesting deletion.', 422); }
            }
            $table = Store::table('deletion_tombstones');
            // Persist the tombstone at acceptance, before any destructive step or external call.
            Store::query($wpdb->prepare("INSERT INTO $table (identity_hash,deleted_at,replay_state) VALUES (%s,%s,'pending') ON DUPLICATE KEY UPDATE identity_hash=identity_hash", $hash, Store::now()));
            Store::update('users', ['status' => 'deletion_pending', 'deletion_requested_at' => Store::now(), 'revision' => (int) $current['revision'] + 1], ['id' => $user['id']]);
            self::enqueue('deletion', 'deletion:' . $hash, ['user_id' => (string) $user['id'], 'identity_hash' => $hash, 'apple_revocation' => in_array('apple.com', $providers, true)]);
            Store::audit('deletion.accepted', 'user:' . $user['id'], ['status' => 'deletion_pending'], (int) $user['id']); return ['status' => 'deletion_pending', 'target_days' => 7];
        }, true);
    }
    public static function scrub(string $user_id): void {
        global $wpdb;
        $comments = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . Store::table('comments') . ' WHERE user_id=%s', $user_id));
        foreach ($comments as $id) { Community::remove_comment((string) $id); }
        $recipes = $wpdb->get_col($wpdb->prepare('SELECT DISTINCT recipe_id FROM ' . Store::table('ratings') . ' WHERE user_id=%s', $user_id));
        foreach ($recipes as $id) { Community::lock_stats((string) $id); }
        Store::query($wpdb->prepare('DELETE FROM ' . Store::table('ratings') . ' WHERE user_id=%s', $user_id));
        foreach ($recipes as $id) { Community::reconcile((string) $id); }
        Store::query($wpdb->prepare('DELETE FROM ' . Store::table('reports') . " WHERE reporter_id=%s OR (target_type='author' AND target_id=%s)", $user_id, $user_id));
        Store::query($wpdb->prepare('DELETE FROM ' . Store::table('user_blocks') . ' WHERE blocker_user_id=%s OR blocked_user_id=%s', $user_id, $user_id));
        Store::query($wpdb->prepare('UPDATE ' . Store::table('devices') . ' SET user_id=NULL WHERE user_id=%s', $user_id));
        Store::update('users', ['display_name' => '', 'email' => null, 'providers' => '[]', 'verified' => 0], ['id' => $user_id]); Store::bump();
    }
    public static function finish_deletion(array $payload): void {
        $user_id = $payload['user_id']; $user = Store::row('users', $user_id);
        if ($user['status'] === 'deleted') { return; }
        // Local scrub commits first; the minimal UID stays only until external deletion succeeds.
        Store::transaction(static function () use ($user_id) { $locked = Store::row('users', $user_id, true); if ($locked['status'] !== 'deleted') { Store::update('users', ['status' => 'deletion_pending'], ['id' => $user_id]); self::scrub($user_id); } });
        if (!empty($payload['apple_revocation'])) {
            // Apple refresh tokens are not present in Firebase ID tokens. A provider adapter must supply revocation proof.
            Apple::revoke($user_id, $user['firebase_uid']);
        }
        Firebase::delete_identity($user['firebase_uid']);
        Store::transaction(static function () use ($user_id, $payload) {
            Store::update('users', ['firebase_uid' => 'deleted:' . wp_generate_uuid4(), 'status' => 'deleted', 'display_name' => '', 'email' => null, 'providers' => '[]', 'verified' => 0], ['id' => $user_id]);
            Store::update('deletion_tombstones', ['replay_state' => 'completed'], ['identity_hash' => $payload['identity_hash']]);
            global $wpdb; Store::query($wpdb->prepare('DELETE FROM ' . Store::table('provider_tokens') . ' WHERE user_id=%s', $user_id));
            Store::audit('deletion.completed', 'user:' . $user_id, ['status' => 'deleted']);
        });
    }
    public static function devices(\WP_REST_Request $request, array $data, ?array $user): array {
        global $wpdb;
        Input::only($data, ['installation_id','fcm_token','platform','app_version','consent','app_id','associate_user']);
        $installation = Input::text($data['installation_id'] ?? '', 16, 128); if (!preg_match('/^[A-Za-z0-9_-]+$/D', $installation)) { throw new Fault('invalid_installation', 'Invalid installation identifier.'); }
        $credential = (string) $request->get_header('x-quickfix-device'); $key = (string) $request->get_header('idempotency-key');
        if (!preg_match('/^[A-Za-z0-9._:-]{8,128}$/D', $key)) { throw new Fault('idempotency_required', 'Supply an Idempotency-Key header.', 400); }
        Store::rate('device-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 10, 60);
        $table = Store::table('devices'); $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE installation_id=%s", $installation), ARRAY_A);
        if ($existing) {
            if (!$credential) {
                Firebase::attest((string) $request->get_header('x-firebase-appcheck'), Input::text($data['app_id'] ?? '', 5, 128));
                $secret = Store::secret('QUICKFIX_DEVICE_KEY'); if (strlen($secret) < 32) { throw new Fault('devices_unconfigured', 'Device registration unavailable.', 503); }
                $credential = hash_hmac('sha256', $installation . ':' . $key, $secret);
            }
            if (!$credential || !hash_equals($existing['credential_hash'], hash('sha256', $credential))) { throw new Fault('device_ownership_required', 'Device registration credential required.', 401); }
        } else {
            Firebase::attest((string) $request->get_header('x-firebase-appcheck'), Input::text($data['app_id'] ?? '', 5, 128));
        }
        if ($request->get_method() === 'DELETE') {
            if (!$existing) { throw new Fault('not_found', 'Device registration unavailable.', 404); }
            Store::update('devices', ['consent' => 0, 'user_id' => null, 'token_cipher' => ''], ['id' => $existing['id']]); return ['opted_out' => true];
        }
        $token = Input::text($data['fcm_token'] ?? '', 20, 4096); $platform = Input::choice($data['platform'] ?? '', ['android','ios','web']);
        $version = Input::text($data['app_version'] ?? '', 1, 30); $consent = Input::boolean($data['consent'] ?? false);
        $associate = Input::boolean($data['associate_user'] ?? false); if ($associate && !$user) { throw new Fault('session_required', 'Sign in before linking a device.', 401); } if ($user) { Identity::active($user, false); }
        // Derive a reproducible one-time initial credential from a separate secret and the registration key.
        // Only its hash is stored, so a lost first response can safely be replayed after renewed attestation.
        if (!$credential) { $secret = Store::secret('QUICKFIX_DEVICE_KEY'); if (strlen($secret) < 32) { throw new Fault('devices_unconfigured', 'Device registration unavailable.', 503); } $credential = hash_hmac('sha256', $installation . ':' . $key, $secret); }
        $cipher = Firebase::encrypt($token); $token_hash = hash('sha256', $token);
        $result = Store::idem('device:' . hash('sha256', $installation), 'registration', $key, $data, static function () use ($table, $installation, $credential, $token_hash, $cipher, $platform, $version, $consent, $associate, $user, $existing, $wpdb) {
            // Do not steal another installation's token without an attestation check.
            $duplicate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE token_hash=%s FOR UPDATE", $token_hash), ARRAY_A);
            if ($duplicate && $duplicate['installation_id'] !== $installation) { throw new Fault('token_registered', 'This token is already registered. Rotate it in the client and try again.', 409); }
            $update = ['token_cipher' => $cipher, 'token_hash' => $token_hash, 'platform' => $platform, 'app_version' => $version, 'consent' => $consent, 'user_id' => $associate ? $user['id'] : null, 'last_seen' => Store::now()];
            if ($existing) { Store::update('devices', $update, ['id' => $existing['id']]); $id = (string) $existing['id']; }
            else { $id = (string) Store::insert('devices', array_merge($update, ['installation_id' => $installation, 'credential_hash' => hash('sha256', $credential)])); }
            return ['id' => $id, 'registered' => true, 'consent' => (bool) $consent];
        });
        $result['registration_credential'] = $credential; return $result;
    }
    public static function campaign(?string $id, array $data): array {
        Input::only($data, ['revision','title','body','target','audience','scheduled_at']);
        return Store::transaction(static function () use ($id, $data) {
            $row = $id ? Store::row('campaigns', $id, true) : ['revision' => 0, 'status' => 'draft', 'title' => '', 'body' => '', 'target' => 'home', 'audience' => 'all', 'scheduled_at' => null];
            Store::revision($row, $data['revision'] ?? 0); if (!in_array($row['status'], ['draft','scheduled'], true)) { throw new Fault('campaign_locked', 'Only draft or scheduled campaigns can be edited.', 409); }
            if (array_key_exists('title', $data)) { $row['title'] = Input::text($data['title'], 1, 80); }
            if (array_key_exists('body', $data)) { $row['body'] = Input::text($data['body'], 1, 180); }
            if (array_key_exists('target', $data)) { $row['target'] = $data['target'] === 'home' ? 'home' : Input::id($data['target']); }
            if ($row['target'] !== 'home') { Catalog::get($row['target']); }
            if (array_key_exists('audience', $data)) { $row['audience'] = Input::choice($data['audience'], ['all','test']); }
            if (array_key_exists('scheduled_at', $data)) { $row['scheduled_at'] = Input::date($data['scheduled_at']); }
            if (!$row['title'] || !$row['body']) { throw new Fault('campaign_incomplete', 'Title and body are required.'); }
            if ($row['scheduled_at'] && $row['scheduled_at'] <= Store::now()) { throw new Fault('invalid_schedule', 'Choose a future send time.'); }
            // Scheduling a draft does not send until the separate confirmation action.
            $row['revision']++; $row['updated_at'] = Store::now();
            if ($id) { Store::update('campaigns', $row, ['id' => $id]); } else { $row['created_at'] = Store::now(); $row['created_by'] = get_current_user_id(); $id = (string) Store::insert('campaigns', $row); }
            Store::audit('campaign.saved', 'campaign:' . $id, ['status' => $row['status']]); return Store::row('campaigns', $id);
        });
    }
    public static function send(string $id, array $data, string $key): array {
        Input::only($data, ['revision','confirm']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Confirm the audience and send action.'); }
        if (!Settings::get()['notifications_enabled']) { throw new Fault('notifications_disabled', 'Notifications are disabled.', 403); } Firebase::credentials();
        return Store::idem('admin:' . get_current_user_id(), 'campaign:' . $id, $key, $data, static function () use ($id, $data) {
            $row = Store::row('campaigns', $id, true); Store::revision($row, $data['revision'] ?? null);
            if ($row['status'] !== 'draft') { throw new Fault('campaign_already_confirmed', 'This campaign has already been confirmed.', 409); }
            if ($row['target'] !== 'home') { Catalog::get($row['target']); }
            $status = $row['scheduled_at'] ? 'scheduled' : 'queued'; Store::update('campaigns', ['status' => $status, 'revision' => (int) $row['revision'] + 1, 'updated_at' => Store::now()], ['id' => $id]);
            if ($status === 'queued') { self::enqueue('campaign_plan', 'campaign-plan:' . $id . ':0', ['campaign_id' => $id, 'after' => 0]); }
            Store::audit('campaign.confirmed', 'campaign:' . $id, ['status' => $status]); return ['id' => $id, 'status' => $status];
        }, true);
    }
    public static function cancel(string $id, array $data): array {
        Input::only($data, ['revision','confirm']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Confirm cancellation.'); }
        return Store::transaction(static function () use ($id, $data) { $row = Store::row('campaigns', $id, true); Store::revision($row, $data['revision'] ?? null); if (!in_array($row['status'], ['draft','scheduled','queued'], true)) { throw new Fault('campaign_sending', 'Sending has begun; cancellation is unavailable.', 409); } Store::update('campaigns', ['status' => 'cancelled', 'revision' => (int) $row['revision'] + 1, 'updated_at' => Store::now()], ['id' => $id]); Store::audit('campaign.cancelled', 'campaign:' . $id); return ['cancelled' => true]; });
    }
    public static function plan(array $payload): void {
        global $wpdb; $id = $payload['campaign_id']; $row = Store::row('campaigns', $id);
        if (in_array($row['status'], ['cancelled','failed','completed','partially_failed'], true)) { return; }
        if (!Settings::get()['notifications_enabled']) { throw new Fault('notifications_disabled', 'Notifications are disabled.', 503); }
        if ($row['target'] !== 'home') { Catalog::get($row['target']); }
        $where = 'consent=1 AND token_cipher<>\'\''; if ($row['audience'] === 'test') { $where .= ' AND is_test=1'; }
        $max_device = $payload['max_device_id'] ?? (int) $wpdb->get_var('SELECT MAX(id) FROM ' . Store::table('devices') . " WHERE $where");
        $devices = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . Store::table('devices') . " WHERE $where AND id>%d AND id<=%d ORDER BY id LIMIT 100", $payload['after'], $max_device));
        Store::transaction(static function () use ($id, $devices, $payload, $max_device, $wpdb) {
            Store::row('campaigns', $id, true); Store::update('campaigns', ['status' => 'sending'], ['id' => $id]);
            foreach ($devices as $device) { Store::query($wpdb->prepare('INSERT INTO ' . Store::table('campaign_deliveries') . " (campaign_id,device_id,status) VALUES (%s,%s,'pending') ON DUPLICATE KEY UPDATE device_id=device_id", $id, $device)); self::enqueue('campaign_device', 'campaign-device:' . $id . ':' . $device, ['campaign_id' => $id, 'device_id' => (string) $device]); }
            if (count($devices) === 100) { $after = (int) end($devices); self::enqueue('campaign_plan', 'campaign-plan:' . $id . ':' . $after, ['campaign_id' => $id, 'after' => $after, 'max_device_id' => $max_device]); }
            else { self::enqueue('campaign_finalize', 'campaign-finalize:' . $id, ['campaign_id' => $id], gmdate('Y-m-d H:i:s', time() + 60)); }
            self::campaign_counts($id);
        });
    }
    public static function deliver(array $payload): void {
        global $wpdb; $id = $payload['campaign_id']; $device_id = $payload['device_id']; $row = Store::row('campaigns', $id);
        if ($row['status'] === 'cancelled') { return; }
        if (!Settings::get()['notifications_enabled']) { throw new Fault('notifications_disabled', 'Notifications are disabled.', 503); }
        $table = Store::table('campaign_deliveries');
        $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE campaign_id=%s AND device_id=%s", $id, $device_id));
        if (in_array($status, ['accepted','failed','skipped'], true)) { return; }
        $device = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Store::table('devices') . ' WHERE id=%s', $device_id), ARRAY_A);
        if (!$device || !$device['consent'] || !$device['token_cipher']) { Store::update('campaign_deliveries', ['status' => 'skipped'], ['campaign_id' => $id, 'device_id' => $device_id]); self::campaign_counts($id); return; }
        if ($row['target'] !== 'home') { Catalog::get($row['target']); }
        Store::update('campaign_deliveries', ['status' => 'sending'], ['campaign_id' => $id, 'device_id' => $device_id]);
        Store::query($wpdb->prepare("UPDATE $table SET attempts=attempts+1 WHERE campaign_id=%s AND device_id=%s", $id, $device_id));
        try {
            Firebase::http('https://fcm.googleapis.com/v1/projects/' . rawurlencode(Settings::get()['firebase_project_id']) . '/messages:send', ['message' => ['token' => Firebase::decrypt($device['token_cipher']), 'notification' => ['title' => $row['title'], 'body' => $row['body']], 'data' => ['campaign_id' => (string) $id, 'destination' => $row['target'] === 'home' ? 'home' : 'recipe', 'recipe_id' => $row['target'] === 'home' ? '' : (string) $row['target']]]], Firebase::access());
            Store::update('campaign_deliveries', ['status' => 'accepted'], ['campaign_id' => $id, 'device_id' => $device_id]);
        } catch (Fault $e) {
            if (in_array($e->slug, ['invalid_device_token','firebase_not_found'], true)) {
                Store::update('devices', ['token_cipher' => '', 'consent' => 0, 'user_id' => null], ['id' => $device_id]); Store::update('campaign_deliveries', ['status' => 'failed'], ['campaign_id' => $id, 'device_id' => $device_id]);
            } else { throw $e; }
        }
        self::campaign_counts($id);
    }
    public static function campaign_counts(string $id): array {
        global $wpdb;
        $counts = $wpdb->get_row($wpdb->prepare('SELECT COUNT(*) eligible,SUM(attempts) attempted,SUM(status=\'accepted\') accepted,SUM(status=\'failed\') permanent_failures,SUM(status IN (\'pending\',\'sending\')) remaining FROM ' . Store::table('campaign_deliveries') . ' WHERE campaign_id=%s', $id), ARRAY_A);
        $update = []; foreach (['eligible','attempted','accepted','permanent_failures'] as $field) { $update[$field] = (int) $counts[$field]; } Store::update('campaigns', $update, ['id' => $id]); return $counts;
    }
    public static function finalize(array $payload): void {
        $id = $payload['campaign_id']; $campaign = Store::row('campaigns', $id); if ($campaign['status'] === 'cancelled') { return; }
        $counts = self::campaign_counts($id); if ((int) $counts['remaining'] > 0) { throw new Fault('campaign_in_progress', 'Campaign deliveries are still queued.', 503); }
        $status = (int) $counts['permanent_failures'] ? ((int) $counts['accepted'] ? 'partially_failed' : 'failed') : 'completed'; Store::update('campaigns', ['status' => $status, 'updated_at' => Store::now()], ['id' => $id]); Store::audit('campaign.finished', 'campaign:' . $id, ['status' => $status]);
    }
    public static function run(): void {
        global $wpdb; if (get_option('quickfix_schema_version') !== Schema::VERSION) { return; }
        update_option('quickfix_last_queue_run', Store::now(), false);
        // One queue runner at a time, including external scheduler and WP-Cron invocations.
        $lock_name = 'quickfix_worker_' . substr(hash('sha256', $wpdb->prefix), 0, 48); if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)', $lock_name)) !== 1) { return; }
        try {
            $campaigns = $wpdb->get_results($wpdb->prepare('SELECT id FROM ' . Store::table('campaigns') . " WHERE status='scheduled' AND scheduled_at<=%s ORDER BY scheduled_at,id LIMIT 10", Store::now()), ARRAY_A);
            foreach ($campaigns as $campaign) { Store::transaction(static function () use ($campaign) { $row = Store::row('campaigns', (string) $campaign['id'], true); if ($row['status'] === 'scheduled') { Store::update('campaigns', ['status' => 'queued'], ['id' => $campaign['id']]); self::enqueue('campaign_plan', 'campaign-plan:' . $campaign['id'] . ':0', ['campaign_id' => (string) $campaign['id'], 'after' => 0]); } }); }
            $started = microtime(true); $table = Store::table('jobs');
            for ($i = 0; $i < 10 && microtime(true) - $started < 45; $i++) {
                $owner = wp_generate_uuid4();
                $job = Store::transaction(static function () use ($wpdb, $table, $owner) {
                    $now = Store::now(); $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE (status='queued' OR (status='running' AND lock_until<%s)) AND available_at<=%s ORDER BY available_at,id LIMIT 1 FOR UPDATE", $now, $now), ARRAY_A);
                    if ($job) { Store::update('jobs', ['status' => 'running', 'attempts' => (int) $job['attempts'] + 1, 'lock_owner' => $owner, 'lock_until' => gmdate('Y-m-d H:i:s', time() + 120), 'updated_at' => $now], ['id' => $job['id']]); } return $job;
                });
                if (!$job) { break; }
                $payload = json_decode($job['payload'], true);
                try {
                    switch ($job['kind']) { case 'deletion': self::finish_deletion($payload); break; case 'campaign_plan': self::plan($payload); break; case 'campaign_device': self::deliver($payload); break; case 'campaign_finalize': self::finalize($payload); break; default: throw new Fault('unknown_job', 'Unsupported job.', 503); }
                    Store::update('jobs', ['status' => 'completed', 'lock_until' => null, 'lock_owner' => null, 'safe_error' => '', 'payload' => '{}', 'updated_at' => Store::now()], ['id' => $job['id'], 'lock_owner' => $owner]);
                } catch (\Throwable $e) {
                    $attempts = (int) $job['attempts'] + 1; $slug = $e instanceof Fault ? $e->slug : 'worker_error';
                    $durable = $job['kind'] === 'deletion' || $job['kind'] === 'campaign_finalize';
                    $retry = $durable || $attempts < 8;
                    Store::update('jobs', ['status' => $retry ? 'queued' : 'failed', 'available_at' => gmdate('Y-m-d H:i:s', time() + min(21600, 30 * 2 ** min($attempts, 10))), 'lock_until' => null, 'lock_owner' => null, 'safe_error' => $slug, 'updated_at' => Store::now()], ['id' => $job['id'], 'lock_owner' => $owner]);
                    if ($job['kind'] === 'campaign_device') { Store::query($wpdb->prepare('UPDATE ' . Store::table('campaigns') . ' SET retries=retries+1 WHERE id=%s', $payload['campaign_id'])); if (!$retry) { Store::update('campaign_deliveries', ['status' => 'failed'], ['campaign_id' => $payload['campaign_id'], 'device_id' => $payload['device_id']]); } }
                    if ($job['kind'] === 'campaign_plan' && !$retry) { Store::update('campaigns', ['status' => 'failed'], ['id' => $payload['campaign_id']]); }
                    Store::audit('job.retry', 'job:' . $job['id'], ['reason' => $slug, 'job' => $job['kind']]);
                }
            }
            self::maintenance();
        } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name)); }
    }
    public static function maintenance(): void {
        global $wpdb; if ((int) get_option('quickfix_last_maintenance', 0) > time() - 3600) { return; }
        $ids = $wpdb->get_col('SELECT recipe_id FROM ' . Store::table('rating_stats') . ' ORDER BY calculated_at LIMIT 100'); foreach ($ids as $id) { Store::transaction(static fn() => Community::reconcile((string) $id)); }
        foreach (['idempotency','rate_limits'] as $table) { Store::query($wpdb->prepare('DELETE FROM ' . Store::table($table) . ' WHERE expires_at<%s LIMIT 1000', Store::now())); }
        $settings = Settings::get(); Store::query($wpdb->prepare('DELETE FROM ' . Store::table('audit') . ' WHERE created_at<%s LIMIT 1000', gmdate('Y-m-d H:i:s', time() - 86400 * $settings['audit_retention_days'])));
        Store::query($wpdb->prepare('DELETE FROM ' . Store::table('jobs') . " WHERE status='completed' AND kind NOT LIKE 'campaign%%' AND updated_at<%s LIMIT 1000", gmdate('Y-m-d H:i:s', time() - 86400 * $settings['operational_retention_days'])));
        update_option('quickfix_last_maintenance', time(), false);
    }
    public static function diagnostics(): array {
        global $wpdb; $configured = true; try { Firebase::credentials(); } catch (\Throwable $e) { $configured = false; }
        $source = Catalog::source_table('recipes'); $source_ok = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($source))) === $source;
        return ['schema_version' => get_option('quickfix_schema_version'), 'migration_error' => get_option('quickfix_migration_error', null), 'source_schema_available' => $source_ok, 'php' => PHP_VERSION, 'firebase_configured' => $configured, 'device_encryption_configured' => strlen(Store::secret('QUICKFIX_DEVICE_KEY')) >= 32, 'deletion_key_configured' => strlen(Store::secret('QUICKFIX_IDENTITY_HASH_KEY')) >= 32, 'external_scheduler_confirmed' => Store::secret('QUICKFIX_EXTERNAL_SCHEDULER') === '1', 'last_queue_run' => get_option('quickfix_last_queue_run', null), 'queue_backlog' => (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . Store::table('jobs') . " WHERE status IN ('queued','running')"), 'deletion_retries' => (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . Store::table('jobs') . " WHERE kind='deletion' AND safe_error<>'' AND status<>'completed'"), 'last_api_health_check' => get_option('quickfix_api_health', null), 'cdn_purge_adapter' => has_action('quickfix_purge_catalog'), 'legacy_wordpress_comments' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} c INNER JOIN {$wpdb->posts} p ON p.ID=c.comment_post_ID WHERE p.post_type='recipes'")];
    }
    public static function export_tombstones(array $args): void {
        global $wpdb; if (empty($args[0])) { \WP_CLI::error('Specify an absolute export path outside the public web root.'); }
        $parent = realpath(dirname($args[0])); $root = strtolower(str_replace('\\','/',realpath(ABSPATH)) . '/');
        if (!$parent || str_starts_with(strtolower(str_replace('\\','/',$parent) . '/'), $root)) { \WP_CLI::error('Export must be outside the public web root.'); }
        $rows = $wpdb->get_results('SELECT identity_hash,deleted_at,replay_state FROM ' . Store::table('deletion_tombstones'), ARRAY_A);
        if (file_put_contents($args[0], wp_json_encode($rows, JSON_PRETTY_PRINT), LOCK_EX) === false) { \WP_CLI::error('Export failed.'); } chmod($args[0], 0600); \WP_CLI::success('Exported minimal tombstones; protect this file independently of database backups.');
    }
    public static function replay_tombstones(array $args): void {
        global $wpdb; $path = $args[0] ?? ''; if (!is_file($path)) { \WP_CLI::error('Specify the independently retained tombstone export.'); }
        $rows = json_decode(file_get_contents($path), true); if (!is_array($rows)) { \WP_CLI::error('Invalid export.'); }
        foreach ($rows as $row) { if (!preg_match('/^[a-f0-9]{64}$/D', $row['identity_hash'] ?? '')) { \WP_CLI::error('Invalid identity hash.'); } Store::query($wpdb->prepare('INSERT INTO ' . Store::table('deletion_tombstones') . ' (identity_hash,deleted_at,replay_state) VALUES (%s,%s,%s) ON DUPLICATE KEY UPDATE deleted_at=LEAST(deleted_at,VALUES(deleted_at))', $row['identity_hash'], $row['deleted_at'], $row['replay_state'])); }
        $users = $wpdb->get_results('SELECT id,firebase_uid FROM ' . Store::table('users') . " WHERE status<>'deleted'", ARRAY_A);
        foreach ($users as $user) { $hash = Firebase::identity_hash($user['firebase_uid']); if ($wpdb->get_var($wpdb->prepare('SELECT identity_hash FROM ' . Store::table('deletion_tombstones') . ' WHERE identity_hash=%s', $hash))) { Store::transaction(static function () use ($user, $hash) { $current = Store::row('users', (string) $user['id'], true); $apple = in_array('apple.com', json_decode($current['providers'], true) ?: [], true); Store::update('users', ['status' => 'deletion_pending'], ['id' => $user['id']]); self::scrub((string) $user['id']); self::enqueue('deletion', 'restore-deletion:' . $hash, ['user_id' => (string) $user['id'], 'identity_hash' => $hash, 'apple_revocation' => $apple]); }); } }
        \WP_CLI::success('Restored identities suppressed and deletion jobs enqueued. Run the worker before reopening traffic.');
    }
}
