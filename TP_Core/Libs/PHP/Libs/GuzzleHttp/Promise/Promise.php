<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 18:13
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class Promise implements PromiseInterface{
        private $__state = self::PENDING;
        private $__result;
        private $__cancelFn;
        private $__waitFn;
        private $__waitList;
        private $__handlers = [];
        public function __construct(callable $waitFn = null,callable $cancelFn = null) {
            $this->__waitFn = $waitFn;
            $this->__cancelFn = $cancelFn;
        }
        public function then(callable $onFulfilled = null,callable $onRejected = null) {
            if ($this->__state === self::PENDING) {
                $p = new Promise(null, [$this, 'cancel']);
                $this->__handlers[] = [$p, $onFulfilled, $onRejected];
                $p->__waitList = $this->__waitList;
                $p->__waitList[] = $this;
                return $p;
            }
            if ($this->__state === self::FULFILLED) {
                $promise = Create::promiseFor($this->__result);
                return $onFulfilled ? $promise->then($onFulfilled) : $promise;
            }
            $_rejection = Create::rejectionFor($this->__result);
            $rejection = null;
            if($_rejection instanceof PromiseInterface){
                $rejection = $_rejection;
            }
            return $onRejected ? $rejection->then(null, $onRejected) : $rejection;
        }
        public function otherwise(callable $onRejected){
            return $this->then(null, $onRejected);
        }
        public function wait($unwrap = true){
            $this->__waitIfPending();
            if ($this->__result instanceof PromiseInterface){return $this->__result->wait($unwrap);}
            if ($unwrap) {
                if ($this->__state === self::FULFILLED){return $this->__result;}
                throw Create::exceptionFor($this->__result);
            }
            return null;
        }
        public function getState():string{
            return $this->__state;
        }
        public function cancel():string{
            if ($this->__state !== self::PENDING){ return;}
            $this->__waitFn = $this->__waitList = null;
            if ($this->__cancelFn) {
                $fn = $this->__cancelFn;
                $this->__cancelFn = null;
                try {
                    $fn();
                } catch (\Throwable $e) {
                    $this->reject($e);
                } catch (\Exception $e) {
                    $this->reject($e);
                }
            }
            /** @psalm-suppress RedundantCondition */
            if ($this->__state === self::PENDING){$this->reject(new CancellationException('Promise has been cancelled'));}
            return null;
        }
        public function resolve($value):string{
            $this->__settle(self::FULFILLED, $value);
            return null;
        }
        public function reject($reason):string{
            $this->__settle(self::REJECTED, $reason);
            return null;
        }
        private function __settle($state, $value):string{
            if ($this->__state !== self::PENDING) {
                if ($state === $this->__state && $value === $this->__result){return;}
                throw $this->__state === $state
                    ? new \LogicException("The promise is already {$state}.")
                    : new \LogicException("Cannot change a {$this->__state} promise to {$state}");
            }
            if ($value === $this){throw new \LogicException('Cannot fulfill or reject a promise with itself');}
            $this->__state = $state;
            $this->__result = $value;
            $handlers = $this->__handlers;
            $this->__handlers = null;
            $this->__waitList = $this->__waitFn = null;
            $this->__cancelFn = null;
            if (!$handlers){return;}
            if (!is_object($value) || !method_exists($value, 'then')) {
                $id = $state === self::FULFILLED ? 1 : 2;
                Utils::queue()->add(static function () use ($id, $value, $handlers) {
                    foreach ($handlers as $handler) {
                        self::__callHandler($id, $value, $handler);
                    }
                });
            } elseif ($value instanceof self && PromiseIs::pending($value)) {
                $value->handlers = array_merge($value->handlers, $handlers);
            } else {
                $value->then(
                    static function ($value) use ($handlers) {
                        foreach ($handlers as $handler){self::__callHandler(1, $value, $handler);}
                    },
                    static function ($reason) use ($handlers) {
                        foreach ($handlers as $handler){self::__callHandler(2, $reason, $handler);}
                    }
                );
            }
            return null;
        }
        private static function __callHandler($index, $value, array $handler):string {
            $promise = $handler[0];
            if (PromiseIs::settled($promise)){ return;}
            try {
                if (isset($handler[$index])) {
                    $f = $handler[$index];
                    //unset($handler);
                    $promise->resolve($f($value));
                }elseif ($index === 1){$promise->resolve($value);
                }else {$promise->reject($value);}
            } catch (\Throwable $reason) {
                $promise->reject($reason);
            } catch (\Exception $reason) {
                $promise->reject($reason);
            }
            return null;
        }
        private function __waitIfPending(){
            if ($this->__state !== self::PENDING){ return;}
            if ($this->__waitFn) {$this->__invokeWaitFn();
            }elseif ($this->__waitList) {$this->__invokeWaitList();
            }else {
                $this->reject('Cannot wait on a promise that has '
                    . 'no internal wait function. You must provide a wait '
                    . 'function when constructing the promise to be able to '
                    . 'wait on a promise.');
            }
            Utils::queue()->run();
            /** @psalm-suppress RedundantCondition */
            if ($this->__state === self::PENDING){$this->reject('Invoking the wait callback did not resolve the promise');}
            return null;
        }
        private function __invokeWaitFn():string{
            try {
                $wfn = $this->__waitFn;
                $this->__waitFn = null;
                $wfn(true);
            } catch (\Exception $reason) {
                if ($this->__state === self::PENDING){ $this->reject($reason);}
                else {throw $reason;}
            }
            return null;
        }
        private function __invokeWaitList():string{
            $waitList = $this->__waitList;
            $this->__waitList = null;
            foreach ($waitList as $result) {
                do {
                    if($result instanceof self){
                        $result->__waitIfPending();
                        $result = $result->__result;
                    }
                } while ($result instanceof self);
                if ($result instanceof PromiseInterface){ $result->wait(false);}
            }
            return null;
        }
    }
}else{die;}