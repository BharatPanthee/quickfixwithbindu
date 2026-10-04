<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Yummy_Bites_Alpha_Color_Customize_Control' ) ) :
class Yummy_Bites_Alpha_Color_Customize_Control extends WP_Customize_Control {
	/**
	 * Official control name.
	 */
	public $type = 'yummy-bites-color-alpha';
	/**
	 * Add support for palettes to be passed in.
	 *
	 * Supported palette values are true, false, or an array of RGBa and Hex colors.
	 */
	public $palette;
	/**
	 * Add support for showing the opacity value on the slider handle.
	 */
	public $show_opacity;
	/**
	 * Enqueue scripts and styles.
	 *
	 * Ideally these would get registered and given proper paths before this control object
	 * gets initialized, then we could simply enqueue them here, but for completeness as a
	 * stand alone class we'll register and enqueue them here.
	 */
	
	public function enqueue() {
		wp_enqueue_script(
			'yummy-bites-color-alpha-picker',
			trailingslashit( get_template_directory_uri() ) . 'inc/custom-controls/coloralpha/color-alpha-picker.js',
			array( 'jquery', 'wp-color-picker' ),
			YUMMY_BITES_THEME_VERSION,
			true
		);
		wp_enqueue_style(
			'yummy-bites-color-alpha-picker',
			trailingslashit( get_template_directory_uri() ) . 'inc/custom-controls/coloralpha/color-alpha-picker.css',
			array( 'wp-color-picker' ),
			YUMMY_BITES_THEME_VERSION
		);

		wp_localize_script(
			'yummy-bites-color-alpha-picker',
			'yummy_bites_color_presets',
			array(
				'preset_one'   => yummy_bites_color_preset_array('one'),
				'preset_two'   => yummy_bites_color_preset_array('two'),
				'preset_three' => yummy_bites_color_preset_array('three'),
				'preset_four'  => yummy_bites_color_preset_array('four'),
				'preset_five'  => yummy_bites_color_preset_array('five'),
				'preset_six'   => yummy_bites_color_preset_array('six'),
				'preset_seven' => yummy_bites_color_preset_array('seven'),
				'preset_eight' => yummy_bites_color_preset_array('eight'),
				'preset_nine'  => yummy_bites_color_preset_array('nine'),
				'default'      => yummy_bites_color_preset_array('default'),
				'proActive'    => yummy_bites_pro_is_activated(),
				'extensions'   => get_option( 'ybp_active_extensions', array() ),
			)
		);
	}

	public function to_json() {
		parent::to_json();
		$this->json['palette']      = $this->palette;
		$this->json['defaultValue'] = $this->setting->default;
		$this->json['link']         = $this->get_link();
		$this->json['show_opacity'] = $this->show_opacity;

		if ( is_array( $this->json['palette'] ) ) {
			$this->json['palette'] = implode( ',', $this->json['palette'] );
		} else {
			// Default to true.
			$this->json['palette'] = ( false === $this->json['palette'] || 'false' === $this->json['palette'] ) ? 'false' : 'true';
		}

		// Support passing show_opacity as string or boolean. Default to true.
		$this->json[ 'show_opacity' ] = ( false === $this->json[ 'show_opacity' ] || 'false' === $this->json[ 'show_opacity' ] ) ? 'false' : 'true';
	}

	/**
	 * Render the control.
	 */
	public function render_content() {}

	public function content_template() {
		?>
		<div class="yummy-bites-alpha-color">
			<# if ( data.label && '' !== data.label ) { #>
				<span class="customize-control-title">{{ data.label }}</span>
			<# } #>
			<input class="yummy-bites-color-alpha-control" type="text" data-palette="{{{ data.palette }}}" data-default-color="{{ data.defaultValue }}" {{{ data.link }}} />
		</div>
		<?php
	}
}
endif;