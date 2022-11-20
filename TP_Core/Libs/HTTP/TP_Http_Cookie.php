<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 15:50
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Filters\_filter_01;
if(ABSPATH){
    class TP_Http_Cookie{
        use _filter_01;
        public $domain;
        public $expires;
        public $host_only;
        public $name;
        public $path;
        public $port;
        public $value;
        public function __construct( $data, $requested_url = '' ){
            if ( $requested_url ) $parsed_url = parse_url( $requested_url );
            if ( isset( $parsed_url['host'] ) ) $this->domain = $parsed_url['host'];
            $this->path = isset( $parsed_url['path'] ) ?: '/';
            if ( '/' !== substr( $this->path, -1 ) ) $this->path = dirname( $this->path ) . '/';
            if ( is_string( $data ) ) {
                $pairs = explode( ';', $data );
                $name        = trim( substr( $pairs[0], 0, strpos( $pairs[0], '=' ) ) );
                $value       = substr( $pairs[0], strpos( $pairs[0], '=' ) + 1 );
                $this->name  = $name;
                $this->value = urldecode( $value );
                array_shift( $pairs );
                foreach ( $pairs as $pair ) {
                    $pair = rtrim( $pair );
                    if ( empty( $pair ) ) continue;
                    @list( $key, $val ) = strpos( $pair, '=' ) ? explode( '=', $pair ) : array( $pair, '' );
                    $key               = strtolower( trim( $key ) );
                    if ( 'expires' === $key ) $val = strtotime( $val );
                    $this->$key = $val;
                }
            } else {
                if ( ! isset( $data['name'] ) ) return;
                foreach ( array( 'name', 'value', 'path', 'domain', 'port', 'host_only' ) as $field ) {
                    if ( isset( $data[ $field ] ) ) $this->$field = $data[ $field ];
                }
                if ( isset( $data['expires'] ) )
                    $this->expires = is_int( $data['expires'] ) ? $data['expires'] : strtotime( $data['expires'] );
                else $this->expires = null;
            }
        }//109
        public function test( $url ): bool{
            if ( is_null( $this->name ) ) return false;
            if ( isset( $this->expires ) && time() > $this->expires ) return false;
            $url         = parse_url( $url );
            $url['port'] = $url['port'] ?? ( 'https' === $url['scheme'] ? 443 : 80 );
            $url['path'] = $url['path'] ?? '/';
            $path   = $this->path ?? '/';
            $port   = $this->port ?? null;
            $domain = isset( $this->domain ) ? strtolower( $this->domain ) : strtolower( $url['host'] );
            if ( false === strpos( $domain, '.' ) )$domain .= '.local';
            $domain = ( '.' === $domain[0]) ? substr( $domain, 1 ) : $domain;
            if ( substr( $url['host'], -strlen( $domain ) ) !== $domain )
                return false;
            if ( ! empty( $port ) && ! in_array( $url['port'], array_map( 'intval', explode( ',', $port ) ), true ) )
                return false;
            if (strpos($url['path'], $path) !== 0) return false;
            return true;
        }//180
        public function getHeaderValue(): string{
            if ( ! isset( $this->name,$this->value )) return '';
            return $this->name . '=' . $this->_apply_filters( 'tp_http_cookie_value', $this->value, $this->name );
        }//229
        public function getFullHeader(): string{
            return 'Cookie: ' . $this->getHeaderValue();
        }//252
        public function get_attributes(): array{
            return ['expires' => $this->expires,'path' => $this->path,'domain' => $this->domain,];
        }//269
    }
}else die;