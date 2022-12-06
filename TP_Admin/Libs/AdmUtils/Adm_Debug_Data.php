<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-6-2022
 * Time: 22:28
 */
namespace TP_Admin\Libs\AdmUtils;
if(ABSPATH){
    class Adm_Debug_Data{
        public static function check_for_updates(){return '';}//16
        public static function debug_data(){return '';}//35
        public static function get_mysql_var( $var ){return '';}//1483
        public static function format( $info_array, $type ){return '';}//1507
        public static function get_database_size(){return '';}//1574
        public static function get_sizes(){return '';}//1596
    }
}else die;