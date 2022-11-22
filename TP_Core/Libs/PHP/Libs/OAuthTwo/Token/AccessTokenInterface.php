<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:19
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Token;
use JsonSerializable;
//use ReturnTypeWillChange; //php 8.1
//use RuntimeException;
if(ABSPATH){
    interface AccessTokenInterface extends JsonSerializable{
        public function getToken();
        public function getRefreshToken();
        public function getExpires();
        public function hasExpired();
        public function getValues();
        public function __toString();
        public function jsonSerialize();
    }
}else die;