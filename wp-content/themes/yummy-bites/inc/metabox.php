<?php 
/**
* Yummy Bites Metabox for Sidebar Layout
*
* @package Yummy Bites
*
*/ 
if( ! function_exists( 'yummy_bites_add_sidebar_layout_box' ) ) :

function yummy_bites_add_sidebar_layout_box(){
    
    //for post
    add_meta_box( 
        'yummy_bites_sidebar_layout',
        __( 'Sidebar Layout', 'yummy-bites' ),
        'yummy_bites_sidebar_layout_callback', 
        'post',
        'normal',
        'high'
    );

    
    add_meta_box( 
        'yummy_bites_sidebar_layout',
        __( 'Sidebar Layout', 'yummy-bites' ),
        'yummy_bites_sidebar_layout_callback', 
        'page',
        'normal',
        'high'
    );

    //for page title
    add_meta_box( 
        'yummy_bites_page_title_setting',
        __( 'Page Title Setting', 'yummy-bites' ),
        'yummy_bites_page_title_setting_callback', 
        'page',
        'normal',
        'high'
    );
    
}
endif;
add_action( 'add_meta_boxes', 'yummy_bites_add_sidebar_layout_box' );

if( ! function_exists( 'yummy_bites_get_sidebar_layout_data' ) ){
    function yummy_bites_get_sidebar_layout_data(){
            return array(    
                'default-sidebar'=> array(
                'value'     => 'default-sidebar',
                'label'     => __( 'Default Sidebar', 'yummy-bites' ),
                'thumbnail' => get_template_directory_uri() . '/images/default-sidebar.png'
            ),
            'no-sidebar'     => array(
                'value'     => 'no-sidebar',
                'label'     => __( 'Full Width', 'yummy-bites' ),
                'thumbnail' => get_template_directory_uri() . '/images/full-width.png'
            ),
            'centered'     => array(
                'value'     => 'centered',
                'label'     => __( 'Full Width Centered', 'yummy-bites' ),
                'thumbnail' => get_template_directory_uri() . '/images/full-width-centered.png'
            ),    
            'left-sidebar' => array(
                'value'     => 'left-sidebar',
                'label'     => __( 'Left Sidebar', 'yummy-bites' ),
                'thumbnail' => get_template_directory_uri() . '/images/left-sidebar.png'         
            ),
            'right-sidebar' => array(
                'value'     => 'right-sidebar',
                'label'     => __( 'Right Sidebar', 'yummy-bites' ),
                'thumbnail' => get_template_directory_uri() . '/images/right-sidebar.png'         
            )    
        );
    }
}

if( ! function_exists( 'yummy_bites_sidebar_layout_callback' ) ) :

function yummy_bites_sidebar_layout_callback(){
    global $post;
    $yummy_bites_sidebar_layout = yummy_bites_get_sidebar_layout_data();
    wp_nonce_field( basename( __FILE__ ), 'yummy_bites_nonce' ); ?> 
    <table class="form-table">
        <tr>
            <td colspan="4"><em class="f13"><?php esc_html_e( 'Choose Sidebar Template', 'yummy-bites' ); ?></em></td>
        </tr>
        <tr>
            <td>
                <?php  
                    foreach( $yummy_bites_sidebar_layout as $field ){  
                        $layout = get_post_meta( $post->ID, '_yummy_bites_sidebar_layout', true ); ?>
                        <div class="hide-radio radio-image-wrapper" style="float:left; margin-right:30px;">
                            <input id="<?php echo esc_attr( $field['value'] ); ?>" type="radio" name="yummy_bites_sidebar_layout" value="<?php echo esc_attr( $field['value'] ); ?>" <?php checked( $field['value'], $layout ); if( empty( $layout ) ){ checked( $field['value'], 'default-sidebar' ); }?>/>
                            <label class="description" for="<?php echo esc_attr( $field['value'] ); ?>">
                                <img src="<?php echo esc_url( $field['thumbnail'] ); ?>" alt="<?php echo esc_attr( $field['label'] ); ?>" />
                            </label>
                        </div>
                        <?php 
                    } // end foreach 
                ?>
                <div class="clear"></div>
            </td>
        </tr>
    </table> 
<?php 
}
endif;

if( ! function_exists( 'yummy_bites_save_sidebar_layout' ) ) :

function yummy_bites_save_sidebar_layout( $post_id ){
    $yummy_bites_sidebar_layout = yummy_bites_get_sidebar_layout_data();

    // Verify the nonce before proceeding.
    if( !isset( $_POST[ 'yummy_bites_nonce' ] ) || !wp_verify_nonce( $_POST[ 'yummy_bites_nonce' ], basename( __FILE__ ) ) )
        return;
    
    // Stop WP from clearing custom fields on autosave
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
        return;

    if( 'page' == $_POST['post_type'] ){  
        if( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;  
    }elseif( ! current_user_can( 'edit_post', $post_id ) ){  
        return $post_id;  
    }

    $layout = isset( $_POST['yummy_bites_sidebar_layout'] ) ? sanitize_key( $_POST['yummy_bites_sidebar_layout'] ) : 'default-sidebar';

    if( array_key_exists( $layout, $yummy_bites_sidebar_layout ) ){
        update_post_meta( $post_id, '_yummy_bites_sidebar_layout', $layout );
    }else{
        delete_post_meta( $post_id, '_yummy_bites_sidebar_layout' );
    }
}
endif;
add_action( 'save_post' , 'yummy_bites_save_sidebar_layout' );

/**
 * Metabox for Page Title
 */
function yummy_bites_page_title_setting_callback(){
    global $post;
    wp_nonce_field( basename( __FILE__ ), 'yummy_bites_nonce' ); 
    $value = get_post_meta( $post->ID, '_yummy_page_title', true ); ?>

    <div class="yummy-title-wrapper" style="display:flex; align-items:center;">
        <label><?php esc_html_e( "Remove Page Title", 'yummy-bites' ); ?></label>
        <input type="checkbox" name="yummy_page_title" value="false" <?php checked( $value, 'false' ); ?>  style="margin: 1px 0 0 10px;">
    </div>
    <label><em><?php esc_html_e( "Enable this setting to remove page title and top spacing.", 'yummy-bites' ); ?></em></label>
    <?php
}

function yummy_bites_save_page_title_setting( $post_id ){

    $page_title_value = isset( $_POST['yummy_page_title'] ) ? sanitize_key( $_POST['yummy_page_title'] ) : '';

    update_post_meta( $post_id, '_yummy_page_title', $page_title_value );

}
add_action( 'save_post' , 'yummy_bites_save_page_title_setting' );