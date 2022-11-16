<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Admin\Libs\Adm_Screen;
use TP_Core\Traits\Methods\Components\auth_check_html_view;
if(ABSPATH){
    trait _methods_16{
        /**
         * @description Retrieve IDs that are not already present in the cache.
         * @param $object_ids
         * @param $cache_key
         * @return array
         */
        protected function _get_non_cached_ids( $object_ids, $cache_key ):array{
            $non_cached_ids = [];
            $cache_values   = $this->_tp_cache_get_multiple( $object_ids, $cache_key );
            foreach ( $cache_values as $id => $value ) {
                if ( ! $value ) $non_cached_ids[] = (int) $id;
            }
            return $non_cached_ids;
        }//6939
        /**
         * @description Test if the current device has the capability to upload files.
         * @return bool
         */
        protected function _device_can_upload():bool{
            if ( ! $this->_tp_is_mobile() ) return true;
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if ( strpos( $user_agent, 'iPhone' ) !== false|| strpos( $user_agent, 'iPad' ) !== false || strpos( $user_agent, 'iPod' ) !== false )
                return preg_match( '#OS ([\d_]+) like Mac OS X#', $user_agent, $version ) && version_compare( $version[1], '6', '>=' );
            return true;
        }//6960
        /**
         * @description Test if a given path is a stream URL
         * @param $path
         * @return bool
         */
        protected function _tp_is_stream( $path ):bool{
            $scheme_separator = strpos( $path, '://' );
            if ( false === $scheme_separator ) return false;
            $stream = substr( $path, 0, $scheme_separator );
            return in_array( $stream, stream_get_wrappers(), true );
        }//6984
        /**
         * @description Test if the supplied date is valid for the Gregorian calendar.
         * @param $month
         * @param $day
         * @param $year
         * @param $source_date
         * @return mixed
         */
        protected function _tp_check_date( $month, $day, $year, $source_date ){
            return $this->_apply_filters( 'tp_check_date', checkdate( $month, $day, $year ), $source_date );
        }//7010
        /**
         * @description Load the auth check for monitoring whether the user is still logged in.
         */
        protected function _tp_auth_check_load():void{
            if ( ! $this->_is_admin() && ! $this->_is_user_logged_in() ) return;
            if ( defined( 'IFRAME_REQUEST' ) ) return;
            $_screen = $this->_get_current_screen();
            $screen = null;
            if($_screen instanceof Adm_Screen ){
                $screen = $_screen;
            }
            $hidden = array( 'update', 'update-network', 'update-core', 'update-core-network', 'upgrade', 'upgrade-network', 'network' );
            $show   = ! in_array( $screen->id, $hidden, true );
            if ( $this->_apply_filters( 'tp_auth_check_load', $show, $screen ) ) {
                $this->tp_enqueue_style( 'tp-auth-check' );
                $this->tp_enqueue_script( 'tp-auth-check' );
                $this->_add_action( 'admin_print_footer_scripts', [$this,'tp_auth_check_html'], 5 );
                $this->_add_action( 'tp_print_footer_scripts', [$this,'tp_auth_check_html'], 5 );
            }
        }//7033
        /**
         * @description Output the HTML that shows the wp-login dialog when the user is no longer logged in.
         * @return auth_check_html_view
         */
        protected function _get_auth_check_html():auth_check_html_view{
            $login_url      = $this->_tp_login_url();
            $current_domain = ( $this->_is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
            $same_domain    = ( strpos( $login_url, $current_domain ) === 0 );
            $args['same_domain'] = $this->_apply_filters( 'tp_auth_check_same_domain', $same_domain );
            $args['wrap_class'] = $same_domain ? 'hidden' : 'hidden fallback';
            $args['login_url'] =$login_url;
            return new auth_check_html_view($args);
        }//7071
        public function tp_auth_check_html():void{
            echo $this->_get_auth_check_html();
        }
        /**
         * @description Check whether a user is still logged in, for the heartbeat.
         * @param $response
         * @return mixed
         */
        protected function _tp_auth_check( $response ){
            $response['tp-auth-check'] = $this->_is_user_logged_in() && empty( $this->__login_grace_period );
            return $response;
        }//7123
        /**
         * @description Return RegEx body to liberally match an opening HTML tag.
         * @param $tag
         * @return string
         */
        protected function _get_tag_regex( $tag ):string{
            if ( empty( $tag ) ) return '';
            return sprintf( '<%1$s[^<]*(?:>[\s\S]*<\/%1$s>|\s*\/>)', $this->_tag_escape( $tag ) );
        }//7144
        /**
         * @description Retrieve a canonical form of the provided charset appropriate for passing to PHP
         * @param $charset
         * @return string
         */
        protected function _canonical_charset( $charset ):string{
            if ( 'utf-8' === strtolower( $charset ) || 'utf8' === strtolower( $charset ) )
                return 'UTF-8';
            if ( 'iso-8859-1' === strtolower( $charset ) || 'iso8859-1' === strtolower( $charset ) )
                return 'ISO-8859-1';
            return $charset;
        }//7170
        /**
         * @description  Set the mb string internal encoding to a binary safe encoding when func_overload
         * @description  . is enabled.
         * @param bool $reset
         */
        protected function _mb_string_binary_safe_encoding( $reset = false ):void{
            static $encodings  = array();
            static $overloaded = null;
            if ( is_null( $overloaded ) ) {
                if ( function_exists( 'mb_internal_encoding' )
                    && ( (int) ini_get( 'mbstring.func_overload' ) & 2 )
                ) $overloaded = true;
                else $overloaded = false;
            }
            if ( false === $overloaded ) return;
            if ( ! $reset ) {
                $encoding = mb_internal_encoding();
                $encodings[] = $encoding;
                mb_internal_encoding( 'ISO-8859-1' );
            }
            if ( $reset && $encodings ) {
                $encoding = array_pop( $encodings );
                mb_internal_encoding( $encoding );
            }
        }//7207
    }
}else die;