<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_rest{
        public $tp_rest_additional_fields;
        public $tp_rest_auth_cookie;
        public $tp_rest_server;
        private $__tp_rest_application_password_status, $__tp_rest_application_password_uuid;//todo
        protected function _construct_rest():void{
            $this->tp_rest_additional_fields;
            $this->tp_rest_auth_cookie;
            $this->tp_rest_server;
        }
    }
}else die;