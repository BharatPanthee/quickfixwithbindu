<?php
/**
 * Featured On Section
 * 
 * @package Yummy_Bites
 */
$defaults             = yummy_bites_get_general_defaults();
$feature_recipe_title = get_theme_mod( 'feature_recipe_title', $defaults['feature_recipe_title'] );

if( $feature_recipe_title || is_active_sidebar( 'featured-on' ) ){
?>
<div id="featured_on_section" class="featured-on-section section">
    <div class="container">
        <?php if( $feature_recipe_title ){
            echo '<div class="section-header"><h2 class="section-title">' . esc_html( $feature_recipe_title ) . '</h2></div>';
        }
        if( is_active_sidebar( 'featured-on' ) ){ ?>
            <aside id="secondary" class="widget-area" itemscope itemtype="https://schema.org/WPSideBar">                         
                <?php dynamic_sidebar( 'featured-on' ); ?>
            </aside>
        <?php } ?>
    </div>
</div>
<?php }