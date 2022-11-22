<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:12
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    class BadResponseException extends RequestException{
        public function __construct(
            string $message,
            RequestInterface $request,
            ResponseInterface $response,
            \Throwable $previous = null,
            array $handlerContext = []
        ) {
            parent::__construct($message, $request, $response, $previous, $handlerContext);
        }
        public function hasResponse(): bool{
            return true;
        }
        //public function getResponse(): ResponseInterface{
            /** @var ResponseInterface */
            //return parent::getResponse();
        //}
    }
}else{ die;}