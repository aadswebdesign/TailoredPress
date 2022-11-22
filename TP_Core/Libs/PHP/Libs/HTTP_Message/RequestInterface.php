<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 18:48
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface RequestInterface extends MessageInterface{
        public function getRequestTarget();
        public function withRequestTarget($requestTarget);
        public function getMethod();
        public function withMethod($method);
        public function getUri();
        public function withUri(UriInterface $uri, $preserveHost = false);
    }
}else die;