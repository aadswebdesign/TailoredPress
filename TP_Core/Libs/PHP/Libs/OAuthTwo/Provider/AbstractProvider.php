<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:46
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Provider;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Client as HttpClient;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\ClientInterface as HttpClientInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\BadResponseException;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Grant\AbstractGrant;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Grant\GrantFactory;
use TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider\HttpBasicAuthOptionProvider;
use TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider\OptionProviderInterface;
use TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider\PostAuthOptionProvider;
//use TP_Core\Libs\PHP\Libs\OAuthTwo\Provider\Exception\IdentityProviderException;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessToken;
//use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessTokenInterface;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\ArrayAccessorTrait;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\GuardedPropertyTrait;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\QueryBuilderTrait;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Tool\RequestFactory;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use UnexpectedValueException;
if(ABSPATH){
    abstract class AbstractProvider{
        use ArrayAccessorTrait;
        use GuardedPropertyTrait;
        use QueryBuilderTrait;
        const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;
        const METHOD_GET = 'GET';
        const METHOD_POST = 'POST';
        protected $_clientId;
        protected $_clientSecret;
        protected $_redirectUri;
        protected $_state;
        protected $_grantFactory;
        protected $_requestFactory;
        protected $_httpClient;
        protected $_optionProvider;
        public function __construct(array $options = [], array $collaborators = []){
            $this->_fillProperties($options);
            if (empty($collaborators['_grantFactory']))
                $collaborators['_grantFactory'] = new GrantFactory();
            $this->setGrantFactory($collaborators['_grantFactory']);
            if (empty($collaborators['_requestFactory']))
                $collaborators['_requestFactory'] = new RequestFactory();
            $this->setRequestFactory($collaborators['requestFactory']);
            if (empty($collaborators['_httpClient'])) {
                $client_options = $this->_getAllowedClientOptions($options);
                $collaborators['_httpClient'] = new HttpClient(
                    array_intersect_key($options, array_flip($client_options))
                );
            }
            $this->setHttpClient($collaborators['httpClient']);
            if (empty($collaborators['optionProvider']))
                $collaborators['optionProvider'] = new PostAuthOptionProvider();
            $this->setOptionProvider($collaborators['optionProvider']);
        }
        protected function _getAllowedClientOptions(array $options){
            $client_options = ['timeout', 'proxy'];
            if (!empty($options['proxy'])) $client_options[] = 'verify';
            return $client_options;
        }
        public function setGrantFactory(GrantFactory $factory){
            $this->_grantFactory = $factory;
            return $this;
        }
        public function getGrantFactory(){
            return $this->_grantFactory;
        }
        public function setRequestFactory(RequestFactory $factory){
            $this->_requestFactory = $factory;
            return $this;
        }
        public function getRequestFactory(){
            return $this->_requestFactory;
        }
        public function setHttpClient(HttpClientInterface $client){
            $this->_httpClient = $client;
            return $this;
        }
        public function getHttpClient(){
            return $this->_httpClient;
        }
        public function setOptionProvider(OptionProviderInterface $provider){
            $this->_optionProvider = $provider;
            return $this;
        }
        public function getOptionProvider(){
            return $this->_optionProvider;
        }
        public function getState(){
            return $this->_state;
        }
        abstract public function getBaseAuthorizationUrl();
        abstract public function getBaseAccessTokenUrl(array $params);
        abstract public function getResourceOwnerDetailsUrl(AccessToken $token);
        protected function _getRandomState($length = 32){
            return bin2hex(random_bytes($length / 2));
        }
        abstract protected function _getDefaultScopes();
        protected function _getScopeSeparator(){
            return ',';
        }
        protected function _getAuthorizationParameters(array $options){
            if (empty($options['state']))
                $options['state'] = $this->_getRandomState();
            if (empty($options['scope']))
                $options['scope'] = $this->_getDefaultScopes();
            $options += ['response_type' => 'code','approval_prompt' => 'auto'];

            if (is_array($options['scope'])) {
                $separator = $this->_getScopeSeparator();
                $options['scope'] = implode($separator, $options['scope']);
            }
            $this->_state = $options['state'];
            if (!isset($options['redirect_uri']))
                $options['redirect_uri'] = $this->_redirectUri;
            $options['client_id'] = $this->_clientId;
            return $options;
        }
        protected function _getAuthorizationQuery(array $params){
            return $this->buildQueryString($params);
        }
        public function getAuthorizationUrl(array $options = []){
            $base   = $this->getBaseAuthorizationUrl();
            $params = $this->_getAuthorizationParameters($options);
            $query  = $this->_getAuthorizationQuery($params);
            return $this->_appendQuery($base, $query);
        }
        public function authorize(array $options = [],callable $redirectHandler = null) {
            $url = $this->getAuthorizationUrl($options);
            if ($redirectHandler)
                return $redirectHandler($url, $this);
            // @codeCoverageIgnoreStart
            header('Location: ' . $url);
            exit;
            // @codeCoverageIgnoreEnd
        }
        protected function _appendQuery($url, $query){
            $query = trim($query, '?&');
            if ($query) {
                $glue = strstr($url, '?') === false ? '?' : '&';
                return $url . $glue . $query;
            }
            return $url;
        }
        protected function getAccessTokenMethod(){
            return self::METHOD_POST;
        }
        protected function _getAccessTokenResourceOwnerId(){
            return static::ACCESS_TOKEN_RESOURCE_OWNER_ID;
        }
        protected function _getAccessTokenQuery(array $params){
            return $this->buildQueryString($params);
        }
        protected function _verifyGrant($grant){
            if($this->_grantFactory instanceof GrantFactory)
            if (is_string($grant)) return $this->_grantFactory->getGrant($grant);
            $this->_grantFactory->checkGrant($grant);
            return $grant;
        }
        protected function _getAccessTokenUrl(array $params){
            $url = $this->getBaseAccessTokenUrl($params);
            if ($this->getAccessTokenMethod() === self::METHOD_GET) {
                $query = $this->_getAccessTokenQuery($params);
                return $this->_appendQuery($url, $query);
            }
            return $url;
        }
        protected function getAccessTokenRequest(array $params){
            $method  = $this->getAccessTokenMethod();
            $url     = $this->_getAccessTokenUrl($params);
            if($this->_optionProvider instanceof HttpBasicAuthOptionProvider);
            $options = $this->_optionProvider->getAccessTokenOptions($this->getAccessTokenMethod(), $params);
            return $this->getRequest($method, $url, $options);
        }
        public function getAccessToken($grant, array $options = []){
            $grant = $this->_verifyGrant($grant);
            $params = [
                'client_id'     => $this->_clientId,
                'client_secret' => $this->_clientSecret,
                'redirect_uri'  => $this->_redirectUri,
            ];
            $params   = $grant->prepareRequestParameters($params, $options);
            $request  = $this->getAccessTokenRequest($params);
            $response = $this->getParsedResponse($request);
            if (false === is_array($response)) {
                throw new UnexpectedValueException(
                    'Invalid response received from Authorization Server. Expected JSON.'
                );
            }
            $prepared = $this->_prepareAccessTokenResponse($response);
            $token    = $this->_createAccessToken($prepared, $grant);
            return $token;
        }
        public function getRequest($method, $url, array $options = []){
            return $this->_createRequest($method, $url, null, $options);
        }
        public function getAuthenticatedRequest($method, $url, $token, array $options = []){
            return $this->_createRequest($method, $url, $token, $options);
        }
        protected function _createRequest($method, $url, $token, array $options){
            $defaults = ['headers' => $this->getHeaders($token),];
            $options = array_merge_recursive($defaults, $options);
            $factory = $this->getRequestFactory();
            if($factory instanceof RequestFactory);
            return $factory->getRequestWithOptions($method, $url, $options);
        }
        public function getResponse(RequestInterface $request){
            $client = $this->getHttpClient();
            if($client instanceof HttpClientInterface);
            return $client->send($request);
        }
        public function getParsedResponse(RequestInterface $request){
            try {
                $response = $this->getResponse($request);
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
            }
            $parsed = $this->_parseResponse($response);
            $this->_checkResponse($response, $parsed);
            return $parsed;
        }
        protected function _parseJson($content){
            $content = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE)
                throw new UnexpectedValueException(sprintf(
                    "Failed to parse JSON response: %s",
                    json_last_error_msg()
                ));
            return $content;
        }
        protected function _getContentType(ResponseInterface $response){
            return join(';', (array) $response->getHeader('content-type'));
        }
        protected function _parseResponse(ResponseInterface $response){
            $content = (string) $response->getBody();
            $type = $this->_getContentType($response);
            if (strpos($type, 'urlencoded') !== false) {
                parse_str($content, $parsed);
                return $parsed;
            }
            try {
                return $this->_parseJson($content);
            } catch (UnexpectedValueException $e) {
                if (strpos($type, 'json') !== false) throw $e;
                if ($response->getStatusCode() == 500)
                    throw new UnexpectedValueException(
                        'An OAuth server error was encountered that did not contain a JSON body',
                        0,
                        $e
                    );
                return $content;
            }
        }
        abstract protected function _checkResponse(ResponseInterface $response, $data);
        protected function _prepareAccessTokenResponse(array $result){
            if ($this->_getAccessTokenResourceOwnerId() !== null) {
                $result['resource_owner_id'] = $this->__getValueByKey(
                    $result,
                    $this->_getAccessTokenResourceOwnerId()
                );
            }
            return $result;
        }
        protected function _createAccessToken(array $response, AbstractGrant $grant){
            if(isset($grant))
            return new AccessToken($response);//todo
            return false;
        }
        abstract protected function _createResourceOwner(array $response, AccessToken $token);
        public function getResourceOwner(AccessToken $token){
            $response = $this->_fetchResourceOwnerDetails($token);
            return $this->_createResourceOwner($response, $token);
        }
        protected function _fetchResourceOwnerDetails(AccessToken $token){
            $url = $this->getResourceOwnerDetailsUrl($token);
            $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);
            $response = $this->getParsedResponse($request);
            if (false === is_array($response))
                throw new UnexpectedValueException(
                    'Invalid response received from Authorization Server. Expected JSON.'
                );
            return $response;
        }
        protected function _getDefaultHeaders(){
            return [];//todo
        }
        protected function _getAuthorizationHeaders($token = null){
            return [$token];//todo
        }
        public function getHeaders($token = null){
            if ($token)
                return array_merge($this->_getDefaultHeaders(),$this->_getAuthorizationHeaders($token));
            return $this->_getDefaultHeaders();
        }
    }
}else die;