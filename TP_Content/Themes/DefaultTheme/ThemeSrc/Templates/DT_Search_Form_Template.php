<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 20:05
 */
namespace TP_Content\Themes\DefaultTheme\ThemeSrc\Templates;
use TP_Content\Themes\DefaultTheme\ThemeSrc\MethodsCollector;

if(ABSPATH){
    class DT_Search_Form_Template extends MethodsCollector {
        protected $_args=[];
        protected $_html;
        public function __construct($args){
            if($args !== null){
                foreach ($args as $arg => $key){
                    $result = $args[$arg] = $key;
                    $this->_args['attr_aria_label'] = $result["attr_aria_label"];
                }
            }
        }
        private function __to_string():string{
            $this->_html = "<h3>DT_Search_Form_Template</h3>";
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