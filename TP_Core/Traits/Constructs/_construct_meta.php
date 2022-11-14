<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-5-2022
 * Time: 12:34
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_meta{
        public $tp_meta_keys;
        public $tp_meta_boxes;
        protected function _construct_meta():void{
            $this->tp_meta_keys = [];
            $this->tp_meta_boxes = [];
        }
    }
}else die;