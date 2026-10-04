<?php
/**
 * Yummy Bites Footer Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_footer' ) ) : 

function yummy_bites_customize_register_footer( $wp_customize ) {
    $colorDefaults = yummy_bites_get_color_defaults();
    $defaults      = yummy_bites_get_general_defaults();

    $wp_customize->add_section(
        'footer_settings',
        array(
            'title'      => __( 'Footer Settings', 'yummy-bites' ),
            'priority'   => 20,
            'panel' => 'main_footer_settings',
        )
    );
    
    $wp_customize->add_setting(
        'footer_tabs_settings',
        array(
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Tabs_Control(
            $wp_customize, 'footer_tabs_settings', array(
                'section' => 'footer_settings',
                'tabs'    => array(
                    'general' => array(
                        'nicename' => esc_html__( 'General', 'yummy-bites' ),
                        'controls' => array(
                            'footer_copyright',
                            'ed_author_link',
                            'ed_wp_link',
                        ),
                    ),
                    'design' => array(
                        'nicename' => esc_html__( 'Design', 'yummy-bites' ),
                        'controls' => array(
                            'foot_text_color',
                            'foot_widget_heading_color',
                            'foot_bg_color',
                        ),
                    )
                ),
            )
        )
    );

    /** Footer Copyright */
    $wp_customize->add_setting(
        'footer_copyright',
        array(
            'default'           => $defaults['footer_copyright'],
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'footer_copyright',
        array(
            'label'   => __( 'Footer Copyright Text', 'yummy-bites' ),
            'section' => 'footer_settings',
            'type'    => 'textarea',
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'footer_copyright', array(
        'selector' => '.site-info .copyright',
        'render_callback' => 'yummy_bites_get_footer_copyright',
    ) );

    /** Text Color*/
    $wp_customize->add_setting(
        'foot_text_color', 
        array(
            'default'           =>  $colorDefaults['foot_text_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'foot_text_color', 
            array(
                'label'       => __( 'Text Color', 'yummy-bites' ),
                'section'     => 'footer_settings',
            )
        )
    );

    /** Footer Widget Title Color*/
    $wp_customize->add_setting(
        'foot_widget_heading_color', 
        array(
            'default'           =>  $colorDefaults['foot_widget_heading_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'foot_widget_heading_color', 
            array(
                'label'       => __( 'Widget Title Color', 'yummy-bites' ),
                'section'     => 'footer_settings',
            )
        )
    );

    /** Footer Background Color*/
    $wp_customize->add_setting(
        'foot_bg_color', 
        array(
            'default'           =>  $colorDefaults['foot_bg_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'foot_bg_color', 
            array(
                'label'       => __( 'Background Color', 'yummy-bites' ),
                'section'     => 'footer_settings',
            )
        )
    );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_footer' );