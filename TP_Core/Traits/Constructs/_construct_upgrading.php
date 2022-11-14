<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_upgrading{
        public $tp_upgrading;
        protected function _construct_upgrading():void{
            $this->tp_upgrading;
        }
    }
}else die;