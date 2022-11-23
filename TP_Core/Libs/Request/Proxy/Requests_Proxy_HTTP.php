<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 14:08
 */
namespace TP_Core\Libs\Request\Proxy;
use TP_Core\Libs\Request\Exception\Requests_Exception;
use TP_Core\Libs\Request\Requests_Hooks;
use TP_Core\Libs\Request\Requests_Proxy;
if(ABSPATH){
    class Requests_Proxy_HTTP implements Requests_Proxy{
        public $proxy;
        public $user;
        public $pass;
        public $use_authentication;
        public function __construct($args = null) {
            if (is_string($args)) $this->proxy = $args;
            elseif (is_array($args)) {
                if (count($args) === 1) @list($this->proxy) = $args;
                elseif (count($args) === 3) {
                    @list($this->proxy, $this->user, $this->pass) = $args;
                    $this->use_authentication                    = true;
                }
                else throw new Requests_Exception('Invalid number of arguments', 'proxy_http_bad_args');
            }
        }
        public function register(Requests_Hooks $hooks): void {
            $hooks->register('curl.before_send', array($this, 'curl_before_send'));
            $hooks->register('fsockopen.remote_socket', array($this, 'fsockopen_remote_socket'));
            $hooks->register('fsockopen.remote_host_path', array($this, 'fsockopen_remote_host_path'));
            if ($this->use_authentication)
                $hooks->register('fsockopen.after_headers', array($this, 'fsockopen_header'));
        }
        public function curl_before_send(&$handle): void{
            curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($handle, CURLOPT_PROXY, $this->proxy);
            if ($this->use_authentication) {
                curl_setopt($handle, CURLOPT_PROXYAUTH, CURLAUTH_ANY);
                curl_setopt($handle, CURLOPT_PROXYUSERPWD, $this->get_auth_string());
            }
        }
        public function fsockopen_remote_socket(&$remote_socket): void{
            $remote_socket = $this->proxy;
        }
        public function fsockopen_remote_host_path(&$path, $url): void{
            $path = $url;
        }
        public function fsockopen_header(&$out):void {
            $out .= sprintf("Proxy-Authorization: Basic %s\r\n", base64_encode($this->get_auth_string()));
        }
        public function get_auth_string():string {
            return $this->user . ':' . $this->pass;
        }
    }
}else die;