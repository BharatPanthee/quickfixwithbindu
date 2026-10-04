<?php
/**
 * Builder Settings
 *
 * @package Yummy Bites
*/
if ( ! function_exists( 'yummy_bites_customize_register_general_frontpage_builder' ) ) : 

function yummy_bites_customize_register_general_frontpage_builder( $wp_customize ){

    $defaults = yummy_bites_get_general_defaults();

    $wp_customize->add_section( 
        'home_page_builder',
        array(
            'priority' => 5,
            'title'    => __( 'Homepage Builder', 'yummy-bites' )
        ) 
    );

    /** Home Page Builder layouts */
    $wp_customize->add_setting( 
        'builder_types', 
        array(
            'default'           => $defaults['builder_types'],
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            
        ) 
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Radio_Buttonset_Control(
            $wp_customize,
            'builder_types',
            array(
                'section'  => 'home_page_builder',
                'label'    => __( 'Choose Home Page Builder', 'yummy-bites' ),
                'priority' => 5,
                'choices'  => array(
                    'customizer' => __( 'Customizer', 'yummy-bites' ),
                    'builder'    => __( 'Page Builder', 'yummy-bites' ),
                ),
            )
        )
    );

    /** Banner Link Text */
    $wp_customize->add_setting(
        'builder_types_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'builder_types_text',
            array(
                'section'     => 'home_page_builder',
                'description' => __( 'Refresh the Customizer after publishing new changes.', 'yummy-bites' ),
                'priority'    => 20,
            )
        )
    );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_general_frontpage_builder' );