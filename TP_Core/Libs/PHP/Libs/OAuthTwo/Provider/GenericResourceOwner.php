<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:38
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Provider;
if(ABSPATH){
    class GenericResourceOwner implements ResourceOwnerInterface{
        protected $_response;
        protected $_resourceOwnerId;
        public function __construct(array $response, $resourceOwnerId){
            $this->_response = $response;
            $this->_resourceOwnerId = $resourceOwnerId;
        }
        public function getId(){
            return $this->_response[$this->_resourceOwnerId];
        }
        public function toArray(): string {
            return $this->_response;
        }
    }
}else die;