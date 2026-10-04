<?php
/**
 * About Section
 *
 * @package Yummy_Bites
 */

function yummy_bites_customizer_register_frontpage_about( $wp_customize ){

    $defaults       = yummy_bites_get_general_defaults();
    $color_defaults = yummy_bites_get_color_defaults();

    /** About Section */
    $wp_customize->add_section(
        'about_section',
        array(
            'title'    => __( 'About Section', 'yummy-bites' ),
            'priority' => 60,
            'panel'    => 'frontpage_settings',
        )
    );

    $wp_customize->add_setting(
        'about_section_tab_settings',
        array(
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Tabs_Control(
            $wp_customize, 'about_section_tab_settings', array(
            'section' => 'about_section',
                'tabs'    => array(
                    'general' => array(
                        'nicename' => esc_html__( 'General', 'yummy-bites' ),
                        'controls' => array(
                            'ed_about_section',
                            'about_section_alignment',
                            'abt_title',
                            'abt_description',
                            'abt_author_image',
                            'abt_bg_image',
                            'ed_about_social_links',
                            'ed_about_social_links_new_tab',
                            'about_social_media_order',
                            'about_social_media_text',
                            'abt_button_label',
                            'abt_button_link',
                        ),
                    ),
                    'design' => array(
                        'nicename' => esc_html__( 'Design', 'yummy-bites' ),
                        'controls' => array(
                            'abt_bg_color',
                            'abt_title_color',
                            'abt_desc_color',
                        ),
                    )
                ),
            )
        )
    );

    /* About Section Enable/Disable */
    $wp_customize->add_setting( 
        'ed_about_section', 
        array(
            'default'           => true,
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        ) 
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Toggle_Control(
            $wp_customize,
            'ed_about_section',
            array(
                'section'     => 'about_section',
                'label'       => __( 'Show About Section', 'yummy-bites' )
            )
        )
    );


    /** Page title alignment */
    $wp_customize->add_setting( 
        'about_section_alignment', 
        array(
            'default'           => 'right',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Buttonset_Control(
			$wp_customize,
			'about_section_alignment',
			array(
				'section'	  => 'about_section',
				'label'       => __( 'Horizontal Alignment', 'yummy-bites' ),
				'choices'	  => array(
					'left'   => __( 'Left', 'yummy-bites' ),
					'right'  => __( 'Right', 'yummy-bites' ),
				),
			)
		)
	);

    $wp_customize->add_setting(
        'abt_title',
        array(
            'default'           => $defaults['abt_title'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );

    $wp_customize->add_control(
        'abt_title',
        array(
            'label'   => esc_html__( 'Section Title', 'yummy-bites' ),
            'section' => 'about_section',
            'type'    => 'text',
        )
    );

    $wp_customize->selective_refresh->add_partial( 'abt_title', array(
        'selector'        => '#about_section h2.section-title',
        'render_callback' => 'yummy_bites_get_abt_title',
    ) );

    $wp_customize->add_setting(
        'abt_description',
        array(
            'default'           => $defaults['abt_description'],
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage'
        )
    );

    $wp_customize->add_control(
        'abt_description',
        array(
            'label'   => esc_html__( 'Section Description', 'yummy-bites' ),
            'section' => 'about_section',
            'type'    => 'textarea',
        )
    );

    $wp_customize->selective_refresh->add_partial( 'abt_description', array(
        'selector'        => '#about_section .section-subtitle',
        'render_callback' => 'yummy_bites_get_abt_description',
    ) );

    /** Author Image  */
    $wp_customize->add_setting( 'abt_author_image',
        array(
            'default'           => $defaults['abt_author_image'],
            'sanitize_callback' => 'yummy_bites_sanitize_image',
        )
    );
    
    $wp_customize->add_control( 
        new WP_Customize_Image_Control( $wp_customize, 'abt_author_image',
            array(
                'label'         => esc_html__( 'Author Image', 'yummy-bites' ),
                'description'   => esc_html__( 'Choose author image for about section.', 'yummy-bites' ),
                'section'       => 'about_section',
                'type'          => 'image',
            )
        )
    );

    /** Background Image  */
    $wp_customize->add_setting( 'abt_bg_image',
        array(
            'default'           => $defaults['abt_bg_image'],
            'sanitize_callback' => 'yummy_bites_sanitize_image',
        )
    );
    
    $wp_customize->add_control( 
        new WP_Customize_Image_Control( $wp_customize, 'abt_bg_image',
            array(
                'label'         => esc_html__( 'Background Image', 'yummy-bites' ),
                'description'   => esc_html__( 'Choose background image for about section.', 'yummy-bites' ),
                'section'       => 'about_section',
                'type'          => 'image',
            )
        )
    );

    /** Enable Social Links */
    $wp_customize->add_setting( 
        'ed_about_social_links', 
        array(
            'default'           => $defaults['ed_about_social_links'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_about_social_links',
			array(
				'section'     => 'about_section',
				'label'	      => __( 'Show Social Media', 'yummy-bites' )
			)
		)
	);
    
    $wp_customize->add_setting( 
        'ed_about_social_links_new_tab', 
        array(
            'default'           => $defaults['ed_about_social_links_new_tab'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_about_social_links_new_tab',
			array(
				'section'         => 'about_section',
				'label'           => __( 'Open in a new tab', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_about_social_media_ac',
			)
		)
	);

    $wp_customize->add_setting(
		'about_social_media_order', 
		array(
			'default'           => $defaults['about_social_media_order'], 
			'sanitize_callback' => 'yummy_bites_sanitize_sortable',
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Sortable_Control(
			$wp_customize,
			'about_social_media_order',
			array(
				'section'     => 'about_section',
				'label'       => __( 'Social Media', 'yummy-bites' ),
				'choices'     => array(
                    'yummy_facebook'    => __( 'Facebook', 'yummy-bites'),
                    'yummy_twitter'     => __( 'Twitter', 'yummy-bites'),
                    'yummy_instagram'   => __( 'Instagram', 'yummy-bites'),
                    'yummy_pinterest'   => __( 'Pinterest', 'yummy-bites'),
                    'yummy_youtube'     => __( 'Youtube', 'yummy-bites'),
                    'yummy_tiktok'      => __( 'TikTok', 'yummy-bites'),
                    'yummy_linkedin'    => __( 'LinkedIn', 'yummy-bites'),
                    'yummy_whatsapp'    => __( 'WhatsApp', 'yummy-bites'),
                    'yummy_viber'       => __( 'Viber', 'yummy-bites'),
                    'yummy_telegram'    => __( 'Telegram', 'yummy-bites'),
                ),
                'active_callback' => 'yummy_bites_about_social_media_ac',
			)
		)
    );

    $wp_customize->add_setting(
        'about_social_media_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'about_social_media_text',
            array(
                'section'         => 'about_section',
                'description'     => sprintf(__( 'You can add links to your social media profiles %1$s here. %2$s', 'yummy-bites' ), '<span class="text-inner-link about_social_media_text">', '</span>'),
                'active_callback' => 'yummy_bites_about_social_media_ac'
            )
        )
    );

    $wp_customize->add_setting(
        'abt_button_label',
        array(
            'default'           => $defaults['abt_button_label'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );

    $wp_customize->add_control(
        'abt_button_label',
        array(
            'label'   => esc_html__( 'Button Label', 'yummy-bites' ),
            'section' => 'about_section',
            'type'    => 'text',
        )
    );

    $wp_customize->selective_refresh->add_partial( 'abt_button_label', array(
        'selector'        => '#about_section .btn-wrapper a.btn-primary',
        'render_callback' => 'yummy_bites_get_abt_button_label',
    ) );

    $wp_customize->add_setting(
        'abt_button_link',
        array(
            'default'           => $defaults['abt_button_link'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        'abt_button_link',
        array(
            'label'   => esc_html__( 'Button Link', 'yummy-bites' ),
            'section' => 'about_section',
            'type'    => 'text',
        )
    );

    /** About title Color */
    $wp_customize->add_setting(
        'abt_title_color',
        array(
            'default'           => $color_defaults['abt_title_color'],
            'transport'         => 'postMessage',
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
        )
    );

    $wp_customize->add_control(
        new Yummy_Bites_Alpha_Color_Customize_Control(
            $wp_customize,
            'abt_title_color',
            array(
                'label'    => __( 'Title Color', 'yummy-bites' ),
                'section'  => 'about_section'
            )
        )
    );

    /** About Description Color */
    $wp_customize->add_setting(
        'abt_desc_color',
        array(
            'default'           => $color_defaults['abt_desc_color'],
            'transport'         => 'postMessage',
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
        )
    );

    $wp_customize->add_control(
        new Yummy_Bites_Alpha_Color_Customize_Control(
            $wp_customize,
            'abt_desc_color',
            array(
                'label'    => __( 'Description Color', 'yummy-bites' ),
                'section'  => 'about_section'
            )
        )
    );

    /** Background Color*/
    $wp_customize->add_setting( 
        'abt_bg_color', 
        array(
            'default'           =>  $color_defaults['abt_bg_color'],
            'sanitize_callback' => 'yummy_bites_sanitize_rgba',
            'transport'         => 'postMessage',
        ) 
    );

    $wp_customize->add_control( 
        new Yummy_Bites_Alpha_Color_Customize_Control( 
            $wp_customize, 
            'abt_bg_color', 
            array(
                'label'    => __( 'Background Color', 'yummy-bites' ),
                'section'  => 'about_section',
                'priority' => 10,
            )
        )
    );

}
add_action( 'customize_register', 'yummy_bites_customizer_register_frontpage_about' );