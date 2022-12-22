<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\TP_Library\Components;
if(ABSPATH){
    trait DefaultSidebar{
        //private $__cpn_args;
        private function __cpn_construct($args = null){
            //$this->__cpn_args = $args;
        }
        private function __cpn_string():string{
            $output = "<br>";
            $output .= "TP_Content\Themes\TP_Library\Components\DefaultSidebar";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

