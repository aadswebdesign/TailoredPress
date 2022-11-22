<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 05:13
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    class LazyOpenStream{
        use StreamDecoratorTrait;
        private $__filename;
        private $__mode;
        public function __construct(string $filename, string $mode){
            $this->__filename = $filename;
            $this->__mode = $mode;
        }
        protected function createStream(): StreamInterface{
            return Utils::streamFor(Utils::tryFopen($this->__filename, $this->__mode));
        }
    }
}else die;