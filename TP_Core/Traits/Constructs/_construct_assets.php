<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_assets{
        public $tp_compress_css;
        public $tp_compress_scripts;
        public $tp_concatenate_scripts;
        public $tp_editor_styles;
        public $tp_scripts;
        public $tp_styles;
        public $tp_stylesheet;
        private function _construct_assets():void{
            $this->tp_compress_css;
            $this->tp_compress_scripts;
            $this->tp_concatenate_scripts;
            $this->tp_editor_styles = [];
            $this->tp_scripts;
            $this->tp_styles;
            $this->tp_stylesheet;
        }
    }
}else die;