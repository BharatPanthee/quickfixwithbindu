<?php
/**
 * Yummy Bites Custom Control
 * 
 * @package Yummy Bites
*/

if( ! function_exists( 'yummy_bites_register_custom_controls' ) ) :
/**
 * Register Custom Controls
*/
function yummy_bites_register_custom_controls( $wp_customize ){    
    // Load our custom control.
    require_once get_template_directory() . '/inc/custom-controls/note/class-note-control.php';
    require_once get_template_directory() . '/inc/custom-controls/radioimg/class-radio-image-control.php';
    require_once get_template_directory() . '/inc/custom-controls/repeater/class-repeater-setting.php';
    require_once get_template_directory() . '/inc/custom-controls/repeater/class-control-repeater.php';
    require_once get_template_directory() . '/inc/custom-controls/toggle/class-toggle-control.php';
    require_once get_template_directory() . '/inc/custom-controls/typography/class-typography-control.php';
    require_once get_template_directory() . '/inc/custom-controls/coloralpha/class-color-alpha-control.php';
    require_once get_template_directory() . '/inc/custom-controls/group/class-group-control.php';  
    require_once get_template_directory() . '/inc/custom-controls/select/class-select-control.php';  
    require_once get_template_directory() . '/inc/custom-controls/slider/class-slider-control.php';     
    require_once get_template_directory() . '/inc/custom-controls/range/class-range-control.php';
    require_once get_template_directory() . '/inc/custom-controls/grouptitle/class-group-title.php';
    require_once get_template_directory() . '/inc/custom-controls/spacing/class-spacing-control.php';
    require_once get_template_directory() . '/inc/custom-controls/tabs/class-customizer-tabs-control.php';
    require_once get_template_directory() . '/inc/custom-controls/sortable/class-sortable-control.php';
    require_once get_template_directory() . '/inc/custom-controls/radiobtn/class-radio-buttonset-control.php';
    require_once get_template_directory() . '/inc/custom-controls/visibility/class-customizer-visibility.php';
    
    // Register the control type.
    $wp_customize->register_control_type( 'Yummy_Bites_Radio_Image_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Toggle_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Typography_Customize_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Alpha_Color_Customize_Control' ); 
    $wp_customize->register_control_type( 'Yummy_Bites_Group_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Range_Slider_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Select_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Slider_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Spacing_Control' );
    $wp_customize->register_control_type( 'Yummy_Bites_Sortable_Control' ); 
    $wp_customize->register_control_type( 'Yummy_Bites_Radio_Buttonset_Control' );

}
endif;
add_action( 'customize_register', 'yummy_bites_register_custom_controls' );