<?php
/**
 * Yummy Bites Dynamic Styles
 * 
 * @package Yummy Bites
*/

if ( ! function_exists( 'yummy_bites_dynamic_css') ) :
/**
 * Dynamic Style
 */
function yummy_bites_dynamic_css(){
    
    $typo_defaults   = yummy_bites_get_typography_defaults();
    $defaults        = yummy_bites_get_color_defaults();
    $site_defaults   = yummy_bites_get_site_defaults();
    $button_defaults = yummy_bites_get_button_defaults();
    $layout_defaults = yummy_bites_get_general_defaults();
    $color_defaults  = yummy_bites_get_color_defaults();
    
	$primary_font   = wp_parse_args( get_theme_mod( 'primary_font' ), $typo_defaults['primary_font'] );
	$sitetitle      = wp_parse_args( get_theme_mod( 'site_title' ), $typo_defaults['site_title'] );
	$button         = wp_parse_args( get_theme_mod( 'button' ), $typo_defaults['button'] );
    $heading_one    = wp_parse_args( get_theme_mod( 'heading_one' ), $typo_defaults['heading_one'] );
	$heading_two    = wp_parse_args( get_theme_mod( 'heading_two' ), $typo_defaults['heading_two'] );
	$heading_three  = wp_parse_args( get_theme_mod( 'heading_three' ), $typo_defaults['heading_three'] );
	$heading_four   = wp_parse_args( get_theme_mod( 'heading_four' ), $typo_defaults['heading_four'] );
	$heading_five   = wp_parse_args( get_theme_mod( 'heading_five' ), $typo_defaults['heading_five'] );
	$heading_six    = wp_parse_args( get_theme_mod( 'heading_six' ), $typo_defaults['heading_six'] );

    //Primary Font variables
    $primarydesktopFontSize = isset(  $primary_font['desktop']['font_size'] ) ? $primary_font['desktop']['font_size'] : $typo_defaults['primary_font']['desktop']['font_size'];
    $primarydesktopSpacing  = isset(  $primary_font['desktop']['letter_spacing'] ) ? $primary_font['desktop']['letter_spacing'] : $typo_defaults['primary_font']['desktop']['letter_spacing'];
    $primarydesktopHeight   = isset(  $primary_font['desktop']['line_height'] ) ? $primary_font['desktop']['line_height'] : $typo_defaults['primary_font']['desktop']['line_height'];
    $primarytabletFontSize  = isset(  $primary_font['tablet']['font_size'] ) ? $primary_font['tablet']['font_size'] : $typo_defaults['primary_font']['tablet']['font_size'];
    $primarytabletSpacing   = isset(  $primary_font['tablet']['letter_spacing'] ) ? $primary_font['tablet']['letter_spacing'] : $typo_defaults['primary_font']['tablet']['letter_spacing'];
    $primarytabletHeight    = isset(  $primary_font['tablet']['line_height'] ) ? $primary_font['tablet']['line_height'] : $typo_defaults['primary_font']['tablet']['line_height'];
    $primarymobileFontSize  = isset(  $primary_font['mobile']['font_size'] ) ? $primary_font['mobile']['font_size'] : $typo_defaults['primary_font']['mobile']['font_size'];
    $primarymobileSpacing   = isset(  $primary_font['mobile']['letter_spacing'] ) ? $primary_font['mobile']['letter_spacing'] : $typo_defaults['primary_font']['mobile']['letter_spacing'];
    $primarymobileHeight    = isset(  $primary_font['mobile']['line_height'] ) ? $primary_font['mobile']['line_height'] : $typo_defaults['primary_font']['mobile']['line_height'];

    //Site Title variables
    $sitedesktopFontSize = isset(  $sitetitle['desktop']['font_size'] ) ? $sitetitle['desktop']['font_size'] : $typo_defaults['site_title']['desktop']['font_size'];
    $sitedesktopSpacing  = isset(  $sitetitle['desktop']['letter_spacing'] ) ? $sitetitle['desktop']['letter_spacing'] : $typo_defaults['site_title']['desktop']['letter_spacing'];
    $sitedesktopHeight   = isset(  $sitetitle['desktop']['line_height'] ) ? $sitetitle['desktop']['line_height'] : $typo_defaults['site_title']['desktop']['line_height'];
    $sitetabletFontSize  = isset(  $sitetitle['tablet']['font_size'] ) ? $sitetitle['tablet']['font_size'] : $typo_defaults['site_title']['tablet']['font_size'];
    $sitetabletSpacing   = isset(  $sitetitle['tablet']['letter_spacing'] ) ? $sitetitle['tablet']['letter_spacing'] : $typo_defaults['site_title']['tablet']['letter_spacing'];
    $sitetabletHeight    = isset(  $sitetitle['tablet']['line_height'] ) ? $sitetitle['tablet']['line_height'] : $typo_defaults['site_title']['tablet']['line_height'];
    $sitemobileFontSize  = isset(  $sitetitle['mobile']['font_size'] ) ? $sitetitle['mobile']['font_size'] : $typo_defaults['site_title']['mobile']['font_size'];
    $sitemobileSpacing   = isset(  $sitetitle['mobile']['letter_spacing'] ) ? $sitetitle['mobile']['letter_spacing'] : $typo_defaults['site_title']['mobile']['letter_spacing'];
    $sitemobileHeight    = isset(  $sitetitle['mobile']['line_height'] ) ? $sitetitle['mobile']['line_height'] : $typo_defaults['site_title']['mobile']['line_height'];
    
    //Button variables
    $btndesktopFontSize = isset(  $button['desktop']['font_size'] ) ? $button['desktop']['font_size'] : $typo_defaults['button']['desktop']['font_size'];
    $btndesktopSpacing  = isset(  $button['desktop']['letter_spacing'] ) ? $button['desktop']['letter_spacing'] : $typo_defaults['button']['desktop']['letter_spacing'];
    $btndesktopHeight   = isset(  $button['desktop']['line_height'] ) ? $button['desktop']['line_height'] : $typo_defaults['button']['desktop']['line_height'];
    $btntabletFontSize  = isset(  $button['tablet']['font_size'] ) ? $button['tablet']['font_size'] : $typo_defaults['button']['tablet']['font_size'];
    $btntabletSpacing   = isset(  $button['tablet']['letter_spacing'] ) ? $button['tablet']['letter_spacing'] : $typo_defaults['button']['tablet']['letter_spacing'];
    $btntabletHeight    = isset(  $button['tablet']['line_height'] ) ? $button['tablet']['line_height'] : $typo_defaults['button']['tablet']['line_height'];
    $btnmobileFontSize  = isset(  $button['mobile']['font_size'] ) ? $button['mobile']['font_size'] : $typo_defaults['button']['mobile']['font_size'];
    $btnmobileSpacing   = isset(  $button['mobile']['letter_spacing'] ) ? $button['mobile']['letter_spacing'] : $typo_defaults['button']['mobile']['letter_spacing'];
    $btnmobileHeight    = isset(  $button['mobile']['line_height'] ) ? $button['mobile']['line_height'] : $typo_defaults['button']['mobile']['line_height'];

    //Heading 1 variables
    $h1desktopFontSize = isset(  $heading_one['desktop']['font_size'] ) ? $heading_one['desktop']['font_size'] : $typo_defaults['heading_one']['desktop']['font_size'];
    $h1desktopSpacing  = isset(  $heading_one['desktop']['letter_spacing'] ) ? $heading_one['desktop']['letter_spacing'] : $typo_defaults['heading_one']['desktop']['letter_spacing'];
    $h1desktopHeight   = isset(  $heading_one['desktop']['line_height'] ) ? $heading_one['desktop']['line_height'] : $typo_defaults['heading_one']['desktop']['line_height'];
    $h1tabletFontSize  = isset(  $heading_one['tablet']['font_size'] ) ? $heading_one['tablet']['font_size'] : $typo_defaults['heading_one']['tablet']['font_size'];
    $h1tabletSpacing   = isset(  $heading_one['tablet']['letter_spacing'] ) ? $heading_one['tablet']['letter_spacing'] : $typo_defaults['heading_one']['tablet']['letter_spacing'];
    $h1tabletHeight    = isset(  $heading_one['tablet']['line_height'] ) ? $heading_one['tablet']['line_height'] : $typo_defaults['heading_one']['tablet']['line_height'];
    $h1mobileFontSize  = isset(  $heading_one['mobile']['font_size'] ) ? $heading_one['mobile']['font_size'] : $typo_defaults['heading_one']['mobile']['font_size'];
    $h1mobileSpacing   = isset(  $heading_one['mobile']['letter_spacing'] ) ? $heading_one['mobile']['letter_spacing'] : $typo_defaults['heading_one']['mobile']['letter_spacing'];
    $h1mobileHeight    = isset(  $heading_one['mobile']['line_height'] ) ? $heading_one['mobile']['line_height'] : $typo_defaults['heading_one']['mobile']['line_height'];
    
    //Heading 2 variables
    $h2desktopFontSize = isset(  $heading_two['desktop']['font_size'] ) ? $heading_two['desktop']['font_size'] : $typo_defaults['heading_two']['desktop']['font_size'];
    $h2desktopSpacing  = isset(  $heading_two['desktop']['letter_spacing'] ) ? $heading_two['desktop']['letter_spacing'] : $typo_defaults['heading_two']['desktop']['letter_spacing'];
    $h2desktopHeight   = isset(  $heading_two['desktop']['line_height'] ) ? $heading_two['desktop']['line_height'] : $typo_defaults['heading_two']['desktop']['line_height'];
    $h2tabletFontSize  = isset(  $heading_two['tablet']['font_size'] ) ? $heading_two['tablet']['font_size'] : $typo_defaults['heading_two']['tablet']['font_size'];
    $h2tabletSpacing   = isset(  $heading_two['tablet']['letter_spacing'] ) ? $heading_two['tablet']['letter_spacing'] : $typo_defaults['heading_two']['tablet']['letter_spacing'];
    $h2tabletHeight    = isset(  $heading_two['tablet']['line_height'] ) ? $heading_two['tablet']['line_height'] : $typo_defaults['heading_two']['tablet']['line_height'];
    $h2mobileFontSize  = isset(  $heading_two['mobile']['font_size'] ) ? $heading_two['mobile']['font_size'] : $typo_defaults['heading_two']['mobile']['font_size'];
    $h2mobileSpacing   = isset(  $heading_two['mobile']['letter_spacing'] ) ? $heading_two['mobile']['letter_spacing'] : $typo_defaults['heading_two']['mobile']['letter_spacing'];
    $h2mobileHeight    = isset(  $heading_two['mobile']['line_height'] ) ? $heading_two['mobile']['line_height'] : $typo_defaults['heading_two']['mobile']['line_height'];
    
    //Heading 3 variables
    $h3desktopFontSize = isset(  $heading_three['desktop']['font_size'] ) ? $heading_three['desktop']['font_size'] : $typo_defaults['heading_three']['desktop']['font_size'];
    $h3desktopSpacing  = isset(  $heading_three['desktop']['letter_spacing'] ) ? $heading_three['desktop']['letter_spacing'] : $typo_defaults['heading_three']['desktop']['letter_spacing'];
    $h3desktopHeight   = isset(  $heading_three['desktop']['line_height'] ) ? $heading_three['desktop']['line_height'] : $typo_defaults['heading_three']['desktop']['line_height'];
    $h3tabletFontSize  = isset(  $heading_three['tablet']['font_size'] ) ? $heading_three['tablet']['font_size'] : $typo_defaults['heading_three']['tablet']['font_size'];
    $h3tabletSpacing   = isset(  $heading_three['tablet']['letter_spacing'] ) ? $heading_three['tablet']['letter_spacing'] : $typo_defaults['heading_three']['tablet']['letter_spacing'];
    $h3tabletHeight    = isset(  $heading_three['tablet']['line_height'] ) ? $heading_three['tablet']['line_height'] : $typo_defaults['heading_three']['tablet']['line_height'];
    $h3mobileFontSize  = isset(  $heading_three['mobile']['font_size'] ) ? $heading_three['mobile']['font_size'] : $typo_defaults['heading_three']['mobile']['font_size'];
    $h3mobileSpacing   = isset(  $heading_three['mobile']['letter_spacing'] ) ? $heading_three['mobile']['letter_spacing'] : $typo_defaults['heading_three']['mobile']['letter_spacing'];
    $h3mobileHeight    = isset(  $heading_three['mobile']['line_height'] ) ? $heading_three['mobile']['line_height'] : $typo_defaults['heading_three']['mobile']['line_height'];
    
    //Heading 4 variables
    $h4desktopFontSize = isset(  $heading_four['desktop']['font_size'] ) ? $heading_four['desktop']['font_size'] : $typo_defaults['heading_four']['desktop']['font_size'];
    $h4desktopSpacing  = isset(  $heading_four['desktop']['letter_spacing'] ) ? $heading_four['desktop']['letter_spacing'] : $typo_defaults['heading_four']['desktop']['letter_spacing'];
    $h4desktopHeight   = isset(  $heading_four['desktop']['line_height'] ) ? $heading_four['desktop']['line_height'] : $typo_defaults['heading_four']['desktop']['line_height'];
    $h4tabletFontSize  = isset(  $heading_four['tablet']['font_size'] ) ? $heading_four['tablet']['font_size'] : $typo_defaults['heading_four']['tablet']['font_size'];
    $h4tabletSpacing   = isset(  $heading_four['tablet']['letter_spacing'] ) ? $heading_four['tablet']['letter_spacing'] : $typo_defaults['heading_four']['tablet']['letter_spacing'];
    $h4tabletHeight    = isset(  $heading_four['tablet']['line_height'] ) ? $heading_four['tablet']['line_height'] : $typo_defaults['heading_four']['tablet']['line_height'];
    $h4mobileFontSize  = isset(  $heading_four['mobile']['font_size'] ) ? $heading_four['mobile']['font_size'] : $typo_defaults['heading_four']['mobile']['font_size'];
    $h4mobileSpacing   = isset(  $heading_four['mobile']['letter_spacing'] ) ? $heading_four['mobile']['letter_spacing'] : $typo_defaults['heading_four']['mobile']['letter_spacing'];
    $h4mobileHeight    = isset(  $heading_four['mobile']['line_height'] ) ? $heading_four['mobile']['line_height'] : $typo_defaults['heading_four']['mobile']['line_height'];
    
    //Heading 5 variables
    $h5desktopFontSize = isset(  $heading_five['desktop']['font_size'] ) ? $heading_five['desktop']['font_size'] : $typo_defaults['heading_five']['desktop']['font_size'];
    $h5desktopSpacing  = isset(  $heading_five['desktop']['letter_spacing'] ) ? $heading_five['desktop']['letter_spacing'] : $typo_defaults['heading_five']['desktop']['letter_spacing'];
    $h5desktopHeight   = isset(  $heading_five['desktop']['line_height'] ) ? $heading_five['desktop']['line_height'] : $typo_defaults['heading_five']['desktop']['line_height'];
    $h5tabletFontSize  = isset(  $heading_five['tablet']['font_size'] ) ? $heading_five['tablet']['font_size'] : $typo_defaults['heading_five']['tablet']['font_size'];
    $h5tabletSpacing   = isset(  $heading_five['tablet']['letter_spacing'] ) ? $heading_five['tablet']['letter_spacing'] : $typo_defaults['heading_five']['tablet']['letter_spacing'];
    $h5tabletHeight    = isset(  $heading_five['tablet']['line_height'] ) ? $heading_five['tablet']['line_height'] : $typo_defaults['heading_five']['tablet']['line_height'];
    $h5mobileFontSize  = isset(  $heading_five['mobile']['font_size'] ) ? $heading_five['mobile']['font_size'] : $typo_defaults['heading_five']['mobile']['font_size'];
    $h5mobileSpacing   = isset(  $heading_five['mobile']['letter_spacing'] ) ? $heading_five['mobile']['letter_spacing'] : $typo_defaults['heading_five']['mobile']['letter_spacing'];
    $h5mobileHeight    = isset(  $heading_five['mobile']['line_height'] ) ? $heading_five['mobile']['line_height'] : $typo_defaults['heading_five']['mobile']['line_height'];
    
    //Heading 6 variables
    $h6desktopFontSize = isset(  $heading_six['desktop']['font_size'] ) ? $heading_six['desktop']['font_size'] : $typo_defaults['heading_six']['desktop']['font_size'];
    $h6desktopSpacing  = isset(  $heading_six['desktop']['letter_spacing'] ) ? $heading_six['desktop']['letter_spacing'] : $typo_defaults['heading_six']['desktop']['letter_spacing'];
    $h6desktopHeight   = isset(  $heading_six['desktop']['line_height'] ) ? $heading_six['desktop']['line_height'] : $typo_defaults['heading_six']['desktop']['line_height'];
    $h6tabletFontSize  = isset(  $heading_six['tablet']['font_size'] ) ? $heading_six['tablet']['font_size'] : $typo_defaults['heading_six']['tablet']['font_size'];
    $h6tabletSpacing   = isset(  $heading_six['tablet']['letter_spacing'] ) ? $heading_six['tablet']['letter_spacing'] : $typo_defaults['heading_six']['tablet']['letter_spacing'];
    $h6tabletHeight    = isset(  $heading_six['tablet']['line_height'] ) ? $heading_six['tablet']['line_height'] : $typo_defaults['heading_six']['tablet']['line_height'];
    $h6mobileFontSize  = isset(  $heading_six['mobile']['font_size'] ) ? $heading_six['mobile']['font_size'] : $typo_defaults['heading_six']['mobile']['font_size'];
    $h6mobileSpacing   = isset(  $heading_six['mobile']['letter_spacing'] ) ? $heading_six['mobile']['letter_spacing'] : $typo_defaults['heading_six']['mobile']['letter_spacing'];
    $h6mobileHeight    = isset(  $heading_six['mobile']['line_height'] ) ? $heading_six['mobile']['line_height'] : $typo_defaults['heading_six']['mobile']['line_height'];

    $primary_font_family       = yummy_bites_get_font_family( $primary_font );
    $sitetitle_font_family     = yummy_bites_get_font_family( $sitetitle );
    $btn_font_family           = yummy_bites_get_font_family( $button );
    $heading_one_font_family   = yummy_bites_get_font_family( $heading_one );
    $heading_two_font_family   = yummy_bites_get_font_family( $heading_two );
    $heading_three_font_family = yummy_bites_get_font_family( $heading_three );
    $heading_four_font_family  = yummy_bites_get_font_family( $heading_four );
    $heading_five_font_family  = yummy_bites_get_font_family( $heading_five );
    $heading_six_font_family   = yummy_bites_get_font_family( $heading_six );

    $siteFontFamily = $sitetitle_font_family === '"Default Family"' ? 'inherit' : $sitetitle_font_family;
    $btnFontFamily  = $btn_font_family === '"Default Family"' ? 'inherit' : $btn_font_family;
    $h1FontFamily   = $heading_one_font_family === '"Default Family"' ? 'inherit' : $heading_one_font_family;
    $h2FontFamily   = $heading_two_font_family === '"Default Family"' ? 'inherit' : $heading_two_font_family;
    $h3FontFamily   = $heading_three_font_family === '"Default Family"' ? 'inherit' : $heading_three_font_family;
    $h4FontFamily   = $heading_four_font_family === '"Default Family"' ? 'inherit' : $heading_four_font_family;
    $h5FontFamily   = $heading_five_font_family === '"Default Family"' ? 'inherit' : $heading_five_font_family;
    $h6FontFamily   = $heading_six_font_family === '"Default Family"' ? 'inherit' : $heading_six_font_family;

    $logo_width        = get_theme_mod( 'logo_width', $site_defaults['logo_width'] );
	$tablet_logo_width = get_theme_mod( 'tablet_logo_width', $site_defaults['tablet_logo_width'] );
	$mobile_logo_width = get_theme_mod( 'mobile_logo_width', $site_defaults['mobile_logo_width'] );

    $container_width        = get_theme_mod( 'container_width', $layout_defaults['container_width'] );
	$tablet_container_width = get_theme_mod( 'tablet_container_width', $layout_defaults['tablet_container_width'] );
	$mobile_container_width = get_theme_mod( 'mobile_container_width', $layout_defaults['mobile_container_width'] );

    $fullwidth_centered        = get_theme_mod( 'fullwidth_centered', $layout_defaults['fullwidth_centered']);
    $tablet_fullwidth_centered = get_theme_mod( 'tablet_fullwidth_centered', $layout_defaults['tablet_fullwidth_centered']);
    $mobile_fullwidth_centered = get_theme_mod( 'mobile_fullwidth_centered', $layout_defaults['mobile_fullwidth_centered']);

    $sidebar_width        = get_theme_mod( 'sidebar_width', $layout_defaults['sidebar_width']);

    $widgets_spacing        = get_theme_mod( 'widgets_spacing', $layout_defaults['widgets_spacing']);
    $tablet_widgets_spacing = get_theme_mod( 'tablet_widgets_spacing', $layout_defaults['tablet_widgets_spacing']);
    $mobile_widgets_spacing = get_theme_mod( 'mobile_widgets_spacing', $layout_defaults['mobile_widgets_spacing']);

    $scroll_top_size        = get_theme_mod( 'scroll_top_size', $layout_defaults['scroll_top_size']);
    $tablet_scroll_top_size = get_theme_mod( 'tablet_scroll_top_size', $layout_defaults['tablet_scroll_top_size']);
    $mobile_scroll_top_size = get_theme_mod( 'mobile_scroll_top_size', $layout_defaults['mobile_scroll_top_size']);

    $button_roundness = get_theme_mod( 'button_roundness', $button_defaults['button_roundness'] );

    $btnRoundTop    = isset(  $button_roundness['top'] ) ? $button_roundness['top'] : $button_defaults['button_roundness']['top'];
    $btnRoundRight  = isset(  $button_roundness['right'] ) ? $button_roundness['right'] : $button_defaults['button_roundness']['right'];
    $btnRoundBottom = isset(  $button_roundness['bottom'] ) ? $button_roundness['bottom'] : $button_defaults['button_roundness']['bottom'];
    $btnRoundLeft   = isset(  $button_roundness['left'] ) ? $button_roundness['left'] : $button_defaults['button_roundness']['left'];

	$button_padding   = get_theme_mod( 'button_padding', $button_defaults['button_padding'] );

    $btnPaddingTop    = isset(  $button_padding['top'] ) ? $button_padding['top'] : $button_defaults['button_padding']['top'];
    $btnPaddingRight  = isset(  $button_padding['right'] ) ? $button_padding['right'] : $button_defaults['button_padding']['right'];
    $btnPaddingBottom = isset(  $button_padding['bottom'] ) ? $button_padding['bottom'] : $button_defaults['button_padding']['bottom'];
    $btnPaddingLeft   = isset(  $button_padding['left'] ) ? $button_padding['left'] : $button_defaults['button_padding']['left'];

    //COLOR VARIABLES

    $primary_color    = get_theme_mod( 'primary_color', $defaults['primary_color'] );
    $rgb              = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $primary_color ) );
    $secondary_color  = get_theme_mod( 'secondary_color', $defaults['secondary_color'] );
    $rgb2             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $secondary_color ) );
    $body_font_color  = get_theme_mod( 'body_font_color', $defaults['body_font_color'] );
    $rgb3             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $body_font_color ) );
    $heading_color    = get_theme_mod( 'heading_color', $defaults['heading_color'] );
    $rgb4             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $heading_color ) );
    $background_color = get_theme_mod( 'site_bg_color', $defaults['site_bg_color'] );
    $rgb5             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $background_color ) );
    $accent_color_one = get_theme_mod( 'accent_color_one', $defaults['accent_color_one'] );
    $accent_color_two = get_theme_mod( 'accent_color_two', $defaults['accent_color_two'] );

    //Button Color
	$btn_text_color         = get_theme_mod( 'btn_text_color_initial', $defaults['btn_text_color_initial'] );
	$btn_bg_color           = get_theme_mod( 'btn_bg_color_initial', $defaults['btn_bg_color_initial'] );
	$btn_text_hover_color   = get_theme_mod( 'btn_text_color_hover', $defaults['btn_text_color_hover'] );
	$btn_bg_hover_color     = get_theme_mod( 'btn_bg_color_hover', $defaults['btn_bg_color_hover'] );
	$btn_border_color       = get_theme_mod( 'btn_border_color_initial', $defaults['btn_border_color_initial'] );
	$btn_border_hover_color = get_theme_mod( 'btn_border_color_hover', $defaults['btn_border_color_hover'] );

    //Footer Color
    $foot_text_color      = get_theme_mod( 'foot_text_color', $defaults['foot_text_color'] );
    $foot_bg_color        = get_theme_mod( 'foot_bg_color', $defaults['foot_bg_color'] );
    $widget_heading_color = get_theme_mod( 'foot_widget_heading_color', $defaults['foot_widget_heading_color'] );

    $enable_typography = '';

    if( yummy_bites_is_delicious_recipe_activated() ){
        $global_settings = delicious_recipes_get_global_settings();
        $enable_typography = ( isset( $global_settings['enablePluginTypography']['0'] ) && 'yes' === $global_settings['enablePluginTypography']['0'] ) ? true : false;
    }

     // About Section Color
	$about_bg_color    = get_theme_mod( 'abt_bg_color', $color_defaults['abt_bg_color'] );
	$about_title_color = get_theme_mod( 'abt_title_color', $color_defaults['abt_title_color'] );
	$about_desc_color  = get_theme_mod( 'abt_desc_color', $color_defaults['abt_desc_color'] );

    echo "<style type='text/css' media='all'>"; ?>
    
    :root {
        --yummy-primary-color       : <?php echo yummy_bites_sanitize_rgba( $primary_color ); ?>;
        --yummy-primary-color-rgb   : <?php printf('%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2] ); ?>;
        --primary-color       : <?php echo yummy_bites_sanitize_rgba( $primary_color ); ?>;
        --primary-color-rgb   : <?php printf('%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2] ); ?>;
        --yummy-secondary-color     : <?php echo yummy_bites_sanitize_rgba( $secondary_color ); ?>;
        --yummy-secondary-color-rgb : <?php printf('%1$s, %2$s, %3$s', $rgb2[0], $rgb2[1], $rgb2[2] ); ?>;
        --secondary-color     : <?php echo yummy_bites_sanitize_rgba( $secondary_color ); ?>;
        --secondary-color-rgb : <?php printf('%1$s, %2$s, %3$s', $rgb2[0], $rgb2[1], $rgb2[2] ); ?>;
        --yummy-font-color          : <?php echo yummy_bites_sanitize_rgba( $body_font_color ); ?>;
        --yummy-font-color-rgb      : <?php printf('%1$s, %2$s, %3$s', $rgb3[0], $rgb3[1], $rgb3[2] ); ?>;
        --font-color          : <?php echo yummy_bites_sanitize_rgba( $body_font_color ); ?>;
        --font-color-rgb      : <?php printf('%1$s, %2$s, %3$s', $rgb3[0], $rgb3[1], $rgb3[2] ); ?>;
        --yummy-heading-color       : <?php echo yummy_bites_sanitize_rgba( $heading_color ); ?>;
        --yummy-heading-color-rgb   : <?php printf('%1$s, %2$s, %3$s', $rgb4[0], $rgb4[1], $rgb4[2] ); ?>;
        --yummy-background-color    : <?php echo yummy_bites_sanitize_rgba( $background_color ); ?>;
        --yummy-background-color-rgb: <?php printf('%1$s, %2$s, %3$s', $rgb5[0], $rgb5[1], $rgb5[2] ); ?>;

        --yummy-primary-font: <?php echo wp_kses_post( $primary_font_family ); ?>;     
        --yummy-primary-font-weight: <?php echo esc_html( $primary_font['weight'] ); ?>;
        --yummy-primary-font-transform: <?php echo esc_html( $primary_font['transform'] ); ?>;

        --yummy-secondary-font: <?php echo wp_kses_post( $h1FontFamily ); ?>;
        --yummy-secondary-font-weight: <?php echo esc_html( $heading_one['weight'] ); ?>;

        <?php if( ! $enable_typography ) { ?> --dr-primary-font: <?php echo esc_html( $primary_font_family ); ?>; <?php } ?>
        <?php if( ! $enable_typography ) { ?> --dr-secondary-font: <?php echo esc_html( $h1FontFamily ); ?>; <?php } ?>

        --yummy-btn-text-initial-color: <?php echo yummy_bites_sanitize_rgba( $btn_text_color ); ?>;
        --yummy-btn-text-hover-color: <?php echo yummy_bites_sanitize_rgba( $btn_text_hover_color ); ?>;
        --yummy-btn-bg-initial-color: <?php echo yummy_bites_sanitize_rgba( $btn_bg_color ); ?>;
        --yummy-btn-bg-hover-color: <?php echo yummy_bites_sanitize_rgba( $btn_bg_hover_color ); ?>;
        --yummy-btn-border-initial-color: <?php echo yummy_bites_sanitize_rgba( $btn_border_color ); ?>;
        --yummy-btn-border-hover-color: <?php echo yummy_bites_sanitize_rgba( $btn_border_hover_color ); ?>;

        --yummy-btn-font-family: <?php echo wp_kses_post( $btnFontFamily ); ?>;     
        --yummy-btn-font-weight: <?php echo esc_html( $button['weight'] ); ?>;
        --yummy-btn-font-transform: <?php echo esc_html( $button['transform'] ); ?>;
        --yummy-btn-roundness-top: <?php echo absint( $btnRoundTop ); ?>px;
        --yummy-btn-roundness-right: <?php echo absint( $btnRoundRight ); ?>px;
        --yummy-btn-roundness-bottom: <?php echo absint( $btnRoundBottom ); ?>px;
        --yummy-btn-roundness-left: <?php echo absint( $btnRoundLeft ); ?>px;
        --yummy-btn-padding-top: <?php echo absint( $btnPaddingTop ); ?>px;
        --yummy-btn-padding-right: <?php echo absint( $btnPaddingRight); ?>px;
        --yummy-btn-padding-bottom: <?php echo absint( $btnPaddingBottom ); ?>px;
        --yummy-btn-padding-left: <?php echo absint( $btnPaddingLeft ); ?>px;
	}

    .site-branding .site-title{
        font-family   : <?php echo wp_kses_post( $siteFontFamily ); ?>;
        font-weight   : <?php echo esc_html( $sitetitle['weight'] ); ?>;
        text-transform: <?php echo esc_html( $sitetitle['transform'] ); ?>;
    }
    
    .site-header .custom-logo{
		width : <?php echo absint( $logo_width ); ?>px;
	}

    .site-footer{
        --yummy-foot-text-color   : <?php echo yummy_bites_sanitize_rgba( $foot_text_color ); ?>;
        --yummy-foot-bg-color     : <?php echo yummy_bites_sanitize_rgba( $foot_bg_color ); ?>;
        --yummy-widget-title-color: <?php echo yummy_bites_sanitize_rgba( $widget_heading_color ); ?>;
    }

    .elementor-page h1,
    h1{
        font-family : <?php echo wp_kses_post( $h1FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_one['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_one['weight'] ); ?>;
    }

    .elementor-page h2,
    h2{
        font-family : <?php echo wp_kses_post( $h2FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_two['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_two['weight'] ); ?>;
    }

    .elementor-page h3,
    h3{
        font-family : <?php echo wp_kses_post( $h3FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_three['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_three['weight'] ); ?>;
    }

    .elementor-page h4,
    h4{
        font-family : <?php echo wp_kses_post( $h4FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_four['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_four['weight'] ); ?>;
    }

    .elementor-page h5,
    h5{
        font-family : <?php echo wp_kses_post( $h5FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_five['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_five['weight'] ); ?>;
    }
    
    .elementor-page h6,
    h6{
        font-family : <?php echo wp_kses_post( $h6FontFamily ); ?>;
        text-transform: <?php echo esc_html( $heading_six['transform'] ); ?>;      
        font-weight: <?php echo esc_html( $heading_six['weight'] ); ?>;
    }

    @media (min-width: 1024px){
        :root{
            --yummy-primary-font-size   : <?php echo absint( $primarydesktopFontSize ); ?>px;
            --yummy-primary-font-height : <?php echo floatval( $primarydesktopHeight ); ?>em;
            --yummy-primary-font-spacing: <?php echo absint( $primarydesktopSpacing ); ?>px;

            --yummy-secondary-font-height : <?php echo floatval( $h1desktopHeight ); ?>em;
            --yummy-secondary-font-spacing: <?php echo absint( $h1desktopSpacing ); ?>px;

            --yummy-container-width  : <?php echo absint($container_width); ?>px;
            --yummy-centered-maxwidth: <?php echo absint($fullwidth_centered); ?>px;

            --yummy-btn-font-size   : <?php echo absint( $btndesktopFontSize ); ?>px;
            --yummy-btn-font-height : <?php echo floatval( $btndesktopHeight ); ?>em;
            --yummy-btn-font-spacing: <?php echo absint( $btndesktopSpacing ); ?>px;

            --yummy-widget-spacing: <?php echo absint($widgets_spacing); ?>px;
        }

        .site-header .site-branding .site-title {
            font-size     : <?php echo absint( $sitedesktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $sitedesktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $sitedesktopSpacing ); ?>px;
        }

        .back-to-top{
            --yummy-scroll-to-top-size: <?php echo absint($scroll_top_size); ?>px;
        }

        .elementor-page h1,
        h1{
            font-size   : <?php echo absint( $h1desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h1desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h1desktopSpacing ); ?>px;
        }

        .elementor-page h2,
        h2{
            font-size   : <?php echo absint( $h2desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h2desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h2desktopSpacing ); ?>px;
        }

        .elementor-page h3,
        h3{
            font-size   : <?php echo absint( $h3desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h3desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h3desktopSpacing ); ?>px;
        }

        .elementor-page h4,
        h4{
            font-size   : <?php echo absint( $h4desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h4desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h4desktopSpacing ); ?>px;
        }

        .elementor-page h5,
        h5{
            font-size   : <?php echo absint( $h5desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h5desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h5desktopSpacing ); ?>px;
        }

        .elementor-page h6,
        h6{
            font-size   : <?php echo absint( $h6desktopFontSize ); ?>px;
            line-height   : <?php echo floatval( $h6desktopHeight ); ?>em;
            letter-spacing: <?php echo absint( $h6desktopSpacing ); ?>px;
        }
    }

    @media (min-width: 992px) {
        .single-recipe .widget-area,
        .page-grid{
            --yummy-sidebar-width: <?php echo absint($sidebar_width); ?>%;
        }
    }

    @media (min-width: 767px) and (max-width: 1024px){
        :root{
            --yummy-primary-font-size: <?php echo absint( $primarytabletFontSize ); ?>px;
            --yummy-primary-font-height: <?php echo floatval( $primarytabletHeight ); ?>em;
            --yummy-primary-font-spacing: <?php echo absint( $primarytabletSpacing ); ?>px;

            --yummy-secondary-font-height : <?php echo floatval( $h1tabletHeight ); ?>em;
            --yummy-secondary-font-spacing: <?php echo absint( $h1tabletSpacing ); ?>px;

            --yummy-container-width  : <?php echo absint($tablet_container_width); ?>px;
            --yummy-centered-maxwidth: <?php echo absint($tablet_fullwidth_centered); ?>px;

            --yummy-btn-font-size   : <?php echo absint( $btntabletFontSize ); ?>px;
            --yummy-btn-font-height : <?php echo floatval( $btntabletHeight ); ?>em;
            --yummy-btn-font-spacing: <?php echo absint( $btntabletSpacing ); ?>px;

            --yummy-widget-spacing: <?php echo absint($tablet_widgets_spacing); ?>px;
        }

        .site-branding .site-title {
            font-size   : <?php echo absint( $sitetabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $sitetabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $sitetabletSpacing ); ?>px;
        }

        .site-branding .custom-logo-link img{
			width: <?php echo absint( $tablet_logo_width ); ?>px;
        }


        .back-to-top{
            --yummy-scroll-to-top-size: <?php echo absint($tablet_scroll_top_size); ?>px;
        }

        .elementor-page h1,
        h1{
            font-size   : <?php echo absint( $h1tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h1tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h1tabletSpacing ); ?>px;
        }

        .elementor-page h2,
        h2{
            font-size   : <?php echo absint( $h2tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h2tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h2tabletSpacing ); ?>px;
        }

        .elementor-page h3,
        h3{
            font-size   : <?php echo absint( $h3tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h3tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h3tabletSpacing ); ?>px;
        }

        .elementor-page h4,
        h4{
            font-size   : <?php echo absint( $h4tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h4tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h4tabletSpacing ); ?>px;
        }

        .elementor-page h5,
        h5{
            font-size   : <?php echo absint( $h5tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h5tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h5tabletSpacing ); ?>px;
        }

        .elementor-page h6,
        h6{
            font-size   : <?php echo absint( $h6tabletFontSize ); ?>px;
            line-height   : <?php echo floatval( $h6tabletHeight ); ?>em;
            letter-spacing: <?php echo absint( $h6tabletSpacing ); ?>px;
        }
    }

    @media (max-width: 767px){
        :root{
            --yummy-primary-font-size: <?php echo absint( $primarymobileFontSize ); ?>px;
            --yummy-primary-font-height: <?php echo floatval( $primarymobileHeight ); ?>em;
            --yummy-primary-font-spacing: <?php echo absint( $primarymobileSpacing ); ?>px;

            --yummy-secondary-font-height : <?php echo floatval( $h1mobileHeight ); ?>em;
            --yummy-secondary-font-spacing: <?php echo absint( $h1mobileSpacing ); ?>px;

            --yummy-container-width  : <?php echo absint($mobile_container_width); ?>px;
            --yummy-centered-maxwidth: <?php echo absint($mobile_fullwidth_centered); ?>px;

            --yummy-btn-font-size   : <?php echo absint( $btnmobileFontSize ); ?>px;
            --yummy-btn-font-height : <?php echo floatval( $btnmobileHeight ); ?>em;
            --yummy-btn-font-spacing: <?php echo absint( $btnmobileSpacing ); ?>px;

            --yummy-widget-spacing: <?php echo absint($mobile_widgets_spacing); ?>px;
        }

        .site-branding .site-title{
            font-size   : <?php echo absint( $sitemobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $sitemobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $sitemobileSpacing ); ?>px;
        }

        .site-branding .custom-logo-link img{
            width: <?php echo absint( $mobile_logo_width ); ?>px;
        }

        .back-to-top{
            --yummy-scroll-to-top-size: <?php echo absint($mobile_scroll_top_size); ?>px;
        }

        .elementor-page h1,
        h1{
            font-size   : <?php echo absint( $h1mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h1mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h1mobileSpacing ); ?>px;
        }

        .elementor-page h2,
        h2{
            font-size   : <?php echo absint( $h2mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h2mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h2mobileSpacing ); ?>px;
        }

        .elementor-page h3,
        h3{
            font-size   : <?php echo absint( $h3mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h3mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h3mobileSpacing ); ?>px;
        }

        .elementor-page h4,
        h4{
            font-size   : <?php echo absint( $h4mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h4mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h4mobileSpacing ); ?>px;
        }

        .elementor-page h5,
        h5{
            font-size   : <?php echo absint( $h5mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h5mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h5mobileSpacing ); ?>px;
        }

        .elementor-page h6,
        h6{
            font-size   : <?php echo absint( $h6mobileFontSize ); ?>px;
            line-height   : <?php echo floatval( $h6mobileHeight ); ?>em;
            letter-spacing: <?php echo absint( $h6mobileSpacing ); ?>px;
        }
    }

    .tr-about-section{
        --yummy-abt-title-color: <?php echo yummy_bites_sanitize_rgba( $about_title_color ); ?>;
        --yummy-abt-desc-color: <?php echo yummy_bites_sanitize_rgba( $about_desc_color ); ?>;
        --yummy-bg-color: <?php echo yummy_bites_sanitize_rgba( $about_bg_color ); ?>;
    }

    <?php if( yummy_bites_is_elementor_activated()){ ?>
        :root {
            --e-global-color-primary_color     : <?php echo yummy_bites_sanitize_rgba( $primary_color ); ?>;
            --e-global-color-secondary_color   : <?php echo yummy_bites_sanitize_rgba( $secondary_color ); ?>;
            --e-global-color-body_font_color   : <?php echo yummy_bites_sanitize_rgba( $body_font_color ); ?>;
            --e-global-color-heading_color     : <?php echo yummy_bites_sanitize_rgba( $heading_color ); ?>;
            --e-global-color-accent_color_one  : <?php echo yummy_bites_sanitize_rgba( $accent_color_one ); ?>;
            --e-global-color-accent_color_two  : <?php echo yummy_bites_sanitize_rgba( $accent_color_two ); ?>;
        }
    <?php }
    echo "</style>";
}
endif;
add_action( 'wp_head', 'yummy_bites_dynamic_css', 99 );

/**
 * convert hex to rgb
 * @link https://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
*/
function yummy_bites_hex2rgb($hex) {
    if($hex[0] === '#' ){
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    } else {
        $hex = str_replace("rgba(", "", $hex);
        $hex = str_replace(")", "", $hex);
        $rgb = explode(",", $hex );
        $opacity = array_pop($rgb); //removing opacity value from rgba

        return $rgb;
    }
}

/**
 * Convert '#' to '%23'
*/
function yummy_bites_hash_to_percent23( $color_code ){
    $color_code = str_replace( "#", "%23", $color_code );
    return $color_code;
}

if ( ! function_exists( 'yummy_bites_gutenberg_inline_style' ) ) : 
/**
 * Gutenberg Dynamic Style
 */
function yummy_bites_gutenberg_inline_style(){
    $typo_defaults   = yummy_bites_get_typography_defaults();
    $defaults        = yummy_bites_get_color_defaults();
    $button_defaults = yummy_bites_get_button_defaults();
	$layout_defaults = yummy_bites_get_general_defaults();
    
    $primary_font   = wp_parse_args( get_theme_mod( 'primary_font' ), $typo_defaults['primary_font'] );
    $button         = wp_parse_args( get_theme_mod( 'button' ), $typo_defaults['button'] );
    $heading_one    = wp_parse_args( get_theme_mod( 'heading_one' ), $typo_defaults['heading_one'] );
	$heading_two    = wp_parse_args( get_theme_mod( 'heading_two' ), $typo_defaults['heading_two'] );
	$heading_three  = wp_parse_args( get_theme_mod( 'heading_three' ), $typo_defaults['heading_three'] );
	$heading_four   = wp_parse_args( get_theme_mod( 'heading_four' ), $typo_defaults['heading_four'] );
	$heading_five   = wp_parse_args( get_theme_mod( 'heading_five' ), $typo_defaults['heading_five'] );
	$heading_six    = wp_parse_args( get_theme_mod( 'heading_six' ), $typo_defaults['heading_six'] );

    //Primary Font variables
    $primarydesktopFontSize = isset(  $primary_font['desktop']['font_size'] ) ? $primary_font['desktop']['font_size'] : $typo_defaults['primary_font']['desktop']['font_size'];
    $primarydesktopSpacing  = isset(  $primary_font['desktop']['letter_spacing'] ) ? $primary_font['desktop']['letter_spacing'] : $typo_defaults['primary_font']['desktop']['letter_spacing'];
    $primarydesktopHeight   = isset(  $primary_font['desktop']['line_height'] ) ? $primary_font['desktop']['line_height'] : $typo_defaults['primary_font']['desktop']['line_height'];
    $primarytabletFontSize  = isset(  $primary_font['tablet']['font_size'] ) ? $primary_font['tablet']['font_size'] : $typo_defaults['primary_font']['tablet']['font_size'];
    $primarytabletSpacing   = isset(  $primary_font['tablet']['letter_spacing'] ) ? $primary_font['tablet']['letter_spacing'] : $typo_defaults['primary_font']['tablet']['letter_spacing'];
    $primarytabletHeight    = isset(  $primary_font['tablet']['line_height'] ) ? $primary_font['tablet']['line_height'] : $typo_defaults['primary_font']['tablet']['line_height'];
    $primarymobileFontSize  = isset(  $primary_font['mobile']['font_size'] ) ? $primary_font['mobile']['font_size'] : $typo_defaults['primary_font']['mobile']['font_size'];
    $primarymobileSpacing   = isset(  $primary_font['mobile']['letter_spacing'] ) ? $primary_font['mobile']['letter_spacing'] : $typo_defaults['primary_font']['mobile']['letter_spacing'];
    $primarymobileHeight    = isset(  $primary_font['mobile']['line_height'] ) ? $primary_font['mobile']['line_height'] : $typo_defaults['primary_font']['mobile']['line_height'];

    //Button variables
    $btndesktopFontSize = isset(  $button['desktop']['font_size'] ) ? $button['desktop']['font_size'] : $typo_defaults['button']['desktop']['font_size'];
    $btndesktopSpacing  = isset(  $button['desktop']['letter_spacing'] ) ? $button['desktop']['letter_spacing'] : $typo_defaults['button']['desktop']['letter_spacing'];
    $btndesktopHeight   = isset(  $button['desktop']['line_height'] ) ? $button['desktop']['line_height'] : $typo_defaults['button']['desktop']['line_height'];
    $btntabletFontSize  = isset(  $button['tablet']['font_size'] ) ? $button['tablet']['font_size'] : $typo_defaults['button']['tablet']['font_size'];
    $btntabletSpacing   = isset(  $button['tablet']['letter_spacing'] ) ? $button['tablet']['letter_spacing'] : $typo_defaults['button']['tablet']['letter_spacing'];
    $btntabletHeight    = isset(  $button['tablet']['line_height'] ) ? $button['tablet']['line_height'] : $typo_defaults['button']['tablet']['line_height'];
    $btnmobileFontSize  = isset(  $button['mobile']['font_size'] ) ? $button['mobile']['font_size'] : $typo_defaults['button']['mobile']['font_size'];
    $btnmobileSpacing   = isset(  $button['mobile']['letter_spacing'] ) ? $button['mobile']['letter_spacing'] : $typo_defaults['button']['mobile']['letter_spacing'];
    $btnmobileHeight    = isset(  $button['mobile']['line_height'] ) ? $button['mobile']['line_height'] : $typo_defaults['button']['mobile']['line_height'];

    //Heading 1 variables
    $h1desktopFontSize = isset(  $heading_one['desktop']['font_size'] ) ? $heading_one['desktop']['font_size'] : $typo_defaults['heading_one']['desktop']['font_size'];
    $h1desktopSpacing  = isset(  $heading_one['desktop']['letter_spacing'] ) ? $heading_one['desktop']['letter_spacing'] : $typo_defaults['heading_one']['desktop']['letter_spacing'];
    $h1desktopHeight   = isset(  $heading_one['desktop']['line_height'] ) ? $heading_one['desktop']['line_height'] : $typo_defaults['heading_one']['desktop']['line_height'];
    $h1tabletFontSize  = isset(  $heading_one['tablet']['font_size'] ) ? $heading_one['tablet']['font_size'] : $typo_defaults['heading_one']['tablet']['font_size'];
    $h1tabletSpacing   = isset(  $heading_one['tablet']['letter_spacing'] ) ? $heading_one['tablet']['letter_spacing'] : $typo_defaults['heading_one']['tablet']['letter_spacing'];
    $h1tabletHeight    = isset(  $heading_one['tablet']['line_height'] ) ? $heading_one['tablet']['line_height'] : $typo_defaults['heading_one']['tablet']['line_height'];
    $h1mobileFontSize  = isset(  $heading_one['mobile']['font_size'] ) ? $heading_one['mobile']['font_size'] : $typo_defaults['heading_one']['mobile']['font_size'];
    $h1mobileSpacing   = isset(  $heading_one['mobile']['letter_spacing'] ) ? $heading_one['mobile']['letter_spacing'] : $typo_defaults['heading_one']['mobile']['letter_spacing'];
    $h1mobileHeight    = isset(  $heading_one['mobile']['line_height'] ) ? $heading_one['mobile']['line_height'] : $typo_defaults['heading_one']['mobile']['line_height'];
    
    //Heading 2 variables
    $h2desktopFontSize = isset(  $heading_two['desktop']['font_size'] ) ? $heading_two['desktop']['font_size'] : $typo_defaults['heading_two']['desktop']['font_size'];
    $h2desktopSpacing  = isset(  $heading_two['desktop']['letter_spacing'] ) ? $heading_two['desktop']['letter_spacing'] : $typo_defaults['heading_two']['desktop']['letter_spacing'];
    $h2desktopHeight   = isset(  $heading_two['desktop']['line_height'] ) ? $heading_two['desktop']['line_height'] : $typo_defaults['heading_two']['desktop']['line_height'];
    $h2tabletFontSize  = isset(  $heading_two['tablet']['font_size'] ) ? $heading_two['tablet']['font_size'] : $typo_defaults['heading_two']['tablet']['font_size'];
    $h2tabletSpacing   = isset(  $heading_two['tablet']['letter_spacing'] ) ? $heading_two['tablet']['letter_spacing'] : $typo_defaults['heading_two']['tablet']['letter_spacing'];
    $h2tabletHeight    = isset(  $heading_two['tablet']['line_height'] ) ? $heading_two['tablet']['line_height'] : $typo_defaults['heading_two']['tablet']['line_height'];
    $h2mobileFontSize  = isset(  $heading_two['mobile']['font_size'] ) ? $heading_two['mobile']['font_size'] : $typo_defaults['heading_two']['mobile']['font_size'];
    $h2mobileSpacing   = isset(  $heading_two['mobile']['letter_spacing'] ) ? $heading_two['mobile']['letter_spacing'] : $typo_defaults['heading_two']['mobile']['letter_spacing'];
    $h2mobileHeight    = isset(  $heading_two['mobile']['line_height'] ) ? $heading_two['mobile']['line_height'] : $typo_defaults['heading_two']['mobile']['line_height'];
    
    //Heading 3 variables
    $h3desktopFontSize = isset(  $heading_three['desktop']['font_size'] ) ? $heading_three['desktop']['font_size'] : $typo_defaults['heading_three']['desktop']['font_size'];
    $h3desktopSpacing  = isset(  $heading_three['desktop']['letter_spacing'] ) ? $heading_three['desktop']['letter_spacing'] : $typo_defaults['heading_three']['desktop']['letter_spacing'];
    $h3desktopHeight   = isset(  $heading_three['desktop']['line_height'] ) ? $heading_three['desktop']['line_height'] : $typo_defaults['heading_three']['desktop']['line_height'];
    $h3tabletFontSize  = isset(  $heading_three['tablet']['font_size'] ) ? $heading_three['tablet']['font_size'] : $typo_defaults['heading_three']['tablet']['font_size'];
    $h3tabletSpacing   = isset(  $heading_three['tablet']['letter_spacing'] ) ? $heading_three['tablet']['letter_spacing'] : $typo_defaults['heading_three']['tablet']['letter_spacing'];
    $h3tabletHeight    = isset(  $heading_three['tablet']['line_height'] ) ? $heading_three['tablet']['line_height'] : $typo_defaults['heading_three']['tablet']['line_height'];
    $h3mobileFontSize  = isset(  $heading_three['mobile']['font_size'] ) ? $heading_three['mobile']['font_size'] : $typo_defaults['heading_three']['mobile']['font_size'];
    $h3mobileSpacing   = isset(  $heading_three['mobile']['letter_spacing'] ) ? $heading_three['mobile']['letter_spacing'] : $typo_defaults['heading_three']['mobile']['letter_spacing'];
    $h3mobileHeight    = isset(  $heading_three['mobile']['line_height'] ) ? $heading_three['mobile']['line_height'] : $typo_defaults['heading_three']['mobile']['line_height'];
    
    //Heading 4 variables
    $h4desktopFontSize = isset(  $heading_four['desktop']['font_size'] ) ? $heading_four['desktop']['font_size'] : $typo_defaults['heading_four']['desktop']['font_size'];
    $h4desktopSpacing  = isset(  $heading_four['desktop']['letter_spacing'] ) ? $heading_four['desktop']['letter_spacing'] : $typo_defaults['heading_four']['desktop']['letter_spacing'];
    $h4desktopHeight   = isset(  $heading_four['desktop']['line_height'] ) ? $heading_four['desktop']['line_height'] : $typo_defaults['heading_four']['desktop']['line_height'];
    $h4tabletFontSize  = isset(  $heading_four['tablet']['font_size'] ) ? $heading_four['tablet']['font_size'] : $typo_defaults['heading_four']['tablet']['font_size'];
    $h4tabletSpacing   = isset(  $heading_four['tablet']['letter_spacing'] ) ? $heading_four['tablet']['letter_spacing'] : $typo_defaults['heading_four']['tablet']['letter_spacing'];
    $h4tabletHeight    = isset(  $heading_four['tablet']['line_height'] ) ? $heading_four['tablet']['line_height'] : $typo_defaults['heading_four']['tablet']['line_height'];
    $h4mobileFontSize  = isset(  $heading_four['mobile']['font_size'] ) ? $heading_four['mobile']['font_size'] : $typo_defaults['heading_four']['mobile']['font_size'];
    $h4mobileSpacing   = isset(  $heading_four['mobile']['letter_spacing'] ) ? $heading_four['mobile']['letter_spacing'] : $typo_defaults['heading_four']['mobile']['letter_spacing'];
    $h4mobileHeight    = isset(  $heading_four['mobile']['line_height'] ) ? $heading_four['mobile']['line_height'] : $typo_defaults['heading_four']['mobile']['line_height'];
    
    //Heading 5 variables
    $h5desktopFontSize = isset(  $heading_five['desktop']['font_size'] ) ? $heading_five['desktop']['font_size'] : $typo_defaults['heading_five']['desktop']['font_size'];
    $h5desktopSpacing  = isset(  $heading_five['desktop']['letter_spacing'] ) ? $heading_five['desktop']['letter_spacing'] : $typo_defaults['heading_five']['desktop']['letter_spacing'];
    $h5desktopHeight   = isset(  $heading_five['desktop']['line_height'] ) ? $heading_five['desktop']['line_height'] : $typo_defaults['heading_five']['desktop']['line_height'];
    $h5tabletFontSize  = isset(  $heading_five['tablet']['font_size'] ) ? $heading_five['tablet']['font_size'] : $typo_defaults['heading_five']['tablet']['font_size'];
    $h5tabletSpacing   = isset(  $heading_five['tablet']['letter_spacing'] ) ? $heading_five['tablet']['letter_spacing'] : $typo_defaults['heading_five']['tablet']['letter_spacing'];
    $h5tabletHeight    = isset(  $heading_five['tablet']['line_height'] ) ? $heading_five['tablet']['line_height'] : $typo_defaults['heading_five']['tablet']['line_height'];
    $h5mobileFontSize  = isset(  $heading_five['mobile']['font_size'] ) ? $heading_five['mobile']['font_size'] : $typo_defaults['heading_five']['mobile']['font_size'];
    $h5mobileSpacing   = isset(  $heading_five['mobile']['letter_spacing'] ) ? $heading_five['mobile']['letter_spacing'] : $typo_defaults['heading_five']['mobile']['letter_spacing'];
    $h5mobileHeight    = isset(  $heading_five['mobile']['line_height'] ) ? $heading_five['mobile']['line_height'] : $typo_defaults['heading_five']['mobile']['line_height'];
    
    //Heading 6 variables
    $h6desktopFontSize = isset(  $heading_six['desktop']['font_size'] ) ? $heading_six['desktop']['font_size'] : $typo_defaults['heading_six']['desktop']['font_size'];
    $h6desktopSpacing  = isset(  $heading_six['desktop']['letter_spacing'] ) ? $heading_six['desktop']['letter_spacing'] : $typo_defaults['heading_six']['desktop']['letter_spacing'];
    $h6desktopHeight   = isset(  $heading_six['desktop']['line_height'] ) ? $heading_six['desktop']['line_height'] : $typo_defaults['heading_six']['desktop']['line_height'];
    $h6tabletFontSize  = isset(  $heading_six['tablet']['font_size'] ) ? $heading_six['tablet']['font_size'] : $typo_defaults['heading_six']['tablet']['font_size'];
    $h6tabletSpacing   = isset(  $heading_six['tablet']['letter_spacing'] ) ? $heading_six['tablet']['letter_spacing'] : $typo_defaults['heading_six']['tablet']['letter_spacing'];
    $h6tabletHeight    = isset(  $heading_six['tablet']['line_height'] ) ? $heading_six['tablet']['line_height'] : $typo_defaults['heading_six']['tablet']['line_height'];
    $h6mobileFontSize  = isset(  $heading_six['mobile']['font_size'] ) ? $heading_six['mobile']['font_size'] : $typo_defaults['heading_six']['mobile']['font_size'];
    $h6mobileSpacing   = isset(  $heading_six['mobile']['letter_spacing'] ) ? $heading_six['mobile']['letter_spacing'] : $typo_defaults['heading_six']['mobile']['letter_spacing'];
    $h6mobileHeight    = isset(  $heading_six['mobile']['line_height'] ) ? $heading_six['mobile']['line_height'] : $typo_defaults['heading_six']['mobile']['line_height'];

    $primary_font_family       = yummy_bites_get_font_family( $primary_font );
    $btn_font_family           = yummy_bites_get_font_family( $button );
    $heading_one_font_family   = yummy_bites_get_font_family( $heading_one );
    $heading_two_font_family   = yummy_bites_get_font_family( $heading_two );
    $heading_three_font_family = yummy_bites_get_font_family( $heading_three );
    $heading_four_font_family  = yummy_bites_get_font_family( $heading_four );
    $heading_five_font_family  = yummy_bites_get_font_family( $heading_five );
    $heading_six_font_family   = yummy_bites_get_font_family( $heading_six );

    $btnFontFamily  = $btn_font_family === '"Default Family"' ? 'inherit' : $btn_font_family;
    $h1FontFamily   = $heading_one_font_family === '"Default Family"' ? 'inherit' : $heading_one_font_family;
    $h2FontFamily   = $heading_two_font_family === '"Default Family"' ? 'inherit' : $heading_two_font_family;
    $h3FontFamily   = $heading_three_font_family === '"Default Family"' ? 'inherit' : $heading_three_font_family;
    $h4FontFamily   = $heading_four_font_family === '"Default Family"' ? 'inherit' : $heading_four_font_family;
    $h5FontFamily   = $heading_five_font_family === '"Default Family"' ? 'inherit' : $heading_five_font_family;
    $h6FontFamily   = $heading_six_font_family === '"Default Family"' ? 'inherit' : $heading_six_font_family;

    $primary_color    = get_theme_mod( 'primary_color', $defaults['primary_color'] );
    $rgb              = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $primary_color ) );
    $secondary_color  = get_theme_mod( 'secondary_color', $defaults['secondary_color'] );
    $rgb2             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $secondary_color ) );
    $body_font_color  = get_theme_mod( 'body_font_color', $defaults['body_font_color'] );
    $rgb3             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $body_font_color ) );
    $heading_color    = get_theme_mod( 'heading_color', $defaults['heading_color'] );
    $rgb4             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $heading_color ) );
    $background_color = get_theme_mod( 'site_bg_color', $defaults['site_bg_color'] );
    $rgb5             = yummy_bites_hex2rgb( yummy_bites_sanitize_rgba( $background_color ) );

    $button_roundness = get_theme_mod( 'button_roundness', $button_defaults['button_roundness'] );
    $button_padding   = get_theme_mod( 'button_padding', $button_defaults['button_padding'] );

    $btnRoundTop    = isset(  $button_roundness['top'] ) ? $button_roundness['top'] : $button_defaults['button_roundness']['top'];
    $btnRoundRight  = isset(  $button_roundness['right'] ) ? $button_roundness['right'] : $button_defaults['button_roundness']['right'];
    $btnRoundBottom = isset(  $button_roundness['bottom'] ) ? $button_roundness['bottom'] : $button_defaults['button_roundness']['bottom'];
    $btnRoundLeft   = isset(  $button_roundness['left'] ) ? $button_roundness['left'] : $button_defaults['button_roundness']['left'];

    $btnPaddingTop    = isset(  $button_padding['top'] ) ? $button_padding['top'] : $button_defaults['button_padding']['top'];
    $btnPaddingRight  = isset(  $button_padding['right'] ) ? $button_padding['right'] : $button_defaults['button_padding']['right'];
    $btnPaddingBottom = isset(  $button_padding['bottom'] ) ? $button_padding['bottom'] : $button_defaults['button_padding']['bottom'];
    $btnPaddingLeft   = isset(  $button_padding['left'] ) ? $button_padding['left'] : $button_defaults['button_padding']['left'];
    
    //Button Color
    $btn_text_color         = get_theme_mod( 'btn_text_color_initial', $defaults['btn_text_color_initial'] );
    $btn_bg_color           = get_theme_mod( 'btn_bg_color_initial', $defaults['btn_bg_color_initial'] );
    $btn_text_hover_color   = get_theme_mod( 'btn_text_color_hover', $defaults['btn_text_color_hover'] );
    $btn_bg_hover_color     = get_theme_mod( 'btn_bg_color_hover', $defaults['btn_bg_color_hover'] );
    $btn_border_color       = get_theme_mod( 'btn_border_color_initial', $defaults['btn_border_color_initial'] );
    $btn_border_hover_color = get_theme_mod( 'btn_border_color_hover', $defaults['btn_border_color_hover'] );

    $container_width        = get_theme_mod( 'container_width', $layout_defaults['container_width'] );
	$tablet_container_width = get_theme_mod( 'tablet_container_width', $layout_defaults['tablet_container_width'] );
	$mobile_container_width = get_theme_mod( 'mobile_container_width', $layout_defaults['mobile_container_width'] );

    $custom_css = ':root .block-editor-page {
        --yummy-primary-color   : ' . yummy_bites_sanitize_rgba( $primary_color ) . ';
        --yummy-primary-color-rgb: ' . $rgb[0] . ',' . $rgb[1] .',' . $rgb[2] . ';
        --yummy-secondary-color   : ' . yummy_bites_sanitize_rgba( $secondary_color ) . ';
        --yummy-secondary-color-rgb: ' . $rgb2[0] . ',' . $rgb2[1] .',' . $rgb2[2] . ';
        --yummy-font-color : ' . yummy_bites_sanitize_rgba( $body_font_color ) . ';
        --yummy-font-color-rgb:' . $rgb3[0] . ',' . $rgb3[1] .',' . $rgb3[2] . ';
        --yummy-heading-color   : ' . yummy_bites_sanitize_rgba( $heading_color ) . ';
        --yummy-heading-color-rgb:' . $rgb4[0] . ',' . $rgb4[1] .',' . $rgb4[2] . ';
        --yummy-background-color: ' . yummy_bites_sanitize_rgba( $background_color ) . ';
        --yummy-background-color-rgb:' . $rgb5[0] . ',' . $rgb5[1] .',' . $rgb5[2] . ';

        --yummy-btn-text-initial-color  : ' . yummy_bites_sanitize_rgba( $btn_text_color ) . ';
        --yummy-btn-text-hover-color    : ' . yummy_bites_sanitize_rgba( $btn_text_hover_color ) . ';
        --yummy-btn-bg-initial-color    : ' . yummy_bites_sanitize_rgba( $btn_bg_color ) . ';
        --yummy-btn-bg-hover-color      : ' . yummy_bites_sanitize_rgba( $btn_bg_hover_color ) . ';
        --yummy-btn-border-initial-color: ' . yummy_bites_sanitize_rgba( $btn_border_color ) . ';
        --yummy-btn-border-hover-color  : ' . yummy_bites_sanitize_rgba( $btn_border_hover_color ) . ';

        --yummy-primary-font   : ' . wp_kses_post( $primary_font_family ) . ';
        --yummy-primary-font-weight   : ' . esc_html( $primary_font['weight'] ) . ';
        --yummy-primary-font-transform: ' . esc_html( $primary_font['transform'] ) . ';

        --yummy-secondary-font   : ' . wp_kses_post( $h1FontFamily ) . ';
        --yummy-secondary-font-weight   : ' . esc_html( $heading_one['weight'] ) . ';

        --yummy-btn-font-family     : ' . wp_kses_post( $btnFontFamily ) . ';
        --yummy-btn-font-weight     : ' . esc_html( $button['weight'] ) . ';
        --yummy-btn-font-transform  : ' . esc_html( $button['transform'] ) . ';
        --yummy-btn-roundness-top   : ' . absint( $btnRoundTop ) . 'px;
        --yummy-btn-roundness-right : ' . absint( $btnRoundRight ) . 'px;
        --yummy-btn-roundness-bottom: ' . absint( $btnRoundBottom ) . 'px;
        --yummy-btn-roundness-left  : ' . absint( $btnRoundLeft ) . 'px;
        --yummy-btn-padding-top     : ' . absint( $btnPaddingTop) . 'px;
        --yummy-btn-padding-right   : ' . absint( $btnPaddingRight ) . 'px;
        --yummy-btn-padding-bottom  : ' . absint( $btnPaddingBottom ) . 'px;
        --yummy-btn-padding-left    : ' . absint( $btnPaddingLeft ) . 'px;
    }
    .block-editor-page .editor-styles-wrapper h1{
        font-family :' . wp_kses_post( $h1FontFamily ) . '; 
        text-transform:' . esc_html( $heading_one['transform'] ) . ';       
        font-weight:' . esc_html( $heading_one['weight'] ) . '; 
    }
    .block-editor-page .editor-styles-wrapper h2{
        font-family :' . wp_kses_post( $h2FontFamily ) . '; 
        text-transform:' . esc_html( $heading_two['transform'] ) . ';       
        font-weight:' . esc_html( $heading_two['weight'] ) . '; 
    }
    .block-editor-page .editor-styles-wrapper h3{
        font-family :' . wp_kses_post( $h3FontFamily ) . '; 
        text-transform:' . esc_html( $heading_three['transform'] ) . ';       
        font-weight:' . esc_html( $heading_three['weight'] ) . '; 
    }
    .block-editor-page .editor-styles-wrapper h4{
        font-family :' . wp_kses_post( $h4FontFamily ) . '; 
        text-transform:' . esc_html( $heading_four['transform'] ) . ';       
        font-weight:' . esc_html( $heading_four['weight'] ) . '; 
    }
    .block-editor-page .editor-styles-wrapper h5{
        font-family :' . wp_kses_post( $h5FontFamily ) . '; 
        text-transform:' . esc_html( $heading_five['transform'] ) . ';       
        font-weight:' . esc_html( $heading_five['weight'] ) . '; 
    }
    .block-editor-page .editor-styles-wrapper h6{
        font-family :' . wp_kses_post( $h6FontFamily ) . '; 
        text-transform:' . esc_html( $heading_six['transform'] ) . ';       
        font-weight:' . esc_html( $heading_six['weight'] ) . '; 
    }
    @media (min-width: 1024px){
        :root .block-editor-page .editor-styles-wrapper{
            --yummy-primary-font-size   : ' . absint( $primarydesktopFontSize ) . 'px;
            --yummy-primary-font-height : ' . floatval( $primarydesktopHeight ) . 'em;
            --yummy-primary-font-spacing: ' . absint( $primarydesktopSpacing ) . 'px;
            
            --yummy-secondary-font-spacing: ' . absint( $h1desktopSpacing ) . 'px;            
            --yummy-secondary-font-height: ' . floatval( $h1desktopHeight ) . 'em;

            --yummy-container-width  :' . absint($container_width) . 'px;

            --yummy-btn-font-size   : ' . absint( $btndesktopFontSize ) . 'px;
            --yummy-btn-font-height : ' . floatval( $btndesktopHeight ) . 'em;
            --yummy-btn-font-spacing: ' . absint( $btndesktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h1{
            font-size   : ' . absint( $h1desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h1desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h1desktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h2{
            font-size   : ' . absint( $h2desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h2desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h2desktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h3{
            font-size   : ' . absint( $h3desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h3desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h3desktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h4{
            font-size   : ' . absint( $h4desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h4desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h4desktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h5{
            font-size   : ' . absint( $h5desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h5desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h5desktopSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h6{
            font-size   : ' . absint( $h6desktopFontSize ) . 'px;
            line-height   : ' . floatval( $h6desktopHeight ) . 'em;
            letter-spacing: ' . absint( $h6desktopSpacing ) . 'px;
        }
    }
    @media (min-width: 767px) and (max-width: 1024px){
        :root .block-editor-page .editor-styles-wrapper{
            --yummy-primary-font-size   : ' . absint( $primarytabletFontSize ) . 'px;
            --yummy-primary-font-height : ' . floatval( $primarytabletHeight ) . 'em;
            --yummy-primary-font-spacing: ' . absint( $primarytabletSpacing ) . 'px;

            --yummy-secondary-font-spacing: ' . absint( $h1tabletSpacing ) . 'px;            
            --yummy-secondary-font-height: ' . floatval( $h1tabletHeight ) . 'em;

            --yummy-container-width  :' . absint($tablet_container_width) . 'px;

            --yummy-btn-font-size   : ' . absint( $btntabletFontSize ) . 'px;
            --yummy-btn-font-height : ' . floatval( $btntabletHeight ) . 'em;
            --yummy-btn-font-spacing: ' . absint( $btntabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h1{
            font-size   : ' . absint( $h1tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h1tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h1tabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h2{
            font-size   : ' . absint( $h2tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h2tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h2tabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h3{
            font-size   : ' . absint( $h3tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h3tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h3tabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h4{
            font-size   : ' . absint( $h4tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h4tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h4tabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h5{
            font-size   : ' . absint( $h5tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h5tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h5tabletSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h6{
            font-size   : ' . absint( $h6tabletFontSize ) . 'px;
            line-height   : ' . floatval( $h6tabletHeight ) . 'em;
            letter-spacing: ' . absint( $h6tabletSpacing ) . 'px;
        }
    }
    @media (max-width: 767px){
        :root .block-editor-page .editor-styles-wrapper{
            --yummy-primary-font-size   : ' . absint( $primarymobileFontSize ) . 'px;
            --yummy-primary-font-height : ' . floatval( $primarymobileHeight ) . 'em;
            --yummy-primary-font-spacing: ' . absint( $primarymobileSpacing ) . 'px;
            
            --yummy-secondary-font-spacing: ' . absint( $h1mobileSpacing ) . 'px;            
            --yummy-secondary-font-height: ' . floatval( $h1mobileHeight ) . 'em;

            --yummy-container-width  : ' . absint($mobile_container_width) . 'px;

            --yummy-btn-font-size   : ' . absint( $btnmobileFontSize ) . 'px;
            --yummy-btn-font-height : ' . floatval( $btnmobileHeight ) . 'em;
            --yummy-btn-font-spacing: ' . absint( $btnmobileSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h1{
            font-size   : ' . absint( $h1mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h1mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h1mobileSpacing ) . 'px;
        }
        .block-editor-page .editor-styles-wrapper h2{
            font-size   : ' . absint( $h2mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h2mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h2mobileSpacing ) . 'px;
        }
        .block-editor-page h3{
            font-size   : ' . absint( $h3mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h3mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h3mobileSpacing ) . 'px;
        }
        .block-editor-page h4{
            font-size   : ' . absint( $h4mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h4mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h4mobileSpacing ) . 'px;
        }
        .block-editor-page h5{
            font-size   : ' . absint( $h5mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h5mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h5mobileSpacing ) . 'px;
        }
        .block-editor-page h6{
            font-size   : ' . absint( $h6mobileFontSize ) . 'px;
            line-height   : ' . floatval( $h6mobileHeight ) . 'em;
            letter-spacing: ' . absint( $h6mobileSpacing ) . 'px;
        }
    }';

    return $custom_css;
}
endif;

/**
 * Add Dynamic SVG
 *
 * @return void
 */
function yummy_bites_other_css(){
    echo "<style id='yummy-bites-dynamic-css' type='text/css' media='all'>"; ?>
       
        blockquote::before{
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg width='72' height='54' viewBox='0 0 72 54' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M16.32 54C11.2 54 7.168 52.1684 4.224 48.5053C1.408 44.7158 0 39.7895 0 33.7263C0 26.5263 1.856 19.9579 5.568 14.0211C9.408 8.08422 15.104 3.41053 22.656 0L32.64 8.14737C27.392 9.91579 22.976 12.5684 19.392 16.1053C15.808 19.5158 13.44 23.3684 12.288 27.6632L13.248 28.0421C14.272 27.0316 16.064 26.5263 18.624 26.5263C21.824 26.5263 24.64 27.7263 27.072 30.1263C29.632 32.4 30.912 35.6211 30.912 39.7895C30.912 43.8316 29.504 47.2421 26.688 50.0211C23.872 52.6737 20.416 54 16.32 54ZM55.68 54C50.56 54 46.528 52.1684 43.584 48.5053C40.768 44.7158 39.36 39.7895 39.36 33.7263C39.36 26.5263 41.216 19.9579 44.928 14.0211C48.768 8.08422 54.464 3.41053 62.016 0L72 8.14737C66.752 9.91579 62.336 12.5684 58.752 16.1053C55.168 19.5158 52.8 23.3684 51.648 27.6632L52.608 28.0421C53.632 27.0316 55.424 26.5263 57.984 26.5263C61.184 26.5263 64 27.7263 66.432 30.1263C68.992 32.4 70.272 35.6211 70.272 39.7895C70.272 43.8316 68.864 47.2421 66.048 50.0211C63.232 52.6737 59.776 54 55.68 54Z' fill='%23FDEFEF'/%3E%3C/svg%3E%0A");
            mask-image: url("data:image/svg+xml,%3Csvg width='72' height='54' viewBox='0 0 72 54' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M16.32 54C11.2 54 7.168 52.1684 4.224 48.5053C1.408 44.7158 0 39.7895 0 33.7263C0 26.5263 1.856 19.9579 5.568 14.0211C9.408 8.08422 15.104 3.41053 22.656 0L32.64 8.14737C27.392 9.91579 22.976 12.5684 19.392 16.1053C15.808 19.5158 13.44 23.3684 12.288 27.6632L13.248 28.0421C14.272 27.0316 16.064 26.5263 18.624 26.5263C21.824 26.5263 24.64 27.7263 27.072 30.1263C29.632 32.4 30.912 35.6211 30.912 39.7895C30.912 43.8316 29.504 47.2421 26.688 50.0211C23.872 52.6737 20.416 54 16.32 54ZM55.68 54C50.56 54 46.528 52.1684 43.584 48.5053C40.768 44.7158 39.36 39.7895 39.36 33.7263C39.36 26.5263 41.216 19.9579 44.928 14.0211C48.768 8.08422 54.464 3.41053 62.016 0L72 8.14737C66.752 9.91579 62.336 12.5684 58.752 16.1053C55.168 19.5158 52.8 23.3684 51.648 27.6632L52.608 28.0421C53.632 27.0316 55.424 26.5263 57.984 26.5263C61.184 26.5263 64 27.7263 66.432 30.1263C68.992 32.4 70.272 35.6211 70.272 39.7895C70.272 43.8316 68.864 47.2421 66.048 50.0211C63.232 52.6737 59.776 54 55.68 54Z' fill='%23FDEFEF'/%3E%3C/svg%3E%0A");
        }

        nav.yummy-post-navigation .meta-nav::before{
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg width='41' height='15' viewBox='0 0 41 15' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cline y1='-0.5' x2='39' y2='-0.5' transform='matrix(-1 0 0 1 40.5 8)' stroke='%23A8A8A8'/%3E%3Cpath d='M9 0C9 5 1 7.5 1 7.5C1 7.5 9 10 9 15' stroke='%23A8A8A8' stroke-linejoin='round'/%3E%3C/svg%3E ");
            mask-image: url("data:image/svg+xml,%3Csvg width='41' height='15' viewBox='0 0 41 15' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cline y1='-0.5' x2='39' y2='-0.5' transform='matrix(-1 0 0 1 40.5 8)' stroke='%23A8A8A8'/%3E%3Cpath d='M9 0C9 5 1 7.5 1 7.5C1 7.5 9 10 9 15' stroke='%23A8A8A8' stroke-linejoin='round'/%3E%3C/svg%3E ");
        }

        .navigation.pagination .nav-links :is(.prev, .next)::after,
        .navigation.pagination .nav-links :is(.prev, .next)::before{
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg width='9' height='12' viewBox='0 0 9 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0.840088 1.41L5.42009 6L0.840088 10.59L2.25009 12L8.25009 6L2.25009 0L0.840088 1.41Z' fill='%23757575'/%3E%3C/svg%3E%0A");
            mask-image: url("data:image/svg+xml,%3Csvg width='9' height='12' viewBox='0 0 9 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0.840088 1.41L5.42009 6L0.840088 10.59L2.25009 12L8.25009 6L2.25009 0L0.840088 1.41Z' fill='%23757575'/%3E%3C/svg%3E%0A");            
        }

        :where(.widget.widget_bttk_author_bio .readmore,
            .readmore,
            .btn-cta.btn-1,
            .blossomthemes-email-newsletter-wrapper form input[type="submit"],
            .comments-area .comment-respond form .submit,
            .comments-area .comment-respond form input[type=submit],
            .site .wp-block-button:not(.yummy-btn-secondary) .wp-block-button__link,
            input[type=submit],
            .site .btn-primary)::after {
                -webkit-mask-image: url("data:image/svg+xml,%3Csvg width='15' height='12' viewBox='0 0 15 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9.75 11.25L8.7 10.2L12.15 6.75H0V5.25H12.15L8.7 1.8L9.75 0.75L15 6L9.75 11.25Z' fill='white'/%3E%3C/svg%3E%0A");
                mask-image: url("data:image/svg+xml,%3Csvg width='15' height='12' viewBox='0 0 15 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9.75 11.25L8.7 10.2L12.15 6.75H0V5.25H12.15L8.7 1.8L9.75 0.75L15 6L9.75 11.25Z' fill='white'/%3E%3C/svg%3E%0A");
        }

        .wp-block-search .wp-block-search__button{
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23001A1ACC'/%3E%3C/svg%3E%0A");
        }

        .search-form .search-submit{
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23001A1ACC'/%3E%3C/svg%3E%0A");
        }

        .search-form .search-submit:hover{
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23A60505'/%3E%3C/svg%3E%0A");
        }

        :is(.tax-recipe-course, .tax-recipe-key, .tax-recipe-badge, .tax-recipe-cuisine, .tax-recipe-cooking-method, .tax-recipe-dietary, .tax-recipe-tag, .search) .page-header-img-wrap .search-form .search-submit{
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23fff'/%3E%3C/svg%3E%0A");
        }

        :is(.tax-recipe-course, .tax-recipe-key, .tax-recipe-badge, .tax-recipe-cuisine, .tax-recipe-cooking-method, .tax-recipe-dietary, .tax-recipe-tag, .search) .page-header-img-wrap .search-form .search-submit:hover{
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23EDA602'/%3E%3C/svg%3E%0A");
        }

        :is(.yummy-btn-secondary .wp-block-button__link, .btn-cta.btn-2, .site .btn-secondary)::after{
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg width='15' height='12' viewBox='0 0 15 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9.75 11.25L8.7 10.2L12.15 6.75H0V5.25H12.15L8.7 1.8L9.75 0.75L15 6L9.75 11.25Z' fill='white'/%3E%3C/svg%3E%0A");
            mask-image: url("data:image/svg+xml,%3Csvg width='15' height='12' viewBox='0 0 15 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9.75 11.25L8.7 10.2L12.15 6.75H0V5.25H12.15L8.7 1.8L9.75 0.75L15 6L9.75 11.25Z' fill='white'/%3E%3C/svg%3E%0A");
        }

        .owl-carousel .owl-nav button.owl-prev {
            background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 1L1 8L8 15' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M15 8H1' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E%0A") !important;
        }

        .owl-carousel .owl-nav button.owl-prev:hover {
            background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 1L1 8L8 15' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M15 8H1' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E%0A") !important;
        }

        .owl-carousel .owl-nav button.owl-next {
            background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 8H15' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M8 1L15 8L8 15' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E%0A") !important;
        }
        
        .owl-carousel .owl-nav button.owl-next:hover {
            background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 8H15' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M8 1L15 8L8 15' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E%0A") !important;
        }

        input[type=checkbox]:checked {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='17.747' height='16.42' viewBox='0 0 17.747 16.42'%3E%3Cg id='layer1' transform='translate(0)'%3E%3Cg id='layer1-2' transform='translate(0 0)'%3E%3Cpath id='path4068' d='M-838.971-494.409l5.659,6.568a51.977,51.977,0,0,1,12.088-16.42c-4.247,2.4-8.927,6.946-12.926,12.577Z' transform='translate(838.971 504.261)' fill='%2317be8a'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9.736' height='6.204' viewBox='0 0 9.736 6.204'%3E%3Cpath id='Path_26478' data-name='Path 26478' d='M5,0,0,4.164,5,8.328' transform='translate(0.704 5.704) rotate(-90)' fill='none' stroke='%23808080' stroke-linecap='round' stroke-linejoin='round' stroke-width='1'/%3E%3C/svg%3E%0A");
        }

        .widget.widget_calendar .calendar_wrap .wp-calendar-nav .wp-calendar-nav-prev {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='17.562' height='13.244' viewBox='0 0 17.562 13.244'%3E%3Cg id='Group_762' data-name='Group 762' transform='matrix(-1, 0, 0, 1, 16.812, 1.061)'%3E%3Cpath id='Path_4' data-name='Path 4' d='M3290.465,368.331l5.561,5.561-5.561,5.561' transform='translate(-3280.275 -368.331)' fill='none' stroke='%231e1e1e' stroke-linecap='round' stroke-width='1.5'/%3E%3Cline id='Line_5' data-name='Line 5' x2='14.523' transform='translate(0 6)' fill='none' stroke='%231e1e1e' stroke-linecap='round' stroke-width='1.5'/%3E%3C/g%3E%3C/svg%3E%0A");
        }

        .widget.widget_calendar .calendar_wrap .wp-calendar-nav .wp-calendar-nav-prev:hover {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='17.562' height='13.244' viewBox='0 0 17.562 13.244'%3E%3Cg id='Group_762' data-name='Group 762' transform='matrix(-1, 0, 0, 1, 16.812, 1.061)'%3E%3Cpath id='Path_4' data-name='Path 4' d='M3290.465,368.331l5.561,5.561-5.561,5.561' transform='translate(-3280.275 -368.331)' fill='none' stroke='%2317BE8A' stroke-linecap='round' stroke-width='1.5'/%3E%3Cline id='Line_5' data-name='Line 5' x2='14.523' transform='translate(0 6)' fill='none' stroke='%2317BE8A' stroke-linecap='round' stroke-width='1.5'/%3E%3C/g%3E%3C/svg%3E%0A");
        }

        .widget.widget_calendar .calendar_wrap .wp-calendar-nav .wp-calendar-nav-next {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='17.562' height='13.244' viewBox='0 0 17.562 13.244'%3E%3Cg id='Group_762' data-name='Group 762' transform='translate(0.75 1.061)'%3E%3Cpath id='Path_4' data-name='Path 4' d='M3290.465,368.331l5.561,5.561-5.561,5.561' transform='translate(-3280.275 -368.331)' fill='none' stroke='%231e1e1e' stroke-linecap='round' stroke-width='1.5'/%3E%3Cline id='Line_5' data-name='Line 5' x2='14.523' transform='translate(0 6)' fill='none' stroke='%231e1e1e' stroke-linecap='round' stroke-width='1.5'/%3E%3C/g%3E%3C/svg%3E%0A");
        }

        .widget.widget_calendar .calendar_wrap .wp-calendar-nav .wp-calendar-nav-next:hover {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='17.562' height='13.244' viewBox='0 0 17.562 13.244'%3E%3Cg id='Group_762' data-name='Group 762' transform='translate(0.75 1.061)'%3E%3Cpath id='Path_4' data-name='Path 4' d='M3290.465,368.331l5.561,5.561-5.561,5.561' transform='translate(-3280.275 -368.331)' fill='none' stroke='%2317BE8A' stroke-linecap='round' stroke-width='1.5'/%3E%3Cline id='Line_5' data-name='Line 5' x2='14.523' transform='translate(0 6)' fill='none' stroke='%2317BE8A' stroke-linecap='round' stroke-width='1.5'/%3E%3C/g%3E%3C/svg%3E%0A");
        }

        .comments-area .comment.bypostauthor > .comment-body .comment-meta .comment-author::after {
            background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" width="17.96" height="17.96" viewBox="0 0 17.96 17.96"><g transform="translate(-584 -10824)"><rect width="17.96" height="17.96" rx="8.98" transform="translate(584 10824)" fill="%2300ab0b"/><path d="M5058.939,3595.743l2.417,2.418,5.32-5.32" transform="translate(-4469.439 7237.66)" fill="none" stroke="%23fff" stroke-linecap="round" stroke-width="2"/></g></svg>');
        }

        .site-footer .footer-t .grid .col .widget ul.accordion li a.toggle::after {
            background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="%23fff" d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"></path></svg>');
        }

        .wp-block-search .wp-block-search__button:hover {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 18 18'%3E%3Cpath id='Path_24900' data-name='Path 24900' d='M11.572,19.163a7.532,7.532,0,0,0,4.676-1.624L20.709,22,22,20.709l-4.461-4.461a7.57,7.57,0,1,0-5.967,2.915Zm0-13.363A5.782,5.782,0,1,1,5.8,11.572,5.782,5.782,0,0,1,11.572,5.8Z' transform='translate(-4 -4)' fill='%23A60505'/%3E%3C/svg%3E%0A");
        }

        .error404 .error-page-top-wrapper .error-404 .error-404-content-wrapper .error-404-search .search-form .search-submit {
            background-image: url("data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M16 16L22 22' stroke='%23757575' stroke-width='2' stroke-miterlimit='10' stroke-linecap='round'/%3E%3Cpath d='M9.50055 18.0011C13.9191 18.0011 17.5011 14.4191 17.5011 10.0006C17.5011 5.58197 13.9191 2 9.50055 2C5.08197 2 1.5 5.58197 1.5 10.0006C1.5 14.4191 5.08197 18.0011 9.50055 18.0011Z' stroke='%23757575' stroke-width='2' stroke-miterlimit='10' stroke-linecap='round'/%3E%3C/svg%3E%0A");
        }

    <?php echo "</style>";
}
add_action( 'wp_head', 'yummy_bites_other_css', 99 );