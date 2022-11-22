<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 05:30
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\ConnectException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\RequestException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\FulfilledPromise;
//use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\TransferStats;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Utils as Http_Utils;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){ //todo needs testing as all of this stack
    class StreamHandler{
        private $__lastHeaders = [];
        public function __invoke(RequestInterface $request, array $options): string{
            if (isset($options['delay'])) {\usleep($options['delay'] * 1000);}
            $startTime = isset($options['on_stats']) ? Http_Utils::currentTime() : null;
            try {
                $request_without = $request->withoutHeader('Expect');
                $request_stream = null;
                if($request_without instanceof RequestInterface){ $request_stream = $request_without->getBody();}
                if (($request_stream instanceof StreamInterface)&& 0 === $request_stream->getSize()){$request = $request->withHeader('Content-Length', '0');}

                return $this->__createResponse($request,$options,$this->__createStream($request, $options),$startTime);
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if (false !== \strpos($message, 'getaddrinfo') // DNS lookup failed
                    || false !== \strpos($message, 'Connection refused')
                    || false !== \strpos($message, "couldn't connect to host") // error on HHVM
                    || false !== \strpos($message, "connection attempt failed")
                ) {$e = new ConnectException($e->getMessage(), $request, $e);}
                else {$e = RequestException::wrapException($request, $e);}
                $this->__invokeStats($options, $request, $startTime, null, $e);
                return P\Create::rejectionFor($e);
            }
        }
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param array $options
         * @param RequestInterface $request
         * @param float|null $startTime
         * @param ResponseInterface|null $response
         * @param \Throwable|null $error
         */
        private function __invokeStats(
            array $options,RequestInterface $request,?float $startTime,
            ResponseInterface $response = null,\Throwable $error = null): void {
            if (isset($options['on_stats'])) {
                $stats = new TransferStats($request, $response, Http_Utils::currentTime() - $startTime, $error, []);
                ($options['on_stats'])($stats);
            }
            return null;
        }//82
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param RequestInterface $request
         * @param array $options
         * @param $stream
         * @param float|null $startTime
         * @return string
         */
        private function __createResponse(RequestInterface $request, array $options, $stream, ?float $startTime): string{
            $last_headers = $this->__lastHeaders;
            static $ver, $status, $reason, $headers;
            $this->__lastHeaders = [];
            try {
                $header_setup = [$ver, $status, $reason, $headers];
                $header_setup .= HeaderProcessor::parseHeaders($last_headers);
            } catch (\Exception $e) {
                return P\Create::rejectionFor(
                    new RequestException('An error was encountered while creating the response', $request, null, $e)
                );
            }
            $stream_setup = [$stream, $header_setup];
            $stream_setup .= $this->__checkDecode($options, $headers, $stream);
            $stream_result = Psr7\Utils::streamFor($stream_setup);
            $sink = $stream_result;
            if (\strcasecmp('HEAD', $request->getMethod())){$sink = $this->__createSink($stream, $options);}
            try {
                $response = new Psr7\Response($status, $headers, $sink, $ver, $reason);
            } catch (\Exception $e) {
                return P\Create::rejectionFor(
                    new RequestException('An error was encountered while creating the response', $request, null, $e)
                );
            }
            if (isset($options['on_headers'])) {
                try {
                    $options['on_headers']($response);
                } catch (\Exception $e) {
                    return P\Create::rejectionFor(
                        new RequestException('An error was encountered during the on_headers event', $request, $response, $e)
                    );
                }
            }
            if ($sink !== $stream_result && ( $response instanceof ResponseInterface )){$this->__drain($stream_result, $sink, $response->getHeaderLine('Content-Length'));}
            $this->__invokeStats($options, $request, $startTime, $response, null);
            return new FulfilledPromise($response);
        }//98
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param StreamInterface $stream
         * @param array $options
         * @return StreamInterface
         */
        private function __createSink(StreamInterface $stream, array $options): StreamInterface{
            if (!empty($options['stream'])){ return $stream;}
            $sink = $options['sink'] ?? Psr7\Utils::tryFopen('php://temp', 'r+');
            $lazy_stream = new Psr7\LazyOpenStream($sink, 'w+');
            $_lazy_stream =null;
            if($lazy_stream instanceof StreamInterface){
                $_lazy_stream = $lazy_stream;
            }
            return \is_string($sink) ? $_lazy_stream : Psr7\Utils::streamFor($sink);
        }//148
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param array $options
         * @param array $headers
         * @param $stream
         * @return array
         */
        private function __checkDecode(array $options, array $headers, $stream): array{
            if (!empty($options['decode_content'])) {
                $normalizedKeys = Http_Utils::normalizeHeaderKeys($headers);
                if (isset($normalizedKeys['content-encoding'])) {
                    $encoding = $headers[$normalizedKeys['content-encoding']];
                    if ($encoding[0] === 'gzip' || $encoding[0] === 'deflate') {
                        $stream = new Psr7\InflateStream(Psr7\Utils::streamFor($stream));
                        $headers['x-encoded-content-encoding'] = $headers[$normalizedKeys['content-encoding']];
                        unset($headers[$normalizedKeys['content-encoding']]);
                        if (isset($normalizedKeys['content-length'])) {
                            $headers['x-encoded-content-length'] = $headers[$normalizedKeys['content-length']];
                            $length = (int) $stream->getSize();
                            if ($length === 0){ unset($headers[$normalizedKeys['content-length']]);}
                            else {$headers[$normalizedKeys['content-length']] = [$length];}
                        }
                    }
                }
            }
            return [$stream, $headers];
        }//162
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param StreamInterface $source
         * @param StreamInterface $sink
         * @param string $contentLength
         * @return StreamInterface
         */
        private function __drain(StreamInterface $source, StreamInterface $sink, string $contentLength): StreamInterface{
            Psr7\Utils::copyToStream($source,$sink,(\$contentLength !=='' && (int) $contentLength > 0) ? (int) $contentLength : -1);
            $sink->seek(0);
            $source->close();
            return $sink;
        }//201
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param callable $callback
         * @return mixed
         */
        private function __createResource(callable $callback){
            $errors = []; //not used $_,
            \set_error_handler(static function ($msg, $file, $line) use (&$errors): bool {
                $errors[] = ['message' => $msg,'file' => $file,'line' => $line];
                return true;
            });
            try {
                $resource = $callback();
            } finally {
                \restore_error_handler();
            }
            if (!$resource) {
                $message = 'Error creating resource: ';
                foreach ($errors as $err) {
                    foreach ($err as $key => $value){$message .= "[$key] $value" . \PHP_EOL;}
                }
                throw new \RuntimeException(\trim($message));
            }
            return $resource;
        }//228
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param RequestInterface $request
         * @param array $options
         * @return mixed
         */
        private function __createStream(RequestInterface $request, array $options){
            static $methods;
            if (!$methods) {$methods = \array_flip(\get_class_methods(__CLASS__));}
            $get_uri = $request->getUri();
            $get_scheme = null;
            if($get_uri instanceof UriInterface){
                $get_scheme = $get_uri->getScheme();
            }
            if (!\in_array($get_scheme, ['http', 'https'])){
                throw new RequestException(\sprintf("The scheme '%s' is not supported.", $get_scheme), $request);
            }
            if ($request->getProtocolVersion() === '1.1' && !$request->hasHeader('Connection')) {
                $request = $request->withHeader('Connection', 'close');}
            if (!isset($options['verify'])){ $options['verify'] = true;}
            $params = [];
            $context = $this->__getDefaultContext($request);
            if (isset($options['on_headers']) && !\is_callable($options['on_headers'])){
                throw new \InvalidArgumentException('on_headers must be callable');
            }
            if (!empty($options)) {
                foreach ($options as $key => $value) {
                    $method = "add_{$key}";
                    if (isset($methods[$method])){$this->{$method}($request, $context, $value, $params);}
                }
            }
            if (isset($options['stream_context'])) {
                if (!\is_array($options['stream_context'])){
                    throw new \InvalidArgumentException('stream_context must be an array');
                }
                $context = \array_replace_recursive($context, $options['stream_context']);
            }
            if (isset($options['auth'][2]) && 'ntlm' === $options['auth'][2]){
                throw new \InvalidArgumentException('Microsoft NTLM authentication only supported with curl handler');
            }
            $uri = $this->__resolveHost($request, $options);
            $contextResource = $this->__createResource(
                static function () use ($context, $params) {
                    return \stream_context_create($context, $params);
                }
            );
            return $this->__createResource(
                function () use ($uri, &$http_response_header, $contextResource, $context, $options, $request) {
                    $resource = @\fopen((string) $uri, 'rb', false, $contextResource);
                    $this->__lastHeaders = $http_response_header ?? [];
                    if (false === $resource){
                        throw new ConnectException(sprintf('Connection refused for URI %s', $uri), $request, null, $context);
                    }
                    if (isset($options['read_timeout'])) {
                        $readTimeout = $options['read_timeout'];
                        $sec = (int) $readTimeout;
                        $up_sec = ($readTimeout - $sec) * 100000;
                        \stream_set_timeout($resource, $sec, $up_sec);
                    }
                    return $resource;
                }
            );
        }//262
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param RequestInterface $request
         * @param array $options
         * @return UriInterface
         */
        private function __resolveHost(RequestInterface $request, array $options): UriInterface{
            $get_uri = $request->getUri();
            if (($get_uri instanceof UriInterface) && isset($options['force_ip_resolve']) && !\filter_var($get_uri->getHost(), \FILTER_VALIDATE_IP)) {
                if ('v4' === $options['force_ip_resolve']) {
                    $records = \dns_get_record($get_uri->getHost(), \DNS_A);
                    if (false === $records || !isset($records[0]['ip'])){
                        throw new ConnectException(\sprintf("Could not resolve IPv4 address for host '%s'", $get_uri->getHost()), $request);
                    }
                    return $get_uri->withHost($records[0]['ip']);
                }
                if ('v6' === $options['force_ip_resolve']) {
                    $records = \dns_get_record($get_uri->getHost(), \DNS_AAAA);
                    if (false === $records || !isset($records[0]['ipv6'])){
                        throw new ConnectException(\sprintf("Could not resolve IPv6 address for host '%s'", $get_uri->getHost()), $request);
                    }
                    return $get_uri->withHost('[' . $records[0]['ipv6'] . ']');
                }
            }
            return $get_uri;
        }//343
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param RequestInterface $request
         * @return array
         */
        private function __getDefaultContext(RequestInterface $request): array{
            $headers = '';
            $get_uri = $request->getUri();
            $get_host = null;
            if($get_uri instanceof UriInterface){
                $get_host = $get_uri->getHost();
            }
            foreach ($request->getHeaders() as $name => $value) {
                foreach ($value as $val){ $headers .= "$name: $val\r\n";}
            }
            $context = [
                'http' => [
                    'method'           => $request->getMethod(),
                    'header'           => $headers,
                    'protocol_version' => $request->getProtocolVersion(),
                    'ignore_errors'    => true,
                    'follow_location'  => 0,
                ],
                'ssl' => ['peer_name' => $get_host,],
            ];
            $body = (string) $request->getBody();
            if (!empty($body)) {
                $context['http']['content'] = $body;
                if (!$request->hasHeader('Content-Type')){$context['http']['header'] .= "Content-Type:\r\n";}
            }
            $context['http']['header'] = \rtrim($context['http']['header']);
            return $context;
        }//367
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param string $url
         * @return array
         */
        private function __parse_proxy(string $url): array{
            $parsed = \parse_url($url);
            if ($parsed !== false && isset($parsed['scheme'],$parsed['host'],$parsed['port']) && $parsed['scheme'] === 'http') {
                $auth = null;
                if (isset($parsed['user'],$parsed['pass'])){$auth = \base64_encode("{$parsed['user']}:{$parsed['pass']}");}
                return [
                    'proxy' => "tcp://{$parsed['host']}:{$parsed['port']}",
                    'auth' => $auth ? "Basic {$auth}" : null,
                ];
            }
            return ['proxy' => $url,'auth' => null,];
        }//440
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param array $params
         * @param callable $notify
         */
        private static function __addNotification(array &$params, callable $notify): void{
            if (!isset($params['notification'])){ $params['notification'] = $notify;}
            else { $params['notification'] = self::__callArray([$params['notification'],$notify]);}
            return null;
        }//572
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param array $functions
         * @return callable
         */
        private static function __callArray(array $functions): callable{
            return static function (...$args) use ($functions) {
                foreach ($functions as $fn) {
                    $fn(...$args);
                }
            };
        }//585
        protected function _add_proxy(RequestInterface $request, array &$options, $value): void{
            $uri = null;
            $get_uri = $request->getUri();
            if (!\is_array($value)){$uri = $value;}
            elseif($get_uri instanceof UriInterface) {
                $scheme = $get_uri->getScheme();
                if (isset($value[$scheme])){
                    if (!isset($value['no']) || !Http_Utils::isHostInNoProxy($get_uri->getHost(), $value['no'])){$uri = $value[$scheme];}
                }
            }
            if (!$uri) {return;}
            $parsed = $this->__parse_proxy($uri);
            $options['http']['proxy'] = $parsed['proxy'];
            if ($parsed['auth']) {
                if (!isset($options['http']['header'])){$options['http']['header'] = [];}
                $options['http']['header'] .= "\r\nProxy-Authorization: {$parsed['auth']}";
            }
            return null;
        }//407
        protected function _add_timeout(array &$options,RequestInterface $value): void{
            if ($value > 0){ $options['http']['timeout'] = $value;}
            return null;
        }//468
        protected function _add_verify(array &$options, $value): void{
            if ($value === false) {
                $options['ssl']['verify_peer'] = false;
                $options['ssl']['verify_peer_name'] = false;
                return;
            }
            if (\is_string($value)) {
                $options['ssl']['cafile'] = $value;
                if (!\file_exists($value)){throw new \RuntimeException("SSL CA bundle not found: $value");}
            } elseif ($value !== true){throw new \InvalidArgumentException('Invalid verify request option');}
            $options['ssl']['verify_peer'] = true;
            $options['ssl']['verify_peer_name'] = true;
            $options['ssl']['allow_self_signed'] = false;
            return null;
        }//478
        protected function _add_cert(array &$options, $value): void{
            if (($value instanceof RequestInterface) && \is_array($value)) {
                $options['ssl']['passphrase'] = $value[1];
                /** @noinspection MultiAssignmentUsageInspection */
                $value = $value[0];
            }
            if (!\file_exists($value)){throw new \RuntimeException("SSL certificate not found: {$value}");}
            $options['ssl']['local_cert'] = $value;
            return null;
        }//504
        protected function _add_progress($value, array &$params): void{
            self::__addNotification(
                $params,
                static function ($code, $a, $b, $c, $transferred, $total) use ($value) {
                    if ($code === \STREAM_NOTIFY_PROGRESS){
                        $code_total = $a + $b + $c;
                        $value($total, $code_total, $transferred, 0, 0);
                    }
                }
            );
            return null;
        }//521
        protected function _add_debug(RequestInterface $request, $value, array &$params): void{
            if ($value === false) {return;}
            static $map = [
                \STREAM_NOTIFY_CONNECT       => 'CONNECT',
                \STREAM_NOTIFY_AUTH_REQUIRED => 'AUTH_REQUIRED',
                \STREAM_NOTIFY_AUTH_RESULT   => 'AUTH_RESULT',
                \STREAM_NOTIFY_MIME_TYPE_IS  => 'MIME_TYPE_IS',
                \STREAM_NOTIFY_FILE_SIZE_IS  => 'FILE_SIZE_IS',
                \STREAM_NOTIFY_REDIRECTED    => 'REDIRECTED',
                \STREAM_NOTIFY_PROGRESS      => 'PROGRESS',
                \STREAM_NOTIFY_FAILURE       => 'FAILURE',
                \STREAM_NOTIFY_COMPLETED     => 'COMPLETED',
                \STREAM_NOTIFY_RESOLVE       => 'RESOLVE',
            ];
            static $args = ['severity', 'message', 'message_code', 'bytes_transferred', 'bytes_max'];
            $value = Http_Utils::debugResource($value);
            $get_uri = $request->getUri();
            $indent = null;
            if($get_uri instanceof UriInterface){
                $indent = $request->getMethod() . ' ' . $get_uri->withFragment('');
            }
            self::__addNotification(
                $params,
                static function (int $code, ...$passed) use ($indent, $value, $map, $args): void {
                    \fprintf($value, '<%s> [%s] ', $indent, $map[$code]);
                    foreach (\array_filter($passed) as $i => $v){\fwrite($value, $args[$i] . ': "' . $v . '" ');}
                    \fwrite($value, "\n");
                    return null;
                }
            );
            return null;
        }
    }
}else{die;}