<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-12-2022
 * Time: 08:14
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Templates;
if(ABSPATH){
    class Special_Footer{
        //private $__ftr_args;
        public function __construct($args = null){
            //$this->__ftr_args = $args;
        }
        private function __to_string():string{
            $output  = "";
            $output .= "</br>Special Footer";
            $output .= "</body></html>";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else{die;}

