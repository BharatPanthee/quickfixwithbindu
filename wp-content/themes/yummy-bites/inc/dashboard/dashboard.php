<?php 
/**
 * New React Dashboard page
 * 
 * @package Yummy Bites
 */

/**
 * Init Admin Menu.
 *
 * @return void
 */
function yummy_bites_dashboard_menu() { 
    add_theme_page(
		YUMMY_BITES_THEME_NAME,
		YUMMY_BITES_THEME_NAME,
		'manage_options',
		'yummy-bites-dashboard',
		'yummy_bites_dashboard_page'
	);
}
add_action( 'admin_menu', 'yummy_bites_dashboard_menu' );

/**
 * Callback function for React Dashboard Admin Page.
 * 
 * @return void
 */
function yummy_bites_dashboard_page() { ?>
    <div id="cw-dashboard" class="cw-dashboard"></div>
    <?php
}

/**
 * Enqueue scripts and styles for admin scripts.
 * 
 * @return void
 */
function yummy_bites_dashboard_scripts() {

    $admin_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : null;

    if( $admin_page === 'yummy-bites-dashboard' ){
        $dependencies_file_path = get_template_directory() . '/build/dashboard.asset.php';
        if ( file_exists( $dependencies_file_path ) ) {
            $dashboard_assets  = require $dependencies_file_path;
            $js_dependencies   = ( ! empty( $dashboard_assets['dependencies'] ) ) ? $dashboard_assets['dependencies'] : [];
            $version           = ( ! empty( $dashboard_assets['version'] ) ) ? $dashboard_assets['version'] : '2.0.0';
            $js_dependencies[] = 'updates';

            wp_enqueue_script(
                'yummy-bites-react-dashboard',
                get_template_directory_uri() . '/build/dashboard.js',
                $js_dependencies,
                $version,
                true
            );

            // Add Translation support for Dashboard 
            wp_set_script_translations( 'yummy-bites-react-dashboard', 'yummy-bites' );
            
            $arrayargs = [
                'ajax_url'           => esc_url( admin_url( 'admin-ajax.php' ) ),
                'admin_url'          => esc_url( admin_url( ) ),
                'blog_name'          => YUMMY_BITES_THEME_NAME,
                'theme_version'      => YUMMY_BITES_THEME_VERSION,
                'nonce'              => wp_create_nonce( 'yummy_bites_dashboard_nonce' ),
                'inactivePlugins'    => yummy_bites_get_inactive_plugins(),
                'activePlugins'      => yummy_bites_get_active_plugins(),
                'pricing'            => esc_url('https://wpdelicious.com/pricing/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=upgrade_to_pro'),
				'keywords'           => esc_url('https://wpdelicious.com/keyword-research-for-food-bloggers/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=recipe_keywords'),
                'review'             => esc_url('https://wordpress.org/support/theme/yummy-bites/reviews/'),
                'docmentation'       => esc_url('https://wpdelicious.com/docs-category/yummy-bites/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=docs'),
                'support'            => esc_url('https://wpdelicious.com/support-ticket/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=support'),
                'bundle_pricing'     => esc_url('https://wpdelicious.com/bundle-pricing/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=upgrade_to_pro'),
                'videotutorial'      => esc_url('https://www.youtube.com/@wpdelicious/'),
                'get_pro'            => esc_url('https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=upgrade_to_pro'),
                'website'            => esc_url('https://wpdelicious.com/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=website_visit'),
                'customizer_url'     => esc_url( admin_url( 'customize.php' ) ),
                'custom_logo'        => esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ),
                'colors'             => esc_url( admin_url( 'customize.php?autofocus[section]=colors' ) ),
                'layout'             => esc_url( admin_url( 'customize.php?autofocus[panel]=layout_settings' ) ),
                'front'              => esc_url( admin_url( 'customize.php?autofocus[panel]=front_page_settings' ) ),
                'general'            => esc_url( admin_url( 'customize.php?autofocus[panel]=general_settings' ) ),
                'instagram'          => esc_url( admin_url( 'customize.php?autofocus[section]=instagram_settings' ) ),
                'footer'             => esc_url( admin_url( 'customize.php?autofocus[section]=footer_settings' ) ),
                'typography'             => esc_url( admin_url( 'customize.php?autofocus[section]=typography_settings' ) ),
                'social'             => esc_url( admin_url( 'customize.php?autofocus[section]=social_network_section' ) ),
                'frontpage'             => esc_url( admin_url( 'customize.php?autofocus[panel]=frontpage_settings' ) ),
            ];

            wp_localize_script( 'yummy-bites-react-dashboard','cw_dashboard',$arrayargs );
        }
        wp_enqueue_style( 'yummy-bites-react-dashboard', get_template_directory_uri() . '/build/dashboard.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'yummy_bites_dashboard_scripts' );

/**
 * Get the inactive plugins.
 *
 * @return array
 */
function yummy_bites_get_inactive_plugins() {
    if (!current_user_can('install_plugins') && !current_user_can('activate_plugins')) {
        return new \WP_Error( 'rest_forbidden', esc_html__( 'Sorry, you are not allowed to do that.', 'yummy-bites' ), array( 'status' => 403 ) );
    }

    // Get the list of all installed plugins
    $all_plugins = get_plugins();

    // Fetch the row from the options table containing active plugins
    $active_plugins_option = get_option('active_plugins');

    // Unserialize the active plugins data
    $active_plugins = is_array($active_plugins_option) ? $active_plugins_option : [];

    // Get the slugs of active plugins
    $active_plugin_slugs = array_map(function($plugin) {
        return plugin_basename($plugin);
    }, $active_plugins);

    // Get the slugs of inactive plugins
    $inactive_plugin_slugs = array_diff(array_keys($all_plugins), $active_plugin_slugs);

    // Get the details of inactive plugins
    $inactive_plugins = array_intersect_key($all_plugins, array_flip($inactive_plugin_slugs));

    // Initialize an empty array to hold the modified inactive plugins
    $modified_inactive_plugins = array();
    // Iterate over each inactive plugin
    foreach ($inactive_plugins as $key => $plugin_data) {
        $extract = explode( '/', $key );
        // Extract the necessary information
        $name = $plugin_data['Name'];
        $slug = $extract[0];

        // Add the plugin to the modified array
        $modified_inactive_plugins[] = array(
            'name' => esc_html($name),
            'slug' => sanitize_title($slug),
            'url'  => yummy_bites_get_activation_url($slug)
        );
    }

    // Return the modified array
    return $modified_inactive_plugins;
}

/**
 * Get the activation URL for a plugin.
 *
 * @param string $plugin_slug The plugin slug.
 *
 * @return string|bool The activation URL if the plugin exists, false otherwise.
 */
function yummy_bites_get_activation_url($plugin_slug) {
    if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
        $plugins = get_plugins( '/' . $plugin_slug );
        if ( ! empty( $plugins ) ) {
            $keys        = array_keys( $plugins );
            $plugin_file = $plugin_slug . '/' . $keys[0];
            $url         = wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'activate',
                        'plugin' => $plugin_file,
                    ),
                    admin_url( 'plugins.php' )
                ),
                'activate-plugin_' . $plugin_file
            );
            return $url;
        }
    }
    return false;
}

/**
 * Get the active plugins.
 *
 * @return array
 */
function yummy_bites_get_active_plugins() {
    $active_plugins = get_plugins();
    $plugins = array();

    foreach ($active_plugins as $key => $plugin) {
        if ( is_plugin_active( $key ) ) {
            $extract = explode( '/', $key );
            $path    = ABSPATH . 'wp-content/plugins/' . $key;
            $plugin_data = get_plugin_data($path);
            $plugins[] = array(
                'name'    => esc_html($plugin_data['Name']),
                'slug'    => sanitize_title($extract[0]),
                'version' => esc_html($plugin_data['Version']),
            );
        }
    }

    return $plugins;
}