<?php
/**
 * Yummy Bites Widget Areas
 * 
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 * @package Yummy Bites
 */
if ( ! function_exists('yummy_bites_widgets_init') ) :

function yummy_bites_widgets_init(){    
    $sidebars = array(
        'sidebar'   => array(
            'name'        => __( 'Sidebar', 'yummy-bites' ),
            'id'          => 'sidebar', 
            'description' => __( 'Default Sidebar', 'yummy-bites' ),
        ),
        'featured-on' => array(
            'name'        => __( 'Featured On', 'yummy-bites' ),
            'id'          => 'featured-on', 
            'description' => __( 'Add "Gallery Block" for featured on section.', 'yummy-bites' ),
        ),
        'blog-sidebar' => array(
            'name'        => __( 'Blog Sidebar', 'yummy-bites' ),
            'id'          => 'blog-sidebar', 
            'description' => __( 'Blog Sidebar', 'yummy-bites' ),
        ),
        'newsletter' => array(
            'name'        => __( 'Newsletter Section', 'yummy-bites' ),
            'id'          => 'newsletter', 
            'description' => __( 'Add "BlossomThemes: Email Newsletter" widget for newsletter section.', 'yummy-bites' ),
        ),
        'footer-one'=> array(
            'name'        => __( 'Footer One', 'yummy-bites' ),
            'id'          => 'footer-one', 
            'description' => __( 'Add footer one widgets here.', 'yummy-bites' ),
        ),
        'footer-two'=> array(
            'name'        => __( 'Footer Two', 'yummy-bites' ),
            'id'          => 'footer-two', 
            'description' => __( 'Add footer two widgets here.', 'yummy-bites' ),
        ),
        'footer-three'=> array(
            'name'        => __( 'Footer Three', 'yummy-bites' ),
            'id'          => 'footer-three', 
            'description' => __( 'Add footer three widgets here.', 'yummy-bites' ),
        ),
        'footer-four'=> array(
            'name'        => __( 'Footer Four', 'yummy-bites' ),
            'id'          => 'footer-four', 
            'description' => __( 'Add footer four widgets here.', 'yummy-bites' ),
        )
    );
    
    foreach( $sidebars as $sidebar ){
        register_sidebar( array(
            'name'          => esc_html( $sidebar['name'] ),
            'id'            => esc_attr( $sidebar['id'] ),
            'description'   => esc_html( $sidebar['description'] ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title" itemprop="name">',
            'after_title'   => '</h2>',
        ) );
    }
}
endif;
add_action( 'widgets_init', 'yummy_bites_widgets_init' );