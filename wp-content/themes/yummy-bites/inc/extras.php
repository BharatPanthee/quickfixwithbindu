<?php

/**
 * Yummy Bites Standalone Functions.
 *
 * @package Yummy Bites
 */

if ( ! function_exists('yummy_bites_site_branding')) :
/**
 * Site Branding
 */
function yummy_bites_site_branding($mobile = false){
    $site_title       = get_bloginfo('name');
    $site_description = get_bloginfo('description', 'display');
    $header_text      = get_theme_mod( 'header_text', 1 );

    if ( has_custom_logo() || $site_title || $site_description || $header_text) :
        if ( has_custom_logo() && ( $site_title || $site_description ) && $header_text  ) {
            $branding_class = ' has-image-text';
        } else {
            $branding_class = '';
        } ?>
        <div class="site-branding<?php echo esc_attr( $branding_class ); ?>" itemscope itemtype="https://schema.org/Organization">
            <?php if ( function_exists('has_custom_logo') && has_custom_logo()) { ?>
                <div class="site-logo">
                    <?php the_custom_logo(); ?>
                </div>
            <?php } 
            if ( ( $site_title ||  $site_description) ) :
                if( $branding_class )echo '<div class="site-title-wrap">';
                if ( is_front_page() && !$mobile) { ?>
                    <h1 class="site-title" itemprop="name"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home" itemprop="url"><?php bloginfo('name'); ?></a></h1>
                <?php
                } else { ?>
                    <p class="site-title" itemprop="name"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home" itemprop="url"><?php bloginfo('name'); ?></a></p>
                <?php }

                $description = get_bloginfo('description', 'display');
                if ($description || is_customize_preview()) { ?>
                    <p class="site-description" itemprop="description"><?php echo $description; ?></p>
            <?php }
                if( $branding_class ) echo '</div>';
            endif; ?>
        </div>
    <?php endif;
}
endif;

if ( ! function_exists('yummy_bites_primary_navigation') ) :
/**
 * Primary Navigation.
 */
function yummy_bites_primary_navigation(){
    if( yummy_bites_pro_is_activated() ){
        $default       = yummy_bites_pro_get_customizer_defaults();
        $layoutdefault = yummy_bites_pro_get_customizer_layouts_defaults();
        $layouts       = get_theme_mod('header_layouts', $layoutdefault['header_layouts'] );
        $menu_stretch  = get_theme_mod('header_strech_menu', $default['header_strech_menu'] );

        $data_stretch = ( $menu_stretch && ( $layouts == 'one' || $layouts == 'two' || $layouts == 'seven' || $layouts == 'eight' || $layouts == 'nine' ) ) ? 'data-stretch=yes' : '';
    }else{
        $data_stretch = '';
    }
    
    if (current_user_can('manage_options') || has_nav_menu('primary')) {
    ?>
        <nav id="site-navigation" class="main-navigation" <?php echo esc_attr( $data_stretch ); ?> itemscope itemtype="https://schema.org/SiteNavigationElement">
            <?php
            wp_nav_menu(array(
                'theme_location'  => 'primary',
                'menu_id'         => 'primary-menu',
                'menu_class'      => 'nav-menu',
                'container_class' => 'primary-menu-container',
                'fallback_cb'     => 'yummy_bites_primary_menu_fallback',
            ));
            ?>
        </nav><!-- #site-navigation -->
    <?php
    }
}
endif;

if ( ! function_exists('yummy_bites_primary_menu_fallback') ) :
/**
 * Fallback for primary menu
 */
function yummy_bites_primary_menu_fallback(){
    if (current_user_can('manage_options')) {
        echo '<ul id="primary-menu" class="nav-menu">';
        echo '<li><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Click here to add a menu', 'yummy-bites') . '</a></li>';
        echo '</ul>';
    }
}
endif;

if ( ! function_exists('yummy_bites_secondary_navigation') ) :
/**
 * Secondary Navigation
 */
function yummy_bites_secondary_navigation(){
    if (current_user_can('manage_options') || has_nav_menu('secondary')) { ?>
        <nav class="secondary-nav">
            <?php
            wp_nav_menu(array(
                'theme_location'  => 'secondary',
                'menu_id'         => 'secondary-menu',
                'menu_class'      => 'nav-menu',
                'container_class' => 'secondary-menu-container',
                'fallback_cb'     => 'yummy_bites_secondary_menu_fallback',
            ));
            ?>
        </nav>
    <?php
    }
}
endif;

if ( ! function_exists('yummy_bites_secondary_menu_fallback') ) :
/**
 * Fallback for secondary menu
 */
function yummy_bites_secondary_menu_fallback(){
    if (current_user_can('manage_options')) {
        echo '<div class="menu-secondary-container"><ul id="secondary-menu" class="nav-menu">';
        echo '<li><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Click here to add a menu', 'yummy-bites') . '</a></li>';
        echo '</ul></div>';
    }
}
endif;

if( ! function_exists( 'yummy_bites_footer_navigation' ) ) :
/**
 * footer Navigation
*/
function yummy_bites_footer_navigation(){ ?>
    <nav class="footer-navigation">
        <?php
            wp_nav_menu( array(
                'theme_location' => 'footer',
                'menu_class'     => 'nav-menu',
                'menu_id'        => 'footer-menu',
                'fallback_cb'    => 'yummy_bites_footer_menu_fallback',
            ) );
        ?>
    </nav>
    <?php
}
endif;

if( ! function_exists( 'yummy_bites_footer_menu_fallback' ) ) :
/**
 * Fallback for footer menu
*/
function yummy_bites_footer_menu_fallback(){
    if( current_user_can( 'manage_options' ) ){
        echo '<ul id="footer-menu" class="nav-menu">';
        echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Click here to add a menu', 'yummy-bites' ) . '</a></li>';
        echo '</ul>';
    }
}
endif;

if ( ! function_exists('yummy_bites_mobile_navigation') ) :
/**
 * Mobile Navigation
 */
function yummy_bites_mobile_navigation(){
    $defaults               = yummy_bites_get_general_defaults();
    $ed_cart                = get_theme_mod('ed_woo_cart', $defaults['ed_woo_cart']);
    $ed_search              = get_theme_mod('ed_header_search', $defaults['ed_header_search']);
    $ed_social_media        = get_theme_mod('ed_social_links', $defaults['ed_social_links']);
    $social_media_order     = get_theme_mod('social_media_order', $defaults['social_media_order']);
    $ed_social_media_newtab = get_theme_mod('ed_social_links_new_tab', $defaults['ed_social_links_new_tab']);

    if( yummy_bites_pro_is_activated() ){
        $prodefaults    = yummy_bites_pro_get_customizer_layouts_defaults();
        $header_layout    = get_theme_mod( 'header_layouts', $prodefaults['header_layouts'] );
    }
    ?>
    <div class="mobile-header">
        <div class="header-main">
            <div class="container">
                <div class="mob-nav-wrap">
                    <div class="header-mob-top">
                        <?php
                        if ( $ed_social_media ) { ?>
                            <div class="header-social-wrapper">
                                <div class="header-social">
                                    <?php
                                    $social_icons = new Yummy_Bites_Social_Lists;
                                    $social_icons->yummy_bites_social_links($ed_social_media, $ed_social_media_newtab, $social_media_order);
                                    ?>
                                </div>
                            </div>
                        <?php }
                        if ( ( yummy_bites_is_woocommerce_activated() && $ed_cart ) || $ed_search) { ?>
                            <div class="header-right">
                                <?php if ($ed_search) yummy_bites_search();
                                if (yummy_bites_is_woocommerce_activated() && $ed_cart) yummy_bites_wc_cart_count();
                                ?>
                            </div>
                        <?php }
                        ?>
                    </div>
                    <div class="header-mob-bottom">
                        <?php yummy_bites_site_branding( true ); ?>
                        <div class="toggle-btn-wrap">
                            <button class="toggle-btn" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".close-main-nav-toggle">
                                <span class="toggle-bar"></span>
                                <span class="toggle-bar"></span>
                                <span class="toggle-bar"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mobile-header-popup">
                    <div class="header-bottom-slide mobile-menu-list main-menu-modal cover-modal" data-modal-target-string=".main-menu-modal">
                        <div class="header-bottom-slide-inner mobile-menu" aria-label="<?php esc_attr_e('Mobile', 'yummy-bites'); ?>">
                            <div class="container">
                                <div class="mobile-header-wrap">
                                    <button class="close close-main-nav-toggle" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".main-menu-modal"></button>
                                </div>
                                <div class="mobile-header-wrapper">
                                    <div class="header-left">
                                        <?php yummy_bites_site_branding( true ); 
                                            yummy_bites_primary_navigation(); 
                                        if ( yummy_bites_pro_is_activated() && ( $header_layout !== 'four' && $header_layout !== 'five' && $header_layout !=='six' && $header_layout !== 'eight'&& $header_layout !== 'ten')  ){
                                            yummy_bites_secondary_navigation();
                                        }elseif( !yummy_bites_pro_is_activated() ){
                                            yummy_bites_secondary_navigation();
                                        } 
                                        if ( $ed_social_media ) { ?>
                                            <div class="header-social-wrapper">
                                                <div class="header-social">
                                                    <?php
                                                    $social_icons = new Yummy_Bites_Social_Lists;
                                                    $social_icons->yummy_bites_social_links($ed_social_media, $ed_social_media_newtab, $social_media_order);
                                                    ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
endif;

if ( ! function_exists('yummy_bites_search') ):
/**
 * Search form Section
 */
function yummy_bites_search(){
    $defaults  = yummy_bites_get_general_defaults();
    $ed_search = get_theme_mod('ed_header_search', $defaults['ed_header_search']);
    if ($ed_search) { ?>
        <div class="header-search">
            <button class="search-toggle" data-toggle-target=".search-modal" data-toggle-body-class="showing-search-modal" data-set-focus=".search-modal .search-field" aria-expanded="false">
                <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.83325 16.6667C13.9754 16.6667 17.3333 13.3089 17.3333 9.16675C17.3333 5.02461 13.9754 1.66675 9.83325 1.66675C5.69112 1.66675 2.33325 5.02461 2.33325 9.16675C2.33325 13.3089 5.69112 16.6667 9.83325 16.6667Z" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M18.1665 17.5L15.6665 15" stroke="inherit" fill="none" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div class="header-search-wrap search-modal cover-modal" data-modal-target-string=".search-modal">
                <div class="header-search-inner">
                    <button aria-label="<?php esc_attr_e('search form close', 'yummy-bites'); ?>" class="close" data-toggle-target=".search-modal" data-toggle-body-class="showing-search-modal" data-set-focus=".search-modal .search-field" aria-expanded="false"></button>
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>
    <?php }
}
endif;

if ( ! function_exists('yummy_bites_header_style') ) :
/**
 * Styles the header image and text displayed on the blog.
 *
 */
function yummy_bites_header_style(){
    $defaults           = yummy_bites_get_site_defaults();
    $color_defaults     = yummy_bites_get_color_defaults();
    $hide_title         = get_theme_mod('hide_title', $defaults['hide_title']);
    $hide_tagline       = get_theme_mod('hide_tagline', $defaults['hide_tagline']);
    $site_title_color   = get_theme_mod('site_title_color', $color_defaults['site_title_color']);
    $site_tagline_color = get_theme_mod('site_tagline_color', $color_defaults['site_tagline_color']);
    ?>
    <style type="text/css">
        <?php if ($hide_title) { ?>.site-title {
            position: absolute;
            clip: rect(1px, 1px, 1px, 1px);
        }

        <?php } else { ?>.site-branding .site-title a {
            color: <?php echo esc_attr($site_title_color); ?>;
        }

        <?php } ?><?php if ($hide_tagline) { ?>.site-description {
            position: absolute;
            clip: rect(1px, 1px, 1px, 1px);
        }

        <?php } else { ?>.site-branding .site-description {
            color: <?php echo esc_attr($site_tagline_color); ?>;
        }

        <?php } ?>
    </style>
<?php
}
endif;

if ( ! function_exists('yummy_bites_posted_on') ) :
/**
 * Prints HTML with meta information for the current post-date/time.
 */
function yummy_bites_posted_on(){
    $defaults = yummy_bites_get_general_defaults();
    $ed_updated_post_date = get_theme_mod('ed_post_update_date', $defaults['ed_post_update_date']);
    $on = '';

    if ( get_the_time('U') !== get_the_modified_time('U') ) {
        if ($ed_updated_post_date) {
            $time_string = '<time class="entry-date published updated" datetime="%3$s" itemprop="dateModified">%4$s</time><time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time>';
            $on = __('Updated on ', 'yummy-bites');
        } else {
            $time_string = '<time class="entry-date published" datetime="%1$s" itemprop="datePublished">%2$s</time><time class="updated" datetime="%3$s" itemprop="dateModified">%4$s</time>';
        }
    } else {
        $time_string = '<time class="entry-date published updated" datetime="%1$s" itemprop="datePublished">%2$s</time><time class="updated" datetime="%3$s" itemprop="dateModified">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date('c')),
        esc_html(get_the_date()),
        esc_attr(get_the_modified_date('c')),
        esc_html(get_the_modified_date())
    );

    $posted_on = sprintf('%1$s %2$s', esc_html($on), '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>');

    echo '<span class="posted-on"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33337 0.666992C4.70156 0.666992 5.00004 0.965469 5.00004 1.33366V2.00033H9.00004V1.33366C9.00004 0.965469 9.29852 0.666992 9.66671 0.666992C10.0349 0.666992 10.3334 0.965469 10.3334 1.33366V2.00033H11.6667C12.7713 2.00033 13.6667 2.89576 13.6667 4.00033V13.3337C13.6667 14.4382 12.7713 15.3337 11.6667 15.3337H2.33337C1.2288 15.3337 0.333374 14.4382 0.333374 13.3337V4.00033C0.333374 2.89576 1.2288 2.00033 2.33337 2.00033H3.66671V1.33366C3.66671 0.965469 3.96518 0.666992 4.33337 0.666992ZM12.3334 7.33366V13.3337C12.3334 13.7018 12.0349 14.0003 11.6667 14.0003H2.33337C1.96518 14.0003 1.66671 13.7018 1.66671 13.3337V7.33366H12.3334Z" fill="#757575"/>
    </svg>' . $posted_on . '</span>'; // WPCS: XSS OK.
}
endif;

if (! function_exists('yummy_bites_posted_by') ) :
/**
 * Prints HTML with meta information for the current author.
 */
function yummy_bites_posted_by(){
    global $post;
    $author_id = $post->post_author;
    $byline = sprintf(
        /* translators: %s: post author. */
        esc_html( '%s'), '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID', $author_id))) . '" itemprop="url">      <img class="avatar" src="' . esc_url(get_avatar_url(get_the_author_meta('ID'), array('size' => 40))) . '" alt="' . esc_attr(get_the_author()) . '" /> <b class="fn">' . esc_html(get_the_author()) . '</b></a></span>'
    );

    echo '<span class="byline" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $byline . '</span>';
}
endif;

if ( ! function_exists('yummy_bites_comment_count') ) :
/**
 * Comment Count
 */
function yummy_bites_comment_count(){
    if (!post_password_required() && (comments_open() || get_comments_number())) {
        echo '<span class="comments"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.00004 0.333008C0.631854 0.333008 0.333377 0.631485 0.333377 0.999674L0.333374 12.9997C0.333374 13.2693 0.495802 13.5124 0.744918 13.6156C0.994035 13.7188 1.28078 13.6617 1.47145 13.4711L3.94285 10.9997H13C13.3682 10.9997 13.6667 10.7012 13.6667 10.333L13.6667 0.999674C13.6667 0.822864 13.5965 0.653294 13.4714 0.52827C13.3464 0.403246 13.1769 0.333008 13 0.333008H1.00004ZM3.00004 4.33301C3.00004 3.96482 3.29852 3.66634 3.66671 3.66634H10.3334C10.7016 3.66634 11 3.96482 11 4.33301C11 4.7012 10.7016 4.99967 10.3334 4.99967H3.66671C3.29852 4.99967 3.00004 4.7012 3.00004 4.33301ZM3.66671 6.33301C3.29852 6.33301 3.00004 6.63148 3.00004 6.99967C3.00004 7.36786 3.29852 7.66634 3.66671 7.66634H7.00004C7.36823 7.66634 7.66671 7.36786 7.66671 6.99967C7.66671 6.63148 7.36823 6.33301 7.00004 6.33301H3.66671Z" fill="#757575"/>
    </svg>';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'yummy-bites'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                get_the_title()
            )
        );
        echo '</span>';
    }
}
endif;

if ( ! function_exists('yummy_bites_related_posts') ) :
/**
 * Related Posts 
 */
function yummy_bites_related_posts() {
    $defaults        = yummy_bites_get_general_defaults();
    $ed_related_post = get_theme_mod('ed_related', $defaults['ed_related']);
    if ($ed_related_post) {
        yummy_bites_get_posts_list('related');
    }
}
endif;

if ( ! function_exists('yummy_bites_comment_toggle') ) :
/**
 * Function toggle comment section position
 */
function yummy_bites_comment_toggle() {
    $defaults        = yummy_bites_get_general_defaults();
    $comment_postion = get_theme_mod('toggle_comments', $defaults['toggle_comments']);

    if ($comment_postion === 'below-post') {
        $priority = 5;
    } else {
        $priority = 40;
    }
    return absint($priority);
}
endif;

if ( ! function_exists('yummy_bites_estimated_reading_time') ) :
/** 
 * Reading Time Calculate Function 
 */
function yummy_bites_estimated_reading_time($content){
    $defaults = yummy_bites_get_general_defaults();
    $wpm = get_theme_mod('read_words_per_minute', $defaults['read_words_per_minute']);
    $clean_content = strip_shortcodes($content);
    $clean_content = strip_tags($clean_content);
    $word_count = str_word_count($clean_content);
    $time = ceil($word_count / $wpm);
    echo '<span class="post-read-time">' . absint($time) . esc_html__(' min read', 'yummy-bites') . '</span>';
}
endif;

if ( ! function_exists('yummy_bites_category') ) :
/**
 * Prints categories
 */
function yummy_bites_category() {
    global $post;
    // Hide category and tag text for pages.
    if ('post' === get_post_type()) {
        /* translators: used between list items, there is a space after the comma */

        if( yummy_bites_pro_is_activated() ){ 
            $categories = get_the_terms($post->post_id, 'category');
            if( $categories ){
                foreach( $categories as $catID){
                    $color_id = get_term_meta( $catID->term_id, 'trp-category-color-id', true );
                    $colorStyle = isset( $color_id ) && !empty( $color_id ) ? 'style=--yummy-category-color:' . $color_id : 'style=--yummy-category-color:var(--yummy-primary-color)';
                    echo '<span class="cat-links"' . esc_attr($colorStyle) . ' itemprop="about"><a href="'. esc_url( get_term_link( $catID->term_id ) ). '" rel="category tag">' . esc_html($catID->name) . '</a></span>';
                }
            }
        } else { 
            $categories_list = get_the_category_list(' ');
            if ($categories_list) {
                echo '<span class="cat-links" itemprop="about">' . $categories_list . '</span>';
            }
        }
    } else {
        $taxonomies = get_post_taxonomies( get_the_ID() );
        
        if ( ! empty( $taxonomies ) && is_array($taxonomies) ) {
            foreach ( $taxonomies as $taxonomy ) {
                $terms = get_the_terms( get_the_ID(), $taxonomy );
                
                if ( ! $terms || is_wp_error( $terms ) ) {
                    continue;
                }
                
                foreach ( $terms as $term ) {
                    printf(
                        '<span class="cat-links" style="--yummy-category-color:var(--yummy-primary-color)" itemprop="about">
                            <a href="%s" rel="category tag">%s</a>
                        </span>',
                        esc_url( get_term_link( $term ) ),
                        esc_html( $term->name )
                    );
                }
                
                break; // Only show terms from first taxonomy with terms
            }
        }
    }
}
endif;

if ( ! function_exists('yummy_bites_tag') ) :
/**
 * Prints tags
 */
function yummy_bites_tag(){
    // Hide category and tag text for pages.
    if ('post' === get_post_type()) {
        $tags_list = get_the_tag_list('', ' ');
        if ($tags_list) {
            /* translators: 1: list of tags. */
            printf('<div class="tags" itemprop="about">' . esc_html__('%1$sTags:%2$s %3$s', 'yummy-bites') . '</div>', '<span>', '</span>', $tags_list);
        }
    }
}
endif;


if ( ! function_exists('yummy_bites_get_template_part')) :
    /**
     * Get template from Tasty Recipe Pro plugin or theme.
     *
     * @param string $template Name of the section.
     */
    function yummy_bites_get_template_part($template){

        if ( locate_template('sections/' . $template . '.php') ) {
            get_template_part('sections/' . $template);
        } else {
            if ( defined('YUMMY_BITES_PRO_PATH') ) {
                if ( file_exists(YUMMY_BITES_PRO_PATH . 'sections/' . $template . '.php')) {
                    require_once(YUMMY_BITES_PRO_PATH . 'sections/' . $template . '.php');
                }
            }
        }
    }
endif;

if ( ! function_exists('yummy_bites_get_posts_list') ) :
/**
 * Returns Latest, Related & Popular Posts
 */
function yummy_bites_get_posts_list( $status ){
    global $post;
    $defaults         = yummy_bites_get_general_defaults();
    $related_post_num = get_theme_mod('no_of_posts_rp', $defaults['no_of_posts_rp']);
    $prodefaults      = yummy_bites_pro_is_activated() ? yummy_bites_pro_get_customizer_defaults() : [];
    $post_num         = yummy_bites_pro_is_activated() ? get_theme_mod( 'no_of_posts_404', $prodefaults['no_of_posts_404'] ) : 3;

    $args = array(
        'posts_status'        => 'publish',
        'ignore_sticky_posts' => true
    );

    switch ( $status ) {
        case 'latest':
            $args['post_type']      = ( yummy_bites_is_delicious_recipe_activated()) ? DELICIOUS_RECIPE_POST_TYPE : 'post';
            $args['posts_per_page'] = $post_num;
            $relatedPostTitle       = __('Recommended For You', 'yummy-bites');
            $class                  = 'related-posts';
            $image_size             = 'yummy-bites-blog-one';
            break;

        case 'related':
            $args['post_type']      = ( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE === get_post_type()) ? DELICIOUS_RECIPE_POST_TYPE : 'post';
            $args['posts_per_page'] = $related_post_num;
            $args['post__not_in']   = array($post->ID);
            $args['orderby']        = 'rand';
            $relatedPostTitle       = get_theme_mod('related_post_title', $defaults['related_post_title']);
            $class                  = 'related-posts';
            $image_size             = 'yummy-bites-blog-one';

            if (yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE === get_post_type()) {
                $cats = get_the_terms($post->ID, 'recipe-course');
                if (!$cats) return false;
                $c = array();
                foreach ($cats as $cat) {
                    $c[] = $cat->term_id;
                }
                $args['tax_query']    = array(array('taxonomy' => 'recipe-course', 'terms' => $c));
            } else {
                $cats = get_the_category($post->ID);
                if ($cats) {
                    $c = array();
                    foreach ($cats as $cat) {
                        $c[] = $cat->term_id;
                    }
                    $args['category__in'] = $c;
                }
            }
            break;
    }

    $qry = new WP_Query($args);

    if ($qry->have_posts()) { ?>
        <div class="<?php echo esc_attr($class); ?>">
            <?php if ($relatedPostTitle) echo '<h2 class="title">' . esc_html($relatedPostTitle) . '</h2>'; ?>
            <div class="article-wrap">
                <?php while ($qry->have_posts()) {
                    $qry->the_post(); ?>
                    <article <?php post_class( 'post' );?>>
                        <figure class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                <?php
                                if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( $image_size, array('itemprop' => 'image'));
                                } else {
                                    yummy_bites_get_fallback_svg( $image_size ); //fallback
                                }
                                ?>
                            </a>
                            <?php if (yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type()) yummy_bites_recipe_keywords(); ?>
                        </figure>
                        <div class="content-wrapper">
                            <?php if (yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type()) { ?>
                                <header class="entry-header">
                                    <?php the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>'); ?>
                                </header>
                                <footer class="item-footer">
                                    <?php yummy_bites_prep_time(); ?>
                                    <?php yummy_bites_difficulty_level(); ?>
                                </footer>
                            <?php } else {
                                echo '<header class="entry-header">';
                                yummy_bites_category();
                                the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>');
                                if ('post'  == get_post_type()) {
                                    echo '<div class="entry-meta">';
                                    yummy_bites_posted_on();
                                    echo '</div>';
                                }
                                echo '</header>';
                            } ?>
                        </div>
                    </article>
                <?php } ?>
            </div>
        </div>
    <?php
        wp_reset_postdata();
    }
}
endif;

if ( ! function_exists('yummy_bites_breadcrumb') ) :
/**
 * Breadcrumbs
 */
function yummy_bites_breadcrumb(){
    global $post;
    $defaults       = yummy_bites_get_general_defaults();
    $post_page      = get_option('page_for_posts'); //The ID of the page that displays posts.
    $show_front     = get_option('show_on_front');  //What to show on the front page    
    $home           = get_theme_mod('home_text', $defaults['home_text']); // text for the 'Home' link
    $separator_icon = get_theme_mod('separator_icon', $defaults['separator_icon']);
    $delimiter      = '<span class="separator">' . yummy_bites_breadcrumb_icons_list($separator_icon) . '</span>';
    $before         = '<span class="current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">'; // tag before the current crumb
    $after          = '</span>'; // tag after the current crumb

    if ( get_theme_mod('ed_breadcrumb', $defaults['ed_breadcrumb'])) {
        $depth = 1;
        echo '<div class="breadcrumb-wrapper"><div id="crumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="' . esc_url(home_url()) . '" itemprop="item"><span itemprop="name" class="home-text">' . esc_html($home) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';

        if (is_home()) {
            $depth = 2;
            echo $before . '<a itemprop="item" href="' . esc_url(get_the_permalink()) . '"><span itemprop="name">' . esc_html(single_post_title('', false)) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_category()) {
            $depth = 2;
            $thisCat = get_category(get_query_var('cat'), false);
            if ($show_front === 'page' && $post_page) { //If static blog post page is set
                $p = get_post($post_page);
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_permalink($post_page)) . '" itemprop="item"><span itemprop="name">' . esc_html($p->post_title) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                $depth++;
            }
            if ($thisCat->parent != 0) {
                $parent_categories = get_category_parents($thisCat->parent, false, ',');
                $parent_categories = explode(',', $parent_categories);
                foreach ($parent_categories as $parent_term) {
                    $parent_obj = get_term_by('name', $parent_term, 'category');
                    if (is_object($parent_obj)) {
                        $term_url  = get_term_link($parent_obj->term_id);
                        $term_name = $parent_obj->name;
                        echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url($term_url) . '"><span itemprop="name">' . esc_html($term_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                        $depth++;
                    }
                }
            }
            echo $before . '<a itemprop="item" href="' . esc_url( get_term_link($thisCat->term_id) ) . '"><span itemprop="name">' .  esc_html(single_cat_title('', false)) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_woocommerce_activated() && (is_product_category() || is_product_tag())) { //For Woocommerce archive page
            $depth = 2;
            $current_term = $GLOBALS['wp_query']->get_queried_object();
            if (wc_get_page_id('shop')) { //Displaying Shop link in woocommerce archive page
                $_name = wc_get_page_id('shop') ? get_the_title(wc_get_page_id('shop')) : '';
                if (!$_name) {
                    $product_post_type = get_post_type_object('product');
                    $_name = $product_post_type->labels->singular_name;
                }
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '" itemprop="item"><span itemprop="name">' . esc_html($_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                $depth++;
            }
            if (is_product_category()) {
                $ancestors = get_ancestors($current_term->term_id, 'product_cat');
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor) {
                    $ancestor = get_term($ancestor, 'product_cat');
                    if (!is_wp_error($ancestor) && $ancestor) {
                        echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_term_link($ancestor)) . '" itemprop="item"><span itemprop="name">' . esc_html($ancestor->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                        $depth++;
                    }
                }
            }
            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($current_term->term_id)) . '"><span itemprop="name">' . esc_html($current_term->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_woocommerce_activated() && is_shop()) { //Shop Archive page
            $depth = 2;
            if (get_option('page_on_front') == wc_get_page_id('shop')) {
                return;
            }
            $_name    = wc_get_page_id('shop') ? get_the_title(wc_get_page_id('shop')) : '';
            $shop_url = (wc_get_page_id('shop') && wc_get_page_id('shop') > 0)  ? get_the_permalink(wc_get_page_id('shop')) : home_url('/shop');
            if (!$_name) {
                $product_post_type = get_post_type_object('product');
                $_name             = $product_post_type->labels->singular_name;
            }
            echo $before . '<a itemprop="item" href="' . esc_url($shop_url) . '"><span itemprop="name">' . esc_html($_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-cuisine')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-cuisine';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-course')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-course';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-cooking-method')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-cooking-method';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-tag')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-tag';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-key')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-key';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-badge')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-badge';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (yummy_bites_is_delicious_recipe_activated() && is_tax('recipe-dietary')) {
            $depth = 2;
            $queried_object = get_queried_object();
            $taxonomy = 'recipe-dietary';
            $ancestors = get_ancestors($queried_object->term_id, $taxonomy);
            if (!empty($ancestors)) {
                $termz = get_term($ancestors[0], $taxonomy);
                $ancestors_title = !empty($termz->name) ? esc_html($termz->name) : '';
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url(get_term_link($termz->term_id)) . '"><span itemprop="name">' . $ancestors_title . ' </span></a><meta itemprop="position" content="' . absint($depth) . '"/><span class="separator">' . $delimiter . '</span></span> ';

                $depth++;
            }

            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html($queried_object->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_tag()) {
            $depth          = 2;
            $queried_object = get_queried_object();
            echo $before . '<a itemprop="item" href="' . esc_url(get_term_link($queried_object->term_id)) . '"><span itemprop="name">' . esc_html(single_tag_title('', false)) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_author()) {
            global $author;
            $depth    = 2;
            $userdata = get_userdata($author);
            echo $before . '<a itemprop="item" href="' . esc_url(get_author_posts_url($author)) . '"><span itemprop="name">' . esc_html($userdata->display_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_search()) {
            $depth       = 2;
            $request_uri = $_SERVER['REQUEST_URI'];
            echo $before . '<a itemprop="item" href="' . esc_url($request_uri) . '"><span itemprop="name">' . sprintf(__('Search Results for "%s"', 'yummy-bites'), esc_html(get_search_query())) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_day()) {
            $depth = 2;
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_year_link(get_the_time(__('Y', 'yummy-bites')))) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_time(__('Y', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
            $depth++;
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_month_link(get_the_time(__('Y', 'yummy-bites')), get_the_time(__('m', 'yummy-bites')))) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_time(__('F', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
            $depth++;
            echo $before . '<a itemprop="item" href="' . esc_url(get_day_link(get_the_time(__('Y', 'yummy-bites')), get_the_time(__('m', 'yummy-bites')), get_the_time(__('d', 'yummy-bites')))) . '"><span itemprop="name">' . esc_html(get_the_time(__('d', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_month()) {
            $depth = 2;
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_year_link(get_the_time(__('Y', 'yummy-bites')))) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_time(__('Y', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
            $depth++;
            echo $before . '<a itemprop="item" href="' . esc_url(get_month_link(get_the_time(__('Y', 'yummy-bites')), get_the_time(__('m', 'yummy-bites')))) . '"><span itemprop="name">' . esc_html(get_the_time(__('F', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_year()) {
            $depth = 2;
            echo $before . '<a itemprop="item" href="' . esc_url(get_year_link(get_the_time(__('Y', 'yummy-bites')))) . '"><span itemprop="name">' . esc_html(get_the_time(__('Y', 'yummy-bites'))) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_single() && !is_attachment()) {
            $depth = 2;
            if (yummy_bites_is_woocommerce_activated() && 'product' === get_post_type()) { //For Woocommerce single product
                if (wc_get_page_id('shop')) { //Displaying Shop link in woocommerce archive page
                    $_name = wc_get_page_id('shop') ? get_the_title(wc_get_page_id('shop')) : '';
                    if (!$_name) {
                        $product_post_type = get_post_type_object('product');
                        $_name = $product_post_type->labels->singular_name;
                    }
                    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '" itemprop="item"><span itemprop="name">' . esc_html($_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                    $depth++;
                }
                if ($terms = wc_get_product_terms($post->ID, 'product_cat', array('orderby' => 'parent', 'order' => 'DESC'))) {
                    $main_term = apply_filters('woocommerce_breadcrumb_main_term', $terms[0], $terms);
                    $ancestors = get_ancestors($main_term->term_id, 'product_cat');
                    $ancestors = array_reverse($ancestors);
                    foreach ($ancestors as $ancestor) {
                        $ancestor = get_term($ancestor, 'product_cat');
                        if (!is_wp_error($ancestor) && $ancestor) {
                            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_term_link($ancestor)) . '" itemprop="item"><span itemprop="name">' . esc_html($ancestor->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                            $depth++;
                        }
                    }
                    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_term_link($main_term)) . '" itemprop="item"><span itemprop="name">' . esc_html($main_term->name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                    $depth++;
                }
                echo $before . '<a href="' . esc_url(get_the_permalink()) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
            } elseif (get_post_type() != 'post') {
                $post_type = get_post_type_object(get_post_type());
                if ($post_type->has_archive == true) { // For CPT Archive Link                   
                    // Add support for a non-standard label of 'archive_title' (special use case).
                    $label = !empty($post_type->labels->singular_name) ? $post_type->labels->singular_name : $post_type->labels->name;
                    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_post_type_archive_link(get_post_type())) . '" itemprop="item"><span itemprop="name">' . esc_html($label) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                    $depth++;
                }
                echo $before . '<a href="' . esc_url(get_the_permalink()) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
            } else { //For Post                
                $cat_object       = get_the_category();
                $potential_parent = 0;

                if ($show_front === 'page' && $post_page) { //If static blog post page is set
                    $p = get_post($post_page);
                    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_permalink($post_page)) . '" itemprop="item"><span itemprop="name">' . esc_html($p->post_title) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                    $depth++;
                }

                if ($cat_object) { //Getting category hierarchy if any        
                    //Now try to find the deepest term of those that we know of
                    $use_term = key($cat_object);
                    foreach ($cat_object as $key => $object) {
                        //Can't use the next($cat_object) trick since order is unknown
                        if ($object->parent > 0  && ($potential_parent === 0 || $object->parent === $potential_parent)) {
                            $use_term         = $key;
                            $potential_parent = $object->term_id;
                        }
                    }
                    $cat  = $cat_object[$use_term];
                    $cats = get_category_parents($cat, false, ',');
                    $cats = explode(',', $cats);
                    foreach ($cats as $cat) {
                        $cat_obj = get_term_by('name', $cat, 'category');
                        if (is_object($cat_obj)) {
                            $term_url  = get_term_link($cat_obj->term_id);
                            $term_name = $cat_obj->name;
                            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . esc_url($term_url) . '"><span itemprop="name">' . esc_html($term_name) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                            $depth++;
                        }
                    }
                }
                echo $before . '<a itemprop="item" href="' . esc_url(get_the_permalink()) . '"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
            }
        } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) { //For Custom Post Archive
            $depth     = 2;
            $post_type = get_post_type_object(get_post_type());
            if ( $post_type ) { // If post type is not empty
                if (get_query_var('paged')) {
                    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_post_type_archive_link($post_type->name)) . '" itemprop="item"><span itemprop="name">' . esc_html($post_type->label) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                    echo $before . sprintf(__('Page %s', 'yummy-bites'), get_query_var('paged')) . $after; //@todo need to check this
                } else {
                    echo $before . '<a itemprop="item" href="' . esc_url(get_post_type_archive_link($post_type->name)) . '"><span itemprop="name">' . esc_html($post_type->label) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
                }
            }
        } elseif (is_attachment()) {
            $depth = 2;
            echo $before . '<a itemprop="item" href="' . esc_url(get_the_permalink()) . '"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_page() && !$post->post_parent) {
            $depth = 2;
            echo $before . '<a itemprop="item" href="' . esc_url(get_the_permalink()) . '"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        } elseif (is_page() && $post->post_parent) {
            $depth       = 2;
            $parent_id   = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $current_page  = get_post($parent_id);
                $breadcrumbs[] = $current_page->ID;
                $parent_id     = $current_page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            for ($i = 0; $i < count($breadcrumbs); $i++) {
                echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url(get_permalink($breadcrumbs[$i])) . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title($breadcrumbs[$i])) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $delimiter . '</span>';
                $depth++;
            }
            echo $before . '<a href="' . get_permalink() . '" itemprop="item"><span itemprop="name">' . esc_html(get_the_title()) . '</span></a><meta itemprop="position" content="' . absint($depth) . '" /></span>' . $after;
        } elseif (is_404()) {
            $depth = 2;
            echo $before . '<a itemprop="item" href="' . esc_url(home_url()) . '"><span itemprop="name">' . esc_html__('404 Error - Page Not Found', 'yummy-bites') . '</span></a><meta itemprop="position" content="' . absint($depth) . '" />' . $after;
        }

        if (get_query_var('paged')) printf(__(' (Page %s)', 'yummy-bites'), get_query_var('paged'));

        echo '</div></div><!-- .crumbs --><!-- .breadcrumb-wrapper -->';
    }
}
endif;

if ( ! function_exists('yummy_bites_breadcrumb_icons_list') ) :
/**
 * Breadcrumbs Icon List
 */
function yummy_bites_breadcrumb_icons_list($separator_icon = 'one'){

    switch ($separator_icon) {
        case 'one':
            return '<svg width="15" height="15" viewBox="0 0 20 20"><path d="M7.7,20c-0.3,0-0.5-0.1-0.7-0.3c-0.4-0.4-0.4-1.1,0-1.5l8.1-8.1L6.7,1.8c-0.4-0.4-0.4-1.1,0-1.5
        c0.4-0.4,1.1-0.4,1.5,0l9.1,9.1c0.4,0.4,0.4,1.1,0,1.5l-8.8,8.9C8.2,19.9,7.9,20,7.7,20z" opacity="0.7"/></svg>';
            break;
        case 'two':
            return '<svg width="15" height="15" viewBox="0 0 20 20"><polygon points="7,0 18,10 7,20 " opacity="0.7"/></svg>';
            break;
        case 'three':
            return '<svg width="15" height="15" viewBox="0 0 20 20"><path d="M6.1,20c-0.2,0-0.3,0-0.5-0.1c-0.5-0.2-0.7-0.8-0.4-1.3l9.5-17.9C15,0.1,15.6,0,16.1,0.2
        c0.5,0.2,0.7,0.8,0.4,1.3L6.9,19.4C6.8,19.8,6.5,19.9,6.1,20z" opacity="0.7"/></svg>';
            break;

        default:
            # code...

            break;
    }
}
endif;

if ( ! function_exists('yummy_bites_theme_comment') ) :
/**
 * Callback function for Comment List *
 * 
 * @link https://codex.wordpress.org/Function_Reference/wp_list_comments 
 */
function yummy_bites_theme_comment($comment, $args, $depth){
    if ('div' == $args['style']) {
        $tag = 'div';
        $add_below = 'comment';
    } else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
    ?>
    <<?php echo $tag ?> <?php comment_class(empty($args['has_children']) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">

        <?php if ('div' != $args['style']) : ?>
            <div id="div-comment-<?php comment_ID() ?>" class="comment-body" itemscope itemtype="http://schema.org/UserComments">
            <?php endif; ?>

            <footer class="comment-meta">
                <div class="comment-author vcard">
                    <?php if ($args['avatar_size'] != 0) echo get_avatar($comment, $args['avatar_size']); ?>
                </div><!-- .comment-author vcard -->
            </footer>

            <div class="text-holder">
                <div class="top">
                    <div class="left">
                        <?php if ($comment->comment_approved == '0') : ?>
                            <em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'yummy-bites'); ?></em>
                            <br />
                        <?php endif; ?>
                        <?php printf(__('<b class="fn" itemprop="creator" itemscope itemtype="http://schema.org/Person">%s</b> <span class="says">says:</span>', 'yummy-bites'), get_comment_author_link()); ?>
                        <div class="comment-metadata commentmetadata">
                            <?php esc_html_e('Posted on', 'yummy-bites'); ?>
                            <a href="<?php echo esc_url(htmlspecialchars(get_comment_link($comment->comment_ID))); ?>">
                                <time itemprop="commentTime" datetime="<?php echo esc_attr(get_gmt_from_date(get_comment_date() . get_comment_time(), 'Y-m-d H:i:s')); ?>"><?php printf(esc_html__('%1$s at %2$s', 'yummy-bites'), get_comment_date(),  get_comment_time()); ?></time>
                            </a>
                        </div>
                    </div>
                    <div class="reply">
                        <?php comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                    </div>
                </div>
                <div class="comment-content" itemprop="commentText"><?php comment_text(); ?></div>
            </div><!-- .text-holder -->

            <?php if ('div' != $args['style']) : ?>
            </div><!-- .comment-body -->
        <?php endif; ?>

        <?php
    }
endif;

if ( ! function_exists('yummy_bites_search_post_count') ) :
    /**
     * Search Result Page Count
     */
    function yummy_bites_search_post_count(){
        global $wp_query;
        $found_posts = $wp_query->found_posts;
        $visible_post = get_option('posts_per_page');

        if ($found_posts > 0) {
            echo '<span class="search-results-count">';
            if ($found_posts > $visible_post) {
                printf(esc_html__('Showing %1$s of %2$s Results', 'yummy-bites'), number_format_i18n($visible_post), number_format_i18n($found_posts));
            } else {
                /* translators: 1: found posts. */
                printf(_nx('%s Result', '%s Results', $found_posts, 'found posts', 'yummy-bites'), number_format_i18n($found_posts));
            }
            echo '</span>';
        }
    }
endif;

if ( ! function_exists('yummy_bites_sidebar') ) :
    /**
     * Return sidebar layouts for pages/posts
     */
    function yummy_bites_sidebar($class = false){
        global $post;
        $return      = false;
        $defaults    = yummy_bites_get_general_defaults();
        $page_layout = get_theme_mod('page_sidebar_layout', $defaults['page_sidebar_layout']); //Default Layout Style for Pages
        $post_layout = get_theme_mod('post_sidebar_layout', $defaults['post_sidebar_layout']); //Default Layout Style for Posts
        $layout      = get_theme_mod('layout_style', $defaults['layout_style']); //Default Layout Style for Styling Settings

        if ( is_singular() ) {
            if (get_post_meta($post->ID, '_yummy_bites_sidebar_layout', true)) {
                $sidebar_layout = get_post_meta($post->ID, '_yummy_bites_sidebar_layout', true);
            } else {
                $sidebar_layout = 'default-sidebar';
            }

            if ( is_page() ) {
                $dr_template = array( 'templates/pages/recipe-courses.php', 'templates/pages/recipe-cuisines.php', 'templates/pages/recipe-cooking-methods.php', 'templates/pages/recipe-keys.php', 'templates/pages/recipe-tags.php' );
                if( is_page_template( $dr_template ) ){
                    if( $page_layout == 'no-sidebar' ){
                        $return = $class ? 'full-width' : false;
                    }elseif( $page_layout == 'centered' ){
                        $return = $class ? 'full-width centered' : false;
                    }elseif( is_active_sidebar( 'delicious-recipe-sidebar' ) ){            
                        if( $class ){
                            if( $page_layout == 'right-sidebar' ) $return = 'rightsidebar'; //With Sidebar
                            if( $page_layout == 'left-sidebar' ) $return = 'leftsidebar';
                        }else{
                            $return = 'delicious-recipe-sidebar';    
                        }                         
                    }else{
                        $return = $class ? 'full-width' : false;
                    } 
                }elseif( is_active_sidebar('sidebar') ) {
                    if ($sidebar_layout == 'no-sidebar' || ($sidebar_layout == 'default-sidebar' && $page_layout == 'no-sidebar')) {
                        $return = $class ? 'full-width' : false;
                    } elseif ($sidebar_layout == 'centered' || ($sidebar_layout == 'default-sidebar' && $page_layout == 'centered')) {
                        $return = $class ? 'full-width centered' : false;
                    } elseif (($sidebar_layout == 'default-sidebar' && $page_layout == 'right-sidebar') || ($sidebar_layout == 'right-sidebar')) {
                        $return = $class ? 'rightsidebar' : 'sidebar';
                    } elseif (($sidebar_layout == 'default-sidebar' && $page_layout == 'left-sidebar') || ($sidebar_layout == 'left-sidebar')) {
                        $return = $class ? 'leftsidebar' : 'sidebar';
                    }
                } else {
                    $return = $class ? 'full-width' : false;
                }
                if (yummy_bites_is_woocommerce_activated() && (is_cart() || is_checkout())) {
                    $return = $class ? 'full-width' : false; //Fullwidth for woocommerce cart and checkout pages
                }
                if (is_front_page() && !is_home()) {
                    $return = $class ? 'full-width' : false; //Fullwidth for wishlist page
                }
            } 

            if ( is_single() ){
                if( 'product' === get_post_type() ){ //For Product Post Type
                    if( $post_layout == 'no-sidebar' || $post_layout == 'centered' ){
                        $return = $class ? 'full-width' : false; //Fullwidth
                    }elseif( is_active_sidebar( 'shop-sidebar' ) ){
                        if( $class ){
                            if( $post_layout == 'right-sidebar' ) $return = 'rightsidebar'; //With Sidebar
                            if( $post_layout == 'left-sidebar' ) $return = 'leftsidebar';
                        }
                    }else{
                        $return = $class ? 'full-width' : false; //Fullwidth
                    }
                }elseif( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE === get_post_type() ){ //For Product Post Type
                    if( $post_layout == 'no-sidebar' ){
                        $return = $class ? 'full-width' : false; //Fullwidth
                    }elseif( $post_layout == 'centered' ){
                        $return = $class ? 'full-width centered' : false; //Fullwidth
                    }elseif( is_active_sidebar( 'delicious-recipe-sidebar' ) ){
                        if( $class ){
                            if( $post_layout == 'right-sidebar' ) $return = 'rightsidebar'; //With Sidebar
                            if( $post_layout == 'left-sidebar' ) $return = 'leftsidebar';
                        }else{
                            $return = 'delicious-recipe-sidebar';
                        }
                    }else{
                        $return = $class ? 'full-width' : false; //Fullwidth
                    } 
                }elseif( 'post' === get_post_type() ){ //For default post type
                    if( is_active_sidebar( 'sidebar' ) ){
                        if( $sidebar_layout == 'no-sidebar' || ( $sidebar_layout == 'default-sidebar' && $post_layout == 'no-sidebar' ) ){
                            $return = $class ? 'full-width' : false;
                        }elseif( $sidebar_layout == 'centered' || ( $sidebar_layout == 'default-sidebar' && $post_layout == 'centered' ) ){
                            $return = $class ? 'full-width centered' : false;
                        }elseif( ( $sidebar_layout == 'default-sidebar' && $post_layout == 'right-sidebar' ) || ( $sidebar_layout == 'right-sidebar' ) ){
                            $return = $class ? 'rightsidebar' : 'sidebar';
                        }elseif( ( $sidebar_layout == 'default-sidebar' && $post_layout == 'left-sidebar' ) || ( $sidebar_layout == 'left-sidebar' ) ){
                            $return = $class ? 'leftsidebar' : 'sidebar';
                        }
                    }else{
                        $return = $class ? 'full-width' : false;
                    }
                }
            }
        } elseif (yummy_bites_is_woocommerce_activated() && (is_shop() || is_product_category() || is_product_tag())) {
            if ($layout == 'no-sidebar') {
                $return = $class ? 'full-width' : false;
            } elseif (is_active_sidebar('shop-sidebar')) {
                if ($class) {
                    if ($layout == 'right-sidebar') $return = 'rightsidebar'; //With Sidebar
                    if ($layout == 'left-sidebar') $return = 'leftsidebar';
                }
            } else {
                $return = $class ? 'full-width' : false;
            }
        } elseif( yummy_bites_is_delicious_recipe_activated() && (is_post_type_archive('recipe') || is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag', 'recipe-dietary']) ) ) {
            if ($layout == 'no-sidebar') {
                $return = $class ? 'full-width' : false; //Fullwidth
            } elseif (is_active_sidebar('delicious-recipe-sidebar')) {
                if ($class) {
                    if ($layout == 'right-sidebar') $return = 'rightsidebar'; //With Sidebar
                    if ($layout == 'left-sidebar') $return = 'leftsidebar';
                } else {
                    $return = 'delicious-recipe-sidebar';
                }
            } else {
                $return = $class ? 'full-width' : false; //Fullwidth
            }
        } else {
            if ($layout == 'no-sidebar') {
                $return = $class ? 'full-width' : false;
            } elseif (is_active_sidebar('sidebar')) {
                if ($class) {
                    if ($layout == 'right-sidebar') $return = 'rightsidebar'; //With Sidebar
                    if ($layout == 'left-sidebar') $return = 'leftsidebar';
                } else {
                    $return = 'sidebar';
                }
            } else {
                $return = $class ? 'full-width' : false;
            }
        }
        return $return;
    }
endif;


if ( ! function_exists('yummy_bites_get_categories') ) :
/**
 * Function to list post categories in customizer options
 */
function yummy_bites_get_categories($select = true, $taxonomy = 'category', $slug = false, $hide_empty = false){
    /* Option list of all categories */
    $categories = array();

    $args = array(
        'hide_empty' => $hide_empty,
        'taxonomy'   => $taxonomy
    );

    $catlists = get_terms($args);
    if ($select) $categories[''] = __('Choose Category', 'yummy-bites');
    foreach ($catlists as $category) {
        if ($slug) {
            $categories[$category->slug] = $category->name;
        } else {
            $categories[$category->term_id] = $category->name;
        }
    }

    return $categories;
}
endif;

if ( ! function_exists('yummy_bites_archive_image_sizes') ) :
/**
 * Home Image Sizes 
 */
function yummy_bites_archive_image_sizes(){
    if ( yummy_bites_pro_is_activated()) {
        $prodefaults    = yummy_bites_pro_get_customizer_layouts_defaults();
        if( is_home() || is_front_page() ){
            $blog_layout    =  get_theme_mod('blog_layouts', $prodefaults['blog_layouts']);
        }else{
            $blog_layout    = get_theme_mod('archive_layouts', $prodefaults['archive_layouts']);
        }
        
    }

    if (yummy_bites_pro_is_activated() && ( $blog_layout == 'two' || $blog_layout == 'three')) {
        $image_size =  'yummy-bites-blog-classic';
    } elseif (yummy_bites_pro_is_activated() &&  $blog_layout == 'four') {
        $image_size =  'yummy-bites-blog-four';
    } elseif (yummy_bites_pro_is_activated() &&  $blog_layout == 'five') {
        $image_size =  'yummy-bites-blog-five';
    } elseif ( yummy_bites_pro_is_activated() &&  $blog_layout == 'six') {
        $image_size =  'full';
    } else {
        $image_size =  'yummy-bites-blog-one';
    }

    return $image_size;
}
endif;


if ( ! function_exists('yummy_bites_single_image_sizes') ) :
/**
 * Home Image Sizes 
 */
function yummy_bites_single_image_sizes(){
    $sidebar        = yummy_bites_sidebar();

    if (yummy_bites_pro_is_activated()) {
        $single_layout  = yummy_bites_pro_single_meta_layout();
    }

    if (yummy_bites_pro_is_activated() && ($single_layout == 'two')) {
        $image_size = ($sidebar) ? 'yummy-bites-single-two' : 'yummy-bites-fullwidth';
    } elseif (yummy_bites_pro_is_activated() &&  $single_layout == 'three') {
        $image_size = ($sidebar) ? 'yummy-bites-single-three' : 'yummy-bites-fullwidth';
    } elseif (yummy_bites_pro_is_activated() &&  $single_layout == 'four') {
        $image_size = 'yummy-bites-fullwidth';
    } else {
        $image_size = ($sidebar) ? 'yummy-bites-single-one' : 'yummy-bites-fullwidth';
    }

    return $image_size;
}
endif;


if ( ! function_exists('yummy_bites_get_image_sizes') ) :
    /**
     * Get information about available image sizes
     */
    function yummy_bites_get_image_sizes($size = ''){

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach ($get_intermediate_image_sizes as $_size) {
            if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
                $sizes[$_size]['width'] = get_option($_size . '_size_w');
                $sizes[$_size]['height'] = get_option($_size . '_size_h');
                $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
                );
            }
        }
        // Get only 1 size if found
        if ($size) {
            if (isset($sizes[$size])) {
                return $sizes[$size];
            } else {
                return false;
            }
        }
        return $sizes;
    }
endif;


if ( ! function_exists('yummy_bites_get_fallback_svg') ) :
    /**
     * Get Fallback SVG
     */
    function yummy_bites_get_fallback_svg($post_thumbnail){
        if (!$post_thumbnail) {
            return;
        }

        $defaults      = yummy_bites_get_color_defaults();
        $image_size    = yummy_bites_get_image_sizes($post_thumbnail);
        $primary_color = get_theme_mod('primary_color', $defaults['primary_color']);

        if ($image_size) { ?>
            <div class="svg-holder">
                <svg class="fallback-svg" viewBox="0 0 <?php echo esc_attr($image_size['width']); ?> <?php echo esc_attr($image_size['height']); ?>" preserveAspectRatio="none">
                    <rect width="<?php echo esc_attr($image_size['width']); ?>" height="<?php echo esc_attr($image_size['height']); ?>" style="fill:<?php echo yummy_bites_sanitize_rgba($primary_color); ?>;opacity: 0.1"></rect>
                </svg>
            </div>
        <?php
        }
    }
endif;

if ( ! function_exists('yummy_bites_get_home_sections') ) :
    /**
     * Returns Home Sections 
     */
    function yummy_bites_get_home_sections(){
        $defaults  = yummy_bites_get_banner_defaults();
        $ed_banner = get_theme_mod('ed_banner_section', $defaults['ed_banner_section']);
        $sections = array(
            'newsletter',
            'blog',
            'about',
            'featured-on'
        );
        if ($ed_banner != 'no_banner') array_unshift($sections, 'banner');
        return apply_filters('yummy_bites_home_sections', $sections);
    }
endif;

if ( ! function_exists('yummy_bites_slider_meta_contents') ) :
    /**
     * Slider Meta
     */
    function yummy_bites_slider_meta_contents(){
        $defaults  = yummy_bites_get_banner_defaults();
        $read_more = get_theme_mod('slider_readmore', $defaults['slider_readmore']);

        if( yummy_bites_pro_is_activated() ){
            $prodefaults   = yummy_bites_pro_get_customizer_layouts_defaults();
            $slider_layout = get_theme_mod( 'slider_layouts', $prodefaults['slider_layouts'] );
        }

        if ( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) {
            if ( ( yummy_bites_pro_is_activated() && ( $slider_layout == 'one' ||  $slider_layout == 'three' ) )  || !yummy_bites_pro_is_activated() ){
                echo '<div class="cat-links-wrap">';
                    yummy_bites_recipe_category();
                echo '</div>';
            }
            the_title('<h2 class="item-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
            if ( ( yummy_bites_pro_is_activated() &&  $slider_layout == 'one') || !yummy_bites_pro_is_activated() ){
                echo '<div class="item-content">';
                    the_excerpt();
                echo '</div>';
            }
            echo '<footer class="item-footer">';
                if ( ( yummy_bites_pro_is_activated() &&  $slider_layout == 'one') || !yummy_bites_pro_is_activated() ){
                    if ( $read_more ) echo '<div class="btn-wrapper"> <a href="' . esc_url( get_the_permalink()) . '" class="btn-primary">' . esc_html( $read_more ) . '</a></div>';
                }
                echo '<div class="recipe-item-meta">';
                    yummy_bites_prep_time();
                    yummy_bites_difficulty_level();
                    yummy_bites_recipe_rating();
                echo '</div>';
            echo '</footer>';
        }elseif( 'post' == get_post_type() ){
            if ( ( yummy_bites_pro_is_activated() && ( $slider_layout == 'one' ||  $slider_layout == 'three' ) )  || ! yummy_bites_pro_is_activated() ){
                echo '<div class="cat-links-wrap">';
                    yummy_bites_category();
                echo '</div>';
            }
            the_title('<h2 class="item-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');

            if ( ( yummy_bites_pro_is_activated() &&  $slider_layout == 'one') || ! yummy_bites_pro_is_activated() ){
                echo '<div class="item-content">';
                    the_excerpt();
                echo '</div>';
            }

            if ( ( yummy_bites_pro_is_activated() &&  $slider_layout == 'one') || ! yummy_bites_pro_is_activated() ){
                echo '<footer class="item-footer">';
                    if ( $read_more ) echo '<div class="btn-wrapper"> <a href="' . esc_url( get_the_permalink()) . '" class="btn-primary">' . esc_html( $read_more ) . '</a></div>';
                echo '</footer>';
            }
        }
    }
endif;

if( ! function_exists('yummy_bites_blog_content') ) :
    /**
     * Content for homepage blog section
     *
     * @return void
     */
    function yummy_bites_blog_content(){
        $defaults       = yummy_bites_get_general_defaults();
        $meta_structure = get_theme_mod( 'blog_meta_order', $defaults['blog_meta_order'] );
        $readmore       = get_theme_mod( 'blog_readmore', $defaults['blog_readmore'] );?>
        <div class="content-wrapper">
            <header class="entry-header">
                <?php
                if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) {
                        yummy_bites_recipe_category();
                    the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
                }else{ 
                    yummy_bites_category(); ?>
                    <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php
                }
                echo '<div class="entry-meta">';
                    foreach( $meta_structure as $post_meta ){
                        if( $post_meta == 'author' ) yummy_bites_posted_by();
                        if( $post_meta == 'date' ) yummy_bites_posted_on();				
                        if( $post_meta == 'comment' ) yummy_bites_comment_count();
                        if( $post_meta == 'reading-time' ) yummy_bites_estimated_reading_time( get_post( get_the_ID() )->post_content );								
                    }						
                echo '</div>';?>
            </header>
            <div class="entry-content" itemprop="text">
                <?php the_excerpt(); ?>
            </div>  
            <footer class="entry-footer">  
                <div class="button-wrap">
                    <a href="<?php the_permalink(); ?>" class="btn-secondary"><?php echo esc_html( $readmore ); ?></a>
                </div>   
                <?php
                    if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) {
                        echo '<div class="meta-data">';
                            yummy_bites_prep_time();
                            yummy_bites_difficulty_level();
                            yummy_bites_recipe_rating();
                        echo '</div>';
                    } ?>
            </footer> 					
        </div>
        <?php
    }
endif;

function yummy_bites_search_only_posts_and_recipes( $query ) {

    
    if ( $query->is_search && !is_admin() ) {
        $search_array = yummy_bites_is_delicious_recipe_activated() ? array('post', DELICIOUS_RECIPE_POST_TYPE ) : array('post', 'page');
        // Only include posts and custom post types, excluding pages
        $query->set( 'post_type', $search_array );
    }
}

add_action( 'pre_get_posts', 'yummy_bites_search_only_posts_and_recipes' );

if ( ! function_exists('wp_body_open')) :
    /**
     * Fire the wp_body_open action.
     * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
     */
    function wp_body_open(){
        /**
         * Triggered after the opening <body> tag.
         */
        do_action('wp_body_open');
    }
endif;

if ( ! function_exists('yummy_bites_is_woocommerce_activated')) :
    /**
     * Query WooCommerce activation
     */
    function yummy_bites_is_woocommerce_activated(){
        return class_exists('woocommerce') ? true : false;
    }
endif;

if ( ! function_exists('yummy_bites_is_btnw_activated') ) :
    /**
     * Is BlossomThemes Email Newsletters active or not
     */
    function yummy_bites_is_btnw_activated(){
        return class_exists('Blossomthemes_Email_Newsletter') ? true : false;
    }
endif;

if ( ! function_exists('yummy_bites_is_delicious_recipe_activated') ) :
    /**
     * Check if Delicious Recipe Plugin is installed
     */
    function yummy_bites_is_delicious_recipe_activated()
    {
        return class_exists('WP_Delicious\DeliciousRecipes') ? true : false;
    }
endif;

if ( ! function_exists('yummy_bites_pro_is_activated') ) :
    /**
     * Check if Yummy Bites Pro is activated
     */
    function yummy_bites_pro_is_activated(){
        return class_exists('Yummy_Bites_Pro') ? true : false;
    }
endif;

if ( ! function_exists('yummy_bites_is_bttk_activated') ) :
    /**
     * Is BlossomThemes Toolkit Plugin active or not
     */
    function yummy_bites_is_bttk_activated(){
        return class_exists( 'Blossomthemes_Toolkit' ) ? true : false;
    }
endif;


/**
 * Checks if elementor is active or not
 * @return boolean
 * 
*/
function yummy_bites_is_elementor_activated(){
    return class_exists( 'Elementor\\Plugin' ) ? true : false; 
}
/**
 * Customizer Frontpage activated or not
*/
function yummy_bites_is_frontpage_customizer_activated(){
    return ( get_theme_mod( 'builder_types','customizer' ) == 'customizer' ) ? true : false;     
}

/**
 * Customizer Builder activated or not
*/
function yummy_bites_is_frontpage_builder_activated(){
    return ( get_theme_mod( 'builder_types','customizer' ) == 'builder' ) ? true : false;    
}

/**
 * Check whether the current edited post is created in Elementor Page Builder
 *
 * @return boolean
 */
function yummy_bites_is_elementor_activated_post(){
    if( yummy_bites_is_elementor_activated() && is_singular() && ! is_post_type_archive( 'recipe' ) ){
        global $post;
        $post_id = $post->ID;        
        return \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ? true : false;
    }else{
        return false;
    }
}

/*
 * Add filter only if function exists
 */
if (function_exists('DEMO_IMPORTER_PLUS_setup')) {
    add_filter(
        'demo_importer_plus_api_url',
        function () {
            return 'https://wpdeliciousdemo.com/';
        }
    );
}

if ( ! function_exists('yummy_bites_demo_importer_checked') ):
/**
 * Add filter only if function exists
 */
function yummy_bites_demo_importer_checked() {
    if (function_exists('DEMO_IMPORTER_PLUS_setup')) {
        add_filter(
            'demo_importer_plus_api_id',
            function () {
                return  array( '394', '398', '405', '108','97','168','176','180','181', '182', '148', '155', '158', '161', '166', '487', '551', '556' );
            }
        );
    }
}
endif;

/**
 * Function for demo importer
 */
yummy_bites_demo_importer_checked();

add_filter( 'demo-importer-plus:importable-site-options', function( $options ) {
    $options[] = 'delicious_recipe_settings';

    return $options;
} );

/**
 * Filter to modify the Demo Importer Plus link
 */
if ( ! yummy_bites_pro_is_activated() ) {
    add_filter( 'demo_importer_plus_get_pro_text', function() { return __( 'Get Yummy Bites Pro', 'yummy-bites' ); } );
    add_filter( 'demo_importer_plus_get_pro_url', function() { return esc_url('https://wpdelicious.com/wordpress-themes/yummy-bites-pro/?utm_source=yummy_bites&utm_medium=dashboard&utm_campaign=upgrade_to_pro'); } );
} else {
    add_filter( 'demo_importer_plus_get_pro_text', '__return_false' );
    add_filter( 'demo_importer_plus_get_pro_url', '__return_false' );
}