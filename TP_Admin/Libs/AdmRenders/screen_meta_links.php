<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-7-2022
 * Time: 17:20
 */
namespace TP_Admin\Libs\AdmRenders;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class screen_meta_links{
        use _I10n_01;
        protected $_args;
        public function __construct($link_args){
            $this->_args['screen_options'] = $link_args['screen_options'];
            $this->_args['get_help_tabs'] = $link_args['get_help_tabs'];
        }
        private function __to_string():string{
            $output = "<div id='screen_meta_links' class='block meta-links'><ul>";//div8
            if ( $this->_args['screen_options']){
                $output .= "<li id='screen_options_link_wrap' class='hide-if-no-js screen-meta-toggle'>";//div9
                $output .= "<dd><button type='button' id='show_settings_link' class='button show-settings' aria-controls='screen-options-wrap' aria-expanded='false'>{$this->__('Screen Options')}</button></dd>";
                $output .= "</li>";
            }
            if ( $this->_args['get_help_tabs'] ){
                $output .= "<li id='contextual_help_link_wrap' class='hide-if-no-js screen-meta-toggle'>";//div10
                $output .= "<dd><button type='button' id='contextual_help_link' class='button show-settings' aria-controls='screen-options-wrap' aria-expanded='false'>{$this->__('Help')}</button></dd>";
                $output .= "</li>";
            }
            $output .= "</ul></div>";//end div8
            return (string) $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;