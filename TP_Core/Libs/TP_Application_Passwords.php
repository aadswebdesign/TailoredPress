<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 18:10
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Traits\Pluggables\_pluggable_04;
use TP_Core\Traits\Pluggables\_pluggable_05;
use TP_Core\Traits\User\_user_03;
if(ABSPATH){
    class TP_Application_Passwords{
        use _action_01, _methods_13, _methods_17, _option_03, _pluggable_04;
        use _pluggable_05, _I10n_01, _formats_10, _user_03;
        public const USERMETA_KEY_APPLICATION_PASSWORDS = '_application_passwords';
        public const OPTION_KEY_IN_USE = 'using_application_passwords';
        public const PW_LENGTH = 24;
        public static function is_in_use(): bool{
            $network_id = (new self)->_get_main_network_id();
            return (bool) (new self)->_get_network_option( $network_id, self::OPTION_KEY_IN_USE );
        }//55
        public static function create_new_application_password( $user_id,array ...$args){
            if ( ! empty( $args['name'] ) )
                $args['name'] = (new self)->_sanitize_text_field( $args['name'] );
            if ( empty( $args['name'] ) )
                return new TP_Error( 'application_password_empty_name', (new self)->__( 'An application name is required to create an application password.' ), array( 'status' => 400 ) );
            if ( static::application_name_exists_for_user( $user_id, $args['name'] ) )
                return new TP_Error( 'application_password_duplicate_name', (new self)->__( 'Each application name should be unique.' ), array( 'status' => 409 ) );
             $new_password    = (new self)->_tp_generate_password( static::PW_LENGTH, false );
            $hashed_password = (new self)->_tp_hash_password( $new_password );
            $new_item = ['uuid' => (new self)->_tp_generate_uuid4(),'app_id' => empty( $args['app_id'] ) ? '' : $args['app_id'],
                'name' => $args['name'], 'password' => $hashed_password,'created' => time(),'last_used' => null,'last_ip' => null,];
            $passwords = static::get_user_application_passwords( $user_id );
            $passwords[] = $new_item;
            $saved = static::_set_user_application_passwords( $user_id, $passwords );
            if ( ! $saved ) return new TP_Error( 'db_error', (new self)->__( 'Could not save application password.' ) );
            $network_id = (new self)->_get_main_network_id();
            if ( ! (new self)->_get_network_option( $network_id, self::OPTION_KEY_IN_USE ) )
                (new self)->_update_network_option( $network_id, self::OPTION_KEY_IN_USE, true );
            (new self)->_do_action( 'tp_create_application_password', $user_id, $new_item, $new_password, $args );
            return array( $new_password, $new_item );
        }//73
        public static function get_user_application_passwords( $user_id ){
            $passwords = (new self)->_get_user_meta( $user_id, static::USERMETA_KEY_APPLICATION_PASSWORDS, true );
            if ( ! is_array( $passwords ) ) return [];
            $save = false;
            foreach ( $passwords as $i => $password ) {
                if ( ! isset( $password['uuid'] ) ) {
                    $passwords[ $i ]['uuid'] = (new self)->_tp_generate_uuid4();
                    $save                    = true;
                }
            }
            if ( $save ) static::_set_user_application_passwords( $user_id, $passwords );
            return $passwords;
        }//162
        public static function get_user_application_password( $user_id, $uuid ){
            $passwords = static::get_user_application_passwords( $user_id );
            foreach ( $passwords as $password ) {
                if ( $password['uuid'] === $uuid ) return $password;
            }
            return null;
        }//194
        public static function application_name_exists_for_user( $user_id, $name ): bool{
            $passwords = static::get_user_application_passwords( $user_id );
            foreach ( $passwords as $password ) {
                if ( strtolower( $password['name'] ) === strtolower( $name ) ) return true;
            }
            return false;
        }//215
        public static function update_application_password( $user_id, $uuid, array ...$update){
            $passwords = static::get_user_application_passwords( $user_id );
            foreach ( $passwords as &$item ) {
                if ( $item['uuid'] !== $uuid ) continue;
                if ( ! empty( $update['name'] ) ) $update['name'] = (new self)->_sanitize_text_field( $update['name'] );
                $save = false;
                if ( ! empty( $update['name'] ) && $item['name'] !== $update['name'] ) {
                    $item['name'] = $update['name'];
                    $save         = true;
                }
                if ( $save ) {
                    $saved = static::_set_user_application_passwords( $user_id, $passwords );
                    if ( ! $saved ) return new TP_Error( 'db_error', (new self)->__( 'Could not save application password.' ) );
                }
                (new self)->_do_action( 'tp_update_application_password', $user_id, $item, $update );
                return true;
            }
            return new TP_Error( 'application_password_not_found', (new self)->__( 'Could not find an application password with that id.' ) );
        }//237
        public static function record_application_password_usage( $user_id, $uuid ){
            $passwords = static::get_user_application_passwords( $user_id );
            foreach ( $passwords as &$password ) {
                if ( $password['uuid'] !== $uuid ) continue;
                if ( $password['last_used'] + DAY_IN_SECONDS > time() ) return true;
                $password['last_used'] = time();
                $password['last_ip']   = $_SERVER['REMOTE_ADDR'];
                $saved = static::_set_user_application_passwords( $user_id, $passwords );
                if ( ! $saved ) return new TP_Error( 'db_error', (new self)->__( 'Could not save application password.' ) );
                return true;
            }
            return new TP_Error( 'application_password_not_found', (new self)->__( 'Could not find an application password with that id.' ) );
        }//290
        public static function delete_application_password( $user_id, $uuid ){
            $passwords = static::get_user_application_passwords( $user_id );
            foreach ( $passwords as $key => $item ) {
                if ( $item['uuid'] === $uuid ) {
                    unset( $passwords[ $key ] );
                    $saved = static::_set_user_application_passwords( $user_id, $passwords );
                    if ( ! $saved )  return new TP_Error( 'db_error', (new self)->__( 'Could not delete application password.' ) );
                    (new self)->_do_action( 'tp_delete_application_password', $user_id, $item );
                    return true;
                }
            }
            return new TP_Error( 'application_password_not_found', (new self)->__( 'Could not find an application password with that id.' ) );
        }//328
        public static function delete_all_application_passwords( $user_id ){
            $passwords = static::get_user_application_passwords( $user_id );
            if ( $passwords ) {
                $saved = static::_set_user_application_passwords( $user_id,[]);
                if ( ! $saved ) return new TP_Error( 'db_error', (new self)->__( 'Could not delete application passwords.' ) );
                foreach ( $passwords as $item )
                    (new self)->_do_action( 'tp_delete_application_password', $user_id, $item );
                return count( $passwords );
            }
            return 0;
        }//365
        protected static function _set_user_application_passwords( $user_id, $passwords ){
            return (new self)->_update_user_meta( $user_id, static::USERMETA_KEY_APPLICATION_PASSWORDS, $passwords );
        }//396
        public static function chunk_password( $raw_password ): string{
            $raw_password = preg_replace( '/[^a-z\d]/i', '', $raw_password );
            return trim( chunk_split( $raw_password, 4, ' ' ) );
        }//408
    }
}else die;