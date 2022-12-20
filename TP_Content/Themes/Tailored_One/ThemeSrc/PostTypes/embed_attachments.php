<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-12-2022
 * Time: 14:28
 */

namespace TP_Content\Themes\Tailored_One\ThemeSrc\PostTypes;
if(ABSPATH){
    class embed_attachments{
        protected $_args;
        public function __construct($args = null){
            $this->_args = $args;
        }
        private function __to_string():string{
            $output  = "";
            $output .= "</br>embed_attachments from ThemeSrc\PostTypes</br>";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}