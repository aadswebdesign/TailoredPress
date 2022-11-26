<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 05:44
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Pluggables\_pluggable_05;
if(ABSPATH){
    abstract class TP_Session_Tokens{
        use _filter_01;
        use _formats_11;
        use _pluggable_05;

        protected $_user_id;
        protected function __construct( $user_id ) {
            $this->_user_id = $user_id;
        }//34
        final public static function get_instance( $user_id ) {
            $manager = (new static($user_id))->_apply_filters( 'session_token_manager', 'TP_User_Meta_Session_Tokens' );
            return new $manager( $user_id );
        }//48
        private function __hash_token( $token ) {
            // If ext/hash is not present, use sha1() instead.
            if ( function_exists( 'hash' ) ) return hash( 'sha256', $token );
            else return sha1( $token );
        }//69
        final public function get( $token ) {
            $verifier = $this->__hash_token( $token );
            return $this->_get_session( $verifier );
        }//86
        final public function verify( $token ): bool{
            $verifier = $this->__hash_token( $token );
            return (bool) $this->_get_session( $verifier );
        }//104
        final public function create( $expiration ) {
            $session               = $this->_apply_filters( 'attach_session_information', array(), $this->_user_id );
            $session['expiration'] = $expiration;
            // IP address.
            if ( ! empty( $_SERVER['REMOTE_ADDR'] ) )
                $session['ip'] = $_SERVER['REMOTE_ADDR'];
            // User-agent.
            if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) )
                $session['ua'] = $this->_tp_unslash( $_SERVER['HTTP_USER_AGENT'] );
            // Timestamp.
            $session['login'] = time();
            $token = $this->_tp_generate_password( 43, false, false );
            $this->update( $token, $session );
            return $token;
        }//154
        final public function update( $token, $session ): void{
            $verifier = $this->__hash_token( $token );
            $this->_update_session( $verifier, $session );
        }//164
        final public function destroy( $token ): void{
            $verifier = $this->__hash_token( $token );
            $this->_update_session( $verifier, null );
        }
        final protected function _is_still_valid( $session ): bool{
            return $session['expiration'] >= time();
        }//
        final public function destroy_all(): void{
            $this->_destroy_all_sessions();
        }//176
        final public static function destroy_all_for_all_users(): void{
            $manager = (new static($user_id = null))->_apply_filters( 'session_token_manager', 'TP_User_Meta_Session_Tokens' );
            call_user_func( array( $manager, 'drop_sessions' ) );
        }
        final public function get_all(): array{
            return array_values( $this->_get_sessions() );
        }
        abstract protected function _get_sessions();
        abstract protected function _get_session( $verifier );
        abstract protected function _update_session( $verifier, $session = null );
        abstract protected function _destroy_other_sessions( $verifier );
        abstract protected function _destroy_all_sessions();
        public static function drop_sessions(): void {}
    }
}else die;