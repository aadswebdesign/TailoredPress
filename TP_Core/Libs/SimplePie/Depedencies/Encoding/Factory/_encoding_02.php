<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 16:15
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Decode_HTML_Entities;
if(ABSPATH){
    trait _encoding_02{
        public function sp_percent_encoding_normalization($match){
            $integer = hexdec($match[1]);
            if (($integer >= 0x41 && $integer <= 0x5A) || ($integer >= 0x61 && $integer <= 0x7A) || ($integer >= 0x30 && $integer <= 0x39) || $integer === 0x2D || $integer === 0x2E || $integer === 0x5F || $integer === 0x7E)
                return chr($integer);
            return strtoupper($match[0]);
        }//270 from SimplePie_Misc
        public function sp_change_encoding($data, $input, $output){
            $input = $this->__sp_encoding($input);
            $output = $this->__sp_encoding($output);
            // We fail to fail on non US-ASCII bytes
            if ($input === 'US-ASCII'){
                $non_ascii_octects = '';
                if (!$non_ascii_octects){
                    for ($i = 0x80; $i <= 0xFF; $i++)
                        $non_ascii_octects .= chr($i);
                }
                $data = substr($data, 0, strcspn($data, $non_ascii_octects));
            }
            // This is first, as behaviour of this is completely predictable
            if ($input === 'windows-1252' && $output === 'UTF-8')
                return $this->__sp_windows_1252_to_utf8($data);
            // This is second, as behaviour of this varies only with PHP version (the middle part of this expression checks the encoding is supported).
            elseif (function_exists('mb_convert_encoding') && ($return = $this->__sp_change_encoding_mb_string($data, $input, $output)))
                return $return;
            // This is third, as behaviour of this varies with OS userland and PHP version
            elseif (function_exists('iconv') && ($return = $this->__sp_change_encoding_icon_v($data, $input, $output)))
                return $return;
            // This is last, as behaviour of this varies with OS user land and PHP version
            elseif (class_exists('\UConverter') && ($return = $this->__sp_change_encoding_u_converter($data, $input, $output)))
                return $return;
            // If we can't do anything, just fail
            return false;
        }//303 from SimplePie_Misc
        public function sp_get_curl_version(){
            if (is_array($curl = curl_version()))
                $curl = $curl['version'];
            elseif (strpos($curl, 'curl/') === 0)
                $curl = substr($curl, 5, strcspn($curl, "\x09\x0A\x0B\x0C\x0D", 5));
            elseif (strpos($curl, 'libcurl/') === 0)
                $curl = substr($curl, 8, strcspn($curl, "\x09\x0A\x0B\x0C\x0D", 8));
            else $curl = 0;
            return $curl;
        }//1726 from SimplePie_Misc
        public function sp_strip_comments($data):string{
            $output = '';
            while (($start = strpos($data, '<!--')) !== false){
                $output .= substr($data, 0, $start);
                if (($end = strpos($data, '-->', $start)) !== false)
                    $data = substr_replace($data, '', 0, $end + 3);
                else $data = '';
            }
            return $output . $data;
        }//1753 from SimplePie_Misc
        public function sp_parse_date($dt){
            return $this->get_date_object($dt);
        }//1771 from SimplePie_Misc
        public function sp_entities_decode($data): ?array{
            $decoder = new SimplePie_Decode_HTML_Entities($data);
            if($decoder instanceof SimplePie_Decode_HTML_Entities){
                return $decoder->parse();
            }
        }
        // from SimplePie_Misc
        private function __sp_windows_1252_to_utf8($string):string{
            $convert_table = array("\x80" => "\xE2\x82\xAC", "\x81" => "\xEF\xBF\xBD", "\x82" => "\xE2\x80\x9A", "\x83" => "\xC6\x92", "\x84" => "\xE2\x80\x9E", "\x85" => "\xE2\x80\xA6", "\x86" => "\xE2\x80\xA0", "\x87" => "\xE2\x80\xA1", "\x88" => "\xCB\x86", "\x89" => "\xE2\x80\xB0", "\x8A" => "\xC5\xA0", "\x8B" => "\xE2\x80\xB9", "\x8C" => "\xC5\x92", "\x8D" => "\xEF\xBF\xBD", "\x8E" => "\xC5\xBD", "\x8F" => "\xEF\xBF\xBD", "\x90" => "\xEF\xBF\xBD", "\x91" => "\xE2\x80\x98", "\x92" => "\xE2\x80\x99", "\x93" => "\xE2\x80\x9C", "\x94" => "\xE2\x80\x9D", "\x95" => "\xE2\x80\xA2", "\x96" => "\xE2\x80\x93", "\x97" => "\xE2\x80\x94", "\x98" => "\xCB\x9C", "\x99" => "\xE2\x84\xA2", "\x9A" => "\xC5\xA1", "\x9B" => "\xE2\x80\xBA", "\x9C" => "\xC5\x93", "\x9D" => "\xEF\xBF\xBD", "\x9E" => "\xC5\xBE", "\x9F" => "\xC5\xB8", "\xA0" => "\xC2\xA0", "\xA1" => "\xC2\xA1", "\xA2" => "\xC2\xA2", "\xA3" => "\xC2\xA3", "\xA4" => "\xC2\xA4", "\xA5" => "\xC2\xA5", "\xA6" => "\xC2\xA6", "\xA7" => "\xC2\xA7", "\xA8" => "\xC2\xA8", "\xA9" => "\xC2\xA9", "\xAA" => "\xC2\xAA", "\xAB" => "\xC2\xAB", "\xAC" => "\xC2\xAC", "\xAD" => "\xC2\xAD", "\xAE" => "\xC2\xAE", "\xAF" => "\xC2\xAF", "\xB0" => "\xC2\xB0", "\xB1" => "\xC2\xB1", "\xB2" => "\xC2\xB2", "\xB3" => "\xC2\xB3", "\xB4" => "\xC2\xB4", "\xB5" => "\xC2\xB5", "\xB6" => "\xC2\xB6", "\xB7" => "\xC2\xB7", "\xB8" => "\xC2\xB8", "\xB9" => "\xC2\xB9", "\xBA" => "\xC2\xBA", "\xBB" => "\xC2\xBB", "\xBC" => "\xC2\xBC", "\xBD" => "\xC2\xBD", "\xBE" => "\xC2\xBE", "\xBF" => "\xC2\xBF", "\xC0" => "\xC3\x80", "\xC1" => "\xC3\x81", "\xC2" => "\xC3\x82", "\xC3" => "\xC3\x83", "\xC4" => "\xC3\x84", "\xC5" => "\xC3\x85", "\xC6" => "\xC3\x86", "\xC7" => "\xC3\x87", "\xC8" => "\xC3\x88", "\xC9" => "\xC3\x89", "\xCA" => "\xC3\x8A", "\xCB" => "\xC3\x8B", "\xCC" => "\xC3\x8C", "\xCD" => "\xC3\x8D", "\xCE" => "\xC3\x8E", "\xCF" => "\xC3\x8F", "\xD0" => "\xC3\x90", "\xD1" => "\xC3\x91", "\xD2" => "\xC3\x92", "\xD3" => "\xC3\x93", "\xD4" => "\xC3\x94", "\xD5" => "\xC3\x95", "\xD6" => "\xC3\x96", "\xD7" => "\xC3\x97", "\xD8" => "\xC3\x98", "\xD9" => "\xC3\x99", "\xDA" => "\xC3\x9A", "\xDB" => "\xC3\x9B", "\xDC" => "\xC3\x9C", "\xDD" => "\xC3\x9D", "\xDE" => "\xC3\x9E", "\xDF" => "\xC3\x9F", "\xE0" => "\xC3\xA0", "\xE1" => "\xC3\xA1", "\xE2" => "\xC3\xA2", "\xE3" => "\xC3\xA3", "\xE4" => "\xC3\xA4", "\xE5" => "\xC3\xA5", "\xE6" => "\xC3\xA6", "\xE7" => "\xC3\xA7", "\xE8" => "\xC3\xA8", "\xE9" => "\xC3\xA9", "\xEA" => "\xC3\xAA", "\xEB" => "\xC3\xAB", "\xEC" => "\xC3\xAC", "\xED" => "\xC3\xAD", "\xEE" => "\xC3\xAE", "\xEF" => "\xC3\xAF", "\xF0" => "\xC3\xB0", "\xF1" => "\xC3\xB1", "\xF2" => "\xC3\xB2", "\xF3" => "\xC3\xB3", "\xF4" => "\xC3\xB4", "\xF5" => "\xC3\xB5", "\xF6" => "\xC3\xB6", "\xF7" => "\xC3\xB7", "\xF8" => "\xC3\xB8", "\xF9" => "\xC3\xB9", "\xFA" => "\xC3\xBA", "\xFB" => "\xC3\xBB", "\xFC" => "\xC3\xBC", "\xFD" => "\xC3\xBD", "\xFE" => "\xC3\xBE", "\xFF" => "\xC3\xBF");
            return strtr($string, $convert_table);
        }//288 from SimplePie_Misc
        private function __sp_change_encoding_mb_string($data, $input, $output){
            if ($input === 'windows-949') $input = 'EUC-KR';
            if ($output === 'windows-949') $output = 'EUC-KR';
            if ($input === 'Windows-31J') $input = 'SJIS';
            if ($output === 'Windows-31J') $output = 'SJIS';
            // Check that the encoding is supported
            if (!in_array($input, mb_list_encodings(), true))
                return false;
            if (@mb_convert_encoding("\x80", 'UTF-16BE', $input) === "\x00\x80")
                return false;
            // Let's do some conversion
            if ($return = @mb_convert_encoding($data, $output, $input))
                return $return;
            return false;
        }//347 from SimplePie_Misc
        private function __sp_change_encoding_icon_v($data, $input, $output):string{
            return @iconv($input, $output, $data);
        }//386 from SimplePie_Misc
        private function __sp_change_encoding_u_converter($data, $input, $output):string{
            return mb_convert_encoding($data, $output, $input);//todo instead of UConvert
        }//379 from SimplePie_Misc
    }
}else die;