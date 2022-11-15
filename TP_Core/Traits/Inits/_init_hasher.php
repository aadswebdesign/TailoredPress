<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-5-2022
 * Time: 15:54
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_PasswordHash;
if(ABSPATH){
    trait _init_hasher{
        protected $_tp_hasher;
        protected function _init_hasher($iteration_count_log2 = null, $portable_hashes = null):TP_PasswordHash{
            if(!($this->_tp_hasher instanceof TP_PasswordHash))
                $this->_tp_hasher = new TP_PasswordHash($iteration_count_log2, $portable_hashes);
            return $this->_tp_hasher;
        }
    }
}else die;