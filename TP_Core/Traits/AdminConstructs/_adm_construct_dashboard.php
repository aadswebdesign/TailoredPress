<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Core\Traits\AdminConstructs;
if(ABSPATH){
    trait _adm_construct_dashboard{
        public $tp_dashboard;
        public $tp_dashboard_control_callbacks;
        public $tp_registered_modules;
        public $tp_registered_modules_controls;
        protected function _adm_construct_dashboard():void{
            $this->tp_dashboard;
            $this->tp_dashboard_control_callbacks = [];
            $this->tp_registered_modules;
            $this->tp_registered_modules_controls;
        }
    }
}else{die;}

