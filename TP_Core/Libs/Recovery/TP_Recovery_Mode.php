<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-8-2022
 * Time: 08:46
 */
namespace TP_Core\Libs\Recovery;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Paused_Extensions_Storage;
if(ABSPATH){
    class TP_Recovery_Mode extends Recovery_Base  {
        public function __construct() {
            $this->_cookie_service = new TP_Recovery_Mode_Cookie_Service;
            $this->_key_service    = new TP_Recovery_Mode_Key_Service();
            $this->_link_service   = new TP_Recovery_Mode_Link_Service( $this->_cookie_service, $this->_key_service );
            $this->_email_service  = new TP_Recovery_Mode_Email_Service( $this->_link_service );
        }
        public function initialize(): void{
            $this->_is_initialized = true;
            $this->_add_action( 'tp_logout', array( $this, 'exit_recovery_mode' ) );
            $this->_add_action( 'login_form_' . self::EXIT_ACTION, array( $this, 'handle_exit_recovery_mode' ) );
            $this->_add_action( 'recovery_mode_clean_expired_keys', array( $this, 'clean_expired_keys' ) );
            if ( ! $this->_tp_next_scheduled( 'recovery_mode_clean_expired_keys' ) && ! $this->_tp_installing() )
                $this->_tp_schedule_event( time(), 'daily', 'recovery_mode_clean_expired_keys' );
            if ( defined( 'TP_RECOVERY_MODE_SESSION_ID' ) ) {
                $this->_is_active  = true;
                $this->_session_id = TP_RECOVERY_MODE_SESSION_ID;
                return;
            }
            if ( $this->_cookie_service->is_cookie_set() ) {
                $this->_handle_cookie();
                return;
            }
            $this->_link_service->handle_begin_link( $this->_get_link_ttl() );
        }
        public function is_active(): bool{
            return $this->_is_active;
        }
        public function get_session_id(): string{
            return $this->_session_id;
        }
        public function is_initialized(): bool{
            return $this->_is_initialized;
        }
        public function handle_error( array $error ) {
            $extension = $this->_get_extension_for_error( $error );
            if ( ! $this->is_active() ) {
                if ( ! $this->_is_protected_endpoint() )
                    return new TP_Error( 'non_protected_endpoint', $this->__( 'Error occurred on a non-protected endpoint.' ) );
                return $this->_email_service->maybe_send_recovery_mode_email( $this->_get_email_rate_limit(), $error, $extension );
            }
            if ( ! $this->_store_error( $error ) )
                return new TP_Error( 'storage_error', $this->__( 'Failed to store the error.' ) );
            if ( headers_sent() ) return true;
            $this->_redirect_protected();
            return false;
        }
        public function exit_recovery_mode(): bool{
            if ( ! $this->is_active() ) return false;
            $this->_email_service->clear_rate_limit();
            $this->_cookie_service->clear_cookie();
            $paused_themes = $this->_tp_paused_themes();
            if( $paused_themes instanceof TP_Paused_Extensions_Storage ){
                $paused_themes->delete_all();
            }
            return true;
        }
        public function handle_exit_recovery_mode(): void{
            $redirect_to = $this->_tp_get_referer();
            if ( ! $redirect_to )
                $redirect_to = $this->_is_user_logged_in() ? $this->_admin_url() : $this->_home_url();
            if ( ! $this->is_active() ) {
                $this->_tp_safe_redirect( $redirect_to );
                die;
            }
            if ( ! isset( $_GET['action'] ) || self::EXIT_ACTION !== $_GET['action'] )
                return;
            if ( ! isset( $_GET['_tp_nonce'] ) || ! $this->_tp_verify_nonce( $_GET['_tp_nonce'], self::EXIT_ACTION ) )
                $this->_tp_die( $this->__( 'Exit recovery mode link expired.' ), 403 );
            if ( ! $this->exit_recovery_mode() )
                $this->_tp_die( $this->__( 'Failed to exit recovery mode. Please try again later.' ) );
            $this->_tp_safe_redirect( $redirect_to );
            die;
        }
        public function clean_expired_keys(): void{
            $this->_key_service->clean_expired_keys( $this->_get_link_ttl() );
        }
        protected function _handle_cookie(): void{
            $validated = $this->_cookie_service->validate_cookie();
            $session_id = $this->_cookie_service->get_session_id_from_cookie();
            if ( $this->_init_error( $validated ) ) {
                $this->_cookie_service->clear_cookie();
                $validated->add_data( array( 'status' => 403 ) );
                $this->_tp_die( $validated );
            }
            $this->_is_active  = true;
            $this->_session_id = $session_id;
        }
        protected function _get_email_rate_limit() {
            return $this->_apply_filters( 'recovery_mode_email_rate_limit', DAY_IN_SECONDS );
        }
        protected function _get_link_ttl() {
            $rate_limit = $this->_get_email_rate_limit();
            $valid_for  = $rate_limit;
            $valid_for = $this->_apply_filters( 'recovery_mode_email_link_ttl', $valid_for );
            return max( $valid_for, $rate_limit );
        }
        protected function _get_extension_for_error( $error ) {
            if ( ! isset( $error['file'] ) ) return false;
            $error_file    = $this->_tp_normalize_path( $error['file'] );
            if ( empty( $this->_tp_theme_directories ) ) return false;
            foreach ( $this->_tp_theme_directories as $theme_directory ) {
                $theme_directory = $this->_tp_normalize_path( $theme_directory );
                if ( 0 === strpos( $error_file, $theme_directory ) ) {
                    $path  = str_replace( $theme_directory . '/', '', $error_file );
                    $parts = explode( '/', $path );
                    return ['type' => 'theme','slug' => $parts[0],];
                }
            }
            return false;
        }
        protected function _store_error( $error ) {
            $extension = $this->_get_extension_for_error( $error );
            if ( ! $extension ) return false;
            if ($extension['type'] === 'theme') {
                $paused_themes = $this->_tp_paused_themes();
                if ($paused_themes instanceof TP_Paused_Extensions_Storage) {
                    return $paused_themes->set($extension['slug'], $error);
                }
            }
            return false;
        }
        protected function _redirect_protected(): void{
            $scheme = $this->__is_ssl() ? 'https://' : 'http://';
            $url = "{$scheme}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            $this->_tp_safe_redirect( $url );
            exit;
        }
        private function __is_ssl(): bool{
            if ( isset($_SERVER['HTTPS']) ) {
                if ( 'on' === strtolower($_SERVER['HTTPS']) )
                    return true;
                if ( '1' === $_SERVER['HTTPS'] )
                    return true;
            } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' === $_SERVER['SERVER_PORT'] ) )
                return true;
            return false;
        }//1410
    }
}else die;