<?php
/**
 * Template part for displaying single post
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Yummy Bites
 */
if( yummy_bites_pro_is_activated() ){
    $single_layout  = yummy_bites_pro_single_meta_layout();
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php 
        if ( ( yummy_bites_pro_is_activated() && ( $single_layout === 'one' || $single_layout === 'two' ) ) || !yummy_bites_pro_is_activated() ) {
            echo '<div class="single-page-header">'; 
            /**
             * @hooked yummy_bites_entry_header   - 15
             * @hooked yummy_bites_post_thumbnail - 20
            */
            do_action( 'yummy_bites_before_single_post_entry_content' );
            echo '</div>';
        }
        
        echo '<div class="content-wrap">';
    
        /**
         * @hooked yummy_bites_entry_content - 15
         * @hooked yummy_bites_entry_footer  - 20
        */
        do_action( 'yummy_bites_single_post_entry_content' );

        echo '</div>';
    ?>
</article><!-- #post-<?php the_ID(); ?> -->