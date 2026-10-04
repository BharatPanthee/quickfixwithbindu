<?php
/**
 * Blog Settings
 *
 * @package Yummy_Bites
 */

 function yummy_bites_customize_register_blog( $wp_customize ){
    $defaults = yummy_bites_get_general_defaults();

    /** Blog Section */
    $wp_customize->add_section(
        'blog_section',
        array(
            'title'    => __( 'Blog Section', 'yummy-bites' ),
            'priority' => 45,
            'panel'    => 'frontpage_settings',
        )
    );

    $wp_customize->add_setting(
        'blog_main_title',
        array(
            'default'           => $defaults['blog_main_title'],
            'sanitize_callback' => 'sanitize_text_field', 
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'blog_main_title',
        array(
            'label'   => __( 'Blog Title', 'yummy-bites' ),
            'section' => 'blog_section',
            'type'    => 'text',
        )
    );

    $wp_customize->selective_refresh->add_partial( 'blog_main_title', array(
        'selector'        => '#blog_section h2.section-title',
        'render_callback' => 'yummy_bites_get_blog_main_title',
    ) );

    $wp_customize->add_setting(
        'blog_main_content',
        array(
            'default'           => $defaults['blog_main_content'],
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage' 
        )
    );
    
    $wp_customize->add_control(
        'blog_main_content',
        array(
            'label'   => __( 'Blog Description', 'yummy-bites' ),
            'section' => 'blog_section',
            'type'    => 'textarea',
        )
    );

    $wp_customize->selective_refresh->add_partial( 'blog_main_content', array(
        'selector'        => '#blog_section .section-desc',
        'render_callback' => 'yummy_bites_get_blog_main_content',
    ) );


    $wp_customize->add_setting(
		'blog_post_options',
		array(
			'default'			=> $defaults['blog_post_options'],
			'sanitize_callback' => 'yummy_bites_sanitize_select'
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Select_Control(
    		$wp_customize,
    		'blog_post_options',
    		array(
                'label'       => __( 'Blog Options', 'yummy-bites' ),
                'description' => __( 'Choose to display Recipes or Blog Posts.', 'yummy-bites' ),
                'section'     => 'blog_section',
                'choices'     => array(
                    'post'   => __( 'Blog Posts', 'yummy-bites' ),
                    'recipe' => __( 'Recipes', 'yummy-bites' ),
                    'both'   => __( 'Blog Posts and Recipes', 'yummy-bites' ),
                ),
			)            
		)
    );


    /** Read More Label */
    $wp_customize->add_setting(
        'blog_readmore',
        array(
            'default'           => $defaults['blog_readmore'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage'
        )
    );
    
    $wp_customize->add_control(
        'blog_readmore',
        array(
            'label'   => __( 'Read More Label', 'yummy-bites' ),
            'section' => 'blog_section',
            'type'    => 'text'
        )
    );

    $wp_customize->selective_refresh->add_partial( 'blog_readmore', array(
        'selector'        => '#blog_section article .entry-footer a.btn-secondary',
        'render_callback' => 'yummy_bites_get_blog_readmore',
    ) );

    $wp_customize->add_setting(
        'blog_post_per_page',
        array(
            'default'           => $defaults['blog_post_per_page'],
            'sanitize_callback' => 'yummy_bites_sanitize_empty_absint',
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Range_Slider_Control(
            $wp_customize,
            'blog_post_per_page',
            array(
                'label'    => __( 'Posts Per Page', 'yummy-bites' ),
                'section'  => 'blog_section',
                'settings' => array(  'desktop' => 'blog_post_per_page' ),
                'choices'  => array(
                    'desktop' => array(
                        'min'  => 4,
                        'max'  => 20,
                        'step' => 1,
                        'edit' => true,
                    ),
                ),
            )
        )
    );

    $wp_customize->add_setting(
        'blog_sidebar_texts',
        array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post' 
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Note_Control( 
            $wp_customize,
            'blog_sidebar_texts',
            array(
                'section'     => 'blog_section',
                'description' => sprintf( __( '%1$sClick here%2$s to add widgets in the sidebar.', 'yummy-bites' ), '<span class="text-inner-link blog_sidebar_texts">', '</span>' ),
                'priority'    => 60,
            )
        )
    );
}
add_action( 'customize_register', 'yummy_bites_customize_register_blog' );