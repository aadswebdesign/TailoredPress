<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-12-2022
 * Time: 08:22
 */
namespace TP_Content\Themes;
use TP_Core\Traits\TP_Template_Loader;

if(ABSPATH){
    class ThemePanel{
        use TP_Template_Loader;
        protected $_args;
        public function __construct($args = null){
            $this->__tpl_construct($args);
        }
        private function __to_string():string{
            $output  = "</br>ThemePanel</br>";
            $output .= $this->__tpl_to_string();
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}