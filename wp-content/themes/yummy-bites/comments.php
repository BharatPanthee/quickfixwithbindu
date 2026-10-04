<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Yummy Bites
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
$defaults     = yummy_bites_get_general_defaults();
$comment_form = get_theme_mod( 'single_comment_form', $defaults['single_comment_form'] );
?>

<div id="comments" class="comments-area">

	<?php
	if( $comment_form === 'above' ) comment_form();

	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				printf( // WPCS: XSS OK.
					/* translators: 1: comment count number. */
					esc_html( _nx( '%1$s Comment', '%1$s Comments', get_comments_number(), 'comments title', 'yummy-bites' ) ),
					number_format_i18n( get_comments_number() )
				);
			?>
		</h2><!-- .comments-title -->

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
                    'callback'   => 'yummy_bites_theme_comment',
                    'avatar_size' => 85,
				) );
			?>
		</ol><!-- .comment-list -->

		<?php the_comments_navigation();

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() ) : ?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'yummy-bites' ); ?></p>
		<?php
		endif;

	endif; // Check for have_comments().

	if( $comment_form === 'below' ) comment_form();
	?>

</div><!-- #comments -->
