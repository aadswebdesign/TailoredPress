<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:22
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    interface ClientInterface{
        public const MAJOR_VERSION = 7;
        public function send(RequestInterface $request, array $options = []): ResponseInterface;
        public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface;
        public function request(string $method, $uri, array $options = []): ResponseInterface;
        public function requestAsync(string $method, $uri, array $options = []): PromiseInterface;
    }
}else die;