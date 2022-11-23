<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 23:43
 */

namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Cookie\Requests_Cookie_Jar;
if(ABSPATH){
    class Requests_Session{
        public $data = array();
        public $headers = [];
        public $options = array();
        public $url;
        public function __construct($url = null, $headers = array(), $data = array(), $options = array()) {
            $this->url     = $url;
            $this->headers = $headers;
            $this->data    = $data;
            $this->options = $options;
            if (empty($this->options['cookies'])) $this->options['cookies'] = new Requests_Cookie_Jar();
        }
        public function __get($key) {
            if (isset($this->options[$key])) return $this->options[$key];
            return null;
        }
        public function __set($key, $value) {
            $this->options[$key] = $value;
        }
        public function __isset($key) {
            return isset($this->options[$key]);
        }
        public function __unset($key) {
            if (isset($this->options[$key])) {
                unset($this->options[$key]);
            }
        }
        public function get($url, $headers = [],array ...$options): Requests_Response{
            return $this->request($url, $headers, null, TP_GET, $options);
        }
        public function head($url, $headers = [],array ...$options): Requests_Response{
            return $this->request($url, $headers, null, TP_HEAD, $options);
        }
        public function delete($url, $headers = [],array ...$options): Requests_Response{
            return $this->request($url, $headers, null, TP_DELETE, $options);
        }
        public function post($url, $headers = [], $data = [],array ...$options): Requests_Response {
            return $this->request($url, $headers, $data, TP_POST, $options);
        }
        public function put($url, $headers = [], $data = [], array ...$options): Requests_Response {
            return $this->request($url, $headers, $data, TP_PUT, $options);
        }
        public function patch($url, $headers, $data = array(), $options = []): Requests_Response {
            return $this->request($url, $headers, $data, TP_PATCH, $options);
        }
        public function request($url, $headers = [], $data = [], $type = TP_GET, array ...$options): Requests_Response{
            $request = $this->_merge_request(compact('url', 'headers', 'data', 'options'));
            return TP_Requests::request($request['url'], $request['headers'], $request['data'], $type, $request['options']);
        }
        public function request_multiple($requests, $options = []) {
            foreach ($requests as $key => $request)
                $requests[$key] = $this->_merge_request($request, false);
            $options = array_merge($this->options, $options);
            unset($options['type']);
            return TP_Requests::request_multiple($requests, $options);
        }
        protected function _merge_request($request, $merge_options = true) {
            if ($this->url !== null) {
                $request['url'] = Requests_IRI::absolutize($this->url, $request['url']);
                $request['url'] = $request['url']->uri;
            }
            if (empty($request['headers'])) $request['headers'] = array();
            $request['headers'] = array_merge($this->headers, $request['headers']);
            if (empty($request['data'])) {
                if (is_array($this->data)) $request['data'] = $this->data;
            }elseif (is_array($request['data']) && is_array($this->data))
                $request['data'] = array_merge($this->data, $request['data']);
            if ($merge_options !== false) {
                $request['options'] = array_merge($this->options, $request['options']);
                unset($request['options']['type']);
            }
            return $request;
        }
    }
}else die;

