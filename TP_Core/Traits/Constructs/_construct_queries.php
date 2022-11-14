<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_queries{
        public $tp_query,$tp_the_query;
        protected function _construct_queries():void{
            $this->tp_query;
            $this->tp_the_query;
        }
    }
}else die;