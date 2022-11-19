<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-5-2022
 * Time: 20:14
 */
declare(strict_types=1);
namespace TP_Core\Libs\AssetsTools;
if(ABSPATH){
    interface DependenciesInterface{
        public function do_items(  $handles, $group = false );
        public function do_item( $handle, $group = false);
        public function all_deps( $handles, $recursion = false, $group = false );
        public function add($handle,...$args);
        public function add_data( $handle,...$args);
        public function get_data( $handle, $key );
        public function remove( $handles );
        public function enqueue( $handles );
        public function dequeue( $handles );
        public function query( $handle, $list = 'registered' );
        public function set_group( $handle, $recursion, $group );
    }
}else die;