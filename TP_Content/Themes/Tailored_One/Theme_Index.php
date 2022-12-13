<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-9-2022
 * Time: 19:35
 */
namespace TP_Content\Themes\Tailored_One;
use TP_Core\CoreSettings;
if(ABSPATH){
    class Theme_Index extends CoreSettings {
        protected $_args;
        protected $_html;
        protected $_get_a_footer;
        protected $_footer_args;
        protected $_get_a_header;
        protected $_header_args;
        protected $_get_a_partial = [];
        protected $_partial_args = [];
        protected $_get_a_sidebar;
        protected $_sidebar_args;
        public function __construct(...$args){
            parent::__construct();
            //$this->_args = $args;
            $this->_footer_args = [
                'name' => 'get_footer',
                'theme_name' => 'Tailored_One',
                'class_name' => 'DT_Default_Footer'
            ];
            $this->_get_a_footer = $this->_get_footer($args,$this->_footer_args);
            $this->_header_args = [
                'name' => 'get_header',
                'theme_name' => 'Tailored_One',
                'class_name' => 'DT_Default_Header'
            ];
            $this->_get_a_header = $this->_get_header($args,$this->_header_args);
            $this->_partial_args['one'] =[
                'name' => 'get_partial_one',
                'theme_name' => 'Tailored_One',
                'class_name' => 'DT_Partial_One'
            ];
            $this->_get_a_partial['one'] = $this->_get_partial($args,$this->_partial_args['one']);

            $this->_partial_args['two'] =[
                'name' => 'get_partial_two',
                'theme_name' => 'Tailored_One',
                'class_name' => 'DT_Partial_Two'
            ];
            $this->_get_a_partial['two'] = $this->_get_partial($args,$this->_partial_args['two']);

            $this->_sidebar_args = [
                'name' => 'get_header',
                'theme_name' => 'Tailored_One',
                'class_name' => 'DT_Default_Sidebar'
            ];
            $this->_get_a_sidebar = $this->_get_sidebar($args,$this->_sidebar_args);
        }
        private function __to_string():string{
            //$this->_html = $this->_get_a_header;
            //$this->_html .= $this->_get_a_partial['one'];
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "<br/>Tailored_One/Theme_Index";
            //$this->_html .= $this->_get_a_sidebar;
            //$this->_html .= $this->_get_a_partial['two'];
            $this->_html .= "";
            $this->_html .= "";
            $this->_html .= "";
            //$this->_html .= $this->_get_a_footer;
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}