<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Panel;
if(ABSPATH){
    class TP_Customize_Themes_Panel extends TP_Customize_Panel {
        public $type = 'themes';
        protected function _get_render_template():string{
            $output  = "_get_render_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//36
        protected function _get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//67
    }
}else die;