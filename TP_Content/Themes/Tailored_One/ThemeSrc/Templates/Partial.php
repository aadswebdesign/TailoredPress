<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Templates;
if(ABSPATH){
    class Partial{
        //private $__pts_args;
        public function __construct(...$args){
            //$this->__pts_args = $args;
        }
        private function __to_string():string{
            $output = "";
            $output .= "</br>Themes\Tailored_One\ThemeSrc\Templates\Partial";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}