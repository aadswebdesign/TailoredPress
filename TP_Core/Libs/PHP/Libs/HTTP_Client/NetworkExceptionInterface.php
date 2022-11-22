<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:43
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Client;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    interface NetworkExceptionInterface extends ClientExceptionInterface{
        public function getRequest(): RequestInterface;
    }
}else die;