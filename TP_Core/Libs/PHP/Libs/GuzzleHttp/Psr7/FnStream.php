<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 20:08
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class FnStream implements StreamInterface{
        public const SLOTS = [
            '__toString', 'close', 'detach', 'rewind',
            'getSize', 'tell', 'eof', 'isSeekable', 'seek', 'isWritable', 'write',
            'isReadable', 'read', 'getContents', 'getMetadata'
        ];
        private $__methods;
        public function __construct(array ...$methods){
            /** @noinspection UnusedConstructorDependenciesInspection */
            $this->__methods = $methods;
            foreach ($this->__methods as $name => $fn)
                $this->{'_fn_' . $name} = $fn;
        }
        public function __get(string $name): void{
            throw new \BadMethodCallException(str_replace('_fn_', '', $name)
                . '() is not implemented in the FnStream');
        }
        public function __destruct()
        {
            if (isset($this->_fn_close)) {
                call_user_func($this->_fn_close);
            }
        }
        public function __wakeup(): void {
            throw new \LogicException('FnStream should never be unserialized');
        }
        public static function decorate(StreamInterface $stream, array $methods): FnStream
        {
            foreach (array_diff(self::SLOTS, array_keys($methods)) as $diff) {
                $callable = [$stream, $diff];
                $methods[$diff] = $callable;
            }
            return new self($methods);
        }
        public function __toString(): string{
            try {
                /** @noinspection PhpUndefinedFieldInspection */
                return call_user_func($this->_fn__toString);
            } catch (\Throwable $e) {
                if (\PHP_VERSION_ID >= 70400)
                    throw $e;
                trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
                return '';
            }
        }
        public function close(): void{
            /** @noinspection PhpUndefinedFieldInspection */
            call_user_func($this->_fn_close);
            return null;
        }
        public function detach(){
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_detach);
        }
        public function getSize(): ?int{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_getSize);
        }
        public function tell(): int{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_tell);
        }
        public function eof(): bool{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_eof);
        }
        public function isSeekable(): bool{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_isSeekable);
        }
        public function seek($offset, $whence = SEEK_SET): void{
            /** @noinspection PhpUndefinedFieldInspection */
            call_user_func($this->_fn_seek, $offset, $whence);
            return null;
        }
        public function rewind(): void {
            /** @noinspection PhpUndefinedFieldInspection */
            call_user_func($this->_fn_rewind);
            return null;
        }
        public function isWritable(): bool{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_isWritable);
        }
        public function write($string): int{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_write, $string);
        }
        public function isReadable(): bool{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_isReadable);
        }
        public function read($length): string{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_read, $length);
        }
        public function getContents(): string{
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_getContents);
        }
        public function getMetadata($key = null){
            /** @noinspection PhpUndefinedFieldInspection */
            return call_user_func($this->_fn_getMetadata, $key);
        }
    }
}else{die;}