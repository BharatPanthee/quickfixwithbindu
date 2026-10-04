<?php 
/**
 * CTA Section
 *
 * @param obj $wp_customize
 * @return void
 */

function yummy_bites_customize_register_front_page_cta( $wp_customize ){

    /** CTA Section Settings */
    $wp_customize->add_section(
        'cta_image_section',
        array(
            'title'    => __( 'Call To Action Section', 'yummy-bites' ),
            'panel'    => 'frontpage_settings',
            'priority' => 68
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'cta_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'cta_text',
            array(
                'section'     => 'cta_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );
    
    $wp_customize->add_setting( 
        'cta_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'cta_settings',
			array(
				'section'    => 'cta_image_section',
				'label'      => __( 'Call To Action Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/cta-section-settings.png',
                    ),
				)
			)
		)
	);

}
if( !yummy_bites_pro_is_activated() ){
    add_action('customize_register', 'yummy_bites_customize_register_front_page_cta' );
}