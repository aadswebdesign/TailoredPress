<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 13:03
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
//use JsonSchema\Constraints\StringConstraint;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
if(ABSPATH){
    class PrepareBodyMiddleware{
        private $__nextHandler;
        public function __construct(callable $nextHandler){
            $this->__nextHandler = $nextHandler;
        }
        public function __invoke(RequestInterface $request, array $options): PromiseInterface{
            $fn = $this->__nextHandler;
            $_body = $request->getBody();
            $body = null;
            if($_body instanceof StreamInterface){
                $body = $_body;
            }
            if ($body->getSize() === 0) return $fn($request, $options);
            $modify = [];
            if ((!$request->hasHeader('Content-Type')) && ($uri = $body->getMetadata('uri')) && is_string($uri) && $type = Psr7\MimeType::fromFilename($uri))
                $modify['set_headers']['Content-Type'] = $type;
            if (!$request->hasHeader('Content-Length') && !$request->hasHeader('Transfer-Encoding')) {
                $size = $body->getSize();
                if ($size !== null) $modify['set_headers']['Content-Length'] = $size;
                else $modify['set_headers']['Transfer-Encoding'] = 'chunked';
            }
            $this->__addExpectHeader($request, $options, $modify);
            return $fn(Psr7\Utils::modifyRequest($request, $modify), $options);
        }
        private function __addExpectHeader(RequestInterface $request, array $options, array &$modify): void{
            if ($request->hasHeader('Expect')) return;
            $expect = $options['expect'] ?? null;
            if ($expect === false || $request->getProtocolVersion() < 1.1) return;
            if ($expect === true) {
                $modify['set_headers']['Expect'] = '100-Continue';
                return;
            }
            if ($expect === null) $expect = 1048576;
            $_body = $request->getBody();
            $body = null;
            if($_body instanceof StreamInterface){
                $body = $_body;
            }
            $size = $body->getSize();
            if ($size === null || $size >= (int) $expect || !$body->isSeekable())
                $modify['set_headers']['Expect'] = '100-Continue';
            return null;
        }
    }
}else die;