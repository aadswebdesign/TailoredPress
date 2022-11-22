<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 13:25
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
if(ABSPATH){
    interface MessageFormatterInterface{
        public function format(RequestInterface $request, ?ResponseInterface $response = null, ?\Throwable $error = null): string;
    }
}else die;