<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Term;
use TP_Core\Libs\RestApi\Fields\TP_REST_Term_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Terms_Controller extends TP_REST_Controller{
        protected $_taxonomy;
        protected $_meta;
        protected $_sort_column;
        protected $_total_terms;
        protected $_allow_batch = array( 'v1' => true );
        public function __construct( $taxonomy ) {
            $this->_taxonomy  = $taxonomy;
            $tax_obj         =  $this->_get_taxonomy( $taxonomy );
            $this->_rest_base = ! empty( $tax_obj->rest_base ) ? $tax_obj->rest_base : $tax_obj->name;
            $this->_namespace = ! empty( $tax_obj->rest_namespace ) ? $tax_obj->rest_namespace : 'wp/v2';
            $this->_meta = new TP_REST_Term_Meta_Fields( $taxonomy );
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [['methods'=> TP_GET,'callback'=> [$this, 'get_items'],
                     'permission_callback' => [$this, 'get_items_permissions_check'],
                     'args'=> $this->get_collection_params(),
                 ],
                 ['methods'=> TP_POST,'callback'=> [$this,'create_item'],
                     'permission_callback' => [$this,'create_item_permissions_check'],
                     'args'=> $this->get_endpoint_args_for_item_schema( TP_POST ),
                 ],'allow_batch' => $this->_allow_batch,
                    'schema'=> [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base . '/(?P<id>[\d]+)',
                [
                    'args' => ['id' => ['description' => $this->__( 'Unique identifier for the term.' ),'type' => 'integer',]],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => ['context' => $this->get_context_param(['default' => 'view']),],
                    ],
                    ['methods' => TP_EDITABLE,'callback'=> [$this, 'update_item'],
                        'permission_callback' => [$this, 'update_item_permissions_check'],
                        'args'=> $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ],
                    ['methods' => TP_DELETE,'callback' => [$this, 'delete_item'],
                        'permission_callback' => [$this, 'delete_item_permissions_check'],
                        'args' => ['force' => ['type' => 'boolean','default' => false,
                            'description' => $this->__( 'Required to be true, as terms do not support trashing.' ),
                        ],],
                    ],'allow_batch' => $this->_allow_batch,
                    'schema'=> [$this, 'get_public_item_schema'],
                ]
            );
        }//82
        public function get_items_permissions_check( $request ):string{
            $tax_obj = $this->_get_taxonomy( $this->_taxonomy );
            if ( ! $tax_obj || ! $this->check_is_taxonomy_allowed( $this->_taxonomy ) ) return false;
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( $tax_obj->cap->edit_terms ) ) {
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit terms in this taxonomy.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            return true;
        }//155
        public function get_items(TP_REST_Request $request ):string{
            $registered = $this->get_collection_params();
            $parameter_mappings = ['exclude' => 'exclude','include' => 'include','order' => 'order',
                'orderby' => 'orderby','post' => 'post','hide_empty' => 'hide_empty',
                'per_page' => 'number','search' => 'search','slug' => 'slug',];
            $prepared_args = array( 'taxonomy' => $this->_taxonomy );
            foreach ( $parameter_mappings as $api_param => $wp_param ) {
                if ( isset( $registered[ $api_param ], $request[ $api_param ] ) )
                    $prepared_args[ $wp_param ] = $request[ $api_param ];
            }
            if ( isset( $prepared_args['orderby'], $request['orderby'] ) ) {
                $orderby_mappings = ['include_slugs' => 'slug__in',];
                if ( isset( $orderby_mappings[ $request['orderby'] ] ) )
                    $prepared_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
            }
            if ( isset( $registered['offset'] ) && ! empty( $request['offset'] ) )
                $prepared_args['offset'] = $request['offset'];
            else $prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
            $taxonomy_obj = $this->_get_taxonomy( $this->_taxonomy );
            if ( $taxonomy_obj->hierarchical && isset( $registered['parent'], $request['parent'] ) ) {
                if ( 0 === $request['parent'] ) $prepared_args['parent'] = 0;
                else if ( $request['parent'] ) $prepared_args['parent'] = $request['parent'];
            }
            $prepared_args = $this->_apply_filters( "rest_{$this->_taxonomy}_query", $prepared_args, $request );
            if ( ! empty( $prepared_args['post'] ) ) {
                $query_result = $this->_tp_get_object_terms( $prepared_args['post'], $this->_taxonomy, $prepared_args );
                $prepared_args['object_ids'] = $prepared_args['post'];
            } else $query_result = $this->_get_terms( $prepared_args );
            $count_args = $prepared_args;
            unset( $count_args['number'], $count_args['offset'] );
            $total_terms = $this->_tp_count_terms( $count_args );
            if ( ! $total_terms ) $total_terms = 0;
            $response = [];
            foreach ((array) $query_result as $term ) {
                $data       = $this->prepare_item_for_response( $term, $request );
                $response[] = $this->prepare_response_for_collection( $data );
            }
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $per_page = (int) $prepared_args['number'];
            $page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );
            $response->header( 'X-TP-Total', (int) $total_terms );
            $max_pages = ceil( $total_terms / $per_page );
            $response->header( 'X-TP-TotalPages', (int) $max_pages );
            $base = $this->_add_query_arg( $this->_url_encode_deep( $request->get_query_params() ), $this->_rest_url( $this->_namespace . '/' . $this->_rest_base ) );
            if ( $page > 1 ) {
                $prev_page = $page - 1;
                if ( $prev_page > $max_pages ) $prev_page = $max_pages;
                $prev_link = $this->_add_query_arg( 'page', $prev_page, $base );
                $response->link_header( 'prev', $prev_link );
            }
            if ( $max_pages > $page ) {
                $next_page = $page + 1;
                $next_link = $this->_add_query_arg( 'page', $next_page, $base );
                $response->link_header( 'next', $next_link );
            }
            return $response;
        }//181
        protected function _get_term_term( $id ){
            $error = new TP_Error('rest_term_invalid',$this->__( 'Term does not exist.' ),['status' => NOT_FOUND]);
            if ( ! $this->check_is_taxonomy_allowed( $this->_taxonomy ) ) return $error;
            if ( (int) $id <= 0 ) return $error;
            $term = $this->_get_term( (int) $id, $this->_taxonomy );
            if (($term instanceof TP_Term && empty($term)) || $term->taxonomy !== $this->_taxonomy ) return $error;
            return $term;
        }//335
        public function get_item_permissions_check( $request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( 'edit_term', $term->term_id ) )
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit this term.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//366
        public function get_item( $request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            $response = $this->prepare_item_for_response( $term, $request );
            return $this->_rest_ensure_response( $response );
        }//392
        public function create_item_permissions_check( $request ):string{
            if ( ! $this->check_is_taxonomy_allowed( $this->_taxonomy ) ) return false;
            $taxonomy_obj = $this->_get_taxonomy( $this->_taxonomy );
            if ( ( $this->_is_taxonomy_hierarchical( $this->_taxonomy )
                    && ! $this->_current_user_can( $taxonomy_obj->cap->edit_terms ) )
                || ( ! $this->_is_taxonomy_hierarchical( $this->_taxonomy )
                    && ! $this->_current_user_can( $taxonomy_obj->cap->assign_terms ) ) )
                return new TP_Error('rest_cannot_create',
                    $this->__( 'Sorry, you are not allowed to create terms in this taxonomy.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//411
        public function create_item(TP_REST_Request $request ):string{
            if ( isset( $request['parent'] ) ) {
                if ( ! $this->_is_taxonomy_hierarchical( $this->_taxonomy ) )
                    return new TP_Error('rest_taxonomy_not_hierarchical',
                        $this->__( 'Cannot set parent term, taxonomy is not hierarchical.' ),
                        ['status' => BAD_REQUEST]
                    );
                $parent = $this->_get_term( (int) $request['parent'], $this->_taxonomy );
                if ( ! $parent )
                    return new TP_Error('rest_term_invalid',
                        $this->__( 'Parent term does not exist.' ),
                        ['status' => BAD_REQUEST]
                    );
            }
            $prepared_term = $this->_prepare_item_for_database( $request );
            $term = $this->_tp_insert_term( $this->_tp_slash( $prepared_term->name ), $this->_taxonomy, $this->_tp_slash( (array) $prepared_term ) );
            if ( $this->_init_error( $term ) ) {
                $term_id = $term->get_error_data( 'term_exists' );
                if ( $term_id ) {
                    $existing_term = $this->_get_term( $term_id, $this->_taxonomy );
                    if($existing_term  instanceof TP_Term ){
                        $term->add_data( $existing_term->term_id, 'term_exists' );
                    }
                    $term->add_data(['status' => BAD_REQUEST,'term_id' => $term_id,]);
                }
                return $term;
            }
            $term = $this->_get_term( $term['term_id'], $this->_taxonomy );
            $this->_do_action( "rest_insert_{$this->_taxonomy}", $term, $request, true );
            $schema = $this->get_item_schema();
            if ($term  instanceof TP_Term && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $term->term_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $fields_update = $this->_update_additional_fields_for_object( $term, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( "rest_after_insert_{$this->_taxonomy}", $term, $request, true );
            $response = $this->prepare_item_for_response( $term, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }

            $response->set_status( 201 );
            $response->header( 'Location', $this->_rest_url( $this->_namespace . '/' . $this->_rest_base . '/' . $term->term_id ) );
            return $response;
        }//441
        public function update_item_permissions_check($request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! $this->_current_user_can( 'edit_term', $term->term_id ) )
                return new TP_Error('rest_cannot_update',
                    $this->__( 'Sorry, you are not allowed to edit this term.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//557
        public function update_item(TP_REST_Request $request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( isset( $request['parent'] ) ) {
                if ( ! $this->_is_taxonomy_hierarchical( $this->_taxonomy ) )
                    return new TP_Error('rest_taxonomy_not_hierarchical',
                        $this->__( 'Cannot set parent term, taxonomy is not hierarchical.' ),
                        ['status' => BAD_REQUEST]);
                $parent = $this->_get_term( (int) $request['parent'], $this->_taxonomy );
                if ( ! $parent )
                    return new TP_Error('rest_term_invalid',
                        $this->__( 'Parent term does not exist.' ),
                        ['status' => BAD_REQUEST]);
            }
            $prepared_term = $this->_prepare_item_for_database( $request );
            if ( ! empty( $prepared_term ) ) {
                $update = $this->_tp_update_term( $term->term_id, $term->taxonomy, $this->_tp_slash( (array) $prepared_term ) );
                if ( $this->_init_error( $update ) ) return $update;
            }
            $term = $this->_get_term( $term->term_id, $this->_taxonomy );
            if ( $term instanceof TP_Term )
            $this->_do_action( "rest_insert_{$this->_taxonomy}", $term, $request, false );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $term->term_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $fields_update = $this->_update_additional_fields_for_object( $term, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( "rest_after_insert_{$this->_taxonomy}", $term, $request, false );
            $response = $this->prepare_item_for_response( $term, $request );
            return $this->_rest_ensure_response( $response );
        }//583
        public function delete_item_permissions_check( $request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! $this->_current_user_can( 'delete_term', $term->term_id ) )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete this term.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//658
        public function delete_item(TP_REST_Request $request ):string{
            $term = $this->_get_term_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            $force = isset( $request['force'] ) ? (bool) $request['force'] : false;
            if ( ! $force )
                return new TP_Error('rest_trash_not_supported',
                    sprintf( $this->__( "Terms do not support trashing. Set '%s' to delete." ), 'force=true' ),
                    ['status' => NOT_IMPLEMENTED]);
            $request->set_param( 'context', 'view' );
            $previous = $this->prepare_item_for_response( $term, $request );


            $retval = $this->_tp_delete_term( $term->term_id, $term->taxonomy );
            if ( ! $retval )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The term cannot be deleted.' ),
                    ['status' => INTERNAL_SERVER_ERROR]
                );
            $response = new TP_REST_Response();
            $previous_data = null;
            if($previous instanceof TP_REST_Response ){
                $previous_data = $previous->get_data();
            }
            $response->set_data(['deleted' => true,'previous' => $previous_data,] );
            $this->_do_action( "rest_delete_{$this->_taxonomy}", $term, $response, $request );
            return $response;
        }//684
        protected function _prepare_item_for_database( $request ):string{
            $prepared_term = new \stdClass;
            $schema = $this->get_item_schema();
            if ( isset( $request['name'] ) && ! empty( $schema['properties']['name'] ) )
                $prepared_term->name = $request['name'];
            if ( isset( $request['slug'] ) && ! empty( $schema['properties']['slug'] ) )
                $prepared_term->slug = $request['slug'];
            if ( isset( $request['taxonomy'] ) && ! empty( $schema['properties']['taxonomy'] ) )
                $prepared_term->taxonomy = $request['taxonomy'];
            if ( isset( $request['description'] ) && ! empty( $schema['properties']['description'] ) )
                $prepared_term->description = $request['description'];
            if ( isset( $request['parent'] ) && ! empty( $schema['properties']['parent'] ) ) {
                $parent_term_id   = 0;
                $requested_parent = (int) $request['parent'];
                if ( $requested_parent ) {
                    $parent_term = $this->_get_term( $requested_parent, $this->_taxonomy );
                    if ( $parent_term instanceof TP_Term ){
                        $parent_term_id = $parent_term->term_id;
                    }
                }
                $prepared_term->parent = $parent_term_id;
            }
            return $this->_apply_filters( "rest_pre_insert_{$this->_taxonomy}", $prepared_term, $request );
        }//753
        public function prepare_item_for_response( $item, $request ):string{
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( in_array( 'id', $fields, true ) ) $data['id'] = (int) $item->term_id;
            if ( in_array( 'count', $fields, true ) )$data['count'] = (int) $item->count;
            if ( in_array( 'description', $fields, true ) ) $data['description'] = $item->description;
            if ( in_array( 'link', $fields, true ) ) $data['link'] = $this->_get_term_link( $item );
            if ( in_array( 'name', $fields, true ) ) $data['name'] = $item->name;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $item->slug;
            if ( in_array( 'taxonomy', $fields, true ) ) $data['taxonomy'] = $item->taxonomy;
            if ( in_array( 'parent', $fields, true ) ) $data['parent'] = (int) $item->parent;
            if ( in_array( 'meta', $fields, true ) ) $data['meta'] = $this->_meta->get_value( $item->term_id, $request );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links( $this->_prepare_links( $item ) );
            return $this->_apply_filters( "rest_prepare_{$this->_taxonomy}", $response, $item, $request );
        }//815
        protected function _prepare_links( $term ):string{
            $base  = $this->_namespace . '/' . $this->_rest_base;
            $links = [
                'self' => ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $term->term_id ),],
                'collection' => ['href' => $this->_rest_url( $base ),],
                'about' => ['href' => $this->_rest_url( sprintf( 'tp/v1/taxonomies/%s', $this->_taxonomy ) ),],
            ];
            if ( $term->parent ) {
                $parent_term = $this->_get_term( (int) $term->parent, $term->taxonomy );
                if ($parent_term instanceof TP_Term && $parent_term )
                    $links['up'] = ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $parent_term->term_id ),
                        'embeddable' => true,];
            }
            $taxonomy_obj = $this->_get_taxonomy( $term->taxonomy );
            if ( empty( $taxonomy_obj->object_type ) ) return $links;
            $post_type_links = [];
            foreach ( $taxonomy_obj->object_type as $type ) {
                $rest_path = $this->_rest_get_route_for_post_type_items( $type );
                if ( empty( $rest_path ) ) continue;
                $post_type_links[] = ['href' => $this->_add_query_arg( $this->_rest_base, $term->term_id, $this->_rest_url( $rest_path ) ),];
            }
            if ( ! empty( $post_type_links ) )
                $links['https://api.w.org/post_type'] = $post_type_links;
            return $links;
        }//893
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'post_tag' === $this->_taxonomy ? 'tag' : $this->_taxonomy,'type' => 'object',
                'properties' => [
                    'id' => ['description' => $this->__( 'Unique identifier for the term.' ),
                        'type' => 'integer','context' => ['view','embed','edit'],'readonly' => true,
                    ],
                    'count' => ['description' => $this->__( 'Number of published posts for the term.' ),
                        'type' => 'integer','context' => ['view','edit'],'readonly' => true,
                    ],
                    'description' => ['description' => $this->__( 'HTML description of the term.' ),
                        'type'=> 'string','context'=> ['view','edit'],
                    ],
                    'link' => ['description' => $this->__( 'URL of the term.' ),
                        'type' => 'string','format' => 'uri','context' => ['view','embed','edit'],'readonly' => true,
                    ],
                    'name' => ['description' => $this->__( 'HTML title for the term.' ),
                        'type' => 'string','context' => ['view','embed','edit'],
                        'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],'required'=> true,
                    ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the term unique to its type.' ),
                        'type' => 'string','context' => ['view','embed','edit'],
                        'arg_options' => ['sanitize_callback' =>[$this, 'sanitize_slug'],],
                    ],
                    'taxonomy' => ['description' => $this->__( 'Type attribution for the term.' ),
                        'type' => 'string','enum' => [ $this->_taxonomy],'context' => ['view','embed','edit'],'readonly' => true,
                    ],
                ]
            ];
            $taxonomy = $this->_get_taxonomy( $this->_taxonomy );
            if ( $taxonomy->hierarchical )
                $schema['properties']['parent'] = ['description' => $this->__( 'The parent term ID.' ),
                    'type' => 'integer','context' => ['view','edit'],];
            $schema['properties']['meta'] = $this->_meta->get_field_schema();
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//952
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $taxonomy     = $this->_get_taxonomy( $this->_taxonomy );
            $query_params['context']['default'] = 'view';
            $query_params['exclude'] = ['description' => $this->__( 'Ensure result set excludes specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            $query_params['include'] = ['description' => $this->__( 'Limit result set to specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            if ( ! $taxonomy->hierarchical ) $query_params['offset'] = [
                'description' => $this->__( 'Offset the result set by a specific number of items.' ), 'type'=> 'integer',];
            $query_params['order'] = ['description' => $this->__( 'Order sort attribute ascending or descending.' ),
                'type' => 'string','default' => 'asc','enum' =>['asc','desc',]];
            $query_params['orderby'] = ['description' => $this->__( 'Sort collection by term attribute.' ),
                'type' => 'string','default' => 'name',
                'enum'=>['id','include','name','slug','include_slugs','term_group','description','count',]];
            $query_params['hide_empty'] = ['description' => $this->__( 'Whether to hide terms not assigned to any posts.' ),
                'type' => 'boolean','default' => false,];
            if ( $taxonomy->hierarchical ) $query_params['parent'] = ['description' => $this->__( 'Limit result set to terms assigned to a specific parent.' ),
                'type' => 'integer',];
            $query_params['post'] = ['description' => $this->__( 'Limit result set to terms assigned to a specific post.' ),
                'type' => 'integer','default' => null,];
            $query_params['slug'] = ['description' => $this->__( 'Limit result set to terms with one or more specific slugs.' ),
                'type'=> 'array','items'=> ['type' => 'string',],];
            return $this->_apply_filters( "rest_{$this->_taxonomy}_collection_params", $query_params, $taxonomy );
        }//1037
        protected function check_is_taxonomy_allowed( $taxonomy ):string {
            $taxonomy_obj = $this->_get_taxonomy( $taxonomy );
            if ( $taxonomy_obj && ! empty( $taxonomy_obj->show_in_rest ) ) return true;
            return false;
        }//1147
    }
}else die;