<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:59
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
use Iterator;
if(ABSPATH){
    class Each implements PromisorInterface{
        private $__pending = [];
        private $__nextPendingIndex = 0;
        private $__iterable;
        private $__concurrency;
        private $__onFulfilled;
        private $__onRejected;
        private $__aggregate;
        private $__mutex;
        public function __construct($iterable, array $config = []){
            $this->__iterable = Create::iteratorFor($iterable);
            if (isset($config['concurrency'])){$this->__concurrency = $config['concurrency'];}
            if (isset($config['fulfilled'])){$this->__onFulfilled = $config['fulfilled'];}
            if (isset($config['rejected'])){$this->__onRejected = $config['rejected'];}
        }
        public function promise():string{
            $iterable = null;
            if( $this->__iterable instanceof Iterator ){
                $iterable = $this->__iterable;
            }
            if ($this->__aggregate){ return $this->__aggregate;}
            $aggregate = null;
            if($this->__aggregate instanceof PromiseInterface){
                $aggregate = $this->__aggregate;
            }
            try {
                $this->__createPromise();
                $iterable->rewind();
                $this->__refillPending();
            } catch (\Throwable $e) {
                $aggregate->reject($e);
            } catch (\Exception $e) {
                $aggregate->reject($e);
            }
            return $aggregate;
        }
        private function __createPromise():string{
            $this->__mutex = false;
            $this->__aggregate = new Promise(function () {
                if ($this->__checkIfFinished()){ return;}
                reset($this->__pending);
                while ($promise = current($this->__pending)) {
                    next($this->__pending);
                    $promise->wait();
                    if (PromiseIs::settled($this->__aggregate)){ return;}
                }
            });
            $clearFn = function () {
                $this->__iterable = $this->__concurrency = $this->__pending = null;
                $this->__onFulfilled = $this->__onRejected = null;
                $this->__nextPendingIndex = 0;
            };
            $this->__aggregate->then($clearFn, $clearFn);
            return null;
        }
        private function __refillPending():string{
            if (!$this->__concurrency) {
                //todo while ($this->__addPending() && $this->__advanceIterator()){}
                return;
            }
            $concurrency = is_callable($this->__concurrency)
                ? call_user_func($this->__concurrency, count($this->__pending))
                : $this->__concurrency;
            $concurrency = max($concurrency - count($this->__pending), 0);
            if (!$concurrency){ return;}
            $this->__addPending();
            //todo while (--$concurrency && $this->__advanceIterator()&& $this->__addPending()){}
            return null;
        }
        private function __addPending():string{
            $iterable = null;
            if( $this->__iterable instanceof Iterator ){
                $iterable = $this->__iterable;
            }
            if (!$iterable || !$iterable->valid()){return false;}
            $promise = Create::promiseFor($iterable->current());
            $key = $iterable->key();
            $idx = $this->__nextPendingIndex++;
            $this->__pending[$idx] = $promise->then(
                function ($value) use ($idx, $key) {
                    if ($this->__onFulfilled)
                        call_user_func($this->__onFulfilled,$value,$key,$this->__aggregate);
                    $this->__step($idx);
                },
                function ($reason) use ($idx, $key) {
                    if ($this->__onRejected)
                        call_user_func($this->__onRejected,$reason,$key,$this->__aggregate);
                    $this->__step($idx);
                }
            );
            return true;
        }
        private function __advanceIterator():string{
            if ($this->__mutex) {return false;}
            $this->__mutex = true;
            $aggregate = null;
            if($this->__aggregate instanceof PromiseInterface){
                $aggregate = $this->__aggregate;
            }
            try {
                /** @noinspection PhpUndefinedMethodInspection */ //todo next()
                $this->__iterable->next();
                $this->__mutex = false;
                return true;
            } catch (\Throwable $e) {
                $aggregate->reject($e);
                $this->__mutex = false;
                return false;
            } catch (\Exception $e) {
                $aggregate->reject($e);
                $this->__mutex = false;
                return false;
            }
        }
        private function __step($idx):string{
            if (PromiseIs::settled($this->__aggregate)) {return;}
            unset($this->__pending[$idx]);
            if ($this->__advanceIterator() && !$this->__checkIfFinished()){ $this->__refillPending();}
            return null;
        }
        private function __checkIfFinished():string{
            /** @noinspection PhpUndefinedMethodInspection */ //todo valid()
            if (!$this->__pending && !$this->__iterable->valid()) {
                if($this->__aggregate instanceof PromiseInterface){
                    $this->__aggregate->resolve(null);
                }
                return true;
            }
            return false;
        }
    }
}else{die;}