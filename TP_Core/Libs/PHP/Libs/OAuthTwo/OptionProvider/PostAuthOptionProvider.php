<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:23
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Provider\AbstractProvider;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\QueryBuilderTrait;
if(ABSPATH){
    class PostAuthOptionProvider implements OptionProviderInterface{
        use QueryBuilderTrait;
        public function getAccessTokenOptions($method, array $params){
            $options = ['headers' => ['content-type' => 'application/x-www-form-urlencoded']];
            if ($method === AbstractProvider::METHOD_POST)
                $options['body'] = $this->_getAccessTokenBody($params);
            return $options;
        }
        protected function _getAccessTokenBody(array $params){
            return $this->buildQueryString($params);
        }
    }
}else die;