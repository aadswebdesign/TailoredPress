<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 21:08
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class DroppingStream implements StreamInterface{
        use StreamDecoratorTrait;
        private $__maxLength;
        private $__stream;
        public function __construct(StreamInterface $stream, int $maxLength){
            $this->__stream = $stream;
            $this->__maxLength = $maxLength;
        }
        public function write($string): int {
            $diff = $this->__maxLength - $this->__stream->getSize();
            if ($diff <= 0) return 0;
            if (strlen($string) < $diff)
                return $this->__stream->write($string);
            return $this->__stream->write(substr($string, 0, $diff));
        }
    }
}else{die;}