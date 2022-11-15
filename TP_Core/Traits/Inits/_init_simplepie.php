<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 21:17
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Cache;
if(ABSPATH){
    trait _init_simplepie{
        protected $_tp_simplepie;
        protected $_tp_simplepie_cache;
        protected function _init_simplepie():SimplePie{
            if(!($this->_tp_simplepie instanceof SimplePie))
                $this->_tp_simplepie = new SimplePie();
            return $this->_tp_simplepie;
        }
        protected function _init_simplepieCache():SimplePie_Cache{
            if(!($this->_tp_simplepie_cache instanceof SimplePie_Cache))
                $this->_tp_simplepie_cache = new SimplePie_Cache();
            return $this->_tp_simplepie_cache;
        }
    }
}else die;