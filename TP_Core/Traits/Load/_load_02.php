<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Load;
use TP_Core\Libs\TP_Hook;
use TP_Core\Libs\TP_Paused_Extensions_Storage;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _load_02 {
        use _init_cache;
        use _init_db;
        //use _construct_time;
        /**
         * @description Retrieve or display the time from the page start to when function is called.
         * @param int $precision
         * @return string
         */
        protected function _get_timer_stop($precision = 3 ):string{
            $this->tp_time_end   = microtime( true );
            $time_total = $this->tp_time_end - $this->tp_time_start;
            $r= ( function_exists( [$this,'__number_format_i18n'] ) ) ? $this->_number_format_i18n( $time_total, $precision ) : number_format( $time_total, $precision );
            return $r;
        }//381 added
        protected function _timer_stop( $precision = 3 ):void{
            echo $this->_get_timer_stop($precision);
        }//381
        /**
         * @description Set PHP error reporting based on TailoredPress debug settings.
         */
        protected function _tp_debug_mode():void{
            if ( ! $this->_apply_filters( 'enable_tp_debug_mode_checks', true ) ) return;
            if ( TP_DEBUG ) {
                error_reporting( E_ALL );
                if ( TP_DEBUG_DISPLAY ) ini_set( 'display_errors', 1 );
                elseif ( null !== TP_DEBUG_DISPLAY ) ini_set( 'display_errors', 0 );
                if ( in_array( strtolower( (string) TP_DEBUG_LOG ), array( 'true', '1' ), true ) ) $log_path = TP_CONTENT_DIR . '/Assets/logs/debug.log';
                elseif ( is_string( TP_DEBUG_LOG ) ) $log_path = TP_DEBUG_LOG;
                else $log_path = false;
                if ( $log_path ) {
                    ini_set( 'log_errors', 1 );
                    ini_set( 'error_log', $log_path );
                }
            }else error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
            if(defined('XMLRPC_REQUEST')||defined('REST_REQUEST')|| defined('MS_FILES_REQUEST')||( defined( 'TP_INSTALLING' ) && TP_INSTALLING ) || $this->_tp_doing_async() || $this->_tp_is_json_request())
                ini_set( 'display_errors', 0 );
        }//424
        /**
         * @description Set the location of the language directory.
         */
        protected function _tp_set_lang_dir():void{
            if ( ! defined( 'TP_LANG_DIR' ) ) {
                if (! @is_dir( TP_CORE_LANG ) || (file_exists( TP_CONTENT_LANG ) && @is_dir( TP_CONTENT_LANG )))
                    define( 'TP_LANG_DIR', TP_CONTENT_LANG );
                else define( 'TP_LANG_DIR', TP_CORE_LANG );
            }
        }//505
        /**
         * @description Load the database class file and instantiate the `$tpdb` global.
         */
        protected function _require_tp_db():void{
            $this->_init_db();
        }//544 todo
        /**
         * @description Set the database table prefix and the format specifiers for database
         */
        protected function _tp_set_tpdb_vars():void{
            $this->tpdb = $this->_init_db();
            if ( ! empty( $this->tpdb->error ) ) $this->_dead_db();
            $this->tpdb->field_types = ['post_author' => '%d','post_parent' => '%d','menu_order' => '%d','term_id' => '%d',
                'term_group' => '%d','term_taxonomy_id' => '%d','parent' => '%d','count' => '%d','object_id' => '%d',
                'term_order' => '%d','ID' => '%d','comment_ID' => '%d','comment_post_ID' => '%d','comment_parent' => '%d',
                'user_id' => '%d','link_id' => '%d','link_owner' => '%d','link_rating' => '%d','option_id' => '%d',
                'blog_id' => '%d','meta_id' => '%d','post_id' => '%d','user_status' => '%d','umeta_id' => '%d',
                'comment_karma' => '%d','comment_count' => '%d',
                // Multisite:
                'active' => '%d','cat_id' => '%d','deleted' => '%d','lang_id' => '%d',
                'mature' => '%d','public' => '%d','site_id' => '%d','spam' => '%d',];

            $prefix = $this->tpdb->set_prefix( $this->tp_table_prefix );
            if ( $this->_init_error( $prefix ) ) {
                $this->_tp_load_translations_early();
                $this->_tp_die(
                    sprintf($this->__("<strong>Error</strong>:".'%1$s in %2$s can only contain numbers, letters, and underscores.'),
                        "<code>$this->tp_table_prefix</code>","<code>TP_NS_CONFIG</code>")
                );
            }
        }//576
        /**
         * @note as this package don't use globals, it might not be needed
         * @description  Toggle `__tp_using_ext_object_cache` on and off without directly* touching global.
         * @param null $using
         * @return mixed
         */
        protected function _tp_using_ext_object_cache( $using = null ){
            $current_using = $this->tp_using_ext_object_cache;
            if ( null !== $using )
                $this->tp_using_ext_object_cache = $using;
            return $current_using;
        }//646
        protected function _external_cache_class($path = null,$class = null){
            return $this->_tp_load_class('external_cache_classes',TP_NS_THEMES.$path,$class);
        }//added
        /**
         * @description Start the TailoredPress object cache.
         */
        protected function _tp_start_object_cache():void{
            static $first_init = true;
            if ( $first_init && $this->_apply_filters( 'enable_loading_object_cache_dropin', true )){
                if ( ! function_exists( [$this, '_tp_cache_init'] ) ) {
                    if(!empty($this->_external_cache_class())){
                        if(function_exists( [$this, '_tp_cache_init'])) $this->_tp_using_ext_object_cache( true );
                        if ( $this->tp_filter ) $this->tp_filter = TP_Hook::build_pre_initialized_hooks( $this->tp_filter );
                    }
                } elseif ( ! $this->_tp_using_ext_object_cache() && !empty($this->_external_cache_class()))
                    $this->_tp_using_ext_object_cache(true);
            }
            if ( ! $first_init && function_exists( [$this,'_tp_cache_switch_to_blog'] ))
                $this->_tp_cache_switch_to_blog( $this->_get_current_blog_id() );
            elseif( function_exists([$this,'_tp_cache_init'])) $this->_tp_cache_init();
            if(function_exists( [$this,'_tp_cache_add_global_groups'])){
                $this->_tp_cache_add_global_groups(['users', 'userlogins', 'usermeta', 'user_meta', 'useremail', 'userslugs', 'site-transient', 'site-options', 'blog-lookup', 'blog-details', 'site-details', 'rss', 'global-posts', 'blog-id-cache', 'networks', 'sites', 'blog_meta']);
                $this->_tp_cache_add_non_persistent_groups(['counts']); //todo
            }
            $first_init = false;
        }//666 //todo !!
        /**
         * @description Redirect to the installer if TailoredPress is not installed.
         */
        protected function _tp_not_installed():void{
            if ( $this->_is_multisite() ) {
                if ( ! $this->_is_blog_installed() && ! $this->_tp_installing() ) {
                    $this->_nocache_headers();
                    $this->_tp_die( $this->__( 'The site you have requested is not installed properly. Please contact the system administrator.' ) );
                }
            } elseif ( ! $this->_is_blog_installed() && ! $this->_tp_installing() ) {
                $this->_nocache_headers();
                //new load_kses(); //todo
                //new load_pluggables(); //todo
                $link = $this->_tp_guess_url() . '/TP_Admin/install.php';//todo
                $this->_tp_redirect( $link );
                die();
            }
        }//748 todo
        /**
         * @description Retrieves an array of active and valid themes.
         * @return array
         */
        protected function _tp_get_active_and_valid_themes():array{
            $themes = [];
            if ('tp-activate.php' !== $this->tp_pagenow &&  $this->_tp_installing())
                return $themes;
            if ( TP_TEMPLATE_PATH !== TP_STYLESHEET_PATH )
                $themes[] = TP_STYLESHEET_PATH;
            $themes[] = TP_TEMPLATE_PATH;
            if ( $this->_tp_is_recovery_mode() ) {
                $themes = $this->_tp_skip_paused_themes( $themes );
                if(empty($themes)) $this->_add_filter( 'tp_using_themes', '__return_false' );
            }
            return $themes;
        }//891 //todo lots to sort out
        /**
         * @description Filters a given list of themes, removing any paused themes from it.
         * @param array $themes
         * @return array
         */
        protected function _tp_skip_paused_themes( array $themes ):array{
            $_paused_themes = $this->_tp_paused_themes();
            $paused_themes = null;
            if( $_paused_themes instanceof TP_Paused_Extensions_Storage ){
                $paused_themes = $_paused_themes;
            }
            $paused_themes = $paused_themes->get_all();
            if ( empty( $paused_themes ) ) return $themes;
            foreach ( $themes as $index => $theme ) {
                $theme = basename( $theme );
                if ( array_key_exists( $theme, $paused_themes ) ) {
                    unset( $themes[ $index ] );
                    // Store list of paused themes for displaying an admin notice.
                    $this->tp_paused_themes[ $theme ] = $paused_themes[ $theme ];
                }
            }
            return $themes;
        }//930
    }
}else die;