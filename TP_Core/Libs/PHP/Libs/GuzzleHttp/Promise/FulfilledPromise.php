<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:04
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class FulfilledPromise implements PromiseInterface{
        private $__value;
        public function __construct($value){
            if (is_object($value) && method_exists($value, 'then')){
                throw new \InvalidArgumentException('You cannot create a FulfilledPromise with a promise.');
            }
            $this->__value = $value;
        }
        public function then(callable $onFulfilled = null,callable $onRejected = null) {
            if (!$onFulfilled){ return $this;}
            $queue = Utils::queue();
            $p = new Promise([$queue, 'run']);
            $value = $this->__value;
            $queue->add(static function () use ($p, $value, $onFulfilled) {
                if (PromiseIs::pending($p)) {
                    try {
                        $p->resolve($onFulfilled($value));
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
        public function wait($unwrap = true, $defaultDelivery = null){
            return $unwrap ? $this->__value : null;
        }
        public function getState():string{
            return self::FULFILLED;
        }
        public function resolve($value):string{
            if ($value !== $this->__value){throw new \LogicException("Cannot resolve a fulfilled promise");}
            return null;
        }
        public function reject($reason):string{
            throw new \LogicException("Cannot reject a fulfilled promise");
        }
        public function cancel():null{
            return null;
        }// pass
    }
}else{die;}

