<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 18:34
 */
namespace TP_Core\Traits\Options;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _option_02 {
        use _init_db;
        protected function _set_transient( $transient, $value, $expiration = 0 ){return '';}//901
        protected function _delete_expired_transients( $force_db = false ){return '';}//1011
        protected function _tp_get_user_settings():string{
            $output  = "";
            if ( ! $this->_is_admin() || $this->_tp_doing_async() ){ return false;}
            $user_id = $this->_get_current_user_id();
            if ( ! $user_id ) return false;
            if ( ! $this->_is_user_member_of_blog() ) return false;
            $settings = (string) $this->_get_user_option( 'user-settings', $user_id );
            $_settings_cookie =  $_COOKIE[ 'tp-settings-' . $user_id ];
            if ( isset($_settings_cookie ) ) {
                $cookie = preg_replace( '/[^A-Za-z0-9=&_]/', '', $_COOKIE[ 'tp-settings-' . $user_id ] );
                if ( $cookie === $settings ) return false;
                $last_saved = (int) $this->_get_user_option( 'user-settings-time', $user_id );
                $_settings_time_cookie =  $_COOKIE[ 'tp-settings-time-' . $user_id ];
                $current    = isset( $_settings_time_cookie ) ? preg_replace( '/[^\D]/', '', $_COOKIE[ 'tp-settings-time-' . $user_id ] ) : 0;
                if ( $current > $last_saved ) {
                    $this->_update_user_option( $user_id, 'user-settings', $cookie, false );
                    $this->_update_user_option( $user_id, 'user-settings-time', time() - 5, false );
                    return false;
                }
            }
            $secure = ( 'https' === parse_url( $this->_admin_url(), PHP_URL_SCHEME ) );
            $output .= setcookie( 'tp-settings-' . $user_id, $settings, time() + YEAR_IN_SECONDS, SITE_COOKIE_PATH, null, $secure );
            $output .= setcookie( 'tp-settings-time-' . $user_id, time(), time() + YEAR_IN_SECONDS, SITE_COOKIE_PATH, null, $secure );
            $output .= $_COOKIE[ 'tp-settings-' . $user_id ] = $settings;
            return $output;
        }//1071
        protected function _tp_user_settings():void{
            echo $this->_tp_get_user_settings();
        }//1071
        protected function _get_user_setting( $name, $default = false ){return '';}//1123
        protected function _set_user_setting( $name, $value ){return '';}//1143
        protected function _delete_user_setting( $names ){return '';}//1167
        protected function _get_all_user_settings(){return '';}//1199
        protected function _tp_set_all_user_settings( $user_settings ){return '';}//1243
        protected function _delete_all_user_settings(){
            $user_id =  $this->_get_current_user_id();
            if ( ! $user_id ) return;
            $this->_update_user_option( $user_id, 'user-settings', '', false );
            setcookie( 'tp-settings-' . $user_id, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH );
        }//1279
        protected function _get_site_option( $option, $default = false, $deprecated = true ) {return '';}//1303
    }
}else die;