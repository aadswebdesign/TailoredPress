<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:35
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Token;
use InvalidArgumentException;
use RuntimeException;
if(ABSPATH){
    class AccessToken implements AccessTokenInterface, ResourceOwnerAccessTokenInterface{
        private static $__timeNow;
        protected $_accessToken;
        protected $_expires;
        protected $_refreshToken;
        protected $_resourceOwnerId;
        protected $_values = [];
        public static function setTimeNow($timeNow){
            self::$__timeNow = $timeNow;
        }//63
        public static function resetTimeNow(){
            self::$__timeNow = null;
        }//73
        public function getTimeNow(){
            return self::$__timeNow ?: time();
        }//81
        public function __construct(array $options = []){
            if (empty($options['access_token']))
                throw new InvalidArgumentException('Required option not passed: "access_token"');
            $this->_accessToken = $options['access_token'];
            if (!empty($options['resource_owner_id']))
                $this->_resourceOwnerId = $options['resource_owner_id'];
            if (!empty($options['refresh_token']))
                $this->_refreshToken = $options['refresh_token'];
            if (isset($options['expires_in'])) {
                if (!is_numeric($options['expires_in']))
                    throw new \InvalidArgumentException('expires_in value must be an integer');
                $this->_expires = $options['expires_in'] !== 0 ? $this->getTimeNow() + $options['expires_in'] : 0;
            } elseif (!empty($options['expires'])) {
                $expires = $options['expires'];
                if (!$this->_isExpirationTimestamp($expires)) $expires += $this->getTimeNow();
                $this->_expires = $expires;
            }
            $this->_values = array_diff_key($options, array_flip(['access_token','resource_owner_id','refresh_token','expires_in','expires',]));
        }//93
        protected function _isExpirationTimestamp($value){
            // If the given value is larger than the original OAuth 2 draft date,
            // assume that it is meant to be a (possible expired) timestamp.
            $oauth2InceptionDate = 1349067600; // 2012-10-01
            return ($value > $oauth2InceptionDate);
        }
        public function getToken(){
            return $this->_accessToken;
        }
        public function getRefreshToken(){
            return $this->_refreshToken;
        }
        public function getExpires(){
            return $this->_expires;
        }
        public function getResourceOwnerId(){
            return $this->_resourceOwnerId;
        }
        public function hasExpired(): string {
            $expires = $this->getExpires();
            if (empty($expires))
                throw new RuntimeException('"expires" is not set on the token');
            return $expires < time();
        }
        public function getValues(){
            return $this->_values;
        }
        public function __toString(){
            return (string) $this->getToken();
        }
        public function jsonSerialize(){
            $parameters = $this->_values;
            if ($this->_accessToken)
                $parameters['access_token'] = $this->_accessToken;
            if ($this->_refreshToken)
                $parameters['refresh_token'] = $this->_refreshToken;
            if ($this->_expires)
                $parameters['expires'] = $this->_expires;
            if ($this->_resourceOwnerId)
                $parameters['resource_owner_id'] = $this->_resourceOwnerId;
            return $parameters;
        }
    }
}else die;