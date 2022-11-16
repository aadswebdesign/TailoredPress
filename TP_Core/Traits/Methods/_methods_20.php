<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_20{
        /**
         * todo
         * @description Checks compatibility with the current TailoredPress version.
         * @param $required
         * @return bool
         */
        protected function _tp_is_version_compatible( $required ): bool{
            @list( $version ) = explode( '-', TP_VERSION );
            return empty( $required ) || version_compare( $version, $required, '>=' );
        }//8357
        /**
         * @description Checks compatibility with the current PHP version.
         * @param $required
         * @return bool
         */
        protected function _is_php_version_compatible( $required ): bool{
            return empty( $required ) || version_compare( PHP_VERSION, $required, '>=' );
        }//8374
        /**
         * @description Check if two numbers are nearly the same.
         * @param $expected
         * @param $actual
         * @param int $precision
         * @return bool
         */
        protected function _tp_fuzzy_number_match( $expected, $actual, $precision = 1 ): bool{
            return abs( (float) $expected - (float) $actual ) <= $precision;
        }//8390
        /**
         * or I ever need this?
         * @description Is the server running earlier than 1.5.0 version of lighttpd?
         * @return bool
         */
        protected function _is_light_tpd_before_150(): bool{
            $server_parts    = explode( '/', $_SERVER['SERVER_SOFTWARE'] ?? '' );
            $server_parts[1] = $server_parts[1] ?? '';
            return ( 'lighttpd' === $server_parts[0] && -1 === version_compare( $server_parts[1], '1.5.0' ) );
        }//5807 ?
        protected function _strip_namespace_from_classname($obj){
            if(!$obj){
                return false;
            }
            $classname = get_class($obj);
            if (preg_match('@\\\\([\w]+)$@', $classname, $matches))
                $classname = $matches[1];
            return $classname;
        }//new method
        protected function _tp_array_merge(...$merges): array{
            return array_merge( $merges );
        }//new method todo testing
        protected function _tp_array_merge_recursive(...$merges): array{
            return array_merge_recursive( $merges );
        }//new method todo testing
        /**
         * @description Polyfill for `array_key_last()` function added in PHP 7.3.
         * @param array $arr
         * @return mixed
         */
        protected function _tp_array_key_last( array $arr ){
            if ( empty( $arr ) ) return null;
            end( $arr );
            return key( $arr );
        }//411
        /**
         * @description Polyfill for `str_starts_with()` function added in PHP 8.0.
         * @param $haystack
         * @param $needle
         * @return string
         */
        protected function _tp_str_starts_with($haystack, $needle):string{
            return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
        }//451 as a custom method todo testing
        /**
         * @description Polyfill for `str_ends_with()` function added in PHP 8.0.
         * @param $hay_stack
         * @param $needle
         * @return bool
         */
        protected function _tp_str_ends_with($hay_stack, $needle ): bool{
            if ( '' === $hay_stack && '' !== $needle ) return false;
            $len = strlen( $needle );
            return 0 === substr_compare( $hay_stack, $needle, -$len, $len );
        }//472 as a custom method todo testing
        protected function _compat_microtime($get_as_float = false){
            if (!function_exists('gettimeofday')) {
                $time = time();
                return $get_as_float ? ($time * 1000000.0) : '0.00000000 ' . $time;
            }
            $gtod = gettimeofday();
            $usec = $gtod['usec'] / 1000000.0;
            return $get_as_float ? ($gtod['sec'] + $usec) : (sprintf('%.8f ', $usec) . $gtod['sec']);
        }//new method todo testing
        protected function _tp_microtime($get_as_float = false):mixed{
            return $this->_compat_microtime($get_as_float);
        }
    }
}else die;