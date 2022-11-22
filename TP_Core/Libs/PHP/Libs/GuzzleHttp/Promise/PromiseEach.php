<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:52
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    final class PromiseEach{
        public static function of($iterable, callable $onFulfilled = null,callable $onRejected = null): string {
            return (new Each($iterable, ['fulfilled' => $onFulfilled,'rejected'  => $onRejected]))->promise();
        }
        public static function ofLimit($iterable,$concurrency,callable $onFulfilled = null,callable $onRejected = null): string{
            return (new Each($iterable, ['fulfilled' => $onFulfilled,'rejected' => $onRejected,'concurrency' => $concurrency]))->promise();
        }
        public static function ofLimitAll($iterable,$concurrency,callable $onFulfilled = null): string{
            return static::ofLimit( $iterable,$concurrency,$onFulfilled,
                static function ($reason, $idx, PromiseInterface $aggregate) {
                    $aggregate->reject($reason);
                    $aggregate->reject($idx);
                });
        }
    }
}else{die;}