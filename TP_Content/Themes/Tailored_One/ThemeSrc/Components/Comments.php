<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-12-2022
 * Time: 07:52
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Components;
if(ABSPATH){
    class Comments{
        //private $__args;
        public function __construct($args = null){
            //$this->__args = $args;
        }
        private function __to_string():string{
            $output  = "";
            $output .= "";
            $output .= "<br>TP_Content\Themes\Tailored_One\ThemeSrc\Components\Comments";
            $output .= "";
            $output .= "";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}