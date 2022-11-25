<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 15:01
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Admin\Traits\PostAdmin\_post_admin_03;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Post\TP_Post_Type;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\RestApi\Fields\TP_REST_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Posts_Controller extends TP_REST_Controller {
        use _post_admin_03;
        protected $_post_type;
        protected $_meta;
        protected $_password_check_passed = [];
        protected $_allow_batch = ['v1' => true];
        public function __construct( $post_type ){}//57
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [
                    [
                        'methods' => TP_GET,'callback' => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'get_items_permissions_check' ),
                        'args' => $this->get_collection_params(),
                    ],
                    ['methods' => TP_POST,
                        'callback' => array( $this, 'create_item' ),
                        'permission_callback' => array( $this, 'create_item_permissions_check' ),
                        'args' => $this->get_endpoint_args_for_item_schema( TP_POST ),],
                    'allow_batch' => $this->_allow_batch,
                    'schema'      => [$this, 'get_public_item_schema'],
                ]
            );
            $schema = $this->get_item_schema();
            $get_item_args = ['context' => $this->get_context_param( ['default' => 'view'] ),];
            if ( isset( $schema['properties']['password'] ) ) {
                $get_item_args['password'] = ['description' => $this->__( 'The password for the post if it is password protected.' ),'type' => 'string',];
            }
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<id>[\d]+)',
                [
                    'args' => [
                        'id' => ['description' => $this->__( 'Unique identifier for the post.' ),'type' => 'integer',],
                    ],
                    [
                        'methods' => TP_GET,
                        'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => $get_item_args,
                    ],
                    [
                        'methods' => TP_EDITABLE,
                        'callback' => [$this, 'update_item'],
                        'permission_callback' => [$this, 'update_item_permissions_check'],
                        'args' => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ],
                    [
                        'methods' => TP_DELETE,
                        'callback' => [$this, 'delete_item'],
                        'permission_callback' => [$this, 'delete_item_permissions_check'],
                        'args' => [
                            'force' => [
                                'type' => 'boolean',
                                'default' => false,
                                'description' => $this->__( 'Whether to bypass Trash and force deletion.' ),
                            ],
                        ],
                    ],
                    'allow_batch' => $this->_allow_batch,
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//73
        public function get_items_permissions_check( $request ):string{
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( $post_type->cap->edit_posts ) ) {
                return new TP_Error( 'rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit posts in this post type.' ),
                    array( 'status' => $this->_rest_authorization_required_code() )
                );
            }
            return true;
        }//154
        public function check_password_required( $required, $post ) {
            if ( ! $required ) return $required;
            $post = $this->_get_post( $post );
            if ( ! $post ) return $required;
            if ( ! empty( $this->_password_check_passed[ $post->ID ] ) )
                return false;
            return ! $this->_current_user_can( 'edit_post', $post->ID );
        }//182
        public function get_items(TP_REST_Request $request ):string{
            if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
                return new TP_Error('rest_no_search_term_defined',
                    $this->__( 'You need to define a search term to order by relevance.' ),
                    ['status' => BAD_REQUEST] );
            }
            if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
                return new TP_Error('rest_orderby_include_missing_include',
                    $this->__( 'You need to define an include parameter to order by include.' ),
                    ['status' => BAD_REQUEST]);
            }
            $registered = $this->get_collection_params();
            $args       = [];
            $parameter_mappings = [
                'author' => 'author__in','author_exclude' => 'author__not_in','exclude' => 'post__not_in','include' => 'post__in',
                'menu_order' => 'menu_order','offset' => 'offset','order' => 'order','orderby' => 'orderby','page' => 'paged',
                'parent' => 'post_parent__in','parent_exclude' => 'post_parent__not_in','search' => 's','slug' => 'post_name__in','status' => 'post_status',
            ];
            foreach ( $parameter_mappings as $api_param => $tp_param ) {
                if ( isset( $registered[ $api_param ], $request[ $api_param ] ) )
                    $args[ $tp_param ] = $request[ $api_param ];
            }
            $args['date_query'] = [];
            if ( isset( $registered['before'], $request['before'] ) )
                $args['date_query'][] = ['before' => $request['before'],'column' => 'post_date',];
            if ( isset( $registered['modified_before'], $request['modified_before'] ) )
                $args['date_query'][] = ['before' => $request['modified_before'],'column' => 'post_modified',];
            if ( isset( $registered['after'], $request['after'] ) )
                $args['date_query'][] = ['after'  => $request['after'],'column' => 'post_date',];
            if ( isset( $registered['modified_after'], $request['modified_after'] ) )
                $args['date_query'][] = ['after'  => $request['modified_after'],'column' => 'post_modified',];
            if ( isset( $registered['per_page'] ) ) $args['posts_per_page'] = $request['per_page'];
            if ( isset( $registered['sticky'], $request['sticky'] ) ) {
                $sticky_posts = $this->_get_option( 'sticky_posts', [] );
                if ( ! is_array( $sticky_posts ) ) $sticky_posts = [];
                if ( $request['sticky'] ) {
                    $args['post__in'] = $args['post__in'] ? array_intersect( $sticky_posts, $args['post__in'] ) : $sticky_posts;
                    if ( ! $args['post__in'] )  $args['post__in'] = [0];
                } elseif ( $sticky_posts )
                    $args['post__not_in'] = array_merge( $args['post__not_in'], $sticky_posts );
            }
            $args = $this->__prepare_tax_query( $args, $request );
            $args['post_type'] = $this->_post_type;
            $args       = $this->_apply_filters( "rest_{$this->_post_type}_query", $args, $request );
            $query_args = $this->_prepare_items_query( $args, $request );
            $posts_query  = new TP_Query();
            $query_result = $posts_query->query_main( $query_args );
            if ( 'edit' === $request['context'] )
                $this->_add_filter( 'post_password_required', array( $this, 'check_password_required' ), 10, 2 );
            $posts = [];
            foreach ( $query_result as $post ) {
                if ( ! $this->check_read_permission( $post ) ) continue;
                $data    = $this->prepare_item_for_response( $post, $request );
                $posts[] = $this->prepare_response_for_collection( $data );
            }
            if ( 'edit' === $request['context'] )
                $this->_remove_filter( 'post_password_required', array( $this, 'check_password_required' ) );
            $page        = (int) $query_args['paged'];
            $total_posts = $posts_query->found_posts;
            if ( $total_posts < 1 ) {
                unset( $query_args['paged'] );
                $count_query = new TP_Query();
                $count_query->query_main( $query_args );
                $total_posts = $count_query->found_posts;
            }
            $max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );
            if ( $page > $max_pages && $total_posts > 0 ) {
                return new TP_Error(
                    'rest_post_invalid_page_number',
                    $this->__( 'The page number requested is larger than the number of pages available.' ),
                    ['status' => BAD_REQUEST]
                );
            }
            $_response = $this->_rest_ensure_response( $posts );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->header( 'X-TP-Total',$total_posts );
            $response->header( 'X-TP-TotalPages', (int) $max_pages );
            $request_params = $request->get_query_params();
            $base = $this->_add_query_arg( $this->_url_encode_deep( $request_params ), $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ) );
            if ( $page > 1 ) {
                $prev_page = $page - 1;
                if ( $prev_page > $max_pages )  $prev_page = $max_pages;
                $prev_link = $this->_add_query_arg( 'page', $prev_page, $base );
                $response->link_header( 'prev', $prev_link );
            }
            if ( $max_pages > $page ) {
                $next_page = $page + 1;
                $next_link = $this->_add_query_arg( 'page', $next_page, $base );
                $response->link_header( 'next', $next_link );
            }
            return $response;
        }//209
        protected function _get_rest_post( $id ){
            $error = new TP_Error('rest_post_invalid_id',$this->__( 'Invalid post ID.' ),['status' => 404]);
            if ( (int) $id <= 0 ) return $error;
            $post = $this->_get_post( (int) $id );
            if (($post instanceof \stdClass && empty($post)) || empty( $post->ID ) || $this->_post_type !== $post->post_type )
                return $error;
            return $post;
        }//444
        public function get_item_permissions_check( $request ):string{
            $post = $this->_get_rest_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            if ( 'edit' === $request['context'] && $post && ! $this->_check_update_permission( $post ) ) {
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            if ($post && !empty($request['password']) && !hash_equals($post->post_password, $request['password'])) return new TP_Error('rest_post_incorrect_password',
                $this->__( 'Incorrect post password.' ),['status' => FORBIDDEN]
            );
            if ( 'edit' === $request['context'] )
                $this->_add_filter( 'post_password_required', array( $this, 'check_password_required' ), 10, 2 );
            if ( $post ) return $this->check_read_permission( $post );
            return true;
        }//471
        public function can_access_password_content( $post, $request ){
            if ( empty( $post->post_password ) ) return false;
            if ('edit' === $request['context'] && $this->_current_user_can( 'edit_post', $post->ID ))
                return true;
            if ( empty( $request['password'] ) ) return false;
            return hash_equals( $post->post_password, $request['password'] );
        }//520
        public function get_item( $request ):string{
            $post = $this->_get_rest_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            $data     = $this->prepare_item_for_response( $post, $request );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            if ( $this->_is_post_type_viewable( $this->_get_post_type_object( $post->post_type ) ) )
                $response->link_header( 'alternate', $this->_get_permalink( $post->ID ), ['type' => 'text/html'] );
            return $response;
        }//554
        public function create_item_permissions_check( $request ):string{
            if ( ! empty( $request['id'] ) )
                return new TP_Error('rest_post_exists',$this->__( 'Cannot create existing post.' ),[ 'status' => BAD_REQUEST]);
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( ! empty( $request['author'] ) && $this->_get_current_user_id() !== $request['author'] && ! $this->_current_user_can( $post_type->cap->edit_others_posts ) )
                return new TP_Error( 'rest_cannot_edit_others',
                    $this->__( 'Sorry, you are not allowed to create posts as this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( ! empty( $request['sticky'] ) && ! $this->_current_user_can( $post_type->cap->edit_others_posts ) && ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                return new TP_Error('rest_cannot_assign_sticky',
                    $this->__( 'Sorry, you are not allowed to make posts sticky.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( ! $this->_current_user_can( $post_type->cap->create_posts ) ) {
                return new TP_Error('rest_cannot_create',
                    $this->__( 'Sorry, you are not allowed to create posts as this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            if ( ! $this->_check_assign_terms_permission( $request ) ) {
                return new TP_Error('rest_cannot_assign_term',
                    $this->__( 'Sorry, you are not allowed to assign the provided terms.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            return true;
        }//578
        public function create_item( TP_REST_Request $request ):string{
            if ( ! empty( $request['id'] ) )
                return new TP_Error('rest_post_exists',
                    $this->__( 'Cannot create existing post.' ),['status' => BAD_REQUEST]
                );
            $prepared_post = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_post ) ) return $prepared_post;
            $prepared_post->post_type = $this->_post_type;
            $_post_id = $this->_tp_insert_post( $this->_tp_slash( (array) $prepared_post ), true, false );
            $post_id = null;
            if($_post_id instanceof TP_Error){
                $post_id = $_post_id;
            }
            if ( $this->_init_error( $post_id ) ) {
                if ( 'db_insert_error' === $post_id->get_error_code() )
                    $post_id->add_data( ['status' => INTERNAL_SERVER_ERROR] );
                else $post_id->add_data(['status' => BAD_REQUEST]);
                return $post_id;
            }
            $post = $this->_get_post( $post_id );
            $this->_do_action( "rest_insert_{$this->_post_type}", $post, $request, true );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['sticky'] ) ) {
                if ( ! empty( $request['sticky'] ) ) $this->_stick_post( $post_id );
                else  $this->_unstick_post( $post_id );
            }
            if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) )
                $this->_handle_featured_media( $request['featured_media'], $post_id );
            if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) )
                $this->_set_post_format( $post, $request['format'] );
            if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) )
                $this->handle_template( $request['template'], $post_id, true );
            $terms_update = $this->_handle_terms( $post_id, $request );
            if ( $this->_init_error( $terms_update ) ) return $terms_update;
            if ($this->_meta instanceof TP_REST_Meta_Fields && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $post_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $post          = $this->_get_post( $post_id );
            $fields_update = $this->_update_additional_fields_for_object( $post, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( "rest_after_insert_{$this->_post_type}", $post, $request, true );
            $this->_tp_after_insert_post( $post, false, null );
            $response = $this->prepare_item_for_response( $post, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( CREATED );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $post_id ) ) );
            return $response;
        }//632
        public function update_item_permissions_check( $request ):string{
            $post = $this->_get_rest_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( $post && ! $this->_check_update_permission( $post ) )
                return new TP_Error('rest_cannot_edit',
                    $this->__( 'Sorry, you are not allowed to edit this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( ! empty( $request['author'] ) && $this->_get_current_user_id() !== $request['author'] && ! $this->_current_user_can( $post_type->cap->edit_others_posts ) )
                return new TP_Error('rest_cannot_edit_others',
                    $this->__( 'Sorry, you are not allowed to update posts as this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( ! empty( $request['sticky'] ) && ! $this->_current_user_can( $post_type->cap->edit_others_posts ) && ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                return new TP_Error('rest_cannot_assign_sticky',
                    $this->__( 'Sorry, you are not allowed to make posts sticky.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( ! $this->_check_assign_terms_permission( $request ) )
                return new TP_Error('rest_cannot_assign_term',
                    $this->__( 'Sorry, you are not allowed to assign the provided terms.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//766
        public function update_item(TP_REST_Request $request ):string{
            //if($this->_meta instanceof TP_REST_Meta_Fields){}
            $valid_check = $this->_get_post( $request['id'] );
            if ( $this->_init_error( $valid_check ) ) return $valid_check;
            $post_before = $this->_get_post( $request['id'] );
            $post        = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $post ) ) return $post;
            $_post_id = $this->_tp_update_post( $this->_tp_slash( (array) $post ), true, false );
            $post_id = null;
            if($_post_id instanceof TP_Error){
                $post_id = $_post_id;
            }
            if ( $this->_init_error( $post_id ) ) {
                if ( 'db_update_error' === $post_id->get_error_code() )
                    $post_id->add_data(['status' => INTERNAL_SERVER_ERROR]);
                else  $post_id->add_data(['status' => BAD_REQUEST]);
                return $post_id;
            }
            $post = $this->_get_post( $post_id );
            $_post_id = null;
            if( $post instanceof \stdClass ){
                $_post_id = $post->ID;
            }
            $this->_do_action( "rest_insert_{$this->_post_type}", $post, $request, false );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) )
                $this->_set_post_format( $post, $request['format'] );
            if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) )
                $this->_handle_featured_media( $request['featured_media'], $post_id );
            if ( ! empty( $schema['properties']['sticky'] ) && isset( $request['sticky'] ) ) {
                if ( ! empty( $request['sticky'] ) ) $this->_stick_post( $post_id );
                else $this->_unstick_post( $post_id );
            }
            if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) )
                $this->handle_template( $request['template'], $_post_id );
            $terms_update = $this->_handle_terms( $_post_id, $request );
            if ( $this->_init_error( $terms_update ) ) return $terms_update;
            if ($this->_meta instanceof TP_REST_Meta_Fields && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $_post_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $post          = $this->_get_post( $post_id );
            $fields_update = $this->_update_additional_fields_for_object( $post, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            if ( 'attachment' === $this->_post_type ) {
                $response = $this->prepare_item_for_response( $post, $request );
                return $this->_rest_ensure_response( $response );
            }
            $this->_do_action( "rest_after_insert_{$this->_post_type}", $post, $request, false );
            $this->_tp_after_insert_post( $post, true, $post_before );
            $response = $this->prepare_item_for_response( $post, $request );
            return $this->_rest_ensure_response( $response );
        }//817
        public function delete_item_permissions_check( $request ):string{
            $post = $this->_get_rest_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            if ( $post && ! $this->_check_delete_permission( $post ) )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete this post.' ),
                    [ 'status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//916
        public function delete_item( $request ):string{
            if($request instanceof TP_REST_Request){}
            $post = $this->_get_rest_post( $request['id'] );
            if ( $this->_init_error( $post ) ) return $post;
            $id    = $post->ID;
            $force = (bool) $request['force'];
            $supports_trash = ( EMPTY_TRASH_DAYS > 0 );
            if ( 'attachment' === $post->post_type )
                $supports_trash = $supports_trash && __MEDIA_TRASH;
            $supports_trash = $this->_apply_filters( "rest_{$this->_post_type}_trashable", $supports_trash, $post );
            if ( ! $this->_check_delete_permission( $post ) ) {
                return new TP_Error('rest_user_cannot_delete_post',
                    $this->__( 'Sorry, you are not allowed to delete this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            $request->set_param( 'context', 'edit' );
            if ( $force ) {
                $previous = $this->prepare_item_for_response( $post, $request );
                if($previous instanceof TP_REST_Response){}//
                $result   = $this->_tp_delete_post( $id, true );
                $response = new TP_REST_Response();
                $response->set_data( ['deleted'  => true,'previous' => $previous->get_data(),]);
            } else {
                if ( ! $supports_trash )
                    return new TP_Error('rest_trash_not_supported',/* translators: %s: force=true */
                        sprintf( $this->__( "The post does not support trashing. Set '%s' to delete." ), 'force=true' ),
                        ['status' => NOT_IMPLEMENTED ]
                    );
                if ( 'trash' === $post->post_status )
                    return new TP_Error('rest_already_trashed',
                        $this->__( 'The post has already been deleted.' ),
                        ['status' => GONE]
                    );
                $result   = $this->_tp_trash_post( $id );
                $post     = $this->_get_post( $id );
                $response = $this->prepare_item_for_response( $post, $request );
            }
            if ( ! $result )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The post cannot be deleted.' ),
                    array( 'status' => INTERNAL_SERVER_ERROR )
                );
            $this->_do_action( "rest_delete_{$this->_post_type}", $post, $response, $request );
            return $response;
        }//941
        protected function _prepare_items_query($request = null,...$prepared_args):string{
            $query_args = [];
            foreach ( $prepared_args as $key => $value )
                $query_args[ $key ] = $this->_apply_filters( "rest_query_var_{$key}", $value );
            if ( 'post' !== $this->_post_type || ! isset( $query_args['ignore_sticky_posts'] ) )
                $query_args['ignore_sticky_posts'] = true;
            if ( isset( $query_args['orderby'],$request['orderby'] ) ) {
                $orderby_mappings = ['id' => 'ID','include' => 'post__in','slug' => 'post_name','include_slugs' => 'post_name__in',];
                if ( isset( $orderby_mappings[ $request['orderby'] ] ) )
                    $query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
            }
            return $query_args;
        }//1064
        protected function _prepare_date_response( $date_gmt, $date = null ){
            if ( isset( $date ) ) return $this->_mysql_to_rfc3339( $date );
            if ( '0000-00-00 00:00:00' === $date_gmt ) return null;
            return $this->_mysql_to_rfc3339( $date_gmt );
        }//1111
        protected function _prepare_item_for_database(TP_REST_Request $request ):string{
            $prepared_post  = new \stdClass();
            $current_status = '';
            if ( isset( $request['id'] ) ) {
                $existing_post = $this->_get_rest_post( $request['id'] );
                if ( $this->_init_error( $existing_post ) ) return $existing_post;
                $prepared_post->ID = $existing_post->ID;
                $current_status    = $existing_post->post_status;
            }
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
                if ( is_string( $request['title'] ) )
                    $prepared_post->post_title = $request['title'];
                elseif ( ! empty( $request['title']['raw'] ) )
                    $prepared_post->post_title = $request['title']['raw'];
            }
            if ( ! empty( $schema['properties']['content'] ) && isset( $request['content'] ) ) {
                if ( is_string( $request['content'] ) )
                    $prepared_post->post_content = $request['content'];
                elseif ( isset( $request['content']['raw'] ) )
                    $prepared_post->post_content = $request['content']['raw'];
            }
            if ( ! empty( $schema['properties']['excerpt'] ) && isset( $request['excerpt'] ) ) {
                if ( is_string( $request['excerpt'] ) )
                    $prepared_post->post_excerpt = $request['excerpt'];
                elseif ( isset( $request['excerpt']['raw'] ) )
                    $prepared_post->post_excerpt = $request['excerpt']['raw'];
            }
            if ( empty( $request['id'] ) )
                $prepared_post->post_type = $this->_post_type;
            else $prepared_post->post_type = $this->_get_post_type( $request['id'] );
            $post_type = $this->_get_post_type_object( $prepared_post->post_type );
            if (! empty( $schema['properties']['status'] ) && isset( $request['status'] ) &&
                ( ! $current_status || $current_status !== $request['status'] )
            ) {
                $status = $this->_handle_status_param( $request['status'], $post_type );
                if ( $this->_init_error( $status ) ) return $status;
                $prepared_post->post_status = $status;
            }
            if ( ! empty( $schema['properties']['date'] ) && ! empty( $request['date'] ) ) {
                $current_date = isset( $prepared_post->ID ) ? $this->_get_post( $prepared_post->ID )->post_date : false;
                $date_data    = $this->_rest_get_date_with_gmt( $request['date'] );
                if ( ! empty( $date_data ) && $current_date !== $date_data[0] ) {
                    @list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
                    $prepared_post->edit_date                                        = true;
                }
            } elseif ( ! empty( $schema['properties']['date_gmt'] ) && ! empty( $request['date_gmt'] ) ) {
                $current_date = isset( $prepared_post->ID ) ? $this->_get_post( $prepared_post->ID )->post_date_gmt : false;
                $date_data    = $this->_rest_get_date_with_gmt( $request['date_gmt'], true );
                if ( ! empty( $date_data ) && $current_date !== $date_data[1] ) {
                    @list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
                    $prepared_post->edit_date                                        = true;
                }
            }
            if (
                ( ! empty( $schema['properties']['date_gmt'] ) && $request->has_param( 'date_gmt' ) && null === $request['date_gmt'] ) ||
                ( ! empty( $schema['properties']['date'] ) && $request->has_param( 'date' ) && null === $request['date'] )
            ) {
                $prepared_post->post_date_gmt = null;
                $prepared_post->post_date     = null;
            }
            if ( ! empty( $schema['properties']['slug'] ) && isset( $request['slug'] ) )
                $prepared_post->post_name = $request['slug'];
            if ( ! empty( $schema['properties']['author'] ) && ! empty( $request['author'] ) ) {
                $post_author = (int) $request['author'];
                if ( $this->_get_current_user_id() !== $post_author ) {
                    $user_obj = $this->_get_user_data( $post_author );
                    if ( ! $user_obj )
                        return new TP_Error('rest_invalid_author',$this->__('Invalid author ID.'),['status' => BAD_REQUEST]);
                }
                $prepared_post->post_author = $post_author;
            }
            if ( ! empty( $schema['properties']['password'] ) && isset( $request['password'] ) ) {
                $prepared_post->post_password = $request['password'];
                if ( '' !== $request['password'] ) {
                    if ( ! empty( $schema['properties']['sticky'] ) && ! empty( $request['sticky'] ) )
                        return new TP_Error('rest_invalid_field',
                            $this->__( 'A post can not be sticky and have a password.' ),
                            ['status' => BAD_REQUEST]
                        );
                    if ( ! empty( $prepared_post->ID ) && $this->_is_sticky( $prepared_post->ID ) )
                        return new TP_Error('rest_invalid_field',
                            $this->__( 'A sticky post can not be password protected.' ),
                            ['status' => BAD_REQUEST]
                        );
                }
            }
            if (!empty($schema['properties']['sticky']) && !empty($request['sticky']) && !empty($prepared_post->ID) && $this->_post_password_required($prepared_post->ID)) return new TP_Error('rest_invalid_field',
                $this->__( 'A password protected post can not be set to sticky.' ),
                ['status' => BAD_REQUEST]
            );
            if ( ! empty( $schema['properties']['parent'] ) && isset( $request['parent'] ) ) {
                if ( 0 === (int) $request['parent'] ) {
                    $prepared_post->post_parent = 0;
                } else {
                    $parent = $this->_get_post( (int) $request['parent'] );
                    $parent_id = null;
                    if( $parent instanceof \stdClass ){
                        $parent_id = $parent->ID;
                    }
                    if ( empty( $parent ) )
                        return new TP_Error('rest_post_invalid_id',
                            $this->__( 'Invalid post parent ID.' ),
                            ['status' => BAD_REQUEST]
                        );
                    $prepared_post->post_parent = $parent_id;
                }
            }
            if ( ! empty( $schema['properties']['menu_order'] ) && isset( $request['menu_order'] ) )
                $prepared_post->menu_order = (int) $request['menu_order'];
            if ( ! empty( $schema['properties']['comment_status'] ) && ! empty( $request['comment_status'] ) )
                $prepared_post->comment_status = $request['comment_status'];
            if ( ! empty( $schema['properties']['ping_status'] ) && ! empty( $request['ping_status'] ) )
                $prepared_post->ping_status = $request['ping_status'];
            if ( ! empty( $schema['properties']['template'] ) ) $prepared_post->page_template = null;
            return $this->_apply_filters( "rest_pre_insert_{$this->_post_type}", $prepared_post, $request );
        }//1134
        public function check_status( $status,TP_REST_Request $request, $param ){
            if ( $request['id'] ) {
                $post = $this->_get_rest_post( $request['id'] );
                if ( $post->post_status === $status && ! $this->_init_error( $post ))
                    return true;
            }
            $args = $request->get_attributes()['args'][ $param ];
            return $this->_rest_validate_value_from_schema( $status, $args, $param );
        }//1362
        protected function _handle_status_param( $post_status, $post_type ) {
            switch ( $post_status ) {
                case 'draft':
                case 'pending':
                    break;
                case 'private':
                    if ( ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                        return new TP_Error('rest_cannot_publish',
                            $this->__( 'Sorry, you are not allowed to create private posts in this post type.' ),
                            ['status' => $this->_rest_authorization_required_code()]
                        );
                    break;
                case 'publish':
                case 'future':
                    if ( ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                        return new TP_Error(
                            'rest_cannot_publish',
                            $this->__( 'Sorry, you are not allowed to publish posts in this post type.' ),
                            ['status' => $this->_rest_authorization_required_code()]
                        );
                    break;
                default:
                    if ( ! $this->_get_post_status_object( $post_status ) )
                        $post_status = 'draft';
                    break;
            }
            return $post_status;
        }//1385
        protected function _handle_featured_media( $featured_media, $post_id ){
            $featured_media = (int) $featured_media;
            if ( $featured_media ) {
                $result = $this->_set_post_thumbnail( $post_id, $featured_media );
                if ( $result ) return true;
                else return new TP_Error('rest_invalid_featured_media',
                        $this->__( 'Invalid featured media ID.' ),
                        ['status' => BAD_REQUEST]
                     );
            } else return $this->_delete_post_thumbnail( $post_id );
        }//1429
        public function check_template( $template, $request ){
            if ( ! $template ) return true;
            if ( $request['id'] ) {
                $post             = $this->_get_post( $request['id'] );
                $current_template = $this->_get_page_template_slug( $request['id'] );
            } else {
                $post             = null;
                $current_template = '';
            }
            if ( $template === $current_template ) return true;
            $allowed_templates = $this->_tp_get_theme()->get_page_templates( $post, $this->_post_type );
            if ( isset( $allowed_templates[ $template ] ) ) return true;
            return new TP_Error('rest_invalid_param',/* translators: 1: Parameter, 2: List of valid values. */
                sprintf( $this->__( '%1$s is not one of %2$s.' ), 'template', implode( ', ', array_keys( $allowed_templates ) ) )
            );
        }//1458
        public function handle_template( $template, $post_id, $validate = false ): void{
            if ( $validate && ! array_key_exists( $template, $this->_tp_get_theme()->get_page_templates( $this->_get_post( $post_id ))))
                $template = '';
            $this->_update_post_meta( $post_id, '_tp_page_template', $template );
        }//1501
        protected function _handle_terms( $post_id, $request ) {
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                if ( ! isset( $request[ $base ] ) ) continue;
                $result = $this->_tp_set_object_terms( $post_id, $request[ $base ], $taxonomy->name );
                if ( $this->_init_error( $result ) ) return $result;
            }
            return null;
        }//1519
        protected function _check_assign_terms_permission( $request ): bool{
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                if ( ! isset( $request[ $base ] ) ) continue;
                foreach ( (array) $request[ $base ] as $term_id ) {
                    if ( ! $this->_get_term( $term_id, $taxonomy->name ) ) continue;
                    if ( ! $this->_current_user_can( 'assign_term', (int) $term_id ) )  return false;
                }
            }
            return true;
        }//1545
        protected function _check_is_post_type_allowed( $post_type ): bool{
            if ( ! is_object( $post_type ) )
                $post_type = $this->_get_post_type_object( $post_type );
            if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) )
                return true;
            return false;
        }//1577
        public function check_read_permission( $post ):bool{
            $post_type = $this->_get_post_type_object( $post->post_type );
            if ( ! $this->_check_is_post_type_allowed( $post_type ) )
                return false;
            if ( 'publish' === $post->post_status || $this->_current_user_can( 'read_post', $post->ID ) )
                return true;
            $post_status_obj = $this->_get_post_status_object( $post->post_status );
            if ( $post_status_obj && $post_status_obj->public ) return true;
            if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
                $parent = $this->_get_post( $post->post_parent );
                if ( $parent ) return $this->check_read_permission( $parent );
            }
            if ( 'inherit' === $post->post_status ) return true;
            return false;
        }//1599
        protected function _check_update_permission( $post ) {
            $post_type = $this->_get_post_type_object( $post->post_type );
            if ( ! $this->_check_is_post_type_allowed( $post_type ) )
                return false;
            return $this->_current_user_can( 'edit_post', $post->ID );
        }//1642
        protected function _check_create_permission( $post ) {
            $_post_type = $this->_get_post_type_object( $post->post_type );
            $post_type = null;
            if($_post_type instanceof TP_Post_Type){
                $post_type = $_post_type;
            }
            if ( ! $this->_check_is_post_type_allowed( $post_type ) )
                return false;
            return $this->_current_user_can( $post_type->cap->create_posts );
        }//1660
        protected function _check_delete_permission( $post ) {
            $post_type = $this->_get_post_type_object( $post->post_type );
            if ( ! $this->_check_is_post_type_allowed( $post_type ) ) return false;
            return $this->_current_user_can( 'delete_post', $post->ID );
        }//1678
        public function prepare_item_for_response( $item,TP_REST_Request $request ):string{
            $post = $item;
            $this->_tp_post['post'] = $post;
            $this->_setup_postdata( $post );
            $fields = $this->get_fields_for_response( $request );
            $data = [];
            if ( $this->_rest_is_field_included( 'id', $fields ) )
                $data['id'] = $post->ID;
            if ( $this->_rest_is_field_included( 'date', $fields ) )
                $data['date'] = $this->_prepare_date_response( $post->post_date_gmt, $post->post_date );
            if ( $this->_rest_is_field_included( 'date_gmt', $fields ) ) {
                if ( '0000-00-00 00:00:00' === $post->post_date_gmt )
                    $post_date_gmt = $this->_get_gmt_from_date( $post->post_date );
                else $post_date_gmt = $post->post_date_gmt;
                $data['date_gmt'] = $this->_prepare_date_response( $post_date_gmt );
            }
            if ( $this->_rest_is_field_included( 'guid', $fields ) )
                $data['guid'] = ['rendered' => $this->_apply_filters( 'get_the_guid', $post->guid, $post->ID ),'raw' => $post->guid,];
            if ( $this->_rest_is_field_included( 'modified', $fields ) )
                $data['modified'] = $this->_prepare_date_response( $post->post_modified_gmt, $post->post_modified );
            if ( $this->_rest_is_field_included( 'modified_gmt', $fields ) ) {
                if ( '0000-00-00 00:00:00' === $post->post_modified_gmt )
                    $post_modified_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( $this->_get_option( 'gmt_offset' ) * 3600 ) );
                else  $post_modified_gmt = $post->post_modified_gmt;
                $data['modified_gmt'] = $this->_prepare_date_response( $post_modified_gmt );
            }
            if ( $this->_rest_is_field_included( 'password', $fields ) ) $data['password'] = $post->post_password;
            if ( $this->_rest_is_field_included( 'slug', $fields ) ) $data['slug'] = $post->post_name;
            if ( $this->_rest_is_field_included( 'status', $fields ) ) $data['status'] = $post->post_status;
            if ( $this->_rest_is_field_included( 'type', $fields ) ) $data['type'] = $post->post_type;
            if ( $this->_rest_is_field_included( 'link', $fields ) ) $data['link'] = $this->_get_permalink( $post->ID );
            if ( $this->_rest_is_field_included( 'title', $fields ) ) $data['title'] = array();
            if ( $this->_rest_is_field_included( 'title.raw', $fields ) ) $data['title']['raw'] = $post->post_title;
            if ( $this->_rest_is_field_included( 'title.rendered', $fields ) ) {
                $this->_add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
                $data['title']['rendered'] = $this->_get_the_title( $post->ID );
                $this->_remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
            }
            $has_password_filter = false;
            if ( $this->can_access_password_content( $post, $request ) ) {
                $this->_password_check_passed[ $post->ID ] = true;
                $this->_add_filter( 'post_password_required', array( $this, 'check_password_required' ), 10, 2 );
                $has_password_filter = true;
            }
            if ( $this->_rest_is_field_included( 'content', $fields ) ) $data['content'] = [];
            if ( $this->_rest_is_field_included( 'content.raw', $fields ) ) $data['content']['raw'] = $post->post_content;
            if ( $this->_rest_is_field_included( 'content.rendered', $fields ) )
                $data['content']['rendered'] = $this->_post_password_required( $post ) ? '' : $this->_apply_filters( 'the_content', $post->post_content );
            if ( $this->_rest_is_field_included( 'content.protected', $fields ) )
                $data['content']['protected'] = (bool) $post->post_password;
            if ( $this->_rest_is_field_included( 'content.block_version', $fields ) )
                $data['content']['block_version'] = $this->_block_version( $post->post_content );
            if ( $this->_rest_is_field_included( 'excerpt', $fields ) ) {
                $excerpt = $this->_apply_filters( 'get_the_excerpt', $post->post_excerpt, $post );
                $excerpt = $this->_apply_filters( 'the_excerpt', $excerpt );
                $data['excerpt'] = ['raw' => $post->post_excerpt, 'rendered'  => $this->_post_password_required( $post ) ? '' : $excerpt,
                    'protected' => (bool) $post->post_password,];
            }
            if ( $has_password_filter ) $this->_remove_filter( 'post_password_required', array( $this, 'check_password_required' ) );
            if ( $this->_rest_is_field_included( 'author', $fields ) )
                $data['author'] = (int) $post->post_author;
            if ( $this->_rest_is_field_included( 'featured_media', $fields ) )
                $data['featured_media'] = (int) $this->_get_post_thumbnail_id( $post->ID );
            if ( $this->_rest_is_field_included( 'parent', $fields ) ) $data['parent'] = (int) $post->post_parent;
            if ( $this->_rest_is_field_included( 'menu_order', $fields ) ) $data['menu_order'] = (int) $post->menu_order;
            if ( $this->_rest_is_field_included( 'comment_status', $fields ) )
                $data['comment_status'] = $post->comment_status;
            if ( $this->_rest_is_field_included( 'ping_status', $fields ) ) $data['ping_status'] = $post->ping_status;
            if ( $this->_rest_is_field_included( 'sticky', $fields ) ) $data['sticky'] = $this->_is_sticky( $post->ID );
            if ( $this->_rest_is_field_included( 'template', $fields ) ) {
                $template = $this->_get_page_template_slug( $post->ID );
                if ( $template ) $data['template'] = $template;
                else $data['template'] = '';
            }
            if ( $this->_rest_is_field_included( 'format', $fields ) ) {
                $data['format'] = $this->_get_post_format( $post->ID );
                if ( empty( $data['format'] ) )  $data['format'] = 'standard';
            }
            if ($this->_meta instanceof TP_REST_Meta_Fields && $this->_rest_is_field_included( 'meta', $fields ) ) $data['meta'] = $this->_meta->get_value( $post->ID, $request );
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                if ( $this->_rest_is_field_included( $base, $fields ) ) {
                    $terms         = $this->_get_the_terms( $post, $taxonomy->name );
                    $data[ $base ] = $terms ? array_values( $this->_tp_list_pluck( $terms, 'term_id' ) ) : array();
                }
            }
            $post_type_obj = $this->_get_post_type_object( $post->post_type );
            if ($post_type_obj->public && $this->_is_post_type_viewable( $post_type_obj )) {
                $permalink_template_requested = $this->_rest_is_field_included( 'permalink_template', $fields );
                $generated_slug_requested     = $this->_rest_is_field_included( 'generated_slug', $fields );
                if ( $permalink_template_requested || $generated_slug_requested ) {
                    if ( ! function_exists( 'get_sample_permalink' ) ) {
                        //$this->_init_post();
                        TP_Post::get_instance($post->ID);//will see?
                    }
                    $sample_permalink = $this->_get_sample_permalink( $post->ID, $post->post_title, '' );
                    if ( $permalink_template_requested ) $data['permalink_template'] = $sample_permalink[0];
                    if ( $generated_slug_requested ) $data['generated_slug'] = $sample_permalink[1];
                }
            }
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $links = $this->_prepare_links( $post );
            $response->add_links( $links );
            if ( ! empty( $links['self']['href'] ) ) {
                $actions = $this->_get_available_actions( $post, $request );
                $self = $links['self']['href'];
                foreach ( $actions as $rel ) $response->add_link( $rel, $self );
            }
            return $this->_apply_filters( "rest_prepare_{$this->_post_type}", $response, $post, $request );
        }//1698
        public function protected_title_format(): string{
            return '%s';
        }//1973
        protected function _prepare_links( $post ):string{
            $base = sprintf( '%s/%s', $this->_namespace, $this->_rest_base );
            $links = [
                'self' => ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $post->ID ),],
                'collection' => ['href' => $this->_rest_url( $base ),],
                'about' => ['href' => $this->_rest_url( 'tp/v1/types/' . $this->_post_type ),],
            ];
            if ( ! empty( $post->post_author ) && ( in_array( $post->post_type, array( 'post', 'page' ), true ) || $this->_post_type_supports( $post->post_type, 'author' ) )
                )
                $links['author'] = ['href' => $this->_rest_url( 'tp/v1/users/' . $post->post_author ),'embeddable' => true,];
            if ( in_array( $post->post_type, array( 'post', 'page' ), true ) || $this->_post_type_supports( $post->post_type, 'comments' ) ) {
                $replies_url = $this->_rest_url( 'wp/v2/comments' );
                $replies_url = $this->_add_query_arg( 'post', $post->ID, $replies_url );
                $links['replies'] = ['href' => $replies_url,'embeddable' => true,];
            }
            if ( in_array( $post->post_type, array( 'post', 'page' ), true ) || $this->_post_type_supports( $post->post_type, 'revisions' ) ) {
                $revisions       = $this->_tp_get_post_revisions( $post->ID, array( 'fields' => 'ids' ) );
                $revisions_count = count( $revisions );
                $links['version-history'] = [
                    'href'  => $this->_rest_url( $this->_trailingslashit( $base ) . $post->ID . '/revisions' ),
                    'count' => $revisions_count,
                ];
                if ( $revisions_count > 0 ) {
                    $last_revision = array_shift( $revisions );
                    $links['predecessor-version'] = [
                        'href' => $this->_rest_url( $this->_trailingslashit( $base ) . $post->ID . '/revisions/' . $last_revision ),
                        'id'   => $last_revision,
                    ];
                }
            }
            $post_type_obj = $this->_get_post_type_object( $post->post_type );
            if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) )
                $links['up'] = ['href' => $this->_rest_url( $this->_rest_get_route_for_post( $post->post_parent ) ),
                    'embeddable' => true,];
            $featured_media = $this->_get_post_thumbnail_id( $post->ID );
            if ( $featured_media ) {
                $image_url = $this->_rest_url( $this->_rest_get_route_for_post( $featured_media ) );
                $links['https://api.w.org/featuredmedia'] = ['href' => $image_url, 'embeddable' => true,];
            }
            if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ), true ) ) {
                $attachments_url = $this->_rest_url( $this->_rest_get_route_for_post_type_items( 'attachment' ) );
                $attachments_url = $this->_add_query_arg( 'parent', $post->ID, $attachments_url );
                $links['https://api.w.org/attachment'] = ['href' => $attachments_url,];
            }
            $taxonomies = $this->_get_object_taxonomies( $post->post_type );
            if ( ! empty( $taxonomies ) ) {
                $links['https://api.w.org/term'] = array();
                foreach ( $taxonomies as $tax ) {
                    $taxonomy_route = $this->_rest_get_route_for_taxonomy_items( $tax );
                    if ( empty( $taxonomy_route ) ) continue;
                    $terms_url = $this->_add_query_arg('post',$post->ID,$this->_rest_url( $taxonomy_route));
                    $links['https://api.w.org/term'][] = ['href' => $terms_url,'taxonomy' => $tax,'embeddable' => true,];
                }
            }
            return $links;
        }//1985
        protected function _get_available_actions( $post, $request ):string{
            if ( 'edit' !== $request['context'] ) return [];
            $rels = [];
            $post_type = $this->_get_post_type_object( $post->post_type );
            if ( 'attachment' !== $this->_post_type && $this->_current_user_can( $post_type->cap->publish_posts ) )
                $rels[] = 'https://api.w.org/action-publish';
            if ( $this->_current_user_can( 'unfiltered_html' ) )
                $rels[] = 'https://api.w.org/action-unfiltered-html';
            if (('post' === $post_type->name) && $this->_current_user_can($post_type->cap->edit_others_posts) && $this->_current_user_can($post_type->cap->publish_posts)) $rels[] = 'https://api.w.org/action-sticky';
            if ($this->_post_type_supports($post_type->name, 'author') && $this->_current_user_can($post_type->cap->edit_others_posts)) $rels[] = 'https://api.w.org/action-assign-author';
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $tax ) {
                $tax_base   = ! empty( $tax->rest_base ) ? $tax->rest_base : $tax->name;
                $create_cap = $this->_is_taxonomy_hierarchical( $tax->name ) ? $tax->cap->edit_terms : $tax->cap->assign_terms;
                if ( $this->_current_user_can( $create_cap ) ) $rels[] = 'https://api.w.org/action-create-' . $tax_base;
                if ( $this->_current_user_can( $tax->cap->assign_terms ) ) $rels[] = 'https://api.w.org/action-assign-' . $tax_base;
            }
            return $rels;
        }//2105
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#','title' => $this->_post_type,'type' => 'object',
                'properties' => [
                    'date' => [ 'description' => $this->__( "The date the post was published, in the site's timezone." ),
                        'type' => ['string', 'null'],'format' => 'date-time','context' => ['view', 'edit', 'embed'],
                    ],
                    'date_gmt' => [ 'description' => $this->__( 'The date the post was published, as GMT.' ),
                        'type' => ['string', 'null',],'format' => 'date-time','context' => ['view', 'edit'],
                    ],
                    'guid' => [
                        'description' => $this->__( 'The globally unique identifier for the post.' ),
                        'type' => 'object','context' => ['view', 'edit'],'readonly' => true,
                        'properties' => [
                            'raw'=> ['description' => $this->__( 'GUID for the post, as it exists in the database.' ),
                                'type' => 'string','context' => ['edit'],'readonly' => true,],
                            'rendered' => ['description' => $this->__( 'GUID for the post, transformed for display.' ),
                                'type' => 'string', 'context' => ['view', 'edit'], 'readonly' => true,],
                        ],
                    ],
                    'id' => ['description' => $this->__( 'Unique identifier for the post.' ),
                        'type' => 'integer','context' => ['view', 'edit', 'embed'],'readonly' => true,
                    ],
                    'link' => ['description' => $this->__( 'URL to the post.' ),'type' => 'string',
                        'format' => 'uri','context' => ['view', 'edit', 'embed'],'readonly' => true,],
                    'modified'=> ['description' => $this->__( "The date the post was last modified, in the site's timezone." ),
                        'type'=> 'string','format' => 'date-time','context' => ['view', 'edit'],'readonly' => true,],
                    'modified_gmt' => ['description' => $this->__( 'The date the post was last modified, as GMT.' ),
                        'type' => 'string','format' => 'date-time','context' => ['view', 'edit'],'readonly' => true,],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the post unique to its type.' ),
                        'type' => 'string','context' => ['view', 'edit', 'embed'],
                        'arg_options' => ['sanitize_callback' => [$this, 'sanitize_slug'],],],
                    'status' => ['description' => $this->__( 'A named status for the post.' ),'type' => 'string',
                        'enum' => array_keys( $this->_get_post_stati( ['internal' => false ] ) ),'context' => ['view','edit'],
                        'arg_options' => ['validate_callback' => [$this, 'check_status'],],],
                    'type' => ['description' => $this->__( 'Type of post.' ),
                        'type'=> 'string','context'=> ['view', 'edit', 'embed'],'readonly'=> true,],
                    'password' => ['description' => $this->__( 'A password to protect access to the content and excerpt.' ),
                        'type' => 'string','context' => ['edit'],],
                ],
            ];
            $post_type_obj = $this->_get_post_type_object( $this->_post_type );
            if ($post_type_obj->public && $this->_is_post_type_viewable( $post_type_obj )) {
                $schema['properties']['permalink_template'] = ['description' => $this->__( 'Permalink template for the post.' ),
                    'type' => 'string','context' => ['edit'],'readonly' => true,];
                $schema['properties']['generated_slug'] = ['description' => $this->__( 'Slug automatically generated from the post title.' ),
                    'type'  => 'string','context'  => ['edit'],'readonly' => true,];
            }
            if ( $post_type_obj->hierarchical ) {
                $schema['properties']['parent'] = ['description' => $this->__( 'The ID for the parent of the post.' ),
                    'type' => 'integer','context' => ['view', 'edit'],
                ];
            }
            $post_type_attributes = ['title','editor','author','excerpt','thumbnail',
                'comments','revisions','page-attributes','post-formats','custom-fields',];
            $fixed_schemas = [
                'post' => ['title','editor','author','excerpt','thumbnail',
                    'comments','revisions','post-formats','custom-fields',
                ],
                'page' => ['title','editor','author','excerpt','thumbnail',
                    'comments','revisions','page-attributes','custom-fields',
                ],
                'attachment' => ['title','author','comments','revisions','custom-fields', ],
            ];
            foreach ( $post_type_attributes as $attribute ) {
                if ( isset( $fixed_schemas[ $this->_post_type ] ) && ! in_array( $attribute, $fixed_schemas[ $this->_post_type ], true ) )
                    continue;
                elseif ( ! isset( $fixed_schemas[ $this->_post_type ] ) && ! $this->_post_type_supports( $this->_post_type, $attribute ) )
                    continue;
                switch ( $attribute ) {
                    case 'title':
                        $schema['properties']['title'] = [
                            'description' => $this->__( 'The title for the post.' ),
                            'type' => 'object',
                            'context' => ['view', 'edit', 'embed'],
                            'arg_options' => ['sanitize_callback' => null,'validate_callback' => null, ],
                            'properties'  => [
                               'raw'=> ['description' => $this->__( 'Title for the post, as it exists in the database.' ),
                                    'type' => 'string','context' => ['edit'],],
                               'rendered' => ['description' => $this->__( 'HTML title for the post, transformed for display.' ),
                                    'type' => 'string','context' => ['view', 'edit', 'embed'], 'readonly' => true,],
                            ],
                        ];
                        break;
                    case 'editor':
                        $schema['properties']['content'] = [
                            'description' => $this->__( 'The content for the post.' ),
                            'type' => 'object','context' => ['view','edit'],
                            'arg_options' => ['sanitize_callback' => null,'validate_callback' => null, ],
                            'properties' => [
                                'raw' => ['description' => $this->__( 'Content for the post, as it exists in the database.' ),
                                    'type' => 'string','context' => ['edit'],],
                                'rendered' => ['description' => $this->__( 'HTML content for the post, transformed for display.' ),
                                    'type' => 'string','context' => ['view','edit'],'readonly'=> true,],
                                'block_version' => ['description' => $this->__( 'Version of the content block format used by the post.' ),
                                    'type' => 'integer','context' => ['edit'],'readonly' => true,],
                                'protected' => ['description' => $this->__( 'Whether the content is protected with a password.' ),
                                    'type' => 'boolean', 'context' => ['view','edit', 'embed'], 'readonly' => true,],
                            ],
                        ];
                        break;
                    case 'author':
                        $schema['properties']['author'] = [
                            'description' => $this->__( 'The ID for the author of the post.' ),
                            'type' => 'integer',
                            'context' => ['view','edit','embed'],
                        ];
                        break;
                    case 'excerpt':// Note: sanitization implemented in self::prepare_item_for_database().
                        $schema['properties']['excerpt'] = [
                            'description' => $this->__( 'The excerpt for the post.' ),
                            'type' => 'object','context' => ['view','edit','embed'],
                            'arg_options' => ['sanitize_callback' => null,'validate_callback' => null, ],
                            'properties'  => [
                                'raw'=> ['description' => $this->__( 'Excerpt for the post, as it exists in the database.' ),
                                    'type' => 'string','context' => ['edit'],],
                                'rendered' => ['description' => $this->__( 'HTML excerpt for the post, transformed for display.' ),
                                    'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,],
                                'protected' => ['description' => $this->__( 'Whether the excerpt is protected with a password.' ),
                                    'type' => 'boolean','context' => ['view','edit','embed'],'readonly' => true,],
                            ],
                        ];
                        break;
                    case 'thumbnail':
                        $schema['properties']['featured_media'] = ['description' => $this->__( 'The ID of the featured media for the post.' ),
                            'type' => 'integer','context' => ['view','edit','embed'],];
                        break;
                    case 'comments':
                        $schema['properties']['comment_status'] = ['description' => $this->__( 'Whether or not comments are open on the post.' ),
                            'type' => 'string','enum' => ['open','closed'],'context' => ['view','edit'],];
                        $schema['properties']['ping_status'] = ['description' => $this->__( 'Whether or not the post can be pinged.' ),
                            'type' => 'string','enum' => ['open','closed'],'context' => ['view','edit'],];
                        break;
                    case 'page-attributes':
                        $schema['properties']['menu_order'] = ['description' => $this->__( 'The order of the post in relation to other posts.' ),
                            'type'=> 'integer','context'=> ['view','edit'],];
                        break;
                    case 'post-formats':
                        $formats = array_values( $this->_get_post_format_slugs() );
                       $schema['properties']['format'] = ['description' => $this->__( 'The format for the post.' ),
                           'type' => 'string','enum' => $formats,'context' => ['view','edit'],];
                        break;
                    case 'custom-fields':
                        if($this->_meta instanceof TP_REST_Meta_Fields){
                            $schema['properties']['meta'] = $this->_meta->get_field_schema();
                        }
                        break;
                }
            }
            if ( 'post' === $this->_post_type ) {
                $schema['properties']['sticky'] = ['description' => $this->__( 'Whether or not the post should be treated as sticky.' ),
                    'type' => 'boolean','context' => ['view','edit'],];
            }
            $schema['properties']['template'] = ['description' => $this->__( 'The theme file to use to display the post.' ),
                'type' => 'string','context' => ['view','edit'],'arg_options' => ['validate_callback' => [$this, 'check_template'],],];
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                if ( array_key_exists( $base, $schema['properties'] ) ) {
                    $taxonomy_field_name_with_conflict = ! empty( $taxonomy->rest_base ) ? 'rest_base' : 'name';
                    $this->_doing_it_wrong('register_taxonomy',/* translators: 1: The taxonomy name, 2: The property name, either 'rest_base' or 'name', 3: The conflicting value. */
                        sprintf($this->__( 'The "%1$s" taxonomy "%2$s" property (%3$s) conflicts with an existing property on the REST API Posts Controller. Specify a custom "rest_base" when registering the taxonomy to avoid this error.' ),
                            $taxonomy->name,$taxonomy_field_name_with_conflict,$base),'0.0.1');
                }/* translators: %s: Taxonomy name. */
                $schema['properties'][ $base ] = [
                    'description' => sprintf( $this->__( 'The terms assigned to the post in the %s taxonomy.' ), $taxonomy->name ),
                    'type' => 'array','items' => ['type' => 'integer',],'context' => ['view', 'edit'],];
            }
            $schema_links = $this->_get_schema_links();
            if ( $schema_links ) $schema['links'] = $schema_links;
            $schema_fields = array_keys( $schema['properties'] );
            $schema = $this->_apply_filters( "rest_{$this->_post_type}_item_schema", $schema );
            $new_fields = array_diff( array_keys( $schema['properties'] ), $schema_fields );
            if ( count( $new_fields ) > 0 ) { /* translators: %s: register_rest_field */
                $this->_doing_it_wrong( __METHOD__,
                    sprintf($this->__( 'Please use %s to add new schema properties.' ),'register_rest_field'),'0.0.1');
            }
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//2160
        protected function _get_schema_links():string{
            $href = $this->_rest_url( "{$this->_namespace}/{$this->_rest_base}/{id}" );
            $links = [];
            if ( 'attachment' !== $this->_post_type ) {
                $links[] = ['rel' => 'https://api.w.org/action-publish',
                    'title' => $this->__( 'The current user can publish this post.' ),
                    'href' => $href,'targetSchema' => ['type' => 'object','properties' => ['status' => ['type' => 'string','enum' => ['publish','future'],],],],];
            }
            $links[] = ['rel' => 'https://api.w.org/action-unfiltered-html',
                'title' => $this->__( 'The current user can post unfiltered HTML markup and JavaScript.' ),
                'href' => $href,'targetSchema' => ['type'=> 'object','properties' => ['content' => ['raw' => ['type' => 'string',],],],],];
            if ( 'post' === $this->_post_type ) {
                $links[] = ['rel' => 'https://api.w.org/action-sticky',
                    'title' => $this->__( 'The current user can sticky this post.' ),
                    'href' => $href,'targetSchema' => ['type' => 'object','properties' => ['sticky' => ['type' => 'boolean',],],],];
            }
            if ( $this->_post_type_supports( $this->_post_type, 'author' ) ) {
                $links[] = ['rel' => 'https://api.w.org/action-assign-author',
                    'title' => $this->__( 'The current user can change the author on this post.' ),
                    'href' => $href,'targetSchema' => ['type' => 'object','properties' => ['author' => ['type' => 'integer',],],],];
            }
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $tax ) {/* translators: %s: Taxonomy name. */
                $tax_base = ! empty( $tax->rest_base ) ? $tax->rest_base : $tax->name;
                $assign_title = sprintf( $this->__( 'The current user can assign terms in the %s taxonomy.' ), $tax->name );
                $create_title = sprintf( $this->__( 'The current user can create terms in the %s taxonomy.' ), $tax->name );
                $links[] = ['rel' => 'https://api.w.org/action-assign-' . $tax_base,'title' => $assign_title,'href' => $href,
                    'targetSchema' => ['type' => 'object','properties' => [$tax_base => ['type'  => 'array','items' => ['type' => 'integer',],],],],
                ];
                $links[] = ['rel' => 'https://api.w.org/action-create-' . $tax_base,'title' => $create_title,'href' => $href,
                    'targetSchema' => ['type' => 'object','properties' => [$tax_base => ['type' => 'array','items' => ['type' => 'integer',],],],],
                ];
            }
            return $links;
        }//2591
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $query_params['context']['default'] = 'view';
            $query_params['after'] = ['description' => $this->__( 'Limit response to posts published after a given ISO8601 compliant date.' ),
                'type' => 'string','format' => 'date-time',];
            $query_params['modified_after'] = ['description' => $this->__( 'Limit response to posts modified after a given ISO8601 compliant date.' ),
                'type' => 'string','format' => 'date-time',];
            if ( $this->_post_type_supports( $this->_post_type, 'author' ) ) {
                $query_params['author'] = [
                    'description' => $this->__( 'Limit result set to posts assigned to specific authors.' ),
                    'type' => 'array','items' => ['type' => 'integer',],'default' => [],
                ];
                $query_params['author_exclude'] = ['description' => $this->__( 'Ensure result set excludes posts assigned to specific authors.' ),
                    'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            }
            $query_params['before'] = [ 'description' => $this->__( 'Limit response to posts published before a given ISO8601 compliant date.' ),
                'type' => 'string','format' => 'date-time',];
            $query_params['modified_before'] = ['description' => $this->__( 'Limit response to posts modified before a given ISO8601 compliant date.' ),
                'type' => 'string','format' => 'date-time',];
            $query_params['exclude'] = ['description' => $this->__( 'Ensure result set excludes specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            $query_params['include'] = ['description' => $this->__( 'Limit result set to specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            if ( 'page' === $this->_post_type || $this->_post_type_supports( $this->_post_type, 'page-attributes' ) )
                $query_params['menu_order'] = ['description' => $this->__( 'Limit result set to posts with a specific menu_order value.' ),'type' => 'integer',];
            $query_params['offset'] = ['description' => $this->__( 'Offset the result set by a specific number of items.' ),'type' => 'integer',];
            $query_params['order'] = ['description' => $this->__( 'Order sort attribute ascending or descending.' ),
                'type' => 'string','default' => 'desc','enum' => ['asc', 'desc'],];
            $query_params['orderby'] = [ 'description' => $this->__( 'Sort collection by post attribute.' ),'type' => 'string','default' => 'date',
                'enum' => ['author','date','id','include','modified','parent','relevance','slug','include_slugs','title',], ];
            if ( 'page' === $this->_post_type || $this->_post_type_supports( $this->_post_type, 'page-attributes' ) )
                $query_params['orderby']['enum'][] = 'menu_order';
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( $post_type->hierarchical || 'attachment' === $this->_post_type ) {
                $query_params['parent'] = ['description' => $this->__( 'Limit result set to items with particular parent IDs.' ),
                    'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
                $query_params['parent_exclude'] = [ 'description' => $this->__( 'Limit result set to all items except those of a particular parent ID.' ),
                    'type' => 'array','items' => ['type' => 'integer',],'default' => [],];
            }
            $query_params['slug'] = ['description' => $this->__( 'Limit result set to posts with one or more specific slugs.' ),
                'type' => 'array','items' => ['type' => 'string',],'sanitize_callback' => 'tp_parse_slug_list',];
            $query_params['status'] = ['default' => 'publish',
                'description' => $this->__( 'Limit result set to posts assigned one or more statuses.' ),
                'type' => 'array',
                'items' => ['enum' => array_merge( array_keys( $this->_get_post_stati() ), ['any'] ),'type' => 'string',],
                'sanitize_callback' => [$this, 'sanitize_post_statuses'],];
            $query_params = $this->__prepare_taxonomy_limit_schema( $query_params );
            if ( 'post' === $this->_post_type )
                $query_params['sticky'] = ['description' => $this->__( 'Limit result set to items that are sticky.' ),'type' => 'boolean',];
            return $this->_apply_filters( "rest_{$this->_post_type}_collection_params", $query_params, $post_type );
        }//2719
        public function sanitize_post_statuses( $statuses,TP_REST_Request $request, $parameter ){
            $statuses = $this->_tp_parse_slug_list( $statuses );
            $attributes = $request->get_attributes();
            $default_status = $attributes['args']['status']['default'];
            foreach ( $statuses as $status ) {
                if ( $status === $default_status ) continue;
                $post_type_obj = $this->_get_post_type_object( $this->_post_type );
                if ( $this->_current_user_can( $post_type_obj->cap->edit_posts ) || ('private' === $status && $this->_current_user_can($post_type_obj->cap->read_private_posts))) {
                    $result = $this->_rest_validate_request_arg( $status, $request, $parameter );
                    if ( $this->_init_error( $result ) )  return $result;
                } else return new TP_Error('rest_forbidden_status',$this->__( 'Status is forbidden.' ),['status' => $this->_rest_authorization_required_code()]);
            }
            return $statuses;
        }//2905
        private function __prepare_tax_query( array $args, TP_REST_Request $request ):string{
            $relation = $request['tax_relation'];
            if ( $relation ) $args['tax_query'] = array( 'relation' => $relation );
            $taxonomies = $this->_tp_list_filter(
                $this->_get_object_taxonomies( $this->_post_type, 'objects' ),
                ['show_in_rest' => true]
            );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                $tax_include = $request[ $base ];
                $tax_exclude = $request[ $base . '_exclude' ];
                if ( $tax_include ) {
                    $terms = [];
                    $include_children = false;
                    $operator = 'IN';
                    if ( $this->_rest_is_array( $tax_include ) )$terms = $tax_include;
                    elseif ( $this->_rest_is_object( $tax_include ) ) {
                        $terms = empty( $tax_include['terms'] ) ? [] : $tax_include['terms'];
                        $include_children = ! empty( $tax_include['include_children'] );
                        if ( isset( $tax_include['operator'] ) && 'AND' === $tax_include['operator'] )
                            $operator = 'AND';
                    }
                    if ( $terms )
                        $args['tax_query'][] = ['taxonomy' => $taxonomy->name,'field' => 'term_id',
                            'terms' => $terms,'include_children' => $include_children,'operator' => $operator,];
                }
                if ( $tax_exclude ) {
                    $terms = [];
                    $include_children = false;
                    if ( $this->_rest_is_array( $tax_exclude ) )
                        $terms = $tax_exclude;
                    elseif ( $this->_rest_is_object( $tax_exclude ) ) {
                        $terms            = empty( $tax_exclude['terms'] ) ? [] : $tax_exclude['terms'];
                        $include_children = ! empty( $tax_exclude['include_children'] );
                    }
                    if ( $terms ) {
                        $args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,'field' => 'term_id','terms' => $terms,
                            'include_children' => $include_children,'operator' => 'NOT IN',
                        ];
                    }
                }
            }
            return $args;
        }//2945
        private function __prepare_taxonomy_limit_schema( array $query_params ): array{
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            if ( ! $taxonomies ) return $query_params;
            $query_params['tax_relation'] = [
                'description' => $this->__( 'Limit result set based on relationship between multiple taxonomies.' ),
                'type' => 'string','enum' => ['AND','OR'],];
            $limit_schema = [
                'type'  => ['object','array'],
                'oneOf' => [
                    [
                        'title'=> $this->__( 'Term ID List' ),
                        'description' => $this->__( 'Match terms with the listed IDs.' ),
                        'type'=> 'array','items'=> ['type' => 'integer',],
                    ],
                    [
                        'title' => $this->__( 'Term ID Taxonomy Query' ),
                        'description' => $this->__( 'Perform an advanced term query.' ),
                        'type' => 'object',
                        'properties' => [
                            'terms' => ['description' => $this->__( 'Term IDs.' ),
                                'type' => 'array','items' => ['type' => 'integer',],'default' => [],
                            ],
                            'include_children' => [
                                'description' => $this->__( 'Whether to include child terms in the terms limiting the result set.' ),
                                'type' => 'boolean','default' => false,
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ];
            $include_schema = array_merge(/* translators: %s: Taxonomy name. */
                ['description' => $this->__( 'Limit result set to items with specific terms assigned in the %s taxonomy.' ),],
                $limit_schema
            );
            // 'operator' is supported only for 'include' queries.
            $include_schema['oneOf'][1]['properties']['operator'] = [
                'description' => $this->__( 'Whether items must be assigned all or any of the specified terms.' ),
                'type' => 'string','enum' => ['AND','OR'],'default' => 'OR',];
            $exclude_schema = array_merge(/* translators: %s: Taxonomy name. */
                ['description' => $this->__( 'Limit result set to items except those with specific terms assigned in the %s taxonomy.' ),],
                $limit_schema
            );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                $base_exclude = $base . '_exclude';
                $query_params[ $base ] = $include_schema;
                $query_params[ $base ]['description'] = sprintf( $query_params[ $base ]['description'], $base );
                $query_params[ $base_exclude ] = $exclude_schema;
                $query_params[ $base_exclude ]['description'] = sprintf( $query_params[ $base_exclude ]['description'], $base );
                if ( ! $taxonomy->hierarchical ) {
                    unset( $query_params[ $base ]['oneOf'][1]['properties']['include_children'], $query_params[ $base_exclude ]['oneOf'][1]['properties']['include_children'] );
                }
            }
            return $query_params;
        }//3024
    }
}else die;