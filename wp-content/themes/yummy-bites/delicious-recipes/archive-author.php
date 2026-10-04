<?php
/**
 * The Template for displaying recipe archives.
 *
 * This template can be overridden by copying it to yourtheme/delicious-recipe/archive-author.php.
 *
 * HOWEVER, on occasion WP Delicious will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://wpdelicious.com/docs/template-structure/
 * @package     Delicious_Recipes/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
	$global_settings         = delicious_recipes_get_global_settings();
	$author_profile          = isset( $global_settings['enableAuthorProfile']['0'] ) && 'yes' === $global_settings['enableAuthorProfile']['0'] ? true : false;
	$showAuthorArchiveHeader = isset( $global_settings['showAuthorArchiveHeader']['0'] ) && 'yes' === $global_settings['showAuthorArchiveHeader']['0'] ? true : false;
	$view_type               = delicious_recipes_get_archive_layout();

	if ( $author_profile && $showAuthorArchiveHeader ) :
		?>
			<header class="page-header">
				<div class="container">
					<?php
						/**
						* Recipe content template load.
						*/
						delicious_recipes_get_template( 'recipe/author-profile.php' );
					?>
				</div>
			</header>
		<?php
	endif;
	?>
	<div class="page-grid">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<div class="dr-archive-list-wrapper">
					<div class="dr-archive-list-gridwrap <?php echo esc_attr( $view_type ); ?>">
						<?php
							if ( have_posts() ) {
								while ( have_posts() ) {
									the_post();

									/**
									 * Hook: delicious_recipe_archive_loop.
									 */
									do_action( 'delicious_recipe_archive_loop' );
									$post_type = get_post_type( get_the_ID() );

									if ( DELICIOUS_RECIPE_POST_TYPE === $post_type ) {
										delicious_recipes_get_template_part( 'recipes', $view_type );
									}

								} // end of the loop.
								/**
								 * Get archive pagination.
								 */
								delicious_recipes_get_template( 'archive/pagination.php' );
							} else {
								esc_html_e( 'No recipes found.', 'yummy-bites' );
							}
						?>
					</div>
				</div>
			</main>
		</div>
		<?php do_action( 'delicious_recipes_sidebar' ); ?>
	</div>
<?php	
get_footer();
