<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 02:57
 */

namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Grant\Exception\InvalidGrantException;
if(ABSPATH){
    class GrantFactory{
        protected $_registry = [];
        public function setGrant($name, AbstractGrant $grant){
            $this->_registry[$name] = $grant;
            return $this;
        }
        public function getGrant($name){
            if (empty($this->_registry[$name]))
                $this->_registerDefaultGrant($name);
            return $this->_registry[$name];
        }
        protected function _registerDefaultGrant($name){
            $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
            $class = 'League\\OAuth2\\Client\\Grant\\' . $class;
            $this->checkGrant($class);
            return $this->setGrant($name, new $class);
        }
        public function isGrant($class){
            return is_subclass_of($class, AbstractGrant::class);
        }
        public function checkGrant($class){
            if (!$this->isGrant($class)) {
                throw new InvalidGrantException(sprintf(
                    'Grant "%s" must extend AbstractGrant',
                    is_object($class) ? get_class($class) : $class
                ));
            }
        }
    }
}else die;