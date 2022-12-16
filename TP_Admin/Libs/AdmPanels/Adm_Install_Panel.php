<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-12-2022
 * Time: 12:02
 */
namespace TP_Admin\Libs\AdmPanels;
use TP_Admin\Traits\AdminUpgrade\_adm_upgrade_01;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_09;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\K_Ses\_k_ses_02;
use TP_Core\Traits\K_Ses\_k_ses_03;
use TP_Core\Traits\K_Ses\_k_ses_04;
use TP_Core\Traits\Load\_load_01;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Methods\_methods_18;
use TP_Core\Traits\Methods\_methods_19;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_general_template_09;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_02;
use TP_Libs\Constants;
use TP_Admin\Libs\AdmForms\TP_Install_Form;
use TP_Core\Libs\DB\TP_Db;

if(ABSPATH){
    class Adm_Install_Panel{
		use _action_01,_adm_upgrade_01,_cache_01, Constants,_formats_02, _formats_03, _formats_04, _formats_06;
        use _formats_07, _formats_08, _formats_09, _formats_10, _formats_11,_general_template_02, _general_template_08;
		use _general_template_09, _I10n_01,_I10n_02,_I10n_03,_I10n_04,_I10n_05,_http_01,_http_02,_init_db,_k_ses_02;
        use _k_ses_03,_k_ses_04, _link_template_09, _link_template_10, _load_01, _load_03,_load_04,_methods_03, _methods_04;
        use _methods_05, _methods_08, _methods_10,_methods_13,_methods_15, _methods_18,_methods_19, _option_01, _theme_01,_theme_02;
        //temporary
        public $tp_current_env;//todo
        //----
        private $__admin_email, $__admin_password, $__admin_password_check, $__blog_public;
        private $__public,$__result, $__user_name, $__user_table, $__weblog_title;
        protected $_args, $_install_args, $_get_form, $_form_args = [], $_body_classes,$_html, $_compat;
        protected $_php_compat, $_mysql_compat, $_table_prefix, $_not_upgrade, $_step;
        public $tpdb;
        /**
         * @param array $args
         */
        public function __construct($args = []){
            $this->_db_constants();
            $this->_http_constants();
            $this->_initial_constants();
            $this->_tp_content_constants();
            $this->_tp_core_constants();
            $this->_version_constants();
            $this->_args = $args;
            $this->_form_args['error'] = $args['errors'] ?? '';
            $this->_get_form = new TP_Install_Form($this->_form_args);
            $this->tpdb = $this->_init_db();
			$this->__install_construct();
        }
        private function __install_construct():void{
            define( 'TP_INSTALLING', true );
            $this->_nocache_headers();
            $this->_step =  isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;
            $mysql_version = $this->tpdb->db_version();
            $this->_php_compat = version_compare(PHP_VERSION,TP_REQUIRED_PHP_VERSION, '>=');
            $this->_mysql_compat = version_compare($mysql_version,TP_REQUIRED_MYSQL_VERSION, '>=') || class_exists(TP_Db::class);
            $version_url = sprintf($this->_esc_url( $this->__( 'https://wordpress.org/support/wordpress-version/version-%s/' ) ),$this->_sanitize_title( TP_VERSION ));//todo
            $learn_more = sprintf("<a href='%s'>Learn more about updating PHP</a>",$this->_esc_url( $this->_tp_get_update_php_url() ));
            $php_update_message = "<p>{$learn_more}</p>";
            $annotation = $this->_tp_get_update_php_annotation();
            if ( $annotation ) { $php_update_message .= "<p><em>{$annotation}</em></p>";}
            if ( ! $this->_mysql_compat && ! $this->_php_compat ) {
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires PHP version %3\$s or higher and MySQL version %4\$s or higher. You are running PHP version %5\$s and MySQL version %6\$s."),
                        $version_url,TP_VERSION,TP_REQUIRED_PHP_VERSION,TP_REQUIRED_MYSQL_VERSION,PHP_VERSION,$mysql_version).$php_update_message;
            } elseif (! $this->_php_compat) {
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires PHP version %3\$s or higher. You are running version %4\$s."),
                    $version_url,TP_VERSION,TP_REQUIRED_PHP_VERSION,PHP_VERSION);
            } elseif (! $this->_mysql_compat) {
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires MySQL version %3\$s or higher. You are running version %4\$s."),
                    $version_url,TP_VERSION,TP_REQUIRED_MYSQL_VERSION,$mysql_version);
            }
            $this->_table_prefix = sprintf($this->__('Your %s file has an empty database table prefix, which is not supported.'),
                '<code>TP_Config.php</code> and what you can find in the Root directory.');
            $this->_not_upgrade = sprintf($this->__("The constant %s cannot be defined when installing TailoredPress."),
                '<code>DO_NOT_UPGRADE_GLOBAL_TABLES</code>');
            //language part skipped for now
        }
		private function __display_header( $body_classes = ''):string{
			ob_start();
			header( 'Content-Type: text/html; charset=utf-8' );
			$output  = ob_get_clean();
            if ($this->_is_rtl()){$body_classes .=' rtl';}
            $this->_body_classes = $body_classes ?: 'body-class ';
            $output .= "<!DOCTYPE html><html {$this->_get_language_attributes()}><head>";
            $output .= "<meta name='viewport' content='width=device-width' />";
            $output .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
            $output .= "<meta name='robots' content='noindex,nofollow' />";
            $output .= "<title>{$this->__( 'TailoredPress &rsaquo; Installation' )}</title>";
            $output .= $this->_tp_get_admin_css('install',true);
            $output .= "</head><body class='tp-core-ui {$this->_body_classes}'>";
            $output .= "<header><p><i class='tp-logo'></i>{$this->__('TailoredPress')}</p></header>";
            return $output;
		}
		private function __display_setup_form( $error = null ):string{
            $output  = "";
            $this->__user_table = ( $this->tpdb->get_var( $this->tpdb->prepare( 'SHOW TABLES LIKE %s', $this->tpdb->esc_like( $this->tpdb->users ) ) ) !== null );
            $this->__blog_public = 1;
            if ( isset( $_POST['weblog_title'] ) ) {
                $this->__blog_public = isset( $_POST['blog_public'] );
            }
            $this->__weblog_title = isset( $_POST['weblog_title'] ) ? trim( $this->_tp_unslash( $_POST['weblog_title'] ) ) : '';
            $this->__user_name    = isset( $_POST['user_name'] ) ? trim( $this->_tp_unslash( $_POST['user_name'] ) ) : '';
            $this->__admin_email  = isset( $_POST['admin_email'] ) ? trim( $this->_tp_unslash( $_POST['admin_email'] ) ) : '';
            if ( ! is_null( $error ) ) {
                $output .= "<div class='block step-three error'>";
                $output .= "<h3>{$this->__('Welcome, Hello')}</h3>";
                $output .= "<p class='message'>$error</p></div>";
            }
            return $output;
        }
        private function __display_success():string{
            $output  = "<div class='block step-four success'><ul><li>";
            $output .= "<h3>{$this->__('Success!')}</h3>";
            $output .= "<p>{$this->__('TailoredPress has been installed. Thank you, and enjoy!')}</p></li><li>";
            $output .= "<dt>{$this->__('Username')}</dt>";
            $output .= "<dt>{$this->_esc_html( $this->_sanitize_user( $this->__user_name, true ) )}</dt>";
            $output .= "</li><li><dt>{$this->__('Password')}</dt><dt>";
            if ( ! empty( $this->__result['password'] ) && empty( $this->__admin_password_check ) ){
                $output .= "<code>{$this->_esc_html( $this->__result['password'] )}</code>";
            }
            $output .= "Placeholder for 'password_message'</dt>";//$this->__result['password_message']
            $output .= "</li><li>";
            $output .= "<dt class='step'><a href='{$this->_esc_url( $this->_tp_login_url() )}' class='button button-large'>{$this->__('Log In')}</a></dt>";
            $output .= "<dt></dt></li><li>";
            $output .= "What's left here is the mobile part but that's for later because I've to create the js logic first. ";
            $output .= "</li></ul></div>";
            return $output;
        }
        private function __is_installed():string{
            $output = "<main class='is-installed tp-flex'>";
            $output .= "<h1>{$this->__('Already Installed')}</h1>";
            $output .= "<p>{$this->__('You appear to have already installed TailoredPress. To reinstall please clear your old database tables first.')}</p>";
            $output .= "<p class='step'><a href='{$this->_esc_url( $this->_tp_login_url() )}'>{$this->__('Log In')}</a></p>";
            $output .= "</main></body></html>";
            return $output;
        }
        private function __is_compat():string{
            $output  = "<main class='compat tp-flex'>";
            $output .= "<h1>{$this->__('Requirements Not Met')}</h1>";
            $output .= $this->_compat;
            $output .= "</main></body></html>";
            return $output;
        }
        private function __is_table_prefix():string{
            $output  = "<main class='table-prefix tp-flex'>";
            $output .= "<h1>{$this->__('Configuration Error')}</h1>";
            $output .= "<p>{$this->_table_prefix}</p>";
            $output .= "</main></body></html>";
            return $output;
        }
        private function __is_not_upgrade():string{
            $output = "<main class='not-upgrade tp-flex'>";
            $output .= "<h1>{$this->__('Configuration Error')}</h1>";
            $output .= "<p>{$this->_not_upgrade}</p>";
            $output .= "</main></body></html>";
            return $output;
        }
        private function __language_setup():string{//$language = ''
            $output  = "<footer><p><strong>Notice:</strong> About languages and translations.</p>";
            $output .= "<p>As this stack is built by a single person and not by a big enterprise, is that out of scope for now.</p></footer>";
            return $output;
        }
        private function __install_logic():string{
            $output = "";
            switch ( $this->_step ) {
                case 0: // Step 0. skipped //break;
                case 1:
                    $scripts_to_print[] = 'user-profile';
                    $output  = "<div class='block step-one welcome'>";
                    $output .= "<h3>{$this->__('Welcome, Hello')}</h3>";
                    $output .= "<p class='message'>{$this->__('Welcome to the experimental TailoredPress installation process! Just fill in the information below and you&#8217;ll be on your way')}</p>";
                    $output .= "<h4 class=''>{$this->__('Information needed.')}</h4>";
                    $output .= "<p class=''>{$this->__('Please provide the following information. Don&#8217;t worry, you can always change these settings later.')}</p>";
                    $output .= "</div>";
                    $output .= $this->_get_form;
                    break;
                case 2:
                    $loaded_language = 'en_US';
                    $output = "";
                    $err_msg = null;
                    if ( ! empty( $this->tpdb->error )) { //todo
                        if($this->tpdb instanceof TP_Error){ $err_msg = $this->tpdb->get_error_message();}
                        $output .= $this->_tp_get_die($err_msg);
                    }
                    $scripts_to_print[] = 'user-profile';
                    $this->__weblog_title         = isset( $_POST['weblog_title'] ) ? trim( $this->_tp_unslash( $_POST['weblog_title'] ) ) : '';
                    $this->__user_name            = isset( $_POST['user_name'] ) ? trim( $this->_tp_unslash( $_POST['user_name'] ) ) : '';
                    $this->__admin_password       = isset( $_POST['admin_password'] ) ? $this->_tp_unslash( $_POST['admin_password'] ) : '';
                    $this->__admin_password_check = isset( $_POST['admin_password2'] ) ? $this->_tp_unslash( $_POST['admin_password2'] ) : '';
                    $this->__admin_email          = isset( $_POST['admin_email'] ) ? trim( $this->_tp_unslash( $_POST['admin_email'] ) ) : '';
                    $this->__public               = isset( $_POST['blog_public'] ) ? (int) $_POST['blog_public'] : 1;
                    $error = false;
                    if (empty( $this->__user_name ) ) {
                        $output .= $this->__display_setup_form( $this->__( 'Please provide a valid username.' ) );
                        $error = true;
                    }elseif ( $this->_sanitize_user( $this->__user_name, true ) !== $this->__user_name ) {
                        $output .= $this->__display_setup_form( $this->__( 'The username you provided has invalid characters.' ) );
                        $error = true;
                    }elseif ( $this->__admin_password !== $this->__admin_password_check ) {
                        $output .= $this->__display_setup_form( $this->__( 'Your passwords do not match. Please try again.' ) );
                        $error = true;
                    }elseif ( empty( $this->__admin_email ) ) {
                        $output .= $this->__display_setup_form( $this->__( 'You must provide an email address.' ) );
                        $error = true;
                    }elseif ( ! $this->_is_email( $this->__admin_email ) ) {
                        $output .= $this->__display_setup_form( $this->__( 'Sorry, that isn&#8217;t a valid email address. Email addresses look like <code>username@example.com</code>.' ) );
                        $error = true;
                    }
                    if ( false === $error ) {
                        $output .= $this->tpdb->show_errors();
                        $this->__result = $this->_tp_install( $this->__weblog_title, $this->__user_name, $this->__admin_email, $this->__public, '', $this->_tp_slash( $this->__admin_password ), $loaded_language );
                        $output .= $this->__display_success();
                    }
                    break;
            }
            //$output .= "";
            return $output;
        }
        private function __to_string():string{
            $this->tpdb->base_prefix = 'test_';
            if($this->_is_blog_installed()){ die($this->__display_header().$this->__is_installed());}
            if (! $this->_mysql_compat || ! $this->_php_compat ){ die($this->__display_header().$this->__is_compat());}
            if (! is_string( $this->tpdb->base_prefix ) || '' === $this->tpdb->base_prefix ){ die($this->__display_header().$this->__is_table_prefix());}
            if (defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) { die($this->__display_header().$this->__is_not_upgrade());}
            $output  = $this->__display_header();
            $output .= "<section class='module install'>";
            $output .= $this->__install_logic();
            $output .= '</section>';
            $output .= "";
            $output .= "</br>";//todo is temporary here!
            $output .= $this->__language_setup();
            $output .= "</body></html>";
            return  $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}