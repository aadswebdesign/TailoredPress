<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 05:29
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\RequestOptions;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
if(ABSPATH){
    class Proxy{
        public static function wrapSync(callable $default, callable $sync): callable{
            return static function (RequestInterface $request, array $options) use ($default, $sync): PromiseInterface {
                return empty($options[RequestOptions::SYNCHRONOUS]) ? $default($request, $options) : $sync($request, $options);
            };
        }
        public static function wrapStreaming(callable $default, callable $streaming): callable{
            return static function (RequestInterface $request, array $options) use ($default, $streaming): PromiseInterface {
                return empty($options['stream']) ? $default($request, $options) : $streaming($request, $options);
            };
        }
    }
}else {die;}