<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-12-2022
 * Time: 08:14
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Components;
if(ABSPATH){
    class Special_Header{
        protected $_hdr_args;
        public function __construct($args = null){
            $this->_hdr_args = $args;
        }
        private function __to_string():string{
            $output = "<!DOCTYPE html>";
            $output .= "<html><head>";
            $output .= "";
            $output .= "</head><body>";
            $output .= "<br>Themes\Tailored_One\ThemeSrc\Components\Special_Header";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else{die;}

