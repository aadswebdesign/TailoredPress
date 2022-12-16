<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 12:07
 */
namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Auth\Requests_Auth_Basic;
use TP_Core\Libs\Request\Proxy\Requests_Proxy_HTTP;
use TP_Core\Libs\Request\Cookie\Requests_Cookie_Jar;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class TP_Requests{
        protected static $_certificate_path;
        protected static $_transports = [];
        public static $transport = [];
        public static function add_transport($transport): void{
            if (empty(self::$_transports)) {
                self::$_transports = array(
                    'Requests_Transport_cURL',
                    'Requests_Transport_fsockopen',
                );
            }
            self::$_transports = array_merge(self::$_transports, array($transport));
        }//173
        protected static function get_transport($capabilities = []) {
            ksort($capabilities);
            $cap_string = serialize($capabilities);
            if (isset(self::$transport[$cap_string]) && self::$transport[$cap_string] !== null) {
                $class = self::$transport[$cap_string];
                return new $class();
            }
            if (empty(self::$_transports)) {
                self::$_transports = array('Requests_Transport_cURL','Requests_Transport_fsockopen',);
            }
            foreach (self::$_transports as $class) {
                if (!class_exists($class)) continue;
                $result = $class->test($capabilities);
                if ($result) {
                    self::$transport[$cap_string] = $class;
                    break;
                }
            }
            if (self::$transport[$cap_string] === null)
                throw new Requests_Exception('No working transports found', 'no_transport', self::$_transports);
            $class = self::$transport[$cap_string];
            return new $class();
        }//181
        public static function get($url, $headers = array(), $options = array()): Requests_Response{
            return self::request($url, $headers, null, TP_GET, $options);
        }//232
        public static function head($url, $headers = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, null, TP_HEAD, $options);
        }//239
        public static function delete($url, $headers = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, null, TP_DELETE, $options);
        }//246
        public static function trace($url, $headers = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, null, TP_TRACE, $options);
        }//253
        public static function post($url, $headers = array(), $data = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, $data, TP_POST, $options);
        }//269
        public static function put($url, $headers = array(), $data = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, $data, TP_PUT, $options);
        }//275
        public static function options($url, $headers = array(), $data = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, $data, TP_OPTIONS, $options);
        }//282
        public static function patch($url, $headers, $data = array(), $options = array()): Requests_Response {
            return self::request($url, $headers, $data, TP_PATCH, $options);
        }//294
        public static function request($url, $headers = array(), $data = array(), $type = TP_GET, $options = array()): Requests_Response{
            if (empty($options['type'])) $options['type'] = $type;
            $options = array_merge(self::get_default_options(), $options);
            $hooks = null;
            self::set_defaults($url, $headers, $data, $type, $options);
            if($options['hooks'] instanceof Requests_Hooks){
                $hooks = $options['hooks'];
            }
            $hooks->dispatch('requests.before_request', array(&$url, &$headers, &$data, &$type, &$options));

            if (!empty($options['transport'])) {
                $transport = $options['transport'];
                if (is_string($options['transport'])) $transport = new $transport();
            }
            else {
                $need_ssl     = (stripos($url, 'https://') === 0);
                $capabilities = array('ssl' => $need_ssl);
                $transport    = self::get_transport($capabilities);
            }
            $response = $transport->request($url, $headers, $data, $options);
            $hooks->dispatch('requests.before_parse', array(&$response, $url, $headers, $data, $type, $options));
            return self::parse_response($response, $url, $headers, $data, $options);
        }//359
        public static function request_multiple($requests, $options = array()) {
            $options = array_merge(self::get_default_options(true), $options);
            $hooks = $options['hooks'] ?: new Requests_Hooks();
            if (!empty($options['hooks'])) {
                $hooks->register('transport.internal.parse_response', array('Requests', 'parse_multiple'));
                if (!empty($options['complete']))
                    $hooks->register('multiple.request.complete', $options['complete']);
            }
            $request_item = [];
            foreach ($requests as $id => &$request) {
                $request_hooks = $request['options']['hooks'] ?: new Requests_Hooks();
                if (!isset($request['headers'])) $request['headers'] = array();
                if (!isset($request['data'])) $request['data'] = array();
                if (!isset($request['type'])) $request['type'] = TP_GET;
                if (!isset($request['options'])) {
                    $request['options']         = $options;
                    $request['options']['type'] = $request['type'];
                }else {
                    if (empty($request['options']['type']))
                        $request['options']['type'] = $request['type'];
                    $request_item['options'] = array($options, $request['options']);
                }
                self::set_defaults($request['url'], $request['headers'], $request['data'], $request['type'], $request['options']);
                if ($request['options']['hooks'] !== $options['hooks']) {
                    $request_hooks->register('transport.internal.parse_response', array('Requests', 'parse_multiple'));
                    if (!empty($request['options']['complete']))
                        $request_hooks->register('multiple.request.complete', $request['options']['complete']);
                }
            }
            unset($request);
            $request_item = array_merge($request_item['options']);
            unset($request_item);//todo
            if (!empty($options['transport'])) {
                $transport = $options['transport'];
                if (is_string($options['transport'])) $transport = new $transport();
            } else  $transport = self::get_transport();
            $responses = $transport->request_multiple($requests, $options);
            foreach ($responses as $id => &$response) {
                if (is_string($response)) {
                    $request = $requests[$id];
                    $request_hooks = $request['options']['hooks'] ?: new Requests_Hooks();
                    self::parse_multiple($response, $request);
                    $request_hooks->dispatch('multiple.request.complete', array(&$response, $id));
                }
            }
            return $responses;
        }//429
        protected static function get_default_options($multi_request = false):array {
            $defaults = ['timeout' => 10,'connect_timeout' => 10,
                'useragent' => 'php-requests/' . TP_REQUEST_VERSION,'protocol_version' => 1.1, 'redirected' => 0,
                'redirects' => 10,'follow_redirects' => true,'blocking' => true,'type' => TP_GET,'filename' => false,
                'auth' => false,'proxy' => false,'cookies' => false,'max_bytes' => false,'idn' => true,
                'hooks' => null,'transport' => null,'verify' => self::get_certificate_path(),'verify_name' => true,];
            if ($multi_request !== false) $defaults['complete'] = null;
            return $defaults;
        }
        public static function get_certificate_path(): string{
            if (!empty(self::$_certificate_path))
                return self::$_certificate_path;
            return __DIR__ . '/Requests/Transport/ca_cert.pem';
        }
        public static function set_certificate_path($path): void{
            self::$_certificate_path = $path;
        }
        protected static function set_defaults(&$url, &$headers, &$data, &$type, &$options): void{
            static $headers, $data;// just fooling
            if (!preg_match('/^http(s)?:\/\//i', $url, $matches))
                throw new Requests_Exception('Only HTTP(S) requests are handled.', 'non_http', $url);
            if (empty($options['hooks'])) $options['hooks'] = new Requests_Hooks();
            if (is_array($options['auth'])) $options['auth'] = new Requests_Auth_Basic($options['auth']);
            if ($options['auth'] !== false) $options['auth']->register($options['hooks']);
            if (is_string($options['proxy']) || is_array($options['proxy']))
                $options['proxy'] = new Requests_Proxy_HTTP($options['proxy']);
            if ($options['proxy'] !== false) $options['proxy']->register($options['hooks']);
            if (is_array($options['cookies']))  $options['cookies'] = new Requests_Cookie_Jar($options['cookies']);
            elseif (empty($options['cookies']))
                $options['cookies'] = new Requests_Cookie_Jar();
            if ($options['cookies'] !== false)
                $options['cookies']->register($options['hooks']);
            if ($options['idn'] !== false) {
                $iri       = new Requests_IRI($url);
                $iri->i_host = Requests_IDNAEncoder::encode($iri->i_host);
                $url       = $iri->get_uri();
            }
            $type = strtoupper($type);
            if (!isset($options['data_format'])) {
                if (in_array($type, array(TP_HEAD, TP_GET, TP_DELETE), true))
                    $options['data_format'] = 'query';
                else $options['data_format'] = 'body';
            }
        }
        protected static function parse_response($headers, $url, $req_headers, $req_data, $options): Requests_Response{
            $return = new Requests_Response();
            if (!$options['blocking']) return $return;
            $return->raw  = $headers;
            $return->url  = (string) $url;
            $return->body = '';
            if (!$options['filename']) {
                $pos = strpos($headers, "\r\n\r\n");
                if ($pos === false) throw new Requests_Exception('Missing header/body separator', 'requests.no_crlf_separator');
                $headers = substr($return->raw, 0, $pos);
                $body = substr($return->raw, $pos + 4);
                if (!empty($body)) $return->body = $body;
            }
            $headers = str_replace("\r\n", "\n", $headers);
            $headers = preg_replace('/\n[ \t]/', ' ', $headers);
            $headers = explode("\n", $headers);
            preg_match('#^HTTP/(1\.\d)[ \t]+(\d+)#i', array_shift($headers), $matches);
            if (empty($matches)) throw new Requests_Exception('Response could not be parsed', 'no_version', $headers);
            $return->protocol_version = (float) $matches[1];
            $return->status_code      = (int) $matches[2];
            if ($return->status_code >= 200 && $return->status_code < 300)
                $return->success = true;
            foreach ($headers as $header) {
                @list($key, $value) = explode(':', $header, 2);
                $value             = trim($value);
                /** @noinspection NotOptimalRegularExpressionsInspection */
                preg_replace('#(\s+)#i', ' ', $value);
                $return->headers[$key] = $value;
            }
            if (isset($return->headers['transfer-encoding'])) {
                $return->body = self::decode_chunked($return->body);
                unset($return->headers['transfer-encoding']);
            }
            if (isset($return->headers['content-encoding']))
                $return->body = self::decompress($return->body);
            //fsockopen and cURL compatibility
            if (isset($return->headers['connection']))
                unset($return->headers['connection']);
            $hooks = $options['hooks'] ?: new Requests_Hooks();
            $hooks->dispatch('requests.before_redirect_check', array(&$return, $req_headers, $req_data, $options));
            if (($options['follow_redirects'] === true) && $return->is_redirect()) {
                if (isset($return->headers['location']) && $options['redirected'] < $options['redirects']) {
                    if ($return->status_code === 303) $options['type'] = TP_GET;
                    $options['redirected']++;
                    $location = $return->headers['location'];
                    if (strpos($location, 'http://') !== 0 && strpos($location, 'https://') !== 0) {
                        $location = Requests_IRI::absolutize($url, $location);
                        $location = $location->get_uri();
                    }
                    $hook_args = array(
                        &$location,
                        &$req_headers,
                        &$req_data,
                        &$options,
                        $return,
                    );
                    $hooks->dispatch('requests.before_redirect', $hook_args);
                    $redirected            = self::request($location, $req_headers, $req_data, $options['type'], $options);
                    $redirected->history[] = $return;
                    return $redirected;
                }
                elseif ($options['redirected'] >= $options['redirects'])
                    throw new Requests_Exception('Too many redirects', 'too_many_redirects', $return);
            }
            $return->redirects = $options['redirected'];
            $hooks->dispatch('requests.after_request', array(&$return, $req_headers, $req_data, $options));
            return $return;
        }
        public static function parse_multiple(&$response, $request): void{
            try {
                $url      = $request['url'];
                $headers  = $request['headers'];
                $data     = $request['data'];
                $options  = $request['options'];
                $response = self::parse_response($response, $url, $headers, $data, $options);
            }
            catch (Requests_Exception $e) {
                $response = $e;
            }
        }
        protected static function decode_chunked($data): ?string{
            if (!preg_match('/^([0-9a-f]+)(?:;(?:[\w-]*)(?:=(?:(?:[\w-]*)*|"(?:[^\r\n])*"))?)*\r\n/i', trim($data)))
                return $data;
            $decoded = '';
            $encoded = $data;
            while (true) {
                $is_chunked = (bool) preg_match('/^([0-9a-f]+)(?:;(?:[\w-]*)(?:=(?:(?:[\w-]*)*|"(?:[^\r\n])*"))?)*\r\n/i', $encoded, $matches);
                if (!$is_chunked) return $data;
                $length = hexdec(trim($matches[1]));
                if ($length === 0) return $decoded;
                $chunk_length = strlen($matches[0]);
                $decoded     .= substr($encoded, $chunk_length, $length);
                $encoded      = substr($encoded, $chunk_length + $length + 2);
                if (empty($encoded) || trim($encoded) === '0') return $decoded;
            }
            return null;
        }
        public static function flatten($array): array{
            $return = array();
            foreach ($array as $key => $value)
                $return[] = sprintf('%s: %s', $key, $value);
            return $return;
        }
        public static function flattern($array): array{
            return self::flatten($array);
        }
        public static function decompress($data) {
            if (strpos($data, "\x1f\x8b") !== 0 && strpos($data, "\x78\x9c") !== 0)
                return $data;
            if (function_exists('gzdecode')) {
                $decoded = @gzdecode($data);
                if ($decoded !== false) return $decoded;
            }
            if (function_exists('gzinflate')) {
                $decoded = @gzinflate($data);
                if ($decoded !== false) return $decoded;
            }
            $decoded = self::compatible_gz_inflate($data);
            if ($decoded !== false) return $decoded;
            if (function_exists('gzuncompress')) {
                $decoded = @gzuncompress($data);
                if ($decoded !== false)
                    return $decoded;
            }
            return $data;
        }
        public static function compatible_gz_inflate($gz_data) {
            if (strpos($gz_data, "\x1f\x8b\x08") === 0) {
                $i   = 10;
                $flg = ord(substr($gz_data, 3, 1));
                if ($flg > 0) {
                    if ($flg & 4) {
                        @list($xlen) = unpack('v', substr($gz_data, $i, 2));
                        $i         += 2 + $xlen;
                    }
                    if ($flg & 8) $i = strpos($gz_data, "\0", $i) + 1;
                    if ($flg & 16) $i = strpos($gz_data, "\0", $i) + 1;
                    if ($flg & 2) $i += 2;
                }
                $decompressed = self::compatible_gz_inflate(substr($gz_data, $i));
                if ($decompressed !== false) return $decompressed;
            }
            $huffman_encoded = false;
            @list(, $first_nibble) = unpack('h', $gz_data);
            @list(, $first_two_bytes) = unpack('n', $gz_data);
            if ($first_nibble === 0x08 && ($first_two_bytes % 0x1F) === 0) $huffman_encoded = true;
            if ($huffman_encoded) {
                $decompressed = @gzinflate(substr($gz_data, 2));
                if ($decompressed !== false) return $decompressed;
            }
            if (strpos($gz_data, "\x50\x4b\x03\x04") === 0) {
                // ZIP file format header
                // Offset 6: 2 bytes, General-purpose field
                // Offset 26: 2 bytes, filename length
                // Offset 28: 2 bytes, optional field length
                // Offset 30: Filename field, followed by optional field, followed
                // immediately by data
                @list(, $general_purpose_flag) = unpack('v', substr($gz_data, 6, 2));
                // If the file has been compressed on the fly, 0x08 bit is set of
                // the general purpose field. We can use this to differentiate
                // between a compressed document, and a ZIP file
                $zip_compressed_on_the_fly = ((0x08 & $general_purpose_flag) === 0x08);
                if (!$zip_compressed_on_the_fly) {
                    // Don't attempt to decode a compressed zip file
                    return $gz_data;
                }
                $first_file_start = array_sum(unpack('v2', substr($gz_data, 26, 4)));
                $decompressed     = @gzinflate(substr($gz_data, 30 + $first_file_start));
                if ($decompressed !== false) return $decompressed;
                return false;
            }
            // Finally fall back to straight gzinflate
            $decompressed = @gzinflate($gz_data);
            if ($decompressed !== false) return $decompressed;
            // Fallback for all above failing, not expected, but included for
            // debugging and preventing regressions and to track stats
            $decompressed = @gzinflate(substr($gz_data, 2));
            if ($decompressed !== false) return $decompressed;
            return false;
        }
        public static function match_domain($host, $reference): bool{
            if ($host === $reference) return true;
            $parts = explode('.', $host);
            if (ip2long($host) === false && count($parts) >= 3) {
                $parts[0] = '*';
                $wildcard = implode('.', $parts);
                if ($wildcard === $reference) {
                    return true;
                }
            }
            return false;
        }
    }
}else die;