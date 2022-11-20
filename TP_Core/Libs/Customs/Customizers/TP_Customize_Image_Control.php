<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
if(ABSPATH){
    class TP_Customize_Image_Control extends TP_Customize_Upload_Control{
        public $type = 'image';
        public $mime_type = 'image';
        public function prepare_control():void {}//38
    }
}else die;