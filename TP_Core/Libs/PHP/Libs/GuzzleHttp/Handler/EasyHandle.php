<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-4-2022
 * Time: 16:36
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Response;
use TP_Managers\PHP_Manager\Libs\GuzzleHttp\Utils;
//todo use TP_Managers\PHP_Manager\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\StreamInterface;
if(ABSPATH){
    final class EasyHandle{
        public $handle;
        public $sink;
        public $headers = [];
        public $response;
        public $request;
        public $options = [];
        public $err_no = 0;
        public $onHeadersException;
        public $createResponseException;
        public function createResponse(): void{
            //todo to please my editor
            static $ver,$status,$reason ,$headers;
            $header_setup = [$ver,$status,$reason ,$headers];
            $header_setup .= HeaderProcessor::parseHeaders($this->headers);
            $normalizedKeys = Utils::normalizeHeaderKeys($header_setup);
            if (!empty($this->options['decode_content']) && isset($normalizedKeys['content-encoding'])) {
                $headers['x-encoded-content-encoding'] = $header_setup[$normalizedKeys['content-encoding']];
                unset($header_setup[$normalizedKeys['content-encoding']]);
                if (isset($normalizedKeys['content-length'])) {
                    $bodyLength = null;
                    $header_setup['x-encoded-content-length'] = $header_setup[$normalizedKeys['content-length']];
                    if($this->sink instanceof StreamInterface){
                        $bodyLength = (int) $this->sink->getSize();
                    }
                    if ($bodyLength){$header_setup[$normalizedKeys['content-length']] = $bodyLength;}
                    else{ unset($header_setup[$normalizedKeys['content-length']]);}
                }
                return;
            }
            $response = new Response($status,$headers,$this->sink,$ver,$reason);
            if($response instanceof ResponseInterface){
                $this->response = $response;
            }
            return null;
        }
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param $name
         */
        public function __get($name){
            $msg = $name === 'handle' ? 'The EasyHandle has been released' : 'Invalid property: ' . $name;
            throw new \BadMethodCallException($msg);
        }
    }
}else {die;}