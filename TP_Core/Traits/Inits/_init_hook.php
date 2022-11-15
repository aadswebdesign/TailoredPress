<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 14:22
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Hook;
if(ABSPATH){
    trait _init_hook{
        protected $_tp_hook;
        //protected $_;
        //protected $_;
        //protected $_;
        protected function _init_hook():TP_Hook{
            if(!($this->_tp_hook instanceof TP_Hook)){
                $this->_tp_hook = new TP_Hook();
            }
            return $this->_tp_hook;
        }
    }
}else{die;}