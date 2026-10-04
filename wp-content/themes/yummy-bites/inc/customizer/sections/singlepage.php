<?php
/**
 * Yummy Bites Single Page Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_singlepage_settings' ) ) : 

function yummy_bites_customize_register_singlepage_settings( $wp_customize ) {
    $defaults      = yummy_bites_get_general_defaults();

    $wp_customize->add_section(
        'singlepage_settings',
        array(
            'title'      => __( 'Page', 'yummy-bites' ),
            'priority'   => 60,
        )
    );

    /** Page Title */
    $wp_customize->add_setting(
        'ed_page_title',
        array(
            'default'           => $defaults['ed_page_title'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_page_title',
			array(
				'section' => 'singlepage_settings',
				'label'   => __( 'Page Title', 'yummy-bites' ),
			)
		)
	);

    /** Page title alignment */
    $wp_customize->add_setting( 
        'pagetitle_alignment', 
        array(
            'default'           => $defaults['pagetitle_alignment'],
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            'transport'         => 'postMessage'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Buttonset_Control(
			$wp_customize,
			'pagetitle_alignment',
			array(
				'section'	  => 'singlepage_settings',
				'label'       => __( 'Title Alignment', 'yummy-bites' ),
				'choices'	  => array(
					'left'   => __( 'Left', 'yummy-bites' ),
					'center' => __( 'Center', 'yummy-bites' ),
					'right'  => __( 'Right', 'yummy-bites' ),
				),
			)
		)
	);

    /** Show Featured Image */
    $wp_customize->add_setting( 
        'ed_page_featured_image', 
        array(
            'default'           => $defaults['ed_page_featured_image'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_page_featured_image',
			array(
				'section'     => 'singlepage_settings',
				'label'	      => __( 'Show Featured Image', 'yummy-bites' )
			)
		)
	);

    $wp_customize->add_setting(
        'ed_page_comments',
        array(
            'default'           => $defaults['ed_page_comments'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_page_comments',
			array(
				'section' => 'singlepage_settings',
				'label'   => __( 'Show Comments', 'yummy-bites' ),
			)
		)
	);
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_singlepage_settings' );