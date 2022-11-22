<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 19:38
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\RequiredParameterTrait;
if(ABSPATH){
    abstract class AbstractGrant{
        use RequiredParameterTrait;
        abstract protected function _getName();
        abstract protected function _getRequiredRequestParameters();
        public function __toString(){
            return $this->_getName();
        }
        public function prepareRequestParameters(array $defaults, array $options){
            $defaults['grant_type'] = $this->_getName();
            $required = $this->_getRequiredRequestParameters();
            $provided = array_merge($defaults, $options);
            $this->__checkRequiredParameters($required, $provided);
            return $provided;
        }
    }
}else die;