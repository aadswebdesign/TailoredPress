<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_recovery{
        public $tp_recovery_mode;
        protected function _construct_recovery():void{
            $this->tp_recovery_mode;
        }
    }
}else die;