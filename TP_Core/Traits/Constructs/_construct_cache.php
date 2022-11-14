<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 16:44
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_cache{
        public $tp_cache_expiration;
        public $tp_suspend_cache_invalidation;
        public $tp_using_ext_object_cache;
        protected function _construct_cache():void{
            $this->tp_cache_expiration;
            $this->tp_suspend_cache_invalidation;
            $this->tp_using_ext_object_cache;
        }
    }
}else die;