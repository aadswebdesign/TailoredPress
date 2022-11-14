<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_time{
        public $tp_time_start;
        public $tp_time_end;
        protected function _construct_time():void{
            $this->tp_time_start;
            $this->tp_time_end;
        }
    }
}else die;