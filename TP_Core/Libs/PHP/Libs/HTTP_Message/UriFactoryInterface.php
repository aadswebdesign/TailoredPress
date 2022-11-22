<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 21:42
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface UriFactoryInterface{
        public function createUri(string $uri = ''): UriInterface;
    }
}else die;

