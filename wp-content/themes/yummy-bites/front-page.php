<?php
/**
 * Front Page
 * 
 * @package Yummy_Bites
 */

$home_sections = yummy_bites_get_home_sections();
$builder_types  = get_theme_mod( 'builder_types', 'customizer' );

if ( 'posts' == get_option( 'show_on_front' ) ) { //Show Static Blog Page
    include( get_home_template() );
}elseif( yummy_bites_is_elementor_activated_post() && 'builder' === $builder_types ){ 
    get_header();
    get_template_part('template-parts/content-elementor');
    get_footer();
}elseif( $home_sections ){ 
    get_header();
    //If any one section are enabled then show custom home page.
    foreach( $home_sections as $section ){
        yummy_bites_get_template_part( $section ); 
    }
    get_footer();
}else {
    //If all section are disabled then show respective page template. 
    include( get_page_template() );
}