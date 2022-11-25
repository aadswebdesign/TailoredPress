<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\JSON\TP_Theme_JSON;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Global_Styles_Controller extends TP_REST_Controller {
        protected $_post_type;
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'global-styles';
            $this->_post_type = 'tp_global_styles';
        }
        public function register_routes():void {
            $this->_register_rest_route(
                $this->_namespace,
                // The route.
                sprintf(
                    '/%s/themes/(?P<stylesheet>%s)',
                    $this->_rest_base,
                    '[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?'
                ),
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_theme_item' ),
                        'permission_callback' => array( $this, 'get_theme_item_permissions_check' ),
                        'args'                => array(
                            'stylesheet' => array(
                                'description'       => $this->__( 'The theme identifier' ),
                                'type'              => 'string',
                                'sanitize_callback' => array( $this, '_sanitize_global_styles_callback' ),
                            ),
                        ),
                    ),
                )
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<id>[\/\w-]+)',
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_item' ),
                        'permission_callback' => array( $this, 'get_item_permissions_check' ),
                        'args'                => array(
                            'id' => array(
                                'description'       => $this->__( 'The id of a template' ),
                                'type'              => 'string',
                                'sanitize_callback' => array( $this, '_sanitize_global_styles_callback' ),
                            ),
                        ),
                    ),
                    array(
                        'methods'             => TP_EDITABLE,
                        'callback'            => array( $this, 'update_item' ),
                        'permission_callback' => array( $this, 'update_item_permissions_check' ),
                        'args'                => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
        }//40 //todo shrinking
        public function _sanitize_global_styles_callback( $id_or_stylesheet ) {
            return urldecode( $id_or_stylesheet );
        }//106
        public function get_item_permissions_check( $request ):string {
            $post = $this->_get_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            if ( 'edit' === $request['context'] && $post && ! $this->_check_update_permission( $post ) ) {
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit this global style.' ),
                    ['status' => $this->_rest_authorization_required_code() ]
                );
            }
            if ( ! $this->_check_read_permission( $post ) )
                return new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to view this global style.' ),
                    ['status' => $this->_rest_authorization_required_code() ]
                );
            return true;
        }//118
        protected function _check_read_permission( $post ) {
            return $this->_current_user_can( 'read_post', $post->ID );
        }//151
        public function get_item( $request ):string {
            $post = $this->_get_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            return $this->prepare_item_for_response( $post, $request );
        }//164
        public function update_item_permissions_check( $request ):string {
            $post = $this->_get_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            if ( $post && ! $this->_check_update_permission( $post ) )
                return new TP_Error('rest_cannot_edit',
                    $this->__( 'Sorry, you are not allowed to edit this global style.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//181
        protected function _check_update_permission( $post ) {
            return $this->_current_user_can( 'edit_post', $post->ID );
        }//206
        public function update_item( $request ):string {
            $post_before = $this->_get_post( $request['id'] );
            if ( $this->_init_error( $post_before ) ) return $post_before;
            $changes = $this->_prepare_item_for_database( $request );
            $result  = $this->_tp_update_post( $this->_tp_slash( (array) $changes ), true, false );
            if ( $this->_init_error( $result ) ) return $result;
            $post          = $this->_get_post( $request['id'] );
            $fields_update = $this->_update_additional_fields_for_object( $post, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $this->_tp_after_insert_post( $post, true, $post_before );
            $response = $this->prepare_item_for_response( $post, $request );
            return $this->_rest_ensure_response( $response );
        }//218
        protected function prepare_item_for_database( $request ): \stdClass {
            $changes     = new \stdClass();
            $changes->ID = $request['id'];
            $post = $this->_get_post( $request['id'] );
            if($post instanceof \stdClass ){}//todo
            $existing_config = [];
            if ( $post ) {
                $existing_config     = json_decode( $post->post_content, true );
                $json_decoding_error = json_last_error();
                if ( JSON_ERROR_NONE !== $json_decoding_error || ! isset( $existing_config['isGlobalStylesUserThemeJSON'] ) ||
                    ! $existing_config['isGlobalStylesUserThemeJSON'] ) {
                    $existing_config = [];
                }
            }
            if ( isset( $request['styles'] ) || isset( $request['settings'] ) ) {
                $config = array();
                if ( isset( $request['styles'] ) ) $config['styles'] = $request['styles'];
                elseif ( isset( $existing_config['styles'] ) ) $config['styles'] = $existing_config['styles'];
                if ( isset( $request['settings'] ) ) $config['settings'] = $request['settings'];
                elseif ( isset( $existing_config['settings'] ) ) $config['settings'] = $existing_config['settings'];
                $config['isGlobalStylesUserThemeJSON'] = true;
                $config['version'] = TP_Theme_JSON::LATEST_SCHEMA;
                $changes->post_content = $this->_tp_json_encode( $config );
            }
            if ( isset( $request['title'] ) ) {
                if ( is_string( $request['title'] ) ) $changes->post_title = $request['title'];
                elseif ( ! empty( $request['title']['raw'] ) )
                    $changes->post_title = $request['title']['raw'];
            }
            return $changes;
        }
        public function prepare_item_for_response( $post, $request ):string { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
            $raw_config = json_decode( $post->post_content, true );
            $is_global_styles_user_theme_json = isset( $raw_config['isGlobalStylesUserThemeJSON'] ) && true === $raw_config['isGlobalStylesUserThemeJSON'];
            $config = [];
            if ( $is_global_styles_user_theme_json )
                $config = ( new TP_Theme_JSON( $raw_config, 'custom' ) )->get_raw_data();
            $data   = [];
            $fields = $this->get_fields_for_response( $request );
            if ( $this->_rest_is_field_included( 'id', $fields ) ) $data['id'] = $post->ID;
            if ( $this->_rest_is_field_included( 'title', $fields ) ) $data['title'] = array();
            if ( $this->_rest_is_field_included( 'title.raw', $fields ) ) $data['title']['raw'] = $post->post_title;
            if ( $this->_rest_is_field_included( 'title.rendered', $fields ) ) {
                $this->_add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
                $data['title']['rendered'] = $this->_get_the_title( $post->ID );
                $this->_remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
            }
            if ( $this->_rest_is_field_included( 'settings', $fields ) )
                $data['settings'] = ! empty( $config['settings'] ) && $is_global_styles_user_theme_json ? $config['settings'] : new \stdClass();
            if ( $this->_rest_is_field_included( 'styles', $fields ) )
                $data['styles'] = ! empty( $config['styles'] ) && $is_global_styles_user_theme_json ? $config['styles'] : new \stdClass();
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $links = $this->_prepare_links( $post->ID );
            $response->add_links( $links );
            if ( ! empty( $links['self']['href'] ) ) {
                $actions = $this->_get_available_actions();
                $self    = $links['self']['href'];
                foreach ( $actions as $rel ) $response->add_link( $rel, $self );
            }
            return $response;
        }//304
        protected function _get_post( $id ){
            $error = new TP_Error('rest_global_styles_not_found',
                $this->__( 'No global styles config exist with that id.' ),
                ['status' => NOT_FOUND]
            );
            if ( $id <= 0 ) return $error;
            $post = $this->_get_post( $id );
            if( $post instanceof TP_Post ){
                if ( empty( $post ) || empty( $post->ID ) || $this->_post_type !== $post->post_type )
                    return $error;
            }
            return $post;
        }//391
        protected function _prepare_links( $id ): array{
            $base = sprintf( '%s/%s', $this->_namespace, $this->_rest_base );
            $links = ['self' => ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $id ),],];
            return $links;
        }//399
        protected function _get_available_actions(): array{
            $rels = [];
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( $this->_current_user_can( $post_type->cap->publish_posts ) )
                $rels[] = 'https://api.w.org/action-publish';
            return $rels;
        }//418
        public function protected_title_format(): string{
            return '%s';
        }//440
        public function get_collection_params():array {
            return [];
        }//453
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = [
                '$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => $this->_post_type,
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'description' => $this->__( 'ID of global styles config.' ),
                        'type' => 'string','context' => ['view', 'edit', 'embed'],'readonly' => true,
                    ],
                    'styles' => [
                        'description' => $this->__( 'Global styles.' ),'type' =>  ['object'],'context' => ['view', 'edit'],
                    ],
                    'settings' => [
                        'description' => $this->__( 'Global settings.' ),'type' => ['object'],'context' => ['view', 'edit'],
                    ],
                    'title' => [
                        'description' => $this->__( 'Title of the global styles variation.' ),
                        'type' => ['object', 'string'],'default' => '','context' => ['view', 'edit', 'embed'],
                        'properties'  => [
                            'raw' => ['description' => $this->__( 'Title for the global styles variation, as it exists in the database.' ),
                                'type' => 'string','context' => ['view', 'edit', 'embed'],],
                            'rendered' => ['description' => $this->__( 'HTML title for the post, transformed for display.' ),
                                'type' => 'string','context' => ['view', 'edit', 'embed'],'readonly' => true,],
                        ],
                    ],
                ],
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//462
        public function get_theme_item_permissions_check( $request ){
            if ( ! $this->_current_user_can( 'edit_theme_options' ) ) {
                $this->_init_error( $request );//todo
                return new TP_Error('rest_cannot_manage_global_styles',
                    $this->__( 'Sorry, you are not allowed to access the global styles on this site.' ),
                    ['status' => $this->_rest_authorization_required_code(),]
                );
            }
            return true;
        }//523
        public function get_theme_item( $request ){
            if ( $this->_tp_get_theme()->get_stylesheet() !== $request['stylesheet'] )
                return new TP_Error('rest_theme_not_found',
                    $this->__( 'Theme not found.' ),
                    ['status' => NOT_FOUND]
                );
            $theme  = TP_Theme_JSON_Resolver::get_merged_data( 'theme' );
            $data   = [];
            $fields = $this->get_fields_for_response( $request );
            if ( $this->_rest_is_field_included( 'settings', $fields ) ) $data['settings'] = $theme->get_settings();
            if ( $this->_rest_is_field_included( 'styles', $fields ) ) {
                $raw_data = $theme->get_raw_data();
                if ( isset( $raw_data['styles'] ) ) $data['styles'] = $raw_data['styles'];
            }
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $links = ['self' => ['href' => $this->_rest_url( sprintf( '%s/%s/themes/%s', $this->_namespace, $this->_rest_base, $request['stylesheet'] ) ),],];
            $response->add_links( $links );
            return $response;
        }//547
    }
}else die;