<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 21:55
 */
namespace TP_Core\Traits\HTTP;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _http_03 {
        use _init_db;
        /**
         * @description Determines if the HTTP origin is an authorized one.
         * @param null $origin
         * @return mixed
         */
        protected function _is_allowed_http_origin( $origin = null ){
            $origin_arg = $origin;
            if ( null === $origin ) $origin = $this->_get_http_origin();
            if ( $origin && ! in_array( $origin, $this->_get_allowed_http_origins(), true ) ) $origin = '';
            return $this->_apply_filters( 'allowed_http_origin', $origin, $origin_arg );
        }//454
        /**
         * @description Send Access-Control-Allow-Origin and related headers if the current request
         * @description . is from an allowed origin.
         * @return bool
         */
        protected function _send_origin_headers():bool{
            $origin = $this->_get_http_origin();
            if ( $this->_is_allowed_http_origin( $origin ) ) {
                header( 'Access-Control-Allow-Origin: ' . $origin );
                header( 'Access-Control-Allow-Credentials: true' );
                if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) exit;
                return $origin;
            }
            if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
                $this->_status_header( 403 );
                exit;
            }
            return false;
        }//489
        /**
         * @description Validate a URL for safe use in the HTTP API.
         * @param $url
         * @return bool
         */
        protected function _tp_http_validate_url( $url ):bool{
            if ( ! is_string( $url ) || '' === $url || is_numeric( $url ) ) return false;
            $original_url = $url;
            $url          = $this->_tp_kses_bad_protocol( $url, array( 'http', 'https' ) );
            if ( ! $url || strtolower( $url ) !== strtolower( $original_url ) ) return false;
            $parsed_url = parse_url( $url );
            if ( ! $parsed_url || empty( $parsed_url['host'] ) )return false;
            if ( isset( $parsed_url['user'] ) || isset( $parsed_url['pass'] ) ) return false;
            if ( false !== strpbrk( $parsed_url['host'], ':#?[]' ) ) return false;
            $parsed_home = parse_url( $this->_get_option( 'home' ) );
            $same_host   = isset( $parsed_home['host'] ) && strtolower( $parsed_home['host'] ) === strtolower( $parsed_url['host'] );
            $host        = trim( $parsed_url['host'], '.' );
            if ( ! $same_host ) {
                if ( preg_match( '#^(([1-9]?\d|1\d\d|25[0-5]|2[0-4]\d)\.){3}([1-9]?\d|1\d\d|25[0-5]|2[0-4]\d)$#', $host ) ) {
                    $ip = $host;
                } else {
                    $ip = gethostbyname( $host );
                    if ( $ip === $host )  return false;
                }
                if ( $ip ) {
                    $parts = array_map( 'intval', explode( '.', $ip ) );
                    if ( 127 === $parts[0] || 10 === $parts[0] || 0 === $parts[0]
                        || ( 172 === $parts[0] && 16 <= $parts[1] && 31 >= $parts[1] )
                        || ( 192 === $parts[0] && 168 === $parts[1] )
                    ) {
                        if ( ! $this->_apply_filters( 'http_request_host_is_external', false, $host, $url ) )
                            return false;
                    }
                }
            }
            if ( empty( $parsed_url['port'] ) ) return $url;
            $port = $parsed_url['port'];
            $allowed_ports = $this->_apply_filters( 'http_allowed_safe_ports', array( 80, 443, 8080 ), $host, $url );
            if ( is_array( $allowed_ports ) && in_array( $port, $allowed_ports, true ) )
                return $url;
            if ( $parsed_home && $same_host && isset( $parsed_home['port'] ) && $parsed_home['port'] === $port )
                return $url;
            return false;
        }//517
        /**
         * @description Mark allowed redirect hosts safe for HTTP requests as well.
         * @param $is_external
         * @param $host
         * @return bool
         */
        protected function _allowed_http_request_hosts( $is_external, $host ):bool{
            if ( ! $is_external && $this->_tp_validate_redirect( 'http://' . $host ) )
                $is_external = true;
            return $is_external;
        }//619
        /**
         * @description  Adds any domain in a multisite installation for safe HTTP requests to the
         * @description  . allowed list.
         * @param $is_external
         * @param $host
         * @return mixed
         */
        protected function _ms_allowed_http_request_hosts( $is_external, $host ){
            static $queried = array();
            $tpdb = $this->_init_db();
            if ( $is_external ) return $is_external;
            if ( $this->_get_network()->domain === $host ) return true;
            if ( isset( $queried[ $host ] ) ) return $queried[ $host ];
            $queried[ $host ] = (bool) $tpdb->get_var( $tpdb->prepare( TP_SELECT . " domain FROM ". $tpdb->blogs . " WHERE domain = %s LIMIT 1", $host ) );
            return $queried[ $host ];
        }//640
        /**
         * @descriptionA wrapper for PHP's parse_url() function that handles consistency in the return values * across PHP versions.
         * @param $url
         * @param int $component
         * @return mixed
         */
        protected function _tp_parse_url( $url, $component = -1 ){
            $to_unset = array();
            $url      = (string) $url;
            if (strpos($url, '//') === 0) {
                $to_unset[] = 'scheme';
                $url        = 'placeholder:' . $url;
            } elseif ( '/' === $url[0]) {
                $to_unset[] = 'scheme';
                $to_unset[] = 'host';
                $url        = 'placeholder://placeholder' . $url;
            }
            $parts = parse_url( $url );
            if ( false === $parts ) return $parts;
            foreach ( $to_unset as $key ) unset( $parts[ $key ] );
            return $this->_get_component_from_parsed_url_array( $parts, $component );
        }//682
        /**
         * @description Retrieve a specific component from a parsed URL array.
         * @param $url_parts
         * @param int $component
         * @return mixed
         */
        protected function _get_component_from_parsed_url_array( $url_parts, $component = -1 ){
            if ( -1 === $component ) return $url_parts;
            $key = $this->_tp_translate_php_url_constant_to_key( $component );
            if ( false !== $key && is_array( $url_parts ) && isset( $url_parts[ $key ] ) )
                return $url_parts[ $key ];
            else return null;
        }//729
        /**
         * @description Translate a PHP_URL_* constant to the named array keys PHP uses.
         * @param $constant
         * @return mixed
         */
        protected function _tp_translate_php_url_constant_to_key( $constant ){
            $translation = array(
                PHP_URL_SCHEME   => 'scheme',
                PHP_URL_HOST     => 'host',
                PHP_URL_PORT     => 'port',
                PHP_URL_USER     => 'user',
                PHP_URL_PASS     => 'pass',
                PHP_URL_PATH     => 'path',
                PHP_URL_QUERY    => 'query',
                PHP_URL_FRAGMENT => 'fragment',
            );
            if ( isset( $translation[ $constant ] ) ) return $translation[ $constant ];
            else return false;
        }//754
    }
}else die;