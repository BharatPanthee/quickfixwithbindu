<?php
/**
 * Yummy Bites Archive Page Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_archive_settings' ) ) : 

function yummy_bites_customize_register_archive_settings( $wp_customize ) {
    $defaults      = yummy_bites_get_general_defaults();

    $wp_customize->add_section(
        'archivepage_settings',
        array(
            'title'      => __( 'Archive', 'yummy-bites' ),
            'priority'   => 65,
        )
    );

    /** Page Title */
    $wp_customize->add_setting(
        'ed_archive_title',
        array(
            'default'           => $defaults['ed_archive_title'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_archive_title',
			array(
				'section' => 'archivepage_settings',
				'label'   => __( 'Page Title', 'yummy-bites' ),
			)
		)
	);

    /** Blog Page description */
    $wp_customize->add_setting(
        'ed_archive_desc',
        array(
            'default'           => $defaults['ed_archive_desc'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_archive_desc',
			array(
				'section' => 'archivepage_settings',
				'label'   => __( 'Show Description', 'yummy-bites' )
			)
		)
	);

    /** Prefix Archive Page */
    $wp_customize->add_setting( 
        'ed_prefix_archive', 
        array(
            'default'           => $defaults['ed_prefix_archive'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_prefix_archive',
			array(
				'section'     => 'archivepage_settings',
				'label'	      => __( 'Hide Archive Prefix', 'yummy-bites' )
			)
		)
	);

    /** Show counts */
    $wp_customize->add_setting( 
        'ed_archive_post_count', 
        array(
            'default'           => $defaults['ed_archive_post_count'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        ) 
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Toggle_Control( 
            $wp_customize,
            'ed_archive_post_count',
            array(
                'section'     => 'archivepage_settings',
                'label'	      => __( 'Show Counts', 'yummy-bites' )
            )
        )
    );

    /** Page title alignment */
    $wp_customize->add_setting( 
        'archivetitle_alignment', 
        array(
            'default'           => $defaults['archivetitle_alignment'],
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            'transport'         => 'postMessage'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Buttonset_Control(
			$wp_customize,
			'archivetitle_alignment',
			array(
				'section'	  => 'archivepage_settings',
				'label'       => __( 'Title Alignment', 'yummy-bites' ),
				'choices'	  => array(
					'left'   => __( 'Left', 'yummy-bites' ),
					'center' => __( 'Center', 'yummy-bites' ),
					'right'  => __( 'Right', 'yummy-bites' ),
				),
			)
		)
	);
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_archive_settings' );