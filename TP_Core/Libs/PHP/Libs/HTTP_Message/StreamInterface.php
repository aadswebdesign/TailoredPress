<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 18:44
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface StreamInterface{
        public function __toString();
        public function close();
        public function detach();
        public function getSize();
        public function tell();
        public function eof();
        public function isSeekable();
        public function seek($offset, $whence = SEEK_SET);
        public function rewind();
        public function isWritable();
        public function write($string);
        public function isReadable();
        public function read($length);
        public function getContents();
        public function getMetadata($key = null);
    }
}else die;