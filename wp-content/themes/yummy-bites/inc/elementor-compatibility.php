<?php
/**
 * Helper functions for elementor widgets
 * 
 * @package Yummy Bites
 */

if( ! function_exists( 'yummy_bites_add_theme_colors' ) ) :
/**
 * Yummy Bites Theme Colors
 *
 * @param [type] $response
 * @param [type] $handler
 * @param [type] $request
 * @return void
 */
function yummy_bites_add_theme_colors( $response, $handler, $request ){
    $defaults         = yummy_bites_get_color_defaults();
    $primary_color    = get_theme_mod( 'primary_color', $defaults['primary_color'] );
    $secondary_color  = get_theme_mod( 'secondary_color', $defaults['secondary_color'] );
    $body_font_color  = get_theme_mod( 'body_font_color', $defaults['body_font_color'] );
    $heading_color    = get_theme_mod( 'heading_color', $defaults['heading_color'] );
    $accent_color_one = get_theme_mod( 'accent_color_one', $defaults['accent_color_one'] );
    $accent_color_two = get_theme_mod( 'accent_color_two', $defaults['accent_color_two'] );
    $route            = $request->get_route();

    if ( '/elementor/v1/globals' != $route ) {
        return $response;
    }

    $data = $response->get_data();
    
    $data['colors']['primary_color'] = array(
        'id'    => 'primary_color',
        'title' => __( 'Primary Color', 'yummy-bites' ),
        'value' => $primary_color,
    );

    $data['colors']['secondary_color'] = array(
        'id'    => 'secondary_color',
        'title' => __( 'Secondary Color', 'yummy-bites' ),
        'value' => $secondary_color,
    );

    $data['colors']['body_font_color'] = array(
        'id'    => 'body_font_color',
        'title' => __( 'Font Color', 'yummy-bites' ),
        'value' => $body_font_color,
    );

    $data['colors']['heading_color'] = array(
        'id'    => 'heading_color',
        'title' => __( 'Heading Color', 'yummy-bites' ),
        'value' => $heading_color,
    );

    $data['colors']['accent_color_one'] = array(
        'id'    => 'accent_color_one',
        'title' => __( 'Accent Color One', 'yummy-bites' ),
        'value' => $accent_color_one,
    );

    $data['colors']['accent_color_two'] = array(
        'id'    => 'accent_color_two',
        'title' => __( 'Accent Color Two', 'yummy-bites' ),
        'value' => $accent_color_two,
    );

    $response->set_data( $data );

    return $response;
}
endif;
add_action( 'rest_request_after_callbacks', 'yummy_bites_add_theme_colors', 999, 3 );
    
if( ! function_exists( 'yummy_bites_display_global_colors_elementor' ) ) :
    /**
     * Displays frontend Elementor colors function
     *
     * @param [type] $response
     * @param [type] $handler
     * @param [type] $request
     * @return void
     */
    function yummy_bites_display_global_colors_elementor( $response, $handler, $request ) {
        
        $defaults         = yummy_bites_get_color_defaults();
        $primary_color    = get_theme_mod( 'primary_color', $defaults['primary_color'] );
        $secondary_color  = get_theme_mod( 'secondary_color', $defaults['secondary_color'] );
        $body_font_color  = get_theme_mod( 'body_font_color', $defaults['body_font_color'] );
        $heading_color    = get_theme_mod( 'heading_color', $defaults['heading_color'] );
        $accent_color_one = get_theme_mod( 'accent_color_one', $defaults['accent_color_one'] );
        $accent_color_two = get_theme_mod( 'accent_color_two', $defaults['accent_color_two'] );
        $route = $request->get_route();
    
        if ( 0 !== strpos( $route, '/elementor/v1/globals' ) ) {
            return $response;
        }
    
        $slug_map = array();
    
        $slug_map['primary_color']    = 0;
        $slug_map['secondary_color']  = 1;
        $slug_map['body_font_color']  = 2;
        $slug_map['heading_color']    = 3;
        $slug_map['accent_color_one'] = 4;
        $slug_map['accent_color_two'] = 5;
        
        $rest_id = substr( $route, strrpos( $route, '/' ) + 1 );
    
        if ( ! in_array( $rest_id, array_keys( $slug_map ), true ) ) {
            return $response;
        }

        $response = rest_ensure_response(
            array(
                'id'    => 'primary_color',
                'title' => 'primary_color',
                'value' => $primary_color,
            ),
            array(
                'id'    => 'secondary_color',
                'title' => 'secondary_color',
                'value' => $secondary_color,
            ),
            array(
                'id'    => 'body_font_color',
                'title' => 'body_font_color',
                'value' => $body_font_color,
            ),
            array(
                'id'    => 'heading_color',
                'title' => 'heading_color',
                'value' => $heading_color,
            ),
            array(
                'id'    => 'accent_color_one',
                'title' => 'accent_color_one',
                'value' => $accent_color_one,
            ),
            array(
                'id'    => 'accent_color_two',
                'title' => 'accent_color_two',
                'value' => $accent_color_two,
            )
            
        );
        return $response;
    }
endif;
add_action( 'rest_request_after_callbacks', 'yummy_bites_display_global_colors_elementor', 999, 3 );

/** Disable Default Colours and Default Fonts of elementor on Theme Activation*/
$fresh     = get_option( 'yummy_bites_flag' );
if( ! $fresh ){
    update_option('elementor_disable_color_schemes', 'yes');
    update_option('elementor_disable_typography_schemes', 'yes');
    update_option( 'yummy_bites_flag', true ); 
    update_option( 'elementor_experiment-container', 'active' ); 
}

$gridContainer = get_option( 'yummy_bites_flag_grid_container' );
if( ! $gridContainer ){
    update_option( 'elementor_experiment-container_grid', 'active' ); 
}