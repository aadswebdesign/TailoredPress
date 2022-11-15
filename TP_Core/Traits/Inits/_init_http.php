<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\HTTP\TP_Http;
if(ABSPATH){
    trait _init_http{
        protected $_tp_http;
        protected function _init_http():TP_Http{
            if(!($this->_tp_http instanceof TP_Http)){
                $this->_tp_http = new TP_Http();
            }
            return $this->_tp_http;
        }
    }
}else{die;}