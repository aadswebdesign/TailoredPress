<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 14:38
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie\CookieJar;
//use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\GuzzleException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\InvalidArgumentException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\UriInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Client\ClientInterface as CI;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
if(ABSPATH){
    class Client implements ClientInterface, CI{
        use ClientTrait;
        private $__config;
        public function __construct(array $config = []){
            if (!isset($config['handler']))
                $config['handler'] = HandlerStack::create();
            elseif (!\is_callable($config['handler']))
                throw new InvalidArgumentException('handler must be a callable');
            if (isset($config['base_uri']))
                $config['base_uri'] = Psr7\Utils::uriFor($config['base_uri']);
            $this->__configureDefaults($config);
        }
        public function __call($method, $args){
            if (\count($args) < 1)
                throw new InvalidArgumentException('Magic request methods require a URI and optional options array');
            $uri = $args[0];
            $opts = $args[1] ?? [];
            return \substr($method, -5) === 'Async'
                ? $this->requestAsync(\substr($method, 0, -5), $uri, $opts)
                : $this->request($method, $uri, $opts);
        }
        public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface{
            $options = $this->__prepareDefaults($options);
            return $this->__transfer(
                $request->withUri($this->__buildUri($request->getUri(), $options), $request->hasHeader('Host')),
                $options
            );
        }
        public function send(RequestInterface $request, array $options = []): ResponseInterface{
            $options[RequestOptions::SYNCHRONOUS] = true;
            return $this->sendAsync($request, $options)->wait();
        }
        public function sendRequest(RequestInterface $request): ResponseInterface{
            $options[RequestOptions::SYNCHRONOUS] = true;
            $options[RequestOptions::ALLOW_REDIRECTS] = false;
            $options[RequestOptions::HTTP_ERRORS] = false;
            return $this->sendAsync($request, $options)->wait();
        }
        public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface{
            $options = $this->__prepareDefaults($options);
            $headers = $options['headers'] ?? [];
            $body = $options['body'] ?? null;
            $version = $options['version'] ?? '1.1';
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $uri = $this->__buildUri(Psr7\Utils::uriFor($uri), $options);
            if (\is_array($body)) throw $this->__invalidBody();
            $request = new Psr7\Request($method, $uri, $headers, $body, $version);
            unset($options['headers'], $options['body'], $options['version']);
            return $this->__transfer($request, $options);
        }
        public function request(string $method, $uri = '', array $options = []): ResponseInterface{
            $options[RequestOptions::SYNCHRONOUS] = true;
            return $this->requestAsync($method, $uri, $options)->wait();
        }
        private function __buildUri(UriInterface $uri, array $config): UriInterface{
            if (isset($config['base_uri']))
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $uri = Psr7\UriResolver::resolve(Psr7\Utils::uriFor($config['base_uri']), $uri);
            if (isset($config['idn_conversion']) && ($config['idn_conversion'] !== false)) {
                $idnOptions = ($config['idn_conversion'] === true) ? \IDNA_DEFAULT : $config['idn_conversion'];
                $uri = Utils::idnUriConvert($uri, $idnOptions);
            }
            return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
        }
        private function __configureDefaults(array $config): void{
            $defaults = [
                'allow_redirects' => RedirectMiddleware::$defaultSettings,
                'http_errors'     => true,
                'decode_content'  => true,
                'verify'          => true,
                'cookies'         => false,
                'idn_conversion'  => false,
            ];
            if (\PHP_SAPI === 'cli' && ($proxy = Utils::getenv('HTTP_PROXY')))
                $defaults['proxy']['http'] = $proxy;
            if ($proxy = Utils::getenv('HTTPS_PROXY')) $defaults['proxy']['https'] = $proxy;
            if ($noProxy = Utils::getenv('NO_PROXY')) {
                $cleanedNoProxy = \str_replace(' ', '', $noProxy);
                $defaults['proxy']['no'] = \explode(',', $cleanedNoProxy);
            }
            $this->__config = $config + $defaults;
            if (!empty($config['cookies']) && $config['cookies'] === true)
                $this->__config['cookies'] = new CookieJar();
            if (!isset($this->__config['headers']))
                $this->__config['headers'] = ['User-Agent' => Utils::defaultUserAgent()];
            else {
                foreach (\array_keys($this->__config['headers']) as $name) {
                    if (\strtolower($name) === 'user-agent') return;
                }
                $this->__config['headers']['User-Agent'] = Utils::defaultUserAgent();
            }
            return null;
        }
        private function __prepareDefaults(array $options): array{
            $defaults = $this->__config;
            if (!empty($defaults['headers'])) {
                $defaults['_conditional'] = $defaults['headers'];
                unset($defaults['headers']);
            }
            if (\array_key_exists('headers', $options)) {
                if ($options['headers'] === null) {
                    $defaults['_conditional'] = [];
                    unset($options['headers']);
                } elseif (!\is_array($options['headers']))
                    throw new InvalidArgumentException('headers must be an array');
            }
            $result = $options + $defaults;
            foreach ($result as $k => $v) {
                if ($v === null) unset($result[$k]);
            }
            return $result;
        }
        private function __transfer(RequestInterface $request, array $options): PromiseInterface{
            $request = $this->__applyOptions($request, $options);
            $handler = $options['handler'];
            try {
                return P\Create::promiseFor($handler($request, $options));
            } catch (\Exception $e) {
                return P\Create::rejectionFor($e);
            }
        }
        private function __applyOptions(RequestInterface $request, array &$options): RequestInterface{
            $modify = ['set_headers' => [],];
            if (isset($options['headers'])) {
                if (array_keys($options['headers']) === range(0, count($options['headers']) - 1)) {
                    throw new InvalidArgumentException('The headers array must have header name as keys.');
                }
                $modify['set_headers'] = $options['headers'];
                unset($options['headers']);
            }
            if (isset($options['form_params'])) {
                if (isset($options['multipart'])) {
                    throw new InvalidArgumentException('You cannot use '
                        . 'form_params and multipart at the same time. Use the '
                        . 'form_params option if you want to send application/'
                        . 'x-www-form-urlencoded requests, and the multipart '
                        . 'option to send multipart/form-data requests.');
                }
                $options['body'] = \http_build_query($options['form_params'], '', '&');
                unset($options['form_params']);
                $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
                $options['_conditional']['Content-Type'] = 'application/x-www-form-urlencoded';
            }
            if (isset($options['multipart'])) {
                $options['body'] = new Psr7\MultipartStream($options['multipart']);
                unset($options['multipart']);
            }
            if (isset($options['json'])) {
                $options['body'] = Utils::jsonEncode($options['json']);
                unset($options['json']);
                $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
                $options['_conditional']['Content-Type'] = 'application/json';
            }
            if (!empty($options['decode_content']) && $options['decode_content'] !== true) {
                $options['_conditional'] = Psr7\Utils::caselessRemove(['Accept-Encoding'], $options['_conditional']);
                $modify['set_headers']['Accept-Encoding'] = $options['decode_content'];
            }
            if (isset($options['body'])) {
                if (\is_array($options['body'])) throw $this->__invalidBody();
                $modify['body'] = Psr7\Utils::streamFor($options['body']);
                unset($options['body']);
            }
            if (!empty($options['auth']) && \is_array($options['auth'])) {
                $value = $options['auth'];
                $type = isset($value[2]) ? \strtolower($value[2]) : 'basic';
                switch ($type) {
                    case 'basic':
                        // Ensure that we don't have the header in different case and set the new value.
                        $modify['set_headers'] = Psr7\Utils::caselessRemove(['Authorization'], $modify['set_headers']);
                        $modify['set_headers']['Authorization'] = 'Basic '
                            . \base64_encode("$value[0]:$value[1]");
                        break;
                    case 'digest':
                        // @todo: Do not rely on curl
                        $options['curl'][\CURLOPT_HTTPAUTH] = \CURLAUTH_DIGEST;
                        $options['curl'][\CURLOPT_USERPWD] = "$value[0]:$value[1]";
                        break;
                    case 'ntlm':
                        $options['curl'][\CURLOPT_HTTPAUTH] = \CURLAUTH_NTLM;
                        $options['curl'][\CURLOPT_USERPWD] = "$value[0]:$value[1]";
                        break;
                }
            }
            if (isset($options['query'])) {
                $value = $options['query'];
                if (\is_array($value))
                    $value = \http_build_query($value, '', '&', \PHP_QUERY_RFC3986);
                if (!\is_string($value))
                    throw new InvalidArgumentException('query must be a string or array');
                $modify['query'] = $value;
                unset($options['query']);
            }
            if (isset($options['sink'])&& \is_bool($options['sink'])) {
                throw new InvalidArgumentException('sink must not be a boolean');
            }
            /** @noinspection CallableParameterUseCaseInTypeContextInspection *///todo
            $request = Psr7\Utils::modifyRequest($request, $modify);
            if ($request->getBody() instanceof Psr7\MultipartStream) {
                $_request_body = $request->getBody();
                $request_body = null;
                if($_request_body instanceof Psr7\MultipartStream){
                    $request_body = $_request_body;
                }
                $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
                $options['_conditional']['Content-Type'] = 'multipart/form-data; boundary='
                    . $request_body->getBoundary();
            }
            if (isset($options['_conditional'])) {
                $modify = [];
                foreach ($options['_conditional'] as $k => $v) {
                    if (!$request->hasHeader($k))
                        $modify['set_headers'][$k] = $v;
                }
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $request = Psr7\Utils::modifyRequest($request, $modify);
                unset($options['_conditional']);
            }
            return $request;
        }
        private function __invalidBody(): InvalidArgumentException{
            return new InvalidArgumentException('Passing in the "body" request '
                . 'option as an array to send a request is not supported. '
                . 'Please use the "form_params" request option to send a '
                . 'application/x-www-form-urlencoded request, or the "multipart" '
                . 'request option to send a multipart/form-data request.');
        }
    }
}else die;