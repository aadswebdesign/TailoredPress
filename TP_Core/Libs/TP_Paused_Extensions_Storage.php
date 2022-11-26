<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-3-2022
 * Time: 06:02
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Misc\_error_protection;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Libs\Recovery\TP_Recovery_Mode;
if(ABSPATH){
    class TP_Paused_Extensions_Storage {
        use _error_protection;
        use _option_01;
        protected $_type;
        public function __construct( $extension_type ) {
            $this->_type = $extension_type;
        }
        public function set( $extension, $error ) {
            if ( ! $this->_is_api_loaded() ) return false;
            $option_name = $this->_get_option_name();
            if ( ! $option_name ) return false;
            $paused_extensions = (array) $this->_get_option( $option_name, [] );
            if ( isset( $paused_extensions[ $this->_type ][ $extension ] ) && $paused_extensions[ $this->_type ][ $extension ] === $error )
                return true;
            $paused_extensions[ $this->_type ][ $extension ] = $error;
            return $this->_update_option( $option_name, $paused_extensions );
        }
        public function delete( $extension ) {
            if ( ! $this->_is_api_loaded() ) return false;
            $option_name = $this->_get_option_name();
            if ( ! $option_name ) return false;
            $paused_extensions = (array) $this->_get_option( $option_name, [] );
            // Do not delete if no error is stored.
            if ( ! isset( $paused_extensions[ $this->_type ][ $extension ] ) ) return true;
            unset( $paused_extensions[ $this->_type ][ $extension ] );
            if ( empty( $paused_extensions[ $this->_type ] ) ) unset( $paused_extensions[ $this->_type ] );
            if ( ! $paused_extensions ) return $this->_delete_option( $option_name );
            return $this->_update_option( $option_name, $paused_extensions );
        }
        public function get( $extension ) {
            if ( ! $this->_is_api_loaded() ) return null;
            $paused_extensions = $this->get_all();
            if ( ! isset( $paused_extensions[ $extension ] ) ) return null;
            return $paused_extensions[ $extension ];
        }
        public function get_all() {
            if ( ! $this->_is_api_loaded() ) return [];
            $option_name = $this->_get_option_name();
            if ( ! $option_name ) return [];
            $paused_extensions = (array) $this->_get_option( $option_name, []);
            return $paused_extensions[ $this->_type ] ?? [];
        }
        public function delete_all() {
            if ( ! $this->_is_api_loaded() ) return false;
            $option_name = $this->_get_option_name();
            if ( ! $option_name ) return false;
            $paused_extensions = (array) $this->_get_option( $option_name, [] );
            unset( $paused_extensions[ $this->_type ] );
            if ( ! $paused_extensions ) return $this->_delete_option( $option_name );
            return $this->_update_option( $option_name, $paused_extensions );
        }
        protected function _is_api_loaded(): bool{
            return function_exists( [$this,'get_option'] );
        }
        protected function _get_option_name(): string{
            $_recovery_mode = $this->_tp_recovery_mode();
            $recovery_mode = null;
            if($_recovery_mode  instanceof TP_Recovery_Mode ){
                $recovery_mode = $_recovery_mode;
            }
            if ( ! $recovery_mode->is_active()) return '';//todo
            $session_id = $recovery_mode->get_session_id();//todo
            if ( empty( $session_id ) ) return '';
            return "{$session_id}_paused_extensions";
        }
    }
}else die;