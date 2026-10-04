<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Yummy Bites
 */

get_header(); ?>
	<div class="page-grid">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">

				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/content', 'page' );

					/**
					 * Comment Template
					 * 
					 * @hooked yummy_bites_comment
					*/
					do_action( 'yummy_bites_after_page_content' );

				endwhile; // End of the loop.
				?>

			</main><!-- #main -->
		</div><!-- #primary -->
		<?php get_sidebar(); ?>
	</div>
<?php

get_footer();
