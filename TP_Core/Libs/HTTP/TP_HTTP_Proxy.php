<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 17:02
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class TP_HTTP_Proxy{
        use _action_01, _filter_01, _option_01;
        public function is_enabled(): bool{
            return defined( 'TP_PROXY_HOST' ) && defined( 'TP_PROXY_PORT' );
        }
        public function use_authentication(): bool {
            return defined( 'TP_PROXY_USERNAME' ) && defined( 'TP_PROXY_PASSWORD' );
        }
        public function host() {
            if ( defined( 'TP_PROXY_HOST' ) ) return TP_PROXY_HOST;
            return '';
        }
        public function port() {
            if ( defined( 'TP_PROXY_PORT' ) ) return TP_PROXY_PORT;
            return '';
        }
        public function username() {
            if ( defined( 'TP_PROXY_USERNAME' ) ) return TP_PROXY_USERNAME;
            return '';
        }
        public function password() {
            if ( defined( 'TP_PROXY_PASSWORD' ) ) return TP_PROXY_PASSWORD;
            return '';
        }
        public function authentication(): string{
            return $this->username() . ':' . $this->password();
        }
        public function authentication_header(): string{
            return 'Proxy-Authorization: Basic ' . base64_encode( $this->authentication() );
        }
        public function send_through_proxy( $uri ): ?bool{
            $check = parse_url( $uri );
            if ( false === $check ) return true;
            $home = parse_url( $this->_get_option( 'siteurl' ) );
            $result = $this->_apply_filters( 'pre_http_send_through_proxy', null, $uri, $check, $home );
            if ( ! is_null( $result ) )return $result;
            if ( 'localhost' === $check['host'] || ( isset( $home['host'] ) && $home['host'] === $check['host'] ) )
                return false;
            if ( ! defined( 'TP_PROXY_BYPASS_HOSTS' ) ) return true;
            static $bypass_hosts   = null;
            static $wildcard_regex = array();
            if ( null === $bypass_hosts ) {
                $bypass_hosts = preg_split( '|,\s*|', TP_PROXY_BYPASS_HOSTS );
                if ( false !== strpos( TP_PROXY_BYPASS_HOSTS, '*' ) ) {
                    $wildcard_regex = array();
                    foreach ( $bypass_hosts as $host )
                        $wildcard_regex[] = str_replace( '\*', '.+', preg_quote( $host, '/' ) );
                    $wildcard_regex = '/^(' . implode( '|', $wildcard_regex ) . ')$/i';
                }
            }
            if ( ! empty( $wildcard_regex ) ) return ! preg_match( $wildcard_regex, $check['host'] );
             else  return ! in_array( $check['host'], $bypass_hosts, true );
        }
    }
}else die;