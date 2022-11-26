<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 12:09
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
if(ABSPATH){
    Interface EventHandler{
        public const DOCTYPE_NONE = 0;
        public const DOCTYPE_PUBLIC = 1;
        public const DOCTYPE_SYSTEM = 2;
        public function doctype($name, $idType = 0, $id = null, $quirks = false);
        public function startTag($name, $attributes = array(), $selfClosing = false);
        public function endTag($name);
        public function comment($cdata);
        public function text($cdata);
        public function eof();
        public function parseError($msg, $line, $col);
        public function cdata($data);
        public function processingInstruction($name, $data = null);
    }
}else die;