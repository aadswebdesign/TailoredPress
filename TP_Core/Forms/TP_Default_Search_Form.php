<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 10:44
 */
namespace TP_Core\Forms;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\K_Ses\_k_ses_03;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Query\_query_01;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;

if(ABSPATH){
    class TP_Default_Search_Form{
        use _formats_02,_formats_07,_formats_08,_formats_06,_link_template_09,_link_template_10,_filter_01,_option_01,_load_03,_load_04;
        use _k_ses_03,_I10n_01,_I10n_03,_I10n_04,_I10n_05,_general_template_08,_query_01;
        protected $_args;
        protected $_html;
        public function __construct(...$args){
            if($args !== null){
                foreach ($args as $arg => $key){
                    $_result = $args[$arg] = $key;
                    $this->_args['attr_aria_label'] = $_result["attr_aria_label"];
                }

            }
        }
        private function __to_string():string{
            $this->_html = "<h3>TP_Default_Search_Form</h3>";
            $this->_html .= "<form role='search' {$this->_args['attr_aria_label']} method='get' class='' action='{$this->_esc_url($this->_home_url( '/' ))}'><ul>";
            $this->_html .= "<li><dt><label><span class=''>{$this->__('Search for:', 'label')}</span></label></dt>";
            $this->_html .= "<dd><input type='search' class='' placeholder='{$this->_x('Search &hellip;', 'placeholder')}' value='{$this->_get_search_query()}' name='s' /></dd></li>";
            $this->_html .= "<li><dd><input type='submit' class='' value='{$this->_esc_attr_x('Search', 'submit button')}'/></dd></li>";
            $this->_html .= "</ul></form>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}