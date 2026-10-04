<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #acc-content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Yummy Bites
 */
    
    /**
     * After Content
     * 
     * @hooked yummy_bites_content_end - 20
    */
    do_action( 'yummy_bites_before_footer' );
    
    /**
     * Footer
     * 
     * @hooked yummy_bites_footer_instagram_section - 15
     * @hooked yummy_bites_footer_start             - 20
     * @hooked yummy_bites_footer_top               - 30
     * @hooked yummy_bites_footer_bottom            - 40
     * @hooked yummy_bites_footer_end               - 50
    */
    do_action( 'yummy_bites_footer' );
    
    /**
     * After Footer
     * 
     * @hooked yummy_bites_scrolltotop  - 15
     * @hooked yummy_bites_page_end     - 20
    */
    do_action( 'yummy_bites_after_footer' );

    wp_footer(); ?>

</body>
</html>
