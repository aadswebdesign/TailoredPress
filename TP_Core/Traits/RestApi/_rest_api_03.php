<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
use TP_Core\Libs\RestApi\TP_REST_Server;
use TP_Core\Traits\Inits\_init_rest;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _rest_api_03{
        use _init_rest;
        /**
         * @description Adds the REST API URL to the TP RSD endpoint.
         * @return bool|string
         */
        protected function _get_rest_output_rsd(){
            $api_root = $this->_get_rest_url();
            if ( empty( $api_root ) ) return false;
            $xml = "<api name='TP_API' blogID='1' preferred='false' apiLink='{$this->_esc_url($api_root)}' />";
            return $xml;
        }//938
        protected function _rest_output_rsd():void{
            echo $this->_get_rest_output_rsd();
        }
        /**
         * @description Outputs the REST API link tag into page header.
         */
        protected function _rest_output_link_tp_head():void{
            $api_root = $this->_get_rest_url();
            if ( empty( $api_root ) ) return;
            printf("<link rel='https://api.w.org/' href='%s'/>",$this->_esc_url($api_root));
            $resource = $this->_rest_get_queried_resource_route();
            if ( $resource ) printf("<link rel='alternate' type='application/json' href='%s' />",$this->_esc_url($this->_rest_url( $resource )));
        }//956
        /**
         * @description Sends a Link header for the REST API.
         */
        protected function _rest_output_link_header():void{
            if ( headers_sent() ) return;
            $api_root = $this->_get_rest_url();
            if ( empty( $api_root ) ) return;
            header( sprintf( "Link: <%s>; rel='https://api.w.org/'", $this->_esc_url_raw( $api_root ) ), false );
            $resource = $this->_rest_get_queried_resource_route();
            if ( $resource )
                header( sprintf("Link: <%s>; rel='alternate'; type='application/json'", $this->_esc_url_raw( $this->_rest_url( $resource ) )), false);
        }//977
        /**
         * @description Checks for errors when using cookie-based authentication.
         * @param $result
         * @return bool|TP_Error
         */
        protected function _rest_cookie_check_errors( $result ){
            if ( ! empty( $result ) ) return $result;
            if ( true !== $this->tp_rest_auth_cookie && $this->_is_user_logged_in() )
                return $result;
            $nonce = null;
            if (isset( $_REQUEST['_tpnonce'])) $nonce = $_REQUEST['_tpnonce'];
            elseif(isset( $_SERVER['HTTP_X_TP_NONCE'])) $nonce = $_SERVER['HTTP_X_TP_NONCE'];
            if ( null === $nonce ) {
                $this->_tp_set_current_user( 0 );
                return true;
            }
            $result = $this->_tp_verify_nonce( $nonce, 'tp_rest' );
            if ( ! $result )
                return new TP_Error( 'rest_cookie_invalid_nonce', $this->__( 'Cookie check failed' ), ['status' => 403]);
            $_rest_server = $this->_rest_get_server();
            if($_rest_server instanceof TP_REST_Server)
            $_rest_server->send_header( 'X-TP-Nonce', $this->_tp_create_nonce( 'tp_rest' ) );
            return true;
        }//1012
        /**
         * @description Collects cookie authentication status.
         */
        protected function _rest_cookie_collect_status():void{
            $status_type = $this->_current_action();
            if ( 'auth_cookie_valid' !== $status_type ) {
                $this->tp_rest_auth_cookie = substr( $status_type, 12 );
                return;
            }
            $this->tp_rest_auth_cookie = true;
        }//1066
        /**
         * @description Collects the status of authenticating with an application password.
         * @param $user_or_error
         * @param array $app_password
         */
        protected function _rest_application_password_collect_status( $user_or_error, $app_password = [] ):void{
            $this->__tp_rest_application_password_status = $user_or_error;
            if ( empty( $app_password['uuid'] ) ) $this->__tp_rest_application_password_uuid = null;
            else  $this->__tp_rest_application_password_uuid = $app_password['uuid'];
        }//1091
        /**
         * @description Gets the Application Password used for authenticating the request.
         * @return mixed
         */
        protected function _rest_get_authenticated_app_password(){
            return $this->__tp_rest_application_password_uuid;
        }//1112
        /**
         * @description Checks for errors when using application password-based authentication.
         * @param $result
         * @return mixed
         */
        protected function _rest_application_password_check_errors( $result ){
            if ( ! empty( $result ) ) return $result;
            $pw_status = $this->__tp_rest_application_password_status;
            if ( $this->_init_error( $pw_status) ) {
                if( $pw_status instanceof TP_Error ){
                    $data = $pw_status->get_error_data();
                    if ( ! isset( $data['status'] ) ) $data['status'] = 401;
                    $pw_status->add_data( $data );
                }
                return $pw_status;
            }
            if ( $this->__tp_rest_application_password_status instanceof TP_User ) return true;
            return $result;
        }//1129
        /**
         * @description Adds Application Passwords info to the REST API index.
         * @param $response
         * @return mixed
         */
        protected function _rest_add_application_passwords_to_index( $response ){
            if ( ! $this->_tp_is_application_passwords_available() )  return $response;
            $response->data['authentication']['application-passwords'] = [
                'endpoints' => ['authorization' => $this->_admin_url( 'authorize-application.php' ),],];//todo
            return $response;
        }//1163
        /**
         * @description Retrieves the avatar urls in various sizes.
         * @param $id_or_email
         * @return array
         */
        protected function _rest_get_avatar_urls( $id_or_email ):array{
            $avatar_sizes = $this->_rest_get_avatar_sizes();
            $urls = [];
            foreach ( $avatar_sizes as $size )
                $urls[ $size ] = $this->_get_avatar_url( $id_or_email,['size' => $size]);
            return $urls;
        }//1188
    }
}else die;