<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\AiTheme\Constants\ElementClassConstant;
use Hostinger\WpHelper\Utils;
use Hostinger\WpHelper\Utils as Helper;
use DOMElement;
use DOMDocument;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Requests\Client;

defined( 'ABSPATH' ) || exit;

class CoverImageHandler extends BaseElementHandler {
    private Utils $helper;
    private Client $client;

    public function __construct(string $builder_type) {
        parent::__construct($builder_type);

        $this->helper         = new Helper();
        $config_handler = new Config();
        $this->client         = new Client( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $this->helper::getApiToken(),
            Config::DOMAIN_HEADER => $this->helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );
    }

    public function handle_gutenberg(DOMElement &$node, array $element_structure): void {
        if ( empty( $element_structure['content'] ) ) {
            return;
        }

        $previousElement = $node->previousSibling->previousSibling;

        $value = str_replace(' wp:cover ', '', $previousElement->nodeValue);
        $block = json_decode($value, true);

        $image_manager = new ImageManager( $element_structure['content'] );
        $image_data = $image_manager->get_unsplash_image_data();

        $images = $node->getElementsByTagName('img');

        if ( property_exists( $image_data, 'image' ) && $images->length > 0 ) {
            $image_url = $image_manager->modify_image_url( $image_data->image, $element_structure );
			$alt_description = $image_data->alt_description ?? $element_structure['content'];

            if ( ! empty($block['className'])
                 && str_contains(
                     $block['className'],
                     ElementClassConstant::COVER_IMAGE
                 )) {
                $block['url'] = $image_url;
            }

            $img = $images->item(0);
            $img->setAttribute('src', $image_url);
            $img->setAttribute('alt', $alt_description);

            $previousElement->nodeValue = ' wp:cover ' . json_encode( $block ) .' ';
        }
    }

    public function handle_elementor(array &$element, array $element_structure): void {
        $css_classes = $element['settings']['css_classes'] ?? '';
        if ( ! str_contains( $css_classes, ElementClassConstant::COVER_IMAGE ) ) {
            return;
        }

        $content = $element_structure['content'] ?? '';
        if (empty($content)) {
            return;
        }

        $website_description = get_option('hostinger_ai_description', '');
        $image_context = 'Image for ' . strip_tags($content);

        if (!empty($website_description)) {
            $image_context .= '. Website description: ' . strip_tags($website_description);
        }

        $image_manager = new ImageManager($image_context);
        $image_data = $image_manager->get_unsplash_image_data(!empty($image_context));

        if (empty($image_data) || !property_exists($image_data, 'image')) {
            return;
        }

        $image_url = $image_manager->modify_image_url($image_data->image, $element_structure);

        $element['settings']['background_background'] = 'classic';
        $element['settings']['background_image'] = [
            'url' => $image_url,
            'id' => '',
            'source' => 'url'
        ];
    }
}
