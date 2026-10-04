<?php
/**
 * Active Callback
 * 
 * @package Yummy Bites
*/

/**
 * Active Callback for Banner Slider
*/
function yummy_bites_banner_ac( $control ){
    $banner        = $control->manager->get_setting( 'ed_banner_section' )->value();
    $slider_type   = $control->manager->get_setting( 'slider_type' )->value();
    $control_id    = $control->id;
    
    if ( $control_id == 'slider_type' && $banner == 'slider_banner' ) return true;
    if ( $control_id == 'slider_auto' && $banner == 'slider_banner' ) return true;
    if ( $control_id == 'slider_loop' && $banner == 'slider_banner' ) return true;
    if ( $control_id == 'slider_caption' && $banner == 'slider_banner' ) return true;    
    if ( $control_id == 'slider_readmore' && $banner == 'slider_banner' ) return true;       
    if ( $control_id == 'slider_cat' && $banner == 'slider_banner' && $slider_type == 'cat' ) return true;
    if ( $control_id == 'no_of_slides' && $banner == 'slider_banner' &&  ( $slider_type == 'latest_posts' || $slider_type == 'latest_recipes' ) ) return true;
    if ( $control_id == 'slider_full_image' && $banner == 'slider_banner' ) return true;
    if ( $control_id == 'slider_animation' && $banner == 'slider_banner' ) return true;
    if ( $control_id == 'slider_speed' && $banner == 'slider_banner' ) return true;

    return false;
}

if( ! function_exists( 'yummy_bites_about_social_media_ac' ) ) :
/**
 * Active Callback for social media
*/
function yummy_bites_about_social_media_ac( $control ){
    $ed_about_social_media = $control->manager->get_setting( 'ed_about_social_links' )->value();
    $control_id = $control->id;

    if ( $control_id == 'ed_about_social_links_new_tab' && $ed_about_social_media == true ) return true;
    if ( $control_id == 'about_social_media_order' && $ed_about_social_media == true ) return true;
    if ( $control_id == 'about_social_media_text' && $ed_about_social_media == true ) return true;
    return false;
}
endif;

if ( ! function_exists( 'yummy_bites_blog_view_all_ac' ) ) : 
/**
 * Active Callback for Blog View All Button
*/
function yummy_bites_blog_view_all_ac(){
    $blog = get_option( 'page_for_posts' );
    if( $blog ) return true;
    
    return false; 
}
endif;

if( ! function_exists( 'yummy_bites_scroll_to_top_ac' ) ) :
/**
 * Active Callback for Scroll to top button
*/
function yummy_bites_scroll_to_top_ac($control){
    $ed_scroll_top = $control->manager->get_setting( 'ed_scroll_top' )->value();
    
    if ( $ed_scroll_top ) return true;
    
    return false;
}
endif;


if( ! function_exists( 'yummy_bites_performance_fonts' ) ) :
/**
*Fonts Performance Active Callback 
*/
function yummy_bites_performance_fonts( $control ){
    $ed_google_fonts_locally  = $control->manager->get_setting( 'ed_localgoogle_fonts' )->value();
    $control_id               = $control->id;
    
    if ( $control_id == 'ed_preload_local_fonts' && $ed_google_fonts_locally === true ) return true;
    if ( $control_id == 'flush_google_fonts' && $ed_google_fonts_locally === true) return true;

    return false;
}
endif;

if( ! function_exists( 'yummy_bites_seo_breadcrumb_ac' ) ) :
/**
* Breadcrumb Active Callback 
*/
function yummy_bites_seo_breadcrumb_ac( $control ){
    $control_id  = $control->id;
    $ed_breadcrumb = $control->manager->get_setting( 'ed_breadcrumb' )->value();

    if( $control_id == 'home_text' && $ed_breadcrumb == true) return true;
    if( $control_id == 'separator_icon' && $ed_breadcrumb == true) return true;

    return false;
}
endif;

if( ! function_exists( 'yummy_bites_read_words_per_min_ac' ) ) :
/**
* Blog page read words per minute callback 
*/
function yummy_bites_read_words_per_min_ac( $control ){
    $control_id      = $control->id;
    $blog_meta_order = $control->manager->get_setting( 'blog_meta_order' )->value();

    if( $control_id == 'read_words_per_minute' && in_array( 'reading-time', $blog_meta_order ) ) return true;

    return false;
}
endif;

if( ! function_exists( 'yummy_bites_related_post_ac' ) ) :
/**
 * Active Callback for related posts
*/
function yummy_bites_related_post_ac( $control ){
    
    $ed_related_post = $control->manager->get_setting( 'ed_related' )->value();
    $control_id      = $control->id;

    if ( $control_id == 'related_post_title' && $ed_related_post ) return true;
    if ( $control_id == 'no_of_posts_rp' && $ed_related_post ) return true;
}
endif;

if( ! function_exists( 'yummy_bites_post_comment_ac' ) ) :
/**
 * Active Callback for comment toggle
*/
function yummy_bites_post_comment_ac( $control ){
    $ed_comment = $control->manager->get_setting( 'ed_post_comments' )->value();
    $control_id = $control->id;

    if ( $control_id == 'toggle_comments' && $ed_comment == true ) return true;
    if ( $control_id == 'single_comment_form' && $ed_comment == true ) return true;
    
    return false;
}
endif;

if( ! function_exists( 'yummy_bites_author_section_ac' ) ) :
/**
 * Active Callback for author box
*/
function yummy_bites_author_section_ac( $control ){
    $ed_author = $control->manager->get_setting( 'ed_author' )->value();
    $control_id = $control->id;

    if ( $control_id == 'author_title' && $ed_author == true ) return true;
    
    return false;
}
endif;

if( ! function_exists( 'yummy_bites_social_media_ac' ) ) :
/**
 * Active Callback for social media
*/
function yummy_bites_social_media_ac( $control ){
    $ed_social_media = $control->manager->get_setting( 'ed_social_links' )->value();
    $control_id = $control->id;

    if ( $control_id == 'ed_social_links_new_tab' && $ed_social_media == true ) return true;
    if ( $control_id == 'social_media_order' && $ed_social_media == true ) return true;
    if ( $control_id == 'header_social_media_text' && $ed_social_media == true ) return true;
    
    return false;
}
endif;

if( ! function_exists( 'yummy_bites_instagram_ac' ) ) :
/**
 * Active Callback for instagram
*/
function yummy_bites_instagram_ac( $control ){

    $ed_insta   = $control->manager->get_setting( 'ed_instagram' )->value();
    $control_id = $control->id;

    if ( $control_id == 'instagram_shortcode' && $ed_insta ) return true;

    return false;
}
endif;