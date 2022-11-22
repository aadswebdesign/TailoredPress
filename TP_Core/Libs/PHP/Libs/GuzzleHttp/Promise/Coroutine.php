<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:22
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
use Exception;
use Generator;
use Throwable;
if(ABSPATH){
    final class Coroutine implements PromiseInterface{
        private $__currentPromise;
        private $__generator;
        private $__result;
        public function __construct(callable $generatorFn){
            $this->__generator = $generatorFn();
            $generator = null;
            if($this->__generator instanceof Generator){
                $generator = $this->__generator;
            }
            $this->__result = new Promise(function () {
                if($this->__currentPromise instanceof PromiseInterface){
                    while (isset($this->__currentPromise)){$this->__currentPromise->wait();}
                }
            });
            try {
                $this->__nextCoroutine($generator->current());
            } catch (\Exception $exception) {
                $this->__result->reject($exception);
            } catch (Throwable $throwable) {
                $this->__result->reject($throwable);
            }
        }
        public static function of(callable $generatorFn):string{
            return new self($generatorFn);
        }
        public function then(callable $onFulfilled = null,callable $onRejected = null) {
            return $this->__result->then($onFulfilled, $onRejected);
        }
        public function otherwise(callable $onRejected){
            return $this->__result->otherwise($onRejected);
        }
        public function wait($unwrap = true){
            return $this->__result->wait($unwrap);
        }
        public function getState():string{
            return $this->__result->getState();
        }
        public function resolve($value):string{
            $this->__result->resolve($value);
            return null;
        }
        public function reject($reason):string{
            $this->__result->reject($reason);
            return null;
        }
        public function cancel():bool{
            if($this->__currentPromise instanceof PromiseInterface){
                $this->__currentPromise->cancel();
            }
            $this->__result->cancel();
            return null;
        }
        protected function _handleSuccess($value): string{
            unset($this->currentPromise);
            $generator = null;
            if($this->__generator instanceof Generator){
                $generator = $this->__generator;
            }
            try {
                $next = $generator->send($value);
                if ($generator->valid()){$this->__nextCoroutine($next);}
                else {$this->__result->resolve($value);}
            } catch (Exception $exception) {
                $this->__result->reject($exception);
            } catch (Throwable $throwable) {
                $this->__result->reject($throwable);
            }
            return null;
        }
        protected function _handleFailure($reason):string {
            $generator = null;
            if($this->__generator instanceof Generator){
                $generator = $this->__generator;
            }
            unset($this->currentPromise);
            try {
                $nextYield = $generator->throw(Create::exceptionFor($reason));
                // The throw was caught, so keep iterating on the coroutine
                $this->__nextCoroutine($nextYield);
            } catch (Exception $exception) {
                $this->__result->reject($exception);
            } catch (Throwable $throwable) {
                $this->__result->reject($throwable);
            }
            return null;
        }
        private function __nextCoroutine ($yielded): string{
            $this->__currentPromise = Create::promiseFor($yielded)
                ->then([$this, '_handleSuccess'], [$this, '_handleFailure']);
            return null;
        }
    }
}else{die;}