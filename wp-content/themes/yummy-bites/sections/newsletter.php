<?php
/**
* Newsletter Section
* 
* @package Yummy_Bites
*/
if( is_active_sidebar( 'newsletter' ) ){ ?>
    <section id="newsletter_section" class="newsletter-section section">
        <?php dynamic_sidebar( 'newsletter' ); ?>
    </section> <!-- .newsletter-section -->
    <?php
}

