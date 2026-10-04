<?php
/**
 * Header Panel
 *
 * @package Yummy Bites
 */
if( ! function_exists( 'yummy_bites_customize_register_main_header' ) ) :

function yummy_bites_customize_register_main_header( $wp_customize ) {
	
    /** Header Settings */
    $wp_customize->add_panel(
        'main_header_settings',
        array(
            'title'       => __( 'Main Header', 'yummy-bites' ),
            'priority'    => 15,
            'capability'  => 'edit_theme_options',
            'description' => __( 'Customizer header settings from here.', 'yummy-bites' ),
        )
    );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_main_header' );