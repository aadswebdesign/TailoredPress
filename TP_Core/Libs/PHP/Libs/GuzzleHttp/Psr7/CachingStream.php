<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 18:33
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class CachingStream implements StreamInterface{
        use StreamDecoratorTrait;
        private $__stream;
        private $__remoteStream;
        private $__skipReadBytes = 0;
        public function __construct(StreamInterface $stream,StreamInterface $target = null) {
            $this->__remoteStream = $stream;
            $this->__stream = $target ?: new Stream(Utils::tryFopen('php://temp', 'r+'));
        }
        public function getSize(): ?int{
            $remoteSize = $this->__remoteStream->getSize();
            if (null === $remoteSize) return null;
            return max($this->__stream->getSize(), $remoteSize);
        }
        public function rewind(): void{
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            if ($whence === SEEK_SET) {
                $byte = $offset;
            } elseif ($whence === SEEK_CUR) {
                $byte = $offset + $this->tell();
            } elseif ($whence === SEEK_END) {
                $size = $this->__remoteStream->getSize();
                if ($size === null) $size = $this->_cacheEntireStream();
                $byte = $size + $offset;
            } else {throw new \InvalidArgumentException('Invalid whence');}
            $diff = $byte - $this->__stream->getSize();
            if ($diff > 0) {
                while ($diff > 0 && !$this->__remoteStream->eof()) {
                    $this->read($diff);
                    $diff = $byte - $this->__stream->getSize();
                }
            } else {$this->__stream->seek($byte);}
            return null;
        }
        public function read($length): string{
            $data = $this->__stream->read($length);
            $remaining = $length - strlen($data);
            if ($remaining) {
                $remoteData = $this->__remoteStream->read(
                    $remaining + $this->__skipReadBytes
                );
                if ($this->__skipReadBytes) {
                    $len = strlen($remoteData);
                    $remoteData = substr($remoteData, $this->__skipReadBytes);
                    $this->__skipReadBytes = max(0, $this->__skipReadBytes - $len);
                }
                $data .= $remoteData;
                $this->__stream->write($remoteData);
            }
            return $data;
        }
        public function write($string): int{
            $overflow = (strlen($string) + $this->tell()) - $this->__remoteStream->tell();
            if ($overflow > 0){ $this->__skipReadBytes += $overflow;}
            return $this->__stream->write($string);
        }
        public function eof(): bool{
            return $this->__stream->eof() && $this->__remoteStream->eof();
        }
        public function close(): void{
            $this->__remoteStream->close();
            $this->__stream->close();
            return null;
        }
        private function _cacheEntireStream(): int{
            $target = new FnStream(['write' => 'strlen']);
            Utils::copyToStream($this, $target);
            return $this->tell();
        }
    }
}else{die;}