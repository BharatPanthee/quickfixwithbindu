<?php 
/**
 * Recipe Index Section
 *
 * @param obj $wp_customize
 * @return void
 */

function yummy_bites_customize_register_front_page_recipe_index( $wp_customize ){

    /** Recipe Index Settings */
    $wp_customize->add_section(
        'recipe_index_image_section',
        array(
            'title'    => __( 'Recipe Index Section', 'yummy-bites' ),
            'panel'    => 'frontpage_settings',
            'priority' => 65
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'recipe_index_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'recipe_index_text',
            array(
                'section'     => 'recipe_index_image_section',
                'description' => sprintf( __( '%1$sThis feature is available in Pro version.%2$s %3$sUpgrade to Pro%4$s ', 'yummy-bites' ),'<div class="featured-pro"><span>', '</span>', '<a href="https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=customizer&utm_campaign=upgrade_to_pro" target="_blank">', '</a></div>' ),
            )
        )
    );

    $wp_customize->add_setting( 
        'recipe_index_settings', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'recipe_index_settings',
			array(
				'section'    => 'recipe_index_image_section',
				'label'      => __( 'Recipe Index Settings', 'yummy-bites' ),
				'col'        => 'col-1',
				'feat_class' => 'upg-to-pro',
				'choices'    => array(
					'one' => array(
                        'label' => '',
                        'path'  => get_template_directory_uri() . '/images/recipe-index-section-settings.png',
                    ),
				)
			)
		)
	);

}
if( !yummy_bites_pro_is_activated() ){
    add_action('customize_register', 'yummy_bites_customize_register_front_page_recipe_index');
}