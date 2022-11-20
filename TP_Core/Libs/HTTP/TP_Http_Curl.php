<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 16:43
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_10;
if(ABSPATH){
    class TP_Http_Curl{
        use _action_01;
        use _filter_01;
        use _methods_10;
        use _I10n_01;
        private $__bytes_written_total = 0;
        private $__body = '';
        private $__handle;
        private $__headers = '';
        private $__max_body_length = false;
        private $__stream_handle;
        public function request( $url, $args = array() ){
            $defaults = ['method' => 'GET','timeout' => 5,'redirection' => 5,'http_version' => '1.0','blocking' => true,'headers' => [],'body' => null,'cookies' => [],];
            static $handle;
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            if ( isset( $parsed_args['headers']['User-Agent'] ) ) {
                $parsed_args['user-agent'] = $parsed_args['headers']['User-Agent'];
                unset( $parsed_args['headers']['User-Agent'] );
            } elseif ( isset( $parsed_args['headers']['user-agent'] ) ) {
                $parsed_args['user-agent'] = $parsed_args['headers']['user-agent'];
                unset( $parsed_args['headers']['user-agent'] );
            }
            TP_Http::buildCookieHeader( $parsed_args );
            $proxy = new TP_HTTP_Proxy();
            if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
                curl_setopt( $handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
                curl_setopt( $handle, CURLOPT_PROXY, $proxy->host() );
                curl_setopt( $handle, CURLOPT_PROXYPORT, $proxy->port() );
                if ( $proxy->use_authentication() ) {
                    curl_setopt( $handle, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
                    curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
                }
            }
            $is_local   = isset( $parsed_args['local'] ) && $parsed_args['local'];
            $ssl_verify = isset( $parsed_args['ssl_verify'] ) && $parsed_args['ssl_verify'];
            if ( $is_local ) $ssl_verify = $this->_apply_filters( 'https_local_ssl_verify', $ssl_verify, $url );
            elseif ( ! $is_local ) $ssl_verify = $this->_apply_filters( 'https_ssl_verify', $ssl_verify, $url );
            $timeout = (int) ceil( $parsed_args['timeout'] );
            curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $timeout );
            curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );
            curl_setopt( $handle, CURLOPT_URL, $url );
            curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, ( true === $ssl_verify ) ? 2 : false );
            curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
            if ( $ssl_verify ) curl_setopt( $handle, CURLOPT_CAINFO, $parsed_args['ssl_certificates'] );
            curl_setopt( $handle, CURLOPT_USERAGENT, $parsed_args['user-agent'] );
            curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, false );
            curl_setopt( $handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS );
            switch ( $parsed_args['method'] ) {
                case 'HEAD':
                    curl_setopt( $handle, CURLOPT_NOBODY, true );
                    break;
                case 'POST':
                    curl_setopt( $handle, CURLOPT_POST, true );
                    curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
                    break;
                case 'PUT':
                    curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, 'PUT' );
                    curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
                    break;
                default:
                    curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, $parsed_args['method'] );
                    if ( ! is_null( $parsed_args['body'] ) )
                        curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
                    break;
            }
            if ( true === $parsed_args['blocking'] ) {
                curl_setopt( $handle, CURLOPT_HEADERFUNCTION, array( $this, 'stream_headers' ) );
                curl_setopt( $handle, CURLOPT_WRITEFUNCTION, array( $this, 'stream_body' ) );
            }
            curl_setopt( $handle, CURLOPT_HEADER, false );
            if ( isset( $parsed_args['limit_response_size'] ) )
                $this->__max_body_length = (int) $parsed_args['limit_response_size'];
            else $this->__max_body_length = false;
            if ( $parsed_args['stream'] ) {
                if ( ! TP_DEBUG ) $this->__stream_handle = @fopen( $parsed_args['filename'], 'wb+' );
                else $this->__stream_handle = fopen( $parsed_args['filename'], 'wb+' );
                if ( ! $this->__stream_handle ) {
                    return new TP_Error(
                        'http_request_failed',
                        sprintf($this->__( 'Could not open handle for %1$s to %2$s.' ),'fopen()',$parsed_args['filename']));
                }
            } else $this->__stream_handle = false;
            if ( ! empty( $parsed_args['headers'] ) ) {
                $headers = array();
                foreach ( $parsed_args['headers'] as $name => $value )
                    $headers[] = "{$name}: $value";
                curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
            }
            if ( '1.0' === $parsed_args['http_version'] )
                curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
            else curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
            $this->_do_action_ref_array( 'http_api_curl', array( &$handle, $parsed_args, $url ) );
            if ( ! $parsed_args['blocking'] ) {
                curl_exec( $handle );
                $curl_error = curl_error( $handle );
                if ( $curl_error ) {
                    curl_close( $handle );
                    return new TP_Error( 'http_request_failed', $curl_error );
                }
                if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array( 301, 302 ), true ) ) {
                    curl_close( $handle );
                    return new TP_Error( 'http_request_failed', $this->__( 'Too many redirects.' ) );
                }
                curl_close( $handle );
                return ['headers'  => [],'body' => '','response' => ['code' => false,'message' => false,],'cookies' => [],];
            }
            curl_exec( $handle );
            $processed_headers   = TP_Http::processHeaders( $this->__headers, $url );
            $body                = $this->__body;
            $bytes_written_total = $this->__bytes_written_total;
            $this->__headers             = '';
            $this->__body                = '';
            $this->__bytes_written_total = 0;
            $curl_error = curl_errno( $handle );
            if ( $curl_error || ( $body === '' && empty( $processed_headers['headers'] ) ) ) {
                if ( CURLE_WRITE_ERROR /* 23 */ === $curl_error ) {
                    if ( ! $this->__max_body_length || $this->__max_body_length !== $bytes_written_total ) {
                        if ( $parsed_args['stream'] ) {
                            curl_close( $handle );
                            fclose( $this->__stream_handle );
                            return new TP_Error( 'http_request_failed', $this->__( 'Failed to write request to temporary file.' ) );
                        }
                        curl_close( $handle );
                        return new TP_Error( 'http_request_failed', curl_error( $handle ) );
                    }
                } else {
                    $curl_error = curl_error( $handle );
                    if ( $curl_error ) {
                        curl_close( $handle );
                        return new TP_Error( 'http_request_failed', $curl_error );
                    }
                }
                if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array( 301, 302 ), true ) ) {
                    curl_close( $handle );
                    return new TP_Error( 'http_request_failed', $this->__( 'Too many redirects.' ) );
                }
            }
            curl_close( $handle );
            if ( $parsed_args['stream'] ) fclose( $this->__stream_handle );
            $response = array(
                'headers'  => $processed_headers['headers'],
                'body'     => null,
                'response' => $processed_headers['response'],
                'cookies'  => $processed_headers['cookies'],
                'filename' => $parsed_args['filename'],
            );
            // Handle redirects.
            $redirect_response = TP_Http::handle_redirects( $url, $parsed_args, $response );
            if ( false !== $redirect_response ) return $redirect_response;
            if ( true === $parsed_args['decompress'] && true === TP_Http_Encoding::should_decode( $processed_headers['headers'] ))
                $body = TP_Http_Encoding::decompress( $body );
            $response['body'] = $body;
            return $response;
        }//70
        private function __stream_headers( $handle, $headers ): int{
            $this->__handle = $handle;//todo
            $this->__headers .= $headers;
            return strlen( $headers );
        }//339
        public function stream_headers( $handle = null, $headers ): int{
            return $this->__stream_headers( $handle, $headers );
        }
        private function __stream_body( $handle, $data ){
            $this->__handle = $handle;//todo
            $data_length = strlen( $data );
            if ( $this->__max_body_length && ( $this->__bytes_written_total + $data_length ) > $this->__max_body_length ) {
                $data_length = ( $this->__max_body_length - $this->__bytes_written_total );
                $data        = substr( $data, 0, $data_length );
            }
            if ( (string)$this->__stream_handle )
                $bytes_written = fwrite( $this->__stream_handle, $data );
            else {
                $this->__body   .= $data;
                $bytes_written = $data_length;
            }
            $this->__bytes_written_total += $bytes_written;
            return $bytes_written;
        }//357
        public function stream_body( $handle, $data ){
            return $this->__stream_body( $handle, $data );
        }
        public static function test( $args = array() ){
            if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) )
                return false;
            $is_ssl = isset( $args['ssl'] ) && $args['ssl'];
            if ( $is_ssl ) {
                $curl_version = curl_version();
                if ( ! ( CURL_VERSION_SSL & $curl_version['features'] ) ) return false;
            }
            return (new static)->_apply_filters( 'use_curl_transport', true, $args );
        }//386
    }
}else die;