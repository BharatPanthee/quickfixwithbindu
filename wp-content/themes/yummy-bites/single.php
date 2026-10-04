<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Yummy Bites
 */

get_header(); 

/**
 * Before Post Content
 * yummy_bites_before_content_single
 */
do_action( 'yummy_bites_before_post_content' );
?>
    <div class="page-grid">
        <div id="primary" class="content-area">
            <main id="main" class="site-main">

            <?php
            while ( have_posts() ) : the_post();

                get_template_part( 'template-parts/content', 'single' );

            endwhile; // End of the loop.
            ?>

            </main><!-- #main -->
            
            <?php
            /**
             * @hooked yummy_bites_author        - 15
             * @hooked yummy_bites_navigation    - 20
             * @hooked yummy_bites_related_posts - 35
             * @hooked yummy_bites_comment       - 40
            */
            do_action( 'yummy_bites_after_post_content' );
            ?>
            
        </div><!-- #primary -->     
        <?php get_sidebar(); ?>
    </div>
<?php
yummy_bites_related_posts();
get_footer();