<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\TP_Library\Components;
if(ABSPATH){
    trait DefaultSidebarFooter{
        //private $__cpn_args;
        private function __cpn_construct($args = null){
            //$this->__cpn_args = $args;
        }
        private function __cpn_string():string{
            $output = "";
            $output .= "<br>TP_Content\Themes\TP_Library\Components\DefaultSidebarFooter";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

