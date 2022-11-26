<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 20:13
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Decode_HTML_Entities{
        use _sp_vars;
        use _html_entities;
        use _encodings;
        public function __construct($data){
            $this->__sp_data = $data;
        }
        public function parse():array{
            while (($this->__sp_position = strpos($this->__sp_data, '&', $this->__sp_position)) !== false){
                $this->__consume();
                $this->__entity();
                $this->__sp_consumed = '';
            }
            return $this->__sp_data;
        }
        private function __consume():bool {
            if (isset($this->__sp_data[$this->__sp_position])){
                $this->__sp_consumed .= $this->__sp_data[$this->__sp_position];
                return $this->__sp_data[$this->__sp_position++];
            }
            return false;
        }
        private function __consume_range($chars){
            if ($len = strspn($this->__sp_data, $chars, $this->__sp_position)){
                $data = substr($this->__sp_data, $this->__sp_position, $len);
                $this->__sp_consumed .= $data;
                $this->__sp_position += $len;
                return $data;
            }
            return false;
        }
        private function __un_consume():void{
            $this->__sp_consumed = substr($this->__sp_consumed, 0, -1);
            $this->__sp_position--;
        }
        private function __entity():void{
            switch ($this->__consume()){
                case "\x09":
                case "\x0A":
                case "\x0B":
                case "\x0C":
                case "\x20":
                case "\x3C":
                case "\x26":
                case false:
                    break;
                case "\x23":
                    switch ($this->__consume()){
                        case "\x78":
                        case "\x58":
                            $range = '0123456789ABCDEFabcdef';
                            $hex = true;
                            break;
                        default:
                            $range = '0123456789';
                            $hex = false;
                            $this->__un_consume();
                            break;
                    }
                    if ($code_point = $this->__consume_range($range)){
                        $windows_1252_specials = array(0x0D => "\x0A", 0x80 => "\xE2\x82\xAC", 0x81 => "\xEF\xBF\xBD", 0x82 => "\xE2\x80\x9A", 0x83 => "\xC6\x92", 0x84 => "\xE2\x80\x9E", 0x85 => "\xE2\x80\xA6", 0x86 => "\xE2\x80\xA0", 0x87 => "\xE2\x80\xA1", 0x88 => "\xCB\x86", 0x89 => "\xE2\x80\xB0", 0x8A => "\xC5\xA0", 0x8B => "\xE2\x80\xB9", 0x8C => "\xC5\x92", 0x8D => "\xEF\xBF\xBD", 0x8E => "\xC5\xBD", 0x8F => "\xEF\xBF\xBD", 0x90 => "\xEF\xBF\xBD", 0x91 => "\xE2\x80\x98", 0x92 => "\xE2\x80\x99", 0x93 => "\xE2\x80\x9C", 0x94 => "\xE2\x80\x9D", 0x95 => "\xE2\x80\xA2", 0x96 => "\xE2\x80\x93", 0x97 => "\xE2\x80\x94", 0x98 => "\xCB\x9C", 0x99 => "\xE2\x84\xA2", 0x9A => "\xC5\xA1", 0x9B => "\xE2\x80\xBA", 0x9C => "\xC5\x93", 0x9D => "\xEF\xBF\xBD", 0x9E => "\xC5\xBE", 0x9F => "\xC5\xB8");
                        if($hex) $code_point = hexdec($code_point);
                        else $code_point = (int)($code_point);
                        if (isset($windows_1252_specials[$code_point]))
                            $replacement = $windows_1252_specials[$code_point];
                        else $replacement = $this->sp_code_point_to_utf8($code_point);
                        if (!in_array($this->__consume(), array(';', false), true)) $this->__un_consume();
                        $consumed_length = strlen($this->__sp_consumed);
                        $this->__sp_data = substr_replace($this->__sp_data, $replacement, $this->__sp_position - $consumed_length, $consumed_length);
                        $this->__sp_position += strlen($replacement) - $consumed_length;
                    }
                    break;
                default:
                    $consumed = null;
                    for ($i = 0, $match = null; $i < 9 && $this->__consume() !== false; $i++){
                        $consumed = substr($this->__sp_consumed, 1);
                        if (isset($entities[$consumed])) $match = $consumed;
                    }
                    if ($match !== null){
                        $this->__sp_data = substr_replace($this->__sp_data, $this->_entities[$match], $this->__sp_position - strlen($consumed) - 1, strlen($match) + 1);
                        $this->__sp_position += strlen($this->_entities[$match]) - strlen($consumed) - 1;
                    }
                    break;
            }
        }
    }
}else die;