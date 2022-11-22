<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 13:52
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\BadResponseException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\TooManyRedirectsException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\UriInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
if(ABSPATH){
    class RedirectMiddleware{
        private $__nextHandler;
        public const HISTORY_HEADER = 'X-Guzzle-Redirect-History';
        public const STATUS_HISTORY_HEADER = 'X-Guzzle-Redirect-Status-History';
        public static $defaultSettings = [
            'max'             => 5,
            'protocols'       => ['http', 'https'],
            'strict'          => false,
            'referer'         => false,
            'track_redirects' => false,
        ];
        public function __construct(callable $nextHandler){
            $this->__nextHandler = $nextHandler;
        }
        public function __invoke(RequestInterface $request, array $options): PromiseInterface{
            $fn = $this->__nextHandler;
            if (empty($options['allow_redirects'])) return $fn($request, $options);
            if ($options['allow_redirects'] === true)
                $options['allow_redirects'] = self::$defaultSettings;
            elseif (!\is_array($options['allow_redirects']))
                throw new \InvalidArgumentException('allow_redirects must be true, false, or array');
            else $options['allow_redirects'] += self::$defaultSettings;
            if (empty($options['allow_redirects']['max'])) return $fn($request, $options);
            return $fn($request, $options)
                ->then(function (ResponseInterface $response) use ($request, $options) {
                    return $this->checkRedirect($request, $options, $response);
                });
        }
        public function checkRedirect(RequestInterface $request, array $options, ResponseInterface $response){
            $_request_uri = $request->getUri();
            $request_uri = null;
            if($_request_uri instanceof UriInterface){
                $request_uri = $_request_uri;
            }
            if (\strpos((string) $response->getStatusCode(), '3') !== !$response->hasHeader('Location' || 0)
            ) return $response;
            $this->__guardMax($request, $response, $options);
            $nextRequest = $this->modifyRequest($request, $options, $response);
            $next_request_uri = $nextRequest->getUri();
            if (($next_request_uri instanceof UriInterface) && $request_uri->getHost() !== defined('\CURLOPT_HTTPAUTH') && $next_request_uri->getHost())
                unset($options['curl'][\CURLOPT_HTTPAUTH],$options['curl'][\CURLOPT_USERPWD]);
            if (isset($options['allow_redirects']['on_redirect']))
                ($options['allow_redirects']['on_redirect'])($request,$response,$nextRequest->getUri());
            $promise = $this($nextRequest, $options);
            if (!empty($options['allow_redirects']['track_redirects']))
                return $this->__withTracking($promise,(string) $nextRequest->getUri(),$response->getStatusCode());
            return $promise;
        }
        public function modifyRequest(RequestInterface $request, array $options, ResponseInterface $response): RequestInterface{
            $modify = [];
            $_request_uri = $request->getUri();
            $request_uri = null;
            if($_request_uri instanceof UriInterface){
                $request_uri = $_request_uri;
            }
            $protocols = $options['allow_redirects']['protocols'];
            $statusCode = $response->getStatusCode();
            if ($statusCode === 303 || ($statusCode <= 302 && !$options['allow_redirects']['strict'])){
                $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
                $requestMethod = $request->getMethod();
                $modify['method'] = in_array($requestMethod, $safeMethods, true) ? $requestMethod : 'GET';
                $modify['body'] = '';
            }
            $uri = $this->__redirectUri($request, $response, $protocols);
            if (isset($options['idn_conversion']) && ($options['idn_conversion'] !== false)) {
                $idnOptions = ($options['idn_conversion'] === true) ? \IDNA_DEFAULT : $options['idn_conversion'];
                $uri = Utils::idnUriConvert($uri, $idnOptions);
            }
            $modify['uri'] = $uri;
            Psr7\Message::rewindBody($request);
            if ($options['allow_redirects']['referer']&& $modify['uri']->getScheme() === $request_uri->getScheme()){
                $uri = $request_uri->withUserInfo('');
                $modify['set_headers']['Referer'] = (string) $uri;
            } else $modify['remove_headers'][] = 'Referer';
            if ($request_uri->getHost() !== $modify['uri']->getHost())
                $modify['remove_headers'][] = 'Authorization';
            return Psr7\Utils::modifyRequest($request, $modify);
        }
        private function __withTracking(PromiseInterface $promise, string $uri, int $statusCode): PromiseInterface{
            return $promise->then(
                static function (ResponseInterface $response) use ($uri, $statusCode) {
                    $historyHeader = $response->getHeader(self::HISTORY_HEADER);
                    $statusHeader = $response->getHeader(self::STATUS_HISTORY_HEADER);
                    \array_unshift($historyHeader, $uri);
                    \array_unshift($statusHeader, (string) $statusCode);
                    $history_header = $response->withHeader(self::HISTORY_HEADER, $historyHeader);
                    $status_header = $response->withHeader(self::STATUS_HISTORY_HEADER, $statusHeader);
                    return $history_header . $status_header;
                }
            );
        }
        private function __guardMax(RequestInterface $request, ResponseInterface $response, array &$options): void{
            $current = $options['__redirect_count'] ?? 0;
            $options['__redirect_count'] = $current + 1;
            $max = $options['allow_redirects']['max'];
            if ($options['__redirect_count'] > $max)
                throw new TooManyRedirectsException("Will not follow more than {$max} redirects", $request, $response);
            return null;
        }
        private function __redirectUri(RequestInterface $request, ResponseInterface $response, array $protocols): UriInterface{
            $location = Psr7\UriResolver::resolve(
                $request->getUri(),
                new Psr7\Uri($response->getHeaderLine('Location'))
            );
            if (!\in_array($location->getScheme(), $protocols, true))
                throw new BadResponseException(\sprintf('Redirect URI, %s, does not use one of the allowed redirect protocols: %s', $location, \implode(', ', $protocols)), $request, $response);
            return $location;
        }
    }
}else die;