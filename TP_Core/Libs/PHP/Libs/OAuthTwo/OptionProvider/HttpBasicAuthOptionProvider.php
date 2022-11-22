<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:20
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider;
use InvalidArgumentException;
if(ABSPATH){
    class HttpBasicAuthOptionProvider extends PostAuthOptionProvider{
        public function getAccessTokenOptions($method, array $params){
            if (empty($params['client_id']) || empty($params['client_secret']))
                throw new InvalidArgumentException('clientId and clientSecret are required for http basic auth');
            $encodedCredentials = base64_encode(sprintf('%s:%s', $params['client_id'], $params['client_secret']));
            unset($params['client_id'], $params['client_secret']);
            $options = parent::getAccessTokenOptions($method, $params);
            $options['headers']['Authorization'] = 'Basic ' . $encodedCredentials;
            return $options;
        }
    }
}else die;

