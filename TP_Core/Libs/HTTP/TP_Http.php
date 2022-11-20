<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 08:35
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Request\TP_Requests;
use TP_Core\Libs\Request\Requests_Cookie;
use TP_Core\Libs\Request\Exception\Requests_Exception;
use TP_Core\Libs\Request\Cookie\Requests_Cookie_Jar;
use TP_Core\Libs\Request\Proxy\Requests_Proxy_HTTP;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_03;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\K_Ses\_k_ses_02;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
if(ABSPATH){
    class TP_Http{
        use _init_error, _action_01, _filter_01, _option_01,_load_04;
        use _methods_06, _methods_10, _methods_16, _methods_17,_methods_21;
        use _general_template_02,_link_template_09,_link_template_10;
        use _I10n_01,_I10n_02,_I10n_03,_I10n_04, _http_01, _http_03, _k_ses_02;
        public function request( $url, $args = array() ){
            TP_Requests::set_certificate_path( TP_CORE_ASSETS . '/certificates/ca-bundle.crt' );
            $defaults = [
                'method' => 'GET',
                'timeout' => $this->_apply_filters('http_request_timeout', 5, $url),
                'redirection' => $this->_apply_filters('http_request_redirection_count', 5, $url),
                'http_version' => $this->_apply_filters('http_request_version', '1.0', $url),
                'user-agent' => $this->_apply_filters('http_headers_useragent', 'TailoredPress/' . $this->_get_bloginfo('version') . '; ' . $this->_get_bloginfo('url'), $url),
                'reject_unsafe_urls' => $this->_apply_filters('http_request_reject_unsafe_urls', false, $url),'blocking' => true,'headers' => [],
                'cookies' => [],'body' => null,'compress' => false,'decompress' => true, 'ssl_verify' => true,
                'ssl_certificates' => TP_CORE_ASSETS . '/certificates/ca-bundle.crt','stream' => false,'filename' => null,'limit_response_size' => null,
            ];
            $args = $this->_tp_parse_args($args);
            if (isset($args['method']) && 'HEAD' === $args['method']) $defaults['redirection'] = 0;
            $parsed_args = $this->_tp_parse_args($args, $defaults);
            $parsed_args = $this->_apply_filters('http_request_args', $parsed_args, $url);
            if (!isset($parsed_args['_redirection'])) $parsed_args['_redirection'] = $parsed_args['redirection'];
            $pre = $this->_apply_filters('pre_http_request', false, $parsed_args, $url);
            if (false !== $pre) return $pre;
            if (function_exists('__tp_kses_bad_protocol')) {
                if ($parsed_args['reject_unsafe_urls'])  $url = $this->_tp_http_validate_url($url);
                if ($url) $url = $this->_tp_kses_bad_protocol($url, array('http', 'https', 'ssl'));
            }
            $parsed_url = parse_url($url);
            if (empty($url) || empty($parsed_url['scheme'])) {
                $response = new TP_Error('http_request_failed', $this->__('A valid URL was not provided.'));
                $this->_do_action('http_api_debug', $response, 'response', 'Requests', $parsed_args, $url);
                return $response;
            }
            if ($this->block_request($url)) {
                $response = new TP_Error('http_request_not_executed', $this->__('User has blocked requests through HTTP.'));
                /** This action is documented in wp-includes/class-wp-http.php */
                $this->_do_action('http_api_debug', $response, 'response', 'Requests', $parsed_args, $url);
                return $response;
            }
            if ($parsed_args['stream']) {
                if (empty($parsed_args['filename']))
                    $parsed_args['filename'] = $this->_get_temp_dir() . basename($url);
                $parsed_args['blocking'] = true;
                if (!$this->_tp_is_writable(dirname($parsed_args['filename']))) {
                    $response = new TP_Error('http_request_failed', $this->__('Destination directory for file streaming does not exist or is not writable.'));
                    $this->_do_action('http_api_debug', $response, 'response', 'Requests', $parsed_args, $url);
                    return $response;
                }
            }
            if (is_null($parsed_args['headers'])) $parsed_args['headers'] = [];
            if (!is_array($parsed_args['headers'])) {
                $processed_headers = self::processHeaders($parsed_args['headers']);
                $parsed_args['headers'] = $processed_headers['headers'];
            }
            $headers = $parsed_args['headers'];
            $data = $parsed_args['body'];
            $type = $parsed_args['method'];
            $options = array(
                'timeout' => $parsed_args['timeout'],
                'useragent' => $parsed_args['user-agent'],
                'blocking' => $parsed_args['blocking'],
                'hooks' => new TP_HTTP_Requests_Hooks($url, $parsed_args),
            );
            $options['hooks']->register('requests.before_redirect', array(__CLASS__, 'browser_redirect_compatibility'));
            if (function_exists('__tp_kses_bad_protocol') && $parsed_args['reject_unsafe_urls'])
                $options['hooks']->register('requests.before_redirect', array(__CLASS__, 'validate_redirects'));
            if ($parsed_args['stream']) $options['filename'] = $parsed_args['filename'];
            if (empty($parsed_args['redirection'])) $options['follow_redirects'] = false;
            else $options['redirects'] = $parsed_args['redirection'];
            if (isset($parsed_args['limit_response_size'])) $options['max_bytes'] = $parsed_args['limit_response_size'];
            if (!empty($parsed_args['cookies'])) $options['cookies'] = self::normalize_cookies($parsed_args['cookies']);
            if (!$parsed_args['ssl_verify']) {
                $options['verify'] = false;
                $options['verify_name'] = false;
            } else $options['verify'] = $parsed_args['ssl_certificates'];
            if ('HEAD' !== $type && 'GET' !== $type) $options['data_format'] = 'body';
            $options['verify'] = $this->_apply_filters('https_ssl_verify', $options['verify'], $url);
            $proxy = new TP_HTTP_Proxy();
            if ($proxy->is_enabled() && $proxy->send_through_proxy($url)) {
                $options['proxy'] = new Requests_Proxy_HTTP($proxy->host() . ':' . $proxy->port());
                if ($proxy->use_authentication()) {
                    $options['proxy']->use_authentication = true;
                    $options['proxy']->user = $proxy->username();
                    $options['proxy']->pass = $proxy->password();
                }
            }
            $this->_mb_string_binary_safe_encoding();
            try {
                $requests_response = TP_Requests::request($url, $headers, $data, $type, $options);
                $http_response = new TP_HTTP_Requests_Response($requests_response, $parsed_args['filename']);
                $response = $http_response->to_array();
                $response['http_response'] = $http_response;
            } catch (Requests_Exception $e) {
                $response = new TP_Error('http_request_failed', $e->getMessage());
            }
            $this->_reset_mb_string_encoding();
            $this->_do_action('http_api_debug', $response, 'response', 'Requests', $parsed_args, $url);
            if ($this->_init_error($response)) return $response;
            if (!$parsed_args['blocking']) {
                return array(
                    'headers' => [],'body' => '',
                    'response' => ['code' => false,'message' => false,],
                    'cookies' => [],'http_response' => null,
                );
            }
            return $this->_apply_filters('http_response', $response, $parsed_args, $url);
        }//149
        public static function normalize_cookies( $cookies ): Requests_Cookie_Jar{
            $cookie_jar = new Requests_Cookie_Jar();
            foreach ( $cookies as $name => $value ) {
                if ( $value instanceof TP_Http_Cookie ) {
                    $attributes = array_filter(
                        $value->get_attributes(),
                        static function( $attr ) {
                            return null !== $attr;
                        }
                    );
                    $cookie_jar[ $value->name ] = new Requests_Cookie( $value->name, $value->value, $attributes, array( 'host-only' => $value->host_only ) );
                } elseif ( is_scalar( $value ) ) $cookie_jar[ $name ] = new Requests_Cookie( $name, $value );
            }
            return $cookie_jar;
        }//457
        public static function browser_redirect_compatibility( $location, $headers, $data, &$options, $original ): void{
            if ( 302 === $original->status_code ){
                $options['location'] = $location;
                $options['headers'] = $headers;
                $options['data'] = $data;
                $options['type'] = TP_GET;
            }
        }//492
        public static function validate_redirects( $location ): void {
            if ( ! (new static)->_tp_http_validate_url( $location ) )
                throw new Requests_Exception( (new static)->__( 'A valid URL was not provided.' ), 'tp_http.redirect_failed_validation' );
        }//507
        public function get_first_available_transport( $args, $url = null ){
            $transports = array( 'curl', 'streams' );
            $request_order = $this->_apply_filters( 'http_api_transports', $transports, $args, $url );
            foreach ( $request_order as $transport ) {
                if ( in_array( $transport, $transports, true ) ) $transport = ucfirst( $transport );
                $_class = 'TP_Http_' . $transport;
                $_namespace = TP_NS_CORE_LIBS.'HTTP\\';
                $class = $this->_tp_load_class('http_classes',$_namespace,$_class);
                if ( ! call_user_func( array( $class, 'test' ), $args, $url ) ) continue;
                return $class;
            }
            return false;
        }//523
        private function __dispatch_request( $url, $args ){
            static $transports = array();
            $class = $this->get_first_available_transport( $args, $url );
            if ( ! $class )
                return new TP_Error( 'http_failure', (new static)->__( 'There are no HTTP transports available which can complete the requested request.' ) );
            if ( empty( $transports[ $class ] ) ) $transports[ $class ] = new $class;
            $response = $transports[ $class ]->request( $url, $args );
            $this->_do_action( 'http_api_debug', $response, 'response', $class, $args, $url );
            if ( $this->_init_error( $response ) ) return $response;
            return $this->_apply_filters( 'http_response', $response, $args, $url );
        }//573
        public function dispatch_request( $url, $args ){
            return $this->__dispatch_request( $url, $args );
        }
        public function post( $url, $args = [] ){
            $defaults    = array( 'method' => 'POST' );
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            return $this->request( $url, $parsed_args );
        }//611
        public function get( $url, $args = [] ){
            $defaults    = array( 'method' => 'GET' );
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            return $this->request( $url, $parsed_args );
        }//629
        public function head( $url, $args = [] ){
            $defaults    = array( 'method' => 'HEAD' );
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            return $this->request( $url, $parsed_args );
        }//647
        public static function processResponse( $response ): array
        {
            $response = explode( "\r\n\r\n", $response, 2 );
            return array(
                'headers' => $response[0],
                'body'    => $response[1] ?? '',
            );
        }//666
        public static function processHeaders( $headers, $url = '' ): array{
            if ( is_string( $headers ) ) {
                $headers = str_replace( "\r\n", "\n", $headers );
                $headers = preg_replace( '/\n[ \t]/', ' ', $headers );
                $headers = explode( "\n", $headers );
            }
            $response = ['code' => 0, 'message' => '',];
            for ( $i = count( $headers ) - 1; $i >= 0; $i-- ) {
                if ( ! empty( $headers[ $i ] ) && false === strpos( $headers[ $i ], ':' ) ) {
                    $headers = array_splice( $headers, $i );
                    break;
                }
            }
            $cookies    = array();
            $new_headers = array();
            foreach ( (array) $headers as $temp_header ) {
                if ( empty( $temp_header ) ) continue;
                if ( false === strpos( $temp_header, ':' ) ) {
                    $stack   = explode( ' ', $temp_header, 3 );
                    $stack[] = '';
                    @list( , $response['code'], $response['message']) = $stack;
                    continue;
                }
                @list($key, $value) = explode( ':', $temp_header, 2 );
                $key   = strtolower( $key );
                $value = trim( $value );
                if ( isset( $new_headers[ $key ] ) ) {
                    if ( ! is_array( $new_headers[ $key ] ) )
                        $new_headers[ $key ] = array( $new_headers[ $key ] );
                    $new_headers[ $key ][] = $value;
                } else $new_headers[ $key ] = $value;
                if ( 'set-cookie' === $key ) $cookies[] = new TP_Http_Cookie( $value, $url );
            }
            $response['code'] = (int) $response['code'];
            return array(
                'response' => $response,
                'headers'  => $new_headers,
                'cookies'  => $cookies,
            );
        }//698
        public static function buildCookieHeader( &$r ): void{
            if ( ! empty( $r['cookies'] ) ) {
                foreach ( $r['cookies'] as $name => $value ) {
                    if ( ! is_object( $value ) )
                        $r['cookies'][ $name ] = new TP_Http_Cookie(array('name'  => $name,'value' => $value,));
                }
                $cookies_header = '';
                foreach ( (array) $r['cookies'] as $cookie )
                    $cookies_header .= $cookie->getHeaderValue() . '; ';
                $cookies_header         = substr( $cookies_header, 0, -2 );
                $r['headers']['cookie'] = $cookies_header;
            }
        }//781
        public static function chunkTransferDecode( $body ){
            if ( ! preg_match( '/^([0-9a-f]+)[^\r\n]*\r\n/i', trim( $body ) ) )  return $body;
            $parsed_body = '';
            $body_original = $body;
            while ( true ) {
                $has_chunk = (bool) preg_match( '/^([0-9a-f]+)[^\r\n]*\r\n/i', $body, $match );
                if ( ! $has_chunk || empty( $match[1] ) ) return $body_original;
                $length       = hexdec( $match[1] );
                $chunk_length = strlen( $match[0] );
                $parsed_body .= substr( $body, $chunk_length, $length );
                $body = substr( $body, $length + $chunk_length );
                if ( '0' === trim( $body ) )  return $parsed_body;
            }
            return null;
        }//817
        public function block_request( $uri ): ?bool{
            if ( ! defined( 'TP_HTTP_BLOCK_EXTERNAL' ) || ! TP_HTTP_BLOCK_EXTERNAL )
                return false;
            $check = parse_url( $uri );
            if ( ! $check )  return true;
            $home = parse_url( $this->_get_option( 'siteurl' ) );
            if ( 'localhost' === $check['host'] || ( isset( $home['host'] ) && $home['host'] === $check['host'] ) )
                return $this->_apply_filters( 'block_local_requests', false );
            if ( ! defined( 'TP_ACCESSIBLE_HOSTS' ) )
                return true;
            static $accessible_hosts = null;
            static $wildcard_regex   = array();
            if ( null === $accessible_hosts ) {
                $accessible_hosts = preg_split( '|,\s*|', TP_ACCESSIBLE_HOSTS );
                if ( false !== strpos( TP_ACCESSIBLE_HOSTS, '*' ) ) {
                    $wildcard_regex = array();
                    foreach ( $accessible_hosts as $host )
                        $wildcard_regex[] = str_replace( '\*', '.+', preg_quote( $host, '/' ) );
                    $wildcard_regex = '/^(' . implode( '|', $wildcard_regex ) . ')$/i';
                }
            }
            if ( ! empty( $wildcard_regex ) ) return ! preg_match( $wildcard_regex, $check['host'] );
            else return ! in_array( $check['host'], $accessible_hosts, true ); // Inverse logic, if it's in the array, then don't block it.
        }//870
        public static function make_absolute_url( $maybe_relative_path, $url ): string{
            if ( empty( $url ) ) return $maybe_relative_path;
            $url_parts = (new static)->_tp_parse_url( $url );
            if ( ! $url_parts ) return $maybe_relative_path;
            $relative_url_parts = (new static)->_tp_parse_url( $maybe_relative_path );
            if ( ! $relative_url_parts ) return $maybe_relative_path;
            if ( ! empty( $relative_url_parts['scheme'] ) )
                return $maybe_relative_path;
            $absolute_path = $url_parts['scheme'] . '://';
            if ( isset( $relative_url_parts['host'] ) ) {
                $absolute_path .= $relative_url_parts['host'];
                if ( isset( $relative_url_parts['port'] ) ) $absolute_path .= ':' . $relative_url_parts['port'];
            } else {
                $absolute_path .= $url_parts['host'];
                if ( isset( $url_parts['port'] ) ) $absolute_path .= ':' . $url_parts['port'];
            }
            $path = ! empty( $url_parts['path'] ) ? $url_parts['path'] : '/';
            if ( ! empty( $relative_url_parts['path'] ) && '/' === $relative_url_parts['path'][0] )
                $path = $relative_url_parts['path'];
            elseif ( ! empty( $relative_url_parts['path'] ) ) {
                $path = substr( $path, 0, strrpos( $path, '/' ) + 1 );
                $path .= $relative_url_parts['path'];
                while ( strpos( $path, '../' ) > 1 )
                    $path = preg_replace( '![^/]+/\.\./!', '', $path );
                $path = preg_replace( '!^/(\.\./)+!', '', $path );
            }
            if ( ! empty( $relative_url_parts['query'] ) )
                $path .= '?' . $relative_url_parts['query'];
            return $absolute_path . '/' . ltrim( $path, '/' );
        }//949
        public static function handle_redirects( $url, $args, $response ){
            if ( ! isset( $response['headers']['location'] ) || 0 === $args['_redirection'] )
                return false;
            if ( $response['response']['code'] > 399 || $response['response']['code'] < 300 )
                return false;
            if ( $args['redirection']-- <= 0 )
                return new TP_Error( 'http_request_failed', (new static)->__( 'Too many redirects.' ) );
            $redirect_location = $response['headers']['location'];
            if ( is_array( $redirect_location ) )
                $redirect_location = array_pop( $redirect_location );
            $redirect_location = self::make_absolute_url( $redirect_location, $url );
            if (('POST' === $args['method']) && in_array($response['response']['code'], array(302, 303), true)) $args['method'] = 'GET';
            if ( ! empty( $response['cookies'] ) ) {
                foreach ( $response['cookies'] as $cookie ) {
                    $cookie_test = $cookie ?: new TP_Http_Cookie($cookie);
                    //TP_Http_Cookie
                    if ( $cookie_test->test( $redirect_location )  )
                        $args['cookies'][] = $cookie;
                }
            }
            return (new static)->_tp_remote_request( $redirect_location, $args );
        }//1028
        public static function is_ip_address( $maybe_ip ){
            if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $maybe_ip ) ) return 4;
            if ( false !== strpos( $maybe_ip, ':' ) && preg_match( '/^(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})$/i', trim( $maybe_ip, ' []' ) ) )
                return 6;
            return false;
        }//1087
   }
}else die;