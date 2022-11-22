<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 11:43
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    class MultipartStream implements StreamInterface {
        use StreamDecoratorTrait;
        private $__boundary;
        private $__stream;
        public function __construct(array $elements = [], string $boundary = null){
            $this->__boundary = $boundary ?: sha1(uniqid('', true));
            /** @noinspection UnusedConstructorDependenciesInspection */
            $this->__stream = $this->_createStream($elements);
        }
        public function getBoundary(): string{
            return $this->__boundary;
        }
        public function isWritable(): bool{
            return false;
        }
        protected function _createStream(array $elements = []): StreamInterface{
            $stream = new AppendStream();
            foreach ($elements as $element)
                $this->__addElement($stream, $element);
            $stream->addStream(Utils::streamFor("--{$this->__boundary}--\r\n"));
            return $stream;
        }
        private function __getHeaders(array $headers): string{
            $str = '';
            foreach ($headers as $key => $value) $str .= "{$key}: {$value}\r\n";
            return "--{$this->__boundary}\r\n" . trim($str) . "\r\n\r\n";
        }
        private function __addElement(AppendStream $stream, array $element): void{
            foreach (['contents', 'name'] as $key) {
                if (!array_key_exists($key, $element))
                    throw new \InvalidArgumentException("A '{$key}' key is required");
            }
            $element['contents'] = Utils::streamFor($element['contents']);
            //static $body,$headers;
            if (empty($element['filename'])) {
                $uri = $element['contents']->getMetadata('uri');
                if ($uri && \is_string($uri) && \strpos($uri,'php://') !== 0 && \strpos($uri,'data://') !== 0) {
                    $element['filename'] = $uri;
                }
            }
            $body  = $this->__createElement(
                $element['name'],
                $element['contents'],
                $element['filename'] ?? null,
                $element['headers'] ?? []
            );
            $headers[] = $body;
            $stream->addStream(Utils::streamFor($this->__getHeaders($headers)));
            $stream->addStream($body);
            $stream->addStream(Utils::streamFor("\r\n"));
            return null;
        }
        private function __createElement(string $name, StreamInterface $stream, ?string $filename, array $headers): array{
            $disposition = $this->__getHeader($headers, 'content-disposition');
            if (!$disposition) {
                $headers['Content-Disposition'] = ($filename === '0' || $filename)
                    ? sprintf(
                        'form-data; name="%s"; filename="%s"',
                        $name,
                        basename($filename)
                    )
                    : "form-data; name=\"{$name}\"";
            }
            $length = $this->__getHeader($headers, 'content-length');
            if (!$length && ($length = $stream->getSize())) {
                $headers['Content-Length'] = (string) $length;
            }
            $type = $this->__getHeader($headers, 'content-type');
            if (!$type && ($filename === '0' || $filename)) {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($type = MimeType::fromFilename($filename))
                    $headers['Content-Type'] = $type;
            }
            return [$stream, $headers];
        }
        private function __getHeader(array $headers, string $key){
            $lowercaseHeader = strtolower($key);
            foreach ($headers as $k => $v) {
                if (strtolower($k) === $lowercaseHeader) return $v;
            }
            return null;
        }
    }
}else die;