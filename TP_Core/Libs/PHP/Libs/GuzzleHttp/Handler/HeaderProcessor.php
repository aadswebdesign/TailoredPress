<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-4-2022
 * Time: 16:45
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Utils;
if(ABSPATH){
    final class HeaderProcessor{
        public static function parseHeaders(array $headers): array{
            if ($headers === []){throw new \RuntimeException('Expected a non-empty array of header data');}
            $parts = null;
            if($headers instanceof \Iterator){
                $parts = \explode(' ', \array_shift($headers), 3);
            }
            $version = \explode('/', $parts[0])[1] ?? null;
            if ($version === null){
                throw new \RuntimeException('HTTP version missing from header data');
            }
            $status = $parts[1] ?? null;
            if ($status === null){throw new \RuntimeException('HTTP status code missing from header data');}
            return [$version, (int) $status, $parts[2] ?? null, Utils::headersFromLines($headers)];
        }
    }
}else {die;}