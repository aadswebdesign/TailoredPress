<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 19:07
 */
namespace TP_Core\Libs\Users;
use TP_Core\Libs\TP_Session_Tokens;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
if(ABSPATH){
    class TP_User_Meta_Session_Tokens extends TP_Session_Tokens {
        use _user_02, _user_03, _meta_01;
        protected $_user_id; //todo
        public function __construct($user_id = null){
            parent::__construct($user_id);
            $this->_user_id = $user_id;
        }

        protected function _get_sessions():array {
            $sessions = $this->_get_user_meta( $this->_user_id, 'session_tokens', true );
            if ( ! is_array( $sessions ) ) return array();
            $sessions = array_map( [$this, 'prepare_session'], $sessions );
            return array_filter( $sessions, [$this, 'is_still_valid'] );
        }
        protected function _prepare_session( $session ) {
            if ( is_int( $session ) ) return ['expiration' => $session];
            return $session;
        }
        protected function _get_session( $verifier ) {
            $sessions = $this->_get_sessions();
            if ( isset( $sessions[ $verifier ] ) ) return $sessions[ $verifier ];
            return null;
        }
        protected function _update_session( $verifier, $session = null ): void{
            $sessions = $this->_get_sessions();
            if ( $session ) $sessions[ $verifier ] = $session;
            else unset( $sessions[ $verifier ] );
            $this->_update_sessions( $sessions );
        }
        protected function _update_sessions( $sessions ): void{
            if ( $sessions ) $this->_update_user_meta( $this->_user_id, 'session_tokens', $sessions );
             else $this->_delete_user_meta( $this->_user_id, 'session_tokens' );
        }
        protected function _destroy_other_sessions( $verifier ): void{
            $session = $this->_get_session( $verifier );
            $this->_update_sessions([$verifier => $session]);
        }
        protected function _destroy_all_sessions(): void{
            $this->_update_sessions([]);
        }
        public static function drop_sessions(): void{
            (new self((new static)->_user_id))->_delete_metadata( 'user', 0, 'session_tokens', false, true );
        }
    }
}else die;