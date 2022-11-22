<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 19:05
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    class Stream implements StreamInterface {
        public const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/'; //left private out because of this php version
        public const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';
        private $__stream;
        private $__size;
        private $__seekable;
        private $__readable;
        private $__writable;
        private $__uri;
        private $__customMetadata;
        public function __construct($stream, array $options = []){
            if (!is_resource($stream))
                throw new \InvalidArgumentException('Stream must be a resource');
            if (isset($options['size'])) $this->__size = $options['size'];
            $this->__customMetadata = $options['metadata'] ?? [];
            $this->__stream = $stream;
            $meta = stream_get_meta_data($this->__stream);
            $this->__seekable = $meta['seekable'];
            $this->__readable = (bool)preg_match(self::READABLE_MODES, $meta['mode']);
            $this->__writable = (bool)preg_match(self::WRITABLE_MODES, $meta['mode']);
            $this->__uri = $this->getMetadata('uri');
        }
        public function __destruct(){
            $this->close();
        }
        public function __toString(): string {
            try {
                if ($this->isSeekable()) $this->seek(0);
                return $this->getContents();
            } catch (\Throwable $e) {
                if (\PHP_VERSION_ID >= 70400) throw $e;
                trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
                return '';
            }
        }
        public function getContents(): string{
            if (!isset($this->__stream))
                throw new \RuntimeException('Stream is detached');
            $contents = stream_get_contents($this->__stream);
            if ($contents === false)
                throw new \RuntimeException('Unable to read stream contents');
            return $contents;
        }
        public function close(): void{
            if (isset($this->__stream)) {
                if (is_resource($this->__stream)) fclose($this->__stream);
                $this->detach();
            }
            return null;
        }
        public function detach(){
            if (!isset($this->__stream)) return null;
            $result = $this->__stream;
            unset($this->__stream);
            $this->__size = $this->__uri = null;
            $this->__readable = $this->__writable = $this->__seekable = false;
            return $result;
        }
        public function getSize(): ?int{
            if ($this->__size !== null) return $this->__size;
            if (!isset($this->__stream)) return null;
            if ($this->__uri) clearstatcache(true, $this->__uri);
            $stats = fstat($this->__stream);
            if (is_array($stats) && isset($stats['size'])) {
                $this->__size = $stats['size'];
                return $this->__size;
            }
            return null;
        }
        public function isReadable(): bool{
            return $this->__readable;
        }
        public function isWritable(): bool{
            return $this->__writable;
        }
        public function isSeekable(): bool        {
            return $this->__seekable;
        }
        public function eof(): bool{
            if (!isset($this->__stream))
                throw new \RuntimeException('Stream is detached');
            return feof($this->__stream);
        }
        public function tell(): int{
            if (!isset($this->stream))
                throw new \RuntimeException('Stream is detached');
            $result = ftell($this->__stream);
            if ($result === false)  throw new \RuntimeException('Unable to determine stream position');
            return $result;
        }
        public function rewind(): void{
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            $whence = (int) $whence;
            if (!isset($this->__stream))
                throw new \RuntimeException('Stream is detached');
            if (!$this->__seekable)
                throw new \RuntimeException('Stream is not seekable');
            if (fseek($this->__stream, $offset, $whence) === -1) {
                throw new \RuntimeException('Unable to seek to stream position '
                    . $offset . ' with whence ' . var_export($whence, true));
            }
            return null;
        }
        public function read($length): string{
            if (!isset($this->__stream))
                throw new \RuntimeException('Stream is detached');
            if (!$this->__readable)
                throw new \RuntimeException('Cannot read from non-readable stream');
            if ($length < 0)
                throw new \RuntimeException('Length parameter cannot be negative');
            if (0 === $length) return '';
            $string = fread($this->__stream, $length);
            if (false === $string) throw new \RuntimeException('Unable to read from stream');
            return $string;
        }
        public function write($string): int{
            if (!isset($this->stream)) throw new \RuntimeException('Stream is detached');
            if (!$this->__writable) throw new \RuntimeException('Cannot write to a non-writable stream');
            $this->__size = null;
            $result = fwrite($this->__stream, $string);
            if ($result === false) throw new \RuntimeException('Unable to write to stream');
            return $result;
        }
        public function getMetadata($key = null){
            if (!isset($this->__stream)) return $key ? null : [];
            elseif (!$key) return $this->__customMetadata + stream_get_meta_data($this->__stream);
            elseif (isset($this->__customMetadata[$key]))
                return $this->__customMetadata[$key];
            $meta = stream_get_meta_data($this->__stream);
            return $meta[$key] ?? null;
        }
    }
}else die;