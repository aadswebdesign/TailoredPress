<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\DefaultTheme\ThemeSrc\Templates;
use TP_Content\Themes\DefaultTheme\ThemeSrc\MethodsCollector;
if(ABSPATH){
    class DT_Default_Sidebar extends MethodsCollector {
        protected $_args;
        protected $_html;
        public function __construct($args){
            //$this->_args = $args;
        }
        private function __to_string():string{
            $this->_html = "<br/>";
            $this->_html .= "DT_Default_Sidebar";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

