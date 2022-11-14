<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_rewrite{
        public $tp_rewrite;
        protected function _construct_rewrite():void{
            $this->tp_rewrite;
        }
    }
}else die;