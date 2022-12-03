<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-8-2022
 * Time: 22:25
 */
namespace TP_Admin\BaseLibs;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Constructs\_construct_db;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Pluggables\_pluggable_05;
use TP_Core\Traits\Templates\_general_template_09;

if(ABSPATH){
    class TP_Install_Form{
        use _action_01;
        use _adm_template_04;
        use _construct_db;
        use _formats_02;
        use _formats_08;
        use _formats_11;
        use _general_template_09;
        use _init_db;
        use _I10n_01,_I10n_05;
        use _pluggable_05;
        protected $_args;
        protected $_form_args;
        protected $_html;
        public function __construct(...$args){
            $this->_args = $args;
            $this->tpdb = $this->_init_db();
            $this->_form_args = [
                'user_table'=> ( $this->tpdb->get_var( $this->tpdb->prepare( 'SHOW TABLES LIKE %s', $this->tpdb->esc_like( $this->tpdb->users ) ) ) !== null ),
                'blog_public' => 1,
                'weblog_title' => isset( $_POST['weblog_title'] ) ? trim( $this->_tp_unslash( $_POST['weblog_title'] ) ) : '',
                'user_name' => isset( $_POST['user_name'] ) ? trim( $this->_tp_unslash( $_POST['user_name'] ) ) : '',
                'admin_email' => isset( $_POST['admin_email'] ) ? trim( $this->_tp_unslash( $_POST['admin_email'] ) ) : '',
                'error' => $args['error'],
            ];
            if ( isset( $_POST['weblog_title'] ) ) {
                $this->_form_args['blog_public'] = isset( $_POST['blog_public'] );
            }
        }
        private function __to_string():string{
            $this->_html = "<section class='module install'>";
            if($this->_form_args['error'] !== null){
                $this->_html .= "<div class='block one error'>";
                $this->_html .= "<h1>{$this->_x('Welcome', 'Howdy')}</h1>";
                $this->_html .= "<p class='message'>{$this->_form_args['error']}</p>";
                $this->_html .= "</div>";
            }
            $this->_html .= "<div class='block two form'>";
            $this->_html .= "<form id='setup' method='post' action='tp_install.php?step1' novalidate><ul class='content' role='presentation'><li>";
            $this->_html .= "<dt><label for='weblog_title'>{$this->__('Site Title')}</label></dt>";
            $this->_html .= "<dd><input name='weblog_title' id='weblog_title' type='text' value='{$this->_esc_attr($this->_form_args['weblog_title'])}' size='25' /></dd>";
            $this->_html .= "</li><li>";
            $this->_html .= "<dt><label for='user_login'>{$this->__('Username')}</label></dt>";
            if ( $this->_form_args['user_table'] ) {
                $this->_html .= "<h6>{$this->__('User(s) already exists.')}</h6>";
                $this->_html .= "<input name='user_name' id='user_login' type='hidden' value='admin' />";
            }else{
                $this->_html .= "<dd><input name='user_name' id='user_login' type='text' value='{$this->_esc_attr($this->_sanitize_user( $this->_form_args['user_name'], true ))}' size='25' /></dd>";
                $this->_html .= "<dt>{$this->__('Username-s can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.')}</dt>";
            }
            $this->_html .= "</li>";
            if (!$this->_form_args['user_table'] ){
                $initial_password = isset( $_POST['admin_password'] ) ? stripslashes( $_POST['admin_password'] ) : $this->_tp_generate_password( 18 );
                $admin_pw = (int) isset( $_POST['admin_password'] );
                $this->_html .= "<li class='form-field form-required user-pass1 wrap'><ul><li>";
                $this->_html .= "<dt><label for='pass1'>{$this->__('Password')}</label></dt>";
                $this->_html .= "<div class='tp-password'><ul><li>";
                $this->_html .= "<dd><input name='admin_password' id='pass1' type='password' data-pw='{$this->_esc_attr($initial_password)}' data-reveal='1' autocomplete='off' aria-describedby='pass-strength-result'/></dd>";
                $this->_html .= "<dd><button type='button' class='button tp-hide-pw hide-if-no-js' data-start_masked='{$admin_pw}' data-toggle='0' aria-label='{$this->_esc_attr__( 'Hide password' )}'>";
                $this->_html .= "<i class='dashicons dashicons-hidden'></i><span>{$this->__('Hide')}</span></button></dd>";
                $this->_html .= "<dt id='pass_strength_result' aria-live='polite'></dt>";
                $this->_html .= "</li></ul></div></li><li>";
                $this->_html .= "<dt><p><span class='description important hide-if-no-js'><strong>{$this->__('Important:')}</strong>";
                $this->_html .= "{$this->__('You will need this password to log&nbsp;in. Please store it in a secure location.')}</span></p></dt></li></ul></li>";
                $this->_html .= "<li class='form-field form-required user-pass2 hide-if-js'>";
                $this->_html .= "<dt><label for='pass2'>{$this->__('Repeat Password')}<span class='description'>{$this->__('(required)')}</span></label></dt>";
                $this->_html .= "<dd><input name='admin_password2' id='pass2' type='password' value='{$this->_esc_attr('')}' autocomplete='off' /></dd>";
                $this->_html .= "</li><li class='pw-weak'>";
                $this->_html .= "<dt><h6>{$this->__('Confirm Password')}</h6></dt>";
                $this->_html .= "<dd><input name='pw_weak' id='pw_weak' type='checkbox' class='pw-checkbox' /></dd>";
                $this->_html .= "<dt><label for='pw_weak'>{$this->__('Confirm use of weak password')}</label></dt>";
                $this->_html .= "</li>";
            }
            $privacy_matters = $this->_has_action( 'blog_privacy_selector' ) ? $this->__( 'Site visibility' ) : $this->__( 'Search engine visibility' );
            $this->_html .= "<li>";
            $this->_html .= "<dt><label for='admin_email'>{$this->__('Your Email')}</label></dt>";
            $this->_html .= "<dd><input name='admin_email' id='admin_email' type='email' value='{$this->_esc_attr($this->_form_args['admin_email'])}' /></dd>";
            $this->_html .= "<dt><h6>{$this->__('Double-check your email address before continuing.')}</h6></dt>";
            $this->_html .= "</li><li>";
            $this->_html .= "<dt><h4>{$privacy_matters}</h4></dt>";
            $this->_html .= "</li><li><fieldset><legend class='screen-reader-text'><span>{$privacy_matters}</span></legend><ul>";
            if ( $this->_has_action( 'blog_privacy_selector' ) ) {
                $this->_html .= "<li>";
                $this->_html .= "<dd><input name='blog_public' id='get_blog' type='radio' value='1' {$this->_get_checked( 1, $this->_form_args['blog_public'] )} /></dd>";
                $this->_html .= "<dt><label for='get_blog'>{$this->__('Allow search engines to index this site.')}</label></dt>";
                $this->_html .= "</li><li>";
                $this->_html .= "<dd><input name='blog_public' id='get_no_robots' type='radio' value='0'  {$this->_get_checked(0, $this->_form_args['blog_public'] )}/></dd>";
                $this->_html .= "<dt><label for='get_no_robots'>{$this->__('Discourage search engines from indexing this site.')}</label></dt>";
                $this->_html .= "<dt><p class='description'>{$this->__('Note: Neither of these options blocks access to your site &mdash; it is up to search engines to honor your request.')}</p></dt>";
                $this->_html .= "</li><li>";
                $this->_html .= $this->_do_action( 'blog_privacy_selector' );
                $this->_html .= "</li>";
            }else{
                $this->_html .= "<li>";
                $this->_html .= "<dd><input name='blog_public' id='blog_public' type='checkbox' value='0' {$this->_get_checked(0, $this->_form_args['blog_public'] )} /></dd>";
                $this->_html .= "<dt><label for='blog_public'>{$this->__('Discourage search engines from indexing this site')}</label></dt>";
                $this->_html .= "<dt><p class='description'>{$this->__('It is up to search engines to honor this request.')}</p></dt>";
                $this->_html .= "</li>";
            }
            $this->_html .= "</ul></fieldset></li><li>";
            $language = isset( $_REQUEST['language'] ) ? $this->_esc_attr( $_REQUEST['language'] ) : '';
            $this->_html .= "<dd class='step'>{$this->_get_submit_button($this->__('Install TailoredPress'), 'large', 'Submit', false, array( 'id' => 'submit' ))}</dd>";
            $this->_html .= "<input name='language' type='hidden' value='{$language}'/>";
            $this->_html .= "</li></ul></form></div>";
            $this->_html .= "</section>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }






    }
}else{die;}