<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-5-2022
 * Time: 19:04
 */
namespace TP_Core\Traits\Templates\Components;
if(ABSPATH){
    class print_media_view {
        protected $_html;
        protected $_args;
        public function __construct(...$args){
            $this->_args = $args;
        }
        private function __to_string():string{
            $this->_html = "todo";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;