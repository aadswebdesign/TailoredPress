<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 04:30
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\ConnectException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\RequestException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Utils as Http_Utils;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\LazyOpenStream;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Utils as PsrUtils;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\TransferStats;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    class CurlFactory{ //todo needs testing as all off this stack
        public const CURL_VERSION_STR = 'curl_version';
        public const LOW_CURL_VERSION_NUMBER = '7.21.2';
        private $__handles = [];
        private $__maxHandles;
        public function __construct(int $maxHandles){
            $this->__maxHandles = $maxHandles;
        }
        public function create(RequestInterface $request, array $options): EasyHandle{
            if (isset($options['curl']['body_as_string'])) {
                $options['_body_as_string'] = $options['curl']['body_as_string'];
                unset($options['curl']['body_as_string']);
            }
            $easy = new EasyHandle;
            $easy->request = $request;
            $easy->options = $options;
            $conf = $this->__getDefaultConf($easy);
            $this->__applyMethod($easy, $conf);
            $this->__applyHandlerOptions($easy, $conf);
            $this->__applyHeaders($easy, $conf);
            unset($conf['_headers']);
            if (isset($options['curl'])) {$conf = \array_replace($conf, $options['curl']);}
            $conf[\CURLOPT_HEADERFUNCTION] = $this->__createHeaderFn($easy);
            $easy->handle = $this->__handles ? \array_pop($this->__handles) : \curl_init();
            curl_setopt_array($easy->handle, $conf);
            return $easy;
        }
        public function release(EasyHandle $easy): void{
            $resource = $easy->handle;
            unset($easy->handle);
            if (\count($this->__handles) >= $this->__maxHandles){\curl_close($resource);
            }else {
                \curl_setopt($resource, \CURLOPT_HEADERFUNCTION, null);
                \curl_setopt($resource, \CURLOPT_READFUNCTION, null);
                \curl_setopt($resource, \CURLOPT_WRITEFUNCTION, null);
                \curl_setopt($resource, \CURLOPT_PROGRESSFUNCTION, null);
                \curl_reset($resource);
                $this->__handles[] = $resource;
            }
            return null;
        }
        public static function finish(callable $handler, EasyHandle $easy, CurlFactoryInterface $factory): P\PromiseInterface {
            if (isset($easy->options['on_stats'])){ self::__invokeStats($easy);}
            if (!$easy->response || $easy->err_no){return self::__finishError($handler, $easy, $factory);}
            $factory->release($easy);
            $body = null;
            if($easy->response instanceof ResponseInterface){
                $body = $easy->response->getBody();
            }
            if($body instanceof StreamInterface && $body->isSeekable()){ $body->rewind();}
            return new P\FulfilledPromise($easy->response);
        }
        private static function __invokeStats(EasyHandle $easy): void{
            $curlStats = \curl_getinfo($easy->handle);
            $curlStats['appconnect_time'] = \curl_getinfo($easy->handle, \CURLINFO_APPCONNECT_TIME);
            $stats = new TransferStats($easy->request,$easy->response,$curlStats['total_time'],$easy->err_no,$curlStats);
            ($easy->options['on_stats'])($stats);
            return null;
        }
        private static function __finishError(callable $handler, EasyHandle $easy, CurlFactoryInterface $factory): P\PromiseInterface{
            $ctx = ['err_no' => $easy->err_no,'error' => \curl_error($easy->handle), 'appconnect_time' => \curl_getinfo($easy->handle, \CURLINFO_APPCONNECT_TIME),
                ] + \curl_getinfo($easy->handle);
            $ctx[self::CURL_VERSION_STR] = \curl_version()['version'];
            $factory->release($easy);
            if (empty($easy->options['_err_message']) && (!$easy->err_no || $easy->err_no === 65)){
                return self::__retryFailedRewind($handler, $easy, $ctx);
            }
            return self::__createRejection($easy, $ctx);
        }
        private static function __createRejection(EasyHandle $easy, array $ctx): string{
            static $connectionErrors = [
                \CURLE_OPERATION_TIMEOUTED  => true,
                \CURLE_COULDNT_RESOLVE_HOST => true,
                \CURLE_COULDNT_CONNECT      => true,
                \CURLE_SSL_CONNECT_ERROR    => true,
                \CURLE_GOT_NOTHING          => true,
            ];
            if ($easy->createResponseException) {
                return P\Create::rejectionFor(new RequestException(
                    'An error was encountered while creating the response',
                    $easy->request,
                    $easy->response,
                    $easy->createResponseException,
                    $ctx
                ));
            }
            if ($easy->onHeadersException) {
                return P\Create::rejectionFor(
                    new RequestException(
                        'An error was encountered during the on_headers event',
                        $easy->request,
                        $easy->response,
                        $easy->onHeadersException,
                        $ctx
                    )
                );
            }
            $message = \sprintf(
                'cURL error %s: %s (%s)',
                $ctx['err_no'],
                $ctx['error'],
                'see https://curl.haxx.se/libcurl/c/libcurl-errors.html'
            );
            if($easy->request instanceof RequestInterface){
                $uriString = (string) $easy->request->getUri();
                if ($uriString !== '' && false === \strpos($ctx['error'], $uriString)){
                    $message .= \sprintf(' for %s', $uriString);
                }
            }
            $error = isset($connectionErrors[$easy->err_no])
                ? new ConnectException($message, $easy->request, null, $ctx)
                : new RequestException($message, $easy->request, $easy->response, null, $ctx);
            return P\Create::rejectionFor($error);
        }
        private static function __retryFailedRewind(callable $handler, EasyHandle $easy, array $ctx): P\PromiseInterface{
            try {
                // Only rewind if the body has been read from.
                $body = null;
                if($easy->request instanceof RequestInterface){
                    $body = $easy->request->getBody();
                }
                if($body instanceof StreamInterface &&($body->tell() > 0)){
                    $body->rewind();
                }
            } catch (\RuntimeException $e) {
                $ctx['error'] = 'The connection unexpectedly failed without '
                    . 'providing an error. The request would have been retried, '
                    . 'but attempting to rewind the request body failed. '
                    . 'Exception: ' . $e;
                return self::__createRejection($easy, $ctx);
            }
            if (!isset($easy->options['_curl_retries'])){$easy->options['_curl_retries'] = 1;}
            elseif ($easy->options['_curl_retries'] === 2) {
                $ctx['error'] = 'The cURL request was retried 3 times '
                    . 'and did not succeed. The most likely reason for the failure '
                    . 'is that cURL was unable to rewind the body of the request '
                    . 'and subsequent retries resulted in the same error. Turn on '
                    . 'the debug option to see what went wrong. See '
                    . 'https://bugs.php.net/bug.php?id=47204 for more information.';
                return self::__createRejection($easy, $ctx);
            } else {$easy->options['_curl_retries']++;}
            return $handler($easy->request, $easy->options);
        }//516
        private function __getDefaultConf(EasyHandle $easy): array{
            $get_uri = null;
            if($easy->request instanceof RequestInterface){
                $get_uri = $easy->request->getUri();
            }
            $cong = null;
            if($get_uri instanceof UriInterface){
                $conf = [
                    '_headers'              => $easy->request->getHeaders(),
                    \CURLOPT_CUSTOMREQUEST  => $easy->request->getMethod(),
                    \CURLOPT_URL            => (string) $get_uri->withFragment(''),
                    \CURLOPT_RETURNTRANSFER => false,
                    \CURLOPT_HEADER         => false,
                    \CURLOPT_CONNECTTIMEOUT => 150,
                ];
            }
            if (\defined('CURLOPT_PROTOCOLS')){$conf[\CURLOPT_PROTOCOLS] = \CURLPROTO_HTTP | \CURLPROTO_HTTPS;}
            $version = $easy->request->getProtocolVersion();
            if ($version === 1.1){$conf[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_1_1;}
            elseif ($version === 2.0){
                /** @noinspection PhpUndefinedConstantInspection */
                $conf[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_2_0;
            }else {$conf[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_1_0;}
            return $conf;
        }
        private function __applyMethod(EasyHandle $easy, array &$conf): void{
            $body = null;
            if($easy->request instanceof RequestInterface){
                $body = $easy->request->getBody();
            }
            $size = null;
            if($body instanceof StreamInterface && ($size === null || $size > 0)){
                $size = $body->getSize();
            }
            if ($size === null || $size > 0) {
                $this->__applyBody($easy->request, $easy->options, $conf);
                return;
            }
            $method = $easy->request->getMethod();
            if ($method === 'PUT' || $method === 'POST') {
                if (!$easy->request->hasHeader('Content-Length')){$conf[\CURLOPT_HTTPHEADER][] = 'Content-Length: 0';}
            }elseif ($method === 'HEAD') {
                $conf[\CURLOPT_NOBODY] = true;
                unset(
                    $conf[\CURLOPT_WRITEFUNCTION],
                    $conf[\CURLOPT_READFUNCTION],
                    $conf[\CURLOPT_FILE],
                    $conf[\CURLOPT_INFILE]
                );
            }
            return null;
        }
        private function __applyBody(RequestInterface $request, array $options, array &$conf): void{
            $size = $request->hasHeader('Content-Length') ? (int) $request->getHeaderLine('Content-Length') : null;
            if (($size !== null && $size < 1000000) || !empty($options['_body_as_string'])) {
                $conf[\CURLOPT_POSTFIELDS] = (string) $request->getBody();
                $this->__removeHeader('Content-Length', $conf);
                $this->__removeHeader('Transfer-Encoding', $conf);
            }if (($size !== null && $size < 1000000) || !empty($options['_body_as_string'])) {
                $conf[\CURLOPT_POSTFIELDS] = (string) $request->getBody();
                $this->__removeHeader('Content-Length', $conf);
                $this->__removeHeader('Transfer-Encoding', $conf);
            }else {
                $conf[\CURLOPT_UPLOAD] = true;
                if ($size !== null) {
                    $conf[\CURLOPT_INFILESIZE] = $size;
                    $this->__removeHeader('Content-Length', $conf);
                }
                $body = $request->getBody();
                if($body instanceof StreamInterface && $body->isSeekable()){
                    $body->rewind();
                }
                $conf[\CURLOPT_READFUNCTION] = static function ($length) use ($body) {//not used $ch, $fd,
                    return $body->read($length);
                };
            }
            if (!$request->hasHeader('Expect')){$conf[\CURLOPT_HTTPHEADER][] = 'Expect:';}
            if (!$request->hasHeader('Content-Type')){$conf[\CURLOPT_HTTPHEADER][] = 'Content-Type:';}
            return null;
        }
        private function __applyHeaders(EasyHandle $easy, array &$conf): void{
            foreach ($conf['_headers'] as $name => $values) {
                foreach ($values as $value) {
                    $value = (string) $value;
                    if ($value === ''){ $conf[\CURLOPT_HTTPHEADER][] = "$name;";}
                    else {$conf[\CURLOPT_HTTPHEADER][] = "$name: $value";}
                }
            }
            if($easy->request instanceof RequestInterface && (!$easy->request->hasHeader('Accept'))){
                $conf[\CURLOPT_HTTPHEADER][] = 'Accept:';
            }
            return null;
        }
        private function __removeHeader(string $name, array &$options): void{
            foreach (\array_keys($options['_headers']) as $key) {
                if (!\strcasecmp($key, $name)) {
                    unset($options['_headers'][$key]);
                    return;
                }
            }
            return null;
        }
        private function __applyHandlerOptions(EasyHandle $easy, array &$conf): void{
            $options = $easy->options;
            if (isset($options['verify'])) {
                if ($options['verify'] === false) {
                    unset($conf[\CURLOPT_CAINFO]);
                    /** @noinspection CurlSslServerSpoofingInspection */
                    $conf[\CURLOPT_SSL_VERIFYHOST] = 0;
                    /** @noinspection CurlSslServerSpoofingInspection */
                    $conf[\CURLOPT_SSL_VERIFYPEER] = false;
                } else {
                    $conf[\CURLOPT_SSL_VERIFYHOST] = 2;
                    $conf[\CURLOPT_SSL_VERIFYPEER] = true;
                    if (\is_string($options['verify'])) {
                        if (!\file_exists($options['verify'])){
                            throw new \InvalidArgumentException("SSL CA bundle not found: {$options['verify']}");
                        }
                        if (\is_dir($options['verify']) ||(\is_link($options['verify']) === true &&($verifyLink = \readlink($options['verify'])) !== false && \is_dir($verifyLink))){
                            $conf[\CURLOPT_CAPATH] = $options['verify'];
                        }
                        else {$conf[\CURLOPT_CAINFO] = $options['verify'];}
                    }
                }
            }
            if (($easy->request instanceof RequestInterface) && !isset($options['curl'][\CURLOPT_ENCODING]) && !empty($options['decode_content'])) {
                $accept = $easy->request->getHeaderLine('Accept-Encoding');
                if ($accept){ $conf[\CURLOPT_ENCODING] = $accept;}
                else {
                    $conf[\CURLOPT_ENCODING] = '';
                    $conf[\CURLOPT_HTTPHEADER][] = 'Accept-Encoding:';
                }
            }
            if (!isset($options['sink'])){$options['sink'] = PsrUtils::tryFopen('php://temp', 'w+');}
            $sink = $options['sink'];
            if (!\is_string($sink)){$sink = PsrUtils::streamFor($sink);
            }elseif (!\is_dir(\dirname($sink))){
                throw new \RuntimeException(\sprintf('Directory %s does not exist for sink value of %s', \dirname($sink), $sink));
            }else{$sink = new LazyOpenStream($sink, 'w+');}
            $easy->sink = $sink;
            $conf[\CURLOPT_WRITEFUNCTION] = static function ($write) use ($sink): int {//not used $ch,
                return $sink->write($write);
            };
            $timeoutRequiresNoSignal = false;
            if (isset($options['timeout'])) {
                $timeoutRequiresNoSignal |= $options['timeout'] < 1;
                $conf[\CURLOPT_TIMEOUT_MS] = $options['timeout'] * 1000;
            }
            if (isset($options['force_ip_resolve'])) {
                if ('v4' === $options['force_ip_resolve']){$conf[\CURLOPT_IPRESOLVE] = \CURL_IPRESOLVE_V4;}
                elseif ('v6' === $options['force_ip_resolve']){$conf[\CURLOPT_IPRESOLVE] = \CURL_IPRESOLVE_V6;}
            }
            if (isset($options['connect_timeout'])) {
                $timeoutRequiresNoSignal |= $options['connect_timeout'] < 1;
                $conf[\CURLOPT_CONNECTTIMEOUT_MS] = $options['connect_timeout'] * 1000;
            }
            if ($timeoutRequiresNoSignal && \stripos(\PHP_OS,0, 3) !== 'WIN'){
                $conf[\CURLOPT_NOSIGNAL] = true;
            }
            if (isset($options['proxy'])) {
                if (!\is_array($options['proxy'])){$conf[\CURLOPT_PROXY] = $options['proxy'];
                }else {
                    $get_uri = $easy->request->getUri();
                    $scheme = null;
                    if($get_uri instanceof UriInterface){$scheme = $get_uri->getScheme();}
                    if (isset($options['proxy'][$scheme])) {
                        $host = $get_uri->getHost();
                        if (!isset($options['proxy']['no']) || !Http_Utils::isHostInNoProxy($host, $options['proxy']['no'])){
                            $conf[\CURLOPT_PROXY] = $options['proxy'][$scheme];
                        }
                    }
                }
            }
            if (isset($options['ssl_key'])) {
                static $sslKey=[];
                if (\is_array($options['ssl_key'])) {
                    $keyPassword =$conf[\CURLOPT_SSLKEYPASSWD];
                    $sslKey[$keyPassword] =  $options['ssl_key'];
                    if (\count($options['ssl_key']) === 2){$sslKey = $options['ssl_key'];
                    }else {$sslKey = $options['ssl_key'];}
                }
                $sslKey = $sslKey ?? $options['ssl_key'];
                if (!\file_exists($sslKey)){throw new \InvalidArgumentException("SSL private key not found: {$sslKey}");}
                $conf[\CURLOPT_SSLKEY] = $sslKey;
            }
            if (isset($options['progress'])) {
                $progress = $options['progress'];
                if (!\is_callable($progress)){throw new \InvalidArgumentException('progress client option must be callable');}
                $conf[\CURLOPT_NOPROGRESS] = false;
                $conf[\CURLOPT_PROGRESSFUNCTION] = static function ($resource, int $downloadSize, int $downloaded, int $uploadSize, int $uploaded) use ($progress) {
                    $progress($resource,$downloadSize, $downloaded, $uploadSize, $uploaded);
                };
            }
            if (!empty($options['debug'])) {
                $conf[\CURLOPT_STDERR] = Http_Utils::debugResource($options['debug']);
                $conf[\CURLOPT_VERBOSE] = true;
            }
            return null;
        }
        private function __createHeaderFn(EasyHandle $easy): callable{
            $onHeaders = null;
            if (isset($easy->options['on_headers'])) {$onHeaders = $easy->options['on_headers'] ?? null;}
            if (!\is_callable($onHeaders)){throw new \InvalidArgumentException('on_headers must be callable');}
            return static function ($h) use ($onHeaders,$easy,&$startingResponse) {
                $value = \trim($h);
                if ($value === '') {
                    $startingResponse = true;
                    try {
                        $easy->createResponse();
                    } catch (\Exception $e) {
                        $easy->createResponseException = $e;
                        return -1;
                    }
                    if ($onHeaders !== null) {
                        try {
                            $onHeaders($easy->response);
                        } catch (\Exception $e) {
                            $easy->onHeadersException = $e;
                            return -1;
                        }
                    }
                } elseif ($startingResponse) {
                    $startingResponse = false;
                    $easy->headers = [$value];
                } else {$easy->headers[] = $value;}
                return \strlen($h);
            };//not used $ch,
        }//550
    }
}else{die;}