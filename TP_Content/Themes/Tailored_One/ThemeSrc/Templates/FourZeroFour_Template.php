<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-12-2022
 * Time: 07:52
 */
namespace TP_Content\Themes\Tailored_One\ThemeSrc\Templates;
use TP_Core\CoreSettings;
if(ABSPATH){
    class FourZeroFour_Template extends CoreSettings{
        //private $__args;
        private $__footer_args;
        private $__header_args;
        public function __construct($args = null){
            parent::__construct();
            //$this->__args = $args;
            $this->__footer_args = ['theme_name' => 'Tailored_One'];
            $this->__header_args = ['theme_name' => 'Tailored_One'];
        }
        private function __to_string():string{
            $output  = $this->_get_header($this->__header_args);
            $output .= "";
            $output .= "";
            $output .= "<br>TP_Content\Themes\Tailored_One\FourZeroFour_Template";
            $output .= "";
            $output .= "";
            $output .= $this->_get_footer($this->__footer_args);
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}