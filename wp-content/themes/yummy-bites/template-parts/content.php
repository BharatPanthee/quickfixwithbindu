<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Yummy Bites
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); if( ! is_single() ) echo ' itemscope itemtype="https://schema.org/Blog"'; ?>>
	<?php 
        /**
         * @hooked yummy_bites_post_thumbnail - 15
         * @hooked yummy_bites_entry_header   - 20
        */
        do_action( 'yummy_bites_before_post_entry_content' );
    
        /**
         * @hooked yummy_bites_entry_content - 15
         * @hooked yummy_bites_entry_footer  - 20
        */
        do_action( 'yummy_bites_post_entry_content' );
    ?>
</article><!-- #post-<?php the_ID(); ?> -->