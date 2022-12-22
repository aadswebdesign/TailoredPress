<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-12-2022
 * Time: 08:14
 */
namespace TP_Content\Themes\TP_Library\Components;
if(ABSPATH){
    trait DefaultHeader{
        //private $__cpn_args;
        private function __cpn_construct($args = null){
            //$this->__cpn_args = $args;
        }
        private function __cpn_string():string{
            $output = "<!DOCTYPE html>";
            $output .= "<html><head>";
            $output .= "";
            $output .= "</head><body>";
            $output .= "TP_Content\Themes\TP_Library\Components\Default Header";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else{die;}

