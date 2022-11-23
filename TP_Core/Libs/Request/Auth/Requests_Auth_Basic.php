<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 12:41
 */

namespace TP_Core\Libs\Request\Auth;
use TP_Core\Libs\Request\Exception\Requests_Exception;
use TP_Core\Libs\Request\Requests_Hooks;
if(ABSPATH){
    class Requests_Auth_Basic{
        public $pass;
        public $user;
        public function __construct($args = null) {
            if (is_array($args)) {
                if (count($args) !== 2)
                    throw new Requests_Exception('Invalid number of arguments', 'auth_basic_bad_args');
                @list($this->user, $this->pass) = $args;
            }
        }
        public function register(Requests_Hooks $hooks):void {
            $hooks->register('curl.before_send', array($this, 'curl_before_send'));
            $hooks->register('fsockopen.after_headers', array($this, 'fsockopen_header'));
        }
        public function curl_before_send(&$handle):void {
            curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($handle, CURLOPT_USERPWD, $this->getAuthString());
        }
        public function fsockopen_header(&$out):void {
            $out .= sprintf("Authorization: Basic %s\r\n", base64_encode($this->getAuthString()));
        }
        public function getAuthString():string {
            return $this->user . ':' . $this->pass;
        }
    }
}else die;

