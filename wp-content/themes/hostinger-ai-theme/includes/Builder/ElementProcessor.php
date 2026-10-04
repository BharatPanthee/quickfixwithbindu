<?php

namespace Hostinger\AiTheme\Builder;

use DOMDocument;
use DOMXPath;
use Hostinger\AiTheme\Builder\ElementHandlers\BackgroundImageHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\ButtonHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\CoverImageHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\ImageHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\TitleHandler;
use Hostinger\AiTheme\Constants\ElementClassConstant;

defined( 'ABSPATH' ) || exit;

class ElementProcessor {
    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var string
     */
    private array $section;

    /**
     * @var Helper
     */
    private Helper $helper;

    private string $builder_type;

    /**
     * @param array $section
     */
    public function __construct( array $section ) {
        $this->builder_type = get_option( 'hostinger_ai_builder_type', 'gutenberg' );

        $handler_types = array(
            'title' => new TitleHandler( $this->builder_type ),
            'button' => new ButtonHandler( $this->builder_type ),
            'background-image' => new BackgroundImageHandler( $this->builder_type ),
            'image' => new ImageHandler( $this->builder_type ),
            'cover-image' => new CoverImageHandler( $this->builder_type ),
        );

        $handlers_classes = [
            ElementClassConstant::TITLE               => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::SUBTITLE            => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::CTA_BUTTON          => ElementClassConstant::BUTTON_HANDLER,
            ElementClassConstant::PROJECT_TITLE       => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::SERVICE_TITLE       => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::TESTIMONIAL_TEXT    => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::SERVICE_DESCRIPTION => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::PROJECT_DESCRIPTION => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::DESCRIPTION         => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::TESTIMONIAL_IMAGE   => ElementClassConstant::IMAGE_HANDLER,
            ElementClassConstant::IMAGE               => ElementClassConstant::IMAGE_HANDLER,
            ElementClassConstant::SERVICE_IMAGE       => ElementClassConstant::IMAGE_HANDLER,
            ElementClassConstant::PROJECT_IMAGE       => ElementClassConstant::IMAGE_HANDLER,
            ElementClassConstant::BACKGROUND_IMAGE    => ElementClassConstant::BACKGROUND_IMAGE_HANDLER,
            ElementClassConstant::CARD_TITLE          => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::CARD_DESCRIPTION    => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::CARD_PRICE          => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::WORKPLACE           => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::DATE                => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::COVER_IMAGE         => ElementClassConstant::COVER_IMAGE_HANDLER,
            ElementClassConstant::QUESTION            => ElementClassConstant::TITLE_HANDLER,
            ElementClassConstant::ANSWER              => ElementClassConstant::TITLE_HANDLER,
        ];

        foreach($handlers_classes as $handler_class => $handler_type) {
            $this->handlers[$handler_class] = $handler_types[$handler_type];
        }

        $this->section = $section;
    }

    /**
     * @param Helper $helper
     *
     * @return void
     */
    public function setHelper( Helper $helper ): void {
        $this->helper = $helper;
    }

    /**
     * @param DOMDocument $dom
     *
     * @return mixed
     */
    public function process( DOMDocument $dom ): string {
        $xpath = new DOMXPath($dom);
        $text_nodes = $xpath->query('//*[contains(@class,"' . ElementClassConstant::PREFIX . '")]');

        foreach ($text_nodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $classes = $node->getAttribute('class');

                if (empty($classes)) {
                    continue;
                }

                preg_match_all(ElementClassConstant::PATTERN, $classes, $matches);
                $ai_elements = $matches[0];

                $index = $this->helper->extract_index_number($classes);

                foreach ($ai_elements as $ai_element) {
                    if (isset($this->handlers[$ai_element])) {
                        $element_data = [
                            'class' => $ai_element,
                            'index' => $index
                        ];

                        $element_structure = $this->helper->find_structure($this->section['elements'], $element_data);

                        if (!empty($element_structure)) {
                            $this->handlers[$ai_element]->handle_gutenberg($node, $element_structure);
                        }
                    }
                }
            }
        }

        $html = $dom->saveHTML();

        $html = preg_replace('/<\/html>$/', '', $html);
        $html = preg_replace('/<\/body>$/', '', $html);

        return $html;
    }

    public function prepare_json(): array {
        $json_data = json_decode( $this->section['html'] ?? '', true );

        $section_type = $this->section['type'] ?? $this->section['section'] ?? '';

        foreach ($this->handlers as $handler) {
            $handler->set_section_context($section_type, 0);
        }

        $processed_data = $this->traverse_elementor_data($json_data, function ($element) use ($section_type) {
            $css_classes = $this->get_element_css_classes( $element );

            if (!empty($css_classes)) {
                $ai_elements = $this->helper->extract_class_names($css_classes, ElementClassConstant::PATTERN);

                if(!empty($ai_elements)) {
                    $element_index = $this->helper->extract_index_number( $css_classes );

                    foreach ( $ai_elements as $ai_element ) {
                        $element_data = [
                            'class' => $ai_element,
                            'index' => $element_index,
                        ];

                        $element_structure = $this->helper->find_structure($this->section['elements'], $element_data);
                        if (!empty($element_structure)) {
                            $this->handlers[$ai_element]->set_section_context($section_type, $element_index);
                            $this->handlers[$ai_element]->handle_elementor($element, $element_structure);
                        }
                    }
                }
            }

            return $element;
        });

        if (str_starts_with($section_type, 'product-categories') || str_starts_with($section_type, 'hero-for-online-store') || str_starts_with($section_type, 'product-list')) {
            $processed_data = $this->process_product_buttons($processed_data, $section_type);
        }

        return $processed_data;
    }

    private function traverse_elementor_data(array $data, callable $callback): array {
        foreach ($data as $key => $element) {
            if (is_array($element) && isset($element['elType'])) {
                $data[$key] = $callback($element);

                if (isset($data[$key]['elements']) && is_array($data[$key]['elements'])) {
                    $data[$key]['elements'] = $this->traverse_elementor_data($data[$key]['elements'], $callback);
                }
            } elseif (is_array($element)) {
                $data[$key] = $this->traverse_elementor_data($element, $callback);
            }
        }

        return $data;
    }

    private function process_product_buttons( array $data, string $section_type ): array {
        foreach ( $data as &$element ) {
            if ( ! is_array( $element ) || ! isset( $element['elType'] ) ) {
                continue;
            }

            $this->update_button_links( $element, $section_type );

            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $element['elements'] = $this->process_product_buttons( $element['elements'], $section_type );
            }
        }

        return $data;
    }

    private function update_button_links( array &$element, string $section_type ): void {
        $css_classes = $this->get_element_css_classes( $element );

        if ( ! str_contains( $css_classes, ElementClassConstant::CTA_BUTTON ) ) {
            return;
        }

        $product_link = str_starts_with( $section_type, 'product-list' )
            ? $this->get_shop_link()
            : $this->get_random_product_link();

        if ( empty( $product_link ) ) {
            return;
        }

        $element['settings']['link'] = $element['settings']['link'] ?? [];
        $element['settings']['link']['url'] = $product_link;
    }

    private function get_random_product_link(): string {
        $created_product_ids = get_option( 'hostinger_ai_created_products', [] );

        if ( empty( $created_product_ids ) ) {
            return '';
        }

        $random_product_id = $created_product_ids[ array_rand( $created_product_ids ) ];
        $product_link      = get_permalink( $random_product_id );

        return $product_link ?: '';
    }

    private function get_shop_link(): string {
        $shop_page_id = function_exists( 'wc_get_page_id' )
            ? wc_get_page_id( 'shop' )
            : get_option( 'woocommerce_shop_page_id' );

        if ( ! $shop_page_id || $shop_page_id < 0 ) {
            $shop_page_key = get_option( 'hostinger_ai_woo_shop_page_key', 'shop' );
            $shop_page_slug = sanitize_title( $shop_page_key );
            $shop_page = get_page_by_path( $shop_page_slug );
            if ( $shop_page ) {
                $shop_page_id = $shop_page->ID;
            }
        }

        return $shop_page_id > 0 ? ( get_permalink( $shop_page_id ) ?: '' ) : '';
    }

    private function get_element_css_classes( array $element ): string {
        $css_classes = $element['settings']['css_classes'] ?? '';
        if ( empty( $css_classes ) ) {
            $css_classes = $element['settings']['_css_classes'] ?? '';
        }

        return $css_classes;
    }
}
