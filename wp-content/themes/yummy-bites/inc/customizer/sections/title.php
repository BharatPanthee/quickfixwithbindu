<?php
/**
 * Title Setting
 *
 * @package Yummy Bites
 */
if ( ! function_exists( 'yummy_bites_customize_register_title_section' ) ) : 

function yummy_bites_customize_register_title_section( $wp_customize ) {

    $wp_customize->add_section(
		new Yummy_Bites_Group_Title(
			$wp_customize,
			'core',
            array(
                'title'    => __( 'Core', 'yummy-bites' ),
                'priority' => 99,
            )
		)
	);

	$wp_customize->add_section(
		new Yummy_Bites_Group_Title(
			$wp_customize,
			'general',
            array(
                'title' => __( 'General Settings', 'yummy-bites' ),
				'priority' => 5,
            )
		)
	);

	$wp_customize->add_section(
		new Yummy_Bites_Group_Title(
			$wp_customize,
			'posts',
            array(
                'title' => __( 'Posts and Pages', 'yummy-bites' ),
				'priority' => 45,
            )
		)
	);

	$wp_customize->add_section(
		new Yummy_Bites_Group_Title(
			$wp_customize,
			'misc_settings',
			array(
				'title' => __( 'Additional Settings', 'yummy-bites' ),
				'priority' => 70,
			)
		)
	);
	
}
endif;
add_action( 'customize_register', 'yummy_bites_customize_register_title_section' );