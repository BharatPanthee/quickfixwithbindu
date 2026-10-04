<?php
/**
 * Theme Customizer
 *
 * @package Yummy Bites
 * Requiring customizer panels & sections
*/
$yummy_bites_sections     = array(  'info', 'layout', 'site', 'appearance', 'title', 'colors', 'social-network', 'seo', 'instagram', 'blogpage', 'singlepage','archive', 'singlepost', 'additional', 'builder' );
$yummy_bites_panels       = array( 'home','general', 'header', 'footer' );
$yummy_bites_sub_sections = array(
    'home'    => array( 'banner', 'newsletter', 'blog', 'about', 'category', 'featured-area', 'recipe-index', 'search', 'recipe-category', 'cta', 'sort', 'featured-on' ),
    'general' => array( 'container', 'sidebar', 'scroll-to-top', 'button'),
    'header'  => array( 'general-header', 'social-media' ),
    'footer'  => array( 'footer' ),
);
foreach( $yummy_bites_panels as $p ){
    require get_template_directory() . '/inc/customizer/' . $p . '.php';
}

foreach( $yummy_bites_sub_sections as $key => $sections ){
    foreach( $sections as $section ){        
        require get_template_directory() . '/inc/customizer/panels/' . $key . '/' . $section . '.php';
    }
}

foreach( $yummy_bites_sections as $section ){
    require get_template_directory() . '/inc/customizer/sections/' . $section . '.php';
}

/**
 * Sanitization Functions
*/
require get_template_directory() . '/inc/customizer/sanitization-functions.php';

/**
 * Active Callbacks
*/
require get_template_directory() . '/inc/customizer/active-callback.php';

if( ! function_exists( 'yummy_bites_customize_preview_js' ) ) :
/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function yummy_bites_customize_preview_js() {
	// Use minified libraries if SCRIPT_DEBUG is false
    $build    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'yummy-bites-customizer', get_template_directory_uri() . '/inc/js' . $build . '/customizer' . $suffix .'.js', array( 'customize-preview' ), YUMMY_BITES_THEME_VERSION, true );

	wp_localize_script(
		'yummy-bites-customizer',
		'yummy_bites_view_port',
		array(
			'mobile'               => yummy_bites_get_media_query( 'mobile' ),
			'tablet'               => yummy_bites_get_media_query( 'tablet' ),
			'desktop'              => yummy_bites_get_media_query( 'desktop' ),
			'googlefonts'          => apply_filters( 'yummy_bites_typography_customize_list', yummy_bites_get_all_google_fonts() ),
			'systemfonts'          => apply_filters( 'yummy_bites_typography_system_stack', '-apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"' ),
			'breadcrumb_sep_one'   => yummy_bites_breadcrumb_icons_list('one'),
			'breadcrumb_sep_two'   => yummy_bites_breadcrumb_icons_list('two'),
			'breadcrumb_sep_three' => yummy_bites_breadcrumb_icons_list('three'),
		)
	);
}
endif;
add_action( 'customize_preview_init', 'yummy_bites_customize_preview_js' );

if( ! function_exists( 'yummy_bites_get_media_query' ) ) :
/**
 * Get the requested media query.
 *
 * @param string $name Name of the media query.
 */
function yummy_bites_get_media_query( $name ) {

	// If the theme function doesn't exist, build our own queries.
	$desktop     = apply_filters( 'yummy_bites_desktop_media_query', '(min-width:1024px)' );
	$tablet      = apply_filters( 'yummy_bites_tablet_media_query', '(min-width: 720px) and (max-width: 1024px)' );
	$mobile      = apply_filters( 'yummy_bites_mobile_media_query', '(max-width:719px)' );

	$queries = apply_filters(
		'yummy_bites_media_queries',
		array(
			'desktop'     => $desktop,
			'tablet'      => $tablet,
			'mobile'      => $mobile,
		)
	);

	return $queries[ $name ];
}
endif;

if( ! function_exists( 'yummy_bites_customize_script' ) ) :

function yummy_bites_customize_script(){

	// Use minified libraries if SCRIPT_DEBUG is false
    $build    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_enqueue_style( 'yummy-bites-customize', get_template_directory_uri() . '/inc/css/customize.css', array(), YUMMY_BITES_THEME_VERSION );
	wp_enqueue_script( 'yummy-bites-customize', get_template_directory_uri() . '/inc/js' . $build  . '/customize' . $suffix .'.js', array( 'jquery', 'customize-controls' ), YUMMY_BITES_THEME_VERSION, true );

	wp_localize_script( 'yummy-bites-customize', 'yummy_bites_cdata',
		array(
			'nonce'        => wp_create_nonce( 'yummy-bites-local-fonts-flush' ),
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'flushit'      => __( 'Successfully Flushed!','yummy-bites' ),
			'proActivated' => yummy_bites_pro_is_activated(),
		)
	);

	wp_localize_script( 'yummy-bites-typography-customizer', 'yummy_bites_customize',
		array(
			'nonce' => wp_create_nonce( 'yummy_bites_customize_nonce' )
		)
	);

	wp_localize_script(
		'yummy-bites-typography-customizer',
		'tastyRecipesTypography',
		array(
			'googleFonts' => apply_filters( 'yummy_bites_typography_customize_list', yummy_bites_get_all_google_fonts() )
		)
	);

	wp_localize_script( 'yummy-bites-typography-customizer', 'typography_defaults', yummy_bites_typography_default_fonts() );
}
endif;
add_action( 'customize_controls_enqueue_scripts', 'yummy_bites_customize_script' );

/*
 * Notifications in customizer
 */
require get_template_directory() . '/inc/customizer-plugin-recommend/customizer-notice/class-customizer-notice.php';

require get_template_directory() . '/inc/customizer-plugin-recommend/plugin-install/class-plugin-install-helper.php';

require get_template_directory() . '/inc/customizer-plugin-recommend/plugin-install/class-plugin-recommend.php';

if( ! function_exists( 'yummy_bites_customizer_notice_init' ) ) {
	function yummy_bites_customizer_notice_init() {
		$config_customizer = array(
			'recommended_plugins' => array(
				//change the slug for respective plugin recomendation
				'delicious-recipes' => array(
					'recommended' => true,
					'description' => sprintf(
						/* translators: %s: plugin name */
						esc_html__( 'If you want to take full advantage of the features this theme has to offer, please install and activate %s plugin.', 'yummy-bites' ), '<strong>WP Delicious</strong>'
					),
				),
			),
			'recommended_plugins_title' => esc_html__( 'Recommended Plugin', 'yummy-bites' ),
			'install_button_label'      => esc_html__( 'Install and Activate', 'yummy-bites' ),
			'activate_button_label'     => esc_html__( 'Activate', 'yummy-bites' ),
			'deactivate_button_label'   => esc_html__( 'Deactivate', 'yummy-bites' ),
		);
		Yummy_Bites_Customizer_Notice::init( apply_filters( 'yummy_bites_customizer_notice_array', $config_customizer ) );
	}
}
add_action('init', 'yummy_bites_customizer_notice_init');

if( !yummy_bites_pro_is_activated()){
	/**
	 * Functions that removes default section in wp
	 *
	 * @param [type] $wp_customize
	 * @return void
	 */
	function yummy_bites_removing_default_sections( $wp_customize ){
		$wp_customize->remove_section('header_image');
	}
	add_action( 'customize_register','yummy_bites_removing_default_sections' );
}
