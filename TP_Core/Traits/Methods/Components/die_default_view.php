<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 02:59
 */
namespace TP_Core\Traits\Methods\Components;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_robots_template;
if(ABSPATH){
    class die_default_view{
        use _action_01,_load_03;
        use _filter_01, _formats_02;
        use _option_01;
        use _I10n_01,_I10n_03,_I10n_04;
        use _methods_04,_methods_12;
        use _robots_template;
        use _general_template_02;
        use _general_template_08;
        protected $_html;
        protected $_args;
        public function __construct(...$args){
            $this->_args = $args;
        }
        private function __to_string():string{
            if ( ! $this->_did_action( 'admin_head' ) ) {
                ob_start();
                if ( ! headers_sent() ) {
                    header( "Content-Type: text/html; charset={$this->_args['charset']}" );
                    $this->_status_header( $this->_args['response'] );
                    $this->_nocache_headers();
                }
                $text_direction = $this->_args['text_direction'];
                $this->_args['dir_attr']=  "dir='$text_direction' {$this->_get_language_attributes()}";
                $this->_html = ob_get_clean();
                $charset = "charset ={$this->_args['charset']}";
                $this->_html  .= "<!DOCTYPE html>";
                $this->_html .= "<html {$this->_args['dir_attr']}>";
                $this->_html .= "<head>";
                $this->_html .= "<meta http-equiv='Content-Type' content='text/html; $charset' />";
                $this->_html .= "<meta name='viewport' content='width=device-width' />";
                $this->_add_filter( 'tp_robots',[$this,'__tp_robots_no_robots']);
                $this->_html .= $this->_tp_get_robots();
                $this->_html .= "<title>{$this->_args['title']}</title>";
                ob_start();
                ?>
                <style>
                    /** todo make this my own */
                </style>
                <?php
                $this->_html .= ob_get_clean();
                $this->_html .= "</head>";
                $this->_html .= "<body id='error_page'><p>todo error page, from (TP_Core\Traits\Methods\Components)</p>";
            }
            $this->_html .= $this->_args['msg'];
            $this->_html .= "</body>";
            $this->_html .= "</html>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;

