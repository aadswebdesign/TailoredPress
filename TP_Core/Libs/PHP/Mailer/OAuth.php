<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 20:05
 */
namespace TP_Core\Libs\PHP\Mailer;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Grant\RefreshToken;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Provider\AbstractProvider;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessToken;
if(ABSPATH){
    class OAuth implements OAuthTokenProvider{
        protected $_provider;
        protected $_oauthToken;
        protected $_oauthUserEmail = '';
        protected $_oauthClientSecret = '';
        protected $_oauthClientId = '';
        protected $_oauthRefreshToken = '';
        public function __construct($options){
            $this->_provider = $options['provider'];
            $this->_oauthUserEmail = $options['userName'];
            $this->_oauthClientSecret = $options['clientSecret'];
            $this->_oauthClientId = $options['clientId'];
            $this->_oauthRefreshToken = $options['refreshToken'];
        }
        protected function _getGrant(): RefreshToken{
            return new RefreshToken();
        }
        protected function _getToken(){
            if($this->_provider instanceof AbstractProvider){}
            return $this->_provider->getAccessToken($this->_getGrant(),['refresh_token' => $this->_oauthRefreshToken]);
        }
        public function getOauth64():string{
            if (($this->_oauthToken instanceof AccessToken) && (null === $this->_oauthToken || $this->_oauthToken->hasExpired()))
                $this->_oauthToken = $this->_getToken();
            return base64_encode('user=' . $this->_oauthUserEmail ."\001auth=Bearer " .$this->_oauthToken ."\001\001");
        }
    }
}else die;