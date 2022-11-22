<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 16:29
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\Each;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromisorInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    class Pool implements PromisorInterface{
        private $__each;
        public function __construct(ClientInterface $client, $requests, array $config = []){
            if (!isset($config['concurrency'])) $config['concurrency'] = 25;
            if (isset($config['options'])) {
                $opts = $config['options'];
                unset($config['options']);
            } else  $opts = [];
            $iterable = P\Create::iteratorFor($requests);
            $requests = static function () use ($iterable, $client, $opts) {
                foreach ($iterable as $key => $rfn) {
                    if ($rfn instanceof RequestInterface)
                        yield $key => $client->sendAsync($rfn, $opts);
                    elseif (\is_callable($rfn)) yield $key => $rfn($opts);
                    else throw new \InvalidArgumentException('Each value yielded by the iterator must be a Psr7\Http\Message\RequestInterface or a callable that returns a promise that fulfills with a Psr7\Message\Http\ResponseInterface object.');
                }
            };
            $this->__each = new Each($requests(), $config);
        }
        public function promise(): PromiseInterface{
            $promise = null;
            if( $this->__each instanceof P\Promise ){
                $promise = $this->__each;
            }
            return $promise->promise();
        }
        public static function batch(ClientInterface $client, $requests, array $options = []): array{
            $res = [];
            self::__cmpCallback($options, 'fulfilled', $res);
            self::__cmpCallback($options, 'rejected', $res);
            $pool = new static($client, $requests, $options);
            $_pool_promise = $pool->promise();
            $pool_promise = null;
            if($_pool_promise instanceof PromiseInterface){
                $pool_promise = $_pool_promise;
            }
            $pool_promise->wait();
            \ksort($res);
            return $res;
        }
        private static function __cmpCallback(array &$options, string $name, array &$results): void{
            if (!isset($options[$name]))
                $options[$name] = static function ($v, $k) use (&$results) {
                    $results[$k] = $v;
                };
            else {
                $currentFn = $options[$name];
                $options[$name] = static function ($v, $k) use (&$results, $currentFn) {
                    $currentFn($v, $k);
                    $results[$k] = $v;
                };
            }
            return null;
        }
    }
}else die;