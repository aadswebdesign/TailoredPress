<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 22:06
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface RequestFactoryInterface{
        public function createRequest(string $method, $uri): RequestInterface;
    }
}else die;