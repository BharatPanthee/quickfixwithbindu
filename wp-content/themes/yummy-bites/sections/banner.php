<?php

$defaults = yummy_bites_get_banner_defaults();

$ed_banner      = get_theme_mod( 'ed_banner_section', $defaults['ed_banner_section'] );
$slider_type    = get_theme_mod( 'slider_type', $defaults['slider_type'] );
$slider_cat     = get_theme_mod( 'slider_cat' );
$posts_per_page = get_theme_mod( 'no_of_slides', $defaults['no_of_slides']);
$ed_full_image  = get_theme_mod( 'slider_full_image', $defaults['slider_full_image'] );
$ed_caption     = get_theme_mod( 'slider_caption', $defaults['slider_caption'] );

if( yummy_bites_pro_is_activated() ){
    $prodefaults           = yummy_bites_pro_get_customizer_layouts_defaults();
    $slider_layout         = get_theme_mod( 'slider_layouts', $prodefaults['slider_layouts'] );
    $slider_cat_course     = get_theme_mod( 'slider_cat_course' );
    $slider_cat_cuisine    = get_theme_mod( 'slider_cat_cuisine' );
    $slider_cat_cookmethod = get_theme_mod( 'slider_cat_cookmethod' );
    $slider_cat_recipekey  = get_theme_mod( 'slider_cat_recipekey' );
}

if ( yummy_bites_pro_is_activated() ) {
    $class = 'style-'.$slider_layout;
}else{
    $class = 'style-one';
}

if ( yummy_bites_pro_is_activated() && $slider_layout == 'two' ) {
    $image_size = $ed_full_image ? 'full' : 'yummy-bites-slider-two';
} elseif ( yummy_bites_pro_is_activated() &&  $slider_layout == 'three' ) {
    $image_size = $ed_full_image ? 'full' : 'yummy-bites-slider-three';
} elseif ( yummy_bites_pro_is_activated() &&  $slider_layout == 'four' ) {
    $image_size = $ed_full_image ? 'full' : 'yummy-bites-slider-four';
}elseif ( yummy_bites_pro_is_activated() &&  $slider_layout == 'five' ) {
    $image_size = $ed_full_image ? 'full': 'yummy-bites-slider-five';
} else {
    $image_size = $ed_full_image ? 'full' : 'yummy-bites-slider-one';
}

$inline_script = "
    jQuery(document).ready(function ($) {
        //Values of yummy_bites_data are defined through wp_localize_script in custom-functions.php
        var slider_auto, slider_loop, rtl;
        if (yummy_bites_data.auto == '1') {
            slider_auto = true;
        } else {
            slider_auto = false;
        }
        if (yummy_bites_data.loop == '1') {
            slider_loop = true;
        } else {
            slider_loop = false;
        }
        if (yummy_bites_data.rtl == '1') {
            rtl = true;
        } else {
            rtl = false;
        }
            
        $('.banner-slider.style-one .item-wrapper').owlCarousel({
            items: 1,
            autoplay: slider_auto,
            loop: slider_loop,
            nav: true,
            dots: false,
            autoplaySpeed: 800,
            rtl: rtl,
            autoplayTimeout: yummy_bites_data.speed,
            animateOut: yummy_bites_data.animation,
        });
    });
";

if( $ed_banner == 'slider_banner' ){
    if( $slider_type == 'latest_posts' || $slider_type == 'cat' || ( yummy_bites_is_delicious_recipe_activated() && $slider_type == 'latest_recipes'|| $slider_type == 'cat_course' || $slider_type == 'cat_cuisine' || $slider_type == 'cat_cookmethod' || $slider_type == 'cat_recipekey' ) ) {
    
        $args = array(         
            'ignore_sticky_posts' => true
        );
        
        if( yummy_bites_is_delicious_recipe_activated() && $slider_type == 'latest_recipes' ){
            $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
            $args['posts_per_page'] = $posts_per_page;          
        }elseif( yummy_bites_pro_is_activated() && yummy_bites_is_delicious_recipe_activated() && $slider_type == 'cat_course' && $slider_cat_course ) {
            $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
            $args['tax_query']      = array( array( 'taxonomy' => 'recipe-course', 'terms' => $slider_cat_course ) ); 
            $args['posts_per_page'] = -1;
        }elseif( yummy_bites_pro_is_activated() && yummy_bites_is_delicious_recipe_activated() && $slider_type == 'cat_cuisine' && $slider_cat_cuisine ) {
            $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
            $args['tax_query']      = array( array( 'taxonomy' => 'recipe-cuisine', 'terms' => $slider_cat_cuisine ) ); 
            $args['posts_per_page'] = -1;
        }elseif( yummy_bites_pro_is_activated() && yummy_bites_is_delicious_recipe_activated() && $slider_type == 'cat_cookmethod' && $slider_cat_cookmethod ) {
            $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
            $args['tax_query']      = array( array( 'taxonomy' => 'recipe-cooking-method', 'terms' => $slider_cat_cookmethod ) ); 
            $args['posts_per_page'] = -1;
        }elseif( yummy_bites_pro_is_activated() && yummy_bites_is_delicious_recipe_activated() && $slider_type == 'cat_recipekey' && $slider_cat_recipekey ) {
            $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
            $args['tax_query']      = array( array( 'taxonomy' => 'recipe-key', 'terms' => $slider_cat_recipekey ) ); 
            $args['posts_per_page'] = -1;
        }elseif( $slider_type === 'cat' && $slider_cat ){
            $args['post_type']      = 'post';
            $args['cat']            = $slider_cat; 
            $args['posts_per_page'] = -1;  
        }else{
            $args['post_type']      = 'post';
            $args['posts_per_page'] = $posts_per_page;
        }
            
        $qry = new WP_Query( $args );
        if( $qry->have_posts() ){ ?>
            <div id="banner_section" class="site-banner banner-slider <?php echo esc_attr( $class ); ?>">
                <div class="container">
                    <div class="item-wrapper owl-carousel">
                        <?php while( $qry->have_posts() ){ $qry->the_post(); ?>
                            <div class="item">
                                <?php 
                                echo '<div class="item-img">';
                                    echo '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
                                        if( has_post_thumbnail() ){
                                            the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );    
                                        }else{ 
                                            yummy_bites_get_fallback_svg( $image_size );//fallback
                                        }
                                    echo '</a>';
                                    if ( ( yummy_bites_pro_is_activated() && $slider_layout != 'three' ) || !yummy_bites_pro_is_activated() ){
                                        if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ){
                                            echo '<div class="recipe-meta-data">';
                                                yummy_bites_recipe_keywords();
                                            echo '</div>';
                                        }
                                    }
                                echo '</div>'; 
                                if( $ed_caption ){ ?>                        
                                    <div class="banner-caption">
                                        <?php yummy_bites_slider_meta_contents();
                                        if( yummy_bites_pro_is_activated() && $slider_layout == 'three' ) {
                                            if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ){
                                                echo '<div class="recipe-meta-data">';
                                                    yummy_bites_recipe_keywords();
                                                echo '</div>'; 
                                            } 
                                        } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>                        
                    </div>
                </div>
            </div>
        <?php }
        wp_reset_postdata();
    }
    wp_add_inline_script( 'yummy-bites-custom', $inline_script );            
}elseif( yummy_bites_pro_is_activated() && $ed_banner == 'search_banner' ){
    do_action( 'yummy_bites_pro_banner_search' );
}elseif( yummy_bites_pro_is_activated() && $ed_banner == 'static_banner' ){
    do_action( 'yummy_bites_pro_static_cta_banner' );
}
