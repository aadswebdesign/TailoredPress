<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 03:22
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    interface CookieJarInterface extends \Countable, \IteratorAggregate{
        public function withCookieHeader(RequestInterface $request): RequestInterface;
        public function extractCookies(RequestInterface $request, ResponseInterface $response): void;
        public function setCookie(SetCookie $cookie): bool;
        public function clear(?string $domain = null, ?string $path = null, ?string $name = null): void;
        public function clearSessionCookies(): void;
        public function toArray(): array;
    }
}else{die;}