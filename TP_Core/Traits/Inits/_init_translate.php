<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 19:21
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\PoMo\TP_Translations;
use TP_Core\Libs\PoMo\NOOP_Translations;
use TP_Core\Libs\PoMo\MO;
if(ABSPATH){
    trait _init_translate{
        protected $_tp_translate;
        protected $_noop_translations;
        protected $_mo;
        protected function _init_translate():TP_Translations{
            if(!($this->_tp_translate instanceof TP_Translations)){
                $this->_tp_translate = new TP_Translations();
            }
            return $this->_tp_translate;
        }
        protected function _init_noop_translations():NOOP_Translations{
            if(!($this->_noop_translations instanceof NOOP_Translations)){
                $this->_noop_translations = new NOOP_Translations();
            }
            return $this->_noop_translations;
        }
        protected function _init_mo():MO{
            if(!($this->_mo instanceof MO)){
                $this->_mo = new MO();
            }
            return $this->_mo;
        }
    }
}else die;

