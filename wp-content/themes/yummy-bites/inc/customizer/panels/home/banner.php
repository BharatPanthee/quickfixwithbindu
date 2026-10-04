<?php
/**
 * Front Page Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_banner_settings' ) ) : 

function yummy_bites_customize_register_banner_settings( $wp_customize ) {    
    $defaults = yummy_bites_get_banner_defaults();

	/** Title */
    $wp_customize->add_section(
        'banner_section',
        array(
            'title'    => __( 'Banner Section', 'yummy-bites' ),
            'priority' => 10,
            'panel'    => 'frontpage_settings'
        )
    );
    

    /** Banner Options */
    $wp_customize->add_setting(
		'ed_banner_section',
		array(
			'default'			=> $defaults['ed_banner_section'],
			'sanitize_callback' => 'yummy_bites_sanitize_select'
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Select_Control(
    		$wp_customize,
    		'ed_banner_section',
    		array(
                'label'       => __( 'Banner Options', 'yummy-bites' ),
                'description' => __( 'Choose banner as static image/video or as a slider.', 'yummy-bites' ),
                'section'     => 'banner_section',
                'choices'     => array(
                    'no_banner'            => __( 'Disable Banner Section', 'yummy-bites' ),
                    'slider_banner'        => __( 'Banner as Slider', 'yummy-bites' ),
                ),
                'priority' => 5
			)            
		)
    );

	/** Slider Content Style */
    $wp_customize->add_setting(
		'slider_type',
		array(
			'default'			=> $defaults['slider_type'],
			'sanitize_callback' => 'yummy_bites_sanitize_select'
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Select_Control(
    		$wp_customize,
    		'slider_type',
    		array(
                'label'           => __( 'Slider Content Style', 'yummy-bites' ),
                'section'         => 'banner_section',
                'choices'         => yummy_bites_slider_options(),
                'active_callback' => 'yummy_bites_banner_ac',
				'priority' => 10
			)
		)
	);
    
    /** Slider Category */
    $wp_customize->add_setting(
		'slider_cat',
		array(
			'default'			=> $defaults['slider_cat'],
			'sanitize_callback' => 'yummy_bites_sanitize_select'
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Select_Control(
    		$wp_customize,
    		'slider_cat',
    		array(
                'label'           => __( 'Slider Category', 'yummy-bites' ),
                'section'         => 'banner_section',
                'choices'         => yummy_bites_get_categories(),
                'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 0
            )
		)
	);

	/** No. of slides */
    $wp_customize->add_setting(
        'no_of_slides',
        array(
            'default'           => $defaults['no_of_slides'],
            'sanitize_callback' => 'yummy_bites_sanitize_number_absint'
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Slider_Control( 
			$wp_customize,
			'no_of_slides',
			array(
				'section'     => 'banner_section',
                'label'       => __( 'Number of Slides', 'yummy-bites' ),
                'description' => __( 'Choose the number of slides you want to display', 'yummy-bites' ),
                'choices'	  => array(
					'min' 	=> 1,
					'max' 	=> 20,
					'step'	=> 1,
				),             
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 50,
			)
		)
	);
    
    /** Slider Auto */
    $wp_customize->add_setting(
        'slider_auto',
        array(
            'default'           => $defaults['slider_auto'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'slider_auto',
			array(
				'section'         => 'banner_section',
				'label'           => __( 'Autoplay Slider', 'yummy-bites' ),
				'description'     => __( 'Enable slider auto transition.', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 80,
			)
		)
	);
    
    /** Slider Loop */
    $wp_customize->add_setting(
        'slider_loop',
        array(
            'default'           => $defaults['slider_loop'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'slider_loop',
			array(
				'section'         => 'banner_section',
				'label'           => __( 'Loop Slider', 'yummy-bites' ),
				'description'     => __( 'Enable slider loop.', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 90,
			)
		)
	);
    
    /** Slider Caption */
    $wp_customize->add_setting(
        'slider_caption',
        array(
            'default'           => $defaults['slider_caption'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'slider_caption',
			array(
				'section'         => 'banner_section',
				'label'           => __( 'Slider Caption', 'yummy-bites' ),
				'description'     => __( 'Enable slider caption.', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 100,
			)
		)
	);
    
    /** Full Image */
    $wp_customize->add_setting(
        'slider_full_image',
        array(
            'default'           => $defaults['slider_full_image'],
            'sanitize_callback' => 'yummy_bites_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
		new Yummy_Bites_Toggle_Control( 
			$wp_customize,
			'slider_full_image',
			array(
				'section'         => 'banner_section',
				'label'           => __( 'Full Image', 'yummy-bites' ),
                'description'     => __( 'Enable to use full size image in slider.', 'yummy-bites' ),
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 110,
			)
		)
	);
    
    /** Slider Animation */
    $wp_customize->add_setting(
		'slider_animation',
		array(
			'default'			=> $defaults['slider_animation'],
			'sanitize_callback' => 'yummy_bites_sanitize_select'
		)
	);

	$wp_customize->add_control(
		new Yummy_Bites_Select_Control(
    		$wp_customize,
    		'slider_animation',
    		array(
                'label'	      => __( 'Slider Animation', 'yummy-bites' ),
                'section'     => 'banner_section',
    			'choices'     => array(
                    'bounceOut'      => __( 'Bounce Out', 'yummy-bites' ),
                    'bounceOutLeft'  => __( 'Bounce Out Left', 'yummy-bites' ),
                    'bounceOutRight' => __( 'Bounce Out Right', 'yummy-bites' ),
                    'bounceOutUp'    => __( 'Bounce Out Up', 'yummy-bites' ),
                    'bounceOutDown'  => __( 'Bounce Out Down', 'yummy-bites' ),
                    'fadeOut'        => __( 'Fade Out', 'yummy-bites' ),
                    'fadeOutLeft'    => __( 'Fade Out Left', 'yummy-bites' ),
                    'fadeOutRight'   => __( 'Fade Out Right', 'yummy-bites' ),
                    'fadeOutUp'      => __( 'Fade Out Up', 'yummy-bites' ),
                    'fadeOutDown'    => __( 'Fade Out Down', 'yummy-bites' ),
                    'flipOutX'       => __( 'Flip OutX', 'yummy-bites' ),
                    'flipOutY'       => __( 'Flip OutY', 'yummy-bites' ),
                    'hinge'          => __( 'Hinge', 'yummy-bites' ),
                    'pulse'          => __( 'Pulse', 'yummy-bites' ),
                    'rollOut'        => __( 'Roll Out', 'yummy-bites' ),
                    'rotateOut'      => __( 'Rotate Out', 'yummy-bites' ),
                    'rubberBand'     => __( 'Rubber Band', 'yummy-bites' ),
                    'shake'          => __( 'Shake', 'yummy-bites' ),
                    'slide'          => __( 'Slide', 'yummy-bites' ),
                    'slideOutLeft'   => __( 'Slide Out Left', 'yummy-bites' ),
                    'slideOutRight'  => __( 'Slide Out Right', 'yummy-bites' ),
                    'slideOutUp'     => __( 'Slide Out Up', 'yummy-bites' ),
                    'slideOutDown'   => __( 'Slide Out Down', 'yummy-bites' ),
                    'swing'          => __( 'Swing', 'yummy-bites' ),
                    'tada'           => __( 'Tada', 'yummy-bites' ),
                    'zoomOut'        => __( 'Zoom Out', 'yummy-bites' ),
                    'zoomOutLeft'    => __( 'Zoom Out Left', 'yummy-bites' ),
                    'zoomOutRight'   => __( 'Zoom Out Right', 'yummy-bites' ),
                    'zoomOutUp'      => __( 'Zoom Out Up', 'yummy-bites' ),
                    'zoomOutDown'    => __( 'Zoom Out Down', 'yummy-bites' ),
                ),  
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 120,                             	
			)
		)
    );
    
    /** Slider Speed */
    $wp_customize->add_setting(
        'slider_speed',
        array(
            'default'           => $defaults['slider_speed'],
            'sanitize_callback' => 'yummy_bites_sanitize_number_absint'
        )
    );
    
    $wp_customize->add_control(
        new Yummy_Bites_Slider_Control( 
            $wp_customize,
            'slider_speed',
            array(
                'section'     => 'banner_section',
                'label'       => __( 'Slider Speed', 'yummy-bites' ),
                'description' => __( 'Controls the speed of slider in miliseconds.', 'yummy-bites' ),
                'choices'     => array(
                    'min'  => 1000,
                    'max'  => 20000,
                    'step' => 500,
                ),
				'active_callback' => 'yummy_bites_banner_ac',
                'priority'        => 130,
            )
        )
    );

    /** Read More Text */
    $wp_customize->add_setting(
        'slider_readmore',
        array(
            'default'           => $defaults['slider_readmore'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage' 
        )
    );
    
    $wp_customize->add_control(
        'slider_readmore',
        array(
            'type'            => 'text',
            'section'         => 'banner_section',
            'label'           => __( 'Slider Read More', 'yummy-bites' ),
            'active_callback' => 'yummy_bites_banner_ac',
            'priority'        => 140,
        )
    );
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_banner_settings' );

if ( ! function_exists( 'yummy_bites_slider_options' ) ) :
    /**
     * @return array Content type options
     */
    function yummy_bites_slider_options() {
        $slider_options = array(
            'latest_posts' => __( 'Latest Posts', 'yummy-bites' ),
            'cat'          => __( 'Category', 'yummy-bites' ),
        );
        if ( yummy_bites_is_delicious_recipe_activated() ) {
            $slider_options = array_merge( $slider_options, array( 'latest_recipes' => __( 'Latest Recipes', 'yummy-bites' ) ) );
        }
        $output = apply_filters( 'yummy_bites_slider_options', $slider_options );
        return $output;
    }
endif;