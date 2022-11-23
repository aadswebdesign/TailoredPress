<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 17:55
 */
namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Response\Requests_Response_Headers;
use TP_Core\Libs\Request\Cookie\Requests_Cookie_Jar;
use TP_Core\Libs\Request\Exception\Requests_Exception;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Response{
        public $body = '';
        public $cookies = array();
        public $headers = array();
        public $history = array();
        public $protocol_version = false;
        public $raw = '';
        public $redirects = 0;
        public $success = false;
        public $status_code = false;
        public $url = '';
        public function __construct() {
            $this->headers = new Requests_Response_Headers();
            $this->cookies = new Requests_Cookie_Jar();
        }
        public function is_redirect(): bool{
            $code = $this->status_code;
            return in_array($code, array(300, 301, 302, 303, 307), true) || ($code > 307 && $code < 400);
        }
        public function throw_for_status($allow_redirects = true): void{
            if ($this->is_redirect()) {
                if (!$allow_redirects)
                    throw new Requests_Exception('Redirection not allowed', 'response.no_redirects', $this);
            }
            elseif (!$this->success) {
                $exception = Requests_Exception_HTTP::get_class($this->status_code);
                throw new $exception(null, $this);
            }
        }
    }
}else die;