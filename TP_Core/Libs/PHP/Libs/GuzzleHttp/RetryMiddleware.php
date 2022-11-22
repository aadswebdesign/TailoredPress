<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 13:52
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    class RetryMiddleware{
        private $__nextHandler;
        private $__decider;
        private $__delay;
        public function __construct(callable $decider, callable $nextHandler, callable $delay = null){
            $this->__decider = $decider;
            $this->__nextHandler = $nextHandler;
            $this->__delay = $delay ?: __CLASS__ . '::exponentialDelay';
        }
        public static function exponentialDelay(int $retries): int{
            return (int) \2**($retries - 1) * 1000;
        }
        public function __invoke(RequestInterface $request, array $options): PromiseInterface{
            if (!isset($options['retries'])) $options['retries'] = 0;
            $fn = $this->__nextHandler;
            return $fn($request, $options)->then($this->__onFulfilled($request, $options),
                    $this->__onRejected($request, $options));
        }
        private function __onFulfilled(RequestInterface $request, array $options): callable{
            return function ($value) use ($request, $options) {
                if (!($this->__decider)($options['retries'],$request,$value,null))
                    return $value;
                return $this->__doRetry($request, $options, $value);
            };
        }
        private function __onRejected(RequestInterface $req, array $options): callable{
            return function ($reason) use ($req, $options) {
                if (!($this->__decider)($options['retries'],$req,null,$reason))
                    return P\Create::rejectionFor($reason);
                return $this->__doRetry($req, $options);
            };
        }
        private function __doRetry(RequestInterface $request, array $options, ResponseInterface $response = null): PromiseInterface{
            $options['delay'] = ($this->__delay)(++$options['retries'], $response);
            return $this($request, $options);
        }
    }
}else die;
