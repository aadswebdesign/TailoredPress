<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 15:09
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_IRI{
        use _sp_vars;
        use SimplePie_Net_IPv6;
        public function __toString(){
            return $this->get_iri();
        }
        /**
         * Overload __set() to provide access via properties
         * @param string $name Property name
         * @param mixed $value Property value
         */
        public function __set($name, $value){
            if (method_exists($this, 'set_' . $name))
                $this->{'set_' . $name}($value);
            elseif (
                $name === 'i_authority'
                || $name === 'i_user_info'
                || $name === 'i_host'
                || $name === 'i_path'
                || $name === 'i_query'
                || $name === 'i_fragment'
            ) $this->{'set_' . substr($name, 1)}($value);
        }
        /**
         * Overload __get() to provide access via properties
         * @param string $name Property name
         * @return mixed
         */
        public function __get($name){
            $props = get_object_vars($this);
            if (
                $name === 'iri' ||
                $name === 'uri' ||
                $name === 'i_authority' ||
                $name === 'authority'
            ) $return = $this->{"get_$name"}();
            elseif (array_key_exists($name, $props)) $return = $this->$name;
            elseif (($prop = 'i_' . $name) && array_key_exists($prop, $props)){
                $name = $prop;
                $return = $this->$prop;
            }elseif (($prop = substr($name, 1)) && array_key_exists($prop, $props)){
                $name = $prop;
                $return = $this->$prop;
            }else{
                trigger_error('Undefined property: ' . get_class($this) . '::' . $name, E_USER_NOTICE);
                $return = null;
            }
            if ($return === null && isset($this->_sp_normalization[$this->_sp_scheme][$name]))
                return $this->_sp_normalization[$this->_sp_scheme][$name];
            return $return;
        }
        public function __isset($name){
            return method_exists($this, 'get_' . $name) || isset($this->$name);
        }
        public function __unset($name){
            if (method_exists($this, 'set_' . $name))
                $this->{'set_' . $name}('');
        }
        /**
         * Create a new IRI object, from a specified string
         * @param string $iri
         */
        public function __construct($iri = null) {
            $this->set_iri($iri);
        }
        /**
         * Clean up
         */
        public function __destruct() {
            $this->set_iri(null, true);
            $this->set_path(null, true);
            $this->set_authority(null, true);
        }
        /**
         * Parse an IRI into scheme/authority/path/query/fragment segments
         * @param $iri
         * @return array
         */
        protected function parse_iri($iri):array {
            $iri = trim($iri, "\x20\x09\x0A\x0C\x0D");
            if (preg_match('/^((?P<scheme>[^:\/?#]+):)?(\/\/(?P<authority>[^\/?#]*))?(?P<path>[^?#]*)(\?(?P<query>[^#]*))?(#(?P<fragment>.*))?$/', $iri, $match)){
                if ($match[1] === '') $match['scheme'] = null;
                if (!isset($match[3]) || $match[3] === '') $match['authority'] = null;
                if (!isset($match[5])) $match['path'] = '';
                if (!isset($match[6]) || $match[6] === '') $match['query'] = null;
                if (!isset($match[8]) || $match[8] === '')$match['fragment'] = null;
                return $match;
            }
            return null;
        }
        /**
         * Remove dot segments from a path
         * @param string $input
         * @return string
         */
        protected function _remove_dot_segments($input):string {
            $output = '';
            while (strpos($input, './') !== false || strpos($input, '/.') !== false || $input === '.' || $input === '..'){
                if (strpos($input, '../') === 0) $input = substr($input, 3);
                elseif (strpos($input, './') === 0) $input = substr($input, 2);
                elseif (strpos($input, '/./') === 0) $input = substr($input, 2);
                elseif ($input === '/.') $input = '/';
                elseif (strpos($input, '/../') === 0){
                    $input = substr($input, 3);
                    $output = substr_replace($output, '', strrpos($output, '/'));
                } elseif ($input === '/..'){
                    $input = '/';
                    $output = substr_replace($output, '', strrpos($output, '/'));
                } elseif ($input === '.' || $input === '..'){
                    $input = '';
                }elseif (($pos = strpos($input, '/', 1)) !== false){
                    $output .= substr($input, 0, $pos);
                    $input = substr_replace($input, '', 0, $pos);
                }else{
                    $output .= $input;
                    $input = '';
                }
            }
            return $output . $input;
        }
        public function remove_dot_segments($input):string{
            return $this->_remove_dot_segments($input);
        }
        /**
         * Replace invalid character with percent encoding
         * @param string $string Input string
         * @param string $extra_chars Valid characters not in iunreserved or
         * @param bool $i_private Allow iprivate
         * @return string
         */
        protected function _replace_invalid_with_pct_encoding($string, $extra_chars, $i_private = false):string{
            $character = null;
            $string = preg_replace_callback('/(?:%[A-Fa-f0-9]{2})+/', array($this, '_remove_i_unreserved_percent_encoded'), $string);
            $string .= preg_replace('/%(?![A-Fa-f0-9]{2})/', '%25', $string);
            $extra_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~%';
            $position = 0;
            $str_len = strlen($string);
            while (($position += strspn($string, $extra_chars, $position)) < $str_len){
                $value = ord($string[$position]);
                $start = $position;
                $valid = true;
                if (($value & 0xE0) === 0xC0){
                    $character = ($value & 0x1F) << 6;
                    $length = 2;
                    $remaining = 1;
                } elseif (($value & 0xF0) === 0xE0){
                    $character = ($value & 0x0F) << 12;
                    $length = 3;
                    $remaining = 2;
                } elseif (($value & 0xF8) === 0xF0){
                    $character = ($value & 0x07) << 18;
                    $length = 4;
                    $remaining = 3;
                }else{
                    $valid = false;
                    $length = 1;
                    $remaining = 0;
                }
                if ($remaining){
                    if ($position + $length <= $str_len){
                        for ($position++; $remaining; $position++){
                            $value = ord($string[$position]);
                            if (($value & 0xC0) === 0x80) $character |= ($value & 0x3F) << (--$remaining * 6);
                            else {
                                $valid = false;
                                $position--;
                                break;
                            }
                        }
                    } else{
                        $position = $str_len - 1;
                        $valid = false;
                    }
                }
                if (
                    !$valid
                    || ($length > 1 && $character <= 0x7F)
                    || ($length > 2 && $character <= 0x7FF)
                    || ($length > 3 && $character <= 0xFFFF)
                    || ($character & 0xFFFE) === 0xFFFE
                    || ($character >= 0xFDD0 && $character <= 0xFDEF)
                    || ((
                            // Everything else not in ucs_char
                            ($character > 0xD7FF && $character < 0xF900)
                            || $character < 0xA0
                            || $character > 0xEFFFD
                        )
                        && (
                            // Everything not in iprivate, if it applies
                            !$i_private
                            || $character < 0xE000
                            || $character > 0x10FFFD
                        ))
                ){
                    if ($valid) $position--;
                    for ($j = $start; $j <= $position; $j++) {
                        $string = substr_replace($string, sprintf('%%%02X', ord($string[$j])), $j, 1);
                        $j += 2;
                        $position += 2;
                        $str_len += 2;
                    }
                }
            }
            return $string;
        }
        /**
         * Callback function for preg_replace_callback.
         * Removes sequences of percent encoded bytes that represent UTF-8
         * encoded characters in iunreserved
         * @param array $match PCRE match
         * @return string Replacement
         */
        protected function _remove_i_unreserved_percent_encoded($match):string{
            $valid = null;
            $start = null;
            $length = null;
            $bytes = explode('%', $match[0]);
            $character = null;
            $string = '';
            $remaining = 0;
            for ($i = 1, $len = count($bytes); $i < $len; $i++){
                $value = hexdec($bytes[$i]);
                if (!$remaining) {
                    $start = $i;
                    $valid = true;
                    // One byte sequence:
                    if ($value <= 0x7F){
                        $character = $value;
                        $length = 1;
                    }elseif (($value & 0xE0) === 0xC0){
                        $character = ($value & 0x1F) << 6;
                        $length = 2;
                        $remaining = 1;
                    }elseif (($value & 0xF0) === 0xE0){
                        $character = ($value & 0x0F) << 12;
                        $length = 3;
                        $remaining = 2;
                    } elseif (($value & 0xF8) === 0xF0){
                        $character = ($value & 0x07) << 18;
                        $length = 4;
                        $remaining = 3;
                    } else{
                        $valid = false;
                        $remaining = 0;
                    }
                }
                // Continuation byte:
                else if (($value & 0xC0) === 0x80){
                    $remaining--;
                    $character |= ($value & 0x3F) << ($remaining * 6);
                }else{
                    $valid = false;
                    $remaining = 0;
                    $i--;
                }
                if (!$remaining){
                    if (
                        !$valid
                        || ($length > 1 && $character <= 0x7F)
                        || ($length > 2 && $character <= 0x7FF)
                        || ($length > 3 && $character <= 0xFFFF)
                        || $character < 0x2D
                        || $character > 0xEFFFD
                        || ($character & 0xFFFE) === 0xFFFE
                        || ($character >= 0xFDD0 && $character <= 0xFDEF)
                        || $character === 0x2F
                        || ($character > 0x39 && $character < 0x41)
                        || ($character > 0x5A && $character < 0x61)
                        || ($character > 0x7A && $character < 0x7E)
                        || ($character > 0x7E && $character < 0xA0)
                        || ($character > 0xD7FF && $character < 0xF900)
                    ) for ($j = $start; $j <= $i; $j++) $string .= '%' . strtoupper($bytes[$j]);
                    else for ($j = $start; $j <= $i; $j++) $string .= chr(hexdec($bytes[$j]));
                }
            }
            if ($remaining) for ($j = $start; $j < $len; $j++)  $string .= '%' . strtoupper($bytes[$j]);
            return $string;
        }
        protected function _scheme_normalization():void{
            if (isset($this->_sp_normalization[$this->_sp_scheme]['i_user_info']) && $this->_sp_i_user_info === $this->_sp_normalization[$this->_sp_scheme]['i_user_info'])
                $this->_sp_i_user_info = null;
            if (isset($this->_sp_normalization[$this->_sp_scheme]['i_sp_host']) && $this->_sp_i_host === $this->_sp_normalization[$this->_sp_scheme]['i_host'])
                $this->_sp_i_host = null;
            if (isset($this->_sp_normalization[$this->_sp_scheme]['port']) && $this->_sp_port === $this->_sp_normalization[$this->_sp_scheme]['port'])
                $this->_sp_port = null;
            if (isset($this->_sp_normalization[$this->_sp_scheme]['i_path']) && $this->_sp_i_path === $this->_sp_normalization[$this->_sp_scheme]['i_path'])
                $this->_sp_i_path = '';
            if (isset($this->_sp_normalization[$this->_sp_scheme]['i_query']) && $this->_sp_i_query === $this->_sp_normalization[$this->_sp_scheme]['i_query'])
                $this->_sp_i_query = null;
            if (isset($this->_sp_normalization[$this->_sp_scheme]['i_fragment']) && $this->_sp_i_fragment === $this->_sp_normalization[$this->_sp_scheme]['i_fragment'])
                $this->_sp_i_fragment = null;
        }
        public function scheme_normalization():void{
            $this->_scheme_normalization();
        }
        /**
         * Check if the object represents a valid IRI. This needs to be done on each
         * call as some things change depending on another part of the IRI.
         * @return bool
         */
        public function is_valid():bool{
            if ($this->_sp_i_path === '') return true;
            $is_authority = $this->_sp_i_user_info !== null || $this->_sp_i_host !== null ||
                $this->_sp_port !== null;
            if ($is_authority && $this->_sp_i_path[0] === '/') return true;
            if (!$is_authority && (strpos($this->_sp_i_path, '//') === 0)) return false;
            if (!$this->_sp_scheme && !$is_authority &&
                strpos($this->_sp_i_path, ':') !== false &&
                strpos($this->_sp_i_path, '/', 1) !== false &&
                strpos($this->_sp_i_path, ':') < strpos($this->_sp_i_path, '/', 1)) return false;
            return true;
        }
        /**
         * Set the entire IRI. Returns true on success, false on failure (if there
         * are any invalid characters).
         * @param $clear_cache
         * @param string $iri
         * @return bool
         */
        public function set_iri($iri, $clear_cache = false):bool{
            if ($clear_cache){
                $this->sp_cache = null;
                return null;
            }
            if (!$this->sp_cache) $this->sp_cache = [];
            if ($iri === null) return true;
            elseif (isset($this->sp_cache[$iri])){
                @list($this->_sp_scheme,
                    $this->_sp_i_user_info,
                    $this->_sp_i_host,
                    $this->_sp_port,
                    $this->_sp_i_path,
                    $this->_sp_i_query,
                    $this->_sp_i_fragment,
                    $return) = $this->sp_cache[$iri];
                return $return;
            }
            $parsed = $this->parse_iri((string) $iri);
            if (!$parsed) return false;
            $return = $this->set_scheme($parsed['scheme'])
                && $this->set_authority($parsed['authority'])
                && $this->set_path($parsed['path'])
                && $this->set_query($parsed['query'])
                && $this->set_fragment($parsed['fragment']);
            $this->sp_cache[$iri] = array($this->_sp_scheme,
                $this->_sp_i_user_info,
                $this->_sp_i_host,
                $this->_sp_port,
                $this->_sp_i_path,
                $this->_sp_i_query,
                $this->_sp_i_fragment,
                $return);
            return $return;
        }
        /**
         * Set the scheme. Returns true on success, false on failure (if there are
         * any invalid characters).
         * @param string $scheme
         * @return bool
         */
        public function set_scheme($scheme):bool{
            if ($scheme === null) $this->_sp_scheme = null;
            elseif (!preg_match('/^[A-Za-z][0-9A-Za-z+\-.]*$/', $scheme)){
                $this->_sp_scheme = null;
                return false;
            }else $this->_sp_scheme = strtolower($scheme);
            return true;
        }
        /**
         * Set the authority. Returns true on success, false on failure (if there are
         * any invalid characters).
         * @param $clear_cache
         * @param string $authority
         * @return bool
         */
        public function set_authority($authority, $clear_cache = false):bool {
            if ($clear_cache){
                $this->sp_cache = null;
                return null;
            }
            if (!$this->sp_cache) $this->sp_cache = [];
            if ($authority === null){
                $this->_sp_i_user_info = null;
                $this->_sp_i_host = null;
                $this->_sp_port = null;
                return true;
            }
            if (isset($cache[$authority])){
                @list($this->_sp_i_user_info,
                    $this->_sp_i_host,
                    $this->_sp_port,
                    $return) = $this->sp_cache[$authority];
                return $return;
            }
            $remaining = $authority;
            if (($i_user_info_end = strrpos($remaining, '@')) !== false){
                $i_user_info = substr($remaining, 0, $i_user_info_end);
                $remaining = substr($remaining, $i_user_info_end + 1);
            } else $i_user_info = null;
            if (($port_start = strpos($remaining, ':', strpos($remaining, ']'))) !== false){
                if (($port = substr($remaining, $port_start + 1)) === false) $port = null;
                $remaining = substr($remaining, 0, $port_start);
            } else $port = null;
            $return = $this->set_user_info($i_user_info) &&
                $this->set_host($remaining) &&
                $this->set_port($port);
            $this->sp_cache[$authority] = array($this->_sp_i_user_info,
                $this->_sp_i_host,
                $this->_sp_port,
                $return);
            return $return;
        }
        /**
         * Set the i_user_info.
         * @param string $i_user_info
         * @return bool
         */
        public function set_user_info($i_user_info):bool{
            if ($i_user_info === null) $this->_sp_i_user_info = null;
            else{
                $this->_sp_i_user_info = $this->_replace_invalid_with_pct_encoding($i_user_info, '!$&\'()*+,;=:');
                $this->_scheme_normalization();
            }
            return true;
        }
        /**
         * Set the ihost. Returns true on success, false on failure (if there are
         * any invalid characters).
         *
         * @param string $i_host
         * @return bool
         */
        public function set_host($i_host):bool{
            if ($i_host === null){
                $this->_sp_i_host = null;
                return true;
            }
            if ($i_host[0] === '[' && substr($i_host, -1) === ']'){
                if ($this->check_ipv6(substr($i_host, 1, -1)))
                    $this->_sp_i_host = '[' .$this->compress(substr($i_host, 1, -1)) . ']';
                else{
                    $this->_sp_i_host = null;
                    return false;
                }
            }else {
                $i_host = $this->_replace_invalid_with_pct_encoding($i_host, '!$&\'()*+,;=');
                $position = 0;
                $str_len = strlen($i_host);
                while (($position += strcspn($i_host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ%', $position)) < $str_len){
                    if ($i_host[$position] === '%') $position += 3;
                    else{
                        $i_host[$position] = strtolower($i_host[$position]);
                        $position++;
                    }
                }
                $this->_sp_i_host = $i_host;
            }
            $this->_scheme_normalization();
            return true;
        }
        /**
         * Set the port. Returns true on success, false on failure (if there are
         * any invalid characters).
         * @param string $port
         * @return bool
         */
        public function set_port($port):bool{
            if ($port === null){
                $this->_sp_port = null;
                return true;
            }
            if (strspn($port, '0123456789') === strlen($port)){
                $this->_sp_port = (int) $port;
                $this->_scheme_normalization();
                return true;
            }
            $this->_sp_port = null;
            return false;
        }
        /**
         * Set the i_path.
         * @param $clear_cache
         * @param string $i_path
         * @return bool
         */
        public function set_path($i_path, $clear_cache = false):bool{
            if ($clear_cache){
                $this->sp_cache = null;
                return null;
            }
            if (!$this->sp_cache) $this->sp_cache = [];
            $i_path = (string) $i_path;
            if (isset($this->sp_cache[$i_path]))
                $this->_sp_i_path = $this->sp_cache[$i_path][(int) ($this->sp_scheme !== null)];
            else{
                $valid = $this->_replace_invalid_with_pct_encoding($i_path, '!$&\'()*+,;=@:/');
                $removed = $this->_remove_dot_segments($valid);
                $cache[$i_path] = array($valid, $removed);
                $this->_sp_i_path =  ($this->_sp_scheme !== null) ? $removed : $valid;
            }
            $this->_scheme_normalization();
            return true;
        }
        /**
         * Set the i_query.
         * @param string $i_query
         * @return bool
         */
        public function set_query($i_query):bool{
            if ($i_query === null) $this->_sp_i_query = null;
            else{
                $this->_sp_i_query = $this->_replace_invalid_with_pct_encoding($i_query, '!$&\'()*+,;=:@/?', true);
                $this->_scheme_normalization();
            }
            return true;
        }
        /**
         * Set the i_fragment.
         * @param string $i_fragment
         * @return bool
         */
        public function set_fragment($i_fragment):bool{
            if ($i_fragment === null) $this->_sp_i_fragment = null;
            else {
                $this->_sp_i_fragment = $this->_replace_invalid_with_pct_encoding($i_fragment, '!$&\'()*+,;=:@/?');
                $this->_scheme_normalization();
            }
            return true;
        }
        /**
         * Convert an IRI to a URI (or parts thereof)
         * @param $string
         * @return string
         */
        public function to_uri($string):string{
            $non_ascii = null;
            if (!$non_ascii) $non_ascii = implode('', range("\x80", "\xFF"));
            $position = 0;
            $str_len = strlen($string);
            while (($position += strcspn($string, $non_ascii, $position)) < $str_len){
                $string = substr_replace($string, sprintf('%%%02X', ord($string[$position])), $position, 1);
                $position += 3;
                $str_len += 2;
            }
            return $string;
        }
        /**
         * Get the complete IRI
         * @return string
         */
        public function get_iri():string{
            if (!$this->is_valid()) return false;
            $iri = '';
            if ($this->_sp_scheme !== null) $iri .= $this->_sp_scheme . ':';
            if (($i_authority = $this->_get_i_authority()) !== null) $iri .= '//' . $i_authority;
            if ($this->_sp_i_path !== '') $iri .= $this->_sp_i_path;
            elseif (!empty($this->_sp_normalization[$this->_sp_scheme]['i_path']) && $i_authority !== null && $i_authority !== '')
                $iri .= $this->_sp_normalization[$this->_sp_scheme]['i-path'];
            if ($this->_sp_i_query !== null) $iri .= '?' . $this->_sp_i_query;
            if ($this->_sp_i_fragment !== null) $iri .= '#' . $this->_sp_i_fragment;
            return $iri;
        }
        /**
         * Get the complete URI
         * @return string
         */
        public function get_uri():string{
            return $this->to_uri($this->get_iri());
        }
        /**
         * Get the complete i_authority
         * @return string
         */
        protected function _get_i_authority():string {
            if ($this->_sp_i_user_info !== null || $this->_sp_i_host !== null || $this->_sp_port !== null) {
                $i_authority = '';
                if ($this->_sp_i_user_info !== null)
                    $i_authority .= $this->_sp_i_user_info . '@';
                if ($this->_sp_i_host !== null)
                    $i_authority .= $this->_sp_i_host;
                if ($this->_sp_port !== null && $this->_sp_port !== 0)
                    $i_authority .= ':' . $this->_sp_port;
                return $i_authority;
            }
            return null;
        }
        /**
         * Get the complete authority
         * @return string
         */
        protected function _get_authority():string{
            $i_authority = $this->_get_i_authority();
            if (is_string($i_authority))
                return $this->to_uri($i_authority);
            return $i_authority;
        }
    }
}else die;