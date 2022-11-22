<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:12
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    final class PromiseIs{
        public static function pending(PromiseInterface $promise): bool
        {
            return $promise->getState() === PromiseInterface::PENDING;
        }
        public static function settled(PromiseInterface $promise): bool
        {
            return $promise->getState() !== PromiseInterface::PENDING;
        }
        public static function fulfilled(PromiseInterface $promise): bool{
            return $promise->getState() === PromiseInterface::FULFILLED;
        }
        public static function rejected(PromiseInterface $promise): bool{
            return $promise->getState() === PromiseInterface::REJECTED;
        }
    }
}else{die;}