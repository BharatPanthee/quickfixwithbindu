<?php
/**
 * @Block Patterns
 *
 * Registers block patterns and categories
 * 
 * @link  https://gutenbergtimes.com/the-wordpress-block-patterns-resource-list/#8-theme-review-team
 * @return void
 *
 */

if( ! function_exists( 'yummy_bites_register_block_patterns' ) ) :

function yummy_bites_register_block_patterns() {

	register_block_pattern_category(
		'yummy-bites-patterns',
		array( 
			'label' => esc_html__( 'Yummy Bites Patterns', 'yummy-bites' ) 
		)
	);
	
}
endif;
add_action( 'init', 'yummy_bites_register_block_patterns' );