<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 15:13
 */
namespace TP_Core\Libs\Request\Transport;
use TP_Core\Libs\Request\Requests_Hooks;
use TP_Core\Libs\Request\TP_Requests;
use TP_Core\Libs\Request\Requests_Transport;
use TP_Core\Libs\Request\Exception\Requests_Exception;
use TP_Core\Libs\Request\Exception\Requests_Exception_Transport_cURL;
if(ABSPATH){
    class Requests_Transport_cURL implements Requests_Transport{
        public const CURL_7_10_5 = 0x070A05;
        public const CURL_7_16_2 = 0x071002;
        protected $_done_headers = false;
        protected $_handle;
        protected $_hooks;
        protected $_response_bytes;
        protected $_response_byte_limit;
        protected $_stream_handle;
        public $headers = '';
        public $info;
        public $response_data = '';
        public $version;
        public function __construct() {
            $curl          = curl_version();
            $this->version = $curl['version_number'];
            $this->_handle  = curl_init();
            curl_setopt($this->_handle, CURLOPT_HEADER, false);
            curl_setopt($this->_handle, CURLOPT_RETURNTRANSFER, 1);
            if ($this->version >= self::CURL_7_10_5) curl_setopt($this->_handle, CURLOPT_ENCODING, '');
            if (defined('CURLOPT_PROTOCOLS'))  curl_setopt($this->_handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            if (defined('CURLOPT_REDIR_PROTOCOLS')) curl_setopt($this->_handle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }
        public function __destruct() {
            if (is_resource($this->_handle)) curl_close($this->_handle);
        }
        public function request($url, $headers = array(), $data = array(), $options = array()):string {
            $this->_hooks = $options['hooks'];
            $this->_setup_handle($url, $headers, $data, $options);
            $options['hooks']->dispatch('curl.before_send', array(&$this->_handle));
            if ($options['filename'] !== false)
                $this->_stream_handle = fopen($options['filename'], 'wb');
            $this->response_data       = '';
            $this->_response_bytes      = 0;
            $this->_response_byte_limit = false;
            if ($options['max_bytes'] !== false)
                $this->_response_byte_limit = $options['max_bytes'];
            if (isset($options['verify'])) {
                if ($options['verify'] === false) {
                    curl_setopt($this->_handle, CURLOPT_SSL_VERIFYHOST,0 ?: 2);
                    curl_setopt($this->_handle, CURLOPT_SSL_VERIFYPEER,0 ?: true);
                }
                elseif (is_string($options['verify']))
                    curl_setopt($this->_handle, CURLOPT_CAINFO, $options['verify']);
            }
            if (isset($options['verify_name']) && $options['verify_name'] === false)
                curl_setopt($this->_handle, CURLOPT_SSL_VERIFYHOST,0 ?: 2);
            curl_exec($this->_handle);
            $response = $this->response_data;
            $options['hooks']->dispatch('curl.after_send', array());
            if (curl_errno($this->_handle) === 23 || curl_errno($this->_handle) === 61) {
                curl_setopt($this->_handle, CURLOPT_ENCODING, 'none');
                $this->response_data  = '';
                $this->_response_bytes = 0;
                curl_exec($this->_handle);
                $response = $this->response_data;
            }
            $this->process_response($response, $options);
            curl_setopt($this->_handle, CURLOPT_HEADERFUNCTION, null);
            curl_setopt($this->_handle, CURLOPT_WRITEFUNCTION, null);
            return $this->headers;
        }
        public function request_multiple($requests, $options):array {
            if (empty($requests)) return array();
            $multi_handle = curl_multi_init();
            $request = [];
            $sub_requests = [];
            $sub_handles  = [];
            $class = get_class($this);
            foreach ($requests as $id => $request) {
                $request_hooks = $request['options']['hooks'] ?: new Requests_Hooks();
                $sub_requests[$id] = new $class();
                $sub_handles[$id]  = $sub_requests[$id]->get_sub_request_handle($request['url'], $request['headers'], $request['data'], $request['options']);
                $request_hooks->dispatch('curl.before_multi_add', array(&$sub_handles[$id]));
                curl_multi_add_handle($multi_handle, $sub_handles[$id]);
            }
            $completed       = 0;
            $responses       = array();
            $sub_request_count = count($sub_requests);
            $request_hooks = $request['options']['hooks'] ?: new Requests_Hooks();
            $request_hooks->dispatch('curl.before_multi_exec', array(&$multi_handle));
            do {
                $active = 0;
                do {
                    $status = curl_multi_exec($multi_handle, $active);
                }
                while ($status === CURLM_CALL_MULTI_PERFORM);
                $to_process = array();
                // Read the information as needed
                while ($done = curl_multi_info_read($multi_handle)) {
                    $key = array_search($done['handle'], $sub_handles, true);
                    if (!isset($to_process[$key])) $to_process[$key] = $done;
                }
                // Parse the finished requests before we start getting the new ones
                foreach ($to_process as $key => $done) {
                    $options = $requests[$key]['options'];
                    $option_hooks = $options['hooks'] ?:  new Requests_Hooks();
                    if ($done['result'] !== CURLE_OK) {
                        //get error string for handle.
                        $reason          = curl_error($done['handle']);
                        $exception       = new Requests_Exception_Transport_cURL(
                            $reason,
                            Requests_Exception_Transport_cURL::EASY,
                            $done['handle'],
                            $done['result']
                        );
                        $responses[$key] = $exception;
                        $option_hooks->dispatch('transport.internal.parse_error', array(&$responses[$key], $requests[$key]));
                    }else {
                        $responses[$key] = $sub_requests[$key]->process_response($sub_requests[$key]->response_data, $options);
                        $option_hooks->dispatch('transport.internal.parse_response', array(&$responses[$key], $requests[$key]));
                    }
                    curl_multi_remove_handle($multi_handle, $done['handle']);
                    curl_close($done['handle']);
                    if (!is_string($responses[$key]))
                        $option_hooks->dispatch('multiple.request.complete', array(&$responses[$key], $key));
                    $completed++;
                }
            }
            while ($active || $completed < $sub_request_count);
            $request_hooks = $request['options']['hooks'] ?: new Requests_Hooks();
            $request_hooks->dispatch('curl.after_multi_exec', array(&$multi_handle));
            curl_multi_close($multi_handle);
            return $responses;
        }
        public function &get_sub_request_handle($url, $headers, $data, $options) {
            $this->_setup_handle($url, $headers, $data, $options);
            if ($options['filename'] !== false)
                $this->_stream_handle = fopen($options['filename'], 'wb');
            $this->response_data       = '';
            $this->_response_bytes      = 0;
            $this->_response_byte_limit = false;
            if ($options['max_bytes'] !== false)
                $this->_response_byte_limit = $options['max_bytes'];
            $this->_hooks = $options['hooks'];
            return $this->_handle;
        }
        protected function _setup_handle($url, $headers, $data, $options):void {
            $hooks = $options['hooks'] ?: new Requests_Hooks();
            $hooks->dispatch('curl.before_request', array(&$this->_handle));
            // Force closing the connection for old versions of cURL (<7.22).
            if (!isset($headers['Connection'])) $headers['Connection'] = 'close';
            if (!isset($headers['Expect']) && $options['protocol_version'] === 1.1)
                $headers['Expect'] = $this->_get_expect_header($data);
            $headers = TP_Requests::flatten($headers);
            if (!empty($data)) {
                $data_format = $options['data_format'];
                if ($data_format === 'query') {
                    $url  = self::_format_get($url, $data);
                    $data = '';
                }elseif (!is_string($data))
                    $data = http_build_query($data, null, '&');
            }
            switch ($options['type']) {
                case TP_POST:
                    curl_setopt($this->_handle, CURLOPT_POST, true);
                    curl_setopt($this->_handle, CURLOPT_POSTFIELDS, $data);
                    break;
                case TP_HEAD:
                    curl_setopt($this->_handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                    curl_setopt($this->_handle, CURLOPT_NOBODY, true);
                    break;
                case TP_TRACE:
                    curl_setopt($this->_handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                    break;
                case TP_PATCH:
                case TP_PUT:
                case TP_DELETE:
                case TP_OPTIONS:
                default:
                    curl_setopt($this->_handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                    if (!empty($data)) curl_setopt($this->_handle, CURLOPT_POSTFIELDS, $data);
            }
            $timeout = max($options['timeout'], 1);
            if (is_int($timeout) || $this->version < self::CURL_7_16_2)
                curl_setopt($this->_handle, CURLOPT_TIMEOUT, ceil($timeout));
            else curl_setopt($this->_handle, CURLOPT_TIMEOUT_MS, round($timeout * 1000));
            if (is_int($options['connect_timeout']) || $this->version < self::CURL_7_16_2)
                curl_setopt($this->_handle, CURLOPT_CONNECTTIMEOUT, ceil($options['connect_timeout']));
            else curl_setopt($this->_handle, CURLOPT_CONNECTTIMEOUT_MS, round($options['connect_timeout'] * 1000));
            curl_setopt($this->_handle, CURLOPT_URL, $url);
            curl_setopt($this->_handle, CURLOPT_REFERER, $url);
            curl_setopt($this->_handle, CURLOPT_USERAGENT, $options['useragent']);
            if (!empty($headers)) curl_setopt($this->_handle, CURLOPT_HTTPHEADER, $headers);
            if ($options['protocol_version'] === 1.1)
                curl_setopt($this->_handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            else curl_setopt($this->_handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            if ($options['blocking'] === true) {
                curl_setopt($this->_handle, CURLOPT_HEADERFUNCTION, array($this, 'stream_headers'));
                curl_setopt($this->_handle, CURLOPT_WRITEFUNCTION, array($this, 'stream_body'));
                curl_setopt($this->_handle, CURLOPT_BUFFERSIZE, TP_BUFFER_SIZE);
            }
        }
        /**
         * Process a response
         *
         * @param string $response Response data from the body
         * @param array $options Request options
         * @return string|false HTTP response data including headers. False if non-blocking.
         * @throws Requests_Exception
         */
        public function process_response($response, $options) {
            if ($options['blocking'] === false) {
                $fake_headers = '';
                $options['hooks']->dispatch('curl.after_request', array(&$fake_headers));
                return false;
            }
            if ($options['filename'] !== false && $this->_stream_handle) {
                fclose($this->_stream_handle);
                $this->headers = trim($this->headers);
            }
            else $this->headers .= $response;
            if (curl_errno($this->_handle)) {
                $error = sprintf(
                    'cURL error %s: %s',
                    curl_errno($this->_handle),
                    curl_error($this->_handle)
                );
                throw new Requests_Exception($error, 'curl_error', $this->_handle);
            }
            $this->info = curl_getinfo($this->_handle);
            $options['hooks']->dispatch('curl.after_request', array(&$this->headers, &$this->info));
            return $this->headers;
        }
        public function stream_headers($handle, $headers) {
            if ($this->_done_headers) {
                $this->headers      = '';
                $this->_done_headers = false;
                $this->_handle = $handle;
            }
            $this->headers .= $headers;
            if ($headers === "\r\n") $this->_done_headers = true;
            return strlen($headers);
        }
        public function stream_body($handle, $data) {
            $this->_hooks = $handle['hooks'] ?: new Requests_Hooks();
            $this->_hooks->dispatch('request.progress', array($data, $this->_response_bytes, $this->_response_byte_limit));
            $data_length = strlen($data);
            $this->_stream_handle = $handle;
            // Are we limiting the response size?
            if ($this->_response_byte_limit) {
                if ($this->_response_bytes === $this->_response_byte_limit)
                    return $data_length;
                if (($this->_response_bytes + $data_length) > $this->_response_byte_limit) {
                    $limited_length = ($this->_response_byte_limit - $this->_response_bytes);
                    $data           = substr($data, 0, $limited_length);
                }
            }
            if ($this->_stream_handle) fwrite($this->_stream_handle, $data);
            else $this->response_data .= $data;
            $this->_response_bytes += strlen($data);
            return $data_length;
        }//483
        protected static function _format_get($url, $data) {
            if (!empty($data)) {
                $query     = '';
                $url_parts = parse_url($url);
                if (empty($url_parts['query'])) $url_parts['query'] = '';
                else $query = $url_parts['query'];
                $query .= '&' . http_build_query($data, null, '&');
                $query  = trim($query, '&');
                if (empty($url_parts['query'])) $url .= '?' . $query;
                else $url = str_replace($url_parts['query'], $query, $url);
            }
            return $url;
        }//519
        public static function test($capabilities = array()):bool {
            if (!function_exists('curl_init') || !function_exists('curl_exec'))
                return false;
            // If needed, check that our installed curl version supports SSL
            if (isset($capabilities['ssl']) && $capabilities['ssl']) {
                $curl_version = curl_version();
                if (!(CURL_VERSION_SSL & $curl_version['features']))
                    return false;
            }
            return true;
        }//549
        protected function _get_expect_header($data):string {
            if (!is_array($data))  return strlen((string) $data) >= 1048576 ? '100-Continue' : '';
            $byte_size = 0;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data));
            foreach ($iterator as $datum) {
                $byte_size += strlen((string) $datum);
                if ($byte_size >= 1048576) return '100-Continue';
            }
            return '';
        }//571
    }
}else die;