<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminConstructs;
if(ABSPATH){
    trait _adm_construct_admins{
        public $tp_adm_css_colors;
        public $tp_adm_page_hooks;
        public $tp_adm_settings_errors;
        public $tp_settings_fields;
        public $tp_settings_sections;
        public $tp_file_descriptions;
        public $tp_file_system;
        public $tp_allowed_files;
        protected function _adm_construct_admins():void{
            $this->tp_adm_css_colors;
            $this->tp_adm_page_hooks;
            $this->tp_adm_settings_errors;
            $this->tp_settings_fields;
            $this->tp_settings_sections;
            $this->tp_file_descriptions;
            $this->tp_file_system;
            $this->tp_allowed_files;
        }
    }
}else{die;}

