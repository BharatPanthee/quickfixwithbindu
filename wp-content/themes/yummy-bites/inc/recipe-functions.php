<?php
/**
 * WP Delicious Functions.
 *
 * @package Yummy_Bites
 */

if( ! function_exists( 'yummy_bites_recipe_keywords' ) ) :
/**
 * Recipe Keys.
 */
function yummy_bites_recipe_keywords(){
    global $recipe;
    if ( ! empty( $recipe->recipe_keys ) ) : ?>
        <span class="dr-category">
            <?php
            foreach( $recipe->recipe_keys as $recipe_key ) {
                $key              = get_term_by( 'name', $recipe_key, 'recipe-key' ); ?>
                <a href="<?php echo esc_url( get_term_link( $key, 'recipe-key' ) ); ?>" title="<?php echo esc_attr( $recipe_key ); ?>">
                    <?php delicious_recipes_get_tax_icon( $key ); ?>
                    <span class="cat-name"><?php echo esc_html( $recipe_key ); ?></span>
                </a>
            <?php } ?>
        </span>
    <?php endif;
}
endif;

if( ! function_exists( 'yummy_bites_prep_time' ) ) :
/**
 * Prep Time.
 */
function yummy_bites_prep_time(){
    global $recipe;
    if( $recipe->total_time ) : ?>
        <span class="cook-time">
            <svg class="icon">
                <use xlink:href="<?php echo plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ?>assets/images/sprite.svg#time"></use>
            </svg>
            <span class="meta-text"><?php echo esc_html( $recipe->total_time ); ?></span>
        </span>
    <?php endif;
}
endif;

if( ! function_exists( 'yummy_bites_difficulty_level' ) ) :
/**
 * Difficulty Level.
 */
function yummy_bites_difficulty_level(){
    global $recipe;
    if( $recipe->difficulty_level ) : ?>
        <span class="cook-difficulty">
            <svg class="icon">
                <use xlink:href="<?php echo plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ?>assets/images/sprite.svg#difficulty"></use>
            </svg>
            <span class="meta-text"><?php echo esc_html( ucfirst( $recipe->difficulty_level ) ); ?></span>
        </span>
    <?php endif;
}
endif;

if( ! function_exists( 'yummy_bites_recipe_category' ) ) :
/**
 * Difficulty Level.
 */
function yummy_bites_recipe_category(){
    global $recipe;
    if ( ! empty( $recipe->ID ) ) :
        $categories = get_the_terms( $recipe->ID, 'recipe-course' );
        
        if( $categories ){
            foreach( $categories as $catID){
                $recipe_key_metas = get_term_meta( $catID->term_id, 'dr_taxonomy_metas', true ); 
                $color_id = isset( $recipe_key_metas['taxonomy_color']) && !empty($recipe_key_metas['taxonomy_color']) ? 'style=--yummy-category-color:' . $recipe_key_metas['taxonomy_color'] : 'style=--yummy-category-color:var(--yummy-primary-color)';
                echo '<span class="cat-links"' . esc_attr($color_id) . ' itemprop="about"><a href="'. esc_url( get_term_link($catID->term_id) ) . '" rel="category tag">' . esc_html($catID->name) . '</a></span>';
            }
        }
    ?>
    <?php endif;
}
endif;
    
if( ! function_exists( 'yummy_bites_recipe_rating' ) ) :
/**
 * Rating
 */
function yummy_bites_recipe_rating(){
    global $recipe;
    if ( $recipe->rating ): ?>
        <span class="post-rating">
            <svg class="icon">
                <use xlink:href="<?php echo plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ?>assets/images/sprite.svg#rating"></use>
            </svg>
            <span class="meta-text"><?php echo esc_html( $recipe->rating ); ?></span>
        </span>
    <?php endif;
}
endif;
    
if( ! function_exists( 'yummy_bites_recipe_pinit' ) ) :
/**
 * Pin it
 */
function yummy_bites_recipe_pinit(){
    global $recipe;
    ?>
    <span class="post-pinit-button">
        <a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>/&media=<?php echo esc_url( $recipe->thumbnail ); ?>&description=So%20delicious!" data-pin-custom="true">
            <img src="<?php echo plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ?>/assets/images/pinit-sm.png" alt="pinit">
        </a>
    </span>
    <?php
}
endif;

if( ! function_exists( 'yummy_bites_recipes_autoload_selector' ) ) :
/**
 * Recipes Autoload Selector.
 */
function yummy_bites_recipes_autoload_selector() {
    return '.site-content > .outer-wrap';
}
endif;
add_filter( 'wp_delicious_autoload_selector', 'yummy_bites_recipes_autoload_selector' );

if( ! function_exists( 'yummy_bites_recipes_autoload_append_selector' ) ) :
/**
 * Recipes Autoload Append Selector.
 */
function yummy_bites_recipes_autoload_append_selector() {
    return '.outer-wrap';
}
endif;
add_filter( 'wp_delicious_autoload_append_selector', 'yummy_bites_recipes_autoload_append_selector' );