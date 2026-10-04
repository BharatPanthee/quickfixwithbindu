<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Data\SectionData;

defined( 'ABSPATH' ) || exit;

class Structure {
    private const EXCLUDED_FROM_MENU_SECTION_TYPES = array(
        'terms-and-conditions',
        'privacy-policy',
        'cookie-policy',
    );

    /**
     * @var string
     */
    private string $brand_name;
    /**
     * @var string
     */
    private string $website_type;
    /**
     * @var string
     */
    private string $description;
    /**
     * @var RequestClient
     */
    private RequestClient $request_client;
    /**
     * @var string|null
     */
    private ?string $builder_type = null;

    /**
     * @param string $brand_name
     * @param string $website_type
     * @param string $description
     * @param string|null $builder_type Specifies which builder type to use (gutenberg or elementor), overriding the default option value if provided
     */
    public function __construct( string $brand_name, string $website_type, string $description, ?string $builder_type = null ) {
        $this->brand_name   = $brand_name;
        $this->website_type = $website_type;
        $this->description  = $description;
        $this->builder_type = $builder_type;
    }

    /**
     * @param RequestClient $request_client
     *
     * @return void
     */
    public function set_request_client( RequestClient $request_client ): void {
        $this->request_client = $request_client;
    }

    public function generate_structure( string $website_type ): array {
        $params = array(
            'brand_name'   => $this->brand_name,
            'website_type' => $this->website_type,
            'description'  => $this->description,
            'language'     => $this->get_site_locale(),
            'sections'     => SectionData::get_sections_for_website_type( $website_type ),
        );

        if ( $website_type === 'booking' ) {
            $params['website_type'] = 'business';
        }

        $structure = $this->request_client->post( '/v3/wordpress/plugin/builder/structure', $params );

        return $this->generate_unique_identifiers( $structure );
    }

    /**
     * @param array $page_data
     *
     * @return array
     */
    public function generate_content( array $page_data ): array {
        $params = array(
            'brand_name'        => $this->brand_name,
            'website_type'      => $this->website_type,
            'description'       => $this->description,
            'image_with_prompt' => false,
            'language'          => $this->get_site_locale(),
            'pages'             => $this->format_page_data( $page_data ),
        );

        if ( $this->website_type === 'booking' ) {
            $params['website_type'] = 'business';
        }

        return $this->request_client->post( '/v3/wordpress/plugin/builder/ai-builder-v3', $params );
    }

    /**
     * @param $structure
     *
     * @return array
     * @throws \Exception
     */
    public function generate_builder_data( $structure ): array {
        foreach ( $structure as &$data ) {
            foreach ( $data['sections'] as &$section_data ) {
                $section_builder = new SectionBuilder( $section_data['section'], $this->builder_type );
                $section_builder->setHelper( new Helper() );

                $generate = $section_builder->generate();
                if ( ! empty( $generate ) ) {
                    $section_data['content']   = $section_builder->get_block_content();
                    $section_data['structure'] = $section_builder->get_block_used_elements();
                }
            }
        }

        $structure = $this->append_products_data( $structure );

        return $this->append_seo_data( $structure );
    }

    /**
     * @param array $structure
     * @param array $content
     *
     * @return array
     */
    public function merge_content( array $structure, array $content ): array {
        foreach ( $content['pages'] as $page => &$content_data ) {
            foreach ( $content_data['sections'] as $section_index => &$section_data ) {
                $section_data['html'] = $this->find_section_content( $page, $structure, $section_index );
            }

            $content_data['exclude_from_menu'] = $this->should_exclude_page_from_menu( $page, $structure );
        }

        return $content;
    }

    private function should_exclude_page_from_menu( string $page, array $structure ): bool {
        foreach ( $structure as $structure_data ) {
            if ( $structure_data['page'] === $page ) {
                return ! empty( $structure_data['exclude_from_menu'] );
            }
        }

        return false;
    }

    /**
     * @param string $page
     * @param array  $structure
     * @param string $section_index
     *
     * @return string
     */
    private function find_section_content( string $page, array $structure, string $section_index ): string {

        foreach ( $structure as $structure_data ) {
            if ( $structure_data['page'] === $page ) {
                foreach ( $structure_data['sections'] as $section_data ) {
                    if ( $section_data['id'] === $section_index ) {
                        return ! empty( $section_data['content'] ) ? $section_data['content'] : '';
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param array $page_data
     *
     * @return array
     */
    private function format_page_data( array $page_data ): array {
        $formatted_data = array();

        foreach ( $page_data as $data ) {
            $sections = array();

            foreach ( $data['sections'] as $section_data ) {

                if ( empty( $section_data['structure'] ) ) {
                    continue;
                }

                $sections[ $section_data['id'] ] = array(
                    'type'     => $section_data['section'],
                    'elements' => $section_data['structure'],
                );
            }

            if ( ! empty( $sections ) ) {
                $formatted_data[ $data['page'] ] = array(
                    'sections' => $sections,
                );
            }
        }

        return $formatted_data;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    private function generate_unique_identifiers( array $structure ): array {
        $result = array();

        if ( get_option( 'hostinger_ai_woo', false ) ) {
            foreach ( $structure as $page => $sections ) {
                if ( strtolower( $page ) === 'shop' ) {
                    update_option( 'hostinger_ai_woo_shop_page_key', $page );
                    break;
                }
            }
        }

        foreach ( $structure as $page => $sections ) {
            $exclude_from_menu = false;

            foreach ( $sections as $section ) {
                if ( in_array( $section, self::EXCLUDED_FROM_MENU_SECTION_TYPES, true ) ) {
                    $exclude_from_menu = true;
                    break;
                }
            }

            $page_data = array(
                'page'              => $page,
                'sections'          => array(),
                'exclude_from_menu' => $exclude_from_menu,
            );

            foreach ( $sections as $section ) {
                $page_data['sections'][] = array(
                    'id'      => uniqid(),
                    'section' => $section,
                );
            }

            $result[] = $page_data;
        }

        return $result;
    }

    private function get_site_locale(): string {
        $wp_locale = get_option( 'hostinger_ai_selected_language', 'en_US' );

        return Helper::get_locale_name( $wp_locale );
    }

    private function append_seo_data( array $structure ): array {
        foreach ( $structure as &$page ) {

            if ( ! isset( $page['sections'] ) ) {
                $page['sections'] = array();
            }

            $page['sections'][] = array(
                'id'        => uniqid(),
                'section'   => 'seo',
                'structure' => array(
                    'seo_title' . uniqid()       => array(
                        'type'      => 'SEO Title',
                        'max_words' => 10,
                    ),
                    'seo_keywords' . uniqid()    => array(
                        'type'      => 'Comma separated list of 3 SEO keywords',
                        'max_words' => 10,
                    ),
                    'seo_description' . uniqid() => array(
                        'type'      => 'SEO Description',
                        'max_words' => 50,
                    ),
                ),
            );
        }

        return $structure;
    }

    private function append_products_data( array $structure ): array {
        if ( ! get_option( 'hostinger_ai_woo', false ) ) {
            return $structure;
        }

        $ecommerce_page = array(
            'page'     => 'ecommercePagesGroup',
            'sections' => array(),
        );

        for ( $i = 0; $i < 5; $i++ ) {
            $ecommerce_page['sections'][] = array(
                'id'        => uniqid(),
                'section'   => 'product',
                'structure' => array(
                    'ecommerce_product_image-' . uniqid()       => array(
                        'type'      => 'Image',
                        'max_words' => '5',
                    ),
                    'ecommerce_product_title-' . uniqid()       => array(
                        'type'      => 'Title',
                        'max_words' => '5',
                    ),
                    'ecommerce_product_description-' . uniqid() => array(
                        'type'      => 'Description',
                        'max_words' => '80',
                    ),
                    'ecommerce_product_price-' . uniqid()       => array(
                        'type'      => 'Price number',
                        'max_words' => '1',
                    ),
                ),
            );
        }

        $structure[] = $ecommerce_page;

        return $structure;
    }

    public function generate_page_structure( string $website_type, string $page_name ): array {
        $params = array(
            'brand_name'   => $this->brand_name,
            'website_type' => $website_type,
            'description'  => $this->description,
            'language'     => $this->get_site_locale(),
            'sections'     => SectionData::get_sections_for_website_type( $website_type ),
            'pages'        => array( $page_name ),
        );

        if ( $website_type === 'booking' ) {
            $params['website_type'] = 'business';
        }

        $structure = $this->request_client->post( '/v3/wordpress/plugin/builder/structure', $params );

        if ( empty( $structure ) ) {
            return array();
        }

        $page_structure = array();

        if ( isset( $structure[ $page_name ] ) ) {
            $page_structure = array( $page_name => $structure[ $page_name ] );
        } else {
            $random_key     = array_rand( $structure );
            $page_structure = array( $random_key => $structure[ $random_key ] );
        }

        return $this->generate_unique_identifiers( $page_structure );
    }
}
