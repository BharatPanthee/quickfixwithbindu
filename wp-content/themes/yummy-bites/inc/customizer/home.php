<?php
/**
 * Front Page Settings
 *
 * @package Yummy Bites
 */
if( ! function_exists( 'yummy_bites_customize_register_frontpage' ) ) :

function yummy_bites_customize_register_frontpage( $wp_customize ) {
	
    if( yummy_bites_is_frontpage_customizer_activated() ){
        /** Front Page Settings */
        $wp_customize->add_panel( 
            'frontpage_settings',
            array(
                'priority'    => 49,
                'capability'  => 'edit_theme_options',
                'title'       => __( 'Front Page Settings', 'yummy-bites' )
            ) 
        );
    }

}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_frontpage' );