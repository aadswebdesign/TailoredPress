<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 20:36
 */
namespace TP_Core\Traits\Compat\Random;
if(ABSPATH){
    trait _byte_safe_strings{
        public function RandomCompat_strlen($binary_string): int{
            if (!is_string($binary_string))
                throw new \TypeError(
                    'RandomCompat_strlen() expects a string'
                );
            if(defined('MB_OVERLOAD_STRING')&&((int) ini_get('mbstring.func_overload')) & MB_OVERLOAD_STRING)
            $str_len = (int) mb_strlen($binary_string, '8bit');
            else
            $str_len =(int) strlen($binary_string);
            return $str_len;
        }//47
        public function RandomCompat_substr($binary_string, $start, $length = null): string{
            if (!is_string($binary_string))
                throw new \TypeError(
                    'RandomCompat_substr(): First argument should be a string'
                );
            if (!is_int($start))
                throw new \TypeError(
                    'RandomCompat_substr(): Second argument should be an integer'
                );
            if ($length === null) {
                /** @var int $length */
                $length = $this->RandomCompat_strlen($binary_string) - $start;
            } elseif (!is_int($length))
                throw new \TypeError(
                    'RandomCompat_substr(): Third argument should be an integer, or omitted'
                );
            if ($length === 0 && $start === $this->RandomCompat_strlen($binary_string))
                return '';
            if ($start > $this->RandomCompat_strlen($binary_string))
                return '';
            if(defined('MB_OVERLOAD_STRING')&&((int) ini_get('mbstring.func_overload')) & MB_OVERLOAD_STRING)
                $sub_str = (string) mb_substr((string) $binary_string,(int) $start,(int) $length,'8bit');
            else $sub_str = (string) mb_substr((string) $binary_string,(int) $start,(int) $length);
            return $sub_str;
        }
    }
}else die;