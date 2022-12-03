<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-8-2022
 * Time: 19:48
 */
namespace TP_Admin\BaseLibs;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_18;
use TP_Core\Traits\Methods\_methods_19;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_general_template_09;
if(ABSPATH){
    class TP_Install{
        use _formats_02;
        use _formats_04;
        use _formats_06;
        use _formats_07;
        use _formats_08;
        use _formats_11;
        use _general_template_02;
        use _general_template_08;
        use _general_template_09;
        use _I10n_01,_I10n_02;
        use _init_db;
        use _methods_04,_methods_05;
        use _methods_08;
        use _methods_18,_methods_19;
        protected $_args;
        protected $_body_classes;
        protected $_html;
        protected $_compat;
        protected $_php_compat;
        protected $_mysql_compat;
        protected $_table_prefix;
        protected $_not_upgrade;
        protected $_step;
        public $tpdb;
        public function __construct(...$args){
            $this->_args = $args;
            $this->__install_construct($args);
        }
        private function __install_construct(...$args):void{
            $this->_args = $args;
            $this->tpdb = $this->_init_db();
            define( 'TP_INSTALLING', true );
            if ( $this->_is_rtl()){$this->_body_classes ='rtl';}
            if ($args['body_classes']){
                $this->_body_classes = $args['body_classes'];
            }
            /** version things */
            $mysql_version = $this->tpdb->db_version();
            $this->_php_compat = version_compare(PHP_VERSION,TP_REQUIRED_PHP_VERSION, '>=');
            $this->_mysql_compat = version_compare($mysql_version,TP_REQUIRED_MYSQL_VERSION, '>=');
            $version_url = sprintf($this->_esc_url( $this->__( 'https://wordpress.org/support/wordpress-version/version-%s/' ) ),$this->_sanitize_title( TP_VERSION ));
            $learn_more = sprintf("<a href='%s'>Learn more about updating PHP</a>",$this->_esc_url( $this->_tp_get_update_php_url() ));
            $php_update_message = "<p>{$learn_more}</p>";
            $annotation = $this->_tp_get_update_php_annotation();
            if ( $annotation ) { $php_update_message .= "<p><em>{$annotation}</em></p>";}
            if ( ! $this->_mysql_compat && ! $this->_php_compat ) {
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires PHP version %3\$s or higher and MySQL version %4\$s or higher. You are running PHP version %5\$s and MySQL version %6\$s."),
                    $version_url,TP_VERSION,TP_REQUIRED_PHP_VERSION,TP_REQUIRED_MYSQL_VERSION,PHP_VERSION,$mysql_version).$php_update_message;
            } elseif(! $this->_php_compat) {
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires PHP version %3\$s or higher. You are running version %4\$s."),
                    $version_url,TP_VERSION,TP_REQUIRED_PHP_VERSION,PHP_VERSION);
            }elseif(! $this->_mysql_compat){
                $this->_compat = sprintf($this->__("You cannot install because <a href='%1\$s'>TailoredPress %2\$s</a> requires MySQL version %3\$s or higher. You are running version %4\$s."),
                    $version_url,TP_VERSION,TP_REQUIRED_MYSQL_VERSION,$mysql_version);
            }
            $this->_table_prefix = sprintf($this->__('Your %s file has an empty database table prefix, which is not supported.'),
                '<code>(class)TP_Config.php</code> and what you can find in the Root directory.');
            $this->_not_upgrade = sprintf($this->__("The constant %s cannot be defined when installing TailoredPress."),
                '<code>DO_NOT_UPGRADE_GLOBAL_TABLES</code>');
            $this->_step =  isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

        }
        private function __display_header():string{
            ob_start();
            header( 'Content-Type: text/html; charset=utf-8' );
            $this->_html = ob_get_clean();
            $this->_html  .= "<!DOCTYPE html>";
            $this->_html .= "<html {$this->_get_language_attributes()}><head>";
            $this->_html .= "<meta name='viewport' content='width=device-width' />";
            $this->_html .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
            $this->_html .= "<meta name='robots' content='noindex,nofollow' />";
            $this->_html .= "<title>{$this->__( 'TailoredPress &rsaquo; Installation' )}</title>";
            $this->_html .= $this->_tp_get_admin_css('install',true);
            $this->_html .= "</head><body class='tp-core-ui {$this->_body_classes}'>";
            $this->_html .= "<section><p><i class='tp-logo'></i>{$this->__('TailoredPress')}</p></section>";
            return (string) $this->_html;
        }
        private function __error_construct():string{
            $this->_html  = "<!DOCTYPE html>";
            $this->_html .= "<html><head>";
            $this->_html .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
            $this->_html .= "<title>Error: PHP is not running</title>";
            $this->_html .= "</head><body class='tp-core-ui'><main class='error tp-flex'>";
            $this->_html .= "<p id='logo'><a href='https://aadswebdesign.nl/'>TailoredPress</a></p>";
            $this->_html .= "<h1>Error: PHP is not running</h1>";
            $this->_html .= "<p>TailoredPress requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>";
            $this->_html .= "</main></body></html>";
            return (string) $this->_html;
        }
        private function __is_installed():string{
            $this->_html = "<main class='is-installed tp-flex'>";
            $this->_html .= "<h1>{$this->__('Already Installed')}</h1>";
            $this->_html .= "<p>{$this->__('You appear to have already installed TailoredPress. To reinstall please clear your old database tables first.')}</p>";
            $this->_html .= "<p class='step'><a href='{$this->_esc_url( $this->_tp_login_url() )}'>{$this->__('Log In')}</a></p>";
            $this->_html .= "</main></body></html>";
            return (string) $this->_html;
        }
        private function __compat():string{
            $this->_html  = "<main class='compat tp-flex'>";
            $this->_html .= "<h1>{$this->__('Requirements Not Met')}</h1>";
            $this->_html .= $this->_compat;
            $this->_html .= "</main></body></html>";
            return (string) $this->_html;
        }
        private function __table_prefix():string{
            $this->_html  = "<main class='table-prefix tp-flex'>";
            $this->_html .= "<h1>{$this->__('Configuration Error')}</h1>";
            $this->_html .= "<p>{$this->_table_prefix}</p>";
            $this->_html .= "</main></body></html>";
            return (string) $this->_html;
        }
        private function __not_upgrade():string{
            $this->_html = "<main class='not-upgrade tp-flex'>";
            $this->_html .= "<h1>{$this->__('Configuration Error')}</h1>";
            $this->_html .= "<p>{$this->_not_upgrade}</p>";
            $this->_html .= "</main></body></html>";
            return (string) $this->_html;
        }
        private function __language_setup($language = ''):string{
            if ( ! empty( $_REQUEST['language'] ) ) {
                $language = (string)preg_replace( '/\w/', '', $_REQUEST['language'] );
            } elseif ( isset( $GLOBALS['tp_local_package'] ) ) {
                $language = (string)$GLOBALS['tp_local_package'];
            }
            return $language;
        }
        private function __install_form($error = null):TP_Install_Form{
            $_form = null;
            if(!($_form instanceof TP_Install_Form)){
                $_form = new TP_Install_Form($error);
            }
            return $_form;
        }
        private function __to_string():string{
            $this->_html = "";
            if ( false ) {
                $this->_html .= $this->__error_construct();
            }
            if ( $this->_is_blog_installed() ){
                $this->_html .= $this->__display_header();
                $this->_html .= $this->__is_installed();
                exit;
            }
            if ( ! $this->_mysql_compat || ! $this->_php_compat ) {
                $this->_html .= $this->__display_header();
                $this->_html .= $this->__compat();
                exit;
            }
            if ( ! is_string( $this->tpdb->base_prefix ) || '' === $this->tpdb->base_prefix ) {
                $this->_html .= $this->__display_header();
                $this->_html .= $this->__table_prefix();
                exit;
            }
            if ( defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
                $this->_html .= $this->__display_header();
                $this->_html .= $this->__not_upgrade();
                exit;
            }
            $scripts_to_print = array( 'todo' );
            switch ( $this->_step ){
                case 0:
                    $this->_html .= $this->__display_header();
                    $this->_html .= "<main class='install tp-flex'>";
                    $this->_html .= "<h1>{$this->__('Welcome', 'Howdy')}</h1>";
                    $this->_html .= "<p>{$this->__('Welcome to TailoredPress. This is based on - but for sure not a replacement of WordPress!')}</p>";
                    $this->_html .= "<h4>Why, the Differences and Facts.</h4>";
                    $this->_html .= "<p>To come.</p>";
                    $this->_html .= "<h2>First the information needed to set this up.</h2>";
                    $this->_html .= "<p>Please provide them here (you can always alter them afterwards.)?</p>";
                    $this->_html .= $this->__install_form();
                    $this->_html .= "</main>";
                break;
                case 1:
                    if ( ! empty( $this->tpdb->error ) ) {
                        $this->_tp_die( $this->tpdb->error->get_error_message() );
                    }
                    $scripts_to_print[] = 'user-profile';
                    $this->_html .= $this->__display_header();
                    $this->_html .= "<main class='errors tp-flex'>";
                    $weblog_title         = isset( $_POST['weblog_title'] ) ? trim( $this->_tp_unslash( $_POST['weblog_title'] ) ) : '';
                    $user_name            = isset( $_POST['user_name'] ) ? trim( $this->_tp_unslash( $_POST['user_name'] ) ) : '';
                    $admin_password       = isset( $_POST['admin_password'] ) ? $this->_tp_unslash( $_POST['admin_password'] ) : '';
                    $admin_password_check = isset( $_POST['admin_password2'] ) ? $this->_tp_unslash( $_POST['admin_password2'] ) : '';
                    $admin_email          = isset( $_POST['admin_email'] ) ? trim( $this->_tp_unslash( $_POST['admin_email'] ) ) : '';
                    $public               = isset( $_POST['blog_public'] ) ? (int) $_POST['blog_public'] : 1;
                    $error = false;

                    if ( empty( $user_name ) ) {
                        $this->_html .= $this->__install_form( $this->__( 'Please provide a valid username.' ) );
                        $error = true;
                    } elseif ( $this->_sanitize_user( $user_name, true ) !== $user_name ) {
                        $this->_html .= $this->__install_form(  $this->__( 'The username you provided has invalid characters.' ) );
                        $error = true;
                    } elseif ( $admin_password !== $admin_password_check ) {
                        $this->_html .= $this->__install_form( $this->__( 'Your passwords do not match. Please try again.' ) );
                        $error = true;
                    } elseif ( empty( $admin_email ) ) {
                        $this->_html .= $this->__install_form( $this->__( 'You must provide an email address.' ) );
                        $error = true;
                    } elseif ( ! $this->_is_email( $admin_email ) ) {
                        $this->_html .= $this->__install_form( $this->__( 'Sorry, that isn&#8217;t a valid email address. Email addresses look like <code>username@example.com</code>.' ) );
                        $error = true;
                    }
                    $this->_html .= "</main>";
                    if ( false === $error ) {
                        $this->_html .= "<main class='success tp-flex'><ul><li>";
                        $this->_html .= "<h1>{$this->__('Success!')}</h1>";
                        $this->_html .= "<p>{$this->__('TailoredPress has been installed. Thanks for giving this a try.')}</p>";
                        $this->_html .= "</li><li>";
                        $this->_html .= "<dt>{$this->__('Username')}</dt>";
                        $this->_html .= "<dt>{$this->_esc_html( $this->_sanitize_user( $user_name, true ) )}</dt>";
                        $this->_html .= "</li><li>";
                        $this->_html .= "<dt>{$this->__('Password')}</dt>";
                        if ( ! empty( $result['password'] ) && empty( $admin_password_check ) ){
                            $this->_html .= "<dt><code>{$this->_esc_html( $result['password'] )}</code></dt>";
                        }
                        $this->_html .= "<dt><p>{$result['password_message']}</p></dt>";
                        $this->_html .= "</li><li>";
                        $this->_html .= "<dt class='step'><a href='{$this->_esc_url( $this->_tp_login_url() )}' class='button button-large'>{$this->__('Log In')}</a></dt>";
                        $this->_html .= "</li></ul></main>";
                    }
                break;
            }
            //todo a couple of js related tasks and that's for later!
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "</body></html>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}