<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-9-2022
 * Time: 14:29
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Components;
use TP_Content\Themes\TP_Library\Components\DefaultSidebar;
if(ABSPATH){
    class Sidebar{
        use DefaultSidebar;
        //private $__args;
        public function __construct($args = null){
            //$this->__args = $args;
            $this->__cpn_construct($args);
        }
        private function __to_string():string{
            $output = "";
            $output .= $this->__cpn_string();
            $output .= "<br>Themes\Tailored_One\ThemeSrc\Components\Sidebar";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

