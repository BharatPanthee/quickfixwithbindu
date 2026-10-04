<?php
/**
 * Child theme of Yummy Bites
 *
 * @package Tasteful_Recipe
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * After setup theme hook
 *
 * @return void
 */
function tasteful_recipe_theme_setup() {
	/*
	 * Make child theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 */
	load_child_theme_textdomain( 'tasteful-recipe', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'tasteful_recipe_theme_setup', 100 );

/**
 * Enqueue styles for the child theme
 *
 * @return void
 */
function tasteful_recipe_styles() {
	// Use minified libraries if SCRIPT_DEBUG is false.
	$suffix   = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$my_theme = wp_get_theme();
	$version  = $my_theme['Version'];

	wp_enqueue_style( 'yummy-bites-style', get_template_directory_uri() . '/style' . $suffix . '.css', array(), array() );
	wp_enqueue_style( 'tasteful-recipe', get_stylesheet_directory_uri() . '/style' . $suffix . '.css', array( 'yummy-bites-style' ), $version );
}
add_action( 'wp_enqueue_scripts', 'tasteful_recipe_styles' );

/**
 * Typography details taken from parent theme.
 *
 * @return array
 */
function yummy_bites_get_typography_defaults() {
	$defaults = array(
		'primary_font'  => array(
			'family'    => 'Oxygen',
			'variants'  => '',
			'category'  => '',
			'weight'    => '400',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 18,
				'line_height'    => 1.75,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 18,
				'line_height'    => 1.75,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 18,
				'line_height'    => 1.75,
				'letter_spacing' => 0,
			),
		),
		'site_title'    => array(
			'family'    => 'Default Family',
			'variants'  => '',
			'category'  => '',
			'weight'    => 'bold',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 28,
				'line_height'    => 1.4,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 28,
				'line_height'    => 1.4,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 28,
				'line_height'    => 1.4,
				'letter_spacing' => 0,
			),
		),
		'button'        => array(
			'family'    => 'Default Family',
			'variants'  => '',
			'category'  => '',
			'weight'    => '500',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 18,
				'line_height'    => 1.6,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 18,
				'line_height'    => 1.6,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 18,
				'line_height'    => 1.6,
				'letter_spacing' => 0,
			),
		),
		'heading_one'   => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 48,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 40,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 36,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
		),
		'heading_two'   => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 40,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 32,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 28,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
		),
		'heading_three' => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 32,
				'line_height'    => 1.3,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 26,
				'line_height'    => 1.4,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 24,
				'line_height'    => 1.4,
				'letter_spacing' => 0,
			),
		),
		'heading_four'  => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 24,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 22,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 22,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
		),
		'heading_five'  => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 20,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 20,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 20,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
		),
		'heading_six'   => array(
			'family'    => 'Caudex',
			'variants'  => '',
			'category'  => '',
			'weight'    => '700',
			'transform' => 'none',
			'desktop'   => array(
				'font_size'      => 18,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'tablet'    => array(
				'font_size'      => 18,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
			'mobile'    => array(
				'font_size'      => 18,
				'line_height'    => 1.5,
				'letter_spacing' => 0,
			),
		),
	);

	return apply_filters( 'yummy_bites_typography_options_defaults', $defaults );
}

/**
 * Default value defined for the button taken from parent theme.
 *
 * @return array
 */
function yummy_bites_get_button_defaults() {

	$defaults = array(
		'button_roundness' => array(
			'top'    => 100,
			'right'  => 100,
			'bottom' => 100,
			'left'   => 100,
		),
		'button_padding'   => array(
			'top'    => 18,
			'right'  => 40,
			'bottom' => 18,
			'left'   => 40,
		),
	);

	return apply_filters( 'yummy_bites_button_options_defaults', $defaults );
}

/**
 * Default value defined for the color taken from parent theme.
 *
 * @return array
 */
function yummy_bites_get_color_defaults() {
	$defaults = array(
		'primary_color'             => '#29A963',
		'secondary_color'           => '#f65030',
		'body_font_color'           => '#5A5A5A',
		'heading_color'             => '#131212',
		'site_bg_color'             => '#ffffff',
		'site_title_color'          => '#000000',
		'accent_color_one'          => '#f6f9f7',
		'accent_color_two'          => '#f2f9f3',
		'site_tagline_color'        => '#333333',
		'btn_text_color_initial'    => '#ffffff',
		'btn_text_color_hover'      => '#ffffff',
		'btn_bg_color_initial'      => '#29A963',
		'btn_bg_color_hover'        => '#f65030',
		'btn_border_color_initial'  => '#29A963',
		'btn_border_color_hover'    => '#f65030',
		'foot_text_color'           => '#5A5A5A',
		'foot_bg_color'             => '#f6f9f7',
		'foot_widget_heading_color' => '#131212',
		'abt_bg_color'              => '#F4F6F5',
		'abt_title_color'           => '#131212',
		'abt_desc_color'            => '#5A5A5A',
	);

	return apply_filters( 'yummy_bites_color_options_defaults', $defaults );
}

/**
 * Header function inherited from parent theme.
 *
 * @return void
 */
function yummy_bites_header() {
	$defaults      = yummy_bites_get_general_defaults();
	$site_defaults = yummy_bites_get_site_defaults();

	$ed_social_media        = get_theme_mod( 'ed_social_links', $defaults['ed_social_links'] );
	$social_media_order     = get_theme_mod( 'social_media_order', $defaults['social_media_order'] );
	$ed_social_media_newtab = get_theme_mod( 'ed_social_links_new_tab', $defaults['ed_social_links_new_tab'] );
	$ed_cart                = get_theme_mod( 'ed_woo_cart', $defaults['ed_woo_cart'] );
	$ed_search              = get_theme_mod( 'ed_header_search', $defaults['ed_header_search'] );
	$blogname               = get_option( 'blogname' );
	$hideblogname           = get_theme_mod( 'hide_title', $site_defaults['hide_title'] );
	$blogdesc               = get_option( 'blogdescription' );
	$hideblogdesc           = get_theme_mod( 'hide_tagline', $site_defaults['hide_tagline'] );
	?>
	<header id="masthead" class="site-header style-seven" itemscope itemtype="http://schema.org/WPHeader">
		<?php if ( $ed_social_media || $ed_search || has_nav_menu( 'secondary' ) ) { ?>
			<div class="header-top">
				<div class="container">
					<div class="header-left">
						<?php yummy_bites_secondary_navigation(); ?>
					</div>
					<?php
					if ( $ed_search || $ed_social_media ) {
						echo '<div class="header-right">';
							$social_icons = new Yummy_Bites_Social_Lists();
							$social_icons->yummy_bites_social_links( $ed_social_media, $ed_social_media_newtab, $social_media_order );
						if ( $ed_search ) {
							get_search_form();
						}
						echo '</div>';
					}
					?>
				</div>
			</div>
			<?php
		}
		if ( has_nav_menu( 'primary' ) || has_custom_logo() || ( ! empty( $blogname ) && ! $hideblogname ) || ( ! empty( $blogdesc ) && ! $hideblogdesc ) || $ed_cart ) {
			?>
			<div class="header-main"> 
				<div class="container">
					<?php
					yummy_bites_site_branding();
					yummy_bites_primary_navigation();

					if ( yummy_bites_is_woocommerce_activated() && $ed_cart ) {
						yummy_bites_wc_cart_count();
					}
					?>
				</div>
			</div>
			<?php
		}
		yummy_bites_mobile_navigation();
		?>
	</header>
	<?php
}

/**
 * Demo Importer function inherited from parent theme.
 *
 * @return void
 */
function yummy_bites_demo_importer_checked() {
	if ( function_exists( 'DEMO_IMPORTER_PLUS_setup' ) ) {
		add_filter(
			'demo_importer_plus_api_id',
			function () {
				return array( '499', '394', '398', '405', '108', '97', '168', '176', '180', '181', '182', '148', '155', '158', '161', '166', '487' );
			}
		);
	}
}

/**
 * Footer Bottom function inherited from parent theme.
 *
 * @return void
 */
function yummy_bites_footer_bottom() {

	?>
	<div class="footer-b">
		<div class="container">
			<div class="footer-bottom-t">
				<div class="site-info">            
					<?php
						yummy_bites_get_footer_copyright();
					if ( yummy_bites_pro_is_activated() ) {
						$partials = new Yummy_Bites_Partials();
						$partials->yummy_bites_pro_ed_author_link();
						$partials->yummy_bites_pro_ed_wp_link();
					} else {
						echo esc_html__( ' Tasteful Recipe | Developed By ', 'tasteful-recipe' );
						echo '<a href="' . esc_url( 'https://wpdelicious.com/' ) . '" rel="nofollow" target="_blank">' . esc_html__( 'WP Delicious', 'tasteful-recipe' ) . '</a>.';
						/* translators: %s is replaced with WordPress URL */
						printf( esc_html__( ' Powered by %s. ', 'tasteful-recipe' ), '<a href="' . esc_url( 'https://wordpress.org/', 'tasteful-recipe' ) . '" rel="nofollow" target="_blank">WordPress</a>' );
					}
					if ( function_exists( 'the_privacy_policy_link' ) ) {
						the_privacy_policy_link();
					}
					?>
				</div>
				<?php yummy_bites_footer_navigation(); ?>
			</div>
		</div>
	</div>
	<?php
}
