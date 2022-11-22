<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 05:28
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    class CurlHandler{
        private $__factory;
        public function __construct(array $options = []){
            $this->__factory = $options['handle_factory']?? new CurlFactory(3);
        }
        public function __invoke(RequestInterface $request, array $options): PromiseInterface{
            if (isset($options['delay'])) {
                \usleep($options['delay'] * 1000);
            }
            $easy = $this->__factory->create($request, $options);
            \curl_exec($easy->handle);
            $easy->err_no = \curl_errno($easy->handle);
            return CurlFactory::finish($this, $easy, $this->__factory);
        }
    }
}else{die;}