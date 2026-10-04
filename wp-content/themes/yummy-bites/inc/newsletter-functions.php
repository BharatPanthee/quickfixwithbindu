<?php
/**
 * Blossomthemes Email Newsletter Functions.
 *
 * @package Yummy_Bites
 */

if( ! function_exists( 'yummy_bites_add_inner_div' ) ) :
    function yummy_bites_add_inner_div(){
        return true;
    }
endif;
add_filter( 'bt_newsletter_widget_inner_wrap_display', 'yummy_bites_add_inner_div' );

if( ! function_exists( 'yummy_bites_start_inner_div' ) ) :
    function yummy_bites_start_inner_div(){
        echo '<div class="container">';
    }
endif;
add_action( 'bt_newsletter_widget_inner_wrap_start', 'yummy_bites_start_inner_div' );

if( ! function_exists( 'yummy_bites_end_inner_div' ) ) :
    function yummy_bites_end_inner_div(){
        echo '</div>';
    }
endif;
add_action( 'bt_newsletter_widget_inner_wrap_close', 'yummy_bites_end_inner_div' );

if( ! function_exists( 'yummy_bites_shortcode_add_inner_div' ) ) :
    function yummy_bites_shortcode_add_inner_div(){
        return true;
    }
endif;
add_filter( 'bt_newsletter_shortcode_inner_wrap_display', 'yummy_bites_shortcode_add_inner_div' );

if( ! function_exists( 'yummy_bites_shortcode_start_inner_div' ) ) :
    function yummy_bites_shortcode_start_inner_div(){
        echo '<div class="container">';
    }
endif;
add_action( 'bt_newsletter_shortcode_inner_wrap_start', 'yummy_bites_shortcode_start_inner_div' );

if( ! function_exists( 'yummy_bites_shortcode_end_inner_div' ) ) :
    function yummy_bites_shortcode_end_inner_div(){
        echo '</div>';
    }
endif;
add_action( 'bt_newsletter_shortcode_inner_wrap_close', 'yummy_bites_shortcode_end_inner_div' );