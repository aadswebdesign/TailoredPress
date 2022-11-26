<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 14:37
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_HTTP_Parser{
        use _sp_vars;
        public function __construct($data){
            $this->_sp_data = $data;
            $this->_sp_data_length = strlen($this->_sp_data);
        }
        public function parse():bool{
            while ($this->_sp_state && $this->_sp_state !== 'emit' && $this->_has_data()){
                $state = $this->_sp_state;
                $this->$state();
            }
            $this->_sp_data = '';
            if ($this->_sp_state === 'emit' || $this->_sp_state === 'body')
                return true;
            $this->sp_http_version = '';
            $this->sp_status_code = '';
            $this->sp_reason = '';
            $this->sp_headers = [];
            $this->sp_body = '';
            return false;
        }
        protected function _has_data():bool{
            return ($this->_sp_position < $this->_sp_data_length);
        }
        protected function _is_linear_whitespace():bool{
            return ($this->_sp_data[$this->_sp_position] === "\x09"
                || $this->_sp_data[$this->_sp_position] === "\x20"
                || ($this->_sp_data[$this->_sp_position] === "\x0A"
                    && isset($this->_sp_data[$this->_sp_position + 1])
                    && ($this->_sp_data[$this->_sp_position + 1] === "\x09" || $this->_sp_data[$this->_sp_position + 1] === "\x20")));
        }
        protected function _http_version():void{
            if (strpos($this->_sp_data, "\x0A") !== false && stripos($this->_sp_data, 'HTTP/') === 0){
                $len = strspn($this->_sp_data, '0123456789.', 5);
                $this->sp_http_version = substr($this->_sp_data, 5, $len);
                $this->_sp_position += 5 + $len;
                if (substr_count($this->sp_http_version, '.') <= 1){
                    $this->sp_http_version = (float) $this->sp_http_version;
                    $this->_sp_position += strspn($this->_sp_data, "\x09\x20", $this->_sp_position);
                    $this->_sp_state = 'status';
                } else $this->_sp_state = false;
            } else $this->_sp_state = false;
        }
        protected function _status():void{
            if ($len = strspn($this->_sp_data, '0123456789', $this->_sp_position)){
                $this->sp_status_code = (int) substr($this->_sp_data, $this->_sp_position, $len);
                $this->_sp_position += $len;
                $this->_sp_state = 'reason';
            } else $this->_sp_state = false;
        }
        protected function _reason():void{
            $len = strcspn($this->_sp_data, "\x0A", $this->_sp_position);
            $this->sp_reason = trim(substr($this->_sp_data, $this->_sp_position, $len), "\x09\x0D\x20");
            $this->_sp_position += $len + 1;
            $this->_sp_state = 'new_line';
        }
        protected function _new_line():void{
            $this->_sp_value = trim($this->_sp_value, "\x0D\x20");
            if ($this->sp_name !== '' && $this->_sp_value !== ''){
                $this->sp_name = strtolower($this->sp_name);
                // We should only use the last Content-Type header. c.f. issue #1
                if (isset($this->sp_headers[$this->sp_name]) && $this->sp_name !== 'content-type')
                    $this->sp_headers[$this->sp_name] .= ', ' . $this->_sp_value;
                else $this->sp_headers[$this->sp_name] = $this->_sp_value;
            }
            $this->sp_name = '';
            $this->_sp_value = '';
            if (strpos($this->_sp_data[$this->_sp_position], "\x0D\x0A") === 0){
                $this->_sp_position += 2;
                $this->_sp_state = 'body';
            }elseif ($this->_sp_data[$this->_sp_position] === "\x0A"){
                $this->_sp_position++;
                $this->_sp_state = 'body';
            } else $this->_sp_state = 'name';
        }
        protected function _name():void{
            $len = strcspn($this->_sp_data, "\x0A:", $this->_sp_position);
            if (isset($this->_sp_data[$this->_sp_position + $len])){
                if ($this->_sp_data[$this->_sp_position + $len] === "\x0A"){
                    $this->_sp_position += $len;
                    $this->_sp_state = 'new_line';
                }else {
                    $this->sp_name = substr($this->_sp_data, $this->_sp_position, $len);
                    $this->_sp_position += $len + 1;
                    $this->_sp_state = 'value';
                }
            }
            else $this->_sp_state = false;
        }
        protected function _linear_whitespace():void{
            do{
                if (substr($this->_sp_data, $this->_sp_position, 2) === "\x0D\x0A")
                    $this->_sp_position += 2;
                elseif ($this->_sp_data[$this->_sp_position] === "\x0A")
                    $this->_sp_position++;
                $this->_sp_position += strspn($this->_sp_data, "\x09\x20", $this->_sp_position);
            } while ($this->_has_data() && $this->_is_linear_whitespace());
            $this->_sp_value .= "\x20";
        }
        protected function _value():void{
            if ($this->_is_linear_whitespace())
                $this->_linear_whitespace();
            else{
                switch ($this->_sp_data[$this->_sp_position]){
                    case '"':
                        // Workaround for ETags: we have to include the quotes as
                        // part of the tag.
                        if (strtolower($this->sp_name) === 'e_tag')
                        {
                            $this->_sp_value .= '"';
                            $this->_sp_position++;
                            $this->_sp_state = 'value_char';
                            break;
                        }
                        $this->_sp_position++;
                        $this->_sp_state = 'quote';
                        break;
                    case "\x0A":
                        $this->_sp_position++;
                        $this->_sp_state = 'new_line';
                        break;
                    default:
                        $this->_sp_state = 'value_char';
                        break;
                }
            }
        }
        protected function _value_char():void{
            $len = strcspn($this->_sp_data, "\x09\x20\x0A\"", $this->_sp_position);
            $this->_sp_value .= substr($this->_sp_data, $this->_sp_position, $len);
            $this->_sp_position += $len;
            $this->_sp_state = 'value';
        }
        protected function _quote():void{
            if ($this->_is_linear_whitespace())
                $this->_linear_whitespace();
            else {
                switch ($this->_sp_data[$this->_sp_position]){
                    case '"':
                        $this->_sp_position++;
                        $this->_sp_state = 'value';
                        break;
                    case "\x0A":
                        $this->_sp_position++;
                        $this->_sp_state = 'new_line';
                        break;
                    case '\\':
                        $this->_sp_position++;
                        $this->_sp_state = 'quote_escaped';
                        break;
                    default:
                        $this->_sp_state = 'quote_char';
                        break;
                }
            }
        }
        protected function _quote_char():void{
            $len = strcspn($this->_sp_data, "\x09\x20\x0A\"\\", $this->_sp_position);
            $this->_sp_value .= substr($this->_sp_data, $this->_sp_position, $len);
            $this->_sp_position += $len;
            $this->_sp_state = 'value';
        }
        protected function _quote_escaped():void{
            $this->_sp_value .= $this->_sp_data[$this->_sp_position];
            $this->_sp_position++;
            $this->_sp_state = 'quote';
        }
        protected function _body():void{
            $this->sp_body = substr($this->_sp_data, $this->_sp_position);
            if (!empty($this->sp_headers['transfer-encoding'])){
                unset($this->sp_headers['transfer-encoding']);
                $this->_sp_state = 'chunked';
            }  else $this->_sp_state = 'emit';
        }
        protected function _chunked():void{
            if (!preg_match('/^([0-9a-f]+)[^\r\n]*\r\n/i', trim($this->sp_body))){
                $this->_sp_state = 'emit';
                return;
            }
            $decoded = '';
            $encoded = $this->sp_body;
            while (true){
                $is_chunked = (bool) preg_match( '/^([0-9a-f]+)[^\r\n]*\r\n/i', $encoded, $matches );
                if (!$is_chunked){
                    // Looks like it's not chunked after all
                    $this->_sp_state = 'emit';
                    return;
                }
                $length = hexdec(trim($matches[1]));
                if ($length === 0){
                    // Ignore trailer headers
                    $this->_sp_state = 'emit';
                    $this->sp_body = $decoded;
                    return;
                }
                $chunk_length = strlen($matches[0]);
                $decoded .= $part = substr($encoded, $chunk_length, $length);
                $encoded = substr($encoded, $chunk_length + $length + 2);
                if (empty($encoded) || trim($encoded) === '0'){
                    $this->_sp_state = 'emit';
                    $this->sp_body = $decoded;
                    return;
                }
            }
        }
        //use _parse_headers;
    }
}else die;