<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:31
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Provider\Exception;
if(ABSPATH){
    class IdentityProviderException extends \Exception{
        protected $_response;
        public function __construct($message, $code, $response){
            $this->_response = $response;
            parent::__construct($message, $code);
        }
        public function getResponseBody(){
            return $this->_response;
        }
    }
}else die;