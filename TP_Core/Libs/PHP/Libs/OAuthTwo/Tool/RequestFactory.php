<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 03:18
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Request;
if(ABSPATH){
    class RequestFactory{
        public function getRequest($method,$uri,array $headers = [],$body = null,$version = '1.1'): Request
        {
            return new Request($method, $uri, $headers, $body, $version);
        }
        protected function _parseOptions(array $options): array
        {
            $defaults = ['headers' => [],'body'=> null,'version' => '1.1',];
            return array_merge($defaults, $options);
        }
        public function getRequestWithOptions($method, $uri, array $options = []): Request
        {
            $options = $this->_parseOptions($options);
            return $this->getRequest($method,$uri,$options['headers'],$options['body'],$options['version']);
        }
    }
}else die;