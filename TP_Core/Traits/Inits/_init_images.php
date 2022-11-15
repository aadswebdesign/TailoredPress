<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-5-2022
 * Time: 16:41
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Editor\TP_Image_Editor_GD;
use TP_Core\Libs\Editor\TP_Image_Editor_Imagick;
if(ABSPATH){
    trait _init_images{
        protected $_tp_editor_gd;
        protected $_tp_editor_imagick;
        protected function _init_editor_gd($file = null):TP_Image_Editor_GD{
            if(!($this->_tp_editor_gd instanceof TP_Image_Editor_GD))
                $this->_tp_editor_gd = new TP_Image_Editor_GD($file);
            return $this->_tp_editor_gd;
        }
        protected function _init_editor_imagick($file = null):TP_Image_Editor_Imagick{
            if(!($this->_tp_editor_imagick instanceof TP_Image_Editor_Imagick))
                $this->_tp_editor_imagick = new TP_Image_Editor_Imagick($file);
            return $this->_tp_editor_imagick;
        }
    }
}else die;

