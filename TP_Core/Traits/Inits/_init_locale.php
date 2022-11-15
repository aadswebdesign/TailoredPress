<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 14:06
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Locale;
use TP_Core\Libs\TP_Locale_Switcher;
if(ABSPATH){
    trait _init_locale{
        protected $_tp_locale;
        protected $_tp_locale_switcher;
        protected function _init_locale():TP_Locale{
            if(!($this->_tp_locale instanceof TP_Locale))
            $this->_tp_locale = new TP_Locale();
            return $this->_tp_locale;
        }
        protected function _init_locale_switcher():TP_Locale_Switcher{
            if(!($this->_tp_locale_switcher instanceof TP_Locale_Switcher))
                $this->_tp_locale_switcher = new TP_Locale_Switcher();
            return $this->_tp_locale_switcher;
        }
    }
}else die;