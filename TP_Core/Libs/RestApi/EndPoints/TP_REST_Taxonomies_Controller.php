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
    class TP_REST_Taxonomies_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'taxonomies';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this,'get_items'],
                    'permission_callback' => [$this,'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
                ],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base. '/(?P<taxonomy>[\w-]+)',
                ['args' =>['taxonomy' => ['description' => $this->__( 'An alphanumeric identifier for the taxonomy.' ),
                    'type' => 'string',],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args'=> ['context' => $this->get_context_param(['default' => 'view']),],
                    ],'schema' => [$this, 'get_public_item_schema']]]
            );
        }//36
        public function get_items_permissions_check( $request ):string{
            if ( 'edit' === $request['context'] ) {
                if ( ! empty( $request['type'] ) )
                    $taxonomies = $this->_get_object_taxonomies( $request['type'], 'objects' );
                else $taxonomies = $this->_get_taxonomies( '', 'objects' );
                foreach ( $taxonomies as $taxonomy ) {
                    if ( ! empty( $taxonomy->show_in_rest ) && $this->_current_user_can( $taxonomy->cap->assign_terms ) )
                        return true;
                }
                return new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to manage terms in this taxonomy.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            }
            return true;
        }//83
        public function get_items($request ):string{
            $registered = $this->get_collection_params();
            if ( isset( $registered['type'] ) && ! empty( $request['type'] ) )
                $taxonomies = $this->_get_object_taxonomies( $request['type'], 'objects' );
            else $taxonomies = $this->_get_taxonomies( '', 'objects' );
            $data = [];
            foreach ( $taxonomies as $tax_type => $value ) {
                if ( empty( $value->show_in_rest ) || ( 'edit' === $request['context'] && ! $this->_current_user_can( $value->cap->assign_terms ) ) )
                    continue;
                $tax               = $this->prepare_item_for_response( $value, $request );
                $tax               = $this->prepare_response_for_collection( $tax );
                $data[ $tax_type ] = $tax;
            }
            if ( empty( $data ) ) $data = (object) $data;
            return $this->_rest_ensure_response( $data );
        }//115
        public function get_item_permissions_check( $request ):string{
            $_tax_obj = $this->_get_taxonomy( $request['taxonomy'] );
            $tax_obj = null;
            if($_tax_obj  instanceof TP_Post_Type ){
                $tax_obj = $_tax_obj;
            }
            if ( $tax_obj ) {
                if ( empty( $tax_obj->show_in_rest ) ) return false;
                if ( 'edit' === $request['context'] && ! $this->_current_user_can( $tax_obj->cap->assign_terms ) )
                    return new TP_Error( 'rest_forbidden_context',
                        $this->__( 'Sorry, you are not allowed to manage terms in this taxonomy.' ),
                        ['status' => $this->_rest_authorization_required_code()]);
            }
            return true;
        }//154
        public function get_item( $request ):string{
            $tax_obj = $this->_get_taxonomy( $request['taxonomy'] );
            if ( empty( $tax_obj ) )
                return new TP_Error('rest_taxonomy_invalid',
                    $this->__( 'Invalid taxonomy.' ),
                    ['status' => NOT_FOUND]);
            $data = $this->prepare_item_for_response( $tax_obj, $request );
            return $this->_rest_ensure_response( $data );
        }//183
        public function prepare_item_for_response( $item, $request ):string{
            $taxonomy = $item;
            $base     = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( in_array( 'name', $fields, true ) ) $data['name'] = $taxonomy->label;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $taxonomy->name;
            if ( in_array( 'capabilities', $fields, true ) ) $data['capabilities'] = $taxonomy->cap;
            if ( in_array( 'description', $fields, true ) ) $data['description'] = $taxonomy->description;
            if ( in_array( 'labels', $fields, true ) ) $data['labels'] = $taxonomy->labels;
            if ( in_array( 'types', $fields, true ) ) $data['types'] = array_values( $taxonomy->object_type );
            if ( in_array( 'show_cloud', $fields, true ) ) $data['show_cloud'] = $taxonomy->show_tagcloud;
            if ( in_array( 'hierarchical', $fields, true ) ) $data['hierarchical'] = $taxonomy->hierarchical;
            if ( in_array( 'rest_base', $fields, true ) ) $data['rest_base'] = $base;
            if ( in_array( 'rest_namespace', $fields, true ) ) $data['rest_namespace'] = $taxonomy->rest_namespace;
            if ( in_array( 'visibility', $fields, true ) ) {
                $data['visibility'] = [
                    'public' => (bool) $taxonomy->public,
                    'publicly_queryable' => (bool) $taxonomy->publicly_queryable,
                    'show_admin_column' => (bool) $taxonomy->show_admin_column,
                    'show_in_nav_menus' => (bool) $taxonomy->show_in_nav_menus,
                    'show_in_quick_edit' => (bool) $taxonomy->show_in_quick_edit,
                    'show_ui' => (bool) $taxonomy->show_ui,
                ];
            }
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links(
                [['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
                  'https://api.w.org/items' => ['href' => $this->_rest_url( $this->_rest_get_route_for_taxonomy_items( $taxonomy->name ) ),],
                ]
            );
            return $this->_apply_filters( 'rest_prepare_taxonomy', $response, $taxonomy, $request );
        }//209
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'taxonomy','type' => 'object',
                'properties' => [
                    'capabilities' => ['description' => $this->__( 'All capabilities used by the taxonomy.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'description' => ['description' => $this->__( 'A human-readable description of the taxonomy.' ),
                        'type' => 'string','context' => ['view','edit'],'readonly' => true,
                    ],
                    'hierarchical' => ['description' => $this->__( 'Whether or not the taxonomy should have children.' ),
                        'type' => 'boolean','context' => ['view','edit'],'readonly' => true,
                    ],
                    'labels' => ['description' => $this->__( 'Human-readable labels for the taxonomy for various contexts.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'name' => ['description' => $this->__( 'The title for the taxonomy.' ),
                        'type' => 'string', 'context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the taxonomy.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'show_cloud'=> ['description' => $this->__( 'Whether or not the term cloud should be displayed.' ),
                        'type'=> 'boolean','context'=> ['edit'],'readonly'=> true,
                    ],
                    'types' => ['description' => $this->__( 'Types associated with the taxonomy.' ),
                        'type' => 'array','items' => ['type' => 'string',],'context' => ['view','edit'],'readonly' => true,
                    ],
                    'rest_base' => ['description' => $this->__( 'REST base route for the taxonomy.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'rest_namespace' => ['description' => $this->__( 'REST namespace route for the taxonomy.' ),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'visibility' => ['description' => $this->__( 'The visibility settings for the taxonomy.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                        'properties'  => [
                            'public' => ['description' => $this->__( 'Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users.' ),
                                'type' => 'boolean',],
                            'publicly_queryable' => ['description' => $this->__( 'Whether the taxonomy is publicly queryable.' ),
                                'type' => 'boolean',],
                            'show_ui' => ['description' => $this->__( 'Whether to generate a default UI for managing this taxonomy.' ),
                                'type' => 'boolean',],
                            'show_admin_column' => ['description' => $this->__( 'Whether to allow automatic creation of taxonomy columns on associated post-types table.' ),
                                'type' => 'boolean',],
                            'show_in_nav_menus' => ['description' => $this->__( 'Whether to make the taxonomy available for selection in navigation menus.' ),
                                'type' => 'boolean',],
                            'show_in_quick_edit' => ['description' => $this->__( 'Whether to show the taxonomy in the quick/bulk edit panel.' ),
                                'type' => 'boolean',],
                        ],
                    ],





                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//309
        public function get_collection_params():array{
            $new_params = [];
            $new_params['context'] = $this->get_context_param(['default' => 'view']);
            $new_params['type'] = ['description' => $this->__( 'Limit results to taxonomies associated with a specific post type.' ),
                'type' => 'string',];
            return $new_params;
        }//430
    }
}else die;