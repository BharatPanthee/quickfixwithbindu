<?php
/**
 * Builder Rest API
 *
 */

namespace Hostinger\AiTheme\Rest;

use Hostinger\AiTheme\Builder\WebsiteBuilder;
use Hostinger\AiTheme\Builder\RequestClient;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Utils;
use Hostinger\Amplitude\AmplitudeManager;
use Exception;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Settings Rest API
 */
class BuilderRoutes {
    private const AMPLITUDE_EVENT_CREATE = 'wordpress.ai_builder.create';
    private const AMPLITUDE_EVENT_CREATED = 'wordpress.ai_builder.created';

    /**
     * @var WebsiteBuilder
     */
    private WebsiteBuilder $website_builder;

    /**
     * @var RequestClient
     */
    private RequestClient $request_client;

    /**
     * @var RequestClient
     */
    private RequestClient $wh_api_client;

    /**
     * @var AmplitudeManager
     */
    private AmplitudeManager $amplitude_manager;

    /**
     * @param WebsiteBuilder $website_builder
     */
    public function __construct( WebsiteBuilder $website_builder ) {
        $this->website_builder = $website_builder;

        $helper = new Utils();
        $config_handler = new Config();
        $default_headers = [
            Config::TOKEN_HEADER  => $helper::getApiToken(),
            Config::DOMAIN_HEADER => $helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ];

        $amplitude_headers = array_diff_key( $default_headers, array( 'Content-Type' => '' ) );

        $client = new Client(
            $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ),
            $default_headers
        );
        $this->request_client = new RequestClient( $client );

        $wh_api_client = new Client(
            $config_handler->getConfigValue( 'base_proxy_rest_uri', HOSTINGER_WP_PROXY_API_URI ),
            $default_headers
        );
        $this->wh_api_client = new RequestClient( $wh_api_client );

        $amplitude_client = new Client(
            $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ),
            $amplitude_headers
        );
        $this->amplitude_manager = new AmplitudeManager( $helper, $config_handler, $amplitude_client );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_colors( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();

        $validation_error = $this->validate_required_fields( $parameters );
        if ( $validation_error ) {
            return $validation_error;
        }

        $detection_error = $this->ensure_brand_and_type( $parameters );
        if ( $detection_error ) {
            return $detection_error;
        }

        $this->sanitize_location( $parameters );
        $this->clear_ai_data( $parameters['website_type'] );
        $this->handle_affiliate_type( $parameters );
        $this->handle_online_store_options( $parameters );
        $this->save_website_options( $parameters );

        $this->amplitude_manager->sendRequest( Endpoints::AMPLITUDE_ENDPOINT, array(
            'action'       => self::AMPLITUDE_EVENT_CREATE,
            'builder_type' => $parameters['builder_type'],
            'website_type' => $parameters['website_type'],
        ) );

        $data = array(
            'colors_generated' => $this->website_builder->generate_colors( $parameters['description'] )
        );

        $response = new \WP_REST_Response( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
        $response->set_status( \WP_Http::OK );

        return $response;
    }

    private function validate_required_fields( array &$parameters ): ?\WP_Error {
        $required_fields = array(
            'description',
            'language',
            'builder_type',
        );

        $errors = array();

        foreach ( $required_fields as $field_key ) {
            if ( empty( $parameters[ $field_key ] ) ) {
                $errors[ $field_key ] = $field_key . ' is missing.';
            } else {
                $parameters[ $field_key ] = sanitize_text_field( $parameters[ $field_key ] );
            }
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, something wrong with data.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        return null;
    }

    private function ensure_brand_and_type( array &$parameters ): ?\WP_Error {
        $needs_detection = empty( $parameters['brand_name'] ) || empty( $parameters['website_type'] );

        if ( ! $needs_detection ) {
            $parameters['brand_name'] = sanitize_text_field( $parameters['brand_name'] );
            $parameters['website_type'] = sanitize_text_field( $parameters['website_type'] );
            return null;
        }

        $original_website_type = ! empty( $parameters['website_type'] ) ? sanitize_text_field( $parameters['website_type'] ) : null;

        $detection_error = $this->perform_brand_and_type_detection( $parameters );

        if ( $detection_error ) {
            return $detection_error;
        }

        // If website type was provided by frontend, we need to use it instead of the one detected.
        if ( $original_website_type !== null ) {
            $parameters['website_type'] = $original_website_type;
        }

        return null;
    }

    private function perform_brand_and_type_detection( array &$parameters ): ?\WP_Error {
        try {
            $builder_type = $parameters['builder_type'] ?? '';
            $is_builder_website_types = in_array( $builder_type, array( 'elementor', 'gutenberg' ), true );

            $detection_data = $this->call_detect_brand_and_type_service( $parameters['description'], $is_builder_website_types );

            if ( ! $this->is_valid_detection_data( $detection_data ) ) {
                return new \WP_Error(
                    'ai_service_error',
                    __( 'Failed to detect brand name and website type.', 'hostinger-ai-theme' ),
                    array(
                        'status' => \WP_Http::SERVICE_UNAVAILABLE,
                    )
                );
            }

            $parameters['brand_name'] = sanitize_text_field( $detection_data['brandName'] );
            $parameters['website_type'] = strtolower( sanitize_text_field( $detection_data['websiteType'] ) );

            return null;
        } catch ( Exception $e ) {
            return new \WP_Error(
                'ai_service_error',
                $e->getMessage(),
                array(
                    'status' => \WP_Http::SERVICE_UNAVAILABLE,
                )
            );
        }
    }

    private function is_valid_detection_data( array $detection_data ): bool {
        if ( empty( $detection_data ) ) {
            return false;
        }

        return isset( $detection_data['brandName'] ) && isset( $detection_data['websiteType'] );
    }

    private function sanitize_location( array &$parameters ): void {
        if ( ! empty( $parameters['location'] ) ) {
            $parameters['location'] = sanitize_text_field( $parameters['location'] );
        }
    }

    private function handle_affiliate_type( array &$parameters ): void {
        $is_affiliate = $parameters['website_type'] === 'affiliate-marketing';

        if ( ! $is_affiliate ) {
            return;
        }

        $parameters['website_type'] = 'blog';
        update_option( 'hostinger_ai_affiliate', true );
    }

    private function clear_ai_data( string $website_type ): void {
        $this->website_builder->clear_ai_content();
        $this->website_builder->clear_ai_data( $website_type );
    }

    private function handle_online_store_options( array $parameters ): void {
        $is_online_store = $parameters['website_type'] === 'online store';

        if ( ! $is_online_store ) {
            return;
        }

        update_option( 'hostinger_ai_woo', true );
        update_option( 'hostinger_ai_woo_location', $parameters['location'] ?? '' );
    }

    private function save_website_options( array $parameters ): void {
        update_option( 'blogname', $parameters['brand_name'] );
        update_option( 'hostinger_ai_brand_name', $parameters['brand_name'] );
        update_option( 'hostinger_ai_website_type', $parameters['website_type'] );
        update_option( 'hostinger_ai_description', $parameters['description'] );
        update_option( 'hostinger_ai_builder_type', $parameters['builder_type'] );
        update_option( 'hostinger_ai_selected_language', $parameters['language'] );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_structure( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $colors = get_option( 'hostinger_ai_colors', false );

        if ( empty( $colors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }

        $brand_name = get_option('hostinger_ai_brand_name' );
        $website_type = get_option('hostinger_ai_website_type' );
        $description = get_option('hostinger_ai_description' );

        $data = array(
            'structure_generated' => $this->website_builder->generate_structure( $brand_name, $website_type, $description )
        );

        $response = new \WP_REST_Response( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_content( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $website_structure = get_option( 'hostinger_ai_website_structure', false );

        if ( empty( $website_structure ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }
        $headers = $request->get_headers();

        if ( ! empty( $headers['x_correlation_id'] ) ) {
            update_option( 'hts_correlation_id', $headers['x_correlation_id'][0] );
        }

        $brand_name = get_option('hostinger_ai_brand_name' );
        $website_type = get_option('hostinger_ai_website_type' );
        $description = get_option('hostinger_ai_description' );

        try {

            $data = array(
                'content_generated' => $this->website_builder->generate_content( $brand_name, $website_type, $description )
            );

        } catch (Exception $e) {
            return new \WP_Error(
                'data_invalid',
                __( 'Problem generating content.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'error' => $e->getMessage()
                )
            );
        }

        $response = new \WP_REST_Response( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function build_content( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $website_content = get_option( 'hostinger_ai_website_content', false );

        if ( empty( $website_content ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }

        try {

            $data = array(
                'content_built' => $this->website_builder->build_content( $website_content )
            );

        } catch (Exception $e) {
            return new \WP_Error(
                'data_invalid',
                __( 'Problem building content.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'error' => $e->getMessage()
                )
            );
        }

        // Purge LiteSpeed cache.
        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        delete_option( 'rewrite_rules' );

        $this->amplitude_manager->sendRequest( Endpoints::AMPLITUDE_ENDPOINT, array(
            'action'       => self::AMPLITUDE_EVENT_CREATED,
            'builder_type' => get_option( 'hostinger_ai_builder_type', '' ),
            'website_type' => get_option( 'hostinger_ai_website_type', '' ),
        ) );

        $response = new \WP_REST_Response( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function enhance_prompt( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();
        $text = $parameters['text']; // Already sanitized and validated by Routes.php

        try {
            $enhanced_text = $this->call_ai_enhancement_service( $text );

            $data = array(
                'data' => array(
                    'improved_prompt' => $enhanced_text
                )
            );

            $response = new \WP_REST_Response( $data );
            $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
            $response->set_status( \WP_Http::OK );

            return $response;

        } catch ( Exception $e ) {
            return new \WP_Error(
                'ai_service_error',
                $e->getMessage(),
                array(
                    'status' => \WP_Http::SERVICE_UNAVAILABLE,
                )
            );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function detect_brand_and_type( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();
        $description = $parameters['description']; // Already sanitized and validated by Routes.php
        $is_builder_website_types = isset( $parameters['is_builder_website_types'] )
            ? filter_var( $parameters['is_builder_website_types'], FILTER_VALIDATE_BOOLEAN )
            : false;

        try {
            $response_data = $this->call_detect_brand_and_type_service( $description, $is_builder_website_types );
            $this->validate_detect_response( $response_data );

            $data = array(
                'data' => array(
                    'brandName' => $response_data['brandName'],
                    'websiteType' => $response_data['websiteType'],
                )
            );

            $response = new \WP_REST_Response( $data );
            $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
            $response->set_status( \WP_Http::OK );

            return $response;

        } catch ( Exception $e ) {
            return new \WP_Error(
                'ai_service_error',
                $e->getMessage(),
                array(
                    'status' => \WP_Http::SERVICE_UNAVAILABLE,
                )
            );
        }
    }

    private function validate_detect_response( array $response_data ): void {
        if ( empty( $response_data ) ) {
            throw new Exception( 'Detect brand and type service returned empty response' );
        }

        if ( ! isset( $response_data['brandName'] ) || empty( $response_data['brandName'] ) ) {
            throw new Exception( 'Detect brand and type service did not return brandName' );
        }

        if ( ! isset( $response_data['websiteType'] ) || empty( $response_data['websiteType'] ) ) {
            throw new Exception( 'Detect brand and type service did not return websiteType' );
        }
    }

    private function enhance_text_content( string $text ): string {
        return $this->call_ai_enhancement_service( $text );
    }

    private function call_ai_enhancement_service( string $text ): string {
        try {
            $request_params = ['text' => $text];
            $response_data = $this->request_client->post( '/v3/wordpress/plugin/builder/prompt-enhance', $request_params );
            $this->validate_enhancement_response( $response_data );

            return $response_data['improved_prompt'];

        } catch ( Exception $exception ) {
            error_log( 'AI Enhancement API Error: ' . $exception->getMessage() );
            throw new Exception( 'AI enhancement service temporarily unavailable: ' . $exception->getMessage() );
        }
    }

    private function validate_enhancement_response( array $response_data ): void {
        if ( empty( $response_data ) ) {
            throw new Exception( 'AI enhancement service returned empty response' );
        }

        if ( ! isset( $response_data['improved_prompt'] ) || empty( $response_data['improved_prompt'] ) ) {
            throw new Exception( 'AI enhancement service did not return improved prompt' );
        }
    }

    private function call_detect_brand_and_type_service( string $description, bool $is_builder_website_types = false ): array {
        try {
            $request_params = [
                'description' => $description,
                'is_builder_website_types' => $is_builder_website_types,
            ];
            $response_data = $this->wh_api_client->post( '/api/v1/builder/detect-brand-and-type', $request_params );

            if ( empty( $response_data ) ) {
                throw new Exception( 'Detect brand and type service returned empty response' );
            }

            return $response_data;

        } catch ( Exception $exception ) {
            error_log( 'Detect Brand and Type API Error: ' . $exception->getMessage() );
            throw new Exception( 'Detect brand and type service temporarily unavailable: ' . $exception->getMessage() );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function check_scam( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();
        $description = $parameters['description'];
        $language = $parameters['language'] ?? '';

        try {
            $response_data = $this->call_scam_detector_service( $description, $language );

            $data = array(
                'data' => array(
                    'isScam' => $response_data['isScam'] ?? false,
                    'scamReason' => $response_data['scamReason'] ?? null,
                )
            );

            $response = new \WP_REST_Response( $data );
            $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
            $response->set_status( \WP_Http::OK );

            return $response;

        } catch ( Exception $e ) {
            return new \WP_Error(
                'ai_service_error',
                $e->getMessage(),
                array(
                    'status' => \WP_Http::SERVICE_UNAVAILABLE,
                )
            );
        }
    }

    private function call_scam_detector_service( string $description, string $language = '' ): array {
        try {
            if ( empty( $language ) ) {
                $language = get_option( 'hostinger_ai_selected_language', 'en_US' );
            }

            $language = sanitize_text_field( $language );

            $request_params = [
                'description' => $description,
                'language' => $language,
            ];
            $response_data = $this->wh_api_client->post( '/api/v1/builder/detect-scam', $request_params );

            if ( empty( $response_data ) ) {
                throw new Exception( 'Scam detector service returned empty response' );
            }

            return $response_data;

        } catch ( Exception $exception ) {
            error_log( 'Scam Detector API Error: ' . $exception->getMessage() );
            throw new Exception( 'Scam detector service temporarily unavailable: ' . $exception->getMessage() );
        }
    }
}
