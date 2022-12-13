<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-12-2022
 * Time: 13:40
 */
namespace TP_Admin;
use TP_Libs\BaseSettings;
use TP_Admin\Traits\_adm_bookmark;
use TP_Admin\Traits\_adm_class_loaders;
use TP_Admin\Traits\_adm_comment;
use TP_Admin\Traits\_adm_options;
use TP_Admin\Traits\_adm_filters;
use TP_Admin\Traits\_adm_list_block;
use TP_Admin\Traits\_adm_category;
use TP_Admin\Traits\_adm_theme_install;
use TP_Admin\Traits\_adm_translation_install;
use TP_Admin\Traits\AdminConstructs\_adm_construct_media;
use TP_Admin\Traits\AdminConstructs\_adm_construct_dashboard;
use TP_Admin\Traits\AdminConstructs\_adm_construct_admins;
//use TP_Admin\Traits\AdminConstructs\_adm_construct_screen;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_01;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_02;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_03;
use TP_Admin\Traits\AdminFile\_adm_file_01;
use TP_Admin\Traits\AdminFile\_adm_file_02;
use TP_Admin\Traits\AdminFile\_adm_file_03;
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
use TP_Admin\Traits\AdminPost\_adm_post_01;
use TP_Admin\Traits\AdminPost\_adm_post_02;
use TP_Admin\Traits\AdminPost\_adm_post_03;
use TP_Admin\Traits\AdminPost\_adm_post_04;
use TP_Admin\Traits\AdminTemplates\_adm_template_01;
use TP_Admin\Traits\AdminTemplates\_adm_template_02;
use TP_Admin\Traits\AdminTemplates\_adm_template_03;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Admin\Traits\AdminTemplates\_adm_template_05;
use TP_Admin\Traits\AdminTheme\_adm_theme_01;
use TP_Admin\Traits\AdminTheme\_adm_theme_02;
use TP_Admin\Traits\AdminUpdate\_adm_update_01;
use TP_Admin\Traits\AdminUpdate\_adm_update_02;
use TP_Admin\Traits\AdminUser\_adm_user_01;
use TP_Admin\Traits\AdminUser\_adm_user_02;

if(ABSPATH){
    class AdminSettings extends BaseSettings {
        use _adm_bookmark,_adm_class_loaders,_adm_comment,_adm_construct_admins;
        use _adm_construct_dashboard, _adm_construct_media;//todo , _adm_construct_screen
        use _adm_dashboard_01,_adm_dashboard_02,_adm_dashboard_03,_adm_file_01,_adm_file_02;
        use _adm_file_03,_adm_filters,_adm_image_01,_adm_image_02,_adm_image_edit;
        use _adm_list_block,_adm_category,_adm_theme_install,_adm_translation_install;
        use _adm_media_01,_adm_media_02,_adm_media_03,_adm_media_04,_adm_media_05,_misc_01,_misc_02;
        use _adm_multisite_01,_adm_multisite_02,_adm_multisite_03,_adm_multisite_hooks;
        use _adm_nav_menu_01,_adm_nav_menu_02,_adm_options,_adm_page_menu_01,_adm_page_menu_02;
        use _adm_page_menu_03,_adm_post_01,_adm_post_02,_adm_post_03,_adm_post_04,_adm_template_01;
        use _adm_template_02,_adm_template_03,_adm_template_04,_adm_template_05,_adm_theme_01;
        use _adm_theme_02,_adm_user_01,_adm_user_02,_adm_update_01,_adm_update_02;
        public $adm_dashboard;
        public $adm_footer;
        public $adm_footer_args;
        public $adm_header;
        public $adm_header_menu;
        public $adm_title;
        public $adm_header_args;
        public $adm_timezone_format;
        public $adm_panel_title;


        public function __construct(){
            parent::__construct();
            $this->_admin_constants();



        }

        protected function _admin_constants():void{
            if ( ! defined( 'TP_ADMIN' ) ) define( 'TP_ADMIN', true );
            if ( ! defined( 'TP_NETWORK_ADMIN' ) ) define( 'TP_NETWORK_ADMIN', false );
            if ( ! defined( 'TP_USER_ADMIN' ) ) define( 'TP_USER_ADMIN', false );
            if ( ! TP_NETWORK_ADMIN && ! TP_USER_ADMIN ) define( 'TP_BLOG_ADMIN', true );
            if ( isset( $_GET['import'] ) && ! defined( 'TP_LOAD_IMPORTERS' ) )
                define( 'TP_LOAD_IMPORTERS', true );
            //dirs
            if(!defined('TP_ADMIN_DIR')) define('TP_ADMIN_DIR', ABSPATH .'/TP_Admin');
            if(!defined('TP_ADMIN_ASSETS')) define('TP_ADMIN_ASSETS', TP_ADMIN_DIR .'/Assets');
            if(!defined('TP_ADMIN_LANG')) define('TP_ADMIN_LANG', TP_ADMIN_ASSETS. '/Languages');
            //namespaces
            if(!defined('TP_NS_ADMIN')) define('TP_NS_ADMIN', 'TP_Admin\\');
            if(!defined('TP_NS_ADMIN_LIBS')) define('TP_NS_ADMIN_LIBS', TP_NS_ADMIN .'Libs\\');
            if(!defined('TP_NS_ADMIN_FILESYSTEM')) define('TP_NS_ADMIN_FILESYSTEM',TP_NS_ADMIN_LIBS . 'AdmFilesystem\\' );
            if(!defined('TP_NS_ADMIN_COMPONENTS')) define('TP_NS_ADMIN_COMPONENTS', TP_NS_ADMIN_LIBS .'AdmComponents\\');
            if(!defined('TP_NS_ADMIN_MODULES')) define('TP_NS_ADMIN_MODULES', TP_NS_ADMIN_LIBS .'AdmModules\\');
            if(!defined('TP_NS_ADMIN_MENU_PAGES')) define('TP_NS_ADMIN_MENU_PAGES', TP_NS_ADMIN_LIBS .'AdmMenusPages\\');
            if(!defined('TP_NS_ADMIN_TB_LIST')) define('TP_NS_ADMIN_TB_LIST', TP_NS_ADMIN_LIBS .'Lists\\');
            //AdmMenus
        }




    }
}else{die;}
