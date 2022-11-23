<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 01:48
 */
namespace TP_Core\Libs\Request;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class Requests_IDNAEncoder{
        public const ACE_PREFIX = 'xn--';
        public const BOOTSTRAP_BASE         = 36;
        public const BOOTSTRAP_TMIN         = 1;
        public const BOOTSTRAP_TMAX         = 26;
        public const BOOTSTRAP_SKEW         = 38;
        public const BOOTSTRAP_DAMP         = 700;
        public const BOOTSTRAP_INITIAL_BIAS = 72;
        public const BOOTSTRAP_INITIAL_N    = 128;
        public static function encode($string): string{
            $parts = explode('.', $string);
            foreach ($parts as &$part)
                $part = self::to_ascii($part);
            return implode('.', $parts);
        }//43
        public static function to_ascii($string) {
            // Step 1: Check if the string is already ASCII
            if (self::_is_ascii($string)) {
                // Skip to step 7
                if (strlen($string) < 64) return $string;
                throw new Requests_Exception('Provided string is too long', 'idna.provided_too_long', $string);
            }
            $string = self::_name_prep($string);
            if (self::_is_ascii($string)) {
                if (strlen($string) < 64) return $string;
                throw new Requests_Exception('Prepared string is too long', 'idna.prepared_too_long', $string);
            }
            if (strpos($string, self::ACE_PREFIX) === 0)
                throw new Requests_Exception('Provided string begins with ACE prefix', 'idna.provided_is_prefixed', $string);
            $string = self::puny_code_encode($string);
            $string = self::ACE_PREFIX . $string;
            if (strlen($string) < 64) return $string;
            throw new Requests_Exception('Encoded string is too long', 'idna.encoded_too_long', $string);
        }
        protected static function _is_ascii($string): bool{
            return (preg_match('/(?:[^\x00-\x7F])/', $string) !== 1);
        }
        protected static function _name_prep($string) {
            return $string;
        }
        protected static function _utf8_to_code_points($input): array{
            $code_points = array();
            $strlen = strlen($input);
            for ($position = 0; $position < $strlen; $position++) {
                $value = ord($input[$position]);
                // One byte sequence:
                if ((~$value & 0x80) === 0x80) {
                    $character = $value;
                    $length    = 1;
                    $remaining = 0;
                }
                // Two byte sequence:
                elseif (($value & 0xE0) === 0xC0) {
                    $character = ($value & 0x1F) << 6;
                    $length    = 2;
                    $remaining = 1;
                }
                // Three byte sequence:
                elseif (($value & 0xF0) === 0xE0) {
                    $character = ($value & 0x0F) << 12;
                    $length    = 3;
                    $remaining = 2;
                }
                // Four byte sequence:
                elseif (($value & 0xF8) === 0xF0) {
                    $character = ($value & 0x07) << 18;
                    $length    = 4;
                    $remaining = 3;
                } else  throw new Requests_Exception('Invalid Unicode code_point', 'idna.invalid_code_point', $value);
                if ($remaining > 0) {
                    if ($position + $length > $strlen)
                        throw new Requests_Exception('Invalid Unicode code_point', 'idna.invalid_code_point', $character);
                    for ($position++; $remaining > 0; $position++) {
                        $value = ord($input[$position]);
                        if (($value & 0xC0) !== 0x80)
                            throw new Requests_Exception('Invalid Unicode codepoint', 'idna.invalidcodepoint', $character);
                        --$remaining;
                        $character |= ($value & 0x3F) << ($remaining * 6);
                    }
                    $position--;
                }
                if (($length > 1 && $character <= 0x7F) || ($length > 2 && $character <= 0x7FF)|| ($length > 3 && $character <= 0xFFFF)
                    || (($character & 0xFFFE) === 0xFFFE) || ($character >= 0xFDD0 && $character <= 0xFDEF)
                    || (($character > 0xD7FF && $character < 0xF900)|| $character < 0x20 || ($character > 0x7E && $character < 0xA0)|| $character > 0xEFFFD
                    )
                ) throw new Requests_Exception('Invalid Unicode code-point', 'idna.invalid_code_point', $character);
                $code_points[] = $character;
            }
            return $code_points;
        }
        public static function puny_code_encode($input): string{
            $output = '';
            // let n = initial_n
            $n = self::BOOTSTRAP_INITIAL_N;
            // let delta = 0
            $delta = 0;
            // let bias = initial_bias
            $bias = self::BOOTSTRAP_INITIAL_BIAS;
            // let h = b = the number of basic code points in the input
            $h = 0;
            $b = null; // see loop
            // copy them to the output in order
            $code_points = self::_utf8_to_code_points($input);
            $extended   = [];

            foreach ($code_points as $char) {
                if ($char < 128) {
                    // Character is valid ASCII
                    // TODO: this should also check if it's valid for a URL
                    $output .= chr($char);
                    $h++;
                }
                elseif ($char < $n)
                    throw new Requests_Exception('Invalid character', 'idna.character_outside_domain', $char);
                else $extended[$char] = true;
            }
            $extended = array_keys($extended);
            sort($extended);
            $b = $h;
            if ($output !== '') $output .= '-';
            $code_point_count = count($code_points);
            while ($h < $code_point_count) {
                // let m = the minimum code point >= n in the input
                $m = array_shift($extended);
                //printf('next code point to insert is %s' . PHP_EOL, dechex($m));
                // let delta = delta + (m - n) * (h + 1), fail on overflow
                $delta += ($m - $n) * ($h + 1);
                $n = $m;
                foreach ($code_points as $numValue) {
                    $c = $numValue;
                    if ($c < $n) $delta++;
                    elseif ($c === $n) {
                        $q = $delta;
                         for ($k = self::BOOTSTRAP_BASE; ; $k += self::BOOTSTRAP_BASE) {
                            if ($k <= ($bias + self::BOOTSTRAP_TMIN)) $t = self::BOOTSTRAP_TMIN;
                            elseif ($k >= ($bias + self::BOOTSTRAP_TMAX))
                                $t = self::BOOTSTRAP_TMAX;
                            else $t = $k - $bias;
                            if ($q < $t) break;
                            $digit   = $t + (($q - $t) % (self::BOOTSTRAP_BASE - $t));
                            $output .= self::_digit_to_char($digit);
                            $q = floor(($q - $t) / (self::BOOTSTRAP_BASE - $t));
                        }
                        $output .= self::_digit_to_char($q);
                        $bias = self::_adapt($delta, $h + 1, $h === $b);
                        $delta = 0;
                        $h++;
                    }
                }
                $delta++;
                $n++;
            }
            return $output;
        }//229
        protected static function _digit_to_char($digit) {
            if ($digit < 0 || $digit > 35)
                throw new Requests_Exception(sprintf('Invalid digit %d', $digit), 'idna.invalid_digit', $digit);
            $digits = 'abcdefghijklmnopqrstuvwxyz0123456789';
            return $digits[$digit];
        }//341
        protected static function _adapt($delta, $num_points, $first_time): int{
            if ($first_time)
                $delta = floor($delta / self::BOOTSTRAP_DAMP);
            else $delta = floor($delta / 2);
            $delta += floor($delta / $num_points);
            $k = 0;
            $max = floor(((self::BOOTSTRAP_BASE - self::BOOTSTRAP_TMIN) * self::BOOTSTRAP_TMAX) / 2);
            while ($delta > $max) {
                $delta = floor($delta / (self::BOOTSTRAP_BASE - self::BOOTSTRAP_TMIN));
                $k += self::BOOTSTRAP_BASE;
            }
            return $k + floor(((self::BOOTSTRAP_BASE - self::BOOTSTRAP_TMIN + 1) * $delta) / ($delta + self::BOOTSTRAP_SKEW));
        }//363
    }
}else die;