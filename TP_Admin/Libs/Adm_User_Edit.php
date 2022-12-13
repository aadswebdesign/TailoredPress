<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-10-2022
 * Time: 10:26
 */
namespace TP_Admin\Libs;
if(ABSPATH){
    class Adm_User_Edit{
        protected $_args;
        protected $_html;
        public function __construct(...$args){
            $this->_args = $args;
        }
        private function __to_string():string{
            $this->_html = "Adm_User_Edit";
            $this->_html .= "";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}