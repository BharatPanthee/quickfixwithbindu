<?php
namespace QuickFix;
use QuickFix\Vendor\Firebase\JWT\JWT;
use QuickFix\Vendor\Firebase\JWT\Key;
use QuickFix\Vendor\Firebase\JWT\JWK;
defined('ABSPATH') || exit;

final class Firebase {
    public static function credentials(): array {
        $path = realpath(Store::secret('QUICKFIX_FIREBASE_CREDENTIALS'));
        $root = strtolower(str_replace('\\', '/', realpath(ABSPATH)) . '/');
        if (!$path || !is_file($path) || str_starts_with(strtolower(str_replace('\\', '/', $path)), $root) || !is_readable($path) || filesize($path) > 65536) { throw new Fault('firebase_unconfigured', 'Identity services are not configured.', 503); }
        $credentials = json_decode(file_get_contents($path), true);
        if (!is_array($credentials) || ($credentials['type'] ?? '') !== 'service_account' || empty($credentials['private_key']) || empty($credentials['client_email']) || ($credentials['project_id'] ?? '') !== Settings::get()['firebase_project_id']) { throw new Fault('firebase_unconfigured', 'Identity services are not configured.', 503); }
        return $credentials;
    }
    public static function http(string $url, array $body = [], string $access = '', string $method = 'POST'): array {
        $args = ['method' => $method, 'timeout' => 12, 'redirection' => 0, 'headers' => ['Content-Type' => 'application/json']];
        if ($body) { $args['body'] = wp_json_encode($body); }
        if ($access) { $args['headers']['Authorization'] = 'Bearer ' . $access; }
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) { throw new Fault('firebase_unavailable', 'Identity or messaging service temporarily unavailable.', 503); }
        $status = wp_remote_retrieve_response_code($response); $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($status < 200 || $status >= 300) {
            $error = $data['error']['status'] ?? '';
            if ($status === 400 && str_contains((string) ($data['error']['message'] ?? ''), 'USER_NOT_FOUND')) { throw new Fault('firebase_not_found', 'Identity unavailable.', 404); }
            if ($status === 400 && isset($data['error']['details'])) { foreach ($data['error']['details'] as $detail) { if (($detail['errorCode'] ?? '') === 'UNREGISTERED') { throw new Fault('invalid_device_token', 'Device registration expired.', 410); } } }
            throw new Fault('firebase_unavailable', 'Identity or messaging service temporarily unavailable.', 503);
        }
        if (!is_array($data)) { throw new Fault('firebase_unavailable', 'Identity service returned an invalid response.', 503); }
        return $data;
    }
    public static function access(): string {
        $credentials = self::credentials(); $cache = 'qf_access_' . hash('sha256', $credentials['client_email'] . ($credentials['private_key_id'] ?? ''));
        $cached = get_transient($cache); if (is_string($cached)) { return $cached; }
        $jwt = JWT::encode(['iss' => $credentials['client_email'], 'scope' => 'https://www.googleapis.com/auth/firebase.messaging https://www.googleapis.com/auth/identitytoolkit', 'aud' => 'https://oauth2.googleapis.com/token', 'iat' => time(), 'exp' => time() + 3600], $credentials['private_key'], 'RS256');
        $response = wp_remote_post('https://oauth2.googleapis.com/token', ['timeout' => 12, 'redirection' => 0, 'body' => ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { throw new Fault('firebase_unavailable', 'Identity services temporarily unavailable.', 503); }
        $data = json_decode(wp_remote_retrieve_body($response), true); if (empty($data['access_token'])) { throw new Fault('firebase_unavailable', 'Identity services temporarily unavailable.', 503); }
        set_transient($cache, $data['access_token'], max(1, min(3500, (int) ($data['expires_in'] ?? 3600) - 60))); return $data['access_token'];
    }
    public static function signing_keys(bool $app_check = false, bool $refresh = false): array {
        $cache = $app_check ? 'quickfix_appcheck_jwks' : 'quickfix_firebase_certs';
        if (!$refresh) { $cached = get_transient($cache); if (is_array($cached)) { return $cached; } }
        $url = $app_check ? 'https://firebaseappcheck.googleapis.com/v1/jwks' : 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
        $response = wp_remote_get($url, ['timeout' => 10, 'redirection' => 0]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { throw new Fault('firebase_unavailable', 'Unable to verify the session right now.', 503); }
        $keys = json_decode(wp_remote_retrieve_body($response), true); if (!is_array($keys) || !$keys) { throw new Fault('firebase_unavailable', 'Unable to verify the session right now.', 503); }
        $control = wp_remote_retrieve_header($response, 'cache-control'); preg_match('/max-age=(\d+)/', $control, $matches);
        set_transient($cache, $keys, max(1, min(21600, (int) ($matches[1] ?? 300)))); return $keys;
    }
    public static function decode(string $token, bool $app_check = false): array {
        if (strlen($token) > 16384 || substr_count($token, '.') !== 2) { throw new Fault('invalid_session', 'Invalid session.', 401); }
        try {
            $header = json_decode(JWT::urlsafeB64Decode(explode('.', $token)[0]), true);
            if (!is_array($header) || ($header['alg'] ?? '') !== 'RS256' || !is_string($header['kid'] ?? null)) { throw new \Exception(); }
            $keys = self::signing_keys($app_check); $kid = $header['kid'];
            if ($app_check) { $parsed = JWK::parseKeySet($keys, 'RS256'); if (!isset($parsed[$kid])) { $parsed = JWK::parseKeySet(self::signing_keys(true, true), 'RS256'); } $key = $parsed[$kid] ?? null; }
            else { if (!isset($keys[$kid])) { $keys = self::signing_keys(false, true); } $key = isset($keys[$kid]) ? new Key($keys[$kid], 'RS256') : null; }
            if (!$key) { throw new \Exception(); }
            $claims = (array) JWT::decode($token, $key);
            if (!isset($claims['exp'], $claims['iat'], $claims['sub']) || $claims['iat'] > time() || $claims['exp'] <= time() || !is_string($claims['sub']) || strlen($claims['sub']) < 1 || strlen($claims['sub']) > 128) { throw new \Exception(); }
            return $claims;
        } catch (Fault $e) { throw $e; } catch (\Throwable $e) { throw new Fault('invalid_session', 'Invalid or expired session.', 401); }
    }
    public static function verify(string $token): array {
        $project = Settings::get()['firebase_project_id']; if (!$project) { throw new Fault('firebase_unconfigured', 'Sign-in is not configured.', 503); }
        $claims = self::decode($token);
        if (($claims['aud'] ?? '') !== $project || ($claims['iss'] ?? '') !== 'https://securetoken.google.com/' . $project || !isset($claims['auth_time']) || !is_int($claims['auth_time']) || $claims['auth_time'] > time()) { throw new Fault('invalid_session', 'Invalid session.', 401); }
        // Supported Admin REST lookup checks disabled identities and token revocation on every protected request.
        $lookup = self::http('https://identitytoolkit.googleapis.com/v1/projects/' . rawurlencode($project) . '/accounts:lookup', ['localId' => [$claims['sub']]], self::access());
        $user = $lookup['users'][0] ?? null;
        if (!$user || ($user['localId'] ?? '') !== $claims['sub'] || !empty($user['disabled']) || (int) ($user['validSince'] ?? 0) > $claims['auth_time']) { throw new Fault('invalid_session', 'This session is no longer valid. Sign in again.', 401); }
        $claims['email_verified'] = !empty($user['emailVerified']) && !empty($claims['email_verified']);
        return $claims;
    }
    public static function attest(string $token, string $app_id): void {
        $claims = self::decode($token, true); $number = Settings::get()['firebase_project_number'];
        $allowed = array_filter(explode(',', Store::secret('QUICKFIX_APP_CHECK_APP_IDS')));
        $aud = $claims['aud'] ?? []; if (!is_array($aud)) { $aud = [$aud]; }
        if (!$number || ($claims['iss'] ?? '') !== 'https://firebaseappcheck.googleapis.com/' . $number || !in_array('projects/' . $number, $aud, true) || $claims['sub'] !== $app_id || !in_array($app_id, $allowed, true)) { throw new Fault('invalid_attestation', 'App verification failed.', 401); }
    }
    public static function delete_identity(string $uid): void {
        try { self::http('https://identitytoolkit.googleapis.com/v1/projects/' . rawurlencode(Settings::get()['firebase_project_id']) . '/accounts:delete', ['localId' => $uid], self::access()); }
        catch (Fault $e) { if ($e->slug !== 'firebase_not_found') { throw $e; } }
    }
    public static function identity_hash(string $uid): string {
        $key = Store::secret('QUICKFIX_IDENTITY_HASH_KEY'); if (strlen($key) < 32) { throw new Fault('deletion_unconfigured', 'Account deletion requires a stable server identity key.', 503); }
        return hash_hmac('sha256', $uid, $key);
    }
    public static function encrypt(string $plaintext): string {
        $key = Store::secret('QUICKFIX_DEVICE_KEY'); if (strlen($key) < 32) { throw new Fault('devices_unconfigured', 'Device registrations are not configured.', 503); }
        $iv = random_bytes(12); $tag = ''; $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) { throw new Fault('encryption_failed', 'Device registration unavailable.', 503); }
        return base64_encode($iv . $tag . $cipher);
    }
    public static function decrypt(string $cipher): string {
        $key = Store::secret('QUICKFIX_DEVICE_KEY'); $raw = base64_decode($cipher, true);
        if (strlen($key) < 32 || $raw === false || strlen($raw) < 29) { throw new Fault('devices_unconfigured', 'Device registration unavailable.', 503); }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', hash('sha256', $key, true), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($plain === false) { throw new Fault('device_key_changed', 'Device registration must be renewed.', 503); } return $plain;
    }
}

final class Identity {
    public static function authenticate(\WP_REST_Request $request): array {
        global $wpdb;
        if (!is_ssl() && !in_array(wp_get_environment_type(), ['local','development'], true) && !(defined('WP_CLI') && WP_CLI)) { throw new Fault('https_required', 'Use HTTPS for authenticated requests.', 403); }
        // Internal request attributes are not read from client parameters; use a per-object map.
        static $verified; $verified ??= new \WeakMap(); if (isset($verified[$request])) { return $verified[$request]; }
        $auth = $request->get_header('authorization');
        if (!preg_match('/^Bearer ([^\s]+)$/D', (string) $auth, $match)) { throw new Fault('session_required', 'Sign in to continue.', 401); }
        $claims = Firebase::verify($match[1]); $uid = $claims['sub'];
        if (Store::secret('QUICKFIX_IDENTITY_HASH_KEY')) {
            $hash = Firebase::identity_hash($uid);
            $tombstone = $wpdb->get_var($wpdb->prepare('SELECT replay_state FROM ' . Store::table('deletion_tombstones') . ' WHERE identity_hash=%s', $hash));
            if ($tombstone && !($request->get_route() === '/quickfix/v1/me/deletion' && $tombstone === 'pending')) { throw new Fault('account_deleted', 'Account unavailable.', 403); }
        }
        $table = Store::table('users');
        $provider = (array) ($claims['firebase'] ?? []); $providers = isset($provider['identities']) ? array_keys((array) $provider['identities']) : [];
        $name = mb_substr(Input::plain($claims['name'] ?? 'Recipe cook'), 0, 50); if (mb_strlen($name) < 2) { $name = 'Recipe cook'; }
        $email = !empty($claims['email']) && is_email($claims['email']) ? sanitize_email($claims['email']) : null;
        Store::query($wpdb->prepare("INSERT INTO $table (firebase_uid,display_name,email,providers,verified,first_seen,last_seen) VALUES (%s,%s,%s,%s,%d,%s,%s) ON DUPLICATE KEY UPDATE email=IF(status IN ('active','community_suspended'),VALUES(email),email),providers=IF(status IN ('active','community_suspended'),VALUES(providers),providers),verified=IF(status IN ('active','community_suspended'),VALUES(verified),verified),last_seen=VALUES(last_seen)", $uid, $name, $email, wp_json_encode($providers), (int) ($claims['email_verified'] ?? false), Store::now(), Store::now()));
        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE firebase_uid=%s", $uid), ARRAY_A);
        if (!$user || $user['status'] === 'deleted') { throw new Fault('account_unavailable', 'Account unavailable.', 403); }
        $user['_claims'] = $claims; $verified[$request] = $user; return $user;
    }
    public static function active(array $user, bool $verified = true): void {
        global $wpdb;
        $current = $wpdb->get_row($wpdb->prepare('SELECT status,verified FROM ' . Store::table('users') . ' WHERE id=%s', $user['id']), ARRAY_A);
        if (!$current || $current['status'] !== 'active') { throw new Fault('community_unavailable', 'Community participation is unavailable for this account.', 403); }
        if ($verified && !$current['verified']) { throw new Fault('email_unverified', 'Verify your email before participating.', 403); }
        if (Settings::get()['maintenance_writes']) { throw new Fault('maintenance', 'Community changes are temporarily unavailable.', 503); }
    }
    public static function profile(array $user): array { return ['id' => (string) $user['id'], 'display_name' => $user['display_name'], 'email' => $user['email'], 'verified' => (bool) $user['verified'], 'providers' => json_decode($user['providers'], true), 'status' => $user['status'], 'revision' => (int) $user['revision']]; }
}

final class Apple {
    public static function credentials(): array {
        $path = realpath(Store::secret('QUICKFIX_APPLE_CREDENTIALS'));
        $root = strtolower(str_replace('\\', '/', realpath(ABSPATH)) . '/');
        if (!$path || !is_file($path) || !is_readable($path) || str_starts_with(strtolower(str_replace('\\', '/', $path)), $root) || filesize($path) > 65536) { throw new Fault('apple_unconfigured', 'Apple account services are not configured.', 503); }
        $credentials = json_decode(file_get_contents($path), true);
        foreach (['team_id','key_id','client_id','private_key'] as $field) { if (!is_string($credentials[$field] ?? null) || !$credentials[$field]) { throw new Fault('apple_unconfigured', 'Apple account services are not configured.', 503); } }
        return $credentials;
    }
    public static function request(string $action, array $fields): array {
        $credentials = self::credentials();
        $secret = JWT::encode(['iss' => $credentials['team_id'], 'iat' => time(), 'exp' => time() + 600, 'aud' => 'https://appleid.apple.com', 'sub' => $credentials['client_id']], $credentials['private_key'], 'ES256', $credentials['key_id']);
        $response = wp_remote_post('https://appleid.apple.com/auth/' . $action, ['timeout' => 12, 'redirection' => 0, 'body' => array_merge(['client_id' => $credentials['client_id'], 'client_secret' => $secret], $fields)]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { throw new Fault('apple_unavailable', 'Apple account services are temporarily unavailable.', 503); }
        return json_decode(wp_remote_retrieve_body($response), true) ?: [];
    }
    public static function associate(array $user, string $code): void {
        Input::text($code, 1, 4096); $claims = $user['_claims']; $firebase = (array) ($claims['firebase'] ?? []); $identities = (array) ($firebase['identities'] ?? []);
        if (empty($identities['apple.com'])) { throw new Fault('apple_identity_required', 'Sign in with your linked Apple identity.', 403); }
        $response = self::request('token', ['grant_type' => 'authorization_code', 'code' => $code]);
        try {
            $keys = get_transient('quickfix_apple_jwks'); if (!$keys) { $keys = Firebase::http('https://appleid.apple.com/auth/keys', [], '', 'GET'); set_transient('quickfix_apple_jwks', $keys, 3600); }
            $verified = (array) JWT::decode($response['id_token'] ?? '', JWK::parseKeySet($keys, 'RS256'));
            if (($verified['iss'] ?? '') !== 'https://appleid.apple.com' || ($verified['aud'] ?? '') !== self::credentials()['client_id'] || !in_array($verified['sub'] ?? '', (array) $identities['apple.com'], true) || empty($response['refresh_token'])) { throw new \Exception(); }
        } catch (\Throwable $e) { throw new Fault('apple_identity_mismatch', 'Apple credential could not be linked to this identity.', 401); }
        Store::transaction(static function () use ($user, $response) {
            Community::lock_user($user, false);
            Store::query('DELETE FROM ' . Store::table('provider_tokens') . ' WHERE user_id=' . (int) $user['id'] . " AND provider='apple.com'");
            Store::insert('provider_tokens', ['user_id' => $user['id'], 'provider' => 'apple.com', 'token_cipher' => Firebase::encrypt($response['refresh_token']), 'updated_at' => Store::now()]);
        });
    }
    public static function revoke(string $user_id, string $uid): void {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT token_cipher,revoked FROM ' . Store::table('provider_tokens') . " WHERE user_id=%s AND provider='apple.com'", $user_id), ARRAY_A);
        if ($row && $row['revoked']) { return; }
        if (!$row || !$row['token_cipher']) { if (apply_filters('quickfix_revoke_provider_tokens', false, $uid, 'apple.com') !== true) { throw new Fault('apple_revocation_required', 'A fresh Apple authorization code is required to revoke this account.', 503); } return; }
        self::request('revoke', ['token' => Firebase::decrypt($row['token_cipher']), 'token_type_hint' => 'refresh_token']);
        Store::update('provider_tokens', ['revoked' => 1, 'token_cipher' => '', 'updated_at' => Store::now()], ['user_id' => $user_id, 'provider' => 'apple.com']);
    }
}
