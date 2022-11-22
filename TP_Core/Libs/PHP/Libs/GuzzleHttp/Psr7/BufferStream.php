<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 18:23
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class BufferStream implements StreamInterface{
        private $__hwm;
        private $__buffer = '';
        public function __construct(int $hwm = 16384){
            $this->__hwm = $hwm;
        }
        public function __toString(){
            return $this->getContents();
        }
        public function getContents(): string{
            $buffer = $this->__buffer;
            $this->__buffer = '';
            return $buffer;
        }
        public function close(): void{
            $this->__buffer = '';
            return null;
        }
        public function detach(): void{
            $this->close();
            return null;
        }
        public function getSize(): ?int{
            return strlen($this->__buffer);
        }
        public function isReadable(): bool{
            return true;
        }
        public function isWritable(): bool{
            return true;
        }
        public function isSeekable(): bool{
            return false;
        }
        public function rewind(): void{
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            throw new \RuntimeException('Cannot seek a BufferStream');
        }
        public function eof(): bool{
            return strlen($this->__buffer) === '';
        }
        public function tell(): int{
            throw new \RuntimeException('Cannot determine the position of a BufferStream');
        }
        public function read($length): string{
            $currentLength = strlen($this->__buffer);
            if ($length >= $currentLength) {
                $result = $this->__buffer;
                $this->__buffer = '';
            } else {
                // Slice up the result to provide a subset of the buffer.
                $result = substr($this->__buffer, 0, $length);
                $this->__buffer = substr($this->__buffer, $length);
            }
            return $result;
        }
        public function write($string): int{
            $this->__buffer .= $string;
            if (strlen($this->__buffer) >= $this->__hwm){ return 0;}
            return strlen($string);
        }
        public function getMetadata($key = null){
            if ($key === 'hwm'){ return $this->__hwm;}
            return $key ? null : [];
        }
    }
}else{die;}