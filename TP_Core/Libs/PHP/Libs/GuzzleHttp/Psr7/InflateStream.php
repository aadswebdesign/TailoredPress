<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 08:13
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    final class InflateStream implements StreamInterface{
        private $__stream;
        use StreamDecoratorTrait;
        public function __construct(StreamInterface $stream){
            $resource = StreamWrapper::getResource($stream);
            stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_READ, ['window' => 15 + 32]);
            /** @noinspection UnusedConstructorDependenciesInspection */
            $this->__stream = $stream->isSeekable() ? new Stream($resource) : new NoSeekStream(new Stream($resource));
        }
    }
}else die;