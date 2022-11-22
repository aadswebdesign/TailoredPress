<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 05:44
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\ClientInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\BadResponseException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    trait ProviderRedirectTrait{
        protected $_redirectLimit = 2;
        protected function _followRequestRedirects(RequestInterface $request){
            $response = null;
            $attempts = 0;
            while ($attempts < $this->_redirectLimit) {
                $attempts++;
                $client = $this->getHttpClient();
                if($client instanceof ClientInterface){
                    $response = $client->send($request, [
                        'allow_redirects' => false
                    ]);
                }
                if ($this->_isRedirect($response)) {
                    $redirectUrl = new Uri($response->getHeader('Location')[0]);
                    $request = $request->withUri($redirectUrl);
                } else break;
            }
            return $response;
        }
        abstract public function getHttpClient();
        public function getRedirectLimit(): int
        {
            return $this->_redirectLimit;
        }
        protected function _isRedirect(ResponseInterface $response): bool
        {
            $statusCode = $response->getStatusCode();
            return $statusCode > 300 && $statusCode < 400 && $response->hasHeader('Location');
        }
        public function getResponse(RequestInterface $request){
            try {
                $response = $this->_followRequestRedirects($request);
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
            }
            return $response;
        }
        public function setRedirectLimit($limit): string
        {
            if (!is_int($limit)) throw new InvalidArgumentException('redirectLimit must be an integer.');
            if ($limit < 1) throw new InvalidArgumentException('redirectLimit must be greater than or equal to one.');
            $this->_redirectLimit = $limit;
            return $this;
        }
    }
}else die;