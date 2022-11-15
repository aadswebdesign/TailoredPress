<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 18:05
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Core;
if(ABSPATH){
    trait _init_core{
        protected $_tp_core;
        protected function _init_core():TP_Core{
           if(!($this->_tp_core instanceof TP_Core))
              $this->_tp_core = new TP_Core();
           return  $this->_tp_core;
        }
    }
}else die;