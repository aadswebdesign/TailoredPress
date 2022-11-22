<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 12:05
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class NoSeekStream implements StreamInterface{
        use StreamDecoratorTrait;
        public function seek($offset, $whence = SEEK_SET): void{
            throw new \RuntimeException('Cannot seek a NoSeekStream');
        }
        public function isSeekable(): bool{
            return false;
        }
    }
}else die;