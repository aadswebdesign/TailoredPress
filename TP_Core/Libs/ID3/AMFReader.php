<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 22:34
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class AMFReader{
        public $stream;
        public function __construct(AMFStream $stream) {
            $this->stream = $stream;
        }
        /**
         * @return mixed
         */
        public function readData() {
            $value = null;
            $type = $this->stream->readByte();
            switch ($type) {
                case 0:
                    $value = $this->readDouble();
                    break;
                case 1:
                    $value = $this->readBoolean();
                    break;
                case 2:
                    $value = $this->readString();
                    break;
                case 3:
                    $value = $this->readObject();
                    break;
                case 6:
                    return null;
                case 8:
                    $value = $this->readMixedArray();
                    break;
                case 10:
                    $value = $this->readArray();
                    break;
                case 11:
                    $value = $this->readDate();
                    break;
                case 13:
                    $value = $this->readLongString();
                    break;
                case 15:
                    $value = $this->readXML();
                    break;
                case 16:
                    $value = $this->readTypedObject();
                    break;
                default:
                    $value = '(unknown or unsupported data type)';
                    break;
            }
            return $value;
        }//576
        /**
         * @return float|false
         */
        public function readDouble() {
            return $this->stream->readDouble();
        }//648
        public function readBoolean():bool {
            return $this->stream->readByte() === 1;
        }//657
        public function readString():string {
            return $this->stream->readUTF();
        }//662
        public function readObject():array {
            $data = [];
            $key = null;
            while ($key = $this->stream->readUTF()) {
                $data[$key] = $this->readData();
            }
            if (($key === '') && ($this->stream->peekByte() === 0x09)) {
                $this->stream->readByte();
            }
            return $data;
        }//669
        public function readMixedArray():array{
            //$highestIndex = $this->stream->readLong();
            $data = [];
            $key = null;
            while ($key = $this->stream->readUTF()) {
                if (is_numeric($key)) {
                    $key = (int) $key;
                }
                $data[$key] = $this->readData();
            }
            if (($key === '') && ($this->stream->peekByte() === 0x09)) {
                $this->stream->readByte();
            }
            return $data;
        }//690
        public function readArray():array {
            $length = $this->stream->readLong();
            $data = array();

            for ($i = 0; $i < $length; $i++) {
                $data[] = $this->readData();
            }
            return $data;
        }//715
        /**
         * @return float|false
         */
        public function readDate() {
            $timestamp = $this->stream->readDouble();
            $timezone = $this->stream->readInt();
            return $timestamp. ' ' . $timezone;
        }//728
        public function readLongString():string {
            return $this->stream->readLongUTF();
        }//737
        public function readXML():string {
            return $this->stream->readLongUTF();
        }//744
        public function readTypedObject():array{
            //$className = $this->stream->readUTF();
            return $this->readObject();
        }//754
    }
}else{die;}