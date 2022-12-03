<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-9-2022
 * Time: 19:35
 */
namespace TP_Content\Themes\DefaultTheme;
use TP_Content\Themes\DefaultTheme\ThemeSrc\MethodsCollector;
if(ABSPATH){
    class Maintenance_Index extends MethodsCollector{
        protected $_args;
        protected $_html;

        public function __construct(...$args){
        }
        private function __to_string():string{
            $this->_html = "";
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "<br/>Maintenance_Index";
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}