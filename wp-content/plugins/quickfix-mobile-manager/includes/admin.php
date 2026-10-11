<?php
namespace QuickFix;
defined('ABSPATH') || exit;
final class Admin {
    public const PAGES = ['dashboard' => ['Dashboard','quickfix_view_audit'], 'recipes' => ['Recipes','quickfix_manage_catalog'], 'categories' => ['Categories','quickfix_manage_catalog'], 'home-placements' => ['Home Layout','quickfix_manage_catalog'], 'comments' => ['Comments','quickfix_moderate'], 'ratings' => ['Ratings','quickfix_moderate'], 'reports' => ['Reports','quickfix_moderate'], 'users' => ['App Users','quickfix_manage_users'], 'campaigns' => ['Notifications','quickfix_manage_notifications'], 'settings' => ['Settings','quickfix_manage_settings'], 'audit' => ['Activity Log','quickfix_view_audit']];
    public static function menu(): void {
        $landing = null; foreach (self::PAGES as $resource => [$label,$cap]) { if (current_user_can($cap)) { $landing = $resource; break; } } if (!$landing) { return; }
        add_menu_page('Mobile App', 'Mobile App', self::PAGES[$landing][1], 'quickfix-' . $landing, [self::class,'render'], 'dashicons-smartphone', 30);
        foreach (self::PAGES as $resource => [$label,$cap]) { add_submenu_page('quickfix-' . $landing, $label, $label, $cap, 'quickfix-' . $resource, [self::class,'render']); }
    }
    public static function assets(string $hook): void {
        if (!str_contains($hook, 'quickfix-')) { return; }
        wp_enqueue_style('quickfix-admin', plugins_url('assets/admin.css', QUICKFIX_MANAGER_FILE), [], '1.0.0');
        wp_enqueue_script('quickfix-admin', plugins_url('assets/admin.js', QUICKFIX_MANAGER_FILE), [], '1.0.0', true);
        $caps = []; foreach (Schema::CAPS as $cap) { $caps[$cap] = current_user_can($cap); }
        wp_localize_script('quickfix-admin', 'QuickFixAdmin', ['api' => rest_url('quickfix/v1/admin/'), 'nonce' => wp_create_nonce('wp_rest'), 'timezone' => wp_timezone_string(), 'capabilities' => $caps]);
    }
    public static function render(): void {
        $resource = str_replace('quickfix-', '', sanitize_key($_GET['page'] ?? '')); $page = self::PAGES[$resource] ?? null;
        if (!$page || !current_user_can($page[1])) { wp_die(esc_html__('You do not have permission to view this page.')); }
        echo '<div class="wrap quickfix-admin"><h1>' . esc_html($page[0]) . '</h1><p>QuickFix With Bindu · Mobile App Manager</p><div id="quickfix-feedback" role="status" aria-live="polite"></div><div id="quickfix-manager" data-resource="' . esc_attr($resource) . '" aria-busy="true"><p>Loading…</p></div><noscript><p>Enable JavaScript to use the administrator forms.</p></noscript></div>';
    }
}
