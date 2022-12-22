<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-12-2022
 * Time: 07:52
 */
namespace TP_Content\Themes\TP_Library\Components;
if(ABSPATH){
    trait DefaultContent_Featured{
        //private $__cpn_args;
        private function __cpn_construct($args = null){
            //$this->__cpn_args = $args;
        }
        private function __cpn_string():string{
            $output  = "";
            $output .= "";
            $output .= "<br>TP_Content\Themes\TP_Library\Components\DefaultContent_Featured";
            $output .= "";
            $output .= "";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}