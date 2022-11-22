<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 19:31
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
use BadMethodCallException;
if(ABSPATH){
    trait RequiredParameterTrait{
        private function __checkRequiredParameter($name, array $params): void
        {
            if (!isset($params[$name]))
                throw new BadMethodCallException(sprintf('Required parameter not passed: "%s"',$name));
            return null;
        }
        private function __checkRequiredParameters(array $names, array $params): void
        {
            foreach ($names as $name) $this->__checkRequiredParameter($name, $params);
            return null;
        }
    }
}else die;