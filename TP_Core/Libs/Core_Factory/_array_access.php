<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-3-2022
 * Time: 20:28
 */
namespace TP_Core\Libs\Core_Factory;
if(ABSPATH){
    trait _array_access {
        public function offsetSet( $offset, $value ): void{}
        public function offsetUnset( $offset ): void {}
        public function offsetExists( $offset ): void{}
        //public function offsetGet( $offset ): void{}
    }
}else die;