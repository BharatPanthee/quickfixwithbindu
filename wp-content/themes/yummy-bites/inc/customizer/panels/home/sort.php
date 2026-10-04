<?php 
/**
 * Sort Section
 *
 * @param obj $wp_customize
 * @return void
 */

function yummy_bites_customize_register_front_page_sort( $wp_customize ){

    /** Sort Front Page Settings */
    $wp_customize->add_section(
        'sort_image_section',
        array(
            'title'    => __( 'Sort Front Page Section', 'yummy-bites' ),
            'panel'    => 'frontpage_settings',
            'priority' => 121
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'sort_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'sort_text',
            array(
                'section'     => 'sort_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );
    
    $wp_customize->add_setting( 
        'sort_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'sort_settings',
			array(
				'section'	  => 'sort_image_section',
				'label'		  => __( 'Sort Front Page Settings', 'yummy-bites' ),
                'col' => 'col-1',
                'feat_class' => 'upg-to-pro',
				'choices'	  => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/sort-section-settings.png',
                    ),
				)
			)
		)
	);

}
if( !yummy_bites_pro_is_activated() ){
    add_action('customize_register', 'yummy_bites_customize_register_front_page_sort' );
}