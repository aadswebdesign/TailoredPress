<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:18
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class RejectedPromise implements PromiseInterface{
        private $__reason;
        public function __construct($reason){
            if (is_object($reason) && method_exists($reason, 'then')){
                throw new \InvalidArgumentException('You cannot create a RejectedPromise with a promise.');
            }
            $this->__reason = $reason;
        }
        public function then(callable $onFulfilled = null,callable $onRejected = null){
            // If there's no onRejected callback then just return self.
            if (!$onRejected){ return $this;}
            $queue = Utils::queue();
            $reason = $this->__reason;
            $p = new Promise([$queue, 'run']);
            $queue->add(static function () use ($p, $reason, $onRejected) {
                if (PromiseIs::pending($p)) {
                    try {
                        $p->resolve($onRejected($reason));
                    } catch (\Throwable $e) {
                        $p->reject($e);
                    } catch (\Exception $e) {
                        $p->reject($e);
                    }
                }
            });
            return $p;
        }
        public function otherwise(callable $onRejected){
            return $this->then(null, $onRejected);
        }
        public function wait($unwrap = true, $defaultDelivery = null):string{
            if ($unwrap){throw Create::exceptionFor($this->__reason);}
            return null;
        }
        public function getState():bool{
            return self::REJECTED;
        }
        public function resolve($value):string{
            throw new \LogicException("Cannot resolve a rejected promise");
        }
        public function reject($reason):string{
            if ($reason !== $this->__reason){
                throw new \LogicException("Cannot reject a rejected promise");
            }
            return null;
        }
        public function cancel():null{
            return null;
        }// pass
    }
}else{die;}