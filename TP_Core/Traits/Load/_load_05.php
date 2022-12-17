<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Load;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Inits\_init_pages;
if(ABSPATH){
    trait _load_05 {
        use _init_locale, _init_db, _init_error, _init_pages;
        /**
         * @description Determines whether the current request should use themes.
         * @return mixed
         */
        protected function _tp_using_themes(){
            return $this->_apply_filters( 'tp_using_themes', defined( 'TP_USE_THEMES' ) && TP_USE_THEMES );
        }//1512
        /**
         * @description Determines whether the current request is a TailoredPress cron request.
         * @return mixed
         */
        protected function _tp_doing_cron(){
            return $this->_apply_filters( 'tp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
        }//1530
        /**
         * @description Determines whether file modifications are allowed.
         * @param $context
         * @return mixed
         */
        protected function _tp_is_file_mod_allowed( $context ){
            return $this->_apply_filters( 'file_mod_allowed', ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS, $context );
        }//1576
        /**
         * @description Start scraping edited file errors.
         */
        protected function _tp_start_scraping_edited_file_errors():void{
            if ( ! isset( $_REQUEST['tp_scrape_key'], $_REQUEST['tp_scrape_nonce'] ) )
                return;
            $key   = substr( $this->_sanitize_key( $this->_tp_unslash( $_REQUEST['tp_scrape_key'] ) ), 0, 32 );
            $nonce = $this->_tp_unslash( $_REQUEST['tp_scrape_nonce'] );
            if ( $this->_get_transient( 'scrape_key_' . $key ) !== $nonce ) {
                echo "###### tp_scraping_result_start:$key ######";
                echo $this->_tp_json_encode(
                    ['code' => 'scrape_nonce_failure','message' => $this->__( 'Scrape key check failed. Please try again.' ),]
                );
                echo "###### tp_scraping_result_end:$key ######";
                die();
            }
            if ( ! defined( 'TP_SANDBOX_SCRAPING' ) ) define( 'TP_SANDBOX_SCRAPING', true );
            register_shutdown_function( 'tp_finalize_scraping_edited_file_errors', $key );
        }//1593
        /**
         * @description Finalize scraping for edited file errors.
         * @param $scrape_key
         */
        protected function _tp_finalize_scraping_edited_file_errors( $scrape_key ):void{
            $error = error_get_last();
            echo "\n###### wp_scraping_result_start:$scrape_key ######\n";
            if ( ! empty( $error ) && in_array( $error['type'], array( E_CORE_ERROR, E_COMPILE_ERROR, E_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
                $error = str_replace( ABSPATH, '', $error );
                echo $this->_tp_json_encode( $error );
            } else echo $this->_tp_json_encode( true );
            echo "\n###### tp_scraping_result_end:$scrape_key ######\n";
        }//1624
        /**
         * @description Checks whether current request is a JSON request, or is expecting a JSON response.
         * @return bool
         */
        protected function _tp_is_json_request():bool{
            if ( isset( $_SERVER['HTTP_ACCEPT'] ) && $this->_tp_is_json_media_type( $_SERVER['HTTP_ACCEPT'] ) )
                return true;
            if ( isset( $_SERVER['CONTENT_TYPE'] ) && $this->_tp_is_json_media_type( $_SERVER['CONTENT_TYPE'] ) )
                return true;
            return false;
        }//1644
        /**
         * @description Checks whether current request is a JSONP request, or is expecting a JSONP response.
         * @return bool
         */
        protected function _tp_is_jsonp_request():bool{
            if ( ! isset( $_GET['_jsonp'] ) ) return false;
            $jsonp_callback = $_GET['_jsonp'];
            if ( ! $this->_tp_jsonp_check_callback( $jsonp_callback ) ) return false;
            return $this->_apply_filters( 'rest_jsonp_enabled', true );
        }//1665
        /**
         * @description Checks whether a string is a valid JSON Media Type.
         * @param $media_type
         * @return mixed
         */
        protected function _tp_is_json_media_type( $media_type ){
            static $cache = [];
            if ( ! isset( $cache[ $media_type ] ) )
                $cache[ $media_type ] = (bool) preg_match( '/(^|\s|,)application\/([\w!#\$&-\^\.\+]+\+)?json(\+oembed)?($|\s|;|,)/i', $media_type );
            return $cache[ $media_type ];
        }//1694
        /**
         * @description Checks whether current request is an XML request, or is expecting an XML response.
         * @return bool
         */
        protected function _tp_is_xml_request():bool{
            $accepted = ['text/xml','application/rss+xml','application/atom+xml',
                'application/rdf+xml','text/xml+oembed','application/xml+oembed',];
            if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {
                foreach ( $accepted as $type ) {
                    if ( false !== strpos( $_SERVER['HTTP_ACCEPT'], $type ) ) return true;
                }
            }
            if ( isset( $_SERVER['CONTENT_TYPE'] ) && in_array( $_SERVER['CONTENT_TYPE'], $accepted, true ) )
                return true;
            return false;
        }//1712
        /**
         * @description Checks if this site is protected by HTTP Basic Auth.
         * @param string $context
         * @return mixed
         */
        protected function _tp_is_site_protected_by_basic_auth( $context = '' ){
            if ( ! $context ) {
                if ( 'tp_login.php' === $this->tp_pagenow ) $context = 'login';//todo
                elseif ( $this->_is_admin() ) $context = 'admin';
                else $context = 'front';
            }
            $is_protected = ! empty( $_SERVER['PHP_AUTH_USER'] ) || ! empty( $_SERVER['PHP_AUTH_PW'] );
            return $this->_apply_filters( 'tp_is_site_protected_by_basic_auth', $is_protected, $context );
        }//1755 //todo

    }
}else die;