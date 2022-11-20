<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 08:00
 */
namespace TP_Core\Libs\Diff\Factory;
if(ABSPATH){
    class _text_diff_op_delete extends _text_diff_op{
        public function __construct( $lines ){
            $this->_original = $lines;
            $this->_final = false;
        }
        public function &reverse():bool{
            $reverse = new _text_diff_op_delete($this->_original);
            return (bool)$reverse;
        }
    }
}else die;