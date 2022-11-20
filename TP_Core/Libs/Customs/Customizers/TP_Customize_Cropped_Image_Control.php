<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Misc\tp_script;
if(ABSPATH){
    class TP_Customize_Cropped_Image_Control extends TP_Customize_Image_Control{
        use tp_script,_methods_12;
        public $type = 'cropped_image';
        public $width = 150;
        public $height = 150;
        public $flex_width = false;
        public $flex_height = false;
        public function enqueue():void {
            $this->tp_enqueue_script( 'customize-views' );
            parent::enqueue();
        }
        public function to_json():void {
            parent::to_json();
            $this->json['width']       = $this->_abs_int( $this->width );
            $this->json['height']      = $this->_abs_int( $this->height );
            $this->json['flex_width']  = $this->_abs_int( $this->flex_width );
            $this->json['flex_height'] = $this->_abs_int( $this->flex_height );
        }

    }
}else die;