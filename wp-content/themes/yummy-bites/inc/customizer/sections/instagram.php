<?php
/**
 * Instagram Settings
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_general_instagram' ) ) : 

function yummy_bites_customize_register_general_instagram( $wp_customize ) {
    $defaults = yummy_bites_get_general_defaults();

    /** Instagram Settings */
    $wp_customize->add_section(
        'instagram_settings',
        array(
            'title'    => __( 'Instagram Settings', 'yummy-bites' ),
            'priority' => 40,
        )
    );
    
    /** Enable Instagram Section */
    $wp_customize->add_setting( 
        'ed_instagram', 
        array(
            'default'           => $defaults['ed_instagram'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Toggle_Control( 
            $wp_customize,
            'ed_instagram',
            array(
                'section'     => 'instagram_settings',
                'label'	      => __( 'Instagram Section', 'yummy-bites' ),
                'description' => __( 'Enable to show Instagram Section', 'yummy-bites' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'instagram_shortcode', 
        array(
            'default'           => '[instagram-feed]',
            'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control(
        'instagram_shortcode',
        array(
            'section'         => 'instagram_settings',
            'label'           => __( 'Shortcode', 'yummy-bites' ),
            'type'            => 'text',
            'description'     => __( 'Add shortcode for your instagram profile below:', 'yummy-bites' ),
            'active_callback' => 'yummy_bites_instagram_ac',
        )
    ); 
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_general_instagram' );