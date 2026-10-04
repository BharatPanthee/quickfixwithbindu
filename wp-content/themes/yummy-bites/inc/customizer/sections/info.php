<?php
/**
 * Yummy Bites Theme Info
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customizer_theme_info' ) ) : 

function yummy_bites_customizer_theme_info( $wp_customize ) {
	
    $wp_customize->add_section( 'theme_info', array(
		'title'       => __( 'Theme Info' , 'yummy-bites' ),
		'priority'    => 4,
	) );
    
    /** Important Links */
	$wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<p>';
	$theme_info .= sprintf( __( 'Theme Link: %1$sClick here.%2$s', 'yummy-bites' ),  '<a href="' . esc_url( 'https://wpdelicious.com/wordpress-themes/' . YUMMY_BITES_THEME_TEXTDOMAIN . '/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';
    $theme_info .= sprintf( __( 'Theme Documentation: %1$sClick here.%2$s', 'yummy-bites' ),  '<a href="' . esc_url( 'https://wpdelicious.com/docs-category/yummy-bites/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';
    $theme_info .= sprintf( __( 'Demo Link: %1$sClick here.%2$s', 'yummy-bites' ),  '<a href="' . esc_url( 'https://wpdelicious.com/yummy-bites-demos/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';

	$wp_customize->add_control( new Yummy_Bites_Note_Control( $wp_customize,
        'theme_info_theme', 
            array(
                'section'     => 'theme_info',
                'description' => $theme_info
            )
        )
    );
    
}
endif;
add_action( 'customize_register', 'yummy_bites_customizer_theme_info' );

if( class_exists( 'WP_Customize_Section' ) ) :
/**
 * Adding Go to Pro Section in Customizer
 * https://github.com/justintadlock/trt-customizer-pro
 */
class Yummy_Bites_Customize_Section_Pro extends WP_Customize_Section {

	/**
	 * The type of customize section being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'yummy-bites-pro-section';

	/**
	 * Custom button text to output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_text = '';

	/**
	 * Custom pro button URL.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_url = '';

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function json() {
		$json = parent::json();

		$json['pro_text'] = $this->pro_text;
		$json['pro_url']  = esc_url( $this->pro_url );

		return $json;
	}

	/**
	 * Outputs the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_template() { ?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">
			<h3 class="accordion-section-title">
				{{ data.title }}
				<# if ( data.pro_text && data.pro_url ) { #>
					<a href="{{{ data.pro_url }}}" class="button button-secondary alignright" target="_blank">{{ data.pro_text }}</a>
				<# } #>
			</h3>
		</li>
	<?php }
}
endif;

if ( ! function_exists( 'yummy_bites_page_sections_pro' ) ) : 
/**
 * Add Pro Button
*/
function yummy_bites_page_sections_pro( $manager ) {
	if( ! yummy_bites_pro_is_activated() ){
		// Register custom section types.
		$manager->register_section_type( 'Yummy_Bites_Customize_Section_Pro' );

		// Register sections.
		$manager->add_section(
			new Yummy_Bites_Customize_Section_Pro(
				$manager,
				'yummy_bites_page_view_pro',
				array(
					'priority' => 3, 
					'pro_text' => esc_html__( 'VIEW PRO VERSION', 'yummy-bites' ),
					'pro_url'  => esc_url( 'https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro' )
				)
			)
		);
	}
}
endif;
add_action( 'customize_register', 'yummy_bites_page_sections_pro' );