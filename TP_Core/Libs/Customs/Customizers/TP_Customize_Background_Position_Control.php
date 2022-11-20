<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
if(ABSPATH){
    class TP_Customize_Background_Position_Control extends TP_Customize_Image_Control {
        public $type = 'background_position';
        public function render_content():void {}
        public function get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//39
    }
}else die;