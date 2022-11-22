<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:16
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\BodySummarizer;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\BodySummarizerInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Client\RequestExceptionInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    class RequestException extends TransferException implements RequestExceptionInterface{
        private $__request;
        private $__response;
        private $__handlerContext;
        public function __construct(
            string $message,
            RequestInterface $request,
            ResponseInterface $response = null,
            \Throwable $previous = null,
            array $handlerContext = []
        ) {
            $code = $response ? $response->getStatusCode() : 0;
            parent::__construct($message, $code, $previous);
            $this->__request = $request;
            $this->__response = $response;
            $this->__handlerContext = $handlerContext;
        }
        public static function wrapException(RequestInterface $request, \Throwable $e): self{
            return $e instanceof self ? $e : new RequestException($e->getMessage(), $request, null, $e);
        }
        public static function create(
            RequestInterface $request,
            ResponseInterface $response = null,
            \Throwable $previous = null,
            array $handlerContext = [],
            BodySummarizerInterface $bodySummarizer = null
        ): self {
            if (!$response) {
                return new self(
                    'Error completing request',
                    $request,
                    null,
                    $previous,
                    $handlerContext
                );
            }
            $level = (int) \floor($response->getStatusCode() / 100);
            if ($level === 4) {
                $label = 'Client error';
                $className = ClientException::class;
            } elseif ($level === 5) {
                $label = 'Server error';
                $className = ServerException::class;
            } else {
                $label = 'Unsuccessful request';
                $className = __CLASS__;
            }

            $uri = $request->getUri();
            $uri = static::__obfuscateUri($uri);
            $message = \sprintf(
                '%s: `%s %s` resulted in a `%s %s` response',
                $label,
                $request->getMethod(),
                $uri->__toString(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );

            $summary = ($bodySummarizer ?? new BodySummarizer())->summarize($response);
            if ($summary !== null) {$message .= ":\n{$summary}\n";}
            return new $className($message, $request, $response, $previous, $handlerContext);
        }
        public function getRequest(): RequestInterface{
            return $this->__request;
        }
        public function getResponse(): ?ResponseInterface{
            return $this->__response;
        }
        public function hasResponse(): bool{
            return $this->__response !== null;
        }
        public function getHandlerContext(): array{
            return $this->__handlerContext;
        }
        private static function __obfuscateUri(UriInterface $uri): UriInterface{
            $userInfo = $uri->getUserInfo();
            if (false !== ($pos = \strpos($userInfo, ':'))){return $uri->withUserInfo(\substr($userInfo, 0, $pos), '***');}

            return $uri;
        }
    }
}else{die;}