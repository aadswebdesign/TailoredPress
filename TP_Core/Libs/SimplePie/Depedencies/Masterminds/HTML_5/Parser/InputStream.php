<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 12:22
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
if(ABSPATH){
    interface InputStream {
        public function currentLine();
        public function columnOffset();
        public function remainingChars();
        public function charsUntil($bytes, $max = null);
        public function charsWhile($bytes, $max = null);
        public function unconsume($howMany = 1);
        public function peek();
    }
}else die;