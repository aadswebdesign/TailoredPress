<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 19:05
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface ServerRequestInterface extends RequestInterface{
        public function getServerParams();
        public function getCookieParams();
        public function withCookieParams(array $cookies);
        public function getQueryParams();
        public function withQueryParams(array $query);
        public function getUploadedFiles();
        public function withUploadedFiles(array $uploadedFiles);
        public function getParsedBody();
        public function withParsedBody($data);
        public function getAttributes();
        public function getAttribute($name, $default = null);
        public function withAttribute($name, $value);
        public function withoutAttribute($name);
    }
}else die;