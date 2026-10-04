<?php
/**
 * Customizer Control: visibility control
 *
 * @package Yummy_Bites
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Yummy_Bites_Customizer_Visibility_Control' ) ) {
	/**
	 * Visibility control.
    */
	class Yummy_Bites_Customizer_Visibility_Control extends Wp_Customize_Control {

        public $type = "yummy-bites-visibility";

        public function enqueue() {
            wp_enqueue_script( 'yummy-bites-visibility', get_template_directory_uri() . '/inc/custom-controls/visibility/visibility.js', array( 'jquery' ), YUMMY_BITES_THEME_VERSION, true );
		    wp_enqueue_style( 'yummy-bites-visibility', get_template_directory_uri() . '/inc/custom-controls/visibility/visibility.css' );
		}

        /**
         * Displays the control content.
         *
         * @since  1.0.0
         * @return void
         */
        public function render_content() {

    
            if ( empty( $this->choices ) ) return ?>

            <?php if ( !empty( $this->label ) ) : ?>
			    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		    <?php endif; ?>

            <?php if ( !empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <?php $multi_values = !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value(); ?>
            <ul>
                <?php foreach ( $this->choices as $value => $label ) : ?>
                    <li>
                        <label>
                            <input type="checkbox" value="<?php echo esc_attr($value); ?>" <?php checked(in_array( $value, $multi_values)); ?> />
                            <span class="yn-icon">
                                <span><?php echo esc_html($label); ?></span>
                                <?php echo $this->get_svg_icon($value); ?>
                            </span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr(implode(',', $multi_values)); ?>" />
            <?php
        }

         // Handle SVG based on the input value (desktop, tablet, mobile)
         protected function get_svg_icon($value) {
            switch ($value) {
                case 'desktop':
                    return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 3H4C2.89543 3 2 3.89543 2 5V15C2 16.1046 2.89543 17 4 17H20C21.1046 17 22 16.1046 22 15V5C22 3.89543 21.1046 3 20 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 21H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
                case 'tablet':
                    return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V4C20 2.89543 19.1046 2 18 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
                case 'mobile':
                    return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 2H7C5.89543 2 5 2.89543 5 4V20C5 21.1046 5.89543 22 7 22H17C18.1046 22 19 21.1046 19 20V4C19 2.89543 18.1046 2 17 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
                default:
                    return '';
            }
        }
    }

}