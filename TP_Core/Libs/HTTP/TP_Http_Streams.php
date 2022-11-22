<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-4-2022
 * Time: 09:04
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_Http_Streams{
        use _filter_01, _methods_10, _I10n_01;
        private $__processed_response, $__error_reporting;
        public function request( $url, $args = [] ){
            $defaults = ['method' => 'GET','timeout' => 5,'redirection' => 5,'http_version' => '1.0',
                'blocking' => true,'headers' => [],'body' => null,'cookies' => [],];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            if ( isset( $parsed_args['headers']['User-Agent'] ) ) {
                $parsed_args['user-agent'] = $parsed_args['headers']['User-Agent'];
                unset( $parsed_args['headers']['User-Agent'] );
            } elseif ( isset( $parsed_args['headers']['user-agent'] ) ) {
                $parsed_args['user-agent'] = $parsed_args['headers']['user-agent'];
                unset( $parsed_args['headers']['user-agent'] );
            }
            TP_Http::buildCookieHeader( $parsed_args );
            $parsed_url = parse_url( $url );
            $connect_host = $parsed_url['host'];
            $secure_transport = ( 'ssl' === $parsed_url['scheme'] || 'https' === $parsed_url['scheme'] );
            if ( ! isset( $parsed_url['port'] ) ) {
                if ( 'ssl' === $parsed_url['scheme'] || 'https' === $parsed_url['scheme'] ) {
                    $parsed_url['port'] = 443;
                    $secure_transport   = true;
                } else $parsed_url['port'] = 80;
            }
            if ( ! isset( $parsed_url['path'] ) ) $parsed_url['path'] = '/';
            if ( isset( $parsed_args['headers']['Host'] ) || isset( $parsed_args['headers']['host'] ) ) {
                if ( isset( $parsed_args['headers']['Host'] ) )
                    $parsed_url['host'] = $parsed_args['headers']['Host'];
                else $parsed_url['host'] = $parsed_args['headers']['host'];
                unset( $parsed_args['headers']['Host'], $parsed_args['headers']['host'] );
            }//80
            //todo  adding a range of .local ip addresses
            if ( 'localhost' === strtolower( $connect_host ) ) $connect_host = '127.0.0.1';
            $connect_host = $secure_transport ? 'ssl://' . $connect_host : 'tcp://' . $connect_host;
            $is_local   = isset( $parsed_args['local'] ) && $parsed_args['local'];
            $ssl_verify = isset( $parsed_args['ssl_verify'] ) && $parsed_args['ssl_verify'];
            if ( $is_local ) $ssl_verify = $this->_apply_filters( 'https_local_ssl_verify', $ssl_verify, $url );
            elseif ( ! $is_local ) $ssl_verify = $this->_apply_filters( 'https_ssl_verify', $ssl_verify, $url );
            $proxy = new TP_HTTP_Proxy();
            $context = stream_context_create(
               ['ssl' => ['verify_peer' => $ssl_verify,'capture_peer_cert' => $ssl_verify,'SNI_enabled' => true,'ca_file' => $parsed_args['ssl_certificates'],'allow_self_signed' => ! $ssl_verify,],]
            );//125
            $timeout         = (int) floor( $parsed_args['timeout'] );
            $u_timeout        = $timeout === $parsed_args['timeout'] ? 0 : 1000000 * $parsed_args['timeout'] % 1000000;
            $connect_timeout = max( $timeout, 1 );
            $connection_error = null;
            $connection_error_str = null;
            if ( ! TP_DEBUG ){
                if ( $secure_transport ) $this->__error_reporting = error_reporting( 0 );
                if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
                    $handle = @stream_socket_client('tcp://' . $proxy->host() . ':' . $proxy->port(),$connection_error,$connection_error_str,$connect_timeout,STREAM_CLIENT_CONNECT,$context);
                }else {
                    $handle = @stream_socket_client($connect_host . ':' . $parsed_url['port'],$connection_error,$connection_error_str,$connect_timeout,STREAM_CLIENT_CONNECT,$context);
                }
                if ( $secure_transport ) error_reporting( $this->__error_reporting );
            }else if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
                $handle = stream_socket_client('tcp://' . $proxy->host() . ':' . $proxy->port(),$connection_error,$connection_error_str,$connect_timeout,STREAM_CLIENT_CONNECT,$context);
            } else {
                $handle = stream_socket_client($connect_host . ':' . $parsed_url['port'],$connection_error,$connection_error_str,$connect_timeout,STREAM_CLIENT_CONNECT,$context);
            }//168
            if ( false === $handle ) {
                if ( $secure_transport && 0 === $connection_error && '' === $connection_error_str )
                    return new TP_Error( 'http_request_failed', $this->__( 'The SSL certificate for the host could not be verified.' ) );
                return new TP_Error( 'http_request_failed', $connection_error . ': ' . $connection_error_str );
            }
            if ($secure_transport && $ssl_verify && !$proxy->is_enabled() && !self::verify_ssl_certificate($handle, $parsed_url['host'])) {
                return new TP_Error( 'http_request_failed', $this->__( 'The SSL certificate for the host could not be verified.' ) );
            }
            stream_set_timeout( $handle, $timeout, $u_timeout ); //214
            if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) )
                $request_path = $url;
            else $request_path = $parsed_url['path'] . ( isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '' );
            $headers = strtoupper( $parsed_args['method'] ) . ' ' . $request_path . ' HTTP/' . $parsed_args['http_version'] . "\r\n";
            $include_port_in_host_header = (
                ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) )
                || ( 'http' === $parsed_url['scheme'] && 80 !== $parsed_url['port'] )
                || ( 'https' === $parsed_url['scheme'] && 443 !== $parsed_url['port'] )
            );//220
            if ( $include_port_in_host_header )
                $headers .= 'Host: ' . $parsed_url['host'] . ':' . $parsed_url['port'] . "\r\n";
            else $headers .= 'Host: ' . $parsed_url['host'] . "\r\n";//221
            if ( isset( $parsed_args['user-agent'] ) )
                $headers .= 'User-agent: ' . $parsed_args['user-agent'] . "\r\n";
            if (is_array($parsed_args['headers'])) {
                foreach ($parsed_args['headers'] as $header => $header_value)
                    $headers .= $header . ': ' . $header_value . "\r\n";
            } else  $headers .= $parsed_args['headers'];
            if ($proxy->use_authentication()) $headers .= $proxy->authentication_header() . "\r\n";
            $headers .= "\r\n";
            if ( ! is_null( $parsed_args['body'] ) ) $headers .= $parsed_args['body'];
            fwrite( $handle, $headers );
            if ( ! $parsed_args['blocking'] ) {
                stream_set_blocking( $handle, 0 );
                fclose( $handle );
                return ['headers'  => [],'body' => '','response' => ['code' => false, 'message' => false,],'cookies'  => [],];
            }
            $response     = '';
            $body_started = false;
            $keep_reading = true;
            $block_size   = 4096;
            if ( isset( $parsed_args['limit_response_size'] ) )
                $block_size = min( $block_size, $parsed_args['limit_response_size'] );
            if ( $parsed_args['stream'] ) {
                if ( ! TP_DEBUG ) $stream_handle = @fopen( $parsed_args['filename'], 'wb+' );
                else $stream_handle = fopen( $parsed_args['filename'], 'wb+' );
                if ( ! $stream_handle ) {
                    return new TP_Error(
                        'http_request_failed',
                        sprintf( $this->__( 'Could not open handle for %1$s to %2$s.' ),'fopen()',$parsed_args['filename'])
                    );
                }
                $bytes_written = 0; //295
                while ( ! feof( $handle ) && $keep_reading ) {
                    $block = fread( $handle, $block_size );
                    if ( ! $body_started ) {
                        $response .= $block;
                        if ( strpos( $response, "\r\n\r\n" ) ) {
                            $processed_response = TP_Http::processResponse( $response );
                            $body_started       = true;
                            $block              = $processed_response['body'];
                            unset( $response );
                            $this->__processed_response['body'] = '';
                        }
                    }
                    $this_block_size = strlen( $block );
                    if ( isset( $parsed_args['limit_response_size'] )&& ( $bytes_written + $this_block_size ) > $parsed_args['limit_response_size']) {
                        $this_block_size = ( $parsed_args['limit_response_size'] - $bytes_written );
                        $block           = substr( $block, 0, $this_block_size );
                    }
                    $bytes_written_to_file = fwrite( $stream_handle, $block );

                    if ( $bytes_written_to_file !== $this_block_size ) {
                        fclose( $handle );
                        fclose( $stream_handle );
                        return new TP_Error( 'http_request_failed', $this->__( 'Failed to write request to temporary file.' ) );
                    }
                    $bytes_written += $bytes_written_to_file;
                    $keep_reading = (! isset( $parsed_args['limit_response_size'] )|| $bytes_written < $parsed_args['limit_response_size']);
                }
                fclose( $stream_handle );//335
            }else{//276
                $header_length = 0;
                while ( ! feof( $handle ) && $keep_reading ) {
                    $block     = fread( $handle, $block_size );
                    $response .= $block;
                    if ( ! $body_started && strpos( $response, "\r\n\r\n" ) ) {
                        $header_length = strpos( $response, "\r\n\r\n" ) + 4;
                        $body_started  = true;
                    }
                    $keep_reading = ( ! $body_started || ! isset( $parsed_args['limit_response_size'] )|| strlen( $response ) < ( $header_length + $parsed_args['limit_response_size'] ));
                }
                $this->__processed_response = TP_Http::processResponse( $response );
                unset( $response );
            }
            fclose( $handle );
            $processed_headers = TP_Http::processHeaders( $this->__processed_response['headers'], $url );
            $response = array(
                'headers'  => $processed_headers['headers'],
                'body'     => null,
                'response' => $processed_headers['response'],
                'cookies'  => $processed_headers['cookies'],
                'filename' => $parsed_args['filename'],
            );
            $redirect_response = TP_Http::handle_redirects( $url, $parsed_args, $response );
            if ( false !== $redirect_response ) return $redirect_response;
            if ( ! empty( $processed_response['body'] )
                && isset( $processed_headers['headers']['transfer-encoding'] )
                && 'chunked' === $processed_headers['headers']['transfer-encoding']
            ) $processed_response['body'] = TP_Http::chunkTransferDecode( $processed_response['body'] );
            if ( true === $parsed_args['decompress'] && true === TP_Http_Encoding::should_decode( $processed_headers['headers'] ))
                $processed_response['body'] = TP_Http_Encoding::decompress( $processed_response['body'] );
            if ( isset( $parsed_args['limit_response_size'] )
                && strlen( $processed_response['body'] ) > $parsed_args['limit_response_size']
            )  $processed_response['body'] = substr( $processed_response['body'], 0, $parsed_args['limit_response_size'] );
            $response['body'] = $processed_response['body'];
            return $response;
        }//29
        public static function verify_ssl_certificate( $stream, $host ):bool{
            $context_options = stream_context_get_options( $stream );
            if ( empty( $context_options['ssl']['peer_certificate'] ) )return false;
            $cert = openssl_x509_parse( $context_options['ssl']['peer_certificate'] );
            if ( ! $cert ) return false;
            $host_type = ( TP_Http::is_ip_address( $host ) ? 'ip' : 'dns' );
            $certificate_host_names = array();
            if ( ! empty( $cert['extensions']['subjectAltName'] ) ) {
                $match_against = preg_split( '/,\s*/', $cert['extensions']['subjectAltName'] );
                foreach ( $match_against as $match ) {
                    @list( $match_type, $match_host ) = explode( ':', $match );
                    if ( strtolower( trim( $match_type ) ) === $host_type ) // IP: or DNS:
                        $certificate_host_names[] = strtolower( trim( $match_host ) );
                }
            } elseif ( ! empty( $cert['subject']['CN'] ) ) // Only use the CN when the certificate includes no subjectAltName extension.
                $certificate_host_names[] = strtolower( $cert['subject']['CN'] );
            if ( in_array( strtolower( $host ), $certificate_host_names, true ) ) return true;
            // IP's can't be wildcards, Stop processing.
            if ( 'ip' === $host_type ) return false;
            // Test to see if the domain is at least 2 deep for wildcard support.
            if ( substr_count( $host, '.' ) < 2 ) return false;
            // Wildcard sub domains certs (*.example.com) are valid for a.example.com but not a.b.example.com.
            $wildcard_host = preg_replace( '/^[^.]+\./', '*.', $host );
            return in_array( strtolower( $wildcard_host ), $certificate_host_names, true );
        }//421
        public static function test( $args = array() ) {
            if ( ! function_exists( 'stream_socket_client' ) ) return false;
            $is_ssl = isset( $args['ssl'] ) && $args['ssl'];
            if ( $is_ssl ) {
                if ( ! extension_loaded( 'openssl' ) ) return false;
                if ( ! function_exists( 'openssl_x509_parse' ) ) return false;
            }
            return (new self)->_apply_filters( 'use_streams_transport', true, $args );
        }//483
    }
}else die;