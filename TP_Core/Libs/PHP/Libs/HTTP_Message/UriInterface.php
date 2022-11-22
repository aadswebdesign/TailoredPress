<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 18:53
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface UriInterface{
        public function getScheme();
        public function getAuthority();
        public function getUserInfo();
        public function getHost();
        public function getPort();
        public function getPath();
        public function getQuery();
        public function getFragment();
        public function withScheme($scheme);
        public function withUserInfo($user, $password = null);
        public function withHost($host);
        public function withPort($port);
        public function withPath($path);
        public function withQuery($query);
        public function withFragment($fragment);
        public function __toString();
    }
}else die;