<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-5-2022
 * Time: 17:37
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Rewrite;
if(ABSPATH){
    trait _init_rewrite{
        protected $_tp_rewrite;
        protected function _init_rewrite():TP_Rewrite{
            if(!($this->_tp_rewrite instanceof TP_Rewrite))
                $this->_tp_rewrite = new TP_Rewrite();
            return $this->_tp_rewrite;
        }
    }
}else die;
