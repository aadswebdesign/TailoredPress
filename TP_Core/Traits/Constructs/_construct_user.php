<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-4-2022
 * Time: 19:41
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_user{
        public $tp_user, $tp_user_data, $tp_user_level, $tp_user_ID, $tp_user_email;
        public $tp_user_login, $tp_user_url, $tp_user_identity, $tp_user_search;
        public $tp_current_user;
        public $tp_manager;
        public $tp_role;
        protected function _construct_user():void{
            $this->tp_user_login;
            $this->tp_user_data;
            $this->tp_user_level;
            $this->tp_user_ID;
            $this->tp_user_email;
            $this->tp_user_url;
            $this->tp_user_identity;
            $this->tp_manager;
        }
    }
}else die;