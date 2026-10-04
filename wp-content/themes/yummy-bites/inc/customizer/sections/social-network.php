<?php
/**
 * Social Media Settings
 *
 * @package Yummy Bites
*/
if ( ! function_exists( 'yummy_bites_customize_register_social_network' ) ) : 

function yummy_bites_customize_register_social_network( $wp_customize ){

    $defaults = yummy_bites_get_general_defaults();

    /** Social Media */
    $wp_customize->add_section( 
        'social_network_section',
        array(
            'priority' => 31,
            'title'    => __( 'Social Media', 'yummy-bites' ),
        ) 
    );

    $wp_customize->add_setting(
        'social_network_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'social_network_text',
            array(
                'section'     => 'social_network_section',
                'label'       => __('Social Media Accounts', 'yummy-bites'),
                'description' => __( 'Add the links to your social media accounts and display them across your site.', 'yummy-bites' ),
            )
        )
    );

    /** Facebook */
    $wp_customize->add_setting(
        'yummy_facebook',
        array(
            'default'           => $defaults['yummy_facebook'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_facebook',
        array(
            'section'         => 'social_network_section',
            'label'           => __( 'Facebook', 'yummy-bites' ),
            'type'            => 'text'
        )
	);

    /** Twitter */
    $wp_customize->add_setting(
        'yummy_twitter',
        array(
            'default'           => $defaults['yummy_twitter'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_twitter',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Twitter', 'yummy-bites' ),
        )
	);

     /** Instagram */
     $wp_customize->add_setting(
        'yummy_instagram',
        array(
            'default'           => $defaults['yummy_instagram'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_instagram',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Instagram', 'yummy-bites' ),
        )
	);

    /** Pinterest */
    $wp_customize->add_setting(
        'yummy_pinterest',
        array(
            'default'           => $defaults['yummy_pinterest'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_pinterest',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Pinterest', 'yummy-bites' ),
        )
	);

    /** YouTube  */
    $wp_customize->add_setting(
        'yummy_youtube',
        array(
            'default'           => $defaults['yummy_youtube'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_youtube',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'YouTube', 'yummy-bites' ),
        )
	);

    /** TikTok  */
    $wp_customize->add_setting(
        'yummy_tiktok',
        array(
            'default'           => $defaults['yummy_tiktok'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_tiktok',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'TikTok', 'yummy-bites' ),
        )
	);

    /** Linkedin */
    $wp_customize->add_setting(
        'yummy_linkedin',
        array(
            'default'           => $defaults['yummy_linkedin'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_linkedin',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Linkedin', 'yummy-bites' ),
        )
	);

    /** Whatsapp */
    $wp_customize->add_setting(
        'yummy_whatsapp',
        array(
            'default'           => $defaults['yummy_whatsapp'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_whatsapp',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Whatsapp', 'yummy-bites' ),
        )
	);

    /** Viber */
    $wp_customize->add_setting(
        'yummy_viber',
        array(
            'default'           => $defaults['yummy_viber'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_viber',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Viber', 'yummy-bites' ),
        )
	);

    /** Telegram */
    $wp_customize->add_setting(
        'yummy_telegram',
        array(
            'default'           => $defaults['yummy_telegram'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    
    $wp_customize->add_control(
        'yummy_telegram',
        array(
            'type'            => 'text',
            'section'         => 'social_network_section',
            'label'           => __( 'Telegram', 'yummy-bites' ),
        )
	);

}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_social_network' );