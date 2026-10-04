<?php
/**
 * Group title
 *
 * @package Yummy Bites
 */

/**
 * Implement group title
 */
if ( ! class_exists( 'Yummy_Bites_Group_Title' ) ) :

class Yummy_Bites_Group_Title extends WP_Customize_Section {
	/**
	 * Type of this section.
	 *
	 * @var string
	 */
	public $type = 'yummy-bites-group-title';

	/**
	 * Special categorization for the section.
	 *
	 * @var string
	 */
	public $kind = 'default';
	

	/**
	 * Output
	 */
	public function render() {
		$description = $this->description;
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="accordion-section yummy-bites-group-title">
			<h3><?php echo esc_html( $this->title ); ?></h3>
			<?php if ( ! empty( $description ) ) { ?>
				<span class="description"><?php echo esc_html( $description ); ?></span>
			<?php } ?>
		</li>
		<?php
	}
}

endif;