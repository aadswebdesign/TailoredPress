<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 13:51
 */
namespace TP_Admin;
use TP_Admin\Traits\_adm_bookmark;
use TP_Admin\Traits\_adm_class_loaders;
use TP_Admin\Traits\_adm_comment;
use TP_Admin\Traits\_adm_options;
use TP_Admin\Traits\_adm_list_block;
use TP_Admin\Traits\_adm_category;
use TP_Admin\Traits\_adm_theme_install;
use TP_Admin\Traits\_adm_translation_install;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_01;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_02;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_03;
use TP_Admin\Traits\AdminImage\_adm_image_01;
use TP_Admin\Traits\AdminImage\_adm_image_02;
use TP_Admin\Traits\AdminImage\_adm_image_edit;
use TP_Admin\Traits\AdminMedia\_adm_media_01;
use TP_Admin\Traits\AdminMedia\_adm_media_02;
use TP_Admin\Traits\AdminMedia\_adm_media_03;
use TP_Admin\Traits\AdminMedia\_adm_media_04;
use TP_Admin\Traits\AdminMedia\_adm_media_05;
use TP_Admin\Traits\AdminMisc\_misc_01;
use TP_Admin\Traits\AdminMisc\_misc_02;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_01;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_02;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_03;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_hooks;
use TP_Admin\Traits\AdminNavMenu\_adm_nav_menu_01;
use TP_Admin\Traits\AdminNavMenu\_adm_nav_menu_02;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_01;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_02;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_03;
use TP_Admin\Traits\AdminTemplates\_adm_template_01;
use TP_Admin\Traits\AdminTemplates\_adm_template_02;
use TP_Admin\Traits\AdminTemplates\_adm_template_03;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Admin\Traits\AdminTemplates\_adm_template_05;
use TP_Admin\Traits\AdminFile\_adm_file_01;
use TP_Admin\Traits\AdminFile\_adm_file_02;
use TP_Admin\Traits\AdminFile\_adm_file_03;
use TP_Admin\Traits\AdminPost\_adm_post_01;
use TP_Admin\Traits\AdminPost\_adm_post_02;
use TP_Admin\Traits\AdminPost\_adm_post_03;
use TP_Admin\Traits\AdminPost\_adm_post_04;
use TP_Admin\Traits\AdminTheme\_adm_theme_01;
use TP_Admin\Traits\AdminTheme\_adm_theme_02;
use TP_Admin\Traits\AdminUpdate\_adm_update_01;
use TP_Admin\Traits\AdminUpdate\_adm_update_02;
use TP_Admin\Traits\AdminUser\_adm_user_01;
use TP_Admin\Traits\AdminUser\_adm_user_02;
use TP_Core\Cores;
use TP_Core\Libs\HTTP\TP_Http;
if(ABSPATH){
    class Admins extends Cores {
        /** @description uses from the Admin Directory */
        use _adm_comment;
        use _adm_media_01,_adm_media_02,_adm_media_03,_adm_media_04,_adm_media_05,_misc_01,_misc_02;
        use _adm_template_01,_adm_template_02,_adm_template_03,_adm_template_04,_adm_template_05;
        use _adm_post_01,_adm_post_02,_adm_post_03,_adm_post_04,_adm_list_block;
        use _adm_file_01,_adm_file_02,_adm_file_03,_adm_user_01,_adm_user_02,_adm_category,_adm_update_01,_adm_update_02;
        use _adm_theme_01,_adm_theme_02,_adm_multisite_01,_adm_multisite_02,_adm_multisite_03;
        use _adm_dashboard_01,_adm_dashboard_02,_adm_dashboard_03,_adm_bookmark,_adm_options;
        use _adm_image_01,_adm_image_02,_adm_image_edit,_adm_nav_menu_01,_adm_nav_menu_02;
        use _adm_page_menu_01,_adm_page_menu_02,_adm_page_menu_03,_adm_class_loaders,_adm_multisite_hooks;
        use _adm_translation_install,_adm_theme_install;
        public $adm_dashboard;
        public $adm_footer;
        public $adm_footer_args;
        public $adm_header;
        public $adm_header_menu;
        public $adm_title;
        public $adm_header_args;
        public $adm_timezone_format;
        public $adm_panel_title;
        private function __error_reporting():bool{
            if ( function_exists( 'error_reporting' ) ) {
                error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
            }
            return false;
        }
        protected function _admin_consts():void{
            if ( ! defined( 'TP_ADMIN' ) ) define( 'TP_ADMIN', true );
            if ( ! defined( 'TP_NETWORK_ADMIN' ) ) define( 'TP_NETWORK_ADMIN', false );
            if ( ! defined( 'TP_USER_ADMIN' ) ) define( 'TP_USER_ADMIN', false );
            if ( ! TP_NETWORK_ADMIN && ! TP_USER_ADMIN ) define( 'TP_BLOG_ADMIN', true );
            if ( isset( $_GET['import'] ) && ! defined( 'TP_LOAD_IMPORTERS' ) )
                define( 'TP_LOAD_IMPORTERS', true );
        }
        protected function _admin_construct():void{
            //$this->_tp_get_star_rating();

            
        }
        protected function _admin_string():string{
            $output  = "<section class='admin-module'>";
            ob_start();
            $this->__error_reporting();
            $output .= ob_get_clean();
            $this->_nocache_headers();
            if ( $this->_get_option( 'db_upgraded' ) ){
                $this->_flush_rewrite_rules();
                $this->_update_option( 'db_upgraded', false );
                $output .= $this->_get_action( 'after_db_upgrade' );
            }elseif(empty( $_POST ) && !$this->_tp_doing_async() && (int) $this->_get_option('db_version') !== TP_DB_VERSION){
                //if ( ! $this->_is_multisite() ) { //todo uncomment this
                    //$this->_tp_redirect( $this->_admin_url( 'upgrade.php?_tp_http_referer=' . urlencode( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
                    //exit;
                //}
                if ( $this->_apply_filters( 'do_mu_upgrade', true ) ) {
                    $_blog_count = $this->_get_blog_count();
                    if ( $_blog_count <= 50 || ( $_blog_count > 50 && random_int( 0, (int) ( $_blog_count / 50 ) ) === 1 ) ) {
                        new TP_Http();
                        $_http_version = '2.0' ?: '1.1';
                        $response = $this->_tp_remote_get($this->_admin_url( 'upgrade.php?step=1' ),['timeout' => 120,'httpversion' => $_http_version,]);
                        $output .= $this->_get_action( 'after_mu_upgrade', $response );
                        unset( $response );
                    }
                    unset( $_blog_count );
                }
            }
            $output .= "<p>TODO placeholder:Admins.php?</p>";
            $output .= "<p>NOTE :Admin.php, implemented as traits! </p>";
            $output .= "<p>What's left is the textdomain and that is on the TODO list!</p>";
            $output .= "<p>NOTE:Adm_Translation.php, implemented as a trait!</p>";
            $output .= "</section>";
            return $output;
        }
    }
}else{die;}