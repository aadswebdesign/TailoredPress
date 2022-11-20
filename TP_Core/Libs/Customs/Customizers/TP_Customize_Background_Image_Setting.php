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
    class TP_Customize_Background_Image_Setting extends TP_Customize_Setting  {
        public $id = 'background_image_thumb';
        public function update():void {//not used  $value
            $this->_remove_theme_mod( 'background_image_thumb' );
        }
    }
}else die;