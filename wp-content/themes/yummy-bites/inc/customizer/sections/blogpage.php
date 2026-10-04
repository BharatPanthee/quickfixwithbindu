<?php
/**
 * Yummy Bites BlogPage Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_blogpage_settings' ) ) : 

function yummy_bites_customize_register_blogpage_settings( $wp_customize ) {
    $defaults      = yummy_bites_get_general_defaults();

    $wp_customize->add_section(
        'blogpage_settings',
        array(
            'title'      => __( 'Blog Page', 'yummy-bites' ),
            'priority'   => 50,
        )
    );

    /** Note */
    $wp_customize->add_setting(
        'blogpage_title_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
			$wp_customize,
			'blogpage_title_text',
			array(
				'section' => 'blogpage_settings',
				'label'   => sprintf(__( '%1$sPage Title%2$s', 'yummy-bites' ), '<span class="trp-customizer-title">', '</span>'),
			)
		)
    );

    /** Page Title */
    $wp_customize->add_setting(
        'ed_blog_title',
        array(
            'default'           => $defaults['ed_blog_title'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_blog_title',
			array(
				'section' => 'blogpage_settings',
				'label'   => __( 'Show Title', 'yummy-bites' ),
			)
		)
	);

    /** Blog Page description */
    $wp_customize->add_setting(
        'ed_blog_desc',
        array(
            'default'           => $defaults['ed_blog_desc'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'ed_blog_desc',
			array(
				'section' => 'blogpage_settings',
				'label'   => __( 'Show Description', 'yummy-bites' )
			)
		)
	);

    /** Page title alignment */
    $wp_customize->add_setting( 
        'blog_alignment', 
        array(
            'default'           => $defaults['blog_alignment'],
            'sanitize_callback' => 'yummy_bites_sanitize_radio',
            'transport'         => 'postMessage'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Buttonset_Control(
			$wp_customize,
			'blog_alignment',
			array(
				'section'	  => 'blogpage_settings',
				'label'       => __( 'Horizontal Alignment', 'yummy-bites' ),
				'choices'	  => array(
					'left'   => __( 'Left', 'yummy-bites' ),
					'center' => __( 'Center', 'yummy-bites' ),
					'right'  => __( 'Right', 'yummy-bites' ),
				),
			)
		)
	);

    /** Crop Feature Image */
    $wp_customize->add_setting(
        'blog_crop_image',
        array(
            'default'           => $defaults['blog_crop_image'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox'
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'blog_crop_image',
			array(
				'section'     => 'blogpage_settings',
				'label'       => __( 'Crop Featured Image', 'yummy-bites' ),
				'description' => __( 'This setting crops the featured image to recommended size. If set to false, it displays the image exactly as uploaded.', 'yummy-bites' )
            )
		)
	);

    /** Note */
    $wp_customize->add_setting(
        'blogpage_post_set_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
			$wp_customize,
			'blogpage_post_set_text',
			array(
				'section' => 'blogpage_settings',
				'label'   => sprintf(__( '%1$sPosts Settings%2$s', 'yummy-bites' ), '<span class="trp-customizer-title">', '</span>'),
			)
		)
    );

    /** Blog Excerpt */
    $wp_customize->add_setting( 
        'blog_content', 
        array(
            'default'           => $defaults['blog_content'],
            'sanitize_callback' => 'yummy_bites_sanitize_radio'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Radio_Buttonset_Control( 
			$wp_customize,
			'blog_content',
			array(
				'section'     => 'blogpage_settings',
				'label'	      => __( 'Enable Blog Excerpt', 'yummy-bites' ),
                'choices'	  => array(
					'excerpt' => __( 'Excerpt', 'yummy-bites' ),
					'content' => __( 'Full Content', 'yummy-bites' )
				),
			)
		)
	);
    
    /** Excerpt Length */
    $wp_customize->add_setting( 
        'excerpt_length', 
        array(
            'default'           => $defaults['excerpt_length'],
            'sanitize_callback' => 'yummy_bites_sanitize_number_absint'
        ) 
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Range_Slider_Control( 
			$wp_customize,
			'excerpt_length',
			array(
				'section'	  => 'blogpage_settings',
				'label'		  => __( 'Excerpt Length', 'yummy-bites' ),
				'description' => __( 'Automatically generated excerpt length (in words).', 'yummy-bites' ),
                'settings'      => array(
                    'desktop' => 'excerpt_length',
                ),
                'choices'	  => array(
                    'desktop' => array(
                        'min' 	=> 10,
                        'max' 	=> 100,
                        'step'	=> 5,
                        'edit'  => true,
                        'unit'  => ''
                    )
				)                 
			)
		)
	);
    
    /** Meta Order */
    $wp_customize->add_setting(
		'blog_meta_order', 
		array(
			'default'           => $defaults['blog_meta_order'], 
			'sanitize_callback' => 'yummy_bites_sanitize_sortable',
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Sortable_Control(
			$wp_customize,
			'blog_meta_order',
			array(
				'section' => 'blogpage_settings',
				'label'   => __( 'Meta Order', 'yummy-bites' ),
				'choices' => array(
                    'author'       => __( 'Author', 'yummy-bites' ),
                    'date'         => __( 'Date', 'yummy-bites' ),
                    'comment'      => __( 'Comment', 'yummy-bites' ),
                    'reading-time' => __( 'Reading Time', 'yummy-bites' )
                ),
			)
		)
    );

    /** Read More Text */
    $wp_customize->add_setting(
        'read_more_text',
        array(
            'default'           => $defaults['read_more_text'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage' 
        )
    );

    $wp_customize->add_control(
        'read_more_text',
        array(
            'type'    => 'text',
            'section' => 'blogpage_settings',
            'label'   => __( 'Read More Label', 'yummy-bites' ),
        )
    );
    
    $wp_customize->selective_refresh->add_partial( 'read_more_text', array(
        'selector' => '.entry-footer .btn-tertiary',
        'render_callback' => 'yummy_bites_get_read_more',
    ) );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_blogpage_settings' );