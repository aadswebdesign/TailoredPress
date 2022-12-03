<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */

namespace TP_Core\Templates;
if(ABSPATH){
    class TP_Block_Template_Header{
        protected $_args;
        protected $_html;
        public function __construct(...$args){
            $this->_args = $args;
        }
        private function __to_string():string{
            $this->_html = "<br/>";
            $this->_html .= "TP_Block_Template_Header";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

