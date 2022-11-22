<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 17:13
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
use TP_Core\Libs\PHP\Libs\HTTP_Client\NetworkExceptionInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    class ConnectException extends TransferException implements NetworkExceptionInterface{
        private $__handlerContext;
        private $__request;
        public function __construct(
            string $message,
            RequestInterface $request,
            \Throwable $previous = null,
            array $handlerContext = []
        ) {
            parent::__construct($message, 0, $previous);
            $this->__request = $request;
            $this->__handlerContext = $handlerContext;
        }
        public function getRequest(): RequestInterface{
            return $this->__request;
        }
        public function getHandlerContext(): array{
            return $this->__handlerContext;
        }
    }
}else{die;}