<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:02
 */
namespace TP_Core\Traits\Pluggables;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_hasher;
if(ABSPATH){
    trait _pluggable_04 {
        use _init_hasher;
        /**
         * @description Notify the blog admin of a user changing password, normally via email.
         * @param $user
         */
        protected function _tp_password_change_notification( $user ):void{
            if ( 0 !== strcasecmp( $user->user_email,  $this->_get_option( 'admin_email' ) ) ) {
                $message = sprintf( $this->__( 'Password changed for user: %s' ), $user->user_login ) . "\r\n";
                $blogname = $this->_tp_special_chars_decode(  $this->_get_option( 'blogname' ), ENT_QUOTES );
                $tp_password_change_notification_email = array(
                    'to'      => $this->_get_option( 'admin_email' ),
                    /* translators: Password change notification email subject. %s: Site title. */
                    'subject' => $this->__( '[%s] Password Changed' ),
                    'message' => $message,
                    'headers' => '',
                );
                $tp_password_change_notification_email =  $this->_apply_filters( 'tp_password_change_notification_email', $tp_password_change_notification_email, $user, $blogname );
                $this->_tp_mail(
                    $tp_password_change_notification_email['to'],
                    $this->_tp_special_chars_decode( sprintf( $tp_password_change_notification_email['subject'], $blogname ) ),
                    $tp_password_change_notification_email['message'],
                    $tp_password_change_notification_email['headers']);
            }
        }//1970
        /**
         * @description Email login credentials to a newly-registered user.
         * @param $user_id
         * @param string $notify
         * @return string
         */
        protected function _tp_new_user_notification( $user_id, $notify = '' ):string{
            if ( ! in_array( $notify, array( 'user', 'admin', 'both', '' ), true ) ) return false;
            $user = $this->_get_user_data( $user_id );
            $blogname = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            if ( 'user' !== $notify ) {
                $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
                $message = sprintf( $this->__( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
                $message .= sprintf( $this->__( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
                $message .= sprintf( $this->__( 'Email: %s' ), $user->user_email ) . "\r\n";
                $tp_new_user_notification_email_admin = array(
                    'to'      => $this->_get_option( 'admin_email' ),
                    'subject' => $this->__( '[%s] New User Registration' ),
                    'message' => $message,
                    'headers' => '',
                );
                $tp_new_user_notification_email_admin = $this->_apply_filters( 'tp_new_user_notification_email_admin', $tp_new_user_notification_email_admin, $user, $blogname );
                $this->_tp_mail(
                    $tp_new_user_notification_email_admin['to'],
                    $this->_tp_special_chars_decode( sprintf( $tp_new_user_notification_email_admin['subject'], $blogname ) ),
                    $tp_new_user_notification_email_admin['message'],
                    $tp_new_user_notification_email_admin['headers']
                );
                if ( $switched_locale ) $this->_restore_previous_locale();
            }
            $key = $this->_get_password_reset_key( $user );
            if ( $this->_init_error( $key ) ) { return false;}
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user ) );
            $message  = sprintf( $this->__( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
            $message .= $this->__( 'To set your password, visit the following address:' ) . "\r\n\r\n";
            $message .= $this->_network_site_url( "tp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";
            $message .= $this->_tp_login_url() . "\r\n";
            $tp_new_user_notification_email = array(
                'to' => $user->user_email,
                'subject' => $this->__( '[%s] Login Details' ),
                'message' => $message,
                'headers' => '',
            );
            $tp_new_user_notification_email = $this->_apply_filters( 'tp_new_user_notification_email', $tp_new_user_notification_email, $user, $blogname );
            $this->_tp_mail(
                $tp_new_user_notification_email['to'],
                $this->_tp_special_chars_decode( sprintf( $tp_new_user_notification_email['subject'], $blogname ) ),
                $tp_new_user_notification_email['message'],
                $tp_new_user_notification_email['headers']
            );
            if ( $switched_locale ) $this->_restore_previous_locale();
        }//2032 Email login credentials to a newly-registered user.
        /**
         * @description Returns the time-dependent variable for nonce creation.
         * @return float
         */
        protected function _tp_nonce_tick():float{
            $nonce_life = $this->_apply_filters( 'nonce_life', DAY_IN_SECONDS );
            return ceil( time() / ( $nonce_life / 2 ) );
        }//2165
        /**
         * @descriptionVerifies that a correct security nonce was used with time limit.
         * @param $nonce
         * @param int $action
         * @return bool|int
         */
        protected function _tp_verify_nonce( $nonce, $action = -1 ){
            $nonce = (string) $nonce;
            $_user  = $this->_tp_get_current_user();
            $user = null;
            if($_user instanceof TP_User ){$user = $_user; }
            $uid   = (int) $user->ID;
            if ( ! $uid ) $uid = $this->_apply_filters( 'nonce_user_logged_out', $uid, $action );
            if ( empty( $nonce ) ) return false;
            $token = $this->_tp_get_session_token();
            $i     = $this->_tp_nonce_tick();
            $expected = substr( $this->_tp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
            if ( hash_equals( $expected, $nonce ) ) return 1;
            $expected = substr( $this->_tp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
            if ( hash_equals( $expected, $nonce ) ) return 2;
            $this->_do_action( 'tp_verify_nonce_failed', $nonce, $action, $user, $token );
            return false;
        }//2193
        /**
         * @description Creates a cryptographic token tied to a specific action, user, user session,
         * @description . and window of time.
         * @param int $action
         * @return string
         */
        protected function _tp_create_nonce( $action = -1 ):string{
            $_user  = $this->_tp_get_current_user();
            $user = null;
            if($_user instanceof TP_User ){$user = $_user; }
            $uid  = (int) $user->ID;
            if ( ! $uid ) $uid = $this->_apply_filters( 'nonce_user_logged_out', $uid, $action );
            $token = $this->_tp_get_session_token();
            $i     = $this->_tp_nonce_tick();
            return substr( $this->_tp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
        }//2256
        /**
         * @description Returns a salt to add to hashes.
         * @param string $scheme
         * @return mixed
         */
        protected function _tp_salt( $scheme = 'auth' ){
            static $cached_salts = array();
            if ( isset( $cached_salts[ $scheme ] ) )
                return $this->_apply_filters( 'salt', $cached_salts[ $scheme ], $scheme );
            static $duplicated_keys;
            if ( null === $duplicated_keys ) {
                $duplicated_keys = array( 'put your unique phrase here' => true );
                foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE', 'SECRET' ) as $first ) {
                    foreach ( array( 'KEY', 'SALT' ) as $second ) {
                        if ( ! defined( "{$first}_{$second}" ) ) continue;
                        $value = constant( "{$first}_{$second}" );
                        $duplicated_keys[ $value ] = isset( $duplicated_keys[ $value ] );
                    }
                }
            }
            $values = ['key' => '', 'salt' => '',];
            if ( defined( 'SECRET_KEY' ) && SECRET_KEY && empty( $duplicated_keys[ SECRET_KEY ] ) )
                $values['key'] = SECRET_KEY;
            if ( 'auth' === $scheme && defined( 'SECRET_SALT' ) && SECRET_SALT && empty( $duplicated_keys[ SECRET_SALT ] ) )
                $values['salt'] = SECRET_SALT;
            if ( in_array( $scheme, array( 'auth', 'secure_auth', 'logged_in', 'nonce' ), true ) ) {
                foreach ( array( 'key', 'salt' ) as $type ) {
                    $const = strtoupper( "{$scheme}_{$type}" );
                    if ( defined( $const ) && constant( $const ) && empty( $duplicated_keys[ constant( $const ) ] ) ) {
                        $values[ $type ] = constant( $const );
                    } elseif ( ! $values[ $type ] ) {
                        $values[ $type ] = $this->_get_site_option( "{$scheme}_{$type}" );
                        if ( ! $values[ $type ] ) {
                            $values[ $type ] = $this->_tp_generate_password( 64, true, true );
                            $this->_update_site_option( "{$scheme}_{$type}", $values[ $type ] );
                        }
                    }
                }
            } else {
                if ( ! $values['key'] ) {
                    $values['key'] = $this->_get_site_option( 'secret_key' );
                    if ( ! $values['key'] ) {
                        $values['key'] = $this->_tp_generate_password( 64, true, true );
                        $this->_update_site_option( 'secret_key', $values['key'] );
                    }
                }
                $values['salt'] = hash_hmac( 'md5', $scheme, $values['key'] );
            }
            $cached_salts[ $scheme ] = $values['key'] . $values['salt'];
            return $this->_apply_filters( 'salt', $cached_salts[ $scheme ], $scheme );
        }//2304
        /**
         * @description Get hash of given string.
         * @param $data
         * @param string $scheme
         * @return string
         */
        protected function _tp_hash( $data, $scheme = 'auth' ):string{
            $salt = $this->_tp_salt( $scheme );
            return hash_hmac( 'md5', $data, $salt );//todo look for a stronger encryption?
        }//2385
        /**
         * @description Create a hash (encrypt) of a plain text password.
         * @param $password
         * @return string
         */
        protected function _tp_hash_password( $password ):string{
            $this->tp_hasher = $this->_init_hasher( 8, true );
            return $this->tp_hasher->HashPassword( trim( $password ) );
        }//2405
        /**
         * @description Checks the plaintext password against the encrypted Password.
         * @param $password
         * @param $hash
         * @param string $user_id
         * @return mixed
         */
        protected function _tp_check_password( $password, $hash, $user_id = '' ){
            $this->tp_hasher = $this->_init_hasher( 8, true );
            if ( strlen( $hash ) <= 32 ) {
                $check = hash_equals( $hash, md5( $password ) );
                if ( $check && $user_id ) {
                    $this->_tp_set_password( $password, $user_id );
                    $hash = $this->_tp_hash_password( $password );
                }
                return $this->_apply_filters( 'check_password', $check, $password, $hash, $user_id );
            }
            $check = $this->tp_hasher->CheckPassword( $password, $hash );
            return $this->_apply_filters( 'check_password', $check, $password, $hash, $user_id );
        }//2442
    }
}else die;