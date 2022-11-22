<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 21:18
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
if(ABSPATH){
    final class Header{
        public static function parse($header): array{
            static $trimmed = "\"'  \n\t\r";
            $params = $matches = [];
            foreach (self::normalize($header) as $val) {
                $part = [];
                foreach (preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $val) as $kvp) {
                    if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                        $m = $matches[0];
                        if (isset($m[1]))
                            $part[trim($m[0], $trimmed)] = trim($m[1], $trimmed);
                        else $part[] = trim($m[0], $trimmed);
                    }
                }
                if ($part) $params[] = $part;
            }
            return $params;
        }
        public static function normalize($header): array{
            $result = [];
            foreach ((array) $header as $value) {
                foreach ((array) $value as $v) {
                    if (strpos($v, ',') === false) {
                        $trimmed = trim($v);
                        if ($trimmed !== '') $result[] = $trimmed;
                        continue;
                    }
                    foreach (preg_split('/,(?=([^"]*"([^"]|\\\\.)*")*[^"]*$)/', $v) as $vv) {
                        $trimmed = trim($vv);
                        if ($trimmed !== '') $result[] = $trimmed;
                    }
                }
            }
            return $result;
        }
    }
}else die;