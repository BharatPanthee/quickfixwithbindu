<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Yummy Bites
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
        /**
         * Post Thumbnail
         * 
         * @hooked yummy_bites_post_thumbnail
        */
        do_action( 'yummy_bites_before_page_entry_content' );
    
        /**
         * Entry Content
         * 
         * @hooked yummy_bites_entry_content - 15
         * @hooked yummy_bites_entry_footer  - 20
        */
        do_action( 'yummy_bites_page_entry_content' );    
    ?>
</article><!-- #post-<?php the_ID(); ?> -->
