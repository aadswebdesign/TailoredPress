<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 08:51
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class LimitStream implements StreamInterface{
        use StreamDecoratorTrait;
        private $__offset;
        private $__limit;
        private $__stream;
        public function __construct(StreamInterface $stream,int $limit = -1,int $offset = 0){
            $this->__stream = $stream;
            $this->setLimit($limit);
            $this->setOffset($offset);
        }
        public function eof(): bool{
            if ($this->__stream->eof()) return true;
            if ($this->__limit === -1) return false;
            return $this->__stream->tell() >= $this->__offset + $this->__limit;
        }
        public function getSize(): ?int{
            if (null === ($length = $this->__stream->getSize()))
                return null;
            elseif ($this->__limit === -1)
                return $length - $this->__offset;
            else return min($this->__limit, $length - $this->__offset);
        }
        public function seek($offset, $whence = SEEK_SET): void{
            if ($whence !== SEEK_SET || $offset < 0) {
                throw new \RuntimeException(sprintf(
                    'Cannot seek to offset %s with whence %s',
                    $offset,
                    $whence
                ));
            }
            $offset += $this->__offset;
            if ($this->__limit !== -1) {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($offset > $this->__offset + $this->__limit)
                    $offset = $this->__offset + $this->__limit;
            }
            $this->__stream->seek($offset);
            return null;
        }
        public function tell(): int{
            return $this->__stream->tell() - $this->__offset;
        }
        public function setOffset(int $offset): void{
            $current = $this->__stream->tell();
            if ($current !== $offset) {
                if ($this->__stream->isSeekable())
                    $this->__stream->seek($offset);
                elseif ($current > $offset)
                    throw new \RuntimeException("Could not seek to stream offset $offset");
                else $this->__stream->read($offset - $current);
            }
            $this->__offset = $offset;
            return null;
        }
        public function setLimit(int $limit): void{
            $this->__limit = $limit;
            return null;
        }
        public function read($length): string{
            if ($this->__limit === -1) {
                return $this->__stream->read($length);
            }
            $remaining = ($this->__offset + $this->__limit) - $this->__stream->tell();
            if ($remaining > 0) {
                return $this->__stream->read(min($remaining, $length));
            }
            return '';
        }
    }
}else die;