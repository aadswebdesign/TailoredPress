<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 18:35
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    trait StreamDecoratorTrait{
        public function __construct(StreamInterface $stream){
            $this->stream = $stream;
        }
        public function __get(string $name){
            if ($name === 'stream') {
                $this->stream = $this->_createStream();
                return $this->stream;
            }
            throw new \UnexpectedValueException("$name not found on class");
        }
        public function __toString(){
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
            /** @noinspection PhpParamsInspection */
            return Utils::copyToString($this);
        }
        public function __call(string $method, array $args){
            $callable = [$this->stream, $method];
            $result = call_user_func_array($callable, $args);
            return $result === $this->stream ? $this : $result;
        }
        public function close(): void{
            $this->stream->close();
            return null;
        }
        public function getMetadata($key = null){
            return $this->stream->getMetadata($key);
        }
        public function detach(){
            return $this->stream->detach();
        }
        public function getSize(): ?int{
            return $this->stream->getSize();
        }
        public function eof(): bool{
            return $this->stream->eof();
        }
        public function tell(): int{
            return $this->stream->tell();
        }
        public function isReadable(): bool{
            return $this->stream->isReadable();
        }
        public function isWritable(): bool{
            return $this->stream->isWritable();
        }
        public function isSeekable(): bool{
            return $this->stream->isSeekable();
        }
        public function rewind(): void{
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            $this->stream->seek($offset, $whence);
            return null;
        }
        public function read($length): string{
            return $this->stream->read($length);
        }
        public function write($string): int{
            return $this->stream->write($string);
        }
        protected function _createStream(): StreamInterface{
            throw new \BadMethodCallException('Not implemented');
        }
    }
}else die;