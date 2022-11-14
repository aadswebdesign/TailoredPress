<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-5-2022
 * Time: 16:41
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_editor{
        public $tp_additional_img_sizes = [];
        public $tp_content_width;
        public $tp_custom_background;
        public $tp_custom_image_header;
        public $tp_rich_edit;
        protected function _construct_editor():void{
            $this->tp_additional_img_sizes;
            $this->tp_content_width;
            $this->tp_custom_background;
            $this->tp_custom_image_header;
            $this->tp_rich_edit;
        }
    }
}else die;

