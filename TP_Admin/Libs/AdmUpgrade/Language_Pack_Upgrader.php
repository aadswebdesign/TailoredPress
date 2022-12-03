<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 05:28
 */
namespace TP_Admin\Libs\AdmUpgrade;
if(ABSPATH){
    class Language_Pack_Upgrader extends TP_Upgrader {
        public $result;
        public $bulk = true;
        public static function async_upgrade( $upgrader = false ):void{}//49
        public function upgrade_strings():void{}//112
        public function upgrade( $update = false,array $args):void{}//135
        public function bulk_upgrade(array $language_updates,array $args):void{}//167
        public function check_package( $source, $remote_source ):void{}//325
        public function get_name_for_update( $update ):void{}//370
        public function clear_destination( $remote_destination ):bool{}//402
    }
}else{die;}