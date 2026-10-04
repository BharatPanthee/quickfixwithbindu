<?php
/**
 * Yummy Bites Customizer Partials
 *
 * @package Yummy Bites
 */

if( ! function_exists( 'yummy_bites_customize_partial_blogname' ) ) :
/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function yummy_bites_customize_partial_blogname() {
	bloginfo( 'name' );
}
endif;

if( ! function_exists( 'yummy_bites_customize_partial_blogdescription' ) ) :
/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function yummy_bites_customize_partial_blogdescription() {
	bloginfo( 'description' );
}
endif;

if( ! function_exists( 'yummy_bites_get_blog_readmore' ) ) :
/**
 * Display blog readmore button
*/
function yummy_bites_get_blog_readmore(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'blog_readmore', $defaults['blog_readmore'] ) );    
}
endif;

if ( ! function_exists( 'yummy_bites_get_home_text' ) ) :
/**
 * Breadcrumb Home Text
 */
function yummy_bites_get_home_text() {
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'home_text', $defaults['home_text'] ) );
}
endif;

if( ! function_exists( 'yummy_bites_get_blog_main_title' ) ) :
/**
 * Blog Section Title
*/
function yummy_bites_get_blog_main_title(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'blog_main_title', $defaults['blog_main_title'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_blog_main_content' ) ) :
/**
 * Blog Section Description
*/
function yummy_bites_get_blog_main_content(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'blog_main_content', $defaults['blog_main_content'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_read_more' ) ) :
/**
 * Display blog readmore button
*/
function yummy_bites_get_read_more(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'read_more_text', $defaults['read_more_text'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_abt_title' ) ) :
/**
 * About Section Title
*/
function yummy_bites_get_abt_title(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'abt_title', $defaults['abt_title'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_abt_description' ) ) :
/**
 * About Section Description
*/
function yummy_bites_get_abt_description(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'abt_description', $defaults['abt_description'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_abt_button_label' ) ) :
/**
 * About Section Read More Button
*/
function yummy_bites_get_abt_button_label(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'abt_button_label', $defaults['abt_button_label'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_feature_recipe_title' ) ) :
/**
 * Featured On Section Title
*/
function yummy_bites_get_feature_recipe_title(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'feature_recipe_title', $defaults['feature_recipe_title'] ) );    
}
endif;

if( ! function_exists( 'yummy_bites_get_author_title' ) ) :
/**
 * Display blog readmore button
*/
function yummy_bites_get_author_title(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'author_title', $defaults['author_title'] ) );
}
endif;

if( ! function_exists( 'yummy_bites_get_related_title' ) ) :
/**
 * Display blog readmore button
*/
function yummy_bites_get_related_title(){
    $defaults = yummy_bites_get_general_defaults();
    return esc_html( get_theme_mod( 'related_post_title', $defaults['related_post_title']) );
}
endif;

if( ! function_exists( 'yummy_bites_get_footer_copyright' ) ) :
/**
 * Footer Copyright
*/
function yummy_bites_get_footer_copyright(){
    $defaults  = yummy_bites_get_general_defaults();
    $copyright = get_theme_mod( 'footer_copyright', $defaults['footer_copyright'] );
    echo '<span class="copyright">';
    if( $copyright ){
        echo wp_kses_post( $copyright );
    }else{
        esc_html_e( '&copy; Copyright ', 'yummy-bites' );
        echo date_i18n( esc_html__( 'Y', 'yummy-bites' ) );
        echo ' <a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>. ';
        esc_html_e( 'All Rights Reserved. ', 'yummy-bites' );
    }
    echo '</span>'; 
}
endif;