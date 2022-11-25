<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\Fields\TP_REST_User_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Queries\TP_User_Query;
if(ABSPATH){
    class TP_REST_Users_Controller extends TP_REST_Controller{
        protected $_meta;
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'users';
            $this->_meta = new TP_REST_User_Meta_Fields();
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                     'permission_callback' => [$this, 'get_items_permissions_check'],
                     'args' => $this->get_collection_params(),],
                 ['methods'=> TP_POST,'callback'=> [$this, 'create_item'],
                     'permission_callback' => [$this, 'create_item_permissions_check'],
                     'args'=> $this->get_endpoint_args_for_item_schema( TP_POST ),
                 ],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base . '/(?P<id>[\d]+)',
                ['args' => ['id' =>['description' => $this->__( 'Unique identifier for the user.' ),'type' => 'integer',]],
                 ['methods'=> TP_GET,'callback'=> [$this, 'get_item'],
                     'permission_callback' => [$this, 'get_item_permissions_check'],
                     'args'=>['context' => $this->get_context_param(['default' => 'view'])] ],
                 ['methods'=> TP_EDITABLE,
                     'callback'=> [$this,'update_item'],
                     'permission_callback' => [$this,'update_item_permissions_check'],
                     'args'=> $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),],
                 ['methods'=> TP_DELETE,
                     'callback'=> [ $this, 'delete_item'],
                     'permission_callback' => [$this, 'delete_item_permissions_check'],
                     'args'=>[
                         'force' => ['type' => 'boolean','default' => false,
                             'description' => $this->__( 'Required to be true, as users do not support trashing.' ),
                         ],
                         'reassign' => ['type' => 'integer',
                             'description' => $this->__( 'Reassign the deleted user\'s posts and links to this user ID.' ),
                             'required' => true,'sanitize_callback' => [$this, 'check_reassign'],
                         ],
                     ]
                 ],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base . '/me',
                [['methods' => TP_GET,'permission_callback' => '__return_true',
                        'callback' => [$this, 'get_current_item'],
                        'args' => ['context' => $this->get_context_param(['default' => 'view']),],
                 ],
                 ['methods' => TP_EDITABLE,'callback' => [$this, 'update_current_item'],
                     'permission_callback' => [$this, 'update_current_item_permissions_check'],
                     'args' => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                 ],
                 ['methods' => TP_DELETE,
                     'callback' => [$this, 'delete_current_item'],
                     'permission_callback' => [$this, 'delete_current_item_permissions_check'],
                     'args'=>
                         ['force' => ['type' => 'boolean','default' => false,
                            'description' => $this->__( 'Required to be true, as users do not support trashing.' ),],
                        'reassign' => ['type' => 'integer','description' => $this->__( 'Reassign the deleted user\'s posts and links to this user ID.' ),
                            'required' => true,'sanitize_callback' => [$this, 'check_reassign'],],
                     ]
                 ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//46
        public function check_reassign( $value ){//not used , $request, $param
            if ( is_numeric( $value ) ) return $value;
            if ( empty( $value ) || false === $value || 'false' === $value )
                return false;
            return new TP_Error('rest_invalid_param',
                $this->__( 'Invalid user parameter(s).' ),
                ['status' => 400]);
        }//167
        public function get_items_permissions_check( $request ):string{
            if ( ! empty( $request['roles'] ) && ! $this->_current_user_can( 'list_users' ) )
                return new TP_Error('rest_user_cannot_view',
                    $this->__( 'Sorry, you are not allowed to filter users by role.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( ! empty( $request['capabilities'] ) && ! $this->_current_user_can( 'list_users' ) )
                return new TP_Error('rest_user_cannot_view',
                    $this->__( 'Sorry, you are not allowed to filter users by capability.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( 'list_users' ) )
                return new TP_Error('rest_forbidden_context',
                    $this->__( 'Sorry, you are not allowed to list users.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            if ( in_array( $request['orderby'], array( 'email', 'registered_date' ), true ) && ! $this->_current_user_can( 'list_users' ) )
                return new TP_Error('rest_forbidden_orderby',
                    $this->__( 'Sorry, you are not allowed to order users by this parameter.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            if ( 'authors' === $request['who'] ) {
                $types = $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' );
                foreach ( $types as $type ) {
                    if ( $this->_post_type_supports( $type->name, 'author' ) && $this->_current_user_can( $type->cap->edit_posts ) )
                        return true;
                }
                return new TP_Error('rest_forbidden_who',
                    $this->__( 'Sorry, you are not allowed to query users by this parameter.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            }
            return true;
        }//191
        public function get_items(TP_REST_Request $request ):string{
            $registered = $this->get_collection_params();
            $parameter_mappings = ['exclude' => 'exclude','include' => 'include',
                'order' => 'order','per_page' => 'number','search' => 'search',
                'roles' => 'role__in','capabilities' => 'capability__in','slug' => 'nicename__in',];
            $prepared_args = [];
            foreach ( $parameter_mappings as $api_param => $wp_param ) {
                if ( isset( $registered[ $api_param ], $request[ $api_param ] ) )
                    $prepared_args[ $wp_param ] = $request[ $api_param ];
            }
            if ( isset( $registered['offset'] ) && ! empty( $request['offset'] ) )
                $prepared_args['offset'] = $request['offset'];
            else $prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
            if ( isset( $registered['orderby'] ) ) {
                $orderby_possibles = ['id' => 'ID','include' => 'include','name' => 'display_name',
                    'registered_date' => 'registered','slug' => 'user_nicename','include_slugs' => 'nicename__in',
                    'email' => 'user_email','url' => 'user_url',];
                $prepared_args['orderby'] = $orderby_possibles[ $request['orderby'] ];
            }
            if ( isset( $registered['who'] ) && ! empty( $request['who'] ) && 'authors' === $request['who'] )
                $prepared_args['who'] = 'authors';
            elseif ( ! $this->_current_user_can( 'list_users' ) )
                $prepared_args['has_published_posts'] = $this->_get_post_types( array( 'show_in_rest' => true ), 'names' );
            if ( ! empty( $request['has_published_posts'] ) )
                $prepared_args['has_published_posts'] = ( true === $request['has_published_posts'] )
                    ? $this->_get_post_types( ['show_in_rest' => true], 'names' ) : (array) $request['has_published_posts'];
            if ( ! empty( $prepared_args['search'] ) )  $prepared_args['search'] = '*' . $prepared_args['search'] . '*';
            $prepared_args = $this->_apply_filters( 'rest_user_query', $prepared_args, $request );
            $query = new TP_User_Query( $prepared_args );
            $users = [];
            foreach ( $query->get_results() as $user ) {
                $data    = $this->prepare_item_for_response( $user, $request );
                $users[] = $this->prepare_response_for_collection( $data );
            }
            $_response = $this->_rest_ensure_response( $users );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $per_page = (int) $prepared_args['number'];
            $page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );
            $prepared_args['fields'] = 'ID';
            $total_users = $query->get_total();
            if ( $total_users < 1 ) {
                unset( $prepared_args['number'], $prepared_args['offset'] );
                $count_query = new TP_User_Query( $prepared_args );
                $total_users = $count_query->get_total();
            }
            $response->header( 'X-TP-Total',$total_users );
            $max_pages = ceil( $total_users / $per_page );
            $response->header( 'X-TP-TotalPages', (int) $max_pages );
            $base = $this->_add_query_arg( $this->_url_encode_deep( $request->get_query_params() ), $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ) );
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
        }//254
        protected function _get_user( $id ){
            $error = new TP_Error('rest_user_invalid_id', $this->__( 'Invalid user ID.' ),['status' => 404]);
            if ( (int) $id <= 0 ) return $error;
            $user = $this->_get_user_data( (int) $id );
            if ( empty( $user ) || ! $user->exists() ) return $error;
            if ( $this->_is_multisite() && ! $this->_is_user_member_of_blog( $user->ID ) ) return $error;
            return $user;
        }//396
        public function get_item_permissions_check( $request ):string{
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            $types = $this->_get_post_types( array( 'show_in_rest' => true ), 'names' );
            if ( $this->_get_current_user_id() === $user->ID ) return true;
            if ( 'edit' === $request['context'] && ! $this->_current_user_can( 'list_users' ) ) {
                return new TP_Error('rest_user_cannot_view',
                    $this->__( 'Sorry, you are not allowed to list users.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            } elseif ( ! $this->_count_user_posts( $user->ID, $types ) && ! $this->_current_user_can( 'edit_user', $user->ID ) && ! $this->_current_user_can( 'list_users' ) )
                return new TP_Error('rest_user_cannot_view',
                    $this->__( 'Sorry, you are not allowed to list users.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//427
        public function get_item( $request ):string{
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            $user     = $this->prepare_item_for_response( $user, $request );
            return $this->_rest_ensure_response( $user );
        }//464
        public function get_current_item( $request ){
            $current_user_id = $this->_get_current_user_id();
            if ( empty( $current_user_id ) )
                return new TP_Error('rest_not_logged_in',
                    $this->__( 'You are not currently logged in.' ),
                    ['status' => UNAUTHORIZED]);
            $user     = $this->_tp_get_user_current();
            $response = $this->prepare_item_for_response( $user, $request );
            $response = $this->_rest_ensure_response( $response );
            return $response;
        }//484
        public function create_item_permissions_check( $request ):string{
            if ( ! $this->_current_user_can( 'create_users' ) )
                return new TP_Error('rest_cannot_create_user',
                    $this->__( 'Sorry, you are not allowed to create new users.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//510
        public function create_item(TP_REST_Request $request ):string{
            if ( ! empty( $request['id'] ) )
                return new TP_Error('rest_user_exists', $this->__( 'Cannot create existing user.' ), ['status' => BAD_REQUEST]);
            $schema = $this->get_item_schema();
            if ( ! empty( $request['roles'] ) && ! empty( $schema['properties']['roles'] ) ) {
                $check_permission = $this->_check_role_update( $request['id'], $request['roles'] );
                if ( $this->_init_error( $check_permission ) ) return $check_permission;
            }
            $user = $this->_prepare_item_for_database( $request );
            if ( $this->_is_multisite() ) {
                $ret = $this->_tp_mu_validate_user_signup( $user->user_login, $user->user_email );
                if ( $ret['errors'] instanceof TP_Error  && $this->_init_error( $ret['errors'] ) && $ret['errors']->has_errors() ) {
                    $error = new TP_Error(
                        'rest_invalid_param',
                        $this->__( 'Invalid user parameter(s).' ),
                        ['status' => BAD_REQUEST]
                    );
                    foreach ( $ret['errors']->errors as $code => $messages ) {
                        foreach ( $messages as $message ) $error->add( $code, $message );
                        $error_data = $error->get_error_data( $code );
                        if ( $error_data ) $error->add_data( $error_data, $code );
                    }
                    return $error;
                }
            }
            if ( $this->_is_multisite() ) {
                $user_id = $this->_tp_mu_create_user( $user->user_login, $user->user_pass, $user->user_email );
                if ( ! $user_id )
                    return new TP_Error('rest_user_create',$this->__( 'Error creating new user.' ),['status' => 500]);
                $user->ID = $user_id;
                $user_id  = $this->_tp_update_user( $this->_tp_slash( (array) $user ) );
                if ( $this->_init_error( $user_id ) ) return $user_id;
                /** @noinspection PhpUndefinedFieldInspection */
                $result = $this->_add_user_to_blog( $this->_get_site()->id, $user_id, '' );
                if ( $this->_init_error( $result ) ) return $result;
            } else {
                $user_id = $this->_tp_insert_user( $this->_tp_slash( (array) $user ) );
                if ( $this->_init_error( $user_id ) ) return $user_id;
            }
            $user = $this->_get_user_by( 'id', $user_id );
            $this->_do_action( 'rest_insert_user', $user, $request, true );
            if ( ! empty( $request['roles'] ) && ! empty( $schema['properties']['roles'] ) )
                array_map( array( $user, 'add_role' ), $request['roles'] );
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $user_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $user          = $this->_get_user_by( 'id', $user_id );
            $fields_update = $this->_update_additional_fields_for_object( $user, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_user', $user, $request, true );
            $response = $this->prepare_item_for_response( $user, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( 201 );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $user_id ) ) );
            return $response;
        }//531
        public function update_item_permissions_check(TP_REST_Request $request ):string{
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! empty( $request['roles'] ) ) {
                if ( ! $this->_current_user_can( 'promote_user', $user->ID ) )
                    return new TP_Error('rest_cannot_edit_roles',
                        $this->__( 'Sorry, you are not allowed to edit roles of this user.' ),
                        ['status' => $this->_rest_authorization_required_code()]);
                $request_params = array_keys( $request->get_params() );
                sort( $request_params );
                if ( array( 'id', 'roles' ) === $request_params ) return true;
            }
            if ( ! $this->_current_user_can( 'edit_user', $user->ID ) )
                return new TP_Error('rest_cannot_edit',
                    $this->__( 'Sorry, you are not allowed to edit this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//669
        public function update_item(TP_REST_Request $request ):string{
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            $id = $user->ID;
            if ( ! $user )
                return new TP_Error('rest_user_invalid_id', $this->__( 'Invalid user ID.' ),['status' => NOT_FOUND]);
            $owner_id = $this->_email_exists( $request['email'] );
            if ( $owner_id && $owner_id !== $id )
                return new TP_Error('rest_user_invalid_email',$this->__( 'Invalid email address.'),['status' => BAD_REQUEST]);
            if ( ! empty( $request['username'] ) && $request['username'] !== $user->user_login )
                return new TP_Error('rest_user_invalid_argument',$this->__( "Username isn't editable." ),['status' => BAD_REQUEST]);
            if ( ! empty( $request['slug'] ) && $request['slug'] !== $user->user_nicename && $this->_get_user_by( 'slug', $request['slug'] ) )
                return new TP_Error('rest_user_invalid_slug',$this->__( 'Invalid slug.'),['status' => BAD_REQUEST]);
            if ( ! empty( $request['roles'] ) ) {
                $check_permission = $this->_check_role_update( $id, $request['roles'] );
                if ( $this->_init_error( $check_permission ) ) return $check_permission;
            }
            $user = $this->_prepare_item_for_database( $request );
            $user->ID = $id;
            $user_id = $this->_tp_update_user( $this->_tp_slash( (array) $user ) );
            if ( $this->_init_error( $user_id ) ) return $user_id;
            $user = $this->_get_user_by( 'id', $user_id );
            $this->_do_action( 'rest_insert_user', $user, $request, false );
            if ( ! empty( $request['roles'] ) ) array_map( array( $user, 'add_role' ), $request['roles'] );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $user          = $this->_get_user_by( 'id', $user_id );
            $fields_update = $this->_update_additional_fields_for_object( $user, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_user', $user, $request, false );
            $response = $this->prepare_item_for_response( $user, $request );
            $response = $this->_rest_ensure_response( $response );
            return $response;
        }//712
        public function update_current_item_permissions_check( $request ){
            $request['id'] = $this->_get_current_user_id();
            return $this->update_item_permissions_check( $request );
        }//818
        public function update_current_item( $request ){
            $request['id'] = $this->_get_current_user_id();
            return $this->update_item( $request );
        }//832
        public function delete_item_permissions_check( $request ):string{
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'delete_user', $user->ID ) )
                return new TP_Error('rest_user_cannot_delete',
                    $this->__( 'Sorry, you are not allowed to delete this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//846
        public function delete_item(TP_REST_Request $request ):string{
            if ( $this->_is_multisite() )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The user cannot be deleted.' ),
                    ['status' => NOT_IMPLEMENTED]);
            $user = $this->_get_user( $request['id'] );
            if ( $this->_init_error( $user ) ) return $user;
            $id       = $user->ID;
            $reassign = false === $request['reassign'] ? null : $this->_abs_int( $request['reassign'] );
            $force    = isset( $request['force'] ) ? (bool) $request['force'] : false;
            if ( ! $force )
                return new TP_Error('rest_trash_not_supported',
                    sprintf( $this->__( "Users do not support trashing. Set '%s' to delete." ), 'force=true' ),
                    ['status' => NOT_IMPLEMENTED]);
            if ( ! empty( $reassign ) ) {
                if ( $reassign === $id || ! $this->_get_user_data( $reassign ) )
                    return new TP_Error('rest_user_invalid_reassign',
                        $this->__( 'Invalid user ID for reassignment.' ),
                        ['status' => BAD_REQUEST]
                    );
            }
            $request->set_param( 'context', 'edit' );
            $_previous = $this->prepare_item_for_response( $user, $request );
            $previous = null;
            if( $_previous instanceof TP_REST_Response ){
                $previous = $_previous;
            }
            $result = $this->_tp_delete_user( $id, $reassign );
            if ( ! $result )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The user cannot be deleted.' ),
                    ['status' => INTERNAL_SERVER_ERROR]);
            $response = new TP_REST_Response();
            $response->set_data( ['deleted' => true,'previous' => $previous->get_data(),]);
            $this->_do_action( 'rest_delete_user', $user, $response, $request );
            return $response;
        }//871
        public function delete_current_item_permissions_check( $request ) {
            $request['id'] = $this->_get_current_user_id();
            return $this->delete_item_permissions_check( $request );
        }//958
        public function delete_current_item( $request ) {
            $request['id'] = $this->_get_current_user_id();
            return $this->delete_item( $request );
        }//972
        public function prepare_item_for_response( $item, $request ):string{
            $user   = $item;
            $data   = [];
            $fields = $this->get_fields_for_response( $request );
            if ( in_array( 'id', $fields, true ) ) $data['id'] = $user->ID;
            if ( in_array( 'username', $fields, true ) ) $data['username'] = $user->user_login;
            if ( in_array( 'name', $fields, true ) ) $data['name'] = $user->display_name;
            if ( in_array( 'first_name', $fields, true ) ) $data['first_name'] = $user->first_name;
            if ( in_array( 'last_name', $fields, true ) ) $data['last_name'] = $user->last_name;
            if ( in_array( 'email', $fields, true ) ) $data['email'] = $user->user_email;
            if ( in_array( 'url', $fields, true ) ) $data['url'] = $user->user_url;
            if ( in_array( 'description', $fields, true ) ) $data['description'] = $user->description;
            if ( in_array( 'link', $fields, true ) ) $data['link'] = $this->_get_author_posts_url( $user->ID, $user->user_nicename );
            if ( in_array( 'locale', $fields, true ) ) $data['locale'] = $this->_get_user_locale( $user );
            if ( in_array( 'nickname', $fields, true ) ) $data['nickname'] = $user->nickname;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $user->user_nicename;
            if ( in_array( 'roles', $fields, true ) ) $data['roles'] = array_values( $user->roles );
            if ( in_array( 'registered_date', $fields, true ) )
                $data['registered_date'] = gmdate( 'c', strtotime( $user->user_registered ) );
            if ( in_array( 'capabilities', $fields, true ) ) $data['capabilities'] = (object) $user->allcaps;
            if ( in_array( 'extra_capabilities', $fields, true ) ) $data['extra_capabilities'] = (object) $user->caps;
            if ( in_array( 'avatar_urls', $fields, true ) ) $data['avatar_urls'] = $this->_rest_get_avatar_urls( $user );
            if ( in_array( 'meta', $fields, true ) ) $data['meta'] = $this->_meta->get_value( $user->ID, $request );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'embed';
            $data = $this->_add_additional_fields_to_object( $data, $request );
            $data = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links( $this->_prepare_links( $user ) );
            return $this->_apply_filters( 'rest_prepare_user', $response, $user, $request );
        }//988
        protected function _prepare_links( $user ):string{
            $links = [
                'self' => ['href' => $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $user->ID ) ),],
                'collection' => ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
            ];
            return $links;
        }//1097
        protected function _prepare_item_for_database( $request ):string{
            $prepared_user = new \stdClass;
            $schema = $this->get_item_schema();
            if ( isset( $request['email'] ) && ! empty( $schema['properties']['email'] ) )
                $prepared_user->user_email = $request['email'];
            if ( isset( $request['username'] ) && ! empty( $schema['properties']['username'] ) )
                $prepared_user->user_login = $request['username'];
            if ( isset( $request['password'] ) && ! empty( $schema['properties']['password'] ) )
                $prepared_user->user_pass = $request['password'];
            if ( isset( $request['id'] ) ) $prepared_user->ID = $this->_abs_int( $request['id'] );
            if ( isset( $request['name'] ) && ! empty( $schema['properties']['name'] ) )
                $prepared_user->display_name = $request['name'];
            if ( isset( $request['first_name'] ) && ! empty( $schema['properties']['first_name'] ) )
                $prepared_user->first_name = $request['first_name'];
            if ( isset( $request['last_name'] ) && ! empty( $schema['properties']['last_name'] ) )
                $prepared_user->last_name = $request['last_name'];
            if ( isset( $request['nickname'] ) && ! empty( $schema['properties']['nickname'] ) )
                $prepared_user->nickname = $request['nickname'];
            if ( isset( $request['slug'] ) && ! empty( $schema['properties']['slug'] ) )
                $prepared_user->user_nicename = $request['slug'];
            if ( isset( $request['description'] ) && ! empty( $schema['properties']['description'] ) )
                $prepared_user->description = $request['description'];
            if ( isset( $request['url'] ) && ! empty( $schema['properties']['url'] ) )
                $prepared_user->user_url = $request['url'];
            if ( isset( $request['locale'] ) && ! empty( $schema['properties']['locale'] ) )
                $prepared_user->locale = $request['locale'];
            if ( isset( $request['roles'] ) ) $prepared_user->role = false;
            return $this->_apply_filters( 'rest_pre_insert_user', $prepared_user, $request );
        }//1118
        protected function _check_role_update( $user_id, $roles ){
            $tp_roles = $this->_init_roles();
            foreach ( $roles as $role ){
                if ( ! isset( $tp_roles->role_objects[ $role ] ) )
                    return new TP_Error('rest_user_invalid_role',
                        sprintf( $this->__( 'The role %s does not exist.' ), $role ),
                        array( 'status' => 400 ));
                $potential_role = $tp_roles->role_objects[ $role ];
                if($potential_role  instanceof  TP_User){

                }
                if ( $this->_get_current_user_id() === $user_id && ! $potential_role->has_cap( 'edit_users' ) && ! ( $this->_is_multisite() && $this->_current_user_can( 'manage_sites' ) ))
                    return new TP_Error($this->__( 'Sorry, you are not allowed to give users that role.' ),
                        ['status' => $this->_rest_authorization_required_code()]
                    );
                $editable_roles = $this->_get_editable_roles();
                if ( empty( $editable_roles[ $role ] ) )
                    return new TP_Error('rest_user_invalid_role',
                        $this->__( 'Sorry, you are not allowed to give users that role.' ),
                        ['status' => FORBIDDEN]
                    );
            }
            return true;
        }//1201
        public function check_username( $value ){//not used , $request, $param
            $username = (string) $value;
            if ( ! $this->_validate_username( $username ) )
                return new TP_Error('rest_user_invalid_username',
                    $this->__( 'This username is invalid because it uses illegal characters. Please enter a valid username.' ),
                    ['status' => BAD_REQUEST]);
            $illegal_logins = (array) $this->_apply_filters( 'illegal_user_logins', [] );
            if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ), true ) )
                return new TP_Error('rest_user_invalid_username',
                    $this->__( 'Sorry, that username is not allowed.' ),
                    ['status' => BAD_REQUEST]);
            return $username;
        }//1263
        public function check_user_password( $value ){//not used , $request, $param
            $password = (string) $value;
            if ( empty( $password ) )
                return new TP_Error('rest_user_invalid_password',
                    $this->__( 'Passwords cannot be empty.' ),
                    ['status' => BAD_REQUEST]);
            if ( false !== strpos( $password, '\\' ) )
                return new TP_Error('rest_user_invalid_password',
                    sprintf( $this->__( 'Passwords cannot contain the "%s" character.' ),
                        '\\'),['status' => BAD_REQUEST] );
            return $password;
        }//1300
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'user','type' => 'object',
                'properties' => [
                    'id' => ['description' => $this->__( 'Unique identifier for the user.' ),
                        'type' => 'integer','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'username' => ['description' => $this->__( 'Login name for the user.' ),
                        'type'=> 'string','context'=> ['edit'],'required'=> true,
                        'arg_options' => ['sanitize_callback' => [$this, 'check_username'],],
                    ],
                    'name' => ['description' => $this->__( 'Display name for the user.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
                    ],
                    'first_name' => ['description' => $this->__( 'First name for the user.' ),
                        'type' => 'string','context' => ['edit'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
                    ],
                    'last_name' => ['description' => $this->__( 'Last name for the user.' ),
                        'type' => 'string','context' => ['edit'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
                    ],
                    'email' => ['description' => $this->__( 'The email address for the user.' ),
                        'type' => 'string','format' => 'email','context' => ['edit'], 'required' => true,
                    ],
                    'url' => ['description' => $this->__( 'URL of the user.' ),
                        'type' => 'string','format' => 'uri','context' => ['embed','view','edit'],
                    ],
                    'description' => ['description' => $this->__( 'Description of the user.' ),
                        'type' => 'string','context' => ['embed','view','edit'],
                    ],
                    'link' => ['description' => $this->__( 'Author URL of the user.' ),
                        'type' => 'string','format' => 'uri','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'locale' => ['description' => $this->__( 'Locale for the user.' ),
                        'type' => 'string','enum' => array_merge( ['', 'en_US'], $this->_get_available_languages() ),'context' => ['edit'],
                    ],
                    'nickname' => ['description' => $this->__( 'The nickname for the user.' ),
                        'type' => 'string','context' => ['edit'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
                    ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the user.' ),
                        'type' => 'string','context' => ['embed','view','edit'],
                        'arg_options' => ['sanitize_callback' =>[$this, 'sanitize_slug'],],
                    ],
                    'registered_date' => ['description' => $this->__( 'Registration date for the user.' ),
                        'type' => 'string','format' => 'date-time','context' => ['edit'],'readonly' => true,
                    ],
                    'roles' => ['description' => $this->__( 'Roles assigned to the user.' ),
                        'type' => 'array','items' => ['type' => 'string',],'context' => ['edit'],
                    ],
                    'password' => ['description' => $this->__( 'Password for the user (never included).' ),
                        'type' => 'string','context' => [], 'required' => true,
                        'arg_options' => ['sanitize_callback' => [$this, 'check_user_password'],],
                    ],
                    'capabilities' => ['description' => $this->__( 'All capabilities assigned to the user.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                    'extra_capabilities' => ['description' => $this->__( 'Any extra capabilities assigned to the user.' ),
                        'type' => 'object','context' => ['edit'],'readonly' => true,
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//1333
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $query_params['context']['default'] = 'view';
            $query_params['exclude'] = ['description' => $this->__( 'Ensure result set excludes specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],
            ];
            $query_params['include'] = ['description' => $this->__( 'Limit result set to specific IDs.' ),
                'type' => 'array','items' => ['type' => 'integer',],'default' => [],
            ];
            $query_params['offset'] = ['description' => $this->__( 'Offset the result set by a specific number of items.' ),'type' => 'integer',];
            $query_params['order'] = ['default' => 'asc','description' => $this->__( 'Order sort attribute ascending or descending.' ),
                'enum' => ['asc', 'desc'],'type' => 'string',
            ];
            $query_params['orderby'] = ['default' => 'name','description' => $this->__( 'Sort collection by user attribute.' ),
                'enum' => ['id','include','name','registered_date','slug','include_slugs','email','url',],
                'type' => 'string',
            ];
            $query_params['slug'] = ['description' => $this->__( 'Limit result set to users with one or more specific slugs.' ),
                'type'=> 'array','items'=> ['type' => 'string'],
            ];
            $query_params['roles'] = ['description' => $this->__( 'Limit result set to users matching at least one specific role provided. Accepts csv list or single role.' ),
                'type' => 'array','items'=> ['type' => 'string'],
            ];
            $query_params['capabilities'] = ['description' => $this->__( 'Limit result set to users matching at least one specific capability provided. Accepts csv list or single capability.' ),
                'type' => 'array','items'=> ['type' => 'string'],
            ];
            $query_params['who'] = ['description' => $this->__( 'Limit result set to users who are considered authors.' ),
                'type' => 'string','enum' => ['authors',],
            ];
            $query_params['has_published_posts'] = ['description' => $this->__( 'Limit result set to users who have published posts.' ),
                'type' => ['boolean', 'array'],'items' => ['type' => 'string','enum' => $this->_get_post_types( ['show_in_rest' => true], 'names' ),],
            ];
            return $this->_apply_filters( 'rest_user_collection_params', $query_params );
        }//1506
    }
}else die;