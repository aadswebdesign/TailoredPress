<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 05:21
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
//use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    class Request implements RequestInterface{
        use MessageTrait;
        private $__method;
        private $__requestTarget;
        private $__uri;
        public function __construct(string $method,
            $uri,array $headers = [],$body = null,string $version = '1.1') {
            $this->assertMethod($method);
            if (!($uri instanceof UriInterface))
                $uri = new Uri($uri);
            $this->__method = strtoupper($method);
            $this->__uri = $uri;
            $this->__setHeaders($headers);
            $this->__protocol = $version;
            if (!isset($this->__headerNames['host']))
                $this->__updateHostFromUri();
            if ($body !== '' && $body !== null)
                $this->__stream = Utils::streamFor($body);
        }
        public function getRequestTarget(): string{
            if ($this->__requestTarget !== null)
                return $this->__requestTarget;
            $target = $this->__uri->getPath();
            if ($target === '') $target = '/';
            if ($this->__uri->getQuery() !== '') {
                $target .= '?' . $this->__uri->getQuery();
            }
            return $target;
        }
        public function withRequestTarget($requestTarget): RequestInterface{
            if (preg_match('#\s#', $requestTarget))
                throw new \InvalidArgumentException(
                    'Invalid request target provided; cannot contain whitespace'
                );
            $new = clone $this;
            $new->__requestTarget = $requestTarget;
            return $new;
        }
        public function getMethod(): string{
            return $this->__method;
        }
        public function withMethod($method): RequestInterface{
            $this->assertMethod($method);
            $new = clone $this;
            $new->__method = strtoupper($method);
            return $new;
        }
        public function getUri(): UriInterface{
            return $this->__uri;
        }
        public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface{
            if ($uri === $this->__uri) return $this;
            $new = clone $this;
            $new->__uri = $uri;
            if (!$preserveHost || !isset($this->__headerNames['host']))
                $new->__updateHostFromUri();
            return $new;
        }
        private function __updateHostFromUri(): void{
            $host = $this->__uri->getHost();
            if ($host === '') return;
            if (($port = $this->__uri->getPort()) !== null)
                $host .= ':' . $port;
            if (isset($this->__headerNames['host']))
                $header = $this->__headerNames['host'];
            else {
                $header = 'Host';
                $this->__headerNames['host'] = 'Host';
            }
            $this->__headers = [$header => [$host]] + $this->__headers;
            return null;
        }
        private function assertMethod($method): void{
            if (!is_string($method) || $method === '')
                throw new \InvalidArgumentException('Method must be a non-empty string.');
            return null;
        }
    }
}else die;