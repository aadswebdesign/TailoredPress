<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 12:31
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ServerRequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UploadedFileInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    class ServerRequest extends Request implements ServerRequestInterface{
        private $__attributes = [];
        private $__cookieParams = [];
        private $__parsedBody;
        private $__queryParams = [];
        private $__serverParams;
        private $__uploadedFiles = [];
        public function __construct(string $method, $uri, array $headers = [],$body = null, string $version = '1.1', array $serverParams = []){
            $this->__serverParams = $serverParams;
            parent::__construct($method, $uri, $headers, $body, $version);
        }
        public static function normalizeFiles(array $files): array{
            $normalized = [];
            foreach ($files as $key => $value) {
                if ($value instanceof UploadedFileInterface)
                    $normalized[$key] = $value;
                elseif (is_array($value) && isset($value['tmp_name']))
                    $normalized[$key] = self::__createUploadedFileFromSpec($value);
                elseif (is_array($value)) {
                    $normalized[$key] = self::normalizeFiles($value);
                    continue;
                } else throw new \InvalidArgumentException('Invalid value in files specification');
            }
            return $normalized;
        }
        public static function fromGlobals(): ServerRequestInterface{
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $headers = getallheaders();
            $uri = self::getUriFromGlobals();
            $_lazy_open_stream =  new LazyOpenStream('php://input', 'r+');
            $lazy_open_stream = null;
            if($_lazy_open_stream instanceof StreamInterface){
                $lazy_open_stream = $_lazy_open_stream;
            }
            $body = new CachingStream($lazy_open_stream);
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';
            $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $_SERVER);
            $request_params = $serverRequest->withCookieParams($_COOKIE)->withQueryParams($_GET);
            $request_params .= $serverRequest->withParsedBody($_POST)->withUploadedFiles(self::normalizeFiles($_FILES));
            $_returned_params = null;
            if($request_params instanceof ServerRequestInterface){
                $_returned_params = $request_params;
            }
            return $_returned_params;
        }
        public static function getUriFromGlobals(): UriInterface{
            $uri = new Uri('');
            $uri = $uri->withScheme(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
            $hasPort = false;
            $uri_interface = null;
            if($uri_interface instanceof UriInterface){
                $uri = $uri_interface;
            }
            if (isset($_SERVER['HTTP_HOST'])) {
                $host =  self::__extractHostAndPortFromAuthority($_SERVER['HTTP_HOST']);
                $port = $host;
                if ($host !== null) $uri = $uri->withHost($host);
                if ($port !== null) {
                    $hasPort = true;
                    $uri = $uri->withPort($port);
                }
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $uri = $uri->withHost($_SERVER['SERVER_NAME']);
            } elseif (isset($_SERVER['SERVER_ADDR'])) {
                $uri = $uri->withHost($_SERVER['SERVER_ADDR']);
            }
            if (!$hasPort && isset($_SERVER['SERVER_PORT'])) {
                $uri = $uri->withPort($_SERVER['SERVER_PORT']);
            }
            $hasQuery = false;
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestUriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
                $uri = $uri->withPath($requestUriParts[0]);
                if (isset($requestUriParts[1])) {
                    if($uri_interface instanceof UriInterface){
                        $uri = $uri_interface;
                    }
                    $hasQuery = true;
                    $uri = $uri->withQuery($requestUriParts[1]);
                }
            }
            if (!$hasQuery && isset($_SERVER['QUERY_STRING'])) {
                $uri = $uri->withQuery($_SERVER['QUERY_STRING']);
            }
            return $uri;
        }
        public function getServerParams(): array{
            return $this->__serverParams;
        }
        public function getUploadedFiles(): array{
            return $this->__uploadedFiles;
        }
        public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface{
            $new = clone $this;
            $new->__uploadedFiles = $uploadedFiles;
            return $new;
        }
        public function getCookieParams(): array{
            return $this->__cookieParams;
        }
        public function withCookieParams(array $cookies): ServerRequestInterface{
            $new = clone $this;
            $new->__cookieParams = $cookies;
            return $new;
        }
        public function getQueryParams(): array{
            return $this->__queryParams;
        }
        public function withQueryParams(array $query): ServerRequestInterface{
            $new = clone $this;
            $new->__queryParams = $query;
            return $new;
        }
        public function getParsedBody(){
            return $this->__parsedBody;
        }
        public function withParsedBody($data): ServerRequestInterface{
            $new = clone $this;
            $new->__parsedBody = $data;
            return $new;
        }
        public function getAttributes(): array {
            return $this->__attributes;
        }
        public function getAttribute($attribute, $default = null){
            if (false === array_key_exists($attribute, $this->__attributes))
                return $default;
            return $this->__attributes[$attribute];
        }
        public function withAttribute($attribute, $value): ServerRequestInterface{
            $new = clone $this;
            $new->__attributes[$attribute] = $value;
            return $new;
        }
        public function withoutAttribute($attribute): ServerRequestInterface{
            if (false === array_key_exists($attribute, $this->__attributes))
                return $this;
            $new = clone $this;
            unset($new->__attributes[$attribute]);
            return $new;
        }
        private static function __createUploadedFileFromSpec(array $value){
            if (is_array($value['tmp_name']))
                return self::__normalizeNestedFileSpec($value);
            return new UploadedFile($value['tmp_name'],(int) $value['size'],(int) $value['error'],$value['name'],$value['type']);
        }
        private static function __normalizeNestedFileSpec(array $files = []): array{
            $normalizedFiles = [];
            foreach (array_keys($files['tmp_name']) as $key) {
                $spec = [
                    'tmp_name' => $files['tmp_name'][$key],
                    'size'     => $files['size'][$key],
                    'error'    => $files['error'][$key],
                    'name'     => $files['name'][$key],
                    'type'     => $files['type'][$key],
                ];
                $normalizedFiles[$key] = self::__createUploadedFileFromSpec($spec);
            }
            return $normalizedFiles;
        }
        private static function __extractHostAndPortFromAuthority(string $authority): array{
            $uri = 'http://' . $authority;
            $parts = parse_url($uri);
            if (false === $parts) return [null, null];
            $host = $parts['host'] ?? null;
            $port = $parts['port'] ?? null;
            return [$host, $port];
        }

    }
}else die;