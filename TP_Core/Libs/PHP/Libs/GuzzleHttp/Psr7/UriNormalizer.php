<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 08:30
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    final class UriNormalizer{
        public const PRESERVING_NORMALIZATIONS =
            self::CAPITALIZE_PERCENT_ENCODING |
            self::DECODE_UNRESERVED_CHARACTERS |
            self::CONVERT_EMPTY_PATH |
            self::REMOVE_DEFAULT_HOST |
            self::REMOVE_DEFAULT_PORT |
            self::REMOVE_DOT_SEGMENTS;
        public const CAPITALIZE_PERCENT_ENCODING = 1;
        public const DECODE_UNRESERVED_CHARACTERS = 2;
        public const CONVERT_EMPTY_PATH = 4;
        public const REMOVE_DEFAULT_HOST = 8;
        public const REMOVE_DEFAULT_PORT = 16;
        public const REMOVE_DOT_SEGMENTS = 32;
        public const REMOVE_DUPLICATE_SLASHES = 64;
        public const SORT_QUERY_PARAMETERS = 128;
        public static function normalize(UriInterface $uri, int $flags = self::PRESERVING_NORMALIZATIONS): UriInterface{
            if ($flags & self::CAPITALIZE_PERCENT_ENCODING)
                $uri = self::__capitalizePercentEncoding($uri);
            if ($flags & self::DECODE_UNRESERVED_CHARACTERS)
                $uri = self::__decodeUnreservedCharacters($uri);
            if ($flags & self::CONVERT_EMPTY_PATH && $uri->getPath() === '' &&
                ($uri->getScheme() === 'http' || $uri->getScheme() === 'https')
            ) $uri = $uri->withPath('/');
            if ($uri instanceof Uri && $flags & self::REMOVE_DEFAULT_HOST && $uri->getScheme() === 'file' && $uri->getHost() === 'localhost')
                $uri = $uri->withHost('');
            if ($flags & self::REMOVE_DEFAULT_PORT && $uri->getPort() !== null && Uri::isDefaultPort($uri))
                $uri = $uri->withPort(null);
            if ($flags & self::REMOVE_DOT_SEGMENTS && !Uri::isRelativePathReference($uri))
                $uri = $uri->withPath(UriResolver::removeDotSegments($uri->getPath()));
            if ($flags & self::REMOVE_DUPLICATE_SLASHES)
                $uri = $uri->withPath(preg_replace('#//++#', '/', $uri->getPath()));
            if ($flags & self::SORT_QUERY_PARAMETERS && $uri->getQuery() !== '') {
                $queryKeyValues = explode('&', $uri->getQuery());
                sort($queryKeyValues);
                $uri = $uri->withQuery(implode('&', $queryKeyValues));
            }
            return $uri;
        }
        public static function isEquivalent(UriInterface $uri1, UriInterface $uri2, int $normalizations = self::PRESERVING_NORMALIZATIONS): bool{
            return (string) self::normalize($uri1, $normalizations) === (string) self::normalize($uri2, $normalizations);
        }
        private static function __capitalizePercentEncoding(UriInterface $uri): UriInterface{
            $regex = '/(?:%[A-Fa-f0-9]{2})++/';
            $callback = static function (array $match) {
                return strtoupper($match[0]);
            };
            $_uri = null;
            if($uri instanceof Uri){
                $_uri = $uri;
            }
            return
                $_uri->withPath(
                    preg_replace_callback($regex, $callback, $_uri->getPath())
                )->withQuery(
                    preg_replace_callback($regex, $callback, $_uri->getQuery())
                );
        }
        private static function __decodeUnreservedCharacters(UriInterface $uri): UriInterface{
            $regex = '/%(?:2D|2E|5F|7E|3\d|[46][1-9A-F]|[57][0-9A])/i';
            $callback = static function (array $match) {
                return rawurldecode($match[0]);
            };
            $_uri = null;
            if($uri instanceof Uri){
                $_uri = $uri;
            }
            return
                $_uri->withPath(
                    preg_replace_callback($regex, $callback, $_uri->getPath())
                )->withQuery(
                    preg_replace_callback($regex, $callback, $_uri->getQuery())
                );
        }
        private function __construct(){}// cannot be instantiated
    }
}else die;