<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use DOMElement;
use WP_Query;

defined( 'ABSPATH' ) || exit;

class ButtonHandler extends BaseElementHandler {
    public function handle_gutenberg(DOMElement &$node, array $element_structure): void {
        $links = $node->getElementsByTagName('a');

        if ($links->length > 0) {
            $link = $links->item(0);
            $link->nodeValue = $element_structure['content'];
			$link->setAttribute('href', $this->get_random_link() );
        }
    }

    public function handle_elementor(array &$element, array $element_structure): void {
        if(empty($element['widgetType'])) {
            return;
        }

        if($element['widgetType'] !== 'button') {
            return;
        }

        $button_link = $this->determine_button_link();

        $element['settings']['text'] = $element_structure['content'];
        $element['settings']['link']['url'] = $button_link;
    }

    private function determine_button_link(): string {
        if (str_starts_with($this->section_type, 'hero-for-online-store')) {
            if ($this->element_index === 1) {
                return $this->get_shop_link();
            } else {
                return $this->get_random_product_link();
            }
        }

        if (str_starts_with($this->section_type, 'product-list')) {
            return $this->get_shop_link();
        }

        if (str_starts_with($this->section_type, 'product-categories')) {
            return $this->get_random_product_link();
        }

        return $this->get_random_link();
    }

    private function get_shop_link(): string {
        $shop_page_id = function_exists('wc_get_page_id')
            ? wc_get_page_id('shop')
            : get_option('woocommerce_shop_page_id');

        if (!$shop_page_id || $shop_page_id < 0) {
            $shop_page_key = get_option('hostinger_ai_woo_shop_page_key', 'shop');
            $shop_page_slug = sanitize_title($shop_page_key);
            $shop_page = get_page_by_path($shop_page_slug);
            if ($shop_page) {
                $shop_page_id = $shop_page->ID;
            }
        }

        if ($shop_page_id > 0) {
            $permalink = get_permalink($shop_page_id);
            return $permalink ?: site_url();
        }

        return site_url();
    }

    private function get_random_product_link(): string {
        $created_product_ids = get_option('hostinger_ai_created_products', []);

        if (empty($created_product_ids)) {
            return $this->get_random_link( [ 'product', 'page' ] );
        }

        $random_product_id = $created_product_ids[array_rand($created_product_ids)];
        $product_link = get_permalink($random_product_id);

        return $product_link ?: site_url();
    }

	private function get_random_link( $post_type = ['post', 'page', 'product'] ): string {
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'rand',
		);

		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$query->the_post();
			$permalink = get_permalink();
			wp_reset_postdata();
			return $permalink;
		}

		return site_url();
	}
}
