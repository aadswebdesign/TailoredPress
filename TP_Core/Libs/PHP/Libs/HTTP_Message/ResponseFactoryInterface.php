<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 22:03
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface ResponseFactoryInterface{
        public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;
    }
}else die;