<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 23:33
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    class Requests_SSL{
        public static function verify_certificate($host, $cert): bool{
            $has_dns_alt = false;
            if (!empty($cert['extensions']) && !empty($cert['extensions']['subjectAltName'])) {
                $alt_names = explode(',', $cert['extensions']['subjectAltName']);
                foreach ($alt_names as $alt_name) {
                    $alt_name = trim($alt_name);
                    if (strpos($alt_name, 'DNS:') !== 0) continue;
                    $has_dns_alt = true;
                    $alt_name = trim(substr($alt_name, 4));
                    if (self::match_domain($host, $alt_name) === true) return true;
                }
            }
            if (!$has_dns_alt && !empty($cert['subject']['CN']) && self::match_domain($host, $cert['subject']['CN']) === true) return true;
            return false;
        }
        public static function verify_reference_name($reference): bool{
            $parts = explode('.', $reference);
            $first = array_shift($parts);
            if (strpos($first, '*') !== false) {
                if ($first !== '*') return false;
                if (count($parts) < 2) return false;
            }
            foreach ($parts as $part) {
                if (strpos($part, '*') !== false) return false;
            }
            return true;
        }
        public static function match_domain($host, $reference): bool{
            if (self::verify_reference_name($reference) !== true)
                return false;
            if ($host === $reference) return true;
            if (ip2long($host) === false) {
                $parts    = explode('.', $host);
                $parts[0] = '*';
                $wildcard = implode('.', $parts);
                if ($wildcard === $reference) return true;
            }
            return false;
        }
    }
}else die;