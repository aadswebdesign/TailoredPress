<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-3-2022
 * Time: 11:45
 */
namespace TP_Core\Libs\Recovery;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    final class TP_Recovery_Mode_Cookie_Service extends Recovery_Base{
        public function is_cookie_set(): bool{
            return ! empty( $_COOKIE[ RECOVERY_MODE_COOKIE ] );
        }
        public function set_cookie():string {
            $value = $this->__generate_cookie();
            $length = $this->_apply_filters( 'recovery_mode_cookie_length', WEEK_IN_SECONDS );
            $expire = time() + $length;
            setcookie( RECOVERY_MODE_COOKIE, $value, $expire, COOKIE_PATH, COOKIE_DOMAIN, $this->_is_ssl(), true );
            if ( COOKIE_PATH !== SITE_COOKIE_PATH )
                setcookie( RECOVERY_MODE_COOKIE, $value, $expire, SITE_COOKIE_PATH, COOKIE_DOMAIN, $this->_is_ssl(), true );
        }
        public function clear_cookie():string{
            setcookie( RECOVERY_MODE_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( RECOVERY_MODE_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
        }
        public function validate_cookie( $cookie = '' ) {
            if ( ! $cookie ) {
                if ( empty( $_COOKIE[ RECOVERY_MODE_COOKIE ] ) )
                    return new TP_Error( 'no_cookie', $this->__( 'No cookie present.' ) );
                $cookie = (string) $_COOKIE[ RECOVERY_MODE_COOKIE ];
            }
            $parts = $this->__parse_cookie( $cookie );
            if ( $this->_init_error( $parts ) )
                return $parts;
            @list( , $created_at, $random, $signature ) = $parts;
            if ( ! ctype_digit( $created_at ) )
                return new TP_Error( 'invalid_created_at', $this->__( 'Invalid cookie format.' ) );
            $length = $this->_apply_filters( 'recovery_mode_cookie_length', WEEK_IN_SECONDS );
            if ( time() > $created_at + $length )
                return new TP_Error( 'expired', $this->__( 'Cookie expired.' ) );
            $to_sign = sprintf( 'recovery_mode|%s|%s', $created_at, $random );
            $hashed  = $this->__recovery_mode_hash( $to_sign );
            if ( ! hash_equals( $signature, $hashed ) )
                return new TP_Error( 'signature_mismatch', $this->__( 'Invalid cookie.' ) );
            return true;
        }
        public function get_session_id_from_cookie( $cookie = '' ) {
            if ( ! $cookie ) {
                if ( empty( $_COOKIE[ RECOVERY_MODE_COOKIE ] ) )
                    return new TP_Error( 'no_cookie', $this->__( 'No cookie present.' ) );
                $cookie = (string) $_COOKIE[ RECOVERY_MODE_COOKIE ];
            }
            $parts = $this->__parse_cookie( $cookie );
            if ( $this->_init_error( $parts ) )
                return $parts;
            @list( , , $random ) = $parts;
            return sha1( $random );
        }
        private function __parse_cookie( $cookie ) {
            $cookie = base64_decode( $cookie );
            $parts  = explode( '|', $cookie );
            if ( 4 !== count( $parts ) )
                return new TP_Error( 'invalid_format', $this->__( 'Invalid cookie format.' ) );
            return $parts;
        }
        private function __generate_cookie() {
            $to_sign = sprintf( 'recovery_mode|%s|%s', time(), $this->_tp_generate_password( 20, false ) );
            $signed  = $this->__recovery_mode_hash( $to_sign );
            return base64_encode( sprintf( '%s|%s', $to_sign, $signed ) );
        }
        private function __recovery_mode_hash( $data ) {
            if ( ! defined( 'AUTH_KEY' ) || AUTH_KEY === 'put your unique phrase here' ) {
                $auth_key = $this->_get_site_option( 'recovery_mode_auth_key' );
                if ( ! $auth_key ) {
                    $auth_key = $this->_tp_generate_password( 64, true, true );
                    $this->_update_site_option( 'recovery_mode_auth_key', $auth_key );
                }
            } else $auth_key = AUTH_KEY;
            if ( ! defined( 'AUTH_SALT' ) || AUTH_SALT === 'put your unique phrase here' || AUTH_SALT === $auth_key ) {
                $auth_salt = $this->_get_site_option( 'recovery_mode_auth_salt' );
                if ( ! $auth_salt ) {
                    $auth_salt = $this->_tp_generate_password( 64, true, true );
                    $this->_update_site_option( 'recovery_mode_auth_salt', $auth_salt );
                }
            } else $auth_salt = AUTH_SALT;
            $secret = $auth_key . $auth_salt;
            return hash_hmac( 'sha1', $data, $secret );
        }
    }
}else die;
