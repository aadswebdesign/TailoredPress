<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 18:47
 */
namespace TP_Core\Libs;
if(ABSPATH){
    class TP_PasswordHash{
        private $__itoa64;
        private $__iteration_count_log2;
        private $__portable_hashes;
        private $__random_state;
        public function __construct($iteration_count_log2, $portable_hashes){
            $this->__itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
                $iteration_count_log2 = 8;
            $this->__iteration_count_log2 = $iteration_count_log2;
            $this->__portable_hashes = $portable_hashes;
            $this->__random_state = microtime();
            if (function_exists('getmypid')) $this->__random_state .= getmypid();
        }//50
        public function PasswordHash($iteration_count_log2, $portable_hashes): void{
            $this->__construct($iteration_count_log2, $portable_hashes);
        }//65
        private function __get_random_bytes($count){
            $output = '';
            if (@is_readable('/dev/urandom') &&
                ($fh = @fopen('/dev/urandom', 'rb'))) {
                $output = fread($fh, $count);
                fclose($fh);
            }
            if (strlen($output) < $count) {
                $output = '';
                for ($i = 0; $i < $count; $i += 16) {
                    $this->__random_state =
                        md5(microtime() . $this->__random_state);
                    $output .= md5($this->__random_state, true);
                }
                $output = substr($output, 0, $count);
            }
            return $output;
        }//70
        private function __encode64($input, $count): string{
            $output = '';
            $i = 0;
            do {
                $value = ord($input[$i++]);
                $output .= $this->__itoa64[$value & 0x3f];
                if ($i < $count)
                    $value |= ord($input[$i]) << 8;
                $output .= $this->__itoa64[($value >> 6) & 0x3f];
                if ($i++ >= $count) break;
                if ($i < $count) $value |= ord($input[$i]) << 16;
                $output .= $this->__itoa64[($value >> 12) & 0x3f];
                if ($i++ >= $count) break;
                $output .= $this->__itoa64[($value >> 18) & 0x3f];
            } while ($i < $count);
            return $output;
        }//92
        private function __generate_salt_private($input): string{
            $output = '$P$';
            $output .= $this->__itoa64[min($this->__iteration_count_log2 +
                ((PHP_VERSION >= '5') ? 5 : 3), 30)];
            $output .= $this->__encode64($input, 6);
            return $output;
        }//115
        private function __crypt_private($password, $setting): string{
            $output = '*0';
            if (strpos($setting, $output) === 0)
                $output = '*1';
            $id = substr($setting, 0, 3);
            if ($id !== '$P$' && $id !== '$H$')
                return $output;
            $count_log2 = strpos($this->__itoa64, $setting[3]);
            if ($count_log2 < 7 || $count_log2 > 30)
                return $output;
            $count = 1 << $count_log2;
            $salt = substr($setting, 4, 8);
            if (strlen($salt) !== 8)
                return $output;
            $hash = md5($salt . $password, true);
            do {
                $hash = md5($hash . $password, true);
            } while (--$count);
            $output = substr($setting, 0, 12);
            $output .= $this->__encode64($hash, 16);
            return $output;
        }//125
        private function __generate_salt_blowfish($input): string{
            $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $output = '$2a$';
            $output .= chr(ord('0') + $this->__iteration_count_log2 / 10);
            $output .= chr(ord('0') + $this->__iteration_count_log2 % 10);
            $output .= '$';
            $i = 0;
            do {
                $c1 = ord($input[$i++]);
                $output .= $itoa64[$c1 >> 2];
                $c1 = ($c1 & 0x03) << 4;
                if ($i >= 16) {
                    $output .= $itoa64[$c1];
                    break;
                }
                $c2 = ord($input[$i++]);
                $c1 |= $c2 >> 4;
                $output .= $itoa64[$c1];
                $c1 = ($c2 & 0x0f) << 2;
                $c2 = ord($input[$i++]);
                $c1 |= $c2 >> 6;
                $output .= $itoa64[$c1];
                $output .= $itoa64[$c2 & 0x3f];
            } while (1);
            return $output;
        }//163
        public function HashPassword($password): string{
            if ( strlen( $password ) > 4096 )  return '*';
            $random = '';
            if (CRYPT_BLOWFISH === 1 && !$this->__portable_hashes) {
                $random = $this->__get_random_bytes(16);
                $hash = crypt($password, $this->__generate_salt_blowfish($random));
                if (strlen($hash) === 60) return $hash;
            }
            if (strlen($random) < 6) $random = $this->__get_random_bytes(6);
            $hash = $this->__crypt_private($password, $this->__generate_salt_private($random));
            if (strlen($hash) === 34) return $hash;
            return '*';
        }//204
        public function CheckPassword($password, $stored_hash): bool{
            if ( strlen( $password ) > 4096 ) return false;
            $hash = $this->__crypt_private($password, $stored_hash);
            if ($hash[0] === '*') $hash = crypt($password, $stored_hash);
            return $hash === $stored_hash;
        }//234
    }
}else die;