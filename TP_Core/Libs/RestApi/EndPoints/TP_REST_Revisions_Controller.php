<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    class TP_REST_Revisions_Controller extends TP_REST_Controller{
        public function __construct($parent_post_type ){
            $this->_parent_post_type  = $parent_post_type;
            $this->_rest_base         = 'revisions';
            $_post_type_object        = $this->_get_post_type_object( $parent_post_type );
            $post_type_object = null;
            if( $_post_type_object instanceof TP_Post_Type ){
                $post_type_object = $_post_type_object;
            }
            $this->_parent_base       = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
            $this->_namespace         = ! empty( $post_type_object->rest_namespace ) ? $post_type_object->rest_namespace : 'tp/v1';
            $this->_parent_controller = $post_type_object->get_rest_controller();
            if ( ! $this->_parent_controller ) $this->_parent_controller = new TP_REST_Posts_Controller( $parent_post_type );
        }//50
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_parent_base . '/(?P<parent>[\d]+)/' . $this->_rest_base,
                ['args' => ['parent' => [
                            'description' => $this->__( 'The ID for the parent of the revision.' ),
                            'type' => 'integer',], ],
                    ['methods' => TP_GET,'callback' => [$this, 'get_items'],
                        'permission_callback' => [$this, 'get_items_permissions_check'],
                        'args' => $this->get_collection_params(),
                    ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_parent_base . '/(?P<parent>[\d]+)/' . $this->_rest_base . '/(?P<id>[\d]+)',
                ['args' => ['parent' => [
                            'description' => $this->__( 'The ID for the parent of the revision.' ),
                            'type'        => 'integer',
                        ],'id' => [
                            'description' => $this->__( 'Unique identifier for the revision.' ),
                            'type'        => 'integer',
                        ],],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item' ],
                        'permission_callback' => [ $this, 'get_item_permissions_check'],
                        'args' => ['context' => $this->get_context_param( ['default' => 'view'] ), ],
                    ],
                    ['methods' => TP_DELETE,'callback' => [$this, 'delete_item'],
                        'permission_callback' => [$this, 'delete_item_permissions_check'],
                        'args' => ['force' => ['type' => 'boolean','default' => false,
                                'description' => $this->__( 'Required to be true, as revisions do not support trashing.' ), ],
                        ],
                    ],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//70
        protected function _get_parent( $parent ){
            $error = new TP_Error('rest_post_invalid_parent',
                $this->__( 'Invalid post parent ID.' ), ['status' => NOT_FOUND]);
            if ( (int) $parent <= 0 ) return $error;
            $parent = $this->_get_post( (int) $parent );
            if( $parent instanceof \stdClass ){} //todo
            if ( empty( $parent ) || empty( $parent->ID ) || $this->_parent_post_type !== $parent->post_type )
                return $error;
            return $parent;
        }//140
        public function get_items_permissions_check( $request ):string{
            $parent = $this->_get_parent( $request['parent'] );
            if ( $this->_init_error( $parent ) ) return $parent;
            if ( ! $this->_current_user_can( 'edit_post', $parent->ID ) )
                return new TP_Error('rest_cannot_read',
                    $this->__( 'Sorry, you are not allowed to view revisions of this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }
        protected function _get_revision( $id ){
            $error = new TP_Error('rest_post_invalid_id',
                $this->__( 'Invalid revision ID.' ), ['status' => NOT_FOUND]);
            if ( (int) $id <= 0 ) return $error;
            $revision = $this->_get_post( (int) $id );
            if( $revision instanceof \stdClass ){} //todo
            if ( empty( $revision ) || empty( $revision->ID ) || 'revision' !== $revision->post_type )
                return $error;
            return $revision;
        }
        public function get_items(TP_REST_Request $request ):string{
            $parent = $this->_get_parent( $request['parent'] );
            if ( $this->_init_error( $parent ) ) return $parent;
            if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) )
                return new TP_Error('rest_no_search_term_defined',
                    $this->__( 'You need to define a search term to order by relevance.' ),
                    ['status' => BAD_REQUEST]);
            if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) )
                return new TP_Error('rest_orderby_include_missing_include',
                    $this->__( 'You need to define an include parameter to order by include.' ),
                    ['status' => BAD_REQUEST]);
            if ( $this->_tp_revisions_enabled( $parent ) ) {
                $registered = $this->get_collection_params();
                $args = ['post_parent' => $parent->ID,'post_type' => 'revision','post_status' => 'inherit',
                    'posts_per_page' => -1,'orderby' => 'date ID','order' => 'DESC','suppress_filters' => true,];
                $parameter_mappings = ['exclude' => 'post__not_in','include' => 'post__in','offset' => 'offset',
                    'order' => 'order','orderby' => 'orderby','page' => 'paged','per_page' => 'posts_per_page','search' => 's',];
                foreach ( $parameter_mappings as $api_param => $wp_param ) {
                    if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) $args[ $wp_param ] = $request[ $api_param ];
                }
                $args       = $this->_apply_filters( 'rest_revision_query', $args, $request );
                $query_args = $this->_prepare_items_query( $args, $request );
                $revisions_query = new TP_Query();
                $revisions       = $revisions_query->query_main( $query_args );
                $offset          = isset( $query_args['offset'] ) ? (int) $query_args['offset'] : 0;
                $page            = (int) $query_args['paged'];
                $total_revisions = $revisions_query->found_posts;
                if ( $total_revisions < 1 ) {
                    unset( $query_args['paged'], $query_args['offset'] );
                    $count_query = new TP_Query();
                    $count_query->query_main( $query_args );
                    $total_revisions = $count_query->found_posts;
                }
                if ( $revisions_query->query_vars['posts_per_page'] > 0 )
                    $max_pages = ceil( $total_revisions / (int) $revisions_query->query_vars['posts_per_page'] );
                else $max_pages = $total_revisions > 0 ? 1 : 0;
                if ( $total_revisions > 0 ) {
                    if ( $offset >= $total_revisions )
                        return new TP_Error('rest_revision_invalid_offset_number',
                            $this->__( 'The offset number requested is larger than or equal to the number of available revisions.' ),
                            ['status' => BAD_REQUEST]);
                    elseif ( ! $offset && $page > $max_pages )
                        return new TP_Error('rest_revision_invalid_page_number',
                            $this->__( 'The page number requested is larger than the number of pages available.' ),
                            ['status' => BAD_REQUEST]);
                }
            } else {
                $revisions       = [];
                $total_revisions = 0;
                $max_pages       = 0;
                $page            = (int) $request['page'];
            }
            $response = [];
            foreach ( $revisions as $revision ) {
                $data       = $this->prepare_item_for_response( $revision, $request );
                $response[] = $this->prepare_response_for_collection( $data );
            }
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->header( 'X-TP-Total',$total_revisions );
            $response->header( 'X-TP-TotalPages', (int) $max_pages );
            $request_params = $request->get_query_params();
            $base = $this->_add_query_arg( $this->_url_encode_deep( $request_params ), $this->_rest_url( sprintf( '%s/%s/%d/%s', $this->_namespace, $this->_parent_base, $request['parent'], $this->_rest_base ) ) );
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
        }//218
        public function get_item_permissions_check( $request ):string{
            return $this->get_items_permissions_check( $request );
        }//367
        public function get_item( $request ):string{
            $parent = $this->_get_parent( $request['parent'] );
            if ( $this->_init_error( $parent ) ) return $parent;
            $revision = $this->_get_revision( $request['id'] );
            if ( $this->_init_error( $revision ) ) return $revision;
            $response = $this->prepare_item_for_response( $revision, $request );
            return $this->_rest_ensure_response( $response );
        }//379
        public function delete_item_permissions_check( $request ):string{
            $parent = $this->_get_parent( $request['parent'] );
            if ( $this->_init_error( $parent ) )return $parent;
            $parent_post_type = $this->_get_post_type_object( $parent->post_type );
            if ($parent_post_type || ! $this->_current_user_can( 'delete_post', $parent->ID ))//not sure ?
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete revisions of this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            $revision = $this->_get_revision( $request['id'] );
            if ( $this->_init_error( $revision ) ) return $revision;
            $response = $this->get_items_permissions_check( $request );
            if ( ! $response || $this->_init_error( $response ) ) return $response;
            if ( ! $this->_current_user_can( 'delete_post', $revision->ID ) )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete this revision.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//402
        public function delete_item( $request ):string{
            $revision = $this->_get_revision( $request['id'] );
            if ( $this->_init_error( $revision ) ) return $revision;
            $force = isset( $request['force'] ) ? (bool) $request['force'] : false;
            if ( ! $force )
                return new TP_Error('rest_trash_not_supported',
                    sprintf( $this->__( "Revisions do not support trashing. Set '%s' to delete." ), 'force=true' ),
                    ['status' => NOT_IMPLEMENTED]
                );
            $previous = $this->prepare_item_for_response( $revision, $request );
            $result = $this->_tp_delete_post( $request['id'], true );
            $this->_do_action( 'rest_delete_revision', $result, $request );
            if ( ! $result )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The post cannot be deleted.' ),
                    ['status' => INTERNAL_SERVER_ERROR]);
            $response = new TP_REST_Response();
            $get_err_data = null;
            if($previous  instanceof TP_Error ){
                $get_err_data = $previous->get_error_data();
            }
            $response->set_data(['deleted' => true,'previous' => $get_err_data,]);
            return $response;
        }//447
        protected function _prepare_items_query( $prepared_args = array(), $request = null ):string{
            $query_args = [];
            foreach ( $prepared_args as $key => $value )
                $query_args[ $key ] = $this->_apply_filters( "rest_query_var-{$key}", $value ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
            if ( isset( $query_args['orderby'], $request['orderby'] ) ) {
                $orderby_mappings = ['id' => 'ID','include' => 'post__in','slug' => 'post_name','include_slugs' => 'post_name__in',];
                if ( isset( $orderby_mappings[ $request['orderby'] ] ) )
                    $query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
            }
            return $query_args;
        }//509
        public function prepare_item_for_response( $item, $request ):string{
            $post = $item;
            $this->_tp_post = $post;
            $this->_setup_postdata( $post );
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( in_array( 'author', $fields, true ) ) $data['author'] = (int) $post->post_author;
            if ( in_array( 'date', $fields, true ) )
                $data['date'] = $this->_prepare_date_response( $post->post_date_gmt, $post->post_date );
            if ( in_array( 'date_gmt', $fields, true ) )
                $data['date_gmt'] = $this->_prepare_date_response( $post->post_date_gmt );
            if ( in_array( 'id', $fields, true ) ) $data['id'] = $post->ID;
            if ( in_array( 'modified', $fields, true ) )
                $data['modified'] = $this->_prepare_date_response( $post->post_modified_gmt, $post->post_modified );
            if ( in_array( 'modified_gmt', $fields, true ) )
                $data['modified_gmt'] = $this->_prepare_date_response( $post->post_modified_gmt );
            if ( in_array( 'parent', $fields, true ) ) $data['parent'] = (int) $post->post_parent;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $post->post_name;
            if ( in_array( 'guid', $fields, true ) )
                $data['guid'] = ['rendered' => $this->_apply_filters( 'get_the_guid', $post->guid, $post->ID ),
                    'raw' => $post->guid,];
            if ( in_array( 'title', $fields, true ) )
                $data['title'] = ['raw'=> $post->post_title, 'rendered' => $this->_get_the_title( $post->ID ),];
            if ( in_array( 'content', $fields, true ) )
                $data['content'] = ['raw' => $post->post_content,'rendered' => $this->_apply_filters( 'the_content', $post->post_content ),];
            if ( in_array( 'excerpt', $fields, true ) )
                $data['excerpt'] = ['raw' => $post->post_excerpt,'rendered' => $this->_prepare_excerpt_response( $post->post_excerpt, $post ),];
            $context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data     = $this->_add_additional_fields_to_object( $data, $request );
            $data     = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            if ( ! empty( $data['parent'] ) )
                $response->add_link( 'parent', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_parent_base, $data['parent'] ) ) );
            return $this->_apply_filters( 'rest_prepare_revision', $response, $post, $request );
        }//544
        protected function _prepare_date_response( $date_gmt, $date = null ){
            if ( '0000-00-00 00:00:00' === $date_gmt ) return null;
            if ( isset( $date ) ) return $this->_mysql_to_rfc3339( $date );
            return $this->_mysql_to_rfc3339( $date_gmt );
        }//650
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => "{$this->_parent_post_type}-revision", 'type' => 'object',
                'properties' => [
                    'author' => ['description' => $this->__( 'The ID for the author of the revision.' ),
                        'type' => 'integer','context' => ['view', 'edit', 'embed'],],
                    'date' => ['description' => $this->__( "The date the revision was published, in the site's timezone." ),
                        'type' => 'string','format' => 'date-time','context' => ['view', 'edit', 'embed'],],
                    'date_gmt' => ['description' => $this->__( 'The date the revision was published, as GMT.' ),
                        'type' => 'string','format' => 'date-time','context' => ['view', 'edit'],],
                    'guid' => ['description' => $this->__( 'GUID for the revision, as it exists in the database.' ),
                        'type' => 'string','context' => ['view', 'edit'],],
                    'id' => ['description' => $this->__( 'Unique identifier for the revision.' ),
                        'type' => 'integer','context' => ['view', 'edit', 'embed'],],
                    'modified' => ['description' => $this->__( "The date the revision was last modified, in the site's timezone." ),
                        'type' => 'string','format' => 'date-time','context' => ['view', 'edit'],],
                    'modified_gmt' => ['description' => $this->__( 'The date the revision was last modified, as GMT.' ),
                        'type' => 'string','format' => 'date-time','context' => ['view', 'edit'],],
                    'parent' => ['description' => $this->__( 'The ID for the parent of the revision.' ),
                        'type' => 'integer','context' => ['view', 'edit', 'embed'], ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the revision unique to its type.' ),
                        'type' => 'string','context' => ['view', 'edit', 'embed'],],
                ],
            ];
            $item_schema = null;
            if($this->_parent_controller instanceof TP_REST_Controller ){
                $item_schema = $this->_parent_controller->get_item_schema();
            }//todo
            $parent_schema = $item_schema;
            if ( ! empty( $parent_schema['properties']['title'] ) )
                $schema['properties']['title'] = $parent_schema['properties']['title'];
            if ( ! empty( $parent_schema['properties']['content'] ) )
                $schema['properties']['content'] = $parent_schema['properties']['content'];
            if ( ! empty( $parent_schema['properties']['excerpt'] ) )
                $schema['properties']['excerpt'] = $parent_schema['properties']['excerpt'];
            if ( ! empty( $parent_schema['properties']['guid'] ) )
                $schema['properties']['guid'] = $parent_schema['properties']['guid'];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//669
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $query_params['context']['default'] = 'view';
            unset( $query_params['per_page']['default'] );
            $query_params['exclude'] = array(
                'description' => $this->__( 'Ensure result set excludes specific IDs.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
                'default'     => array(),
            );
            $query_params['include'] = array(
                'description' => $this->__( 'Limit result set to specific IDs.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
                'default'     => array(),
            );
            $query_params['offset'] = array(
                'description' => $this->__( 'Offset the result set by a specific number of items.' ),
                'type'        => 'integer',
            );
            $query_params['order'] = array(
                'description' => $this->__( 'Order sort attribute ascending or descending.' ),
                'type'        => 'string',
                'default'     => 'desc',
                'enum'        => array( 'asc', 'desc' ),
            );
            $query_params['orderby'] = array(
                'description' => $this->__( 'Sort collection by object attribute.' ),
                'type'        => 'string',
                'default'     => 'date',
                'enum'        => array(
                    'date',
                    'id',
                    'include',
                    'relevance',
                    'slug',
                    'include_slugs',
                    'title',
                ),
            );
            return $query_params;
        }//762 //todo shrink
        protected function _prepare_excerpt_response( $excerpt, $post ){
            $excerpt = $this->_apply_filters( 'the_excerpt', $excerpt, $post );
            if ( empty( $excerpt ) ) return '';
            return $excerpt;
        }//826
    }
}else die;