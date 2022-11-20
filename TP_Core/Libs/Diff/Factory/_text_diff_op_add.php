<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 08:00
 */
namespace TP_Core\Libs\Diff\Factory;
if(ABSPATH){
    class _text_diff_op_add extends _text_diff_op{
        public function __construct( $lines ){
            $this->_final = $lines;
            $this->_original = false;
        }
        public function &reverse():bool{
            $reverse = new _text_diff_op_add($this->_final);
            return (bool)$reverse;
        }
    }
}else die;