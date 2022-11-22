<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 22:01
 */

namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface ServerRequestFactoryInterface{
        public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface;
    }
}else die;