<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 21:48
 */
namespace TP_Core\Libs\Block;
if(ABSPATH){
    interface _block_interface{
        public function register($name, $properties);
        public function unregister( $name, $properties );
        public function registered( $name, $properties );
        public function get_all_registered();
        public function is_registered( $name, $properties );
        public static function get_instance();
    }
}else die;

