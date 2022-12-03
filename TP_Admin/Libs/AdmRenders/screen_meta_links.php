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
        protected $_html;
        protected $_args;
        public function __construct($link_args){
            $this->_args['screen_options'] = $link_args['screen_options'];
            $this->_args['get_help_tabs'] = $link_args['get_help_tabs'];
        }
        private function __to_string():string{
            $this->_html = "<div id='screen_meta_links' class='block meta-links'>";//div8
            if ( $this->_args['screen_options']){
                $this->_html .= "<div id='screen_options_link_wrap' class='hide-if-no-js screen-meta-toggle'>";//div9
                $this->_html .= "<button type='button' id='show_settings_link' class='button show-settings' aria-controls='screen-options-wrap' aria-expanded='false'>{$this->__('Screen Options')}</button>";
                $this->_html .= "</div>";//end div9
            }
            if ( $this->_args['get_help_tabs'] ){
                $this->_html .= "<div id='contextual_help_link_wrap' class='hide-if-no-js screen-meta-toggle'>";//div10
                $this->_html .= "<button type='button' id='contextual_help_link' class='button show-settings' aria-controls='screen-options-wrap' aria-expanded='false'>{$this->__('Help')}</button>";
                $this->_html .= "</div>";//end div10
            }
            $this->_html .= "</div>";//end div8
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;