<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 14:22
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Admin_Bar;

if(ABSPATH){
    trait _init_adminbar{
        protected $_tp_adminbar;
        protected function _init_adminbar():TP_Admin_Bar{
            if(!($this->_tp_adminbar instanceof TP_Admin_Bar)){
                $this->_tp_adminbar = new TP_Admin_Bar();
            }
            return $this->_tp_adminbar;
        }
    }
}else{die;}