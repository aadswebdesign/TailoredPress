<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 15:18
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    interface Requests_Transport{
        public function request($url, $headers = array(), $data = array(), $options = array());
        public function request_multiple($requests, $options);
        public static function test();
    }
}else die;