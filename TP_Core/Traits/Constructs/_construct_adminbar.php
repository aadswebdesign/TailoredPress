<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 04:01
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_adminbar{
        public $tp_admin_bar;
        public $tp_show_admin_bar;
        protected function _construct_adminbar():void{
            $this->tp_admin_bar;
            $this->tp_show_admin_bar;
        }
    }
}else die;
