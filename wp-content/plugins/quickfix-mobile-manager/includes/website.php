<?php
namespace QuickFix;
defined('ABSPATH') || exit;
final class Website {
    private static function assets(): void {
        wp_enqueue_style('quickfix-community', plugins_url('assets/website.css', QUICKFIX_MANAGER_FILE), [], '1.0.0');
        wp_enqueue_script('quickfix-community', plugins_url('assets/website.js', QUICKFIX_MANAGER_FILE), [], '1.0.0', true);
        wp_localize_script('quickfix-community', 'QuickFixWebsite', ['api' => rest_url('quickfix/v1/')]);
    }
    public static function render($attributes = []): string {
        global $wpdb; $attributes = shortcode_atts(['recipe_id' => ''], $attributes);
        $id = $attributes['recipe_id'] ?: $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . Catalog::source_table('recipes') . ' WHERE wp_post_id=%d ORDER BY id LIMIT 1', get_the_ID()));
        if (!$id) { return ''; }
        try { Catalog::get((string) $id, 'website'); } catch (Fault $e) { return ''; }
        self::assets(); return '<section class="quickfix-community" data-recipe="' . esc_attr($id) . '" aria-label="Recipe community"><h2>Ratings and comments</h2><div class="qf-message" role="status" aria-live="polite"></div><div class="qf-content"><p>Loading community…</p></div></section>';
    }
    public static function deletion(): string {
        self::assets(); return '<section class="quickfix-community" data-deletion="true" aria-label="Delete your QuickFix account"><h2>Delete your account</h2><p>Sign in and verify your identity to request deletion. This removes your profile, comments, ratings, reports, blocks and linked device associations. Device-local saved recipes are separate. Completion may take up to seven days; minimal deletion records prevent restored backups from recreating your account.</p><div class="qf-message" role="status" aria-live="polite"></div><div class="qf-content"><p>Loading secure sign-in…</p></div></section>';
    }
}
