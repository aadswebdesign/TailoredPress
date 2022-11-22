<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 13:29
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class StreamWrapper{
        private $__stream;
        private $__mode;
        //added
        private $__cast_as;
        private $__opened_path;
        private $__options;
        private $__path;
        public $context;
        public static function getResource(StreamInterface $stream){
            self::register();
            if ($stream->isReadable())
                $mode = $stream->isWritable() ? 'r+' : 'r';
            elseif ($stream->isWritable())
                $mode = 'w';
            else throw new \InvalidArgumentException('The stream must be readable, writable, or both.');
            return fopen('guzzle://stream', $mode, false, self::createStreamContext($stream));
        }
        public static function createStreamContext(StreamInterface $stream){
            return stream_context_create(['guzzle' => ['stream' => $stream]]);
        }
        public static function register(): void{
            if (!in_array('guzzle', stream_get_wrappers(), true))
                stream_wrapper_register('guzzle', __CLASS__);
            return null;
        }
        public function stream_open(string $path, string $mode, int $options, string &$opened_path = null): bool{
            $this->__options = stream_context_get_options($this->context) ?: $options;
            if (!isset($this->__options['guzzle']['stream'])) return false;
            $this->__mode = $mode;
            $this->__stream = $this->__options['guzzle']['stream'];
            //todo this is going nowhere
            $this->__path = $path;
            $this->__opened_path = $opened_path;
            return true;
        }
        public function stream_read(int $count): string{
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            return $stream->read($count);
        }
        public function stream_write(string $data): int{
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            return $stream->write($data);
        }
        public function stream_tell(): int{
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            return $stream->tell();
        }
        public function stream_eof(): bool{
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            return $stream->eof();
        }
        public function stream_seek(int $offset, int $whence): bool{
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            $stream->seek($offset, $whence);
            return true;
        }
        public function stream_cast(int $cast_as){
            $stream = clone($this->__stream);
            $resource = '';
            if($stream instanceof StreamInterface){
                $resource = $stream->detach();
            }
            //todo is going nowhere
            $this->__cast_as = $cast_as;
            return $resource ?? false;
        }
        public function stream_stat(): array{
            static $modeMap = ['r' => 33060,'rb' => 33060,
                'r+' => 33206,'w' => 33188,'wb' => 33188];
            $stream = null;
            if($this->__stream instanceof StreamInterface){
                $stream = $this->__stream;
            }
            return [
                'dev'=> 0,'ino'=> 0,'mode'=> $modeMap[$this->__mode],
                'nlink' => 0,'uid' => 0,'gid' => 0,'rdev' => 0,
                'size' => $stream->getSize() ?: 0,
                'atime' => 0,'mtime' => 0,'ctime' => 0,'blksize' => 0,'blocks' => 0];
        }
        public function url_stat(): array{ //not used string $path, int $flags
            return ['dev' => 0, 'ino' => 0, 'mode' => 0, 'nlink' => 0, 'uid' => 0,
                'gid' => 0, 'rdev' => 0, 'size' => 0, 'atime' => 0,
                'mtime' => 0,'ctime' => 0,'blksize' => 0,'blocks'  => 0];
        }
    }
}else die;