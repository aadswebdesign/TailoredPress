<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:44
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class Scanner{
        public const CHARS_HEX = 'abcdefABCDEF01234567890';
        public const CHARS_ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
        public const CHARS_ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        use _mm_vars;
        public function __construct($data, $encoding = 'UTF-8'){
            $data = UTF8Utils::convertToUTF8($data, $encoding);
            $this->_errors = UTF8Utils::checkForIllegalCodepoints($data);
            $data = $this->__replaceLineFeeds($data);
            $this->__data = $data;
            $this->__char = 0;
            $this->__eof = strlen($data);
        }
        public function sequenceMatches($sequence, $caseSensitive = true):bool{
            $portion = substr($this->__data, $this->__char, strlen($sequence));
            return $caseSensitive ? $portion === $sequence : 0 === strcasecmp($portion, $sequence);
        }
        public function position():int{
            return $this->__char;
        }
        public function peek():bool{
            if (($this->__char + 1) <= $this->__eof) return $this->__data[$this->__char + 1];
            return false;
        }
        public function next():bool{
            ++$this->__char;
            if ($this->__char < $this->__eof) return $this->__data[$this->__char];
            return false;
        }
        public function current():bool{
            if ($this->__char < $this->__eof) return $this->__data[$this->__char];
            return false;
        }
        public function consume($count = 1):void{
            $this->__char += $count;
        }
        public function un_consume($howMany = 1):void{
            if (($this->__char - $howMany) >= 0) $this->__char -= $howMany;
        }
        public function getHex(){
            return $this->__doCharsWhile(static::CHARS_HEX);
        }
        public function getAsciiAlpha(){
            return $this->__doCharsWhile(static::CHARS_ALPHA);
        }
        public function getAsciiAlphaNum(){
            return $this->__doCharsWhile(static::CHARS_ALNUM);
        }
        public function getNumeric(){
            return $this->__doCharsWhile('0123456789');
        }
        public function whitespace(){
            if ($this->__char >= $this->__eof) return false;
            $len = strspn($this->__data, "\n\t\f ", $this->__char);
            $this->__char += $len;
            return $len;
        }
        public function currentLine():int{
            if (empty($this->__eof) || 0 === $this->__char) return 1;
            return substr_count($this->__data, "\n", 0, min($this->__char, $this->__eof)) + 1;
        }
        public function charsUntil($mask){
            return $this->__doCharsUntil($mask);
        }
        public function charsWhile($mask){
            return $this->__doCharsWhile($mask);
        }
        public function columnOffset(){
            if (0 === $this->__char) return 0;
            $backwardFrom = $this->__char - 1 - strlen($this->__data);
            $lastLine = strrpos($this->__data, "\n", $backwardFrom);
            if (false !== $lastLine) $findLengthOf = substr($this->__data, $lastLine + 1, $this->__char - 1 - $lastLine);
            else $findLengthOf = substr($this->__data, 0, $this->__char);
            return UTF8Utils::countChars($findLengthOf);
        }
        public function remainingChars():string{
            if ($this->__char < $this->__eof) {
                $data = substr($this->__data, $this->__char);
                $this->__char = $this->__eof;
                return $data;
            }
            return ''; // false;
        }
        private function __replaceLineFeeds($data):string{
            $crlfTable = ["\0" => "\xEF\xBF\xBD","\r\n" => "\n","\r" => "\n",];
            return strtr($data, $crlfTable);
        }
        private function __doCharsUntil($bytes, $max = null){
            if ($this->__char >= $this->__eof) return false;
            if (0 === $max || $max) $len = strcspn($this->__data, $bytes, $this->__char, $max);
            else $len = strcspn($this->__data, $bytes, $this->__char);
            $string = (string) substr($this->__data, $this->__char, $len);
            $this->__char += $len;
            return $string;
        }
        private function __doCharsWhile($bytes, $max = null){
            if ($this->__char >= $this->__eof) return false;
            if (0 === $max || $max)  $len = strspn($this->__data, $bytes, $this->__char, $max);
            else $len = strspn($this->__data, $bytes, $this->__char);
            $string = (string) substr($this->__data, $this->__char, $len);
            $this->__char += $len;
            return $string;
        }
    }
}else die;