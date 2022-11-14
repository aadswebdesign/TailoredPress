<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_theme{
        public $tp_themes_allowedtags;
        public $tp_paused_themes;
        public $tp_registered_theme_features;
        public $tp_theme;
        public $tp_theme_directories;
        public $tp_theme_features;
        public $tp_theme_field_defaults;
        public $tp_theme_roots;
        public $tp_themes;

        protected function _construct_theme():void{
            $this->tp_themes_allowedtags;
            $this->tp_paused_themes = [];
            $this->tp_registered_theme_features;
            $this->tp_theme_directories;
            $this->tp_theme_features;
            $this->tp_theme_field_defaults;
            $this->tp_theme_roots;

        }
    }
}else die;