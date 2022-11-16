<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_13{
        /**
         * @description Check if IIS 7+ supports pretty permalinks.
         * @return mixed
         */
        protected function _iis7_supports_permalinks(){
            $supports_permalinks = false;
            if ( $this->tp_is_iis7 ) $supports_permalinks = class_exists( 'DOMDocument', false ) && isset( $_SERVER['IIS_UrlRewriteModule'] ) && ( 'cgi-fcgi' === PHP_SAPI );
            return $this->_apply_filters( 'iis7_supports_permalinks', $supports_permalinks );
        }//5914
        /**
         * @description Whether to force SSL used for the Administration Screens.
         * @param null $force
         * @return bool
         */
        protected function _force_ssl_admin( $force = null ):bool{
            static $forced = false;
            if ( ! is_null( $force ) ) {
                $old_forced = $forced;
                $forced     = $force;
                return $old_forced;
            }
            return $forced;
        }//5941
        /**
         * @description Guess the URL for the site.//todo
         * @return string
         */
        protected function _tp_guess_url():string{
            if ( defined( 'TP_SITEURL' ) && '' !== TP_SITEURL )
                $url = TP_SITEURL;
            else {
                $abspath_fix         = str_replace( '\\', '/', ABSPATH );
                $script_filename_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
                // The request is for the admin.
                if ( strpos( $_SERVER['REQUEST_URI'], 'TP_Admin' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'TP_Login.php' ) !== false ) {
                    $path = preg_replace( '#/(TP_Core/.*|TP_Login.php)#i', '', $_SERVER['REQUEST_URI'] );//todo
                    // The request is for a file in ABSPATH.
                } elseif ( $script_filename_dir . '/' === $abspath_fix ) {
                    // Strip off any file/query params in the path.
                    $path = preg_replace( '#/[^/]*$#', '', $_SERVER['PHP_SELF'] );//i removed
               } else if ( false !== strpos( $_SERVER['SCRIPT_FILENAME'], $abspath_fix ) ) {
                   // Request is hitting a file inside ABSPATH.
                   $directory = str_replace( ABSPATH, '', $script_filename_dir );
                   // Strip off the subdirectory, and any file/query params.
                   $path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '', $_SERVER['REQUEST_URI'] );
               } elseif ( false !== strpos( $abspath_fix, $script_filename_dir ) ) {
                   // Request is hitting a file above ABSPATH.
                   $subdirectory = substr( $abspath_fix, strpos( $abspath_fix, $script_filename_dir ) + strlen( $script_filename_dir ) );
                   // Strip off any file/query params from the path, appending the subdirectory to the installation.
                   $path = preg_replace( '#/[^/]*$#', '', $_SERVER['REQUEST_URI'] ) . $subdirectory;//i removed
               } else {
                   $path = $_SERVER['REQUEST_URI'];
               }
                $schema = $this->_is_ssl() ? 'https://' : 'http://'; // set_url_scheme() is not defined yet.
                $url    = $schema . $_SERVER['HTTP_HOST'] . $path;
            }
            return rtrim( $url, '/' );
        }//5963
        /**
         * @description Temporarily suspend cache additions.
         * @param null $suspend
         * @return bool
         */
        protected function _tp_suspend_cache_addition( $suspend = null ):bool{
            static $_suspend = false;
            if ( is_bool( $suspend ) ) $_suspend = $suspend;
            return $_suspend;
        }//6017
        /**
         * @description Suspend cache invalidation.
         * @param bool $suspend
         * @return mixed
         */
        protected function _tp_suspend_cache_invalidation( $suspend = true ){
            $this->tp_suspend_cache_invalidation;
            $current_suspend = $this->tp_suspend_cache_invalidation;
            $this->tp_suspend_cache_invalidation = $suspend;
            return $current_suspend;
        }//6041
        /**
         * @description Determine whether a site is the main site of the current network.
         * @param null $site_id
         * @param null $network_id
         * @return bool
         */
        protected function _is_main_site( $site_id = null, $network_id = null ):bool{
            if ( ! $this->_is_multisite() ) return true;
            if ( ! $site_id ) $site_id = $this->_get_current_blog_id();
            $site_id = (int) $site_id;
            return $this->_get_main_site_id( $network_id ) === $site_id;
        }//6061
        /**
         * @description Gets the main site ID.
         * @param null $network_id
         * @return int
         */
        protected function _get_main_site_id( $network_id = null ):int{
            if ( ! $this->_is_multisite() ) return $this->_get_current_blog_id();
            $network = $this->_get_network( $network_id );
            if ( ! $network ) return 0;
            return $network->site_id;
        }//6084
        /**
         * @description Determine whether a network is the main network of the Multisite installation.
         * @param null $network_id
         * @return bool
         */
        protected function _is_main_network( $network_id = null ):bool{
            if ( ! $this->_is_multisite() ) return true;
            if ( null === $network_id ) $network_id = $this->_get_current_network_id();
            $network_id = (int) $network_id;
            return ( $this->_get_main_network_id() === $network_id );
        }//6105
        /**
         * @description Get the main network ID.
         * @return int
         */
        protected function _get_main_network_id():int{
            if ( ! $this->_is_multisite() ) return 1;
            $current_network = $this->_get_network();
            if ( defined( 'PRIMARY_NETWORK_ID' ) ) $main_network_id = PRIMARY_NETWORK_ID;
            elseif ( isset( $current_network->id ) && 1 === (int) $current_network->id )
                $main_network_id = 1;
            else {
                $_networks = $this->_get_networks(['fields' => 'ids','number' => 1,]);
                $main_network_id = array_shift( $_networks );
            }
            return (int) $this->_apply_filters( 'get_main_network_id', $main_network_id );
        }//6126
        /**
         * @description Determine whether global terms are enabled
         * @return bool
         */
        protected function _global_terms_enabled():bool{
            if ( ! $this->_is_multisite() ) return false;
            static $global_terms = null;
            if ( is_null( $global_terms ) ) {
                $filter = $this->_apply_filters( 'global_terms_enabled', null );
                if ( ! is_null( $filter ) )  $global_terms = (bool) $filter;
                else $global_terms = (bool) $this->_get_site_option( 'global_terms_enabled', false );
            }
            return $global_terms;
        }//6158
    }
}else die;