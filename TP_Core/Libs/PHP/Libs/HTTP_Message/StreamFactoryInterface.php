<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 21:57
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface StreamFactoryInterface{
        public function createStream(string $content = ''): StreamInterface;
        public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface;
        public function createStreamFromResource($resource): StreamInterface;
    }
}else die;