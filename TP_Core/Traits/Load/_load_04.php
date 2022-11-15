<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Load;
use TP_Core\Traits\Inits\_init_blog;
use TP_Admin\Traits\AdminInits\_adm_init_screen;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _load_04 {
        use _adm_init_screen;
        use _init_blog;
        use _init_locale;
        /**
         * @description Whether the current request is for a user admin screen.
         * @return bool
         */
        protected function _is_user_admin():bool{
            $this->tp_current_screen = $this->_init_get_screen();
            if ( isset( $this->tp_current_screen ) ) {
                return $this->tp_current_screen->get_in_admin('user');
            } elseif ( defined( 'TP_USER_ADMIN' ) )
                return TP_USER_ADMIN;
            return false;
        }//1210
        /**
         * @description If Multisite is enabled.
         * @return bool
         */
        protected function _is_multisite():bool{
            if ( defined( 'MULTISITE' ) ) return MULTISITE;
            if ( defined( 'SUBDOMAIN_INSTALL' ) || defined( 'VHOST' ) || defined( 'SUNRISE' ) )
                return true;
            return false;
        }//1227
        /**
         * @description Retrieve the current site ID.
         * @return mixed
         */
        protected function _get_current_blog_id(){
            $blog_id = $this->_init_blog_id();
            return $this->_abs_int( $blog_id );
        }//1248
        /**
         * @description Retrieves the current network ID.
         * @return int
         */
        protected function _get_current_network_id():int{
            if ( ! $this->_is_multisite() ) return 1;
            $current_network = $this->_get_network();
            if ( ! isset( $current_network->id ) )
                return $this->_get_main_network_id();
            return $this->_abs_int( $current_network->id );
        }//1260
        /**
         * @description Attempt an early load of translations.
         */
        protected function _tp_load_translations_early():void{
            static $loaded = false;
            if ( $loaded ) return;
            $loaded = true;
            if($this->_did_action( 'init' )) return;
            $this->_init_locale_switcher();
            $locales   = [];
            $locations = [];
            /** @noinspection LoopWhichDoesNotLoopInspection */
            while ( true ) {
                if ( defined( 'TP_LANG' ) ) {
                    if ( '' === TP_LANG ) break;
                    $locales[] = TP_LANG;
                }
                if ( isset( $this->__tp_local_package ) )
                    $locales[] = $this->__tp_local_package;
                if ( ! $locales ) break;
                if ( defined( 'TP_LANG_DIR' ) && @is_dir( TP_LANG_DIR ) )
                    $locations[] = TP_LANG_DIR;
                if ( defined( 'TP_CORE_LANG' ) && @is_dir( TP_CORE_LANG ) )
                    $locations[] = TP_CORE_LANG;
                if ( defined( 'TP_CONTENT_LANG' ) && @is_dir( TP_CONTENT_LANG ) )
                    $locations[] = TP_CONTENT_LANG;
                if ( defined( 'TP_ADMIN_LANG' ) && @is_dir( TP_ADMIN_LANG ) )
                    $locations[] = TP_ADMIN_LANG;
                if ( ! $locations ) break;
                $locations = array_unique( $locations );
                foreach ( $locales as $locale ) {
                    foreach ( $locations as $location ) {
                        if ( file_exists( $location . '/' . $locale . '.mo' ) ) {
                            $this->_load_textdomain( 'default', $location . '/' . $locale . '.mo' );
                            if ( defined( 'TP_SETUP_CONFIG' ) && file_exists( $location . '/admin-' . $locale . '.mo' ) )
                                $this->_load_textdomain( 'default', $location . '/admin-' . $locale . '.mo' );
                            break 2;
                        }
                    }
                }
                break;
            }
            $this->_tp_locale = $this->_init_locale();
        }//1289 //todo sort this out
        /**
         * @description Check or set whether TailoredPress is in "installation" mode.
         * @param null $is_installing
         * @return bool
         */
        protected function _tp_installing( $is_installing = null ):bool{
            static $installing = null;
            // Support for the `TP_INSTALLING` constant, defined before TP is loaded.
            if ( is_null( $installing ) )
                $installing = defined( 'TP_INSTALLING' ) && TP_INSTALLING;
            if ( ! is_null( $is_installing ) ) {
                $old_installing = $installing;
                $installing     = $is_installing;
                return (bool) $old_installing;
            }
            return (bool) $installing;
        }//1385
        /**
         * @description Determines if SSL is used.
         * @return bool
         */
        protected function _is_ssl():bool{
            if ( isset($_SERVER['HTTPS']) ) {
                if ( 'on' === strtolower($_SERVER['HTTPS']) ) return true;
                if ( '1' === $_SERVER['HTTPS'] ) return true;
            } elseif ( isset($_SERVER['SERVER_PORT']) && ( SERVER_PORT_SSL === $_SERVER['SERVER_PORT'] ) )
                return true;
            return false;
        }//1410
        /**
         * @description Converts a shorthand byte value to an integer byte value.
         * @param $value
         * @return mixed
         */
        protected function _tp_convert_hr_to_bytes( $value ){
            $value = strtolower( trim( $value ) );
            $bytes = (int) $value;
            if ( false !== strpos( $value, 'g' ) ) $bytes *= GB_IN_BYTES;
            elseif ( false !== strpos( $value, 'm' ) ) $bytes *= MB_IN_BYTES;
            elseif ( false !== strpos( $value, 'k' ) ) $bytes *= KB_IN_BYTES;
            return min( $bytes, PHP_INT_MAX );
        }//1437
        /**
         * @description Determines whether a PHP ini value is changeable at runtime.
         * @param $setting
         * @return bool
         */
        protected function _tp_is_ini_value_changeable( $setting ):bool{
            static $ini_all;
            if ( ! isset( $ini_all ) ) {
                $ini_all = false;
                // Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
                if ( function_exists( 'ini_get_all' ) ) $ini_all = ini_get_all();
            }
            // Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
            if ( isset( $ini_all[ $setting ]['access'] ) && ( INI_ALL === ( $ini_all[ $setting ]['access'] & 7 ) || INI_USER === ( $ini_all[ $setting ]['access'] & 7 ) ) )
                return true;
            // If we were unable to retrieve the details, fail gracefully to assume it's changeable.
            if ( ! is_array( $ini_all ) ) return true;
            return false;
        }//1463
        /**
         * @description Determines whether the current request is a TailoredPress Async request.
         * @return mixed
         */
        protected function _tp_doing_async() {
            return $this->_apply_filters( 'tp_doing_async', defined( 'DOING_ASYNC' ) && DOING_ASYNC );
        }//1494
    }
}else die;