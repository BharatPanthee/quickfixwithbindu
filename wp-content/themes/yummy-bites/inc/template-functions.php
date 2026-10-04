<?php
/**
 * Yummy Bites Template Functions which enhance the theme by hooking into WordPress
 *
 * @package Yummy Bites
 */

if( ! function_exists( 'yummy_bites_doctype' ) ) :
/**
 * Doctype Declaration
*/
function yummy_bites_doctype(){ ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <?php
}
endif;
add_action( 'yummy_bites_doctype', 'yummy_bites_doctype' );

if( ! function_exists( 'yummy_bites_head' ) ) :
/**
 * Before wp_head 
*/
function yummy_bites_head(){ ?>
    <meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php
}
endif;
add_action( 'yummy_bites_before_wp_head', 'yummy_bites_head' );

if( ! function_exists( 'yummy_bites_page_start' ) ) :
/**
 * Page Start
*/
function yummy_bites_page_start(){ ?>
    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#acc-content"><?php esc_html_e( 'Skip to content (Press Enter)', 'yummy-bites' ); ?></a>
    <?php
}
endif;
add_action( 'yummy_bites_before_header', 'yummy_bites_page_start', 20 );

if( ! function_exists( 'yummy_bites_header' ) ) :
/**
 * Header Start
*/
function yummy_bites_header(){ 
    $defaults               = yummy_bites_get_general_defaults();
    $siteDefaults           = yummy_bites_get_site_defaults();
    $ed_social_media        = get_theme_mod( 'ed_social_links', $defaults['ed_social_links'] );
    $social_media_order     = get_theme_mod( 'social_media_order', $defaults['social_media_order']  );
    $ed_social_media_newtab = get_theme_mod( 'ed_social_links_new_tab', $defaults['ed_social_links_new_tab'] );
    $ed_cart                = get_theme_mod( 'ed_woo_cart', $defaults['ed_woo_cart'] );
    $ed_search              = get_theme_mod( 'ed_header_search', $defaults['ed_header_search'] );
    $blogname               = get_option('blogname');
    $hideblogname           = get_theme_mod('hide_title', $siteDefaults['hide_title']);
    $blogdesc               = get_option('blogdescription');
    $hideblogdesc           = get_theme_mod('hide_tagline', $siteDefaults['hide_tagline']);
    ?>
    <header id="masthead" class="site-header style-one" itemscope itemtype="https://schema.org/WPHeader">
        <?php if( $ed_social_media || $ed_search || ( yummy_bites_is_woocommerce_activated() && $ed_cart ) || has_nav_menu( 'secondary' ) ){ ?>
            <div class="header-top">
                <div class="container">
                    <?php if( has_nav_menu( 'secondary') ){ ?>
                        <div class="header-left">
                            <?php yummy_bites_secondary_navigation(); ?>
                        </div>
                    <?php } ?>
                    <?php if ( $ed_search || ( yummy_bites_is_woocommerce_activated() && $ed_cart ) || $ed_social_media ){ ?>
                        <div class="header-right">
                            <?php 
                                $social_icons = new Yummy_Bites_Social_Lists;
                                $social_icons->yummy_bites_social_links( $ed_social_media, $ed_social_media_newtab, $social_media_order );
                                if( $ed_search ) yummy_bites_search();
                                if( yummy_bites_is_woocommerce_activated() && $ed_cart ) yummy_bites_wc_cart_count();
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } if( has_nav_menu( 'primary') || has_custom_logo() || (!empty($blogname) && !$hideblogname) || ( !empty($blogdesc) && !$hideblogdesc) ) { ?>
            <div class="header-main"> 
                <div class="container">
                    <?php 
                    yummy_bites_site_branding(); 
                    yummy_bites_primary_navigation(); ?>
                </div>
            </div>
        <?php }
        yummy_bites_mobile_navigation(); ?>
	</header>
    <?php 
}
endif;

if( yummy_bites_pro_is_activated() && function_exists( 'yummy_bites_pro_header_layouts') ){
    add_action( 'yummy_bites_header', 'yummy_bites_pro_header_layouts', 20 );
} else {
    add_action( 'yummy_bites_header', 'yummy_bites_header', 20 );
}

if( ! function_exists( 'yummy_bites_content_start' ) ) :
/**
 * Content Start
*/
function yummy_bites_content_start(){
    $defaults           = yummy_bites_get_general_defaults();
    $ed_archive_desc    = get_theme_mod( 'ed_archive_desc', $defaults['ed_archive_desc'] );
    $archive_post_count = get_theme_mod( 'ed_archive_post_count', $defaults['ed_archive_post_count'] );
    $archive_alignment  = get_theme_mod( 'archivetitle_alignment', $defaults['archivetitle_alignment'] );
    $alignment          = '';
    $page_hdr_class     = '';
    $background_image = '';
    $style = '';
    
    if( is_search() ||  is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag', 'recipe-dietary']) ) {
        $alignment = ' data-alignment='. $archive_alignment;
		$page_hdr_class = 'page-header';
	}
        if( yummy_bites_is_delicious_recipe_activated() && ( is_archive()  || is_search() || is_post_type_archive( 'recipe' ) ||  is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag',  'recipe-dietary'])  ) ){
            $taxid                                = get_queried_object_id();
            $dr_taxonomy_metas                    = get_term_meta( $taxid, 'dr_taxonomy_metas', true );
            $get_thumb_id                         = isset( $dr_taxonomy_metas['taxonomy_image'] ) ? $dr_taxonomy_metas['taxonomy_image'] : false;
            $get_thumb_image                      = wp_get_attachment_image_src( $get_thumb_id, 'full' );
            $background_color                     = isset( $dr_taxonomy_metas['taxonomy_color']) && !empty($dr_taxonomy_metas['taxonomy_color']) ? 'style=background-color:' . $dr_taxonomy_metas['taxonomy_color'] : '';
            if( $get_thumb_image ) $background_image = ' style="background-image: url(' . esc_url( $get_thumb_image[0] ) . ');"';
            $style = isset( $background_image ) ? $background_image : $background_color;
        }
    if( !( is_front_page() && !is_home() ) ){ ?>
    <div id="acc-content" class="site-content">
        <?php if( is_search() || is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag','recipe-dietary'])  ){ ?>
            <div class="page-header-img-wrap <?php if( $background_image ) echo 'has-bg'; ?>" <?php echo $style; ?> >
                <div class="container">
                    <?php yummy_bites_breadcrumb();
                    echo '<div class="' . esc_attr( $page_hdr_class ) . '"' . esc_attr( $alignment) . '>';       
                        if( is_search() ){
                            echo '<h1 class="page-title">' . esc_html__( 'Search', 'yummy-bites' ) . '</h1>';
                            get_search_form();
                        }
                        if( is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag',  'recipe-dietary'])){
                            the_archive_title();
                            if( $ed_archive_desc ) the_archive_description( '<div class="archive-description">', '</div>' ); 
                        }
                        /**
                         * Show post count on search and archive pages
                         */
                        if( $archive_post_count ){
                            echo '<section class="tasty-recipes-search-count">';
                                yummy_bites_search_post_count();
                            echo '</section>';
                        }
                    echo '</div>';
                    ?>
                </div>
            </div>
        <?php }
        
        if( ( !( is_front_page() && is_home() ) && ! ( yummy_bites_is_elementor_activated() && yummy_bites_is_elementor_activated_post() ) && ! is_search() && !is_tax(['recipe-course', 'recipe-key', 'recipe-badge', 'recipe-cuisine', 'recipe-cooking-method', 'recipe-tag', 'recipe-dietary']) ) ) {
            echo '<div class="container">';
            $delicious_settings = get_option('delicious_recipe_settings');
            $banner_style = $delicious_settings && isset($delicious_settings['selectedBannerLayout']['id']) ? $delicious_settings['selectedBannerLayout']['id'] : 'default';
            if( function_exists( 'DEL_RECIPE_PRO' ) 
                && version_compare( DELICIOUS_RECIPES_PRO_VERSION, '2.2.2', '>' ) 
                && $banner_style !== 'default' 
            ) {
                if( !is_singular( 'recipe' ) ) {
                    yummy_bites_breadcrumb();
                }
            } else {
                yummy_bites_breadcrumb(); 
            }
            echo '</div>';
        }
        if (!is_404()) echo '<div class="container outer-wrap">';
    
    }
    
}
endif;
add_action( 'yummy_bites_content', 'yummy_bites_content_start', 20 );

if( ! function_exists( 'yummy_bites_single_before_content' ) ) :
/**
 * Blog and Archive Title
 */
function yummy_bites_before_content_single(){

    if( yummy_bites_pro_is_activated() ){
        $single_layout  = yummy_bites_pro_single_meta_layout();
        if ( ( $single_layout === 'three' || $single_layout === 'four' ) ){
            echo '<div class="single-page-header">'; 
            /**
             * @hooked yummy_bites_entry_header   - 15
             * @hooked yummy_bites_post_thumbnail - 20
            */
            do_action( 'yummy_bites_before_single_post_entry_content' );
            echo '</div>';
        }
    }
}
endif;
add_action( 'yummy_bites_before_post_content', 'yummy_bites_before_content_single' );

if( ! function_exists( 'yummy_bites_blog_title_header' ) ) :
/**
 * Blog and Archive Title
 */
function yummy_bites_blog_title_header(){

    $defaults          = yummy_bites_get_general_defaults();
    $archive_count     = get_theme_mod( 'ed_archive_post_count', $defaults['ed_archive_post_count'] );
    $ed_blog_title     = get_theme_mod( 'ed_blog_title', $defaults['ed_blog_title'] );
    $ed_blog_desc      = get_theme_mod( 'ed_blog_desc', $defaults['ed_blog_desc'] );
    $blog_alignment    = get_theme_mod( 'blog_alignment', $defaults['blog_alignment'] );
    $ed_archive_title  = get_theme_mod( 'ed_archive_title', $defaults['ed_archive_title'] );
    $ed_archive_desc   = get_theme_mod( 'ed_archive_desc', $defaults['ed_archive_desc'] );
    $archive_alignment = get_theme_mod( 'archivetitle_alignment', $defaults['archivetitle_alignment'] );
    if( is_home() ) {
        $alignment = ' data-alignment='.   $blog_alignment;
        $page_hdr_class = ( $ed_blog_title || $ed_blog_desc) ? 'page-header' : 'no-header-text';
    }
    if( is_archive() ) {
        $alignment = ' data-alignment='.   $archive_alignment;
        $page_hdr_class = ( $ed_archive_title || $ed_archive_desc) ? 'page-header' : 'no-header-text';
    }
    ?>
    <div class="page-header-img-wrap">
        <?php
        echo '<div class="' . esc_attr( $page_hdr_class ) . '"' . esc_attr( $alignment) . '>';       
            if ( is_home() && !is_front_page() ) {
                if ($ed_blog_title){ 
                    echo '<h1 class="page-title">';
                        single_post_title();
                    echo '</h1>';
                }
                if( $ed_blog_desc ){
                    $posts_id   = get_option('page_for_posts');
                    $posts_desc = get_post_field( 'post_content', $posts_id );
                    echo '<div class="archive-description">'. wp_kses_post( $posts_desc ) .'</div>';
                }
            }
            
            if( is_archive() ){
                if( is_author()|| get_the_author_meta( 'description' ) ){ ?>
                    <section class="tasty-recipes-author-box">
                        <div class="author-archive-section">
                            <div class="img-holder"><?php echo get_avatar( get_the_author_meta( 'ID' ), 95 ); ?></div>
                            <div class="author-meta">
                                <?php printf( esc_html__( '%1$s %2$s%3$s%4$s', 'yummy-bites' ), '<h3 class="author-name">', '<span class="vcard">', esc_html( get_the_author_meta( 'display_name' ) ), '</span></h3>' );                
                                    echo '<div class="author-description">' . wp_kses_post( get_the_author_meta( 'description' ) ) . '</div>';
                                ?>
                            </div>
                        </div>
                    </section>
                    <?php 
                }else {  
                if( $ed_archive_title ) the_archive_title();
                if( $ed_archive_desc ) the_archive_description( '<div class="archive-description">', '</div>' );
                }
            }
            /**
             * Show post count on search and archive pages
             */
            if( ( $archive_count && ( is_archive() ) && !is_post_type_archive('product') )) {
                echo '<section class="tasty-recipes-search-count">';
                yummy_bites_search_post_count();
                echo '</section>';
            }
        echo '</div>';
        ?>
    </div>
<?php
}
endif;
add_action( 'yummy_bites_before_blog_posts_content', 'yummy_bites_blog_title_header' );

if ( ! function_exists( 'yummy_bites_post_thumbnail' ) ) :
/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 */
function yummy_bites_post_thumbnail() {

    $image_size       = 'thumbnail';
    $defaults         = yummy_bites_get_general_defaults();
    $ed_featured      = get_theme_mod( 'ed_post_featured_image', $defaults['ed_post_featured_image']);
    $post_crop_image  = get_theme_mod( 'post_crop_image', $defaults[ 'post_crop_image' ] );
    $ed_page_featured = get_theme_mod( 'ed_page_featured_image', $defaults['ed_page_featured_image'] );
    $blog_crop_img    = get_theme_mod( 'blog_crop_image', $defaults[ 'blog_crop_image' ] );

    if( yummy_bites_pro_is_activated() ){
        $ed_social_sharing = get_theme_mod( 'ed_social_sharing', true );
        $ed_sticky_sharing = get_theme_mod( 'ed_sticky_social_sharing', true );
    }

    if( yummy_bites_is_delicious_recipe_activated() && is_singular( DELICIOUS_RECIPE_POST_TYPE ) ) return false;

    if( is_home() || is_archive() || is_search() ){ 
        if( !$blog_crop_img ){
            $image_size = 'full';
        }else{
            $image_size = yummy_bites_archive_image_sizes();
        }
        
        echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';  
            if( has_post_thumbnail() ){            
                the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );            
            }else{
                yummy_bites_get_fallback_svg( $image_size );//fallback      
            }    
            echo '</a>';
            if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) yummy_bites_recipe_keywords();
            if( function_exists( 'yummy_bites_social_share' ) && $ed_social_sharing ) yummy_bites_social_share( $ed_sticky_sharing );
        echo '</figure>';   
    }elseif( is_singular() ){
        $image_size = yummy_bites_single_image_sizes();
        if( is_single() ){
            $image_size = !$post_crop_image ? 'full' : $image_size;
            if( $ed_featured && has_post_thumbnail() ){
                echo '<div class="post-thumbnail">';
                the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );
                echo '</div>';    
            }
        }else{
            if( $ed_page_featured && has_post_thumbnail() ){
                echo '<div class="post-thumbnail">';
                the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );
                echo '</div>';    
            }            
        }
    }elseif( is_front_page() ){
        $image_size = yummy_bites_archive_image_sizes();
        echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';  
            if( has_post_thumbnail() ){            
                the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );            
            }else{
                yummy_bites_get_fallback_svg( $image_size );//fallback    
            }    
            echo '</a>';
            if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) yummy_bites_recipe_keywords();
            if( function_exists( 'yummy_bites_social_share' ) && $ed_social_sharing ) yummy_bites_social_share( $ed_sticky_sharing );
        echo '</figure>'; 
    }
}
endif;
add_action( 'yummy_bites_before_page_entry_content', 'yummy_bites_post_thumbnail' );
add_action( 'yummy_bites_before_post_entry_content', 'yummy_bites_post_thumbnail', 15 );
add_action( 'yummy_bites_before_single_post_entry_content', 'yummy_bites_post_thumbnail', 20 );

if( ! function_exists( 'yummy_bites_entry_header' ) ) :
/**
 * Entry Header
*/
function yummy_bites_entry_header(){ 
    global $post; 
    $defaults          = yummy_bites_get_general_defaults();
    $meta_structure    = get_theme_mod( 'blog_meta_order', $defaults['blog_meta_order'] );
    $single_post_meta  = get_theme_mod( 'post_meta_order', $defaults['post_meta_order'] );
    $ed_page_title     = get_theme_mod( 'ed_page_title', $defaults['ed_page_title'] );
    $page_alignment    = get_theme_mod( 'pagetitle_alignment', $defaults['pagetitle_alignment'] );
    $single_page_title = get_post_meta( $post->ID, '_yummy_page_title', true );
    $add_class         = $single_page_title == true ? ' yummy_title_disabled': '';
    $alignment = '';

    if( yummy_bites_is_delicious_recipe_activated() && is_singular( DELICIOUS_RECIPE_POST_TYPE ) ) return false; 
    if (is_page()){
        $alignment = ' data-alignment='. $page_alignment;
    }  
    if( !is_single() ) echo '<div class="content-wrapper">'; ?>
        <header class="entry-header<?php echo esc_attr( $add_class ); ?>"<?php echo esc_attr($alignment); ?>>
            <?php 
                $ed_cat_single  = get_theme_mod( 'ed_post_category', $defaults['ed_post_category'] );
                
                if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) {
                    yummy_bites_recipe_category();
                    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                    
                    echo '<div class="entry-meta">';
                    foreach( $meta_structure as $post_meta ){
                        if( $post_meta == 'author' ) yummy_bites_posted_by();
                        if( $post_meta == 'date' ) yummy_bites_posted_on();				
                        if( $post_meta == 'comment' ) yummy_bites_comment_count();
                        if( $post_meta == 'reading-time' ) yummy_bites_estimated_reading_time( get_post( get_the_ID() )->post_content );								
                    }
                    echo '</div>';
                }elseif( is_single() || is_search() || is_archive() || is_home()){

                    echo '<div class="entry-meta">';
                        if( is_single() ){
                            if( $ed_cat_single ) yummy_bites_category();
                        }else{
                            yummy_bites_category();    
                        }
                    echo '</div>';

                    if ( is_single() ){
                        the_title( '<h1 class="entry-title">', '</h1>' );
                    } else{
                        the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                    }
                    
                    echo '<div class="entry-meta">';
                        if( is_home()) {
                            foreach( $meta_structure as $post_meta ){
                                if( $post_meta == 'author' ) yummy_bites_posted_by();
                                if( $post_meta == 'date' ) yummy_bites_posted_on();				
                                if( $post_meta == 'comment' ) yummy_bites_comment_count();				
                                if( $post_meta == 'reading-time' ) yummy_bites_estimated_reading_time( get_post( get_the_ID() )->post_content );				
                            }
                        }elseif( is_single() ){
                            foreach( $single_post_meta as $post_meta ){
                                if( $post_meta == 'author' ) yummy_bites_posted_by();
                                if( $post_meta == 'date' ) yummy_bites_posted_on();				
                                if( $post_meta == 'comment' ) yummy_bites_comment_count();				
                                if( $post_meta == 'reading-time' ) yummy_bites_estimated_reading_time( get_post( get_the_ID() )->post_content );				
                            }
                        }elseif( !is_single()){
                            yummy_bites_posted_by();
                            yummy_bites_posted_on();				
                            yummy_bites_comment_count();
                            yummy_bites_estimated_reading_time( get_post( get_the_ID() )->post_content );
                        }

                    echo '</div>';
                }

                if ( is_singular() ) {
                    if( is_page() && $ed_page_title && !is_front_page() ) the_title( '<h1 class="page-title">', '</h1>' );
                } 

            ?>
        </header>         
    <?php    
}
endif;
add_action( 'yummy_bites_before_page_entry_content', 'yummy_bites_entry_header', 10 );
add_action( 'yummy_bites_before_post_entry_content', 'yummy_bites_entry_header', 20 );
add_action( 'yummy_bites_before_single_post_entry_content', 'yummy_bites_entry_header', 15 );

if( ! function_exists( 'yummy_bites_entry_content' ) ) :
/**
 * Entry Content
*/
function yummy_bites_entry_content(){

    $defaults            = yummy_bites_get_general_defaults();
    $blog_content        = get_theme_mod( 'blog_content', $defaults['blog_content'] );

    if( yummy_bites_pro_is_activated() ){
        $prodefaults       = yummy_bites_pro_get_customizer_layouts_defaults();
        if( is_home() || is_front_page() ){
            $blog_layout    =  get_theme_mod('blog_layouts', $prodefaults['blog_layouts']);
        }else{
            $blog_layout    = get_theme_mod('archive_layouts', $prodefaults['archive_layouts']);
        }
        $ed_social_sharing = get_theme_mod( 'ed_social_sharing', true );
        $ed_sticky_sharing = get_theme_mod( 'ed_sticky_social_sharing', true );
    }

	if ( is_single() ) echo '<div class="article-wrapper">'; 
        if( yummy_bites_is_woocommerce_activated() && is_cart()) woocommerce_output_all_notices(); ?>
        <div class="entry-content" itemprop="text">
            <?php
                if( is_singular() || $blog_content === 'content' ){
                    the_content();    
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'yummy-bites' ),
                        'after'  => '</div>',
                    ) );
                }elseif( ( yummy_bites_pro_is_activated() && $blog_layout !== 'four') || !yummy_bites_pro_is_activated() ){
                    the_excerpt();
                }
            ?>
        </div><!-- .entry-content -->
        <?php if( is_single() && function_exists( 'yummy_bites_social_share' ) && $ed_social_sharing ) yummy_bites_social_share( $ed_sticky_sharing );
        if ( is_single() ) echo '</div>';

}
endif;
add_action( 'yummy_bites_page_entry_content', 'yummy_bites_entry_content', 15 );
add_action( 'yummy_bites_post_entry_content', 'yummy_bites_entry_content', 15 );
add_action( 'yummy_bites_single_post_entry_content', 'yummy_bites_entry_content', 15 );

if( ! function_exists( 'yummy_bites_entry_footer' ) ) :
/**
 * Entry Footer
*/
function yummy_bites_entry_footer(){ 
    $defaults  = yummy_bites_get_general_defaults();
    $readmore  = get_theme_mod( 'read_more_text',  $defaults['read_more_text'] );
    $post_tags = get_theme_mod( 'ed_post_tags', $defaults['ed_post_tags'] ); 
    if( is_front_page() && !is_home() ) return; ?>
	<footer class="entry-footer">
		<?php
			if( is_single() && $post_tags ){
                yummy_bites_tag();
			}
            
            if( is_home() || is_archive() || is_search() ){
                echo '<div class="button-wrap">';
                echo '<a href="' . esc_url( get_the_permalink() ) . '" class="btn-secondary">' . esc_html( $readmore ) . '</a>';
                echo '</div>';    
            }
            
            if( yummy_bites_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() && !is_single() ) {
                echo '<div class="meta-data">';
                    yummy_bites_prep_time();
                    yummy_bites_difficulty_level();
                    yummy_bites_recipe_rating();
                echo '</div>';
            }

            if( get_edit_post_link() ){
                edit_post_link(
					sprintf(
						wp_kses(
							/* translators: %s: Name of current post. Only visible to screen readers */
							__( 'Edit <span class="screen-reader-text">%s</span>', 'yummy-bites' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						),
						get_the_title()
					),
					'<span class="edit-link">',
					'</span>'
				);
            }
		?>
	</footer><!-- .entry-footer -->
    <?php if( !is_single() ) echo '</div>'; ?><!-- .content-wrapper -->
    <?php

}
endif;
add_action( 'yummy_bites_page_entry_content', 'yummy_bites_entry_footer', 20 );
add_action( 'yummy_bites_post_entry_content', 'yummy_bites_entry_footer', 20 );
add_action( 'yummy_bites_single_post_entry_content', 'yummy_bites_entry_footer', 20 );

if( ! function_exists( 'yummy_bites_author' ) ) :
/**
 * Author Section
*/
function yummy_bites_author(){
    $defaults     = yummy_bites_get_general_defaults();
    $ed_author    = get_theme_mod( 'ed_author', $defaults['ed_author'] );
    $author_title = get_theme_mod( 'author_title', $defaults['author_title'] );
    if( $ed_author && get_the_author_meta( 'description' ) ){ ?>
        <section class="tasty-recipes-author-box">
            <div class="author-section">
                <div class="img-holder"><?php echo get_avatar( get_the_author_meta( 'ID' ), 160 ); ?></div>
                <div class="author-content">
                    <div class="author-meta">
                        <?php 
                            if( $author_title ) echo '<span class="sub-title">' . esc_html( $author_title ) . '</span>';
                            echo '<h3 class="title">' . esc_html(  get_the_author_meta( 'display_name' ) ) . '</h3>';
                            echo wp_kses_post( wpautop( get_the_author_meta( 'description' ) ) );
                        ?>		
                    </div>
                    <?php 
                        if( yummy_bites_pro_is_activated() && function_exists( 'yummy_bites_author_social' ) ){
                            ?>
                                <div class="author-social-links"><?php yummy_bites_author_social(); ?></div>
                            <?php 
                        }
                    ?>
                </div>
            </div>
        </section>
    <?php 
    }
}
endif;
add_action( 'yummy_bites_after_post_content', 'yummy_bites_author', 15 );
    

if( ! function_exists( 'yummy_bites_navigation' ) ) :
/**
 * Navigation
*/
function yummy_bites_navigation(){
    
    if( yummy_bites_is_delicious_recipe_activated() && is_singular( DELICIOUS_RECIPE_POST_TYPE ) ) return false;

    $defaults   = yummy_bites_get_general_defaults();
    $pagination = get_theme_mod( 'ed_post_pagination',  $defaults['ed_post_pagination'] );
    if( is_single() && $pagination ){
        $prev_post  = get_previous_post();
        $next_post  = get_next_post();
        
        if( $prev_post || $next_post ){?>            
            <nav class="navigation yummy-post-navigation" role="navigation">
                <h2 class="screen-reader-text"><?php esc_html_e( 'Post Navigation', 'yummy-bites' ); ?></h2>
                <div class="nav-links">
                    <?php
                    if( $prev_post ){ ?>
                        <div class="nav-previous nav-holder">
                            <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
                                <span class="meta-nav"><?php esc_html_e( 'Previous Article', 'yummy-bites' ); ?></span>
                            </a>
                            <div class ="prev-nav-wrapper">
                                <figure class="post-thumbnail">
                                    <?php
                                    $prev_img = get_post_thumbnail_id( $prev_post->ID ); ?>
                                    <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
                                        <?php if( $prev_img ){
                                            echo wp_get_attachment_image ( $prev_img, 'thumbnail' );                                        
                                        }else{
                                            yummy_bites_get_fallback_svg( 'thumbnail' );
                                        } ?>
                                    </a>
                                </figure>
                                <span class="post-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></h3>
                            </div>
                        </div>
                    <?php }
                    if( $next_post ){ ?>
                    <div class="nav-next nav-holder">
                        <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="prev">
                            <span class="meta-nav"><?php esc_html_e( 'Next Article', 'yummy-bites' ); ?></span>
                        </a>
                        <div class ="next-nav-wrapper">
                            <figure class="post-thumbnail">
                                <?php
                                $next_img = get_post_thumbnail_id( $next_post->ID ); ?>
                                <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next">
                                    <?php if( $next_img ){
                                        echo wp_get_attachment_image( $next_img, 'thumbnail');                                        
                                    }else{
                                        yummy_bites_get_fallback_svg( 'thumbnail' );
                                    }
                                    ?>
                                </a>
                            </figure>
                            <span class="post-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></h3>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </nav>        
            <?php
        }
    }elseif( ! is_single() && yummy_bites_pro_is_activated() ){
        $prodefaults = yummy_bites_pro_get_customizer_layouts_defaults();
        $pagination  = get_theme_mod( 'pagination_layouts', $prodefaults['pagination_layouts'] );

        switch( $pagination ){
            case 'default': // Default Pagination
            
            the_posts_navigation();
            
            break;
            
            case 'numbered': // Numbered Pagination
            
            the_posts_pagination( array(
                'prev_text'          => __( 'Previous', 'yummy-bites' ),
                'next_text'          => __( 'Next', 'yummy-bites' ),
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'yummy-bites' ) . ' </span>',
            ) );
            
            break;
            
            case 'load_more': // Load More Button
            case 'infinite_scroll': // Auto Infinite Scroll
            
            echo '<div class="pagination-blog"></div>';
            
            break;
            
            default:
            
            the_posts_navigation();
            
            break;
        }
    }else {
        the_posts_pagination( array(
            'prev_text'          => __( 'Previous', 'yummy-bites' ),
            'next_text'          => __( 'Next', 'yummy-bites' ),
            'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'yummy-bites' ) . ' </span>',
        ) );
    }
}
endif;
add_action( 'yummy_bites_after_post_content', 'yummy_bites_navigation', 20 );
add_action( 'yummy_bites_after_posts_content', 'yummy_bites_navigation' );

if( ! function_exists( 'yummy_bites_latest_posts' ) ) :
/**
 * Latest Posts
*/
function yummy_bites_latest_posts(){ 
    yummy_bites_get_posts_list( 'latest' );
}
endif;
add_action( 'yummy_bites_latest_posts', 'yummy_bites_latest_posts' );

if( ! function_exists( 'yummy_bites_comment' ) ) :
/**
 * Comments Template 
*/
function yummy_bites_comment(){
    // If comments are open or we have at least one comment, load up the comment template.
    
    $defaults      = yummy_bites_get_general_defaults();
    $page_comments = get_theme_mod( 'ed_page_comments', $defaults['ed_page_comments'] );
    $post_comments = get_theme_mod( 'ed_post_comments', $defaults['ed_post_comments'] );
    if ( (is_page() && !$page_comments) || (is_single() && !$post_comments) ) return;
	
    if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;
}
endif;
add_action( 'yummy_bites_after_page_content', 'yummy_bites_comment' );

if( ! function_exists( 'yummy_bites_comment_location_hook' ) ) :
/**
 * Comments Location Hook in Single Post
*/
function yummy_bites_comment_location_hook(){
    add_action( 'yummy_bites_after_post_content', 'yummy_bites_comment', yummy_bites_comment_toggle() );
}
endif;
add_action( 'wp', 'yummy_bites_comment_location_hook', 10 );

if( ! function_exists( 'yummy_bites_content_end' ) ) :
/**
 * Content End
*/
function yummy_bites_content_end(){ 
    
    $home_sections = yummy_bites_get_home_sections();
    if( !( is_front_page() && ! is_home() && $home_sections ) ){
        if(!is_404()) echo '</div><!-- .container -->'; ?>        
    </div><!-- .site-content -->
    <?php
    }
}
endif;
add_action( 'yummy_bites_before_footer', 'yummy_bites_content_end', 20 );

if( ! function_exists( 'yummy_bites_footer_instagram_section' ) ) :
/**
 * Bottom Shop Section
*/
function yummy_bites_footer_instagram_section(){ 

    $defaults     = yummy_bites_get_general_defaults();
    $ed_instagram = get_theme_mod( 'ed_instagram', $defaults['ed_instagram'] );
    $insta_code   = get_theme_mod('instagram_shortcode', '[instagram-feed]' );
    if( $ed_instagram ){
        echo '<div class="instagram-section">' . do_shortcode( $insta_code ) . '</div>';
    }
    
}
endif;
add_action( 'yummy_bites_footer', 'yummy_bites_footer_instagram_section', 15 );

if( ! function_exists( 'yummy_bites_footer_start' ) ) :
/**
 * Footer Start
*/
function yummy_bites_footer_start(){
    ?>
    <footer id="colophon" class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
    <?php
}
endif;
add_action( 'yummy_bites_footer', 'yummy_bites_footer_start', 20 );


if( ! function_exists( 'yummy_bites_footer_top' ) ) :
/**
 * Footer Top
*/
function yummy_bites_footer_top(){    
    $footer_sidebars = array( 'footer-one', 'footer-two', 'footer-three', 'footer-four' );
    $active_sidebars = array();
    $sidebar_count   = 0;
    
    foreach ( $footer_sidebars as $sidebar ) {
        if( is_active_sidebar( $sidebar ) ){
            array_push( $active_sidebars, $sidebar );
            $sidebar_count++ ;
        }
    }

    if( $active_sidebars ){ ?>
        <div class="footer-t">
            <div class="container">
                <div class="grid column-<?php echo esc_attr( $sidebar_count ); ?>">
                    <?php foreach( $active_sidebars as $active ){ ?>
                        <div class="col">
                            <?php dynamic_sidebar( $active ); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php 
    }   
}
endif;
add_action( 'yummy_bites_footer', 'yummy_bites_footer_top', 30 );

if( ! function_exists( 'yummy_bites_footer_bottom' ) ) :
/**
 * Footer Bottom
*/
function yummy_bites_footer_bottom(){ ?>
    <div class="footer-b">
		<div class="container">
            <div class="footer-bottom-t">
                <div class="site-info">            
                    <?php
                        yummy_bites_get_footer_copyright();
                        if( yummy_bites_pro_is_activated() ){
                            $partials = new Yummy_Bites_Partials;
                            $partials->yummy_bites_pro_ed_author_link();
                            $partials->yummy_bites_pro_ed_wp_link();
                        }else {
                            echo esc_html__( ' Yummy Bites | Developed By ', 'yummy-bites' ); 
                            echo '<a href="' . esc_url( 'https://wpdelicious.com/' ) .'" rel="nofollow" target="_blank">' . esc_html__( 'WP Delicious', 'yummy-bites' ) . '</a>.';                
                            printf( esc_html__( ' Powered by %s. ', 'yummy-bites' ), '<a href="'. esc_url( 'https://wordpress.org/' ) .'" rel="nofollow" target="_blank">WordPress</a>' );
                        }
                        if( function_exists( 'the_privacy_policy_link' ) ){
                            the_privacy_policy_link();
                        }
                    ?> 
                </div>
                <?php yummy_bites_footer_navigation(); ?>
            </div>
		</div>
	</div>
    <?php
}
endif;
add_action( 'yummy_bites_footer', 'yummy_bites_footer_bottom', 40 );

if( ! function_exists( 'yummy_bites_footer_end' ) ) :
/**
 * Footer End 
*/
function yummy_bites_footer_end(){ ?>
    </footer><!-- #colophon -->
    </div><!-- #acc-content -->
    <?php
}
endif;
add_action( 'yummy_bites_footer', 'yummy_bites_footer_end', 50 );

if( ! function_exists( 'yummy_bites_scrolltotop' ) ) :
/**
 * Scroll To Top
 */
function yummy_bites_scrolltotop(){
    $defaults    = yummy_bites_get_general_defaults();
    $scrolltotop = get_theme_mod( 'ed_scroll_top', $defaults['ed_scroll_top'] );
    if( $scrolltotop ){ ?>
        <div class="back-to-top"> 
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="inherit" d="M6.101 359.293L25.9 379.092c4.686 4.686 12.284 4.686 16.971 0L224 198.393l181.13 180.698c4.686 4.686 12.284 4.686 16.971 0l19.799-19.799c4.686-4.686 4.686-12.284 0-16.971L232.485 132.908c-4.686-4.686-12.284-4.686-16.971 0L6.101 342.322c-4.687 4.687-4.687 12.285 0 16.971z"></path></svg>
        </div>
        <?php
    }
}
endif;
add_action( 'yummy_bites_after_footer', 'yummy_bites_scrolltotop', 15 );

if( ! function_exists( 'yummy_bites_page_end' ) ) :
/**
 * Page End
*/
function yummy_bites_page_end(){ ?>
    </div><!-- #page -->
    <?php
}
endif;
add_action( 'yummy_bites_after_footer', 'yummy_bites_page_end', 20 );