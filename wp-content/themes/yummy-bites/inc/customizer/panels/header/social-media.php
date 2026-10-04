<?php
/**
 * Header Social Media Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_header_social_media' ) ) : 

function yummy_bites_customize_register_header_social_media( $wp_customize ) {    
    
    $defaults = yummy_bites_get_general_defaults();

    /** Social Media Settings */
    $wp_customize->add_section(
        'social_media_settings',
        array(
            'title'    => __( 'Social Media Settings', 'yummy-bites' ),
            'priority' => 20,
            'panel'    => 'main_header_settings',
        )
    );
    
    /** Enable Social Links */
    $wp_customize->add_setting( 
        'ed_social_links', 
        array(
            'default'           => $defaults['ed_social_links'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_social_links',
			array(
				'section'     => 'social_media_settings',
				'label'	      => __( 'Show Social Media', 'yummy-bites' )
			)
		)
	);
    
    $wp_customize->add_setting( 
        'ed_social_links_new_tab', 
        array(
            'default'           => $defaults['ed_social_links_new_tab'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_social_links_new_tab',
			array(
				'section'         => 'social_media_settings',
				'label'           => __( 'Open in a new tab', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_social_media_ac'
			)
		)
	);

    $wp_customize->add_setting(
		'social_media_order', 
		array(
			'default'           => $defaults['social_media_order'], 
			'sanitize_callback' => 'yummy_bites_sanitize_sortable',
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Sortable_Control(
			$wp_customize,
			'social_media_order',
			array(
				'section'     => 'social_media_settings',
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
                    'yummy_telegram'    => __( 'Telegram', 'yummy-bites')
                ),
                'active_callback' => 'yummy_bites_social_media_ac'
			)
		)
    );

    $wp_customize->add_setting(
        'header_social_media_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'header_social_media_text',
            array(
                'section'         => 'social_media_settings',
                'description'     => sprintf(__( 'You can add links to your social media profiles %1$s here. %2$s', 'yummy-bites' ), '<span class="text-inner-link social_media_text">', '</span>'),
                'active_callback' => 'yummy_bites_social_media_ac'
            )
        )
    );

    /** Social Media Settings Ends */
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_header_social_media' );