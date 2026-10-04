<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Yummy Bites
 */
    /**
     * Doctype Hook
     * 
     * @hooked yummy_bites_doctype
    */
    do_action( 'yummy_bites_doctype' );
?>
<head itemscope itemtype="https://schema.org/WebSite">
	<?php 
    /**
     * Before wp_head
     * 
     * @hooked yummy_bites_head
    */
    do_action( 'yummy_bites_before_wp_head' );
    
    wp_head(); ?>
</head>

<body <?php body_class(); ?> itemscope itemtype="https://schema.org/WebPage">

<?php
    wp_body_open();
    
    /**
     * Before Header
     * 
     * @hooked yummy_bites_page_start - 20 
    */
    do_action( 'yummy_bites_before_header' );
    
    /**
     * Header
     * 
     * @hooked yummy_bites_header - 20     
    */
    do_action( 'yummy_bites_header' );
    
    
    /**
     * Content
     * 
     * @hooked yummy_bites_content_start
    */
    do_action( 'yummy_bites_content' );