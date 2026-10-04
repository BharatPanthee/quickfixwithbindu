<?php
/**
 * General Settings
 *
 * @package Yummy Bites
 */
if( ! function_exists( 'yummy_bites_customize_register_general' ) ) :

function yummy_bites_customize_register_general( $wp_customize ) {
   
    /** General Settings */
    $wp_customize->add_panel( 
        'general_settings',
        array(
            'priority'    => 7,
            'capability'  => 'edit_theme_options',
            'title'       => __( 'General', 'yummy-bites' ),
            'description' => __( 'Customize Container, Sidebar, Button and Scroll to Top.', 'yummy-bites' ),
        ) 
    );
    $wp_customize->remove_section('background_image'); // removed default background image

}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_general' );