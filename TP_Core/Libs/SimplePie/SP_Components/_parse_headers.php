<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
if(ABSPATH){
    trait _parse_headers{
        public function prepareHeaders($headers, $count = 1){
            $data = explode("\r\n\r\n", $headers, $count);
            $data = array_pop($data);
            if (false !== stripos($data, "HTTP/1.0 200 Connection established\r\n")) {
                $exploded = explode("\r\n\r\n", $data, 2);
                $data = end($exploded);
            }
            if (false !== stripos($data, "HTTP/1.1 200 Connection established\r\n")) {
                $exploded = explode("\r\n\r\n", $data, 2);
                $data = end($exploded);
            }
            return $data;
        }
    }
}else die;