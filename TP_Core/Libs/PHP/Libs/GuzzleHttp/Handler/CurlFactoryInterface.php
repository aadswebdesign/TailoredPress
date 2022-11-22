<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-4-2022
 * Time: 19:26
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    interface CurlFactoryInterface{
        public function create(RequestInterface $request, array $options): EasyHandle;
        public function release(EasyHandle $easy): void;
    }
}else{die;}