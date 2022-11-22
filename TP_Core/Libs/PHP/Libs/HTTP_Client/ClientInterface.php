<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:45
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Client;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    interface ClientInterface{
        public function sendRequest(RequestInterface $request): ResponseInterface;
    }
}else die;
