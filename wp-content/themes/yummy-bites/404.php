<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 * @package Yummy Bites
 */

get_header(); 
$error_img_class = ( yummy_bites_pro_is_activated() && !empty( get_theme_mod( '404_image') ) ) ? ' error-img-active' : '';
$prodefaults     = yummy_bites_pro_is_activated() ? yummy_bites_pro_get_customizer_defaults() : []; ?>

<div class="error-page-top-wrapper<?php echo esc_attr( $error_img_class); ?>">
    <div class="container">
        <section class="error-404 not-found">
            <div class="error-404-content-wrapper">
                <div class="error404-grid">
                    <div class="page-content">
                        <h1 class="page-title"><?php esc_html_e( '404', 'yummy-bites' );?></h1>
                        <?php 
                        /**
                         * @hooked yummy_bites_404_image
                         */ 
                        do_action('yummy_bites_404_image'); ?>
                        <div class="sub-title-wrapper">
                            <p class="sub-title"><?php esc_html_e( 'Oops! page not found', 'yummy-bites' );?></p>
                        </div>
                        <p><?php esc_html_e( 'The page you are looking for may have been moved, deleted, or possibly never existed. Go back to home and explore again.', 'yummy-bites' ); ?></p>
                        <a class="btn-primary"
                            href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'BACK TO HOMEPAGE', 'yummy-bites' ); ?>
                        </a>
                        <div class="error-404-search">
                            <?php get_search_form(); ?>
                        </div>
                    </div><!-- .page-content -->
                </div>
            </div>
        </section><!-- .error-404 -->
    </div>
</div>
<?php if( ! yummy_bites_pro_is_activated() || ( yummy_bites_pro_is_activated() && get_theme_mod( 'ed_latest_post', $prodefaults['ed_latest_post'] ) ) ) { ?>
    <div class="container">
        <div class="page-grid">
            <div id="primary" class="content-area">
                <?php
                /**
                 * @see yummy_bites_latest_posts
                */
                do_action( 'yummy_bites_latest_posts' ); ?>
            </div><!-- #primary -->
        </div>
    </div>
<?php } 
get_footer();