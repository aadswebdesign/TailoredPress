<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Comment;
use TP_Core\Libs\RestApi\Fields\TP_REST_Comment_Meta_Fields;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Queries\TP_Comment_Query;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    class TP_REST_Comments_Controller extends TP_REST_Controller{
        protected $meta;
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'comments';
            $this->meta = new TP_REST_Comment_Meta_Fields();
        }
        public function register_routes():void {
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [
                    ['methods' => TP_GET,'callback' => [$this, 'get_items'],'permission_callback' => [ $this, 'get_items_permissions_check'],'args' => $this->get_collection_params(),],
                    ['methods' => TP_POST,'callback' => [$this, 'create_item'],'permission_callback' => [ $this, 'get_items_permissions_check'],'args' => $this->get_endpoint_args_for_item_schema( TP_POST ),],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<id>[\d]+)',
                [
                    'args'   => [
                        'id' => ['description' => $this->__( 'Unique identifier for the comment.' ),'type' => 'integer',],
                    ],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => ['context'  => $this->get_context_param(['default' => 'view']),
                            'password' => ['description' => $this->__( 'The password for the parent post of the comment (if the post is password protected).' ), 'type' => 'string',],
                        ],
                    ],
                    ['methods' => TP_EDITABLE,'callback' => [$this,'update_item'],'permission_callback' => [$this,'update_item_permissions_check'],
                        'args' => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ],
                    [
                        'methods' => TP_DELETE,'callback' => [$this, 'delete_item'],'permission_callback' => [ $this, 'delete_item_permissions_check'],
                        'args' => [
                            'force' => ['type' => 'boolean','default' => false, 'description' => $this->__( 'Whether to bypass Trash and force deletion.' ),],
                            'password' => ['description' => $this->__( 'The password for the parent post of the comment (if the post is password protected).' ),'type' => 'string',],
                        ],
                    ],
                    'schema' => array( $this, 'get_public_item_schema' ),
                ]
            );
        }
        public function get_items_permissions_check( $request ):string {
            if ( ! empty( $request['post'] ) ) {
                foreach ( (array) $request['post'] as $post_id ) {
                    $post = $this->_get_post( $post_id );
                    if ( ! empty( $post_id ) && $post && ! $this->_check_read_post_permission( $post, $request ) )
                        return new TP_Error('rest_cannot_read_post',
                            $this->__( 'Sorry, you are not allowed to read the post for this comment.' ),
                            ['status' => $this->_rest_authorization_required_code()]);
                    elseif ( 0 === $post_id && ! $this->_current_user_can( 'moderate_comments' ) )
                        return new TP_Error('rest_cannot_read',
                            $this->__( 'Sorry, you are not allowed to read comments without a post.' ),
                            ['status' => $this->_rest_authorization_required_code()]);
                }
            }
            if ( ! empty( $request['context'] ) && 'edit' === $request['context'] && ! $this->_current_user_can( 'moderate_comments' ) )
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit comments.' ),
                    ['status' => $this->_rest_authorization_required_code()] );
            if ( ! $this->_current_user_can( 'edit_posts' ) ) {
                $protected_params = ['author', 'author_exclude', 'author_email', 'type', 'status'];
                $forbidden_params = [];
                foreach ( $protected_params as $param ) {
                    if ( 'status' === $param ) {
                        if ( 'approve' !== $request[ $param ] ) $forbidden_params[] = $param;
                    } elseif ( 'type' === $param ) {
                        if ( 'comment' !== $request[ $param ] ) $forbidden_params[] = $param;
                    } elseif ( ! empty( $request[ $param ] ) ) $forbidden_params[] = $param;
                }
                if ( ! empty( $forbidden_params ) )
                    return new TP_Error('rest_forbidden_param',
                        sprintf( $this->__( 'Query parameter not permitted: %s' ), implode( ', ', $forbidden_params ) ),
                        ['status' => $this->_rest_authorization_required_code()]);
            }
            return true;
        }
        public function get_items(TP_REST_Request $request ):string{
            $parameter_mappings = ['author' => 'author__in','author_email' => 'author_email',
                'author_exclude' => 'author__not_in','exclude' => 'comment__not_in','include' => 'comment__in',
                'offset' => 'offset','order' => 'order','parent' => 'parent__in','parent_exclude' => 'parent__not_in',
                'per_page' => 'number','post' => 'post__in','search' => 'search','status' => 'status','type' => 'type',];
            $prepared_args = [];
            foreach ( $parameter_mappings as $api_param => $wp_param ) {
                if ( isset( $registered[ $api_param ], $request[ $api_param ] ) )
                    $prepared_args[ $wp_param ] = $request[ $api_param ];
            }
            foreach ( array( 'author_email', 'search' ) as $param ) {
                if ( ! isset( $prepared_args[ $param ] ) ) $prepared_args[ $param ] = '';
            }
            if ( isset( $registered['orderby'] ) )
                $prepared_args['orderby'] = $this->_normalize_query_param( $request['orderby'] );
            $prepared_args['no_found_rows'] = false;
            $prepared_args['date_query'] = array();
            if ( isset( $registered['before'], $request['before'] ) )
                $prepared_args['date_query'][0]['before'] = $request['before'];
            if ( isset( $registered['after'], $request['after'] ) )
                $prepared_args['date_query'][0]['after'] = $request['after'];
            if ( isset( $registered['page'] ) && empty( $request['offset'] ) )
                $prepared_args['offset'] = $prepared_args['number'] * ( $this->_abs_int( $request['page'] ) - 1 );
            $prepared_args = $this->_apply_filters( 'rest_comment_query', $prepared_args, $request );
            $query        = new TP_Comment_Query;
            $query_result = $query->query_comment( $prepared_args );
            $comments = [];
            foreach ( $query_result as $comment ) {
                if ( ! $this->_check_read_permission( $comment, $request ) ) continue;
                $data       = $this->prepare_item_for_response( $comment, $request );
                $comments[] = $this->prepare_response_for_collection( $data );
            }
            $total_comments = (int) $query->found_comments;
            $max_pages      = (int) $query->max_num_pages;
            if ( $total_comments < 1 ) {
                unset( $prepared_args['number'], $prepared_args['offset'] );
                $query                  = new TP_Comment_Query;
                $prepared_args['count'] = true;
                $total_comments = $query->query_comment( $prepared_args );
                $max_pages      = ceil( $total_comments / $request['per_page'] );
            }
            $response = $this->_rest_ensure_response( $comments );
            $response->header( 'X-TP-Total', $total_comments );
            $response->header( 'X-TP-TotalPages', $max_pages );
            $base = $this->_add_query_arg( $this->_url_encode_deep( $request->get_query_params() ), $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ) );
            if ( $request['page'] > 1 ) {
                $prev_page = $request['page'] - 1;
                if ( $prev_page > $max_pages ) $prev_page = $max_pages;
                $prev_link = $this->_add_query_arg( 'page', $prev_page, $base );
                $response->link_header( 'prev', $prev_link );
            }
            if ( $max_pages > $request['page'] ) {
                $next_page = $request['page'] + 1;
                $next_link = $this->_add_query_arg( 'page', $next_page, $base );
                $response->link_header( 'next', $next_link );
            }
            return $response;
        }//194
        protected function _get_comment( $id ):TP_Error {
            $error = new TP_Error('rest_comment_invalid_id', $this->__( 'Invalid comment ID.' ),['status' => NOT_FOUND]);
            if ( (int) $id <= 0 ) return $error;
            $id      = (int) $id;
            $comment = $this->_get_comment( $id );
            if ($comment === null) return $error;
            if ($comment->comment_post_ID !== null ) {
                $post = $this->_get_post( (int) $comment->comment_post_ID );
                if ( empty( $post ) )
                    return new TP_Error('rest_post_invalid_id', $this->__( 'Invalid post ID.'),['status' => NOT_FOUND]);
            }
            return $comment;
        }//338
        public function get_item_permissions_check( $request ):string{
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            if ( ! empty( $request['context'] ) && 'edit' === $request['context'] && ! $this->_current_user_can( 'moderate_comments' ) )
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to edit comments.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            $post = $this->_get_post( $comment->comment_post_ID );
            if ( ! $this->_check_read_permission( $comment, $request ) )
                return new TP_Error('rest_cannot_read',
                    $this->__( 'Sorry, you are not allowed to read this comment.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( $post && ! $this->_check_read_post_permission( $post, $request ) )
                return new TP_Error('rest_cannot_read_post',
                    $this->__( 'Sorry, you are not allowed to read the post for this comment.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//378
        public function get_item( $request ):string {
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            $data = $this->prepare_item_for_response( $comment, $request );
            return $this->_rest_ensure_response( $data );
        }//421
        public function create_item_permissions_check( $request ):string {
            if ( ! $this->_is_user_logged_in() ) {
                if ( $this->_get_option( 'comment_registration' ) )
                    return new TP_Error('rest_comment_login_required',
                        $this->__( 'Sorry, you must be logged in to comment.' ),
                        ['status' => UNAUTHORIZED]
                    );
                $allow_anonymous = $this->_apply_filters( 'rest_allow_anonymous_comments', false, $request );
                if ( ! $allow_anonymous )
                    return new TP_Error('rest_comment_login_required',
                        $this->__( 'Sorry, you must be logged in to comment.' ),
                        ['status' => UNAUTHORIZED]);
            }
            if ( isset( $request['author'] ) && $this->_get_current_user_id() !== $request['author'] && ! $this->_current_user_can( 'moderate_comments' ) )
                return new TP_Error('rest_comment_invalid_author',
                    sprintf( $this->__( "Sorry, you are not allowed to edit '%s' for comments." ), 'author' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( isset( $request['author_ip'] ) && ! $this->_current_user_can( 'moderate_comments' ) ) {
                if ( empty( $_SERVER['REMOTE_ADDR'] ) || $request['author_ip'] !== $_SERVER['REMOTE_ADDR'] )
                    return new TP_Error('rest_comment_invalid_author_ip',
                        sprintf( $this->__( "Sorry, you are not allowed to edit '%s' for comments." ), 'author_ip' ),
                        ['status' => $this->_rest_authorization_required_code()]);
            }
            if ( isset( $request['status'] ) && ! $this->_current_user_can( 'moderate_comments' ) )
                return new TP_Error('rest_comment_invalid_status',
                    sprintf( $this->__( "Sorry, you are not allowed to edit '%s' for comments." ), 'status' ),
                    [['status' => $this->_rest_authorization_required_code()]]);
            if ( empty( $request['post'] ) )
                return new TP_Error('rest_comment_invalid_post_id',
                    $this->__( 'Sorry, you are not allowed to create this comment without a post.' ),
                    ['status' => FORBIDDEN] );
            $post = $this->_get_post( (int) $request['post'] );
            if( $post instanceof \stdClass){}//todo
            if ( ! $post )
                return new TP_Error('rest_comment_invalid_post_id',
                    $this->__( 'Sorry, you are not allowed to create this comment without a post.' ),
                    ['status' => FORBIDDEN]);
            if ( 'draft' === $post->post_status ) {
                return new TP_Error('rest_comment_draft_post',
                    $this->__( 'Sorry, you are not allowed to create a comment on this post.' ),
                    ['status' => FORBIDDEN]);
            }
            if ( 'trash' === $post->post_status ) {
                return new TP_Error('rest_comment_trash_post',
                    $this->__( 'Sorry, you are not allowed to create a comment on this post.' ),
                    ['status' => FORBIDDEN]);
            }
            if ( ! $this->_check_read_post_permission( $post, $request ) )
                return new TP_Error('rest_cannot_read_post',
                    $this->__( 'Sorry, you are not allowed to read the post for this comment.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( ! $this->_comments_open( $post->ID ) ) {
                return new TP_Error('rest_comment_closed',
                    $this->__( 'Sorry, comments are closed for this item.' ),
                    ['status' => FORBIDDEN]);
            }
            return true;
        }//441
        public function create_item(TP_REST_Request $request ):string {
            if ( ! empty( $request['id'] ) )
                return new TP_Error('rest_comment_exists',
                    $this->__( 'Cannot create existing comment.' ),
                    ['status' => BAD_REQUEST]);
            if ( ! empty( $request['type'] ) && 'comment' !== $request['type'] )
                return new TP_Error('rest_invalid_comment_type',
                    $this->__( 'Cannot create a comment with that type.' ),
                    ['status' => BAD_REQUEST]);
            $prepared_comment = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_comment ) ) return $prepared_comment;
            $prepared_comment['comment_type'] = 'comment';
            if ( ! isset( $prepared_comment['comment_content'] ) ) $prepared_comment['comment_content'] = '';
            if ( ! $this->_check_is_comment_content_allowed( $prepared_comment ) )
                return new TP_Error('rest_comment_content_invalid',
                    $this->__( 'Invalid comment content.' ),
                    ['status' => BAD_REQUEST]);
            if ( ! isset( $prepared_comment['comment_date_gmt'] ) )
                $prepared_comment['comment_date_gmt'] = $this->_current_time( 'mysql', true );
            $missing_author = empty( $prepared_comment['user_id'] )
                && empty( $prepared_comment['comment_author'] )
                && empty( $prepared_comment['comment_author_email'] )
                && empty( $prepared_comment['comment_author_url'] );
            if ($missing_author && $this->_is_user_logged_in()) {
                $user = $this->_tp_get_user_current();
                $prepared_comment['user_id']              = $user->ID;
                $prepared_comment['comment_author']       = $user->display_name;
                $prepared_comment['comment_author_email'] = $user->user_email;
                $prepared_comment['comment_author_url']   = $user->user_url;
            }
            if ( $this->_get_option( 'require_name_email' ) ) {
                if ( empty( $prepared_comment['comment_author'] ) || empty( $prepared_comment['comment_author_email'] ) ) {
                    return new TP_Error('rest_comment_author_data_required',
                        $this->__( 'Creating a comment requires valid author name and email values.' ),
                        ['status' => BAD_REQUEST]);
                }
            }
            if ( ! isset( $prepared_comment['comment_author_email'] ) ) $prepared_comment['comment_author_email'] = '';
            if ( ! isset( $prepared_comment['comment_author_url'] ) ) $prepared_comment['comment_author_url'] = '';
            if ( ! isset( $prepared_comment['comment_agent'] ) ) $prepared_comment['comment_agent'] = '';
            $check_comment_lengths = $this->_tp_check_comment_data_max_lengths( $prepared_comment );
            if ( $this->_init_error( $check_comment_lengths ) ) {
                $error_code = $check_comment_lengths->get_error_code();
                return new TP_Error( $error_code,
                    $this->__( 'Comment field exceeds maximum length allowed.' ),
                    ['status' => BAD_REQUEST]);
            }
            $prepared_comment['comment_approved'] = $this->_tp_allow_comment( $prepared_comment, true );
            if ( $this->_init_error( $prepared_comment['comment_approved'] ) ) {
                $error_code    = $prepared_comment['comment_approved']->get_error_code();
                $error_message = $prepared_comment['comment_approved']->get_error_message();
                if ( 'comment_duplicate' === $error_code )
                    return new TP_Error($error_code,$error_message,['status' => CONFLICT ]);
                if ( 'comment_flood' === $error_code )
                    return new TP_Error($error_code,$error_message,['status' => BAD_REQUEST]);
                return $prepared_comment['comment_approved'];
            }
            $prepared_comment = $this->_apply_filters( 'rest_pre_insert_comment', $prepared_comment, $request );
            if ( $this->_init_error( $prepared_comment ) ) return $prepared_comment;
            $comment_id = $this->_tp_insert_comment( $this->_tp_filter_comment( $this->_tp_slash( (array) $prepared_comment ) ) );
            if ( ! $comment_id )
                return new TP_Error('rest_comment_failed_create',$this->__( 'Creating comment failed.' ),['status' => INTERNAL_SERVER_ERROR]);
            if ( isset( $request['status'] ) ) $this->_handle_status_param( $request['status'], $comment_id );
            $comment = $this->_get_comment( $comment_id );
            $this->_do_action( 'rest_insert_comment', $comment, $request, true );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->meta->update_value( $request['meta'], $comment_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $fields_update = $this->_update_additional_fields_for_object( $comment, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $context = $this->_current_user_can( 'moderate_comments' ) ? 'edit' : 'view';
            $request->set_param( 'context', $context );
            $this->_do_action( 'rest_after_insert_comment', $comment, $request, true );
            $response = $this->prepare_item_for_response( $comment, $request );
            $response = $this->_rest_ensure_response( $response );
            $response->set_status( CREATED );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $comment_id ) ) );
            return $response;
        }//565
        public function update_item_permissions_check( $request ):string{
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            if ( ! $this->_check_edit_permission( $comment ) )
                return new TP_Error('rest_cannot_edit',
                    $this->__( 'Sorry, you are not allowed to edit this comment.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//775
        public function update_item(TP_REST_Request $request ):string{
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            $id = $comment->comment_ID;
            if ( isset( $request['type'] ) && $this->_get_comment_type( $id ) !== $request['type'] )
                return new TP_Error('rest_comment_invalid_type',
                    $this->__( 'Sorry, you are not allowed to change the comment type.' ),
                    ['status' => NOT_FOUND]);
            $prepared_args = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_args ) ) return $prepared_args;
            if ( ! empty( $prepared_args['comment_post_ID'] ) ) {
                $post = $this->_get_post( $prepared_args['comment_post_ID'] );
                if ( empty( $post ) )
                    return new TP_Error('rest_comment_invalid_post_id',
                        $this->__( 'Invalid post ID.' ),
                        ['status' => FORBIDDEN]);
            }
            if ( empty( $prepared_args ) && isset( $request['status'] ) ) {
                $change = $this->_handle_status_param( $request['status'], $id );
                if ( ! $change )
                    return new TP_Error('rest_comment_failed_edit',
                        $this->__( 'Updating comment status failed.' ),
                        ['status' => INTERNAL_SERVER_ERROR]);

            } elseif ( ! empty( $prepared_args ) ) {
                if ( $this->_init_error( $prepared_args ) ) return $prepared_args;
                if ( isset( $prepared_args['comment_content'] ) && empty( $prepared_args['comment_content'] ) ) {
                    return new TP_Error('rest_comment_content_invalid',
                        $this->__( 'Invalid comment content.' ),
                        ['status' => BAD_REQUEST]);
                }
                $prepared_args['comment_ID'] = $id;
                $check_comment_lengths = $this->_tp_check_comment_data_max_lengths( $prepared_args );
                if ( $this->_init_error( $check_comment_lengths ) ) {
                    $error_code = $check_comment_lengths->get_error_code();
                    return new TP_Error($error_code,
                        $this->__( 'Comment field exceeds maximum length allowed.' ),
                        ['status' => BAD_REQUEST]);
                }
                $updated = $this->_tp_update_comment( $this->_tp_slash( (array) $prepared_args ), true );
                if ( $this->_init_error( $updated ) )
                    return new TP_Error('rest_comment_failed_edit',
                        $this->__( 'Updating comment failed.' ),
                        ['status' => INTERNAL_SERVER_ERROR]);
                if ( isset( $request['status'] ) )
                    $this->_handle_status_param( $request['status'], $id );
            }
            $comment = $this->_get_comment( $id );
            $this->_do_action( 'rest_insert_comment', $comment, $request, false );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->meta->update_value( $request['meta'], $id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $fields_update = $this->_update_additional_fields_for_object( $comment, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_comment', $comment, $request, false );
            $response = $this->prepare_item_for_response( $comment, $request );
            return $this->_rest_ensure_response( $response );
        }//800
        public function delete_item_permissions_check( $request ):string{
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            if ( ! $this->_check_edit_permission( $comment ) )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete this comment.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//925
        public function delete_item(TP_REST_Request $request ):string{
            $comment = $this->_get_comment( $request['id'] );
            if ( $this->_init_error( $comment ) ) return $comment;
            $force = isset( $request['force'] ) ? (bool) $request['force'] : false;
            $supports_trash = $this->_apply_filters( 'rest_comment_trashable', ( EMPTY_TRASH_DAYS > 0 ), $comment );
            $request->set_param( 'context', 'edit' );
            if ( $force ) {
                $previous = $this->prepare_item_for_response( $comment, $request );
                if($previous  instanceof TP_REST_Response ){}//todo
                $result   = $this->_tp_delete_comment( $comment->comment_ID, true );
                $response = new TP_REST_Response();
                $response->set_data(['deleted'  => true,'previous' => $previous->get_data(),]);
            } else {
                if ( ! $supports_trash )
                    return new TP_Error('rest_trash_not_supported',
                        sprintf( $this->__( "The comment does not support trashing. Set '%s' to delete." ), 'force=true' ),
                        ['status' => NOT_IMPLEMENTED]
                    );
                if ( 'trash' === $comment->comment_approved )
                    return new TP_Error('rest_already_trashed',
                        $this->__( 'The comment has already been trashed.' ),
                        ['status' => GONE]
                    );
                $result   = $this->_tp_trash_comment( $comment->comment_ID );
                $comment  = $this->_get_comment( $comment->comment_ID );
                $response = $this->prepare_item_for_response( $comment, $request );
            }
            if ( ! $result )
                return new TP_Error('rest_cannot_delete',$this->__( 'The comment cannot be deleted.' ), ['status' => INTERNAL_SERVER_ERROR]);
            $this->_do_action( 'rest_delete_comment', $comment, $response, $request );
            return $response;
        }//949
        public function prepare_item_for_response( $item, $request ):string{
            $comment = $item;
            $fields  = $this->get_fields_for_response( $request );
            $data    = [];
            if ( in_array( 'id', $fields, true ) ) $data['id'] = (int) $comment->comment_ID;
            if ( in_array( 'post', $fields, true ) ) $data['post'] = (int) $comment->comment_post_ID;
            if ( in_array( 'parent', $fields, true ) ) $data['parent'] = (int) $comment->comment_parent;
            if ( in_array( 'author', $fields, true ) ) $data['author'] = (int) $comment->user_id;
            if ( in_array( 'author_name', $fields, true ) ) $data['author_name'] = $comment->comment_author;
            if ( in_array( 'author_email', $fields, true ) ) $data['author_email'] = $comment->comment_author_email;
            if ( in_array( 'author_url', $fields, true ) ) $data['author_url'] = $comment->comment_author_url;
            if ( in_array( 'author_ip', $fields, true ) ) $data['author_ip'] = $comment->comment_author_IP;
            if ( in_array( 'author_user_agent', $fields, true ) ) $data['author_user_agent'] = $comment->comment_agent;
            if ( in_array( 'date', $fields, true ) ) $data['date'] = $this->_mysql_to_rfc3339( $comment->comment_date );
            if ( in_array( 'date_gmt', $fields, true ) ) $data['date_gmt'] = $this->_mysql_to_rfc3339( $comment->comment_date_gmt );
            if ( in_array( 'content', $fields, true ) )
                $data['content'] = ['rendered' => $this->_apply_filters( 'comment_text', $comment->comment_content, $comment ),'raw'=> $comment->comment_content, ];
            if ( in_array( 'link', $fields, true ) ) $data['link'] = $this->_get_comment_link( $comment );
            if ( in_array( 'status', $fields, true ) ) $data['status'] = $this->_prepare_status_response( $comment->comment_approved );
            if ( in_array( 'type', $fields, true ) ) $data['type'] = $this->_get_comment_type( $comment->comment_ID );
            if ( in_array( 'author_avatar_urls', $fields, true ) ) $data['author_avatar_urls'] = $this->_rest_get_avatar_urls( $comment );
            if ( in_array( 'meta', $fields, true ) ) $data['meta'] = $this->meta->get_value( $comment->comment_ID, $request );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $response = $this->_rest_ensure_response( $data );
            if($response  instanceof TP_REST_Response ){}//todo
            $response->add_links( $this->_prepare_links( $comment ) );
            return $this->_apply_filters( 'rest_prepare_comment', $response, $comment, $request );
        }//1037
        protected function _prepare_links(TP_Comment $comment ): array{
            $links = [
                'self' => ['href' => $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $comment->comment_ID ) ),],
                'collection' => ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
            ];
            if ( 0 !== (int) $comment->user_id )
                $links['author'] = ['href' => $this->_rest_url( 'tp/v1/users/' . $comment->user_id ),'embeddable' => true,];
            if ( 0 !== (int) $comment->comment_post_ID ) {
                $post = $this->_get_post( $comment->comment_post_ID );
                $post_route = $this->_rest_get_route_for_post( $post );
                if ( ! empty( $post->ID ) && $post_route )
                    $links['up'] = ['href' => $this->_rest_url( $post_route ),'embeddable' => true,'post_type' => $post->post_type,];
            }
            if ( 0 !== (int) $comment->comment_parent )
                $links['in-reply-to'] = ['href' => $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $comment->comment_parent ) ),
                    'embeddable' => true,];
            $comment_children = $comment->get_children(['number' => 1,'count'  => true,]);
            if ( ! empty( $comment_children ) ) {
                $args = ['parent' => $comment->comment_ID,];
                $rest_url = $this->_add_query_arg( $args, $this->_rest_url( $this->_namespace . '/' . $this->_rest_base ) );
                $links['children'] = ['href' => $rest_url,];
            }
            return $links;
        }//1146
        protected function _normalize_query_param( $query_param ): string{
            $prefix = 'comment_';
            switch ( $query_param ) {
                case 'id':
                    $normalized = $prefix . 'ID';
                    break;
                case 'post':
                    $normalized = $prefix . 'post_ID';
                    break;
                case 'parent':
                    $normalized = $prefix . 'parent';
                    break;
                case 'include':
                    $normalized = 'comment__in';
                    break;
                default:
                    $normalized = $prefix . $query_param;
                    break;
            }
            return $normalized;
        }//1214
        protected function _prepare_status_response( $comment_approved ){
            switch ( $comment_approved ) {
                case 'hold':
                case '0':
                    $status = 'hold';
                    break;
                case 'approve':
                case '1':
                    $status = 'approved';
                    break;
                case 'spam':
                case 'trash':
                default:
                    $status = $comment_approved;
                    break;
            }
            return $status;
        }//1246
        protected function _prepare_item_for_database(TP_REST_Request $request ):string{
            $prepared_comment = [];
            if ( isset( $request['content'] ) && is_string( $request['content'] ) )
                $prepared_comment['comment_content'] = trim( $request['content'] );
            elseif ( isset( $request['content']['raw'] ) && is_string( $request['content']['raw'] ) )
                $prepared_comment['comment_content'] = trim( $request['content']['raw'] );
            if ( isset( $request['post'] ) ) $prepared_comment['comment_post_ID'] = (int) $request['post'];
            if ( isset( $request['parent'] ) ) $prepared_comment['comment_parent'] = $request['parent'];
            if ( isset( $request['author'] ) ) {
                $user = new TP_User( $request['author'] );
                if($user  instanceof \stdClass ){} //todo
                if ( $user->exists() ) {
                    $prepared_comment['user_id']              = $user->ID;
                    $prepared_comment['comment_author']       = $user->display_name;
                    $prepared_comment['comment_author_email'] = $user->user_email;
                    $prepared_comment['comment_author_url']   = $user->user_url;
                } else
                    return new TP_Error('rest_comment_author_invalid',
                        $this->__( 'Invalid comment author ID.' ),
                        ['status' => BAD_REQUEST]);
            }
            if ( isset( $request['author_name'] ) ) $prepared_comment['comment_author'] = $request['author_name'];
            if ( isset( $request['author_email'] ) ) $prepared_comment['comment_author_email'] = $request['author_email'];
            if ( isset( $request['author_url'] ) ) $prepared_comment['comment_author_url'] = $request['author_url'];
            if ( isset( $request['author_ip'] ) && $this->_current_user_can( 'moderate_comments' ) )
                $prepared_comment['comment_author_IP'] = $request['author_ip'];
            elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) && $this->_rest_is_ip_address( $_SERVER['REMOTE_ADDR'] ) )
                $prepared_comment['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
            else $prepared_comment['comment_author_IP'] = '127.0.0.1';
            if ( ! empty( $request['author_user_agent'] ) )
                $prepared_comment['comment_agent'] = $request['author_user_agent'];
            elseif ( $request->get_header( 'user_agent' ) )
                $prepared_comment['comment_agent'] = $request->get_header( 'user_agent' );
            if ( ! empty( $request['date'] ) ) {
                $date_data = $this->_rest_get_date_with_gmt( $request['date'] );
                if ( ! empty( $date_data ) ) @list( $prepared_comment['comment_date'], $prepared_comment['comment_date_gmt'] ) = $date_data;
            } elseif ( ! empty( $request['date_gmt'] ) ) {
                $date_data = $this->_rest_get_date_with_gmt( $request['date_gmt'], true );
                if ( ! empty( $date_data ) ) @list( $prepared_comment['comment_date'], $prepared_comment['comment_date_gmt'] ) = $date_data;
            }
            return $this->_apply_filters( 'rest_preprocess_comment', $prepared_comment, $request );
        }//1277
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = array(
                '$schema'    => 'http://json-schema.org/draft-04/schema#',
                'title'      => 'comment',
                'type'       => 'object',
                'properties' => array(
                    'id'                => array(
                        'description' => $this->__( 'Unique identifier for the comment.' ),
                        'type'        => 'integer',
                        'context'     => ['view', 'edit', 'embed'],
                        'readonly'    => true,
                    ),
                    'author'            => array(
                        'description' => $this->__( 'The ID of the user object, if author was a user.' ),
                        'type'        => 'integer',
                        'context'     => ['view', 'edit', 'embed'],
                    ),
                    'author_email'      => array(
                        'description' => $this->__( 'Email address for the comment author.' ),
                        'type'        => 'string',
                        'format'      => 'email',
                        'context'     => ['edit'],
                        'arg_options' => array(
                            'sanitize_callback' => array( $this, 'check_comment_author_email' ),
                            'validate_callback' => null, // Skip built-in validation of 'email'.
                        ),
                    ),
                    'author_ip'         => array(
                        'description' => $this->__( 'IP address for the comment author.' ),
                        'type'        => 'string',
                        'format'      => 'ip',
                        'context'     => ['edit'],
                    ),
                    'author_name'       => array(
                        'description' => $this->__( 'Display name for the comment author.' ),
                        'type'        => 'string',
                        'context'     => ['view', 'edit', 'embed'],
                        'arg_options' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                    ),
                    'author_url'        => array(
                        'description' => $this->__( 'URL for the comment author.' ),
                        'type'        => 'string',
                        'format'      => 'uri',
                        'context'     => ['view', 'edit', 'embed'],
                    ),
                    'author_user_agent' => array(
                        'description' => $this->__( 'User agent for the comment author.' ),
                        'type'        => 'string',
                        'context'     => ['edit'],
                        'arg_options' => array(
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                    ),
                    'content'           => array(
                        'description' => $this->__( 'The content for the comment.' ),
                        'type'        => 'object',
                        'context'     => ['view', 'edit', 'embed'],
                        'arg_options' => array(
                            'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
                            'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
                        ),
                        'properties'  => array(
                            'raw'      => array(
                                'description' => $this->__( 'Content for the comment, as it exists in the database.' ),
                                'type'        => 'string',
                                'context'     => ['edit'],
                            ),
                            'rendered' => array(
                                'description' => $this->__( 'HTML content for the comment, transformed for display.' ),
                                'type'        => 'string',
                                'context'     => ['view', 'edit', 'embed'],
                                'readonly'    => true,
                            ),
                        ),
                    ),
                    'date'              => array(
                        'description' => $this->__( "The date the comment was published, in the site's timezone." ),
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => ['view', 'edit', 'embed'],
                    ),
                    'date_gmt'          => array(
                        'description' => $this->__( 'The date the comment was published, as GMT.' ),
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => ['view', 'edit'],
                    ),
                    'link'              => array(
                        'description' => $this->__( 'URL to the comment.' ),
                        'type'        => 'string',
                        'format'      => 'uri',
                        'context'     => ['view', 'edit', 'embed'],
                        'readonly'    => true,
                    ),
                    'parent'            => array(
                        'description' => $this->__( 'The ID for the parent of the comment.' ),
                        'type'        => 'integer',
                        'context'     => ['view', 'edit', 'embed'],
                        'default'     => 0,
                    ),
                    'post'              => array(
                        'description' => $this->__( 'The ID of the associated post object.' ),
                        'type'        => 'integer',
                        'context'     => ['view', 'edit'],
                        'default'     => 0,
                    ),
                    'status'            => array(
                        'description' => $this->__( 'State of the comment.' ),
                        'type'        => 'string',
                        'context'     => ['view', 'edit'],
                        'arg_options' => array(
                            'sanitize_callback' => 'sanitize_key',
                        ),
                    ),
                    'type'              => array(
                        'description' => $this->__( 'Type of the comment.' ),
                        'type'        => 'string',
                        'context'     => ['view', 'edit', 'embed'],
                        'readonly'    => true,
                    ),
                ),
            );
            if ( $this->_get_option( 'show_avatars' ) ) {
                $avatar_properties = array();
                $avatar_sizes = $this->_rest_get_avatar_sizes();
                foreach ((array) $avatar_sizes as $size ) {
                    $avatar_properties[ $size ] = array(
                        /* translators: %d: Avatar image size in pixels. */
                        'description' => sprintf( $this->__( 'Avatar URL with image size of %d pixels.' ), $size ),
                        'type'        => 'string',
                        'format'      => 'uri',
                        'context'     => ['view', 'edit', 'embed'],
                    );
                }
                $schema['properties']['author_avatar_urls'] = array(
                    'description' => $this->__( 'Avatar URLs for the comment author.' ),
                    'type'        => 'object',
                    'context'     => ['view', 'edit', 'embed'],
                    'readonly'    => true,
                    'properties'  => $avatar_properties,
                );
            }
            $schema['properties']['meta'] = $this->meta->get_field_schema();
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//1375 //todo shrinking
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $query_params['context']['default'] = 'view';
            $query_params['after'] = array(
                'description' => $this->__( 'Limit response to comments published after a given ISO8601 compliant date.' ),
                'type'        => 'string',
                'format'      => 'date-time',
            );
            $query_params['author'] = array(
                'description' => $this->__( 'Limit result set to comments assigned to specific user IDs. Requires authorization.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
            );
            $query_params['author_exclude'] = array(
                'description' => $this->__( 'Ensure result set excludes comments assigned to specific user IDs. Requires authorization.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
            );
            $query_params['author_email'] = array(
                'default'     => null,
                'description' => $this->__( 'Limit result set to that from a specific author email. Requires authorization.' ),
                'format'      => 'email',
                'type'        => 'string',
            );
            $query_params['before'] = array(
                'description' => $this->__( 'Limit response to comments published before a given ISO8601 compliant date.' ),
                'type'        => 'string',
                'format'      => 'date-time',
            );
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
                'enum'        => array(
                    'asc',
                    'desc',
                ),
            );
            $query_params['orderby'] = array(
                'description' => $this->__( 'Sort collection by comment attribute.' ),
                'type'        => 'string',
                'default'     => 'date_gmt',
                'enum'        => array(
                    'date',
                    'date_gmt',
                    'id',
                    'include',
                    'post',
                    'parent',
                    'type',
                ),
            );
            $query_params['parent'] = array(
                'default'     => array(),
                'description' => $this->__( 'Limit result set to comments of specific parent IDs.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
            );
            $query_params['parent_exclude'] = array(
                'default'     => array(),
                'description' => $this->__( 'Ensure result set excludes specific parent IDs.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
            );
            $query_params['post'] = array(
                'default'     => array(),
                'description' => $this->__( 'Limit result set to comments assigned to specific post IDs.' ),
                'type'        => 'array',
                'items'       => array(
                    'type' => 'integer',
                ),
            );
            $query_params['status'] = array(
                'default'           => 'approve',
                'description'       => $this->__( 'Limit result set to comments assigned a specific status. Requires authorization.' ),
                'sanitize_callback' => 'sanitize_key',
                'type'              => 'string',
                'validate_callback' => 'rest_validate_request_arg',
            );
            $query_params['type'] = array(
                'default'           => 'comment',
                'description'       => $this->__( 'Limit result set to comments assigned a specific type. Requires authorization.' ),
                'sanitize_callback' => 'sanitize_key',
                'type'              => 'string',
                'validate_callback' => 'rest_validate_request_arg',
            );
            $query_params['password'] = array(
                'description' => $this->__( 'The password for the post if it is password protected.' ),
                'type'        => 'string',
            );
            return $this->_apply_filters( 'rest_comment_collection_params', $query_params );
        }//1542 //todo shrinking
        protected function _handle_status_param( $new_status, $comment_id ){
            $old_status = $this->_tp_get_comment_status( $comment_id );
            if ( $new_status === $old_status ) return false;
            switch ( $new_status ) {
                case 'approved':
                case 'approve':
                case '1':
                    $changed = $this->_tp_set_comment_status( $comment_id, 'approve' );
                    break;
                case 'hold':
                case '0':
                    $changed = $this->_tp_set_comment_status( $comment_id, 'hold' );
                    break;
                case 'spam':
                    $changed = $this->_tp_spam_comment( $comment_id );
                    break;
                case 'unspam':
                    $changed = $this->_tp_unspam_comment( $comment_id );
                    break;
                case 'trash':
                    $changed = $this->_tp_trash_comment( $comment_id );
                    break;
                case 'untrash':
                    $changed = $this->_tp_untrash_comment( $comment_id );
                    break;
                default:
                    $changed = false;
                    break;
            }
            return $changed;
        }//1701
        protected function _check_read_post_permission( $post, $request ){
            $post_type = $this->_get_post_type_object( $post->post_type );
            if( $post_type instanceof TP_Post_Type ){}//todo
            if ( ! $post_type ) return false;
            $posts_controller = $post_type->get_rest_controller();
            if ( ! $posts_controller instanceof TP_REST_Posts_Controller )
                $posts_controller = new TP_REST_Posts_Controller( $post->post_type );
            $has_password_filter = false;
            $requested_post    = ! empty( $request['post'] ) && ( ! is_array( $request['post'] ) || 1 === count( $request['post'] ) );
            $requested_comment = ! empty( $request['id'] );
            if ( ( $requested_post || $requested_comment ) && $posts_controller->can_access_password_content( $post, $request ) ) {
                $this->_add_filter( 'post_password_required', '__return_false' );
                $has_password_filter = true;
            }
            if ( $this->_post_password_required( $post ) ) $result = $this->_current_user_can( 'edit_post', $post->ID );
            else $result = $posts_controller->check_read_permission( $post );
            if ( $has_password_filter ) $this->_remove_filter( 'post_password_required', '__return_false' );
            return $result;
        }//1749
        protected function _check_read_permission( $comment, $request ){
            if ( ! empty( $comment->comment_post_ID ) ) {
                $post = $this->_get_post( $comment->comment_post_ID );
                if (1 === (int)$comment->comment_approved && $post && $this->_check_read_post_permission($post, $request)) return true;
            }
            if ( 0 === $this->_get_current_user_id() ) return false;
            if ( empty( $comment->comment_post_ID ) && ! $this->_current_user_can( 'moderate_comments' ) ) return false;
            if ( ! empty( $comment->user_id ) && $this->_get_current_user_id() === (int) $comment->user_id ) return true;
            return $this->_current_user_can( 'edit_comment', $comment->comment_ID );
        }//1798
        protected function _check_edit_permission( $comment ){
            if ( 0 === $this->_get_current_user_id() )  return false;
            if ( $this->_current_user_can( 'moderate_comments' ) ) return true;
            return $this->_current_user_can( 'edit_comment', $comment->comment_ID );
        }//1831
        public function check_comment_author_email( $value, $request, $param ){
            $email = (string) $value;
            if ( empty( $email ) ) return $email;
            $check_email = $this->_rest_validate_request_arg( $email, $request, $param );
            if ( $this->_init_error( $check_email ) ) return $check_email;
            return $email;
        }//1858
        protected function _check_is_comment_content_allowed( $prepared_comment ): bool{
            $check = $this->_tp_parse_args(
                $prepared_comment,
                ['comment_post_ID' => 0,'comment_parent' => 0,'user_ID' => 0,'comment_author' => null,'comment_author_email' => null,'comment_author_url' => null,]
            );
            $allow_empty = $this->_apply_filters( 'allow_empty_comment', false, $check );
            if ( $allow_empty ) return true;
            return '' !== $check['comment_content'];
        }//1880
    }
}else die;