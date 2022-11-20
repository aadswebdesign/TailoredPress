<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 08:00
 */
namespace TP_Core\Libs\Diff\Factory;
if(ABSPATH){
    class _text_diff_op_change extends _text_diff_op{
        public function __construct( $orig, $final ){
            $this->_original = $orig;
            $this->_final = $final;
        }
        public function &reverse():bool{
            $reverse = new _text_diff_op_change($this->_final, $this->_original);
            return (bool)$reverse;
        }
    }
}else die;