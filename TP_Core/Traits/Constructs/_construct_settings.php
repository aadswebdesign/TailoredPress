<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-5-2022
 * Time: 02:45
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_settings{
        public
            $tp_updated_user_settings,
            $tp_new_allowed_options,
            $tp_registered_settings,
            $tp_new_whitelist_options;

        protected function _construct_settings():void{
            $this->tp_updated_user_settings;
            $this->tp_new_allowed_options;
            $this->tp_registered_settings;
            $this->tp_new_whitelist_options;
        }
    }
}else die;


