<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\Theme\_theme_08;
use TP_Core\Libs\TP_Theme;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Themes_Controller extends TP_REST_Controller{
        use _theme_07;
        use _theme_08;
        public const PATTERN = '[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?';
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'themes';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),],
                    'schema' => [$this, 'get_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf( '/%s/(?P<stylesheet>%s)', $this->_rest_base, self::PATTERN ),
                [
                    'args' => ['stylesheet' =>[
                        'description' => $this->__( "The theme's stylesheet. This uniquely identifies the theme." ),
                        'type'=> 'string','sanitize_callback' => [$this, '_sanitize_stylesheet_callback'],
                    ]],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],'permission_callback' => [$this, 'get_item_permissions_check'],],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//42
        public function sanitize_stylesheet_callback( $stylesheet ) {
            return urldecode( $stylesheet );
        }//86
        public function get_items_permissions_check( $request ):string{
            if ( $this->_current_user_can( 'switch_themes' ) || $this->_current_user_can( 'manage_network_themes' ) )
                return true;
            $registered = $this->get_collection_params();
            if ( isset( $registered['status'], $request['status'] ) && is_array( $request['status'] ) && array( 'active' ) === $request['status'] )
                return $this->_check_read_active_theme_permission();
            return new TP_Error('rest_cannot_view_themes',
                $this->__( 'Sorry, you are not allowed to view themes.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//98
        public function get_item_permissions_check( $request ):string{
            if ( $this->_current_user_can( 'switch_themes' ) || $this->_current_user_can( 'manage_network_themes' ) )
                return true;
            $tp_theme = $this->_tp_get_theme( $request['stylesheet'] );
            $current_theme = $this->_tp_get_theme();
            if ( $this->_is_same_theme( $tp_theme, $current_theme ) )
                return $this->_check_read_active_theme_permission();
            return new TP_Error('rest_cannot_view_themes',
                $this->__( 'Sorry, you are not allowed to view themes.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//123
        protected function _check_read_active_theme_permission(){
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types(['show_in_rest' => true], 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            return new TP_Error(
                'rest_cannot_view_active_theme',
                $this->__( 'Sorry, you are not allowed to view the active theme.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//149
        public function get_item( $request ):string{
            $tp_theme = $this->_tp_get_theme( $request['stylesheet'] );
            if ( ! $tp_theme->exists() )
                return new TP_Error('rest_theme_not_found',
                    $this->__( 'Theme not found.' ),
                    ['status' => NOT_FOUND]);
            $data = $this->prepare_item_for_response( $tp_theme, $request );
            return $this->_rest_ensure_response( $data );
        }//175
        public function get_items($request ):string{
            $themes = [];
            $active_themes = $this->_tp_get_themes();
            $current_theme = $this->_tp_get_theme();
            $status        = $request['status'];
            foreach ( $active_themes as $theme_name => $theme ) {
                $theme_status = ( $this->_is_same_theme( $theme, $current_theme ) ) ? 'active' : 'inactive';
                if ( is_array( $status ) && ! in_array( $theme_status, $status, true ) ) continue;
                $prepared = $this->prepare_item_for_response( $theme, $request );
                $themes[] = $this->prepare_response_for_collection( $prepared );
            }
            $_response = $this->_rest_ensure_response( $themes );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->header( 'X-TP-Total', count( $themes ) );
            $response->header( 'X-TP-TotalPages', 1 );
            return $response;
        }//197
        public function prepare_item_for_response(TP_Theme $item, $request ):string{
            $theme  = $item;
            $data   = [];
            $fields = $this->get_fields_for_response( $request );
            if ( $this->_rest_is_field_included( 'stylesheet', $fields ) )
                $data['stylesheet'] = $theme->get_stylesheet();
            if ( $this->_rest_is_field_included( 'template', $fields ) )
                $data['template'] = $theme->get_template();
            $plain_field_mappings = ['requires_php' => 'RequiresPHP','requires_tp' => 'RequiresTP',
                'textdomain' => 'TextDomain','version' => 'Version',];
            foreach ( $plain_field_mappings as $field => $header ) {
                if ($theme  instanceof TP_Query && $this->_rest_is_field_included( $field, $fields ) ) {
                    $data[ $field ] = $theme->get( $header );
                }
            }
            if ( $this->_rest_is_field_included( 'screenshot', $fields ) )
                $data['screenshot'] = $theme->get_screenshot() ?: '';
            $rich_field_mappings = ['author' => 'Author','author_uri' => 'AuthorURI','description' => 'Description',
                'name' => 'Name','tags' => 'Tags','theme_uri' => 'ThemeURI',];
            foreach ( $rich_field_mappings as $field => $header ) {
                if ( $this->_rest_is_field_included( "{$field}.raw", $fields ) )
                    $data[ $field ]['raw'] = $theme->display( $header, false, true );
                if ( $this->_rest_is_field_included( "{$field}.rendered", $fields ) )
                    $data[ $field ]['rendered'] = $theme->display( $header );
            }
            $current_theme = $this->_tp_get_theme();
            if ( $this->_rest_is_field_included( 'status', $fields ) )
                $data['status'] = ( $this->_is_same_theme( $theme, $current_theme ) ) ? 'active' : 'inactive';
            if ( $this->_rest_is_field_included( 'theme_supports', $fields ) && $this->_is_same_theme( $theme, $current_theme ) ) {
                foreach ($this->_get_registered_theme_features() as $feature => $config ) {
                    if ( ! is_array( $config['show_in_rest'] ) ) continue;
                    $name = $config['show_in_rest']['name'];
                    if ( ! $this->_rest_is_field_included( "theme_supports.{$name}", $fields ) ) continue;
                    if ( ! $this->_current_theme_supports( $feature ) ) {
                        $data['theme_supports'][ $name ] = $config['show_in_rest']['schema']['default'];
                        continue;
                    }
                    $support = $this->_get_theme_support( $feature );
                    if ( isset( $config['show_in_rest']['prepare_callback'] ) )
                        $prepare = $config['show_in_rest']['prepare_callback'];
                    else $prepare = [$this, 'prepare_theme_support'];
                    $prepared = $prepare( $support, $config, $feature, $request );
                    if ( $this->_init_error( $prepared ) ) continue;
                    $data['theme_supports'][ $name ] = $prepared;
                }
            }
            $data = $this->_add_additional_fields_to_object( $data, $request );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links( $this->_prepare_links( $theme ) );
            if ( $theme->get_stylesheet() === $this->_tp_get_theme()->get_stylesheet() )
                $id = TP_Theme_JSON_Resolver::get_user_global_styles_post_id();
            else {
                $user_cpt = TP_Theme_JSON_Resolver::get_user_data_from_tp_global_styles( $theme );
                $id       = $user_cpt['ID'] ?? null;
            }
            if ( $id )
                $response->add_link(
                    'https://api.w.org/user-global-styles',
                    $this->_rest_url( 'tp/v1/global-styles/' . $id )
                );
            return $this->_apply_filters( 'rest_prepare_theme', $response, $theme, $request );
        }//232
        protected function _prepare_links(TP_Theme $theme ): array{
            return [
                'self'=> ['href' => $this->_rest_url( sprintf( '%s/%s/%s', $this->_namespace, $this->_rest_base, $theme->get_stylesheet() ) ),],
                'collection' => ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
            ];
        }//371
        protected function _is_same_theme(TP_Theme $theme_a,TP_Theme $theme_b ): bool{
            return $theme_a->get_stylesheet() === $theme_b->get_stylesheet();
        }//391
        protected function _prepare_theme_support( $support,array ...$args){ //removed $feature, $request
            $schema = $args['show_in_rest']['schema'];
            if ( 'boolean' === $schema['type'] ) return true;
            if ( is_array( $support ) && ! $args['variadic'] ) $support = $support[0];
            return $this->_rest_sanitize_value_from_schema( $support, $schema );
        }//406
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema'    => 'http://json-schema.org/draft-04/schema#',
                'title' => 'theme','type' => 'object',
                'properties' => [
                    'stylesheet' => ['description' => $this->__( 'The theme\'s stylesheet. This uniquely identifies the theme.' ),
                        'type' => 'string','readonly' => true,
                    ],
                    'template' => ['description' => $this->__( 'The theme\'s template. If this is a child theme, this refers to the parent theme, otherwise this is the same as the theme\'s stylesheet.' ),
                        'type' => 'string','readonly' => true,
                    ],
                    'author' => ['description' => $this->__( 'The theme author.' ),
                        'type' => 'object','readonly' => true,
                        'properties' =>[
                            'raw' => ['description' => $this->__( 'The theme author\'s name, as found in the theme header.' ), 'type' => 'string',],
                            'rendered' => ['description' => $this->__( 'HTML for the theme author, transformed for display.' ),'type' => 'string',],
                        ]
                    ],
                    'author_uri' => ['description' => $this->__( 'The website of the theme author.' ),
                        'type' => 'object','readonly' => true,
                        'properties'  =>[
                            'raw' => ['description' => $this->__( 'The website of the theme author, as found in the theme header.' ),'type' => 'string','format' => 'uri',],
                            'rendered' => ['description' => $this->__( 'The website of the theme author, transformed for display.' ),'type' => 'string','format' => 'uri',],
                        ]
                    ],
                    'description' => ['description' => $this->__( 'A description of the theme.' ),
                        'type' => 'object','readonly' => true,
                        'properties' =>[
                            'raw' => ['description' => $this->__( 'The theme description, as found in the theme header.' ),'type' => 'string',],
                            'rendered' => ['description' => $this->__( 'The theme description, transformed for display.' ),'type' => 'string',],
                        ]
                    ],
                    'name' => [
                        'description' => $this->__( 'The name of the theme.' ),
                        'type' => 'object','readonly' => true,
                        'properties'  =>[
                            'raw' => ['description' => $this->__( 'The theme name, as found in the theme header.' ),'type'=> 'string',],
                            'rendered' => ['description' => $this->__( 'The theme name, transformed for display.' ),'type'=> 'string',],
                        ]
                    ],
                    'requires_php' => ['description' => $this->__( 'The minimum PHP version required for the theme to work.' ),
                        'type' => 'string','readonly' => true,],
                    'requires_tp' => ['description' => $this->__( 'The minimum WordPress version required for the theme to work.' ),
                        'type' => 'string','readonly' => true,],
                    'screenshot' => ['description' => $this->__( 'The theme\'s screenshot URL.' ),
                        'type' => 'string','format' => 'uri','readonly' => true,],
                    'tags' => ['description' => $this->__( 'Tags indicating styles and features of the theme.' ),
                        'type' => 'object','readonly' => true,
                        'properties'  =>[
                            'raw' => ['description' => $this->__( 'The theme tags, as found in the theme header.' ),
                                'type' => 'array','items' => ['type' => 'string',],
                            ],
                            'rendered' => ['description' => $this->__( 'The theme tags, transformed for display.' ),'type' => 'string',],
                        ]
                    ],
                    'textdomain' => ['description' => $this->__( 'The theme\'s text domain.' ),
                        'type' => 'string','readonly' => true,
                    ],
                    'theme_supports' => ['description' => $this->__( 'Features supported by this theme.' ),
                        'type' => 'object','readonly' => true,'properties'  => [],
                    ],
                    'theme_uri' => ['description' => $this->__( 'The URI of the theme\'s webpage.' ),
                        'type'        => 'object','readonly'    => true,
                        'properties'  =>[
                            'raw' => ['description' => $this->__( 'The URI of the theme\'s webpage, as found in the theme header.' ),
                                'type' => 'string','format' => 'uri',],
                            'rendered' => ['description' => $this->__( 'The URI of the theme\'s webpage, transformed for display.' ),
                                'type' => 'string','format' => 'uri',],
                        ]
                    ],
                    'version' => ['description' => $this->__( 'The theme\'s current version.' ),
                        'type' => 'string','readonly' => true,
                    ],
                    'status' => ['description' => $this->__( 'A named status for the theme.' ),
                        'type' => 'string','enum' => ['inactive', 'active'],
                    ],
                ]
            ];
            foreach ($this->_get_registered_theme_features() as $feature => $config ) {
                if ( ! is_array( $config['show_in_rest'] ) ) continue;
                $name = $config['show_in_rest']['name'];
                $schema['properties']['theme_supports']['properties'][ $name ] = $config['show_in_rest']['schema'];
            }
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//427
        public function get_collection_params():array{
            $query_params = ['status' => ['description' => $this->__( 'Limit result set to themes assigned one or more statuses.' ),
                'type' => 'array','items' => ['enum' => ['active', 'inactive'],'type' => 'string',],],];
            return $this->_apply_filters( 'rest_themes_collection_params', $query_params );
        }//606
    }
}else die;