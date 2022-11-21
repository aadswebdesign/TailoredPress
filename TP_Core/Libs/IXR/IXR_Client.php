<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:34
 */
namespace TP_Core\Libs\IXR;
use TP_Core\Libs\IXR\Modules\_pre_module;
if(ABSPATH){
    class IXR_Client{
        public $server;
        public $port;
        public $path;
        public $user_agent;
        public $response;
        public $message = false;
        public $debug = false;
        public $timeout;
        public $headers = [];
        public $error = false;
        public function __construct( $server, $path = false, $port = 80, $timeout = 15 ){
            if (!$path) {
                $bits = parse_url($server);
                $this->server = $bits['host'];
                $this->port = $bits['port'] ?? 80;
                $this->path = $bits['path'] ?? '/';
                if (!$this->path) $this->path = '/';
                if ( ! empty( $bits['query'] ) ) $this->path .= '?' . $bits['query'];
            }else{
                $this->server = $server;
                $this->path = $path;
                $this->_port = $port;
            }
            $this->user_agent = 'The Incutio XML-RPC PHP Library';
            $this->timeout = $timeout;
        }
        public function query( ...$args ):string{
            $method = array_shift($args);
            $request = new IXR_Request($method, $args);
            $length = $request->getLength();
            $xml = $request->getXml();
            $r = "\r\n";
            $request  = "POST {$this->path} HTTP/1.0$r";
            $this->headers['Host']          = $this->server;
            $this->headers['Content-Type']  = 'text/xml';
            $this->headers['User-Agent']    = $this->user_agent;
            $this->headers['Content-Length']= $length;
            foreach( $this->headers as $header => $value ) $request .= "{$header}: {$value}{$r}";
            $request .= $r;
            $request .= $xml;
            if ($this->debug) echo new _pre_module('ixr-request',$request);
            if ($this->timeout) $fp = @fsockopen($this->server, $this->_port, $err_no, $err_str, $this->timeout);
            else $fp = @fsockopen($this->server, $this->_port, $err_no, $err_str);
            if (!$fp) {
                $this->error = new IXR_Error(-32300, 'transport error - could not open socket');
                return false;
            }
            fwrite($fp, $request);
            $contents = '';
            $debugContents = '';
            $gotFirstLine = false;
            $gettingHeaders = true;
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if (!$gotFirstLine) {
                    if (strpos($line, '200') === false) {
                        $this->error = new IXR_Error(-32300, 'transport error - HTTP status code was not 200');
                        return false;
                    }
                    $gotFirstLine = true;
                }
                if (trim($line) === '') $gettingHeaders = false;
                if (!$gettingHeaders) $contents .= $line;
                if ($this->debug) $debugContents .= $line;
            }
            if ($this->debug) echo new _pre_module('ixr-response',$debugContents);
            $this->message = new IXR_Message($contents);
            if (!$this->message->parse_messages()) {
                $this->error = new IXR_Error(-32700, 'parse error. not well formed');
                return false;
            }
            if ($this->message->message_type === 'fault') {
                $this->error = new IXR_Error($this->message->fault_code, $this->message->fault_string);
                return false;
            }
            // Message must be OK
            return true;
        }
        public function getResponse(): string{
            $msg = null;
            if( $this->message instanceof IXR_Message ){
                $msg = $this->message;
            }
            return $msg->params[0];
        }
        public function isError(): string{
            return (is_object($this->error));
        }
        public function getErrorCode(){
            $msg = null;
            if( $this->error instanceof IXR_Error ){
                $msg = $this->error;
            }
            return $msg->code;
        }
        public function getErrorMessage(): string{
            $msg = null;
            if( $this->error instanceof IXR_Error ){
                $msg = $this->error;
            }
            return $msg->message;
        }
    }
}else{die;}