<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 04:34
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ServerRequestFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ServerRequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UploadedFileFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UploadedFileInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriFactoryInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    final class HttpFactory implements RequestFactoryInterface,ResponseFactoryInterface,ServerRequestFactoryInterface,StreamFactoryInterface,UploadedFileFactoryInterface,UriFactoryInterface{
        public function createUploadedFile(
            StreamInterface $stream,
            int $size = null,
            int $error = \UPLOAD_ERR_OK,
            string $clientFilename = null,
            string $clientMediaType = null
        ): UploadedFileInterface {
            if ($size === null) $size = $stream->getSize();
            return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
        }
        public function createStream(string $content = ''): StreamInterface{
            return Utils::streamFor($content);
        }
        public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface{
            try {
                $resource = Utils::tryFopen($file, $mode);
            } catch (\RuntimeException $e) {
                if ('' === $mode || false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true))
                    throw new \InvalidArgumentException(sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
                throw $e;
            }
            return Utils::streamFor($resource);
        }
        public function createStreamFromResource($resource): StreamInterface{
            return Utils::streamFor($resource);
        }
        public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface{
            if (empty($method)) {
                if (!empty($serverParams['REQUEST_METHOD']))
                    $method = $serverParams['REQUEST_METHOD'];
                else throw new \InvalidArgumentException('Cannot determine HTTP method');
            }
            return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
        }

        public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface {
            $response = '';
            if($response instanceof Response ){
                $response = new Response();
            }
            return $response($code, [], null, '1.1', $reasonPhrase);
        }
        public function createRequest(string $method, $uri): RequestInterface{
            return new Request($method, $uri);
        }
        public function createUri(string $uri = ''): UriInterface{
            return new Uri($uri);
        }
    }
}else die;