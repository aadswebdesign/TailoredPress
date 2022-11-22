<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 16:38
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class AppendStream implements StreamInterface {
        private $__streams = [];
        private $__seekable = true;
        private $__current = 0;
        private $__pos = 0;
        public function __construct(array $streams = []){
            foreach ($streams as $stream){ $this->addStream($stream);}
        }
        public function __toString(){
            try {
                $this->rewind();
                return $this->getContents();
            } catch (\Throwable $e) {
                if (\PHP_VERSION_ID >= 70400) {
                    throw $e;
                }
                trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
                return '';
            }

        }
        public function addStream(StreamInterface $stream): void{
            if (!$stream->isReadable()){throw new \InvalidArgumentException('Each stream must be readable');}
            if (!$stream->isSeekable()){ $this->__seekable = false;}
            $this->__streams[] = $stream;
            return null;
        }
        public function getContents(): string
        {
            return Utils::copyToString($this);
        }
        public function close(): void {
            $this->__pos = $this->__current = 0;
            $this->__seekable = true;
            foreach ($this->__streams as $stream){ $stream->close();}
            $this->__streams = [];
            return null;
        }
        public function detach(): void
        {
            $this->__pos = $this->__current = 0;
            $this->__seekable = true;
            foreach ($this->__streams as $stream){ $stream->detach();}
            $this->__streams = [];
            return null;
        }
        public function tell(): int{
            return $this->__pos;
        }
        public function getSize(): ?int{
            $size = 0;
            foreach ($this->__streams as $stream) {
                $s = $stream->getSize();
                if ($s === null){ return null;}
                $size += $s;
            }
            return $size;
        }
        public function eof(): bool{
            return !$this->__streams ||
            ($this->__current >= count($this->__streams) - 1 &&
                $this->__streams[$this->__current]->eof());
        }
        public function rewind(): void {
            $this->seek(0);
            return null;
        }
        public function seek($offset, $whence = SEEK_SET): void{
            if (!$this->__seekable){throw new \RuntimeException('This AppendStream is not seekable');}
            if ($whence !== SEEK_SET){throw new \RuntimeException('The AppendStream can only seek with SEEK_SET');}
            $this->__pos = $this->__current = 0;
            foreach ($this->__streams as $i => $stream) {
                try {
                    $stream->rewind();
                } catch (\Exception $e) {
                    throw new \RuntimeException('Unable to seek stream '
                        . $i . ' of the AppendStream', 0, $e);
                }
            }
            while ($this->__pos < $offset && !$this->eof()) {
                $result = $this->read(min(8096, $offset - $this->__pos));
                if ($result === '') {
                    break;
                }
            }
            return null;
        }
        public function read($length): string{
            $buffer = '';
            $total = count($this->__streams) - 1;
            $remaining = $length;
            $progressToNext = false;
            while ($remaining > 0) {
                if ($progressToNext || $this->__streams[$this->__current]->eof()) {
                    $progressToNext = false;
                    if ($this->__current === $total){ break;}
                    $this->__current++;
                }
                $result = $this->__streams[$this->__current]->read($remaining);
                if ($result === '') {
                    $progressToNext = true;
                    continue;
                }
                $buffer .= $result;
                $remaining = $length - strlen($buffer);
            }
            $this->__pos += strlen($buffer);
            return $buffer;
        }
        public function isReadable(): bool{
            return true;
        }
        public function isWritable(): bool{
            return false;
        }
        public function isSeekable(): bool{
            return $this->__seekable;
        }
        public function write($string): int{
            throw new \RuntimeException('Cannot write to an AppendStream');
        }
        public function getMetadata($key = null): ?array
        {
            return $key ? null : [];
        }
    }
}else{die;}