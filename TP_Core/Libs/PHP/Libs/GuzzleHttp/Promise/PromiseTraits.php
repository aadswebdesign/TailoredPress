<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 03:52
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    trait PromiseTraits{
        public function queue(TaskQueueInterface $assign = null){
            return Utils::queue($assign);
        }
        public function task(callable $task): Promise{
            return Utils::task($task);
        }
        public function promise_for($value){
            return Create::promiseFor($value);
        }
        public function rejection_for($reason): string {
            return Create::rejectionFor($reason);
        }
        public function exception_for($reason): string{
            return Create::exceptionFor($reason);
        }
        public function iterator_for($value): string{
            return Create::iteratorFor($value);
        }
        public function inspect(PromiseInterface $promise): array{
            return Utils::inspect($promise);
        }
        public function inspect_all($promises): array{
            return Utils::inspectAll($promises);
        }
        public function unwrap($promises): array{
            return Utils::unwrap($promises);
        }
        public function all($promises, $recursive = false){
            return Utils::all($promises, $recursive);
        }
        public function some($count, $promises){
            return Utils::some($count, $promises);
        }
        public function any($promises){
            return Utils::any($promises);
        }
        public function settle($promises){
            return Utils::settle($promises);
        }
        public function each($iterable,callable $onFulfilled = null,callable $onRejected = null): string{
            return PromiseEach::of($iterable, $onFulfilled, $onRejected);
        }
        public function each_limit($iterable,$concurrency,callable $onFulfilled = null, callable $onRejected = null): string {
            return PromiseEach::ofLimit($iterable, $concurrency, $onFulfilled, $onRejected);
        }
        public function each_limit_all($iterable,$concurrency,callable $onFulfilled = null): string{
            return PromiseEach::ofLimitAll($iterable, $concurrency, $onFulfilled);
        }
        public function is_fulfilled(PromiseInterface $promise): bool{
            return PromiseIs::fulfilled($promise);
        }
        public function is_rejected(PromiseInterface $promise): bool{
            return PromiseIs::rejected($promise);
        }
        public function is_settled(PromiseInterface $promise): bool{
            return PromiseIs::settled($promise);
        }
        public function coroutine_of(callable $generatorFn): string{
            return Coroutine::of($generatorFn);
        }
    }
}else{die;}

