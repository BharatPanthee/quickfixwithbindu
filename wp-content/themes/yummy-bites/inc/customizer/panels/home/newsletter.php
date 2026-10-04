<?php
/**
 * Newsletter Settings
 *
 * @package Yummy Bites
 */

function yummy_bites_customize_register_frontpage_newsletter( $wp_customize ) {
    $wp_customize->add_section(
        'newsletter_settings',
        array(
            'title'    => __( 'Newsletter Section', 'yummy-bites' ),
            'panel'    => 'frontpage_settings',
            'priority' => 40,
        )
    );
	
    if( yummy_bites_is_btnw_activated() ){
		
		$wp_customize->add_setting(
			'newsletter_text',
			array(
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post', 
			)
		);
		
		$wp_customize->add_control(
			new Yummy_Bites_Note_Control( 
				$wp_customize,
				'newsletter_text',
				array(
					'section'  => 'newsletter_settings',
					'label'    => __( 'Add "BlossomThemes: Email Newsletter Widget" for newsletter section.', 'yummy-bites' ),
					'priority' => 8
				)
			)
		);
		
		$newsletter = $wp_customize->get_section( 'sidebar-widgets-newsletter' );
		if ( ! empty( $newsletter ) ) {
			$newsletter->panel           = 'frontpage_settings';
			$newsletter->priority        = 40;
			$wp_customize->get_control( 'newsletter_text' )->section = 'sidebar-widgets-newsletter';

		}

	}else{
		$wp_customize->add_setting(
			'newsletter_recommend',
			array(
				'sanitize_callback' => 'wp_kses_post',
				
			)
		);

		$wp_customize->add_control(
			new Yummy_Bites_Plugin_Recommend_Control(
				$wp_customize,
				'newsletter_recommend',
				array(
					'section'     => 'newsletter_settings',
					'label'       => __( 'Newsletter Shortcode', 'yummy-bites' ),
					'capability'  => 'install_plugins',
					'plugin_slug' => 'blossomthemes-email-newsletter',//This is the slug of recommended plugin.
					'description' => sprintf( __( 'Please install and activate the recommended plugin %1$sBlossomThemes Email Newsletter%2$s. After that option related with this section will be visible.', 'yummy-bites' ), '<strong>', '</strong>' ),
				)
			)
		);
	}    
}
add_action( 'customize_register', 'yummy_bites_customize_register_frontpage_newsletter' );