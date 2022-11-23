<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 13:43
 */
namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Utility\Requests_Utility_CaseInsensitiveDictionary;
use TP_Core\Libs\Request\Response\Requests_Response_Headers;
if(ABSPATH){
    class Requests_Cookie{
        public $attributes = array();
        public $name;
        public $flags = array();
        public $reference_time = 0;
        public $value;
        public function __construct($name, $value, $attributes = array(), $flags = array(), $reference_time = null) {
            $this->name       = $name;
            $this->value      = $value;
            $this->attributes = $attributes;
            $default_flags    = array(
                'creation'    => time(),
                'last-access' => time(),
                'persistent'  => false,
                'host-only'   => true,
            );
            $this->flags      = array_merge($default_flags, $flags);
            $this->reference_time = time();
            if ($reference_time !== null) $this->reference_time = $reference_time;
            $this->normalize();
        }//67
        public function is_expired(): bool{
            if (isset($this->attributes['max-age'])) {
                $max_age = $this->attributes['max-age'];
                return $max_age < $this->reference_time;
            }
            if (isset($this->attributes['expires'])) {
                $expires = $this->attributes['expires'];
                return $expires < $this->reference_time;
            }
            return false;
        }//95
        public function uri_matches(Requests_IRI $uri): bool{
            if (!$this->domain_matches($uri['host'])) return false;
            if (!$this->path_matches($uri['path']))  return false;
            return empty($this->attributes['secure']) || $uri['scheme'] === 'https';
        }//119
        public function domain_matches($string): bool{
            if (!isset($this->attributes['domain'])) return true;
            $domain_string = $this->attributes['domain'];
            if ($domain_string === $string) return true;
            if ($this->flags['host-only'] === true)
                return false;
            if (strlen($string) <= strlen($domain_string)) return false;
            if (substr($string, -1 * strlen($domain_string)) !== $domain_string)
                return false;
            $prefix = substr($string, 0, strlen($string) - strlen($domain_string));
            if (substr($prefix, -1) !== '.') return false;
            return !preg_match('#^(.+\.)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $string);
        }
        public function path_matches($request_path): bool{
            if (empty($request_path)) $request_path = '/';
            if (!isset($this->attributes['path'])) return true;
            $cookie_path = $this->attributes['path'];
            if ($cookie_path === $request_path) return true;
            if ((strpos($request_path, $cookie_path) === 0) && strlen($request_path) > strlen($cookie_path)) {
                if (substr($cookie_path, -1) === '/') return true;
                if ($request_path[strlen($cookie_path)] === '/') return true;
            }
            return false;
        }
        public function normalize(): bool{
            foreach ($this->attributes as $key => $value) {
                $orig_value = $value;
                $value      = $this->_normalize_attribute($key, $value);
                if ($value === null) {
                    unset($this->attributes[$key]);
                    continue;
                }
                if ($value !== $orig_value) $this->attributes[$key] = $value;
            }
            return true;
        }//228
        protected function _normalize_attribute($name, $value) {
            switch (strtolower($name)) {
                case 'expires':
                    if (is_int($value)) return $value;
                    $expiry_time = strtotime($value);
                    if ($expiry_time === false) return null;
                    return $expiry_time;
                case 'max-age':
                    if (is_int($value)) return $value;
                    if (!preg_match('/^-?\d+$/', $value)) return null;
                    $delta_seconds = (int) $value;
                    if ($delta_seconds <= 0) $expiry_time = 0;
                    else $expiry_time = $this->reference_time + $delta_seconds;
                    return $expiry_time;
                case 'domain':
                    if (empty($value)) return null;
                    if ($value[0] === '.') $value = substr($value, 1);
                    return $value;
                default:
                    return $value;
            }
        }//254
        public function format_for_header(): string{
            return sprintf('%s=%s', $this->name, $this->value);
        }
        public function formatForHeader(): string{
            return $this->format_for_header();
        }
        public function format_for_set_cookie(): string{
            $header_value = $this->format_for_header();
            if (!empty($this->attributes)) {
                $parts = array();
                foreach ($this->attributes as $key => $value) {
                    if (is_numeric($key)) $parts[] = $value;
                    else $parts[] = sprintf('%s=%s', $key, $value);
                }
                $header_value .= '; ' . implode('; ', $parts);
            }
            return $header_value;
        }
        public function formatForSetCookie(): string{
            return $this->format_for_set_cookie();
        }
        public function __toString() {
            return (string)$this->value;
        }
        public static function parse($string, $name = '', $reference_time = null): Requests_Cookie{
            $parts   = explode(';', $string);
            $kv_parts = array_shift($parts);
            if (!empty($name))$value = $string;
            elseif (strpos($kv_parts, '=') === false) {
                $name  = '';
                $value = $kv_parts;
            }else @list($name, $value) = explode('=', $kv_parts, 2);
            $name  = trim($name);
            $value = trim($value);
            $attributes = new Requests_Utility_CaseInsensitiveDictionary();
            if (!empty($parts)) {
                foreach ($parts as $part) {
                    if (strpos($part, '=') === false) {
                        $part_key   = $part;
                        $part_value = true;
                    }
                    else {
                        @list($part_key, $part_value) = explode('=', $part, 2);
                        $part_value                  = trim($part_value);
                    }
                    $part_key              = trim($part_key);
                    $attributes[$part_key] = $part_value;
                }
            }
            return new Requests_Cookie($name, $value, $attributes, array(), $reference_time);
        }
        public static function parse_from_headers(Requests_Response_Headers $headers, Requests_IRI $origin = null, $time = null): array{
            $cookie_headers = $headers->getValues('Set-Cookie');
            if (empty($cookie_headers)) return array();
            $cookies = array();
            foreach ($cookie_headers as $header) {
                $parsed = self::parse($header, '', $time);
                if (empty($parsed->attributes['domain']) && $origin !== null) {
                    $parsed->attributes['domain'] = $origin['host'];
                    $parsed->flags['host-only']   = true;
                }else  $parsed->flags['host-only'] = false;
                $path_is_valid = (!empty($parsed->attributes['path']) && $parsed->attributes['path'][0] === '/');
                if (!$path_is_valid && $origin !== 0) {
                    $path = $origin['path'];
                    if (strpos($path, '/') !== 0) $path = '/';
                    elseif (substr_count($path, '/') === 1) $path = '/';
                    else $path = substr($path, 0, strrpos($path, '/'));
                    $parsed->attributes['path'] = $path;
                }
                if ($origin !== 0 && !$parsed->domain_matches($origin['host'])) continue;
                $cookies[$parsed->name] = $parsed;
            }
            return $cookies;
        }
        public static function parseFromHeaders(Requests_Response_Headers $headers): array{
            return self::parse_from_headers($headers);
        }
    }
}else die;