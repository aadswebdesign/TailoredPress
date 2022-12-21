<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-9-2022
 * Time: 19:35
 */
namespace TP_Content\Themes\Tailored_One;
use TP_Core\CoreSettings;
//use TP_Content\Themes\TP_Library\Templates\Header;
//use TP_Content\Themes\Tailored_One\ThemeSrc\Templates\Tailored_One_Header;



if(ABSPATH){
    class Theme_Index extends CoreSettings {
        private $__footer_args;
        private $__header_args;
        private $__partial_args = [];
        private $__search_args = [];
        private $__sidebar_args = [];


        protected $_get_a_partial = [];
        protected $_partial_args = [];
        protected $_get_a_sidebar;
        protected $_sidebar_args;
        public function __construct($args = null){
            parent::__construct();
            //$index_args = $args;
            $this->__footer_args = ['theme_name' => 'Tailored_One', 'class_name' => 'Special'];
            $this->__header_args = ['theme_name' => 'Tailored_One', 'class_name' => 'Special'];
            $this->__partial_args[1] = ['theme_name' => 'Tailored_One'];
            $this->__partial_args[0] = ['theme_name' => 'Tailored_One', 'class_name' => 'Special'];
            $this->__partial_args[2] = [];
            $this->__search_args[0] = [];
            $this->__search_args[1] = ['theme_name' => 'Tailored_One'];

            $this->__sidebar_args[1] = ['theme_name' => 'Tailored_One'];
            $this->__sidebar_args[0] = ['theme_name' => 'Tailored_One', 'class_name' => 'Special'];

        }
        private function __to_string():string{

            $output  = $this->_get_header($this->__header_args);
            $output .= "<br/>Tailored_One/Theme_Index";
            $output .= $this->_get_sidebar($this->__sidebar_args[1]);
            $output .= "<br/>";
            $output .= $this->_get_sidebar($this->__sidebar_args[0]);
            $output .= "<br/>";
            $output .= $this->_get_partial($this->__partial_args[0]);
            $output .= "<br/>";
            $output .= $this->_get_partial($this->__partial_args[1]);
            $output .= "<br/>";
            $output .= $this->_get_partial($this->__partial_args[2]);
            $output .= "<br/>";
            $output .= $this->_get_search_form($this->__search_args[1]);
            $output .= "<br/>";
            $output .= $this->_get_search_form($this->__search_args[0]);
            $output .= "<br/>";
            $output .= $this->_get_footer($this->__footer_args);
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}