<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Section;
if(ABSPATH){
    class TP_Customize_Themes_Section extends TP_Customize_Section {
        public $type = 'themes';
        public $action = '';
        public $filter_type = 'local';
        public function json():void{
            $exported = parent::json();
            $exported['action'] = $this->action;
            $exported['filter_type'] = $this->filter_type;
             return $exported;
        }
        protected function _get_render_template():string{
            $output  = "_get_render_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//71
        protected function _get_filter_bar_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//122
        protected function _get_filter_drawer_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//166
    }
}else die;