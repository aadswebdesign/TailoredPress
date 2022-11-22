<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 14:45
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
//use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\GuzzleException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
//use TP_Managers\PHP_Manager\Libs\Http_Message\UriInterface;
if(ABSPATH){
    trait ClientTrait{
        abstract public function request(string $method, $uri, array $options = []): ResponseInterface;
        abstract public function requestAsync(string $method, $uri, array $options = []): PromiseInterface;
        public function get($uri, array $options = []): ResponseInterface{
            return $this->request('GET', $uri, $options);
        }
        public function head($uri, array $options = []): ResponseInterface{
            return $this->request('HEAD', $uri, $options);
        }
        public function put($uri, array $options = []): ResponseInterface{
            return $this->request('PUT', $uri, $options);
        }
        public function post($uri, array $options = []): ResponseInterface{
            return $this->request('POST', $uri, $options);
        }
        public function patch($uri, array $options = []): ResponseInterface{
            return $this->request('PATCH', $uri, $options);
        }
        public function delete($uri, array $options = []): ResponseInterface{
            return $this->request('DELETE', $uri, $options);
        }
        public function getAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('GET', $uri, $options);
        }
        public function headAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('HEAD', $uri, $options);
        }
        public function putAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('PUT', $uri, $options);
        }
        public function postAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('POST', $uri, $options);
        }
        public function patchAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('PATCH', $uri, $options);
        }
        public function deleteAsync($uri, array $options = []): PromiseInterface{
            return $this->requestAsync('DELETE', $uri, $options);
        }
    }
}else die;