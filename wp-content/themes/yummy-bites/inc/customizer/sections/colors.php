<?php
/**
 * Yummy Bites Colors Settings
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_colors' ) ) : 

function yummy_bites_customize_register_colors( $wp_customize ) {
    
    $defaults = yummy_bites_get_color_defaults();

    /**Color Presets */

    $wp_customize->add_setting( 
        'color_preset_style', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            'transport'         => 'postMessage',
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'color_preset_style',
			array(
				'section'    => 'colors',
				'label'      => __( 'Choose Color Presets', 'yummy-bites' ),
				'col'        => 'col-1',
				'svg'         => true,
                'priority'    => 10,
				'choices'    => array(
					'one' => array(
                        'label' => esc_html__( 'Default Style', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('one'),
                    ),
					'two' => array(
                        'label' => esc_html__( 'Style Two', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('two'),
                    ),
					'three' => array(
                        'label' => esc_html__( 'Style Three', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('three'),
                    ),
					'four' => array(
                        'label' => esc_html__( 'Style Four', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('four'),
                    ),
					'five' => array(
                        'label' => esc_html__( 'Style Five', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('five'),
                    ),
					'six' => array(
                        'label' => esc_html__( 'Style Six', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('six'),
                    ),
					'seven' => array(
                        'label' => esc_html__( 'Style Seven', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('seven'),
                    ),
					'eight' => array(
                        'label' => esc_html__( 'Style Eight', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('eight'),
                    ),
					'nine' => array(
                        'label' => esc_html__( 'Style Nine', 'yummy-bites' ),
                        'path'  => yummy_bites_color_preset_style_list('nine'),
                    ),
				)
			)
		)
	);

    /** Primary Color*/
    $wp_customize->add_setting( 
        'primary_color', 
        array(
            'default'           =>  $defaults['primary_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'primary_color', 
            array(
                'label'    => __( 'Primary Color', 'yummy-bites' ),
                'section'  => 'colors',
                'priority' => 10,
            )
        )
    );

    /** Secondary Color*/
    $wp_customize->add_setting( 
        'secondary_color', 
        array(
            'default'           =>  $defaults['secondary_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'secondary_color', 
            array(
                'label'    => __( 'Secondary Color', 'yummy-bites' ),
                'section'  => 'colors',
                'priority' => 10,
            )
        )
    );

    /** Body Font Color*/
    $wp_customize->add_setting( 
        'body_font_color', 
        array(
            'default'           =>  $defaults['body_font_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'body_font_color', 
            array(
                'label'       => __( 'Base Font', 'yummy-bites' ),
                'section'     => 'colors',
                'priority'    => 10,
            )
        )
    );

    /** Heading Color*/
    $wp_customize->add_setting( 
        'heading_color', 
        array(
            'default'           =>  $defaults['heading_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'heading_color', 
            array(
                'label'       => __( 'Heading', 'yummy-bites' ),
                'section'     => 'colors',
                'priority'    => 10,
            )
        )
    );

    /** Site Background Color*/
    $wp_customize->add_setting( 
        'site_bg_color', 
        array(
            'default'           =>  $defaults['site_bg_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'site_bg_color', 
            array(
                'label'       => __( 'Site Background', 'yummy-bites' ),
                'section'     => 'colors',
                'priority'    => 10,
            )
        )
    );

    /** Accent Color One*/
    $wp_customize->add_setting( 
        'accent_color_one', 
        array(
            'default'           =>  $defaults['accent_color_one'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'accent_color_one', 
            array(
                'label'       => __( 'Accent Color One', 'yummy-bites' ),
                'section'     => 'colors',
                'priority'    => 60,
                'active_callback' => 'yummy_bites_is_frontpage_builder_activated',
            )
        )
    );

    /** Accent Color Two*/
    $wp_customize->add_setting( 
        'accent_color_two', 
        array(
            'default'           =>  $defaults['accent_color_two'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'accent_color_two', 
            array(
                'label'       => __( 'Accent Color Two', 'yummy-bites' ),
                'section'     => 'colors',
                'priority'    => 70,
                'active_callback' => 'yummy_bites_is_frontpage_builder_activated',
            )
        )
    );

    $wp_customize->add_setting(
        'revert_color_preset',
        array(
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        'revert_color_preset',
        array(
            'label'       => '',
            'description' => '',
            'type'        => 'button',
            'priority'    => 85,
            'settings'    => array(),
            'section'     => 'colors',
            'input_attrs' => array(
                'value' => __( 'Revert Changes', 'yummy-bites' ),
                'class' => 'button button-primary color-revert-change',
            ),
        )
    );

    $wp_customize->get_section( 'colors' )->priority   = 8;
    $wp_customize->remove_control( 'background_color' ); //Remove site background color
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_colors' );