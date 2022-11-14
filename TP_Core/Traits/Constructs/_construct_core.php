<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_core{
        public $PHP_SELF;
        public $tp_allowed_options = [];
        public $tp_auth_secure_cookie;
        public $tp_bookmark;
        public $tp_core;
        public $tp_current_env;
        public $tp_new_allowed_options;
        public $tp_relative_file;
        public $tp_title;
        public $tp_update_title;
        public $tp_version;
        protected function _construct_core():void{
            $this->PHP_SELF;
            $this->tp_allowed_options;
            $this->tp_auth_secure_cookie;
            $this->tp_bookmark;
            $this->tp_current_env = '';
            $this->tp_core;
            $this->tp_new_allowed_options;
            $this->tp_relative_file;
            $this->tp_title;
            $this->tp_update_title;
            $this->tp_version = TP_VERSION;
        }
    }
}else die;