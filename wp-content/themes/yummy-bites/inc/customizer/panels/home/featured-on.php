<?php
/**
 * Featured Recipe Settings
 *
 * @package Yummy Bites
 */

function yummy_bites_customize_register_featured_recipe( $wp_customize ){
    $defaults = yummy_bites_get_general_defaults();
    
    $wp_customize->add_section(
        'featured_recipe_settings',
        array(
            'title'    => __( 'Featured On', 'yummy-bites' ),
            'panel'    => 'frontpage_settings',
        )
    );

    /** Title */
    $wp_customize->add_setting(
        'feature_recipe_title',
        array(
            'default'           => $defaults['feature_recipe_title'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'feature_recipe_title',
        array(
            'label'    => __( 'Title', 'yummy-bites' ),
            'section'  => 'featured_recipe_settings',
            'type'     => 'text',
            'priority' => 8
        )
    );

    $wp_customize->selective_refresh->add_partial( 'feature_recipe_title', array(
        'selector'        => '#featured_on_section h2.section-title',
        'render_callback' => 'yummy_bites_get_feature_recipe_title',
    ) );

    $wp_customize->add_setting(
        'featured_on_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post', 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'featured_on_text',
            array(
                'section'   => 'featured_recipe_settings',
                'label'     => __( 'Add "Gallery Block" for featured on section.', 'yummy-bites' ),
                'priority'  => 8
            )
        )
    );

    $featured_section = $wp_customize->get_section( 'sidebar-widgets-featured-on' );
    if ( ! empty( $featured_section ) ) {
        $featured_section->panel           = 'frontpage_settings';
        $featured_section->priority        = 120;
        $wp_customize->get_control( 'feature_recipe_title' )->section = 'sidebar-widgets-featured-on';
        $wp_customize->get_control( 'featured_on_text' )->section = 'sidebar-widgets-featured-on';
    }
}
add_action( 'customize_register', 'yummy_bites_customize_register_featured_recipe' );