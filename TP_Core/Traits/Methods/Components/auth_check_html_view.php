<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 06:46
 */
namespace TP_Core\Traits\Methods\Components;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
if(ABSPATH){
    class auth_check_html_view{
        use _I10n_01;
        use _I10n_02;
        use _methods_03;
        use _formats_07;
        protected $_html;
        protected $_args;
        public function __construct(...$args){
            $this->_args = $args;
        }
        private function __to_string():string{
            $this->_html = "<div id='tp_auth_check_wrap' class='{$this->_args['wrap_class']}'>";
            $this->_html .= "<div id='tp_auth_check_bg'></div>";
            $this->_html .= "<div id='tp_auth_check'>";
            $this->_html .= "<button type='button' class='tp-auth-check-close btn-link'>";
            $this->_html .= "<span class='screen-reader-text'>{$this->__('Close dialog')}</span>";
            $this->_html .= "</button>";
            if($this->_args['same_domain']){$login_src = $this->_add_query_arg(
                    ['interim-login' => '1','tp_lang' => $this->_get_user_locale(),],$this->_args['login_url']);
                $this->_html .= "<div id='tp_auth_check_form' class='loading' data-src='{$this->_esc_url( $login_src )}'></div>";
            }
            $this->_html .= "<div class='tp-auth-fallback'>";
            $this->_html .= "<p><b class='tp-auth-fallback-expired' tabindex='0'>{$this->__('Session expired')}</b></p>";
            $this->_html .= "<p><a href='{$this->_esc_url( $this->_args['login_url'] )}'> </a> ";
            $this->_html .= $this->__('The login page will open in a new tab. After logging in you can close it and return to this page.');
            $this->_html .= "</p></div></div></div>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;