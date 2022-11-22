<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 04:37
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    final class TransferStats{
        private $__request;
        private $__response;
        private $__transferTime;
        private $__handlerStats;
        private $__handlerErrorData;
        public function __construct(RequestInterface $request,?ResponseInterface $response = null,?float $transferTime = null,$handlerErrorData = null,array $handlerStats = []){
            $this->__request = $request;
            $this->__response = $response;
            $this->__transferTime = $transferTime;
            $this->__handlerErrorData = $handlerErrorData;
            $this->__handlerStats = $handlerStats;
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
        public function getHandlerErrorData(){
            return $this->__handlerErrorData;
        }
        public function getEffectiveUri(): UriInterface{
            return $this->__request->getUri();
        }
        public function getTransferTime(): ?float{
            return $this->__transferTime;
        }
        public function getHandlerStats(): array{
            return $this->__handlerStats;
        }
        public function getHandlerStat(string $stat){
            return $this->__handlerStats[$stat] ?? null;
        }
    }
}else die;
