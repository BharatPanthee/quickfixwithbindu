<?php
/**
 * Footer Panel
 *
 * @package Yummy Bites
 */
if( ! function_exists( 'yummy_bites_customize_register_main_footer' ) ) :

function yummy_bites_customize_register_main_footer( $wp_customize ) {
	
    /** Footer Settings */
    $wp_customize->add_panel(
        'main_footer_settings',
        array(
            'title'       => __( 'Footer', 'yummy-bites' ),
            'priority'    => 20,
            'capability'  => 'edit_theme_options',
            'description' => __( 'Customizer footer settings from here.', 'yummy-bites' ),
        )
    );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_main_footer' );