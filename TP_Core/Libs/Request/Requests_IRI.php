<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 18:34
 */
namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class Requests_IRI{
        private $__character;
        private $__length;
        private $__start;
        private $__valid;
        protected $_i_fragment;
        protected $_i_host;
        protected $_i_path = '';
        protected $_i_query;
        protected $_i_user_info;
        protected $_normalization = [
            'acap' => ['port' => 674],
            'dict' => ['port' => 2628],
            'file' => ['i_host' => 'localhost'],
            'http' => ['port' => 80,],
            'https' => ['port' => 443,]
        ];
        protected $_port;
        protected $_scheme;
        public $i_host;
        public function __toString() {
            return (string)$this->_get_iri();
        }//149
        public function __set($name, $value) {
            if (method_exists($this, 'set_' . $name))
                $this->{'set_' . $name}($value);
            elseif ($name === 'i_authority'|| $name === 'i_user_info'|| $name === 'i_host'
                || $name === 'i_path'|| $name === 'i_query'|| $name === 'i_fragment')
                $this->{'set_' . substr($name, 1)}($value);
        }//171
        public function __get($name) {
            $props = get_object_vars($this);
            if ($name === 'iri' ||$name === 'uri' || $name === 'i_authority' ||$name === 'authority') {
                $method = 'get_' . $name;
                $return = $this->$method();
            }elseif (array_key_exists($name, $props)) $return = $this->$name;
            elseif (($prop = 'i_' . $name) && array_key_exists($prop, $props)) {
                $name = $prop;
                $return = $this->$prop;
            } elseif (($prop = substr($name, 1)) && array_key_exists($prop, $props)) {
                $name = $prop;
                $return = $this->$prop;
            }else {
                trigger_error('Undefined property: ' . get_class($this) . '::' . $name, E_USER_NOTICE);
                $return = null;
            }if ($return === null && isset($this->_normalization[$this->_scheme][$name]))
                return $this->_normalization[$this->_scheme][$name];
            else return $return;
        }//217
        public function __isset($name) {
            return (method_exists($this, 'get_' . $name) || isset($this->$name));
        }//225
        public function __unset($name) {
            if (method_exists($this, 'set_' . $name))
                $this->{'set_' . $name}('');
        }//234
        public function __construct($iri = null) {
            if($iri !== null)
            $this->_set_iri($iri);
            $this->i_host = $this->_i_host;
        }//247
        public static function absolutize($base, $relative) {
            if (!($relative instanceof self))
                $relative = new Requests_IRI($relative);
            if (!$relative->is_valid()) return false;
            elseif ($relative->_scheme !== null) return clone $relative;
            if (!($base instanceof self)) $base = new Requests_IRI($base);
            if ($base->_scheme === null || !$base->is_valid()) return false;
            if ($relative->_get_iri() !== '') {
                if ($relative->_i_user_info !== null || $relative->_i_host !== null || $relative->_port !== null) {
                    $target = clone $relative;
                    $target->_scheme = $base->_scheme;
                }else {
                    $target = new Requests_IRI;
                    $target->_scheme = $base->_scheme;
                    $target->_i_user_info = $base->_i_user_info;
                    $target->_i_host = $base->_i_host;
                    $target->_port = $base->_port;
                    if ($relative->_i_path !== '') {
                        if ($relative->_i_path[0] === '/') $target->_i_path = $relative->_i_path;
                        elseif (($base->_i_user_info !== null || $base->_i_host !== null || $base->_port !== null) && $base->_i_path === '') {
                            $target->_i_path = '/' . $relative->_i_path;
                        }
                        elseif (($last_segment = strrpos($base->_i_path, '/')) !== false)
                            $target->_i_path = substr($base->_i_path, 0, $last_segment + 1) . $relative->_i_path;
                        else {
                            $target->_i_path = $relative->_i_path;
                        }
                        $target->_i_path = $target->_remove_dot_segments($target->_i_path);
                        $target->_i_query = $relative->_i_query;
                    }else {
                        $target->_i_path = $base->_i_path;
                        if ($relative->_i_query !== null) $target->_i_query = $relative->_i_query;
                        elseif ($base->_i_query !== null) $target->_i_query = $base->_i_query;
                    }
                    $target->_i_fragment = $relative->_i_fragment;
                }
            }else {
                $target = clone $base;
                $target->_i_fragment = null;
            }
            $target->_scheme_normalization();
            return $target;
        }//258
        protected function _parse_iri($iri) {
            $iri = trim($iri, "\x20\x09\x0A\x0C\x0D");
            $has_match = preg_match('/^((?P<scheme>[^:\/?#]+):)?(\/\/(?P<authority>[^\/?#]*))?(?P<path>[^?#]*)(\?(?P<query>[^#]*))?(#(?P<fragment>.*))?$/', $iri, $match);
            if (!$has_match)
                throw new Requests_Exception('Cannot parse supplied IRI', 'iri.cannot_parse', $iri);
            if ($match[1] === '') $match['scheme'] = null;
            if (!isset($match[3]) || $match[3] === '') $match['authority'] = null;
            if (!isset($match[5])) $match['path'] = '';
            if (!isset($match[6]) || $match[6] === '') $match['query'] = null;
            if (!isset($match[8]) || $match[8] === '') $match['fragment'] = null;
            return $match;
        }//329
        protected function _remove_dot_segments($input): string{
            $output = '';
            while (strpos($input, './') !== false || strpos($input, '/.') !== false || $input === '.' || $input === '..') {
                if (strpos($input, '../') === 0) $input = substr($input, 3);
                elseif (strpos($input, './') === 0) $input = substr($input, 2);
                elseif (strpos($input, '/./') === 0) $input = substr($input, 2);
                elseif ($input === '/.') $input = '/';
                elseif (strpos($input, '/../') === 0) {
                    $input = substr($input, 3);
                    $output = substr_replace($output, '', strrpos($output, '/'));
                }elseif ($input === '/..') {
                    $input = '/';
                    $output = substr_replace($output, '', strrpos($output, '/'));
                }elseif ($input === '.' || $input === '..') $input = '';
                elseif (($pos = strpos($input, '/', 1)) !== false) {
                    $output .= substr($input, 0, $pos);
                    $input = substr_replace($input, '', 0, $pos);
                } else {
                    $output .= $input;
                    $input = '';
                }
            }
            return $output . $input;
        }//360
        protected function _replace_invalid_with_pct_encoding($string, $extra_chars, $i_private = false) {
            $string = preg_replace_callback('/(?:%[A-Fa-f0-9]{2})+/', array($this, '_remove_i_unreserved_percent_encoded'), $string);
            $string = preg_replace('/%(?![A-Fa-f0-9]{2})/', '%25', $string);
            $extra_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~%';
            $position = 0;
            $strlen = strlen($string);
            while (($position += strspn($string, $extra_chars, $position)) < $strlen) {
                $value = ord($string[$position]);
                $this->__start = $position;
                $this->__valid = true;
                if (($value & 0xE0) === 0xC0) {
                    $this->__character = ($value & 0x1F) << 6;
                    $this->__length = 2;
                    $remaining = 1;
                } elseif (($value & 0xF0) === 0xE0) {
                    $this->__character = ($value & 0x0F) << 12;
                    $this->__length = 3;
                    $remaining = 2;
                } elseif (($value & 0xF8) === 0xF0) {
                    $this->__character = ($value & 0x07) << 18;
                    $this->__length = 4;
                    $remaining = 3;
                } else {
                    $this->__valid = false;
                    $this->__length = 1;
                    $remaining = 0;
                }
                if ($remaining) {
                    if ($position + $this->__length <= $strlen) {
                        for ($position++; $remaining; $position++) {
                            $value = ord($string[$position]);
                            if (($value & 0xC0) === 0x80)
                                $this->__character |= ($value & 0x3F) << (--$remaining * 6);
                            else {
                                $this->__valid = false;
                                $position--;
                                break;
                            }
                        }
                    }else {
                        $position = $strlen - 1;
                        $this->__valid = false;
                    }
                }
                if (!$this->__valid || ($this->__length > 1 && $this->__character <= 0x7F)
                    || ($this->__length > 2 && $this->__character <= 0x7FF) || ($this->__length > 3 && $this->__character <= 0xFFFF)
                    || ($this->__character & 0xFFFE) === 0xFFFE|| ($this->__character >= 0xFDD0 && $this->__character <= 0xFDEF)
                    || ((($this->__character > 0xD7FF && $this->__character < 0xF900) || $this->__character < 0xA0 || $this->__character > 0xEFFFD)
                        && (!$i_private || $this->__character < 0xE000 || $this->__character > 0x10FFFD))
                ){
                    if ($this->__valid) $position--;
                    for ($j = $this->__start; $j <= $position; $j++) {
                        $string = substr_replace($string, sprintf('%%%02X', ord($string[$j])), $j, 1);
                        $j += 2;
                        $position += 2;
                        $strlen += 2;
                    }
                }
            }
            return $string;
        }//422
        protected function _remove_i_unreserved_percent_encoded($match): string{
            $bytes = explode('%', $match[0]);
            $string = '';
            $remaining = 0;
            for ($i = 1, $len = count($bytes); $i < $len; $i++) {
                $value = hexdec($bytes[$i]);
                if (!$remaining) {
                    $this->__start = $i;
                    // By default we are valid
                    $this->__valid = true;
                    // One byte sequence:
                    if ($value <= 0x7F) {
                        $this->__character = $value;
                        $this->__length = 1;
                    } elseif (($value & 0xE0) === 0xC0) {
                        $this->__character = ($value & 0x1F) << 6;
                        $this->__length = 2;
                        $remaining = 1;
                    } elseif (($value & 0xF0) === 0xE0) {
                        $this->__character = ($value & 0x0F) << 12;
                        $this->__length = 3;
                        $remaining = 2;
                    } elseif (($value & 0xF8) === 0xF0) {
                        $this->__character = ($value & 0x07) << 18;
                        $this->__length = 4;
                        $remaining = 3;
                    } else {
                        $this->__valid = false;
                        $remaining = 0;
                    }
                } else if (($value & 0xC0) === 0x80) {
                    $remaining--;
                    $this->__character |= ($value & 0x3F) << ($remaining * 6);
                } else {
                    $this->__valid = false;
                    $remaining = 0;
                    $i--;
                }
                if (!$remaining) {
                    if (!$this->__valid // Invalid sequences
                        // Non-shortest form sequences are invalid
                        || ($this->__length > 1 && $this->__character <= 0x7F) || ($this->__length > 2 && $this->__character <= 0x7FF) || ($this->__length > 3 && $this->__character <= 0xFFFF)
                        // Outside of range of i unreserved code points
                        || $this->__character < 0x2D || $this->__character > 0xEFFFD
                        // Non_characters
                        || ($this->__character & 0xFFFE) === 0xFFFE || ($this->__character >= 0xFDD0 && $this->__character <= 0xFDEF)
                        // Everything else not in i unreserved (this is all BMP)
                        || $this->__character === 0x2F || ($this->__character > 0x39 && $this->__character < 0x41) || ($this->__character > 0x5A && $this->__character < 0x61) || ($this->__character > 0x7A && $this->__character < 0x7E) || ($this->__character > 0x7E && $this->__character < 0xA0) || ($this->__character > 0xD7FF && $this->__character < 0xF900)){
                        for ($j = $this->__start; $j <= $i; $j++)  $string .= '%' . strtoupper($bytes[$j]);
                    }else {
                        for ($j = $this->__start; $j <= $i; $j++) $string .= chr(hexdec($bytes[$j]));
                    }
                }
            }
            if ($remaining) {
                for ($j = $this->__start; $j < $len; $j++) $string .= '%' . strtoupper($bytes[$j]);
            }
            return $string;
        }//545
        protected function _scheme_normalization(): void {
            if (isset($this->_normalization[$this->_scheme]['i_user_info']) && $this->_i_user_info === $this->_normalization[$this->_scheme]['i_user_info'])
                $this->_i_user_info = null;
            if (isset($this->_normalization[$this->_scheme]['i_host']) && $this->_i_host === $this->_normalization[$this->_scheme]['i_host'])
                $this->_i_host = null;
            if (isset($this->_normalization[$this->_scheme]['port']) && $this->_port === $this->_normalization[$this->_scheme]['port'])
                $this->_port = null;
            if (isset($this->_normalization[$this->_scheme]['i_path']) && $this->_i_path === $this->_normalization[$this->_scheme]['i_path'])
                $this->_i_path = '';
            if (isset($this->ihost) && empty($this->_i_path))
                $this->_i_path = '/';
            if (isset($this->_normalization[$this->_scheme]['i_query']) && $this->_i_query === $this->_normalization[$this->_scheme]['i_query'])
                $this->_i_query = null;
            if (isset($this->_normalization[$this->_scheme]['i_fragment']) && $this->_i_fragment === $this->_normalization[$this->_scheme]['i_fragment'])
                $this->_i_fragment = null;
        }//659
        public function is_valid(): bool{
            $is_authority = $this->_i_user_info !== null || $this->_i_host !== null || $this->_port !== null;
            if ($this->_i_path !== '' && (($is_authority && $this->_i_path[0] !== '/') ||
                ($this->_scheme === null &&!$is_authority && strpos($this->_i_path, ':') !== false &&
                 (strpos($this->_i_path, '/') === false ? true : strpos($this->_i_path, ':') < strpos($this->_i_path, '/')))))
                return false;
            return true;
        }//706
        protected function _set_iri($iri): bool{
            static $cache;
            if (!$cache) $cache = array();
            if ($iri === null) return true;
            if (isset($cache[$iri])) {
                @list($this->_scheme,$this->_i_user_info,$this->_i_host,$this->_port,$this->_i_path,$this->_i_query,$this->_i_fragment,$return) = $cache[$iri];
                return $return;
            }
            $parsed = $this->_parse_iri((string) $iri);
            $return = $this->_set_scheme($parsed['scheme'])
                && $this->_set_authority($parsed['authority'])
                && $this->_set_path($parsed['path'])
                && $this->_set_query($parsed['query'])
                && $this->_set_fragment($parsed['fragment']);
            $cache[$iri] = array($this->_scheme,$this->_i_user_info,
                $this->_i_host,$this->_port,$this->_i_path,$this->_i_query,
                $this->_i_fragment,$return);
            return $return;
        }//715
        protected function _set_scheme($scheme): bool{
            if ($scheme === null) $this->_scheme = null;
            elseif (!preg_match('/^[A-Za-z][0-9A-Za-z+\-.]*$/', $scheme)) {
                $this->_scheme = null;
                return false;
            } else $this->_scheme = strtolower($scheme);
            return true;
        }//762
        protected function _set_authority($authority): bool{
            static $cache;
            if (!$cache) $cache = array();
            if ($authority === null) {
                $this->_i_user_info = null;
                $this->_i_host = null;
                $this->_port = null;
                return true;
            }
            if (isset($cache[$authority])) {
                @list($this->_i_user_info,
                    $this->_i_host,
                    $this->_port,
                    $return) = $cache[$authority];
                return $return;
            }
            $remaining = $authority;
            if (($i_user_info_end = strrpos($remaining, '@')) !== false) {
                $i_user_info = substr($remaining, 0, $i_user_info_end);
                $remaining = substr($remaining, $i_user_info_end + 1);
            } else $i_user_info = null;
            if (($port_start = strpos($remaining, ':', strpos($remaining, ']'))) !== false) {
                $port = substr($remaining, $port_start + 1);
                if ($port === false || $port === '') $port = null;
                $remaining = substr($remaining, 0, $port_start);
            }else $port = null;
            $return = $this->_set_user_info($i_user_info) &&
                $this->_set_host($remaining) &&
                $this->_set_port($port);
            $cache[$authority] = array($this->_i_user_info,
                $this->_i_host,$this->_port,$return);
            return $return;
        }//783
        protected function _set_user_info($i_user_info): bool {
            if ($i_user_info === null) $this->_i_user_info = null;
            else {
                $this->_i_user_info = $this->_replace_invalid_with_pct_encoding($i_user_info, '!$&\'()*+,;=:');
                $this->_scheme_normalization();
            }
            return true;
        }//841
        protected function _set_host($i_host): bool{
            if ($i_host === null) {
                $this->_i_host = null;
                return true;
            }
            if (strpos($i_host, '[') === 0 && substr($i_host, -1) === ']') {
                if (Requests_IPv6::check_ipv6(substr($i_host, 1, -1)))
                    $this->_i_host = '[' . Requests_IPv6::compress(substr($i_host, 1, -1)) . ']';
                else {
                    $this->_i_host = null;
                    return false;
                }
            } else {
                $i_host = $this->_replace_invalid_with_pct_encoding($i_host, '!$&\'()*+,;=');
                $position = 0;
                $strlen = strlen($i_host);
                while (($position += strcspn($i_host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ%', $position)) < $strlen) {
                    if ($i_host[$position] === '%') $position += 3;
                    else {
                        $i_host[$position] = strtolower($i_host[$position]);
                        $position++;
                    }
                }
                $this->_i_host = $i_host;
            }
            $this->_scheme_normalization();
            return true;
        }//860
        protected function _set_port($port): bool{
            if ($port === null) {
                $this->_port = null;
                return true;
            }
            if (strspn($port, '0123456789') === strlen($port)) {
                $this->_port = (int) $port;
                $this->_scheme_normalization();
                return true;
            }
            $this->_port = null;
            return false;
        }//907
        protected function _set_path($i_path): bool{
            static $cache;
            if (!$cache) $cache = array();
            $i_path = (string) $i_path;
            if (isset($cache[$i_path]))
                $this->_i_path = $cache[$i_path][(int) ($this->_scheme !== null)];
            else {
                $this->__valid = $this->_replace_invalid_with_pct_encoding($i_path, '!$&\'()*+,;=@:/');
                $removed = $this->_remove_dot_segments($this->__valid);
                $cache[$i_path] = array($this->__valid, $removed);
                $this->_i_path = ($this->_scheme !== null) ? $removed : $this->__valid;
            }
            $this->_scheme_normalization();
            return true;
        }//929
        protected function _set_query($i_query): bool{
            if ($i_query === null) $this->_i_query = null;
            else {
                $this->_i_query = $this->_replace_invalid_with_pct_encoding($i_query, '!$&\'()*+,;=:@/?', true);
                $this->_scheme_normalization();
            }
            return true;
        }//957
        protected function _set_fragment($i_fragment): bool{
            if ($i_fragment === null) $this->_i_fragment = null;
            else {
                $this->_i_fragment = $this->_replace_invalid_with_pct_encoding($i_fragment, '!$&\'()*+,;=:@/?');
                $this->_scheme_normalization();
            }
            return true;
        }//974
        protected function _to_uri($string) {
            if (!is_string($string))return false;
            static $non_ascii;
            if (!$non_ascii) $non_ascii = implode('', range("\x80", "\xFF"));
            $position = 0;
            $strlen = strlen($string);
            while (($position += strcspn($string, $non_ascii, $position)) < $strlen) {
                $string = substr_replace($string, sprintf('%%%02X', ord($string[$position])), $position, 1);
                $position += 3;
                $strlen += 2;
            }
            return $string;
        }//991
        protected function _get_iri() {
            if (!$this->is_valid()) return false;
            $iri = '';
            if ($this->_scheme !== null) $iri .= $this->_scheme . ':';
            if (($i_authority = $this->_get_i_authority()) !== null)
                $iri .= '//' . $i_authority;
            $iri .= $this->_i_path;
            if ($this->_i_query !== null) $iri .= '?' . $this->_i_query;
            if ($this->_i_fragment !== null) $iri .= '#' . $this->_i_fragment;
            return $iri;
        }//1017
        protected function _get_uri() {
            return $this->_to_uri($this->_get_iri());
        }//1045
        public function get_uri(){
            return $this->_get_uri();
        }
        protected function _get_i_authority(): ?string{
            if ($this->_i_user_info === null && $this->_i_host === null && $this->_port === null)
                return null;
            $i_authority = '';
            if ($this->_i_user_info !== null) $i_authority .= $this->_i_user_info . '@';
            if ($this->_i_host !== null) $i_authority .= $this->_i_host;
            if ($this->_port !== null)  $i_authority .= ':' . $this->_port;
            return $i_authority;
        }//1054
        protected function _get_authority() {
            $i_authority = $this->_get_i_authority();
            if (is_string($i_authority)) return $this->_to_uri($i_authority);
            else return $i_authority;
        }//1077
    }
}else die;