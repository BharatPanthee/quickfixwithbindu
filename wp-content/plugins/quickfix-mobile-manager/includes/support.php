<?php
namespace QuickFix;
defined('ABSPATH') || exit;

final class Fault extends \RuntimeException {
    public string $slug;
    public int $status;
    public array $fields;
    public function __construct(string $slug, string $message, int $status = 422, array $fields = []) {
        parent::__construct($message); $this->slug = $slug; $this->status = $status; $this->fields = $fields;
    }
}

final class Store {
    public static function table(string $name): string { global $wpdb; return $wpdb->prefix . 'quickfix_' . $name; }
    public static function now(): string { return gmdate('Y-m-d H:i:s'); }
    public static function request_id(): string { static $id; return $id ??= wp_generate_uuid4(); }
    public static function query(string $sql) { global $wpdb; $previous = $wpdb->suppress_errors(true); try { $result = $wpdb->query($sql); if ($result === false) { throw new Fault('storage_unavailable', 'The operation could not be saved. Please retry.', 503); } return $result; } finally { $wpdb->suppress_errors($previous); } }
    public static function insert(string $table, array $data): int { global $wpdb; $previous = $wpdb->suppress_errors(true); try { if ($wpdb->insert(self::table($table), $data) === false) { if (str_contains($wpdb->last_error, 'Duplicate entry')) { throw new Fault('unique_conflict', 'An item with this identity already exists.', 409); } throw new Fault('storage_unavailable', 'The operation could not be saved. Please retry.', 503); } return (int) $wpdb->insert_id; } finally { $wpdb->suppress_errors($previous); } }
    public static function update(string $table, array $data, array $where): void { global $wpdb; $previous = $wpdb->suppress_errors(true); try { if ($wpdb->update(self::table($table), $data, $where) === false) { throw new Fault('storage_unavailable', 'The operation could not be saved. Please retry.', 503); } } finally { $wpdb->suppress_errors($previous); } }
    public static function transaction(callable $work) {
        static $depth = 0;
        $level = $depth++; $savepoint = 'quickfix_' . $level;
        self::query($level ? 'SAVEPOINT ' . $savepoint : 'START TRANSACTION');
        try { $result = $work(); self::query($level ? 'RELEASE SAVEPOINT ' . $savepoint : 'COMMIT'); return $result; }
        catch (\Throwable $e) { self::query($level ? 'ROLLBACK TO SAVEPOINT ' . $savepoint : 'ROLLBACK'); throw $e; }
        finally { $depth--; }
    }
    public static function row(string $table, string $id, bool $lock = false): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . self::table($table) . ' WHERE id=%s' . ($lock ? ' FOR UPDATE' : ''), $id), ARRAY_A);
        if (!$row) { throw new Fault('not_found', 'Resource unavailable.', 404); }
        return $row;
    }
    public static function revision(array $row, $revision): void {
        if (!is_numeric($revision) || (int) $revision !== (int) $row['revision']) { throw new Fault('revision_conflict', 'This item changed. Refresh and review before saving.', 409); }
    }
    public static function bump(): void {
        global $wpdb;
        // Atomic revision avoids lost cache invalidations from concurrent administrators.
        add_option('quickfix_catalog_revision', '1', '', false);
        self::query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value=CAST(option_value AS UNSIGNED)+1 WHERE option_name=%s", 'quickfix_catalog_revision'));
        wp_cache_delete('quickfix_catalog_revision', 'options');
        wp_cache_delete('alloptions', 'options');
        do_action('quickfix_purge_catalog');
        do_action('litespeed_purge_all');
    }
    public static function audit(string $action, string $target, array $metadata = [], ?int $consumer = null): void {
        $safe = array_intersect_key($metadata, array_flip(['state', 'status', 'revision', 'reason', 'count', 'job', 'fields', 'before', 'after']));
        if (isset($safe['reason'])) { $safe['reason'] = preg_replace(['~Bearer\s+\S+~i', '~(?:AIza|sk-proj-|github_pat_|ghp_)[A-Za-z0-9_-]{15,}~', '~[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}~i'], '[redacted]', (string) $safe['reason']); }
        self::insert('audit', ['actor_type' => $consumer ? 'consumer' : (get_current_user_id() ? 'wordpress' : 'system'), 'actor_id' => $consumer ?? get_current_user_id(), 'action' => $action, 'target' => $target, 'metadata' => wp_json_encode($safe), 'request_id' => self::request_id(), 'created_at' => self::now()]);
    }
    public static function secret(string $name): string { return defined($name) ? (string) constant($name) : (string) getenv($name); }
    public static function rate(string $scope, string $actor, int $limit, int $seconds): void {
        global $wpdb;
        $bucket = hash_hmac('sha256', $scope . ':' . $actor . ':' . floor(time() / $seconds), wp_salt('auth'));
        $table = self::table('rate_limits');
        self::query($wpdb->prepare("INSERT INTO $table (bucket,hits,expires_at) VALUES (%s,1,%s) ON DUPLICATE KEY UPDATE hits=hits+1", $bucket, gmdate('Y-m-d H:i:s', time() + $seconds * 2)));
        $hits = (int) $wpdb->get_var($wpdb->prepare("SELECT hits FROM $table WHERE bucket=%s", $bucket));
        if ($hits > $limit) { throw new Fault('rate_limited', 'Please wait before trying again.', 429, ['retry_after' => $seconds - time() % $seconds]); }
    }
    public static function idem(string $actor, string $scope, string $key, array $payload, callable $work, bool $durable = false) {
        global $wpdb;
        if (!preg_match('/^[A-Za-z0-9._:-]{8,128}$/D', $key)) { throw new Fault('idempotency_required', 'Supply an Idempotency-Key header (8–128 characters).', 400); }
        $hash = hash('sha256', wp_json_encode($payload));
        $dedupe = hash('sha256', "$actor:$scope:$key");
        return self::transaction(static function () use ($wpdb, $dedupe, $hash, $work, $durable) {
            $table = self::table('idempotency');
            self::query($wpdb->prepare("INSERT INTO $table (dedupe_key,request_hash,response,expires_at) VALUES (%s,%s,NULL,%s) ON DUPLICATE KEY UPDATE dedupe_key=dedupe_key", $dedupe, $hash, gmdate('Y-m-d H:i:s', time() + ($durable ? 86400 * 180 : 86400))));
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE dedupe_key=%s FOR UPDATE", $dedupe), ARRAY_A);
            if (!hash_equals($row['request_hash'], $hash)) { throw new Fault('idempotency_conflict', 'This key was used for a different request.', 409); }
            if ($row['response'] !== null) { return json_decode($row['response'], true); }
            $result = $work();
            // Do not persist comment bodies, registration credentials or tokens in replay records.
            self::query($wpdb->prepare("UPDATE $table SET response=%s WHERE dedupe_key=%s", wp_json_encode($result), $dedupe));
            return $result;
        });
    }
    public static function cursor(array $query, ?string $cursor, int $offset = 0): array {
        ksort($query); $binding = hash('sha256', wp_json_encode($query)); $revision = (string) get_option('quickfix_catalog_revision', '1');
        if ($cursor) {
            if (strlen($cursor) > 2048) { throw new Fault('invalid_cursor', 'Invalid cursor.', 400); }
            $parts = explode('.', $cursor);
            if (count($parts) !== 2 || !hash_equals(hash_hmac('sha256', $parts[0], wp_salt('nonce')), $parts[1])) { throw new Fault('invalid_cursor', 'Invalid cursor.', 400); }
            $data = json_decode(base64_decode(strtr($parts[0], '-_', '+/'), true) ?: '', true);
            if (!$data || ($data['query'] ?? '') !== $binding || ($data['revision'] ?? '') !== $revision || ($data['expiry'] ?? 0) < time()) { throw new Fault('cursor_restart', 'The listing changed. Restart from the first page.', 409); }
            $offset = (int) $data['offset'];
            if ($offset < 0 || $offset > 100000) { throw new Fault('invalid_cursor', 'Invalid cursor.', 400); }
        }
        $encode = static function (int $next) use ($binding, $revision): string {
            $data = rtrim(strtr(base64_encode(wp_json_encode(['query' => $binding, 'revision' => $revision, 'offset' => $next, 'expiry' => time() + 300])), '+/', '-_'), '=');
            return $data . '.' . hash_hmac('sha256', $data, wp_salt('nonce'));
        };
        return [$offset, $encode];
    }
}

final class Input {
    public static function text($value, int $min = 0, int $max = 2000, bool $no_urls = false): string {
        if (!is_string($value)) { throw new Fault('invalid_text', 'Expected plain text.', 422); }
        $value = trim($value);
        if (mb_strlen($value) < $min || mb_strlen($value) > $max || $value !== wp_strip_all_tags($value) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', $value) || ($no_urls && preg_match('~(?:https?://|www\.|\b[\w-]+\.(?:com|net|org|io)\b)~iu', $value))) { throw new Fault('invalid_text', "Use plain text between $min and $max characters" . ($no_urls ? ', without links.' : '.'), 422); }
        return $value;
    }
    public static function id($value): string { if (!is_string($value) && !is_int($value)) { throw new Fault('invalid_id', 'Invalid identifier.', 400); } $value = (string) $value; if (!preg_match('/^[1-9][0-9]{0,18}$/D', $value)) { throw new Fault('invalid_id', 'Invalid identifier.', 400); } return $value; }
    public static function integer($value, int $min, int $max): int { if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min || (int) $value > $max) { throw new Fault('invalid_number', "Expected a whole number from $min to $max.", 422); } return (int) $value; }
    public static function boolean($value): int { if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) { throw new Fault('invalid_boolean', 'Expected true or false.', 422); } return (int) $value; }
    public static function choice($value, array $choices): string { if (!is_string($value) || !in_array($value, $choices, true)) { throw new Fault('invalid_choice', 'Unsupported value.', 422); } return $value; }
    public static function date($value): ?string {
        if ($value === null || $value === '') { return null; }
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $value)) { throw new Fault('invalid_date', 'Use an ISO 8601 timestamp with a timezone.', 422); }
        try { $date = new \DateTimeImmutable($value); if (\DateTimeImmutable::getLastErrors() && \DateTimeImmutable::getLastErrors()['warning_count']) { throw new \Exception(); } return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'); }
        catch (\Exception $e) { throw new Fault('invalid_date', 'Invalid date.', 422); }
    }
    public static function url($value, bool $image = false): string {
        if ($value === '') { return ''; }
        if (!is_string($value) || strlen($value) > 1000 || !filter_var($value, FILTER_VALIDATE_URL)) { throw new Fault('invalid_url', 'Use a valid HTTPS URL.', 422); }
        $host = strtolower((string) wp_parse_url($value, PHP_URL_HOST));
        $scheme = wp_parse_url($value, PHP_URL_SCHEME);
        $local = in_array(wp_get_environment_type(), ['local', 'development'], true) && $host === strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        if (($scheme !== 'https' && !($local && $scheme === 'http')) || wp_parse_url($value, PHP_URL_USER) || !$host || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP) || str_ends_with($host, '.internal')) { throw new Fault('invalid_url', 'Use a public HTTPS URL.', 422); }
        if ($image) {
            $hosts = array_merge([strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST)), 'i.ytimg.com'], array_filter(explode(',', Store::secret('QUICKFIX_MEDIA_HOSTS'))));
            if (!in_array($host, $hosts, true)) { throw new Fault('invalid_media_host', 'Image host is not approved by server configuration.', 422); }
        }
        return esc_url_raw($value);
    }
    public static function only(array $data, array $allowed): void { $unknown = array_diff(array_keys($data), $allowed); if ($unknown) { throw new Fault('unknown_fields', 'Unsupported fields.', 400, ['fields' => array_values($unknown)]); } }
    public static function plain($value): string { return trim(html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
}

final class Settings {
    public static function defaults(): array { return ['brand_name' => 'QuickFix With Bindu', 'logo_url' => '', 'support_email' => '', 'about' => '', 'privacy_url' => '', 'terms_url' => '', 'community_rules_url' => '', 'deletion_url' => '', 'comments_enabled' => true, 'ratings_enabled' => true, 'hide_comments' => false, 'notifications_enabled' => false, 'hide_empty_categories' => true, 'maintenance_writes' => false, 'maintenance_message' => '', 'minimum_api_version' => '1', 'minimum_app_version' => '1.0.0', 'firebase_project_id' => '', 'firebase_project_number' => '', 'firebase_client' => [], 'home_sections' => ['featured', 'categories', 'latest'], 'public_rate' => 120, 'write_rate' => 10, 'audit_retention_days' => 180, 'operational_retention_days' => 30, 'revision' => 1]; }
    public static function get(): array { return array_replace(self::defaults(), (array) get_option('quickfix_settings', [])); }
    public static function save(array $data): array {
        global $wpdb;
        return Store::transaction(static function () use ($data, $wpdb) {
            add_option('quickfix_settings', self::defaults(), '', false);
            $raw = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s FOR UPDATE", 'quickfix_settings'));
            $settings = array_replace(self::defaults(), (array) maybe_unserialize($raw));
            Store::revision($settings, $data['revision'] ?? null); Input::only($data, array_keys(self::defaults()));
            foreach ($data as $key => $value) {
                if ($key === 'revision') { continue; }
                if (is_bool(self::defaults()[$key])) { $settings[$key] = (bool) Input::boolean($value); }
                elseif (str_ends_with($key, '_url')) { $settings[$key] = Input::url($value, $key === 'logo_url'); }
                elseif (in_array($key, ['public_rate', 'write_rate', 'audit_retention_days', 'operational_retention_days'], true)) { $settings[$key] = Input::integer($value, 1, 365); }
                elseif ($key === 'support_email') { if ($value && !is_email($value)) { throw new Fault('invalid_email', 'Invalid support email.'); } $settings[$key] = sanitize_email($value); }
                elseif ($key === 'home_sections') { if (!is_array($value) || count($value) > 3) { throw new Fault('invalid_sections', 'Choose up to three home sections.'); } foreach ($value as $section) { Input::choice($section, ['featured', 'categories', 'latest']); } $settings[$key] = array_values(array_unique($value)); }
                elseif ($key === 'firebase_client') { if (!is_array($value)) { throw new Fault('invalid_config', 'Invalid public Firebase configuration.'); } Input::only($value, ['apiKey', 'authDomain', 'projectId', 'appId', 'messagingSenderId']); foreach ($value as $field => $text) { $value[$field] = Input::text($text, 0, 250); } $settings[$key] = $value; }
                else { $settings[$key] = Input::text($value, 0, in_array($key, ['about', 'maintenance_message'], true) ? 2000 : 100); }
            }
            $settings['revision']++;
            update_option('quickfix_settings', $settings, false); Store::bump(); Store::audit('settings.updated', 'settings', ['fields' => array_keys($data), 'revision' => $settings['revision']]);
            return $settings;
        });
    }
}
