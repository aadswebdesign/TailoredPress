<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 16:44
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Object_Cache;
if(ABSPATH){
    trait _init_cache{
        protected $_tp_object_cache;
        protected function _init_object_cache():TP_Object_Cache{
            if(!($this->_tp_object_cache instanceof TP_Object_Cache))
                $this->_tp_object_cache = new TP_Object_Cache();
            return $this->_tp_object_cache;
        }
    }
}else die;