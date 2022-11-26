<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-3-2022
 * Time: 07:00
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
if(ABSPATH){
    trait SimplePie_Net_IPv6{
        private $__c1,$__c2;
        public function un_compress($ip){
            $this->__c1 = -1;
            $this->__c2 = -1;
            if (substr_count($ip, '::') === 1){
                @list($ip1, $ip2) = explode('::', $ip);
                if ($ip1 === '') $this->__c1 = -1;
                else $this->__c1 = substr_count($ip1, ':');
                if ($ip2 === '') $this->__c2 = -1;
                else $this->__c2 = substr_count($ip2, ':');
                if (strpos($ip2, '.') !== false) $this->__c2++;
                if ($this->__c1 === -1 && $this->__c2 === -1) $ip = '0:0:0:0:0:0:0:0';// ::
                // ::xxx
                else if ($this->__c1 === -1){
                    $fill = str_repeat('0:', 7 - $this->__c2);
                    $ip = str_replace('::', $fill, $ip);
                }
                // xxx::
                else if ($this->__c2 === -1){
                    $fill = str_repeat(':0', 7 - $this->__c1);
                    $ip = str_replace('::', $fill, $ip);
                }
                // xxx::xxx
                else {
                    $fill = ':' . str_repeat('0:', 6 - $this->__c2 - $this->__c1);
                    $ip = str_replace('::', $fill, $ip);
                }
            }
            return $ip;
        }
        /**
         * Compresses an IPv6 address
         *
         * RFC 4291 allows you to compress concecutive zero pieces in an address to
         * '::'. This method expects a valid IPv6 address and compresses consecutive
         * zero pieces to '::'.
         *
         * Example:  FF01:0:0:0:0:0:0:101   ->  FF01::101
         *           0:0:0:0:0:0:0:1        ->  ::1
         *
         * @see uncompress()
         * @param string $ip An IPv6 address
         * @return string The compressed IPv6 address
         */
        public function compress($ip):string{
            // Prepare the IP to be compressed
            $ip = $this->un_compress($ip);
            $ip_parts = $this->__split_v6_v4($ip);
            // Replace all leading zeros
            $ip_parts[0] = preg_replace('/(^|:)0+(\d)/', '\1\2', $ip_parts[0]);
            // Find bunches of zeros
            if (preg_match_all('/(?:^|:)(?:0(?::|$))+/', $ip_parts[0], $matches, PREG_OFFSET_CAPTURE)){
                $max = 0;
                $pos = null;
                foreach ($matches[0] as $match){
                    if (strlen($match[0]) > $max){
                        $max = strlen($match[0]);
                        $pos = $match[1];
                    }
                }
                $ip_parts[0] = substr_replace($ip_parts[0], '::', $pos, $max);
            }
            if ($ip_parts[1] !== '') return implode(':', $ip_parts);
            return $ip_parts[0];
        }
        /**
         * Splits an IPv6 address into the IPv6 and IPv4 representation parts
         * RFC 4291 allows you to represent the last two parts of an IPv6 address
         * using the standard IPv4 representation
         *
         * Example:  0:0:0:0:0:0:13.1.68.3
         *           0:0:0:0:0:FFFF:129.144.52.38
         *
         * @param string $ip An IPv6 address
         * @return array [0] contains the IPv6 represented part, and [1] the IPv4 represented part
         */
        private function __split_v6_v4($ip):array{
            if (strpos($ip, '.') !== false){
                $pos = strrpos($ip, ':');
                $ipv6_part = substr($ip, 0, $pos);
                $ipv4_part = substr($ip, $pos + 1);
                return array($ipv6_part, $ipv4_part);
            }
            return array($ip, '');
        }
        /**
         * Checks an IPv6 address
         * Checks if the given IP is a valid IPv6 address
         * @param string $ip An IPv6 address
         * @return bool true if $ip is a valid IPv6 address
         */
        public function check_ipv6($ip):bool{
            $ip = $this->un_compress($ip);
            @list($ipv6, $ipv4) = $this->__split_v6_v4($ip);
            $ipv6 = explode(':', $ipv6);
            $ipv4 = explode('.', $ipv4);
            if ((count($ipv6) === 8 && count($ipv4) === 1) || (count($ipv6) === 6 && count($ipv4) === 4))
            {
                foreach ($ipv6 as $ipv6_part){
                    // The section can't be empty
                    if ($ipv6_part === '') return false;
                    // Nor can it be over four characters
                    if (strlen($ipv6_part) > 4) return false;
                    // Remove leading zeros (this is safe because of the above)
                    $ipv6_part = ltrim($ipv6_part, '0');
                    if ($ipv6_part === '') $ipv6_part = '0';
                    // Check the value is valid
                    $value = hexdec($ipv6_part);
                    if ($value < 0 || $value > 0xFFFF || dechex($value) !== strtolower($ipv6_part))
                        return false;
                }
                if (count($ipv4) === 4)
                {
                    foreach ($ipv4 as $ipv4_part){
                        $value = (int) $ipv4_part;
                        if ((string) $value !== $ipv4_part || $value < 0 || $value > 0xFF) return false;
                    }
                }
                return true;
            }
            return false;
        }
    }
}else die;