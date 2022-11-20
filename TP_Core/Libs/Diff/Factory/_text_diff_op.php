<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 15:30
 */
namespace TP_Core\Libs\Diff\Factory;
if(ABSPATH){
    class _text_diff_op{
        protected $_original;
        protected $_final;

        public function &reverse():bool{
           return trigger_error('Abstract method', E_USER_ERROR);
        }
        protected function _new_original():int{
            return $this->_original ? count($this->_original) : 0;
        }
        protected function _new_final():int{
            return $this->_final ? count($this->_final) : 0;
        }
    }
}else die;

