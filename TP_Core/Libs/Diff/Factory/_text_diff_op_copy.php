<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 07:57
 */
namespace TP_Core\Libs\Diff\Factory;
if(ABSPATH){
    class _text_diff_op_copy extends _text_diff_op{
        /**
         * _text_diff_op_copy constructor.
         * @param $original
         * @param mixed $final
         */
        public function __construct($original, $final = false){
            if (!is_array($final)) $final = $original;
            $this->_original = $original;
            $this->_final = $final;
        }
        public function &reverse():bool{
            $reverse = new _text_diff_op_copy($this->_final, $this->_original);
            return (bool)$reverse;
        }
    }
}else die;