<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Post\TP_Post_Type;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Post_Types_Controller extends TP_REST_Controller {
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'types';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),],
                    'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<type>[\w-]+)',
                ['args'=> ['type' => ['description' => $this->__( 'An alphanumeric identifier for the post type.' ),'type' => 'string',], ],
                    ['methods' => TP_GET,'callback' => array( $this, 'get_item' ),'permission_callback' => '__return_true',
                        'args' => ['context' => $this->get_context_param( array( 'default' => 'view' ) ),],
                    ],
                    'schema' => array( $this, 'get_public_item_schema' ),
                ]
            );
        }//36
        public function get_items_permissions_check( $request ):string{
            if ( 'edit' === $request['context'] ) {
                $types = $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' );
                foreach ( $types as $type ) {
                    if ( $this->_current_user_can( $type->cap->edit_posts ) ) return true;
                }
                return new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to edit posts in this post type.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            }
            return true;
        }//83
        public function get_items($request ):string{
            $data  = [];
            $types = $this->_get_post_types(['show_in_rest' => true], 'objects' );
            foreach ( $types as $type ) {
                if ( 'edit' === $request['context'] && ! $this->_current_user_can( $type->cap->edit_posts ) )
                    continue;
                $post_type           = $this->prepare_item_for_response( $type, $request );
                $data[ $type->name ] = $this->prepare_response_for_collection( $post_type );
            }
            return $this->_rest_ensure_response( $data );
        }//111
        public function get_item( $request ):string{
            $_obj = $this->_get_post_type_object( $request['type'] );
            $obj = null;
            if($_obj  instanceof TP_Post_Type ){
                $obj = $_obj;
            }
            if ($obj !== null)
                return new TP_Error('rest_type_invalid',
                    $this->__( 'Invalid post type.' ),
                    ['status' => NOT_FOUND]);
            if ( empty( $obj->show_in_rest ) )
                return new TP_Error('rest_cannot_read_type',
                    $this->__( 'Cannot view post type.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( $obj->cap->edit_posts ) )
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit posts in this post type.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            $data = $this->prepare_item_for_response( $obj, $request );

            return $this->_rest_ensure_response( $data );
        }//135
        public function prepare_item_for_response( $item, $request ):string{
            $post_type  = $item;
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $post_type->name, 'objects' ), array( 'show_in_rest' => true ) );
            $taxonomies = $this->_tp_list_pluck( $taxonomies, 'name' );
            $base       = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;
            $namespace  = ! empty( $post_type->rest_namespace ) ? $post_type->rest_namespace : 'tp/v1';
            $supports   = $this->_get_all_post_type_supports( $post_type->name );
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( in_array( 'capabilities', $fields, true ) ) $data['capabilities'] = $post_type->cap;
            if ( in_array( 'description', $fields, true ) )  $data['description'] = $post_type->description;
            if ( in_array( 'hierarchical', $fields, true ) ) $data['hierarchical'] = $post_type->hierarchical;
            if ( in_array( 'visibility', $fields, true ) )
                $data['visibility'] = ['show_in_nav_menus' => (bool) $post_type->show_in_nav_menus,
                    'show_ui' => (bool) $post_type->show_ui,];
            if ( in_array( 'viewable', $fields, true ) ) $data['viewable'] = $this->_is_post_type_viewable( $post_type );
            if ( in_array( 'labels', $fields, true ) ) $data['labels'] = $post_type->labels;
            if ( in_array( 'name', $fields, true ) ) $data['name'] = $post_type->label;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $post_type->name;
            if ( in_array( 'supports', $fields, true ) ) $data['supports'] = $supports;
            if ( in_array( 'taxonomies', $fields, true ) ) $data['taxonomies'] = array_values( $taxonomies );
            if ( in_array( 'rest_base', $fields, true ) ) $data['rest_base'] = $base;
            if ( in_array( 'rest_namespace', $fields, true ) ) $data['rest_namespace'] = $namespace;
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links(
                [
                    'collection'=> ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
                    'https://api.w.org/items' => ['href' => $this->_rest_url( $this->_rest_get_route_for_post_type_items( $post_type->name ) ),],
                ]
            );
            return $this->_apply_filters( 'rest_prepare_post_type', $response, $post_type, $request );
        }//177
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'type','type' => 'object',
                'properties' =>[
                    'capabilities' => ['description' => $this->__( 'All capabilities used by the post type.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'description' => ['description' => $this->__( 'A human-readable description of the post type.' ),
                        'type' => 'string','context' => ['view','edit'],'readonly' => true,
                    ],
                    'hierarchical' => ['description' => $this->__( 'Whether or not the post type should have children.' ),
                        'type' => 'boolean','context' => ['view','edit'],'readonly' => true,
                    ],
                    'viewable' => ['description' => $this->__( 'Whether or not the post type can be viewed.' ),
                        'type' => 'boolean','context' => ['edit'],'readonly' => true,
                    ],
                    'labels' => ['description' => $this->__( 'Human-readable labels for the post type for various contexts.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'name' => ['description' => $this->__( 'The title for the post type.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the post type.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'supports' => ['description' => $this->__( 'All features, supported by the post type.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'taxonomies' => ['description' => $this->__( 'Taxonomies associated with post type.' ),
                        'type' => 'array','items' => ['type' => 'string',],'context' => ['view','edit'],'readonly' => true,
                    ],
                    'rest_base' => ['description' => $this->__( 'REST base route for the post type.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'rest_namespace' => ['description' => $this->__( 'REST route\'s namespace for the post type.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'visibility' => ['description' => $this->__( 'The visibility settings for the post type.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                        'properties' => [
                            'show_ui' => ['description' => $this->__( 'Whether to generate a default UI for managing this post type.' ),
                                'type' => 'boolean',
                            ],
                            'show_in_nav_menus' => ['description' => $this->__( 'Whether to make the post type is available for selection in navigation menus.' ),
                                'type' => 'boolean',
                            ],
                        ],
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//281
        public function get_collection_params():array{
            return ['context' => $this->get_context_param(['default' => 'view'])];
        }//391
    }
}else die;