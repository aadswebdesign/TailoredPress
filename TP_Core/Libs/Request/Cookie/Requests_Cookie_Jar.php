<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 13:39
 */
namespace TP_Core\Libs\Request\Cookie;
use TP_Core\Libs\Request\Requests_Cookie;
use TP_Core\Libs\Request\Requests_Hooker;
use TP_Core\Libs\Request\Requests_Response;
use TP_Core\Libs\Request\Requests_IRI;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class Requests_Cookie_Jar implements \ArrayAccess, \IteratorAggregate{
        protected $_cookies = array();
        public function __construct($cookies = array()) {
            $this->_cookies = $cookies;
        }
        public function normalize_cookie($cookie, $key = null) {
            if ($cookie instanceof Requests_Cookie) return $cookie;
            return Requests_Cookie::parse($cookie, $key);
        }
        public function normalizeCookie($cookie, $key = null) {
            return $this->normalize_cookie($cookie, $key);
        }
        public function offsetExists($key):bool {
            return isset($this->_cookies[$key]);
        }
        public function offsetGet($key) {
            if (!isset($this->_cookies[$key])) return null;
            return $this->_cookies[$key];
        }
        public function offsetSet($key, $value):void {
            if ($key === null)
                throw new Requests_Exception('Object is a dictionary, not a list', 'invalid_set');
            $this->_cookies[$key] = $value;
        }
        public function offsetUnset($key):void {
            unset($this->_cookies[$key]);
        }
        public function getIterator() {
            return new \ArrayIterator($this->_cookies);
        }
        public function register(Requests_Hooker $hooks) {
            $hooks->register('requests.before_request', array($this, 'before_request'));
            $hooks->register('requests.before_redirect_check', array($this, 'before_redirect_check'));
        }
        /** @noinspection PhpUnusedParameterInspection
         * @param $url
         * @param $headers
         * @param $data
         * @param $type
         * @param $options
         */
        public function before_request($url, &$headers,&$data, &$type, &$options):void { //todo for later ,
            if (!($url instanceof Requests_IRI))  {$url = new Requests_IRI($url);}
            if (!empty($this->_cookies)) {
                $cookies = [];
                foreach ($this->_cookies as $key => $cookie) {
                    $cookie = $this->normalize_cookie($cookie, $key);
                    if ($cookie->is_expired())  continue;
                    if ($cookie->domain_matches($url->host)) {
                        $cookies[] = $cookie->format_for_header();
                    }
                }
                $headers['Cookie'] = implode('; ', $cookies);
            }
        }
        public function before_redirect_check(Requests_Response $return) {
            $url = $return->url;
            if (!$url instanceof Requests_IRI) $url = new Requests_IRI($url);
            $cookies         = Requests_Cookie::parse_from_headers($return->headers, $url);
            $this->_cookies   = array_merge($this->_cookies, $cookies);
            $return->cookies = $this;
        }
    }
}else die;