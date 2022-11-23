<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-8-2022
 * Time: 12:42
 */
namespace TP_Core\Libs\Recovery;
if(ABSPATH){
    class TP_Recovery_Mode_Link_Service  extends Recovery_Base {
        public const LOGIN_ACTION_ENTER   = 'enter_recovery_mode';
        public const LOGIN_ACTION_ENTERED = 'entered_recovery_mode';
        public function __construct(TP_Recovery_Mode_Cookie_Service $cookie_service, TP_Recovery_Mode_Key_Service $key_service){
            $this->_cookie_service = $cookie_service;
            $this->_key_service    = $key_service;
        }
        public function generate_url() {
            $token = $this->_key_service->generate_recovery_mode_token();
            $key   = $this->_key_service->generate_and_store_recovery_mode_key( $token );
            return $this->get_recovery_mode_begin_url( $token, $key );
        }
        public function handle_begin_link( $ttl ): void{
            if ( ! isset( $GLOBALS['pagenow'] ) || 'tp-login.php' !== $GLOBALS['pagenow'] ) return; //todo
            if ( ! isset( $_GET['action'], $_GET['rm_token'], $_GET['rm_key'] ) || self::LOGIN_ACTION_ENTER !== $_GET['action'] )
                return;
            $validated = $this->_key_service->validate_recovery_mode_key( $_GET['rm_token'], $_GET['rm_key'], $ttl );
            if ( $this->_init_error( $validated ) ) $this->_tp_die( $validated, '' );
            $this->_cookie_service->set_cookie();
            $url = $this->_add_query_arg( 'action', self::LOGIN_ACTION_ENTERED, $this->_tp_login_url() );
            $this->_tp_redirect( $url );
            die;
        }
        private function get_recovery_mode_begin_url( $token, $key ) {
            $url = $this->_add_query_arg(
                array(
                    'action'   => self::LOGIN_ACTION_ENTER,
                    'rm_token' => $token,
                    'rm_key'   => $key,
                ),
                $this->_tp_login_url()
            );
            return $this->_apply_filters( 'recovery_mode_begin_url', $url, $token, $key );
        }
    }
}else die;