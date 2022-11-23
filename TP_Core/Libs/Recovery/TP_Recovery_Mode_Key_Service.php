<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-8-2022
 * Time: 12:12
 */
namespace TP_Core\Libs\Recovery;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_PasswordHash;
if(ABSPATH){
    class TP_Recovery_Mode_Key_Service extends Recovery_Base  {
        private $__option_name = 'recovery_keys';
        private $__tp_hasher;
        public function generate_recovery_mode_token() {
            return $this->_tp_generate_password( 22, false );
        }
        public function generate_and_store_recovery_mode_key( $token ) {
            $key = $this->_tp_generate_password( 22, false );
            if ( empty( $this->__tp_hasher ) ) {
                $this->__tp_hasher = new TP_PasswordHash( 8, true );
            }
            $hashed = $this->__tp_hasher->HashPassword( $key );
            $records = $this->__get_keys();
            $records[ $token ] = array(
                'hashed_key' => $hashed,
                'created_at' => time(),
            );
            $this->__update_keys( $records );
            $this->_do_action( 'generate_recovery_mode_key', $token, $key );
            return $key;
        }
        public function validate_recovery_mode_key( $token, $key, $ttl ) {
            $records = $this->__get_keys();
            if ( ! isset( $records[ $token ] ) )
                return new TP_Error( 'token_not_found', $this->__( 'Recovery Mode not initialized.' ) );
            $record = $records[ $token ];
            $this->__remove_key( $token );
            if ( ! is_array( $record ) || ! isset( $record['hashed_key'], $record['created_at'] ) )
                return new TP_Error( 'invalid_recovery_key_format', $this->__( 'Invalid recovery key format.' ) );
            if ( ! $this->_tp_check_password( $key, $record['hashed_key'] ) )
                return new TP_Error( 'hash_mismatch', $this->__( 'Invalid recovery key.' ) );
            if ( time() > $record['created_at'] + $ttl )
                return new TP_Error( 'key_expired', $this->__( 'Recovery key expired.' ) );
            return true;
        }
        public function clean_expired_keys( $ttl ): void{
            $records = $this->__get_keys();
            foreach ( $records as $key => $record ) {
                if ( ! isset( $record['created_at'] ) || time() > $record['created_at'] + $ttl )
                    unset( $records[ $key ] );
            }
            $this->__update_keys( $records );
        }
        private function __remove_key( $token ): void{
            $records = $this->__get_keys();
            if ( ! isset( $records[ $token ] ) )
                return;
            unset( $records[ $token ] );
            $this->__update_keys( $records );
        }
        private function __get_keys(): array{
            return (array) $this->_get_option( $this->__option_name, array() );
        }
        private function __update_keys( array $keys ) {
            return $this->_update_option( $this->__option_name, $keys );
        }
    }
}else die;