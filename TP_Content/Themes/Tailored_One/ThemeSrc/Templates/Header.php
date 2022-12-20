<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-12-2022
 * Time: 08:14
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Templates;
if(ABSPATH){
    class Header{
        //private $__hdr_args;
        public function __construct($args = null){
            //$this->__hdr_args = $args;
            // $output .= $this->__hdr_args['header_test'];
        }
        private function __to_string():string{
            $output = "<!DOCTYPE html>";
            $output .= "<html><head>";
            $output .= "";
            $output .= "</head><body>";
            $output .= "Header";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else{die;}

