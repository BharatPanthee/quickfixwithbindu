<?php
/**
 * Yummy Bites Custom functions and definitions
 *
 * @package Yummy Bites
 */

if ( ! function_exists( 'yummy_bites_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function yummy_bites_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Yummy Bites, use a find and replace
	 * to change 'yummy-bites' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'yummy-bites', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary'   => esc_html__( 'Primary', 'yummy-bites' ),
		'secondary' => esc_html__( 'Secondary', 'yummy-bites' ),
		'footer'    => esc_html__( 'Footer', 'yummy-bites' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'yummy_bites_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support( 
        'custom-logo', 
        apply_filters( 
            'yummy_bites_custom_logo_args', 
            array( 
                'height'      => 70, /** change height as per theme requirement */
                'width'       => 70, /** change width as per theme requirement */
                'flex-height' => true,
                'flex-width'  => true,
                'header-text' => array( 'site-title', 'site-description' ) 
            )
        ) 
    );
    
    /**
     * Add support for custom header.
    */
    add_theme_support( 
        'custom-header', 
        apply_filters( 
            'yummy_bites_custom_header_args', 
            array(
                'default-image' => '',
                'video'         => true,
                'width'         => 1024, /** change width as per theme requirement */
                'height'        => 1024, 
                'flex-height'   => true,
                'flex-width'    => true,/** change height as per theme requirement */
                'header-text'   => false,
				'wp-head-callback'   => 'yummy_bites_header_style',
            ) 
        ) 
    );

    /**
     * Add Custom Images sizes.
    */
    add_image_size( 'yummy-bites-slider-one', 380, 515, true );
    add_image_size( 'yummy-bites-blog-one', 350, 457, true );
    add_image_size( 'yummy-bites-fullwidth', 1200, 600, true);
    add_image_size( 'yummy-bites-single-one', 720, 950, true);
    
    /** Starter Content */
    $starter_content = array(
        // Specify the core-defined pages to create and add custom thumbnails to some of them.
		'posts' => array( 'home', 'blog' ),
		
        // Default to a static front page and assign the front and posts pages.
		'options' => array(
			'show_on_front' => 'page',
			'page_on_front' => '{{home}}',
			'page_for_posts' => '{{blog}}',
		),
        
        // Set up nav menus for each of the two areas registered in the theme.
		'nav_menus' => array(
			// Assign a menu to the "top" location.
			'primary' => array(
				'name' => __( 'Primary', 'yummy-bites' ),
				'items' => array(
					'page_home',
					'page_blog'
				)
			)
		),
    );
    
    $starter_content = apply_filters( 'yummy_bites_starter_content', $starter_content );

	add_theme_support( 'starter-content', $starter_content );
    
    // Add theme support for Responsive Videos.
    add_theme_support( 'jetpack-responsive-videos' );

    $colorDefaults = yummy_bites_get_color_defaults();
    add_theme_support(
        'editor-color-palette', 
        apply_filters('yummy-bites-editor-color-palette', [
            [
                'name' => esc_attr__( 'Primary Color', 'yummy-bites' ),
                'slug' => 'primary-color',
                'color' => 'var(--yummy-primary-color, ' . $colorDefaults['primary_color'] . ')',
            ],

            [
                'name' => esc_attr__( 'Secondary Color', 'yummy-bites' ),
                'slug' => 'secondary-color',
                'color' => 'var(--yummy-secondary-color, ' . $colorDefaults['secondary_color'] . ')',
            ],

            [
                'name' => esc_attr__( 'Body Font Color', 'yummy-bites' ),
                'slug' => 'body-font-color',
                'color' => 'var(--yummy-font-color, ' . $colorDefaults['body_font_color'] . ')',
            ],

            [
                'name' => esc_attr__( 'Heading Color', 'yummy-bites' ),
                'slug' => 'heading-color',
                'color' => 'var(--yummy-heading-color, '. $colorDefaults['heading_color'] . ')',
            ],

            [
                'name' => esc_attr__( 'Site Background Color', 'yummy-bites' ),
                'slug' => 'site-bg-color',
                'color' => 'var(--yummy-background-color, ' . $colorDefaults['site_bg_color'] . ')',
            ],
        ])
    );

    /**
     * Add support for WP Delicious Plugin.
    */
    add_theme_support('delicious_recipes');

    // Add excerpt support for pages
    add_post_type_support( 'page', 'excerpt' );

    // Add support for full and wide align images.
    add_theme_support( 'align-wide' );

    // Add support for editor styles.
    add_theme_support( 'editor-styles' );

    // Add support for responsive embeds.
    add_theme_support( 'responsive-embeds' );

    // Add support for block editor styles.
    add_theme_support( 'wp-block-styles' );

	// Add support for padding control
	add_theme_support( 'custom-spacing' );
	
	// Add support for border
	add_theme_support( 'border' );

	// Add support for link color
	add_theme_support( 'link-color' );

	// Add support for custom line height
	add_theme_support( 'custom-line-height' );

    //Support for appearance tools
	add_theme_support( 'appearance-tools');

    // Use minified libraries if SCRIPT_DEBUG is false
    $build    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    add_editor_style( get_template_directory_uri(). '/css' . $build . '/pattern-style' . $suffix . '.css' );
}
endif;
add_action( 'after_setup_theme', 'yummy_bites_setup' );

if( ! function_exists( 'yummy_bites_content_width' ) ) :
/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function yummy_bites_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'yummy_bites_content_width', 750 );
}
endif;
add_action( 'after_setup_theme', 'yummy_bites_content_width', 0 );

if( ! function_exists( 'yummy_bites_template_redirect_content_width' ) ) :
/**
* Adjust content_width value according to template.
*
* @return void
*/
function yummy_bites_template_redirect_content_width(){
	$sidebar = yummy_bites_sidebar();
    if( $sidebar ){	   
        $GLOBALS['content_width'] = 750;       
	}else{
        if( is_singular() ){
            if( yummy_bites_sidebar( true ) === 'full-width centered' ){
                $GLOBALS['content_width'] = 750;
            }else{
                $GLOBALS['content_width'] = 1200;                
            }                
        }else{
            $GLOBALS['content_width'] = 1200;
        }
	}
}
endif;
add_action( 'template_redirect', 'yummy_bites_template_redirect_content_width' );

if( ! function_exists( 'yummy_bites_scripts' ) ) :
/**
 * Enqueue scripts and styles.
 */
function yummy_bites_scripts(){
	// Use minified libraries if SCRIPT_DEBUG is false
    $build         = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix        = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    $defaultsfont  = yummy_bites_get_general_defaults();
    $defaults      = yummy_bites_get_banner_defaults();
    $masonry_array = array();

    if( yummy_bites_pro_is_activated() ){
        $prodefaults    = yummy_bites_pro_get_customizer_layouts_defaults();
        $blog_layout    = get_theme_mod( 'blog_layouts', $prodefaults['blog_layouts'] );
        $archive_layout = get_theme_mod( 'archive_layouts', $prodefaults['archive_layouts'] );
        $masonry_array = ( $blog_layout === 'six' || $archive_layout === 'six') ? array( 'masonry' ) : array();  
    }

    $ed_localgoogle_fonts   = get_theme_mod( 'ed_localgoogle_fonts', $defaultsfont['ed_localgoogle_fonts'] );
	$ed_preload_local_fonts = get_theme_mod( 'ed_preload_local_fonts', $defaultsfont['ed_preload_local_fonts'] );

    if( yummy_bites_is_woocommerce_activated() ){
        wp_enqueue_style( 'yummy-bites-woocommerce', get_template_directory_uri(). '/css' . $build . '/woocommerce' . $suffix . '.css', array(), YUMMY_BITES_THEME_VERSION );
    }

    if ( yummy_bites_is_elementor_activated_post() ) {
        wp_enqueue_style( 'yummy-bites-elementor', get_template_directory_uri() . '/css' . $build . '/elementor' . $suffix . '.css', array(), YUMMY_BITES_THEME_VERSION );
    }
    
    if (  $ed_localgoogle_fonts && 
		! is_customize_preview() && 
		! is_admin() &&
        !empty(yummy_bites_google_fonts_url()) && 
		$ed_preload_local_fonts ) {
			yummy_bites_preload_local_fonts( yummy_bites_google_fonts_url() );
	}
    
    if( yummy_bites_is_bttk_activated() )
    wp_enqueue_style( 'yummy-bites-toolkit', get_template_directory_uri(). '/css' . $build . '/blossom-toolkit' . $suffix . '.css', array(), YUMMY_BITES_THEME_VERSION );

    wp_enqueue_style( 'yummy-bites-google-fonts', yummy_bites_google_fonts_url(), array(), null );

    wp_register_style( 'owl-carousel', get_template_directory_uri(). '/css' . $build . '/owl.carousel' . $suffix . '.css', array(), '2.3.4' );
    wp_register_script( 'owl-carousel', get_template_directory_uri() . '/js' . $build . '/owl.carousel' . $suffix . '.js', array( 'jquery' ), '2.3.4', true );
 
    if( is_front_page() && !is_home() && 'slider_banner' === get_theme_mod( 'ed_banner_section', $defaults['ed_banner_section'] )){
        $animation = get_theme_mod( 'slider_animation', '' );
        if ( $animation !== '' ) wp_enqueue_style( 'animate', get_template_directory_uri(). '/css' . $build . '/animate' . $suffix . '.css', array(), '3.5.2' );
        wp_enqueue_style( 'owl-carousel' );
        wp_enqueue_script( 'owl-carousel' );
    }

	wp_enqueue_style( 'yummy-bites-style', get_template_directory_uri() . '/style' . $suffix . '.css', array(), YUMMY_BITES_THEME_VERSION );

	wp_enqueue_script( 'yummy-bites-custom', get_template_directory_uri() . '/js' . $build . '/custom' . $suffix . '.js', $masonry_array, YUMMY_BITES_THEME_VERSION, true );
    wp_enqueue_script( 'yummy-bites-accessibility', get_template_directory_uri() . '/js' . $build . '/modal-accessibility' . $suffix . '.js', array(),YUMMY_BITES_THEME_VERSION, true );
    
    wp_style_add_data( 'yummy-bites-style', 'rtl', 'replace' );

	if( $suffix ){
		wp_style_add_data( 'yummy-bites-style', 'suffix', $suffix );
	}

    $array = array(
        'rtl'           => is_rtl(),
        'auto'          => get_theme_mod( 'slider_auto', $defaults['slider_auto'] ),
        'loop'          => get_theme_mod( 'slider_loop', $defaults['slider_loop'] ),
        'animation'     => esc_attr( get_theme_mod( 'slider_animation' ) ),
        'speed'         => absint( get_theme_mod( 'slider_speed',  $defaults['slider_speed']) ),
        'ajax_url'      => admin_url( 'admin-ajax.php' )
    );
    
    wp_localize_script( 'yummy-bites-custom', 'yummy_bites_data', $array );
    
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}
endif;
add_action( 'wp_enqueue_scripts', 'yummy_bites_scripts' );

if( ! function_exists( 'yummy_bites_admin_scripts' ) ) :
/**
 * Enqueue admin scripts and styles.
*/
function yummy_bites_admin_scripts( $hook ){
	
    wp_enqueue_style( 'yummy-bites-admin', get_template_directory_uri() . '/inc/css/admin.css', '', YUMMY_BITES_THEME_VERSION );
    
}
endif; 
add_action( 'admin_enqueue_scripts', 'yummy_bites_admin_scripts' );

if( ! function_exists( 'yummy_bites_block_editor_styles' ) ) :
/**
 * Enqueue editor styles for Gutenberg
 * 
 * @return void
 */
function yummy_bites_block_editor_styles() {
    // Use minified libraries if SCRIPT_DEBUG is false
    $build  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    
    // Block styles.
    wp_register_style( 'yummy-bites-block-editor-style', get_template_directory_uri() . '/css' . $build . '/editor-block' . $suffix . '.css' );
    wp_enqueue_style( 'yummy-bites-block-editor-style' );

    wp_add_inline_style( 'yummy-bites-block-editor-style', yummy_bites_gutenberg_inline_style() );

    // Add custom fonts.
    wp_enqueue_style( 'yummy-bites-google-fonts', yummy_bites_google_fonts_url(), array(), null );

}
endif;
add_action( 'enqueue_block_editor_assets', 'yummy_bites_block_editor_styles' );

if( ! function_exists( 'yummy_bites_body_classes' ) ) :
/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function yummy_bites_body_classes( $classes ) {
	$defaults              = yummy_bites_get_general_defaults();
	$ed_last_widget_sticky = get_theme_mod( 'ed_last_widget_sticky',$defaults['ed_last_widget_sticky'] );

    if( yummy_bites_pro_is_activated() ){
        $prodefaults    = yummy_bites_pro_get_customizer_layouts_defaults();
        $blog_layout    = get_theme_mod( 'blog_layouts', $prodefaults['blog_layouts'] );
        $archive_layout = get_theme_mod( 'archive_layouts', $prodefaults['archive_layouts'] );
        $single_layout  = yummy_bites_pro_single_meta_layout();
    }

    // Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
    
    // Adds a class of custom-background-image to sites with a custom background image.
	if ( get_background_image() ) {
		$classes[] = 'custom-background-image';
	}
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
		$classes[] = 'custom-background-color';
	}

    if( $ed_last_widget_sticky ){
        $classes[] = 'widget-sticky';
    }

    if ( yummy_bites_pro_is_activated() && is_single() ) {
		$classes[] = 'post-layout-'.$single_layout;
	}elseif( is_single() ){
        $classes[] = 'post-layout-one';
    }

    if ( yummy_bites_pro_is_activated() && is_home() ){
		$classes[] = 'blog-layout-'.$blog_layout;
	}elseif( yummy_bites_pro_is_activated() && ( is_archive() || is_search() )  ){
        $classes[] = 'blog-layout-'. $archive_layout;
    }elseif ( is_home() || is_archive() || is_search()) {
		$classes[] = 'blog-layout-one';
	}

    if( yummy_bites_is_delicious_recipe_activated() ){
        if( is_singular( DELICIOUS_RECIPE_POST_TYPE ) ) {
            $classes[] = 'single-recipe-one';
        }else{
            $classes[] = 'delicious-recipe-activated';
        }
    }

    if( !is_404() ){
        $classes[] = yummy_bites_sidebar( true );
    }
    
	return $classes;
}
endif;
add_filter( 'body_class', 'yummy_bites_body_classes' );

if( ! function_exists( 'yummy_bites_post_classes' ) ) :
/**
 * Add custom classes to the array of post classes.
*/
function yummy_bites_post_classes( $classes ){

    if( yummy_bites_pro_is_activated() ){
        $prodefaults    = yummy_bites_pro_get_customizer_layouts_defaults();
        $blog_layout    = get_theme_mod( 'blog_layouts', $prodefaults['blog_layouts'] );
        $archive_layout = get_theme_mod( 'archive_layouts', $prodefaults['archive_layouts'] );
    }
    
    if( yummy_bites_pro_is_activated() && ( ( is_home() && $blog_layout != 'one' && $blog_layout != 'seven' ) || ( ( is_archive() || is_search() ) && $archive_layout != 'one' && $archive_layout != 'seven' ) ) ){
        $class = 'vertical';
    }else{
        $class = 'horizontal';
    }

    if( is_home() || is_archive() || is_search() ){
        $classes[] = 'latest_post post ' . $class;
    }
    return $classes;
}
endif;
add_filter( 'post_class', 'yummy_bites_post_classes' );

if ( ! function_exists( 'yummy_bites_pingback_header' ) ) : 
/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function yummy_bites_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
endif;
add_action( 'wp_head', 'yummy_bites_pingback_header' );

if( ! function_exists( 'yummy_bites_change_comment_form_default_fields' ) ) :
/**
 * Change Comment form default fields i.e. author, email & url.
*/
function yummy_bites_change_comment_form_default_fields( $fields ){    
    // get the current commenter if available
    $commenter = wp_get_current_commenter();
 
    // core functionality
    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );    
 
    // Change just the author field
    $fields['author'] = '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'yummy-bites' ) . '<span class="required">*</span></label><input id="author" name="author" placeholder="' . esc_attr__( 'Name*', 'yummy-bites' ) . '" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>';
    
    $fields['email'] = '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'yummy-bites' ) . '<span class="required">*</span></label><input id="email" name="email" placeholder="' . esc_attr__( 'Email*', 'yummy-bites' ) . '" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>';
    
    $fields['url'] = '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', 'yummy-bites' ) . '</label><input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'yummy-bites' ) . '" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>'; 
    
    return $fields;    
}
endif;
add_filter( 'comment_form_default_fields', 'yummy_bites_change_comment_form_default_fields' );

if( ! function_exists( 'yummy_bites_change_comment_form_defaults' ) ) :
/**
 * Change Comment Form defaults
*/
function yummy_bites_change_comment_form_defaults( $defaults ){    
    $defaults['comment_field'] = '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'yummy-bites' ) . '</label><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Comment', 'yummy-bites' ) . '" cols="45" rows="5" aria-required="true"></textarea></p>';
    
    return $defaults;    
}
endif;
add_filter( 'comment_form_defaults', 'yummy_bites_change_comment_form_defaults' );

if ( ! function_exists( 'yummy_bites_excerpt_more' ) ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... * 
 */
function yummy_bites_excerpt_more( $more ) {
	return is_admin() ? $more : ' &hellip; ';
}

endif;
add_filter( 'excerpt_more', 'yummy_bites_excerpt_more' );

if ( ! function_exists( 'yummy_bites_excerpt_length' ) ) :
/**
 * Changes the default 55 character in excerpt 
*/
function yummy_bites_excerpt_length() {
    global $post;
    $defaults       = yummy_bites_get_general_defaults();
	$excerpt_length = get_theme_mod( 'excerpt_length', $defaults['excerpt_length'] );
    if ( $post){
        return absint( $excerpt_length );  
    }
}
endif;
add_filter( 'excerpt_length', 'yummy_bites_excerpt_length', 999 );

if( ! function_exists( 'yummy_bites_get_the_archive_title' ) ) :
/**
 * Filter Archive Title
*/
function yummy_bites_get_the_archive_title( $title ){
    $defaults  = yummy_bites_get_general_defaults();
	$ed_prefix = get_theme_mod( 'ed_prefix_archive', $defaults['ed_prefix_archive'] );
	$ed_title  = get_theme_mod( 'ed_archive_title', $defaults['ed_archive_title'] );

    if( is_post_type_archive( 'product' ) ){
        $title = '<h1 class="page-title">' . get_the_title( get_option( 'woocommerce_shop_page_id' ) ) . '</h1>';
    }elseif( is_category() ){
		$page_title =  $ed_title ? '<h1 class="page-title">'. esc_html( single_cat_title( '', false ) ) .'</h1>' : '';
		
		if( !$ed_prefix ){
			$title = '<div class="archive-title-wrapper"><span class="sub-title">'. esc_html__( 'Browsing Category', 'yummy-bites' ) . '</span>' . $page_title . '</div>';
		}else{
			$title = $page_title;
		}			
	}elseif( is_tag() ){
		$page_title =  $ed_title ? '<h1 class="page-title">' . esc_html( single_tag_title( '', false ) ) . '</h1>' : '';

		if( !$ed_prefix ) {
			$title = '<div class="archive-title-wrapper"><span class="sub-title">'. esc_html__( 'Browsing Tag', 'yummy-bites' ) . '</span>'. $page_title .'</div>';
		}else{
			$title = $page_title;
		}
	}elseif( is_year() ){
		$page_title =  $ed_title ? '<h1 class="page-title">' . get_the_date( _x( 'Y', 'yearly archives date format', 'yummy-bites' ) ) . '</h1>' : '';

		if( !$ed_prefix ){
			$title = '<div class="archive-title-wrapper"><span class="sub-title">'. esc_html__( 'Browsing Year', 'yummy-bites' ) . '</span>'. $page_title .'</div>';
		}else{
			$title = $page_title;                   
		}
	}elseif( is_month() ){
		$page_title =  $ed_title ? '<h1 class="page-title">' . get_the_date( _x( 'F Y', 'monthly archives date format', 'yummy-bites' ) ) . '</h1>' : '';

		if( !$ed_prefix ){
			$title = '<div class="archive-title-wrapper"><span class="sub-title">'. esc_html__( 'Browsing Month', 'yummy-bites' ) . '</span>'. $page_title .'</div>';
		}else{
			$title = $page_title;                                   
		}
	}elseif( is_day() ){
		$page_title =  $ed_title ? '<h1 class="page-title">' . get_the_date( _x( 'F j, Y', 'daily archives date format', 'yummy-bites' ) ) . '</h1>' : '';

		if( !$ed_prefix ){
			$title = '<div class="archive-title-wrapper"><span class="sub-title">'. esc_html__( 'Browsing Day', 'yummy-bites' ) . '</span>'. $page_title .'</div>';
		}else{
			$title = $page_title;                                   
		}
	}elseif( is_tax() ) {
		$tax        = get_taxonomy( get_queried_object()->taxonomy );
		$page_title = $ed_title ? '<h1 class="page-title">' . single_term_title( '', false ) . '</h1>' : '';

		if( !$ed_prefix ){
			$title = '<div class="archive-title-wrapper"><span class="sub-title">' . $tax->labels->singular_name . '</span>'. $page_title .'</div>';
		}else{
			$title = $page_title;                                   
		}
	}
    return $title;
}
endif;
add_filter( 'get_the_archive_title', 'yummy_bites_get_the_archive_title' );

if( ! function_exists( 'yummy_bites_get_comment_author_link' ) ) :
/**
 * Filter to modify comment author link
 * @link https://developer.wordpress.org/reference/functions/get_comment_author_link/
 */
function yummy_bites_get_comment_author_link( $return, $author, $comment_ID ){
    $comment = get_comment( $comment_ID );
    $url     = get_comment_author_url( $comment );
    $author  = get_comment_author( $comment );
 
    if ( empty( $url ) || 'http://' == $url )
        $return = '<span itemprop="name">'. esc_html( $author ) .'</span>';
    else
        $return = '<span itemprop="name"><a href=' . esc_url( $url ) . ' rel="external nofollow noopener" class="url" itemprop="url">' . esc_html( $author ) . '</a></span>';

    return $return;
}
endif;
add_filter( 'get_comment_author_link', 'yummy_bites_get_comment_author_link', 10, 3 );

if( ! function_exists( 'yummy_bites_admin_notice' ) ) :
/**
 * Addmin notice for getting started page
*/
function yummy_bites_admin_notice(){
    global $pagenow;
    $theme_args      = wp_get_theme();
    $theme_meta      = get_option( 'yummy_bites_admin_notice' );
    $current_theme   = $theme_args->get( 'Name' );
    $current_screen  = get_current_screen();
    $dismissnonce    = wp_create_nonce( 'yummy_bites_admin_notice' );
    
    if( 'themes.php' == $pagenow && !$theme_meta ){
        
        if( $current_screen->id !== 'dashboard' && $current_screen->id !== 'themes' ){
            return;
        }

        if( is_network_admin() ){
            return;
        }

        if( ! current_user_can( 'manage_options' ) ){
            return;
        } ?>

        <div class="welcome-message notice notice-info">
            <div class="notice-wrapper">
                <div class="notice-text">
                    <h3><?php esc_html_e( 'Congratulations!', 'yummy-bites' ); ?></h3>
                    <p><?php printf( __( '%1$s is now installed and ready to use. Click below to see theme documentation, plugins to install and other details to get started.', 'yummy-bites' ), esc_html( $current_theme ) ); ?></p>
                    <p><a href="<?php echo esc_url( admin_url( 'themes.php?page=yummy-bites-dashboard' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Go to the dashboard.', 'yummy-bites' ); ?></a></p>
                    <p class="dismiss-link"><strong><a href="?yummy_bites_admin_notice=1&_wpnonce=<?php echo esc_attr( $dismissnonce ); ?>"><?php esc_html_e( 'Dismiss', 'yummy-bites' ); ?></a></strong></p>
                </div>
            </div>
        </div>
    <?php }
}
endif;
add_action( 'admin_notices', 'yummy_bites_admin_notice' );

if( ! function_exists( 'yummy_bites_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss
*/
function yummy_bites_update_admin_notice(){

    if (!current_user_can('manage_options')) {
        return;
    }

     // Bail if the nonce doesn't check out
     if ( ( isset( $_GET['yummy_bites_admin_notice'] ) && $_GET['yummy_bites_admin_notice'] = '1' ) && wp_verify_nonce( $_GET['_wpnonce'], 'yummy_bites_admin_notice' ) ) {
        update_option( 'yummy_bites_admin_notice', true );
    }

}
endif;
add_action( 'admin_init', 'yummy_bites_update_admin_notice' );

if ( ! function_exists( 'yummy_bites_dynamic_mce_css' ) ) :
/**
 * Add Editor Style 
 * Add Link Color Option in Editor Style (MCE CSS)
 */
function yummy_bites_dynamic_mce_css( $mce_css ){
    $mce_css .= ', ' . add_query_arg( array( 'action' => 'yummy_bites_dynamic_mce_css', '_nonce' => wp_create_nonce( 'yummy_bites_dynamic_mce_nonce', __FILE__ ) ), admin_url( 'admin-ajax.php' ) );
    return $mce_css;
}
endif;
add_filter( 'mce_css', 'yummy_bites_dynamic_mce_css' );
    
if ( ! function_exists( 'yummy_bites_dynamic_mce_css_ajax_callback' ) ) : 
/**
 * Ajax Callback
 */
function yummy_bites_dynamic_mce_css_ajax_callback(){
    
    /* Check nonce for security */
    $nonce = isset( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : '';
    if( ! wp_verify_nonce( $nonce, 'yummy_bites_dynamic_mce_nonce' ) ){
        die(); // don't print anything
    }
    
    $typo_defaults   = yummy_bites_get_typography_defaults();
    
    /* Get Link Color */
    $primary_font  = wp_parse_args( get_theme_mod( 'primary_font' ), $typo_defaults['primary_font'] );
    $heading_one   = wp_parse_args( get_theme_mod( 'heading_one' ), $typo_defaults['heading_one'] );
    $heading_two   = wp_parse_args( get_theme_mod( 'heading_two' ), $typo_defaults['heading_two'] );
    $heading_three = wp_parse_args( get_theme_mod( 'heading_three' ), $typo_defaults['heading_three'] );
    $heading_four  = wp_parse_args( get_theme_mod( 'heading_four' ), $typo_defaults['heading_four'] );
    $heading_five  = wp_parse_args( get_theme_mod( 'heading_five' ), $typo_defaults['heading_five'] );
    $heading_six   = wp_parse_args( get_theme_mod( 'heading_six' ), $typo_defaults['heading_six'] );

    $primary_font_family       = yummy_bites_get_font_family( $primary_font );
    $heading_one_font_family   = yummy_bites_get_font_family( $heading_one );
    $heading_two_font_family   = yummy_bites_get_font_family( $heading_two );
    $heading_three_font_family = yummy_bites_get_font_family( $heading_three );
    $heading_four_font_family  = yummy_bites_get_font_family( $heading_four );
    $heading_five_font_family  = yummy_bites_get_font_family( $heading_five );
    $heading_six_font_family   = yummy_bites_get_font_family( $heading_six );

    $h1FontFamily   = $heading_one_font_family === '"Default Family"' ? 'inherit' : $heading_one_font_family;
    $h2FontFamily   = $heading_two_font_family === '"Default Family"' ? 'inherit' : $heading_two_font_family;
    $h3FontFamily   = $heading_three_font_family === '"Default Family"' ? 'inherit' : $heading_three_font_family;
    $h4FontFamily   = $heading_four_font_family === '"Default Family"' ? 'inherit' : $heading_four_font_family;
    $h5FontFamily   = $heading_five_font_family === '"Default Family"' ? 'inherit' : $heading_five_font_family;
    $h6FontFamily   = $heading_six_font_family === '"Default Family"' ? 'inherit' : $heading_six_font_family;

    /* Set File Type and Print the CSS Declaration */
    header( 'Content-type: text/css' );
    echo ':root .mce-content-body {
        --yummy-primary-font: ' . wp_kses_post( $primary_font_family ) . ';
        --yummy-secondary-font: ' . wp_kses_post( $h1FontFamily ) . ';
    }
    .mce-content-body h1{
        font-family :' . wp_kses_post( $h1FontFamily ) . ';
    }
    .mce-content-body h2{
        font-family :' . wp_kses_post( $h2FontFamily ) . '; 
    }
    .mce-content-body h3{
        font-family :' . wp_kses_post( $h3FontFamily ) . '; 
    }
    .mce-content-body h4{
        font-family :' . wp_kses_post( $h4FontFamily ) . '; 
    }
    .mce-content-body h5{
        font-family :' . wp_kses_post( $h5FontFamily ) . '; 
    }
    .mce-content-body h6{
        font-family :' . wp_kses_post( $h6FontFamily ) . '; 
    }';
    die(); // end ajax process.
}
endif;
add_action( 'wp_ajax_yummy_bites_dynamic_mce_css', 'yummy_bites_dynamic_mce_css_ajax_callback' );
add_action( 'wp_ajax_no_priv_yummy_bites_dynamic_mce_css', 'yummy_bites_dynamic_mce_css_ajax_callback' );

if( ! function_exists( 'yummy_bites_blog_section_pagination' ) ) :
/**
 * Category pagination function
*/
function yummy_bites_blog_section_pagination(){
    ob_start();
    $paged          = isset( $_POST['paged'] ) ? $_POST['paged'] : '';
    $posts_per_page = 4;

    if( yummy_bites_pro_is_activated() ){
        $ed_social_sharing = get_theme_mod( 'ed_social_sharing', true );
    }

    $args = array(
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => array('type'=>'DESC', 'title'=>'ASC'),
    );
    $args['post_type']      = ( yummy_bites_is_delicious_recipe_activated() ) ? array( 'post', DELICIOUS_RECIPE_POST_TYPE) : 'post';
    $my_posts = new WP_Query($args);

    if ($my_posts->have_posts()) {
            while($my_posts->have_posts()){
            $my_posts->the_post(); ?>
                <article <?php post_class( 'post horizontal');?>>
                    <figure class="post-thumbnail"><a href="<?php echo esc_url( get_permalink() ); ?>">
                        <?php if( has_post_thumbnail() ){
                                the_post_thumbnail( 'yummy-bites-blog-one', array( 'itemprop' => 'image' ) );    
                        }else{
                            yummy_bites_get_fallback_svg( 'yummy-bites-blog-one' );//fallback    
                        } 
                        echo '</a>'; 
                        if( function_exists( 'yummy_bites_social_share' ) && $ed_social_sharing ) {
                            echo "<div class='ajax-social-share'>";
                            yummy_bites_social_share( false, true );
                            echo "</div>";
                        }
                        if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) yummy_bites_recipe_keywords(); ?>
                    </figure>              
                    <?php yummy_bites_blog_content(); ?>
                </article>
            <?php 
        }
    }
    wp_reset_postdata(); 
    $output = ob_get_clean();
    echo $output;
    wp_die();
}
endif;
add_action( 'wp_ajax_yummy_bites_blog_section_pagination', 'yummy_bites_blog_section_pagination' );
add_action( 'wp_ajax_nopriv_yummy_bites_blog_section_pagination', 'yummy_bites_blog_section_pagination' );