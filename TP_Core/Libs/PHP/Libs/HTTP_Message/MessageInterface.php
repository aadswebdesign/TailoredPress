<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 18:39
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface MessageInterface{
        public function getProtocolVersion();
        public function withProtocolVersion($version);
        public function getHeaders();
        public function hasHeader($name);
        public function getHeader($name);
        public function getHeaderLine($name);
        public function withHeader($name, $value);
        public function withAddedHeader($name, $value);
        public function withoutHeader($name);
        public function getBody();
        public function withBody(StreamInterface $body);
    }
}else die;