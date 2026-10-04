<?php 
/**
 * Notification Bar
 *
 * @param obj $wp_customize
 * @return void
 */

function yummy_bites_customize_register_additional_settings( $wp_customize ){

    /** Notification Bar Settings */
    $wp_customize->add_section(
        'notification_bar_image_section',
        array(
            'title'    => __( 'Notification Bar Section', 'yummy-bites' ),
            'priority' => 75
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'notification_bar_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'notification_bar_text',
            array(
                'section'     => 'notification_bar_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'notification_bar_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'notification_bar_settings',
			array(
				'section'    => 'notification_bar_image_section',
				'label'      => __( 'Notification Bar Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/notification-bar-settings.png',
                    ),
				)
			)
		)
	);

    /** Performance Settings */
    $wp_customize->add_section(
        'performance_image_section',
        array(
            'title'    => __( 'Performance Settings', 'yummy-bites' ),
            'priority' => 76
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'performance_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'performance_text',
            array(
                'section'     => 'performance_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'performance_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'performance_settings',
			array(
				'section'    => 'performance_image_section',
				'label'      => __( 'Performance Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/performance-settings.png',
                    ),
				)
			)
		)
	);

    /** Custom Scripts */
    $wp_customize->add_section(
        'custom_scripts_image_section',
        array(
            'title'    => __( 'Custom scripts', 'yummy-bites' ),
            'priority' => 77
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'custom_scripts_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'custom_scripts_text',
            array(
                'section'     => 'custom_scripts_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'custom_scripts_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'custom_scripts_settings',
			array(
				'section'    => 'custom_scripts_image_section',
				'label'      => __( 'Custom Scripts Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/custom-scripts.png',
                    ),
				)
			)
		)
	);

    /** Customizer Reset */
    $wp_customize->add_section(
        'customizer_reset_image_section',
        array(
            'title'    => __( 'Customizer Reset', 'yummy-bites' ),
            'priority' => 78
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'customizer_reset_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'customizer_reset_text',
            array(
                'section'     => 'customizer_reset_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'customizer_reset_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'customizer_reset_settings',
			array(
				'section'    => 'customizer_reset_image_section',
				'label'      => __( 'Customizer Reset Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/customizer-reset-settings.png',
                    ),
				)
			)
		)
	);

}
if( !yummy_bites_pro_is_activated() ){
    add_action('customize_register', 'yummy_bites_customize_register_additional_settings' );
}
