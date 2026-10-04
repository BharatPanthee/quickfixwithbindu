<?php
/**
 * Yummy Bites Woocommerce hooks and functions.
 *
 * @link https://docs.woothemes.com/document/third-party-custom-theme-compatibility/
 *
 * @package Yummy Bites
 */

/**
 * Woocommerce related hooks
*/
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_cart_is_empty', 'woocommerce_output_all_notices', 5 );

if( ! function_exists( 'yummy_bites_woocommerce_support' ) ) :
/**
 * Declare Woocommerce Support
*/
function yummy_bites_woocommerce_support() {
    global $woocommerce;
    
    add_theme_support( 'woocommerce' );
    
    if( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }
}
endif;
add_action( 'after_setup_theme', 'yummy_bites_woocommerce_support');

if( ! function_exists( 'yummy_bites_wc_widgets_init' ) ) :
/**
 * Woocommerce Sidebar
*/
function yummy_bites_wc_widgets_init(){
    register_sidebar( array(
		'name'          => esc_html__( 'Shop Sidebar', 'yummy-bites' ),
		'id'            => 'shop-sidebar',
		'description'   => esc_html__( 'Sidebar displaying only in woocommerce pages.', 'yummy-bites' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );    
}
endif;
add_action( 'widgets_init', 'yummy_bites_wc_widgets_init' );

if( ! function_exists( 'yummy_bites_wc_wrapper' ) ) :
/**
 * Before Content
 * Wraps all WooCommerce content in wrappers which match the theme markup
*/
function yummy_bites_wc_wrapper(){    
    ?>
    <div class="page-grid">
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">
    <?php
}
endif;
add_action( 'woocommerce_before_main_content', 'yummy_bites_wc_wrapper' );

if( ! function_exists( 'yummy_bites_wc_wrapper_end' ) ) :
/**
 * After Content
 * Closes the wrapping divs
*/
function yummy_bites_wc_wrapper_end(){
    ?>
                </main>
            </div>
        <?php do_action( 'yummy_bites_wo_sidebar' ); ?>
    </div>
    <?php
}
endif;
add_action( 'woocommerce_after_main_content', 'yummy_bites_wc_wrapper_end' );

if( ! function_exists( 'yummy_bites_wc_sidebar_cb' ) ) :
/**
 * Callback function for Shop sidebar
*/
function yummy_bites_wc_sidebar_cb(){
    if( is_active_sidebar( 'shop-sidebar' ) ){
        echo '<aside id="secondary" class="widget-area" role="complementary">';
        dynamic_sidebar( 'shop-sidebar' );
        echo '</aside>'; 
    }
}
endif;
add_action( 'yummy_bites_wo_sidebar', 'yummy_bites_wc_sidebar_cb' );

/**
 * Removes the "shop" title on the main shop page
*/
add_filter( 'woocommerce_show_page_title' , '__return_false' );

if( ! function_exists( 'yummy_bites_wc_cart_count' ) ) :
/**
 * Woocommerce Cart Count
 * 
 * @link https://isabelcastillo.com/woocommerce-cart-icon-count-theme-header 
*/
function yummy_bites_wc_cart_count(){
    $cart_page = get_option( 'woocommerce_cart_page_id' );
    $count = WC()->cart->cart_contents_count; 
    if( $cart_page){ ?>
        <div class="header-cart">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart" title="<?php esc_attr_e( 'View your shopping cart', 'yummy-bites' ); ?>">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_196_1496)"><path d="M18.3332 3.33325H4.1665L6.6665 11.6666H15.8332L18.3332 3.33325Z" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.52162 11.7998L5.10861 14.9375L5.08325 15.0175H16.3981" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><ellipse cx="7.08317" cy="17.9167" rx="0.416667" ry="0.416666" stroke="inherit" stroke-opacity="0.9" stroke-width="2"/><ellipse cx="14.6122" cy="17.9203" rx="0.416667" ry="0.416667" stroke="inherit" stroke-opacity="0.9" stroke-width="2"/><path d="M6.66634 11.67L3.66793 1.66602H1.66309" stroke="inherit" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_196_1496"><rect width="20" height="20" fill="white" transform="translate(-0.000244141)"/></clipPath></defs></svg>
                <span class="number"><?php echo absint( $count ); ?></span>
            </a>
        </div>
    <?php
    }
}
endif;

if( ! function_exists( 'yummy_bites_add_to_cart_fragment' ) ) :
/**
 * Ensure cart contents update when products are added to the cart via AJAX
 *
 * @link https://isabelcastillo.com/woocommerce-cart-icon-count-theme-header
 */
function yummy_bites_add_to_cart_fragment( $fragments ){
    ob_start();
    $count = WC()->cart->cart_contents_count; ?>
    <div class="header-cart">
        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart" title="<?php esc_attr_e( 'View your shopping cart', 'yummy-bites' ); ?>">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_196_1496)"><path d="M18.3332 3.33325H4.1665L6.6665 11.6666H15.8332L18.3332 3.33325Z" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.52162 11.7998L5.10861 14.9375L5.08325 15.0175H16.3981" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><ellipse cx="7.08317" cy="17.9167" rx="0.416667" ry="0.416666" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2"/><ellipse cx="14.6122" cy="17.9203" rx="0.416667" ry="0.416667" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2"/><path d="M6.66634 11.67L3.66793 1.66602H1.66309" stroke="inherit" stroke-opacity="0.9" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_196_1496"><rect width="20" height="20" fill="white" stroke="none" transform="translate(-0.000244141)"/></clipPath></defs></svg>
            <span class="number"><?php echo absint( $count ); ?></span>
        </a>
    </div>
    <?php

    $fragments['a.cart'] = ob_get_clean();

    return $fragments;
}
endif;
add_filter( 'woocommerce_add_to_cart_fragments', 'yummy_bites_add_to_cart_fragment' );