<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\TP_Library\Templates;
if(ABSPATH){
    class DefaultSidebar{
        //private $__sdb_args;
        public function __construct(...$args){
            //$this->__sdb_args = $args;
        }
        private function __to_string():string{
            $output = "<br/>";
            $output .= "DefaultSidebar";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

