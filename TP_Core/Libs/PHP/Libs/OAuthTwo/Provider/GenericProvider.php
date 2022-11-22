<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:42
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Provider;
use InvalidArgumentException;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Provider\Exception\IdentityProviderException;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessToken;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\BearerAuthorizationTrait;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    class GenericProvider extends AbstractProvider{
        use BearerAuthorizationTrait;
        private $__urlAuthorize;
        private $__urlAccessToken;
        private $__urlResourceOwnerDetails;
        private $__accessTokenMethod;
        private $__accessTokenResourceOwnerId;
        private $__scopes = null;
        private $__scopeSeparator;
        private $__responseError = 'error';
        private $__responseCode;
        private $__responseResourceOwnerId = 'id';
        public function __construct(array $options = [], array $collaborators = []){
            $this->__assertRequiredOptions($options);
            $possible   = $this->_getConfigurableOptions();
            $configured = array_intersect_key($options, array_flip($possible));
            foreach ($configured as $key => $value) $this->$key = $value;
            $options = array_diff_key($options, $configured);
            parent::__construct($options, $collaborators);
        }
        protected function _getConfigurableOptions(){
            return array_merge($this->_getRequiredOptions(), [
                'accessTokenMethod',
                'accessTokenResourceOwnerId',
                'scopeSeparator',
                'responseError',
                'responseCode',
                'responseResourceOwnerId',
                'scopes',
            ]);
        }
        protected function _getRequiredOptions(){
            return ['urlAuthorize','urlAccessToken','urlResourceOwnerDetails',];
        }
        private function __assertRequiredOptions(array $options){
            $missing = array_diff_key(array_flip($this->_getRequiredOptions()), $options);
            if (!empty($missing))
                throw new InvalidArgumentException(
                    'Required options not defined: ' . implode(', ', array_keys($missing))
                );
        }
        public function getBaseAuthorizationUrl(){
            return $this->__urlAuthorize;
        }
        public function getBaseAccessTokenUrl(array $params)        {
            return $this->__urlAccessToken;
        }
        public function getResourceOwnerDetailsUrl(AccessToken $token){
            return $this->__urlResourceOwnerDetails;
        }
        protected function _getDefaultScopes(){
            return $this->__scopes;
        }
        protected function _getAccessTokenMethod(){
            return $this->__accessTokenMethod ?: parent::getAccessTokenMethod();
        }
        protected function _getAccessTokenResourceOwnerId(){
            return $this->__accessTokenResourceOwnerId ?: parent::_getAccessTokenResourceOwnerId();
        }
        protected function _getScopeSeparator(){
            return $this->__scopeSeparator ?: parent::_getScopeSeparator();
        }
        protected function _checkResponse(ResponseInterface $response, $data){
            if (!empty($data[$this->__responseError])) {
                $error = $data[$this->__responseError];
                if (!is_string($error)) $error = var_export($error, true);
                $code  = $this->__responseCode && !empty($data[$this->__responseCode])? $data[$this->__responseCode] : 0;
                if (!is_int($code)) $code = intval($code);
                throw new IdentityProviderException($error, $code, $data);
            }
        }
        protected function _createResourceOwner(array $response, AccessToken $token){
            return new GenericResourceOwner($response, $this->__responseResourceOwnerId);
        }
    }
}else die;

