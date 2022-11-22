<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 19:00
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface ResponseInterface extends MessageInterface{
        public function getStatusCode();
        public function withStatus($code, $reasonPhrase = '');
        public function getReasonPhrase();
    }
}else die;