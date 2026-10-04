<?php
/**
 * About Section
 */
$defaults                = yummy_bites_get_general_defaults();
$ed_about_section        = get_theme_mod( 'ed_about_section', true);
$abt_title               = get_theme_mod( 'abt_title', $defaults['abt_description'] );
$abt_description         = get_theme_mod( 'abt_description',$defaults['abt_description'] );
$ed_about_social_links   = get_theme_mod( 'ed_about_social_links',$defaults['ed_about_social_links'] );
$social_media_order      = get_theme_mod( 'about_social_media_order', $defaults['about_social_media_order']  );
$ed_social_media_newtab  = get_theme_mod( 'ed_about_social_links_new_tab', $defaults['ed_about_social_links_new_tab'] );
$abt_author_image        = get_theme_mod( 'abt_author_image', $defaults['abt_author_image']);
$abt_bg_image            = get_theme_mod( 'abt_bg_image', $defaults['abt_bg_image']);
$abt_button_label        = get_theme_mod( 'abt_button_label',$defaults['abt_button_label'] );
$abt_button_link         = get_theme_mod( 'abt_button_link',$defaults['abt_button_link'] );
$about_section_alignment = get_theme_mod( 'about_section_alignment', 'right');
$abt_img_id              = attachment_url_to_postid( $abt_author_image );

if( $ed_about_section && ( $abt_title || $abt_description ) ){ ?>
    <section id="about_section" class="tr-about-section" >        
        <div class="container">
            <div class="tr-featured-holder <?php echo esc_attr( $about_section_alignment ); ?> has-featured-image">
                <div class="abt-grid-item">
                    <div class="text-holder">
                        <?php if( $abt_title ){
                            echo '<h2 class="section-title">' . esc_html( $abt_title ) . '</h2>';
                        } 
                        if( $abt_description ){
                            echo '<div class="section-subtitle"><span>' . wp_kses_post( wpautop( $abt_description) ). '</span></div>';
                        } 
                        if( $ed_about_social_links ){ ?>
                            <div class="header-social-wrapper">
                                <div class="header-social">
                                    <?php
                                        $social_icons = new Yummy_Bites_Social_Lists;
                                        $social_icons->yummy_bites_social_links( $ed_about_social_links, $ed_social_media_newtab, $social_media_order );
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if( $abt_button_label && $abt_button_link ) { ?>
                        <div class="btn-wrapper">
                            <a href="<?php echo esc_url( $abt_button_link ); ?>" class="btn-primary">
                                <?php if( $abt_button_label ) echo esc_html( $abt_button_label ); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <?php if( $abt_author_image ) { ?>
                    <div class="abt-grid-item-1">
                        <div class="img-holder">
                            <?php echo wp_get_attachment_image( $abt_img_id, 'full' ); ?>
                        </div>
                    </div>
                <?php } ?>  
            </div>
        </div>
    </section>
<?php } ?>