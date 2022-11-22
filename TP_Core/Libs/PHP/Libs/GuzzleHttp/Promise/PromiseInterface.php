<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:24
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    interface PromiseInterface{
        public const PENDING = 'pending';
        public const FULFILLED = 'fulfilled';
        public const REJECTED = 'rejected';
        public function then(
            callable $onFulfilled = null,
            callable $onRejected = null
        );
        public function otherwise(callable $onRejected);
        public function getState();
        public function resolve($value);
        public function reject($reason);
        public function cancel();
        public function wait($unwrap = true);
    }
}else{die;}