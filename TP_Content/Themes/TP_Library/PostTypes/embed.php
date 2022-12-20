<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-12-2022
 * Time: 14:28
 */
namespace TP_Content\Themes\TP_Library\PostTypes;
if(ABSPATH){
    class embed{
        protected $_args;
        public function __construct($args = null){
            $this->_args = $args;
        }
        private function __to_string():string{
            $output  = "";
            $output .= "</br>embed from TP_Library\PostTypes</br>";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}