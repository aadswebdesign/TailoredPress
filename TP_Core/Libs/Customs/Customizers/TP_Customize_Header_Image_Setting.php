<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Setting;

if(ABSPATH){
    class TP_Customize_Header_Image_Setting extends TP_Customize_Setting {
        public $id = 'header_image_data';
        public function update( $value ):void {}//29
    }
}else die;