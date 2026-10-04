<?php
/**
 * Header Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_header_general_settings' ) ) : 

function yummy_bites_customize_register_header_general_settings( $wp_customize ) {    
    $defaults = yummy_bites_get_general_defaults();

    /** Header Settings */
    $wp_customize->add_section(
        'header_settings',
        array(
            'title'    => __( 'General', 'yummy-bites' ),
            'priority' => 10,
            'panel'    => 'main_header_settings',
        )
    );
    
    /** Enable Header Search */
    $wp_customize->add_setting( 
        'ed_header_search', 
        array(
            'default'           => $defaults['ed_header_search'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
            'priority'          => 20,
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_header_search',
			array(
				'section'     => 'header_settings',
				'label'	      => __( 'Header Search', 'yummy-bites' ),
			)
		)
	);
    
    /** Enable WooCommerce Cart */
    $wp_customize->add_setting( 
        'ed_woo_cart', 
        array(
            'default'           => $defaults['ed_woo_cart'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Toggle_Control( 
            $wp_customize,
            'ed_woo_cart',
            array(
                'section'         => 'header_settings',
                'label'           => __( 'Show Cart', 'yummy-bites' ),
                'priority'        => 16,
                'active_callback' => 'yummy_bites_is_woocommerce_activated'
            )
        )
    );
    // /** Header Settings Ends */
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_header_general_settings' );