<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 19:21
 */
namespace TP_Core\Traits\Inits;
if(ABSPATH){
    trait _init_L10n{
        protected $_tp_L10n = [];
        protected $_tp_L10n_unloaded = [];
        /**
         * @param string $lang
         * @return array
         */
        protected function _init_L10n($lang = ''):array{
            if(!empty($lang))$this->_tp_L10n[$lang];
            return $this->_tp_L10n;
        }
    }
}else die;

