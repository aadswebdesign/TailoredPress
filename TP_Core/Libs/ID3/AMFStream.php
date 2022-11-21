<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 22:49
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class AMFStream{
        public $bytes;
        public $pos;
        public function __construct(&$bytes) {
            $this->bytes =& $bytes;
            $this->pos = 0;
        }//438
        public function readByte():int { //  8-bit
            return ord($this->bytes[$this->pos++]);
        }//446
        public function readInt():int { // 16-bit
            return ($this->readByte() << 8) + $this->readByte();
        }//453
        public function readLong():int { // 32-bit
            return ($this->readByte() << 24) + ($this->readByte() << 16) + ($this->readByte() << 8) + $this->readByte();
        }//460
        /**
         * @return float|false
         */
        public function readDouble() {
            return getid3_lib::BigEndian2Float($this->read(8));
        }//469
        public function readUTF():string {
            $length = $this->readInt();
            return $this->read($length);
        }//474
        public function readLongUTF():string {
            $length = $this->readLong();
            return $this->read($length);
        }//482
        public function read($length):string {
            $val = substr($this->bytes, $this->pos, $length);
            $this->pos += $length;
            return $val;
        }//492
        public function peekByte():int {
            $pos = $this->pos;
            $val = $this->readByte();
            $this->pos = $pos;
            return $val;
        }//501
        public function peekInt():int {
            $pos = $this->pos;
            $val = $this->readInt();
            $this->pos = $pos;
            return $val;
        }//511
        public function peekLong():int {
            $pos = $this->pos;
            $val = $this->readLong();
            $this->pos = $pos;
            return $val;
        }//521
        /**
         * @return float|false
         */
        public function peekDouble() {
            $pos = $this->pos;
            $val = $this->readDouble();
            $this->pos = $pos;
            return $val;
        }//531
        public function peekUTF():string {
            $pos = $this->pos;
            $val = $this->readUTF();
            $this->pos = $pos;
            return $val;
        }//541
        public function peekLongUTF():int {
            $pos = $this->pos;
            $val = $this->readLongUTF();
            $this->pos = $pos;
            return $val;
        }//551
    }
}else{die;}