<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 12:09
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class PumpStream implements StreamInterface{
        private $__source;
        private $__size;
        private $__tellPos = 0;
        private $__metadata;
        private $__buffer;
        public function __construct(callable $source, array $options = []){
            $this->__source = $source;
            $this->__size = $options['size'] ?? null;
            $this->__metadata = $options['metadata'] ?? [];
            $this->__buffer = new BufferStream();
        }
        public function __toString(): string{
            try {
                return Utils::copyToString($this);
            } catch (\Throwable $e) {
                if (\PHP_VERSION_ID >= 70400)
                    throw $e;
                trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
                return '';
            }
        }
        public function close(): void{
            $this->detach();
            return null;
        }
        public function detach(): void
        {
            $this->__tellPos = 0;
            $this->__source = null;
            return null;
        }
        public function getSize(): ?int{
            return $this->__size;
        }
        public function tell(): int{
            return $this->__tellPos;
        }
        public function eof(): bool{
            return $this->__source === null;
        }
        public function isSeekable(): bool{
            return false;
        }
        public function rewind(): void{
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            throw new \RuntimeException('Cannot seek a PumpStream');
        }
        public function isWritable(): bool{
            return false;
        }
        public function write($string): int{
            throw new \RuntimeException('Cannot write to a PumpStream');
        }
        public function isReadable(): bool{
            return true;
        }
        public function read($length): string{
            $data = $this->__buffer->read($length);
            $readLen = strlen($data);
            $this->__tellPos += $readLen;
            $remaining = $length - $readLen;
            if ($remaining) {
                $this->__pump($remaining);
                $data .= $this->__buffer->read($remaining);
                $this->__tellPos += strlen($data) - $readLen;
            }
            return $data;
        }
        public function getContents(): string{
            $result = '';
            while (!$this->eof()) $result .= $this->read(1000000);
            return $result;
        }
        public function getMetadata($key = null){
            if (!$key)return $this->__metadata;
            return $this->__metadata[$key] ?? null;
        }
        private function __pump(int $length): void{
            if ($this->__source) {
                do {
                    $data = call_user_func($this->__source, $length);
                    if ($data === false || $data === null) {
                        $this->__source = null;
                        return;
                    }
                    $this->__buffer->write($data);
                    $length -= strlen($data);
                } while ($length > 0);
            }
            return null;
        }
    }
}else die;