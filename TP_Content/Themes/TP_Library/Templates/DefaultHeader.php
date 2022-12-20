<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-12-2022
 * Time: 08:14
 */
namespace TP_Content\Themes\TP_Library\Templates;
if(ABSPATH){
    class DefaultHeader{
        //private $__hdr_args;
        public function __construct($args = null){
            //$this->__hdr_args = $args;
        }
        private function __to_string():string{
            $output = "<!DOCTYPE html>";
            $output .= "<html><head>";
            $output .= "";
            $output .= "</head><body>";
            $output .= "Default Header";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else{die;}

