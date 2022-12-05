<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-12-2022
 * Time: 05:50
 */
namespace TP_Admin\Libs\AdmComponents;
use TP_Admin\Traits\_adm_class_loaders;
use TP_Admin\Traits\_adm_screen;
use TP_Admin\Traits\AdminConstructs\_adm_construct_screen;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_01;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_02;
use TP_Admin\Traits\AdminDashboard\_adm_dashboard_03;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_02;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_03;
use TP_Admin\Traits\AdminRewrite\_adm_rewrite_02;
use TP_Admin\Traits\AdminTemplates\_adm_template_03;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\AdminBar\_admin_bar_03;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Constructs\_construct_core;
use TP_Core\Traits\Constructs\_construct_locale;
use TP_Core\Traits\Constructs\_construct_menu;
use TP_Core\Traits\Constructs\_construct_page;
use TP_Core\Traits\Constructs\_construct_utils;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\K_Ses\_k_ses_02;
use TP_Core\Traits\K_Ses\_k_ses_03;
use TP_Core\Traits\K_Ses\_k_ses_04;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Misc\_error_protection;
use TP_Core\Traits\Misc\_global_settings;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Misc\tp_link_styles;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Multisite\_ms_network;
use TP_Core\Traits\Multisite\Methods\_ms_methods_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_general_template_09;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Theme\_theme_09;
use TP_Core\Libs\HTTP\TP_Http;
use TP_Core\Traits\User\_user_03;
use TP_Core\Traits\User\_user_05;
if(ABSPATH){
    class ComponentsBase{
        use _action_01, _adm_class_loaders, _adm_construct_screen, _adm_dashboard_01,_adm_dashboard_02;
        use _adm_dashboard_03, _adm_page_menu_02,_adm_page_menu_03, _adm_rewrite_02, _adm_screen, _adm_template_03;
        use _admin_bar_03, _capability_01, _construct_core, _construct_locale,_construct_menu,_construct_page;
        use _construct_utils, _error_protection, _filter_01, _formats_01,_formats_02,_formats_03 ,_formats_07;
        use _formats_08,_formats_10, _general_template_02,_general_template_08, _general_template_09,_global_settings;
        use _http_01, _I10n_01,_I10n_02,_I10n_03,_I10n_04,_I10n_05, _k_ses_02, _k_ses_03, _k_ses_04, _link_template_09;
        use _load_03,_load_04,_load_05, _methods_03,_methods_04, _methods_15, _ms_methods_01, _ms_network;
        use _option_01,_option_02, _pluggable_01, _post_03, _post_template_01, _query_04, _rewrite, _theme_09;
        use tp_link_styles,tp_script, _user_03, _user_05;
        protected $_args;
        public $adm_footer_args, $adm_header_args, $adm_header_menu, $adm_panel_title, $adm_title;
        protected function _error_reporting():void{ //todo lookup load
            error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
        }
        public function __construct($args = null){
            $this->_args = $args;
        }
        protected function _admin_string():string{
            $output  = "<section class='admin-module'>";
            ob_start();
            $this->_error_reporting();
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