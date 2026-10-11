<?php
namespace QuickFix;
defined('ABSPATH') || exit;

final class API {
    public static function normalize_errors($response, $server, $request) {
        if (!str_starts_with($request->get_route(), '/quickfix/v1/')) { return $response; }
        $data = $response->get_data();
        if ($response->get_status() >= 400 && isset($data['code'], $data['message'])) { return self::error(new Fault($data['code'], $data['message'], $response->get_status())); }
        $response->header('Vary', 'Authorization, Cookie'); return $response;
    }
    public static array $contracts = [];
    private static function route(string $path, string $methods, string $access, callable $handler): void {
        self::$contracts[] = ['path' => $path, 'methods' => $methods, 'access' => $access];
        register_rest_route('quickfix/v1', $path, ['methods' => $methods, 'permission_callback' => static function ($request) use ($access) {
            try {
                if (!get_option('quickfix_migration_error') && get_option('quickfix_schema_version') === Schema::VERSION) { Store::rate('public', $_SERVER['REMOTE_ADDR'] ?? 'unknown', Settings::get()['public_rate'], 60); }
                if ($access === 'public' || $access === 'device') { return true; }
                if ($access === 'consumer') { Identity::authenticate($request); return true; }
                if (!is_user_logged_in() || !current_user_can($access)) { throw new Fault('forbidden', 'You do not have permission for this action.', 403); }
                if (!wp_verify_nonce($request->get_header('x-wp-nonce'), 'wp_rest')) { throw new Fault('invalid_nonce', 'Refresh your administrator session.', 403); } return true;
            } catch (Fault $e) { return new \WP_Error($e->slug, $e->getMessage(), ['status' => $e->status, 'request_id' => Store::request_id()]); }
        }, 'callback' => static function ($request) use ($handler, $access) {
            try {
                if (get_option('quickfix_schema_version') !== Schema::VERSION || get_option('quickfix_migration_error')) { throw new Fault('schema_unavailable', 'The service needs administrator attention.', 503); }
                $settings = Settings::get(); $private = $access !== 'public' || (bool) $request->get_header('authorization');
                if ($access === 'consumer' && !in_array($request->get_method(), ['GET','HEAD'], true)) { $user = Identity::authenticate($request); Store::rate('write', $user['id'], $settings['write_rate'], 60); }
                $data = $handler($request); $response = new \WP_REST_Response(['data' => $data, 'meta' => ['catalog_revision' => (string) get_option('quickfix_catalog_revision', '1'), 'api_version' => '1'], 'request_id' => Store::request_id()]);
                $response->header('Cache-Control', $private || $request->get_method() !== 'GET' ? 'private, no-store' : 'public, max-age=60, s-maxage=60');
                $response->header('Vary', 'Authorization, Cookie'); $response->header('X-Request-ID', Store::request_id());
                if (!$private && $request->get_method() === 'GET') {
                    $etag = '"' . hash('sha256', wp_json_encode([$data, get_option('quickfix_catalog_revision')])) . '"';
                    $response->header('ETag', $etag); if ($request->get_header('if-none-match') === $etag) { $response->set_status(304); $response->set_data(null); }
                }
                if ((string) get_option('quickfix_api_health', '') < gmdate('Y-m-d H:i:s', time() - 60)) { update_option('quickfix_api_health', Store::now(), false); } return $response;
            } catch (Fault $e) { return self::error($e); }
            catch (\Throwable $e) { return self::error(new Fault('service_unavailable', 'The service is temporarily unavailable.', 503)); }
        }]);
    }
    public static function error(Fault $e): \WP_REST_Response {
        $response = new \WP_REST_Response(['error' => ['code' => $e->slug, 'message' => $e->getMessage(), 'fields' => $e->fields ?: (object) []], 'request_id' => Store::request_id()], $e->status);
        $response->header('Cache-Control', 'private, no-store'); $response->header('X-Request-ID', Store::request_id()); if ($e->status === 429) { $response->header('Retry-After', (string) ($e->fields['retry_after'] ?? 60)); } return $response;
    }
    public static function body(\WP_REST_Request $request): array { $body = $request->get_json_params(); if (!is_array($body) || array_is_list($body) && $body !== []) { throw new Fault('invalid_body', 'Send a JSON object.', 400); } if (strlen($request->get_body()) > 32768) { throw new Fault('body_too_large', 'Request is too large.', 400); } return $body; }
    public static function optional_user(\WP_REST_Request $request): ?array { return $request->get_header('authorization') ? Identity::authenticate($request) : null; }
    public static function register(): void {
        self::route('/app/config', 'GET', 'public', static function () { $settings = Settings::get(); unset($settings['revision'], $settings['firebase_project_number'], $settings['public_rate'], $settings['write_rate'], $settings['audit_retention_days'], $settings['operational_retention_days']); $settings['firebase_client'] = (object) $settings['firebase_client']; $settings['identity_available'] = true; try { Firebase::credentials(); } catch (\Throwable $e) { $settings['identity_available'] = false; } return $settings; });
        self::route('/app/home', 'GET', 'public', static fn() => Catalog::home());
        self::route('/app/categories', 'GET', 'public', static fn() => Catalog::categories());
        self::route('/app/recipes', 'GET', 'public', static fn($r) => Catalog::listing($r->get_query_params()));
        self::route('/app/recipes/(?P<id>[1-9][0-9]{0,18})', 'GET', 'public', static fn($r) => Catalog::detail($r['id']));
        self::route('/app/recipes/availability', 'POST', 'public', static function ($r) { $data = self::body($r); Input::only($data, ['ids']); if (!isset($data['ids']) || !is_array($data['ids']) || count($data['ids']) > 100) { throw new Fault('invalid_ids', 'Provide at most 100 recipe IDs.', 400); } $items = []; foreach (array_unique($data['ids']) as $raw) { $id = Input::id($raw); try { Catalog::get($id); $available = true; } catch (Fault $e) { if ($e->status !== 404) { throw $e; } $available = false; } $items[] = ['id' => $id, 'available' => $available]; } return ['items' => $items, 'availability_verified_at' => gmdate('c'), 'offline_ttl_hours' => 24]; });
        foreach (['app','website'] as $channel) {
            $root = '/' . $channel . '/recipes/(?P<id>[1-9][0-9]{0,18})';
            self::route($root . ($channel === 'app' ? '/comments' : '/community'), 'GET', 'public', static fn($r) => $channel === 'app' ? Community::comments($r['id'], $channel, $r->get_query_params(), self::optional_user($r)) : Community::community($r['id'], $channel, $r->get_query_params(), self::optional_user($r)));
            self::route($root . '/comments', 'POST', 'consumer', static fn($r) => Community::create_comment($r['id'], $channel, Identity::authenticate($r), self::body($r), (string) $r->get_header('idempotency-key')));
            self::route($root . '/rating', 'PUT,DELETE', 'consumer', static fn($r) => Community::rating($r['id'], $channel, Identity::authenticate($r), $r->get_method() === 'DELETE' ? null : self::body($r)));
        }
        self::route('/me/session', 'POST', 'consumer', static function ($r) { $user = Identity::authenticate($r); $data = self::body($r); Input::only($data, ['apple_authorization_code']); if (isset($data['apple_authorization_code'])) { Apple::associate($user, $data['apple_authorization_code']); } return Identity::profile($user); });
        self::route('/me', 'GET,PATCH', 'consumer', static function ($r) {
            $user = Identity::authenticate($r); if ($r->get_method() === 'GET') { return Identity::profile($user); } Identity::active($user, false);
            $data = self::body($r); Input::only($data, ['display_name','revision']); $name = Input::text($data['display_name'] ?? '', 2, 50, true); if (!apply_filters('quickfix_display_name_allowed', true, $name)) { throw new Fault('invalid_name', 'Choose another display name.'); }
            return Store::transaction(static function () use ($user, $data, $name) { $current = Store::row('users', (string) $user['id'], true); Store::revision($current, $data['revision'] ?? null); if ($current['status'] !== 'active') { throw new Fault('account_unavailable', 'Account changes are unavailable.', 403); } Store::update('users', ['display_name' => $name, 'revision' => (int) $current['revision'] + 1], ['id' => $user['id']]); Store::bump(); return Identity::profile(Store::row('users', (string) $user['id'])); });
        });
        self::route('/me/activity', 'GET', 'consumer', static function ($r) {
            global $wpdb; $user = Identity::authenticate($r); $params = $r->get_query_params(); Input::only($params, ['cursor','limit']); $limit = Input::integer($params['limit'] ?? 20, 1, 50); [$offset, $cursor] = Store::cursor(['activity' => $user['id'], 'limit' => $limit], $params['cursor'] ?? null);
            $rows = $wpdb->get_results($wpdb->prepare('SELECT id,recipe_id,body text,status,revision,created_at,\'comment\' kind FROM ' . Store::table('comments') . ' WHERE user_id=%s UNION ALL SELECT id,recipe_id,CAST(stars AS CHAR) text,status,revision,created_at,\'rating\' kind FROM ' . Store::table('ratings') . ' WHERE user_id=%s ORDER BY created_at DESC,kind,id DESC LIMIT %d OFFSET %d', $user['id'], $user['id'], $limit + 1, $offset), ARRAY_A);
            $more = count($rows) > $limit; $rows = array_slice($rows, 0, $limit); foreach ($rows as &$row) { $row['id'] = (string) $row['id']; $row['recipe_id'] = (string) $row['recipe_id']; $row['text'] = Input::plain($row['text']); } unset($row); return ['items' => $rows, 'next_cursor' => $more ? $cursor($offset + $limit) : null];
        });
        self::route('/me/comments/(?P<id>[1-9][0-9]{0,18})', 'PATCH,DELETE', 'consumer', static fn($r) => Community::own_comment($r['id'], Identity::authenticate($r), $r->get_method() === 'DELETE' ? null : self::body($r)));
        self::route('/me/ratings/(?P<id>[1-9][0-9]{0,18})', 'DELETE', 'consumer', static fn($r) => Community::own_rating($r['id'], Identity::authenticate($r)));
        self::route('/me/reports', 'POST', 'consumer', static fn($r) => Community::report(Identity::authenticate($r), self::body($r), (string) $r->get_header('idempotency-key')));
        self::route('/me/blocks/(?P<id>[1-9][0-9]{0,18})', 'GET,PUT,DELETE', 'consumer', static fn($r) => Community::block($r['id'], Identity::authenticate($r), $r->get_method()));
        self::route('/me/blocks', 'GET', 'consumer', static function ($r) { global $wpdb; $user = Identity::authenticate($r); $params = $r->get_query_params(); Input::only($params, ['cursor','limit']); $limit = Input::integer($params['limit'] ?? 20, 1, 50); [$offset, $cursor] = Store::cursor(['blocks' => $user['id'], 'limit' => $limit], $params['cursor'] ?? null); $rows = $wpdb->get_results($wpdb->prepare('SELECT u.id,u.display_name FROM ' . Store::table('user_blocks') . ' b INNER JOIN ' . Store::table('users') . ' u ON u.id=b.blocked_user_id WHERE b.blocker_user_id=%s ORDER BY u.id LIMIT %d OFFSET %d', $user['id'], $limit + 1, $offset), ARRAY_A); $more = count($rows) > $limit; $items = array_map(static fn($u) => ['id' => (string) $u['id'], 'display_name' => Input::plain($u['display_name'])], array_slice($rows, 0, $limit)); return ['items' => $items, 'next_cursor' => $more ? $cursor($offset + $limit) : null]; });
        self::route('/me/deletion', 'POST', 'consumer', static fn($r) => Jobs::deletion(Identity::authenticate($r), self::body($r), (string) $r->get_header('idempotency-key')));
        self::route('/app/devices', 'POST,DELETE', 'device', static fn($r) => Jobs::devices($r, self::body($r), self::optional_user($r)));
        self::route('/admin/dashboard', 'GET', 'quickfix_view_audit', static fn() => self::dashboard());
        self::route('/admin/home-layout', 'GET,PATCH', 'quickfix_manage_catalog', static function ($r) { if ($r->get_method() === 'PATCH') { $body = self::body($r); Input::only($body, ['revision','home_sections']); $settings = Settings::save($body); } else { $settings = Settings::get(); } return ['revision' => $settings['revision'], 'home_sections' => $settings['home_sections']]; });
        self::route('/admin/recipes', 'GET', 'quickfix_manage_catalog', static fn($r) => self::admin_recipes($r->get_query_params()));
        self::route('/admin/recipes/(?P<id>[1-9][0-9]{0,18})', 'GET,PATCH', 'quickfix_manage_catalog', static fn($r) => $r->get_method() === 'GET' ? Catalog::inspect($r['id']) : Catalog::save($r['id'], self::body($r)));
        self::route('/admin/recipes/bulk', 'POST', 'quickfix_manage_catalog', static function ($r) {
            $data = self::body($r); Input::only($data, ['items','action','category','confirm']); if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) > 100) { throw new Fault('invalid_bulk', 'Choose at most 100 recipes.'); }
            $action = Input::choice($data['action'] ?? '', ['draft','publish','withdraw','assign_category','clear_featured']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Review the selected recipe names and confirm this bulk action.'); }
            $results = []; foreach ($data['items'] as $item) {
                try { Input::only($item, ['id','revision']); $id = Input::id($item['id']); $meta = Catalog::metadata($id); $payload = ['revision' => $item['revision'] ?? null];
                    if ($action === 'assign_category') { $category = Input::id($data['category'] ?? ''); $inspect = Catalog::inspect($id); $payload['categories'] = array_values(array_unique(array_merge($inspect['metadata']['categories'], [$category]))); }
                    elseif ($action === 'clear_featured') { Store::transaction(static function () use ($id, $payload) { global $wpdb; Store::revision(Catalog::metadata($id, true), $payload['revision']); Store::query($wpdb->prepare('DELETE FROM ' . Store::table('home_placements') . ' WHERE recipe_id=%s', $id)); Store::audit('featured.cleared', 'recipe:' . $id); Store::bump(); }); $results[] = ['id' => $id, 'ok' => true]; continue; }
                    else { $payload['state'] = ['draft' => 'draft', 'publish' => 'published', 'withdraw' => 'withdrawn'][$action]; }
                    Catalog::save($id, $payload); $results[] = ['id' => $id, 'ok' => true];
                } catch (Fault $e) { $results[] = ['id' => (string) ($item['id'] ?? ''), 'ok' => false, 'error' => ['code' => $e->slug, 'message' => $e->getMessage()]]; }
            } return ['results' => $results];
        });
        foreach (['categories' => 'app_categories', 'home-placements' => 'home_placements'] as $resource => $table) {
            self::route('/admin/' . $resource, 'GET,POST', 'quickfix_manage_catalog', static fn($r) => $r->get_method() === 'GET' ? self::collection($table, $r->get_query_params()) : self::curation($table, null, self::body($r), 'POST'));
            self::route('/admin/' . $resource . '/(?P<id>[1-9][0-9]{0,18})', 'GET,PATCH,DELETE', 'quickfix_manage_catalog', static fn($r) => $r->get_method() === 'GET' ? Store::row($table, $r['id']) : self::curation($table, $r['id'], self::body($r), $r->get_method()));
        }
        foreach (['comments' => 'quickfix_moderate', 'ratings' => 'quickfix_moderate', 'reports' => 'quickfix_moderate', 'users' => 'quickfix_manage_users'] as $kind => $capability) {
            self::route('/admin/' . $kind, 'GET', $capability, static fn($r) => self::collection($kind, $r->get_query_params()));
            self::route('/admin/' . $kind . '/(?P<id>[1-9][0-9]{0,18})', 'GET,PATCH', $capability, static fn($r) => $r->get_method() === 'GET' ? self::admin_row($kind, Store::row($kind, $r['id'])) : Community::moderate($kind, $r['id'], self::body($r)));
        }
        self::route('/admin/comments/(?P<id>[1-9][0-9]{0,18})/reply', 'POST', 'quickfix_moderate', static fn($r) => Community::moderate('comments', $r['id'], self::body($r)));
        self::route('/admin/campaigns', 'GET,POST', 'quickfix_manage_notifications', static fn($r) => $r->get_method() === 'GET' ? self::collection('campaigns', $r->get_query_params()) : Jobs::campaign(null, self::body($r)));
        self::route('/admin/users/(?P<id>[1-9][0-9]{0,18})/password-reset', 'POST', 'quickfix_manage_users', static function ($r) { $data = self::body($r); Input::only($data, ['confirm']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Confirm password recovery.'); } $user = Store::row('users', $r['id']); $client = Settings::get()['firebase_client']; if (!$user['email'] || empty($client['apiKey']) || $user['status'] === 'deleted') { throw new Fault('reset_unavailable', 'Password recovery unavailable.', 503); } Firebase::http('https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . rawurlencode($client['apiKey']), ['requestType' => 'PASSWORD_RESET', 'email' => $user['email']]); Store::audit('user.password_reset', 'user:' . $r['id']); return ['requested' => true]; });
        self::route('/admin/campaigns/(?P<id>[1-9][0-9]{0,18})', 'GET,PATCH,DELETE', 'quickfix_manage_notifications', static fn($r) => $r->get_method() === 'GET' ? Store::row('campaigns', $r['id']) : ($r->get_method() === 'DELETE' ? Jobs::cancel($r['id'], self::body($r)) : Jobs::campaign($r['id'], self::body($r))));
        self::route('/admin/campaigns/(?P<id>[1-9][0-9]{0,18})/send', 'POST', 'quickfix_manage_notifications', static fn($r) => Jobs::send($r['id'], self::body($r), (string) $r->get_header('idempotency-key')));
        self::route('/admin/settings', 'GET,PATCH', 'quickfix_manage_settings', static function ($r) { $settings = $r->get_method() === 'GET' ? Settings::get() : Settings::save(self::body($r)); $settings['firebase_client'] = (object) $settings['firebase_client']; return $settings; });
        self::route('/admin/audit', 'GET', 'quickfix_view_audit', static fn($r) => self::collection('audit', $r->get_query_params()));
        self::route('/admin/diagnostics', 'GET', 'quickfix_view_audit', static fn() => Jobs::diagnostics());
        self::route('/admin/jobs/run', 'POST', 'quickfix_manage_settings', static function () { Jobs::run(); return Jobs::diagnostics(); });
    }
    public static function dashboard(): array {
        global $wpdb; $counts = [];
        $counts['eligible_website_recipes'] = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . Catalog::joins() . ' WHERE ' . Catalog::website_predicate());
        $counts['app_published_recipes'] = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . Catalog::joins() . ' WHERE ' . Catalog::predicate());
        foreach (['scheduled_additions' => ['mobile_recipes', "state='scheduled'"], 'featured_recipes' => ['home_placements', "section='featured'"], 'pending_comments' => ['comments', "status='pending'"], 'unresolved_reports' => ['reports', "status IN ('open','investigating')"], 'suspended_users' => ['users', "status='community_suspended'"]] as $key => [$table, $condition]) { $counts[$key] = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . Store::table($table) . " WHERE $condition"); }
        return ['counts' => $counts, 'diagnostics' => Jobs::diagnostics(), 'latest_campaign' => $wpdb->get_row('SELECT id,title,status,accepted,permanent_failures FROM ' . Store::table('campaigns') . ' ORDER BY id DESC LIMIT 1', ARRAY_A)];
    }
    public static function admin_recipes(array $params): array {
        global $wpdb; Input::only($params, ['q','state','source_status','category','featured','missing','page','limit']);
        $limit = Input::integer($params['limit'] ?? 25, 1, 100); $page = Input::integer($params['page'] ?? 1, 1, 10000); $where = '1=1';
        if (!empty($params['q'])) { $q = Input::text($params['q'], 1, 100); $where .= $wpdb->prepare(' AND (r.title LIKE %s OR r.id=%s)', '%' . $wpdb->esc_like($q) . '%', $q); }
        if (!empty($params['state'])) { $where .= $wpdb->prepare(' AND COALESCE(m.state,\'excluded\')=%s', Input::choice($params['state'], ['excluded','draft','scheduled','published','withdrawn'])); }
        if (!empty($params['source_status'])) { $where .= $wpdb->prepare(' AND COALESCE(p.post_status,\'missing\')=%s', Input::choice($params['source_status'], ['publish','draft','private','trash','missing'])); }
        if (!empty($params['category'])) { $where .= $wpdb->prepare(' AND EXISTS (SELECT 1 FROM ' . Store::table('recipe_categories') . ' rc WHERE rc.recipe_id=r.id AND rc.category_id=%s)', Input::id($params['category'])); }
        if (!empty($params['featured'])) { $where .= ' AND EXISTS (SELECT 1 FROM ' . Store::table('home_placements') . ' h WHERE h.recipe_id=r.id)'; }
        if (!empty($params['missing'])) { $where .= ' AND (TRIM(r.title)=\'\' OR NOT EXISTS (SELECT 1 FROM ' . Catalog::source_table('recipe_ingredients') . ' i WHERE i.recipe_id=r.id AND TRIM(i.ingredient)<>\'\') OR NOT EXISTS (SELECT 1 FROM ' . Catalog::source_table('recipe_steps') . ' s WHERE s.recipe_id=r.id AND TRIM(s.instruction)<>\'\'))'; }
        $from = Catalog::source_table('recipes') . " r LEFT JOIN {$wpdb->posts} p ON p.ID=r.wp_post_id LEFT JOIN " . Store::table('mobile_recipes') . ' m ON m.recipe_id=r.id';
        $ids_sql = "SELECT r.id id FROM $from WHERE $where";
        if (($params['source_status'] ?? '') === 'missing') {
            $orphan_where = 'r.id IS NULL'; if (!empty($params['q'])) { $orphan_where .= $wpdb->prepare(' AND m.recipe_id=%s', $params['q']); } if (!empty($params['state'])) { $orphan_where .= $wpdb->prepare(' AND m.state=%s', $params['state']); }
            $orphan_from = Store::table('mobile_recipes') . ' m LEFT JOIN ' . Catalog::source_table('recipes') . ' r ON r.id=m.recipe_id';
            $ids_sql .= " UNION SELECT m.recipe_id id FROM $orphan_from WHERE $orphan_where";
        }
        $ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM ($ids_sql) catalog_ids ORDER BY id DESC LIMIT %d OFFSET %d", $limit, ($page - 1) * $limit));
        return ['items' => array_map(static fn($id) => Catalog::inspect((string) $id), $ids), 'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM ($ids_sql) catalog_ids"), 'page' => $page, 'limit' => $limit];
    }
    public static function admin_row(string $kind, array $row): array {
        if ($kind === 'users') { unset($row['_claims']); }
        if (in_array($kind, ['comments','ratings'], true) && !current_user_can('quickfix_manage_users')) { unset($row['email']); }
        foreach (['id','user_id','recipe_id','reporter_id','target_id','parent_comment_id'] as $key) { if (isset($row[$key])) { $row[$key] = (string) $row[$key]; } }
        return $row;
    }
    public static function collection(string $kind, array $params): array {
        global $wpdb; Input::only($params, ['q','status','page','limit','action','target']);
        $limit = Input::integer($params['limit'] ?? 25, 1, 100); $page = Input::integer($params['page'] ?? 1, 1, 10000); $where = '1=1';
        $search = ['app_categories' => ['name','slug'], 'home_placements' => ['recipe_id'], 'comments' => ['body','recipe_id'], 'ratings' => ['recipe_id','user_id'], 'reports' => ['target_id','reason'], 'users' => ['display_name','firebase_uid','email'], 'campaigns' => ['title','target'], 'audit' => ['action','target']][$kind];
        if (!empty($params['q'])) { $q = Input::text($params['q'], 1, 100); $clauses = []; foreach ($search as $column) { $clauses[] = $wpdb->prepare("$column LIKE %s", '%' . $wpdb->esc_like($q) . '%'); } $where .= ' AND (' . implode(' OR ', $clauses) . ')'; }
        if (!empty($params['status']) && in_array($kind, ['comments','ratings','reports','users','campaigns'], true)) { $where .= $wpdb->prepare(' AND status=%s', Input::text($params['status'], 1, 30)); }
        if ($kind === 'audit') { foreach (['action','target'] as $field) { if (!empty($params[$field])) { $where .= $wpdb->prepare(" AND $field=%s", Input::text($params[$field], 1, 100)); } } }
        $table = Store::table($kind); $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d", $limit, ($page - 1) * $limit), ARRAY_A);
        return ['items' => array_map(static fn($row) => self::admin_row($kind, $row), $rows), 'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where"), 'page' => $page, 'limit' => $limit];
    }
    public static function curation(string $table, ?string $id, array $data, string $method): array {
        global $wpdb;
        return Store::transaction(static function () use ($table, $id, $data, $method, $wpdb) {
            if ($id) { $row = Store::row($table, $id, true); Store::revision($row, $data['revision'] ?? null); }
            else { $row = $table === 'app_categories' ? ['name' => '', 'slug' => '', 'description' => '', 'image_ref' => '', 'sort_order' => 0, 'enabled' => 1, 'revision' => 0] : ['recipe_id' => '', 'section' => 'featured', 'position' => 0, 'starts_at' => null, 'ends_at' => null, 'revision' => 0]; }
            if ($method === 'DELETE') {
                Input::only($data, ['revision','confirm']); if (($data['confirm'] ?? false) !== true) { throw new Fault('confirmation_required', 'Confirm removal.'); }
                if ($table === 'app_categories') { Store::query($wpdb->prepare('DELETE FROM ' . Store::table('recipe_categories') . ' WHERE category_id=%s', $id)); }
                Store::query($wpdb->prepare('DELETE FROM ' . Store::table($table) . ' WHERE id=%s', $id)); Store::audit($table . '.removed', $table . ':' . $id); Store::bump(); return ['deleted' => true];
            }
            $allowed = $table === 'app_categories' ? ['revision','name','slug','description','image_ref','sort_order','enabled'] : ['revision','recipe_id','section','position','starts_at','ends_at']; Input::only($data, $allowed);
            foreach ($data as $field => $value) {
                if ($field === 'revision') { continue; }
                if ($field === 'enabled') { $row[$field] = Input::boolean($value); }
                elseif (in_array($field, ['sort_order','position'], true)) { $row[$field] = Input::integer($value, 0, 10000); }
                elseif (in_array($field, ['starts_at','ends_at'], true)) { $row[$field] = Input::date($value); }
                elseif ($field === 'image_ref') { $row[$field] = Input::url($value, true); }
                elseif ($field === 'recipe_id') { $row[$field] = Input::id($value); Catalog::get($row[$field]); }
                elseif ($field === 'section') { $row[$field] = Input::choice($value, ['featured']); }
                elseif ($field === 'slug') { $row[$field] = sanitize_title(Input::text($value, 1, 100)); }
                else { $row[$field] = Input::text($value, $field === 'name' ? 1 : 0, $field === 'description' ? 2000 : 100); }
            }
            if ($table === 'app_categories' && (!$row['name'] || !$row['slug'])) { throw new Fault('category_incomplete', 'Category name and slug are required.'); }
            if ($table === 'home_placements') {
                Catalog::get($row['recipe_id']); if ($row['starts_at'] && $row['ends_at'] && $row['ends_at'] <= $row['starts_at']) { throw new Fault('invalid_schedule', 'End must follow start.'); }
                // Serialize placement edits using a shared row; reject schedules that overlap more than ten placements.
                add_option('quickfix_placements_lock', '1', '', false); $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s FOR UPDATE", 'quickfix_placements_lock'));
                $placements = $wpdb->get_results('SELECT * FROM ' . Store::table('home_placements') . ' WHERE id<>' . (int) ($id ?? 0), ARRAY_A); $placements[] = $row;
                $events = []; foreach ($placements as $placement) { $events[] = [$placement['starts_at'] ?: '0000-01-01 00:00:00', 1]; $events[] = [$placement['ends_at'] ?: '9999-12-31 23:59:59', -1]; } usort($events, static fn($a,$b) => strcmp($a[0],$b[0]) ?: $a[1] <=> $b[1]); $active = 0; foreach ($events as $event) { $active += $event[1]; if ($active > 10) { throw new Fault('featured_limit', 'At most ten featured placements may overlap.'); } }
            }
            $row['revision']++; if ($id) { Store::update($table, $row, ['id' => $id]); } else { $id = (string) Store::insert($table, $row); }
            Store::audit($table . '.saved', $table . ':' . $id, ['revision' => $row['revision']]); Store::bump(); return Store::row($table, $id);
        });
    }
}
