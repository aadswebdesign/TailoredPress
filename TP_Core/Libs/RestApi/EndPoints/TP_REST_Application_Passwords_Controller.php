<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Application_Passwords;
if(ABSPATH){
    class TP_REST_Application_Passwords_Controller extends TP_REST_Controller {
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'users/(?P<user_id>(?:[\d]+|me))/application-passwords';
        }//24
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'get_items_permissions_check' ),
                        'args'                => $this->get_collection_params(),
                    ),
                    array(
                        'methods'             => TP_POST,
                        'callback'            => array( $this, 'create_item' ),
                        'permission_callback' => array( $this, 'create_item_permissions_check' ),
                        'args'                => $this->get_endpoint_args_for_item_schema(),
                    ),
                    array(
                        'methods'             => TP_DELETE,
                        'callback'            => array( $this, 'delete_items' ),
                        'permission_callback' => array( $this, 'delete_items_permissions_check' ),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/introspect',
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_current_item' ),
                        'permission_callback' => array( $this, 'get_current_item_permissions_check' ),
                        'args'                => array(
                            'context' => $this->get_context_param( array( 'default' => 'view' ) ),
                        ),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<uuid>[\w\-]+)',
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_item' ),
                        'permission_callback' => array( $this, 'get_item_permissions_check' ),
                        'args'                => array(
                            'context' => $this->get_context_param( array( 'default' => 'view' ) ),
                        ),
                    ),
                    array(
                        'methods'             => TP_EDITABLE,
                        'callback'            => array( $this, 'update_item' ),
                        'permission_callback' => array( $this, 'update_item_permissions_check' ),
                        'args'                => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ),
                    array(
                        'methods'             => TP_DELETE,
                        'callback'            => array( $this, 'delete_item' ),
                        'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
        }//34
        public function get_items_permissions_check( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'list_app_passwords', $user->ID ) )
                return new TP_Error('rest_cannot_list_application_passwords',
                    $this->__( 'Sorry, you are not allowed to list application passwords for this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//112
        public function get_items( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $passwords = TP_Application_Passwords::get_user_application_passwords( $user->ID );
            $response  = [];
            foreach ((array) $passwords as $password )
                $response[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $password, $request ));
            return new TP_REST_Response( $response );
        }//138
        public function get_item_permissions_check( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'read_app_password', $user->ID, $request['uuid'] ) ) {
                return new TP_Error(
                    'rest_cannot_read_application_password',
                    $this->__( 'Sorry, you are not allowed to read this application password.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            return true;
        }//165
        public function get_item( $request ):string{
            $password = $this->_get_application_password( $request );
            if ( $this->_init_error( $password ) ) return $password;
            return $this->prepare_item_for_response( $password, $request );
        }//191
        public function create_item_permissions_check( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'create_app_password', $user->ID ) )
                return new TP_Error('rest_cannot_create_application_passwords',
                    $this->__( 'Sorry, you are not allowed to create application passwords for this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//209
        public function create_item( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $prepared = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared ) ) return $prepared;
            $created = TP_Application_Passwords::create_new_application_password( $user->ID, $this->_tp_slash( (array) $prepared ) );
            if ( $this->_init_error( $created ) ) return $created;
            $password = $created[0];
            $item     = TP_Application_Passwords::get_user_application_password( $user->ID, $created[1]['uuid'] );
            $item['new_password'] = TP_Application_Passwords::chunk_password( $password );
            $fields_update        = $this->_update_additional_fields_for_object( $item, $request );
            if ( $this->_init_error( $fields_update ) )return $fields_update;
            $this->_do_action( 'rest_after_insert_application_password', $item, $request, true );
            if( $request instanceof TP_REST_Request ){}//todo
            $request->set_param( 'context', 'edit' );
            $_response = $this->prepare_item_for_response($password, $request);
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( 201 );
            $response->header( 'Location', $response->get_links()['self'][0]['href'] );
            return $response;
        }//235
        public function update_item_permissions_check( $request ):string {
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'edit_app_password', $user->ID, $request['uuid'] ) )
                return new TP_Error('rest_cannot_edit_application_password',
                    $this->__( 'Sorry, you are not allowed to edit this application password.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//292
        public function update_item( $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $item = $this->_get_application_password( $request );
            if ( $this->_init_error( $item ) ) return $item;
            $prepared = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared ) ) return $prepared;
            $saved = TP_Application_Passwords::update_application_password( $user->ID, $item['uuid'], $this->_tp_slash( (array) $prepared ) );
            if ( $this->_init_error( $saved ) ) return $saved;
            $fields_update = $this->_update_additional_fields_for_object( $item, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $item = TP_Application_Passwords::get_user_application_password( $user->ID, $item['uuid'] );
            $this->_do_action( 'rest_after_insert_application_password', $item, $request, false );
            if( $request instanceof TP_REST_Request ){}//todo
            $request->set_param( 'context', 'edit' );
            return $this->prepare_item_for_response( $item, $request );
        }//318
        public function delete_items_permissions_check( $request ){
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'delete_app_passwords', $user->ID ) ) {
                return new TP_Error('rest_cannot_delete_application_passwords',
                    $this->__( 'Sorry, you are not allowed to delete application passwords for this user.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            return true;
        }//366
        public function delete_items( $request ){
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $deleted = TP_Application_Passwords::delete_all_application_passwords( $user->ID );
            if ( $this->_init_error( $deleted ) ) return $deleted;
            return new TP_REST_Response(['deleted' => true,'count' => $deleted,]);
        }//392
        public function delete_item_permissions_check($request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_current_user_can( 'delete_app_password', $user->ID, $request['uuid'] ) )
                return new TP_Error('rest_cannot_delete_application_password',
                    $this->__( 'Sorry, you are not allowed to delete this application password.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//421
        public function delete_item( $request ):string
        {
            $user = $this->_get_user($request);
            if ($this->_init_error($user)) return $user;
            $password = $this->_get_application_password($request);
            if ($this->_init_error($password)) return $password;
            if ($request instanceof TP_REST_Request) {//todo}
                $request->set_param('context', 'edit');
                $_previous = $this->prepare_item_for_response($password, $request);
                $previous = null;
                if( $_previous instanceof TP_REST_Response ){
                    $previous = $_previous;
                }
                $deleted = TP_Application_Passwords::delete_application_password($user->ID, $password['uuid']);
                if ($this->_init_error($deleted)) return $deleted;
                return new TP_REST_Response(['deleted' => true, 'previous' => $previous->get_data(),]);
            }
        }//447
        public function get_current_item_permissions_check( $request ){
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            if ( $this->_get_current_user_id() !== $user->ID )
                return new TP_Error(
                    'rest_cannot_introspect_app_password_for_non_authenticated_user',
                    $this->__( 'The authenticated application password can only be introspected for the current user.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//484
        public function get_current_item( $request ){
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $uuid = $this->_rest_get_authenticated_app_password();
            if ( ! $uuid )
                return new TP_Error('rest_no_authenticated_app_password',
                    $this->__( 'Cannot introspect application password.' ),
                    ['status' => NOT_FOUND]);
            $password = TP_Application_Passwords::get_user_application_password( $user->ID, $uuid );
            if ( ! $password ) {
                return new TP_Error('rest_application_password_not_found',
                    $this->__( 'Application password not found.' ),
                    ['status' => INTERNAL_SERVER_ERROR]
                );
            }
            return $this->prepare_item_for_response( $password, $request );
        }//510
        protected function _prepare_item_for_database( $request ):string{
            $prepared = (object) ['name' => $request['name'],];
            if ( $request['app_id'] && ! $request['uuid'] ) $prepared->app_id = $request['app_id'];
            return $this->_apply_filters( 'rest_pre_insert_application_password', $prepared, $request );
        }//577
        public function prepare_item_for_response( $item, $request ):string{
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $prepared = array(
                'uuid'      => $item['uuid'],
                'app_id'    => empty( $item['app_id'] ) ? '' : $item['app_id'],
                'name'      => $item['name'],
                'created'   => gmdate( 'Y-m-d\TH:i:s', $item['created'] ),
                'last_used' => $item['last_used'] ? gmdate( 'Y-m-d\TH:i:s', $item['last_used'] ) : null,
                'last_ip'   => $item['last_ip'] ?: null,
            );
            if ( isset( $item['new_password'] ) )
                $prepared['password'] = $item['new_password'];
            $prepared = $this->_add_additional_fields_to_object( $prepared, $request );
            $prepared = $this->filter_response_by_context( $prepared, $request['context'] );
            $response = new TP_REST_Response( $prepared );
            $response->add_links( $this->_prepare_links( $user, $item ) );
            return $this->_apply_filters( 'rest_prepare_application_password', $response, $item, $request );
        }//606
        protected function _prepare_links( TP_User $user, $item ): array{
            return [
                'self' => [
                    'href' => $this->_rest_url( sprintf( '%s/users/%d/application-passwords/%s', $this->_namespace, $user->ID, $item['uuid'] ) ),
                ],
            ];
        }
        protected function _get_user( $request ){
            if ( ! $this->_tp_is_application_passwords_available() )
                return new TP_Error('application_passwords_disabled',$this->__( 'Application passwords are not available.' ),['status' => NOT_IMPLEMENTED]);
            $error = new TP_Error('rest_user_invalid_id',$this->__( 'Invalid user ID.' ),['status' => NOT_FOUND ]);
            $id = $request['user_id'];
            if ( 'me' === $id ) {
                if ( ! $this->_is_user_logged_in() )
                    return new TP_Error('rest_not_logged_in', $this->__( 'You are not currently logged in.' ), ['status' => UNAUTHORIZED]);
                $user = $this->_tp_get_user_current();
            } else {
                $id = (int) $id;
                if ( $id <= 0 ) return $error;
                $user = $this->_get_user_data( $id );
            }
            if ( empty( $user ) || ! $user->exists() ) return $error;
            if ( $this->_is_multisite() && ! $this->_is_user_member_of_blog( $user->ID ) ) return $error;
            if ( ! $this->_tp_is_application_passwords_available_for_user( $user ) )
                return new TP_Error('application_passwords_disabled_for_user',
                    $this->__( 'Application passwords are not available for your account. Please contact the site administrator for assistance.' ),
                    ['status' => NOT_IMPLEMENTED]);
            return $user;
        }//669
        protected function _get_application_password( $request ){
            $user = $this->_get_user( $request );
            if ( $this->_init_error( $user ) ) return $user;
            $password = TP_Application_Passwords::get_user_application_password( $user->ID, $request['uuid'] );
            if ( ! $password ) return new TP_Error('rest_application_password_not_found', $this->__( 'Application password not found.' ),['status' => NOT_FOUND ]);
            return $password;
        }//733
        public function get_collection_params():array{
            return ['context' => $this->get_context_param( ['default' => 'view'] ),];
        }//760
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $this->_schema = [
                '$schema'    => 'http://json-schema.org/draft-04/schema#',
                'title'      => 'application-password',
                'type'       => 'object',
                'properties' => [
                    'uuid'      => [
                        'description' => $this->__( 'The unique identifier for the application password.' ),
                        'type'        => 'string',
                        'format'      => 'uuid',
                        'context'     => ['view', 'edit', 'embed'],
                        'readonly'    => true,
                    ],
                    'app_id'    => [
                        'description' => $this->__( 'A UUID provided by the application to uniquely identify it. It is recommended to use an UUID v5 with the URL or DNS namespace.' ),
                        'type'        => 'string',
                        'format'      => 'uuid',
                        'context'     => ['view', 'edit','embed'],
                    ],
                    'name'      => [
                        'description' => $this->__( 'The name of the application password.' ),
                        'type'        => 'string',
                        'required'    => true,
                        'context'     => ['view', 'edit','embed'],
                        'minLength'   => 1,
                        'pattern'     => '.*\S.*',
                    ],
                    'password'  => [
                        'description' => $this->__( 'The generated password. Only available after adding an application.' ),
                        'type'        => 'string',
                        'context'     => ['edit'],
                        'readonly'    => true,
                    ],
                    'created'   => [
                        'description' => $this->__( 'The GMT date the application password was created.' ),
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => ['view', 'edit'],
                        'readonly'    => true,
                    ],
                    'last_used' => [
                        'description' => $this->__( 'The GMT date the application password was last used.' ),
                        'type'        => ['string', 'null'],
                        'format'      => 'date-time',
                        'context'     => ['view', 'edit'],
                        'readonly'    => true,
                    ],
                    'last_ip'   => [
                        'description' => $this->__( 'The IP address the application password was last used by.' ),
                        'type'        => ['string', 'null'],
                        'format'      => 'ip',
                        'context'     => ['view', 'edit'],
                        'readonly'    => true,
                    ],
                ],
            ];
            return $this->_add_additional_fields_schema( $this->_schema );
        }//773
    }
}else {die;}