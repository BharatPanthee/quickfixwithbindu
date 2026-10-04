<?php
/**
 * Yummy Bites SEO Settings
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_seo_settings' ) ) : 

function yummy_bites_customize_register_seo_settings( $wp_customize ) { 

    $defaults = yummy_bites_get_general_defaults();
    
    /** SEO Settings */
    $wp_customize->add_section(
        'seo_settings',
        array(
            'title'    => __( 'SEO Settings', 'yummy-bites' ),
            'priority' => 30,
        )
    );
    
    /** Enable Breadcrumb */
    $wp_customize->add_setting( 
        'ed_breadcrumb', 
        array(
            'default'           => $defaults['ed_breadcrumb'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_breadcrumb',
			array(
				'section'     => 'seo_settings',
				'label'	      => __( 'Show Breadcrumb', 'yummy-bites' )
			)
		)
	);
    
    /** Breadcrumb Home Text */
    $wp_customize->add_setting(
        'home_text',
        array(
            'default'           => $defaults['home_text'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'home_text',
        array(
            'type'            => 'text',
            'section'         => 'seo_settings',
            'label'           => __( 'Breadcrumb Home Text', 'yummy-bites' ),
            'active_callback' => 'yummy_bites_seo_breadcrumb_ac'
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'home_text', array(
        'selector'        => '#crumbs .home-text',
        'render_callback' => 'yummy_bites_get_home_text',
    ) );

    /** Separator Text */
    $wp_customize->add_setting( 
        'separator_icon', 
        array(
            'default'           => $defaults[ 'separator_icon' ],
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            'transport'         => 'postMessage'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Image_Control(
			$wp_customize,
			'separator_icon',
			array(
				'section'     => 'seo_settings',
				'label'       => __( 'Separator', 'yummy-bites' ),
				'svg'         => true,
				'col'         => 'col-3',
				'choices'     => array(
                    'one' => array(
                        'label' => __( 'Type 1', 'yummy-bites' ),
                        'path'  => yummy_bites_breadcrumb_icons_list('one'),
                    ),
                    'two' => array(
                        'label' => __( 'Type 2', 'yummy-bites' ),                        
                        'path'  => yummy_bites_breadcrumb_icons_list('two'),
                    ),
                    'three' => array(
                        'label' => __( 'Type 3', 'yummy-bites' ),
                        'path'  => yummy_bites_breadcrumb_icons_list('three'),
                    )
                ),
                'active_callback' => 'yummy_bites_seo_breadcrumb_ac'
			)
		)
    );

    /** Enable post updated text */
    $wp_customize->add_setting( 
        'ed_post_update_date', 
        array(
            'default'           => $defaults['ed_post_update_date'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_post_update_date',
			array(
				'section'     => 'seo_settings',
				'label'	      => __( 'Show Last Updated Post Date', 'yummy-bites' ),
                'description' => __( 'Enable to show last updated post date on listing as well as in single post.', 'yummy-bites' ),
			)
		)
	);
    
    /** SEO Settings Ends */
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_seo_settings' );