<?php
/**
 * Plugin Name: QuickFix Mobile Manager
 * Description: Curated recipe API, shared community, Firebase identity and Mobile App administration.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: QuickFix With Bindu
 * License: GPL-2.0-or-later
 */
namespace QuickFix;
defined('ABSPATH') || exit;
define('QUICKFIX_MANAGER_FILE', __FILE__);
define('QUICKFIX_MANAGER_DIR', __DIR__);
spl_autoload_register(static function ($class) {
    $prefix = 'QuickFix\\Vendor\\Firebase\\JWT\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/vendor/php-jwt/' . substr($class, strlen($prefix)) . '.php';
        if (is_file($file)) { require_once $file; }
    }
});
foreach (['support', 'schema', 'catalog', 'firebase', 'community', 'jobs', 'api', 'admin', 'website'] as $module) {
    require_once __DIR__ . '/includes/' . $module . '.php';
}
register_activation_hook(__FILE__, [Schema::class, 'activate']);
register_deactivation_hook(__FILE__, static function () { wp_clear_scheduled_hook('quickfix_worker'); });
add_action('plugins_loaded', static function () {
    if (get_option('quickfix_schema_version') !== Schema::VERSION) { Schema::activate(); }
    add_action('rest_api_init', [API::class, 'register']);
    add_filter('rest_post_dispatch', [API::class, 'normalize_errors'], 10, 3);
    add_action('admin_menu', [Admin::class, 'menu']);
    add_action('admin_enqueue_scripts', [Admin::class, 'assets']);
    add_action('quickfix_worker', [Jobs::class, 'run']);
    add_shortcode('quickfix_community', [Website::class, 'render']);
    add_shortcode('quickfix_delete_account', [Website::class, 'deletion']);
    add_action('transition_post_status', [Catalog::class, 'source_transition'], 10, 3);
    add_action('before_delete_post', [Catalog::class, 'source_deleted']);
    add_action('save_post_recipes', static function () { Store::bump(); });
    if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::add_command('quickfix jobs run', static function () { Jobs::run(); \WP_CLI::success('Bounded queue run finished.'); });
        \WP_CLI::add_command('quickfix diagnostics', static function () { \WP_CLI::line(wp_json_encode(Jobs::diagnostics(), JSON_PRETTY_PRINT)); });
        \WP_CLI::add_command('quickfix tombstones export', [Jobs::class, 'export_tombstones']);
        \WP_CLI::add_command('quickfix tombstones replay', [Jobs::class, 'replay_tombstones']);
    }
});
add_filter('cron_schedules', static function ($schedules) {
    $schedules['quickfix_minute'] = ['interval' => 60, 'display' => 'QuickFix every minute'];
    return $schedules;
});
