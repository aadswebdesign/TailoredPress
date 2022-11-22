<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 05:26
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\MessageInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
if(ABSPATH){
    trait MessageTrait{
        private $__headers = [];
        private $__headerNames  = [];
        private $__protocol = '1.1';
        private $__stream;
        public function getProtocolVersion(): string{
            return $this->__protocol;
        }
        public function withProtocolVersion($version): MessageInterface{
            if ($this->__protocol === $version && $this instanceof MessageInterface) return $this;
            $new_clone = clone $this;
            $new = null;
            if($new_clone instanceof MessageInterface){
                $new = $new_clone;
            }
            $new->__protocol = $version;
            return $new;
        }
        public function getHeaders(): array{
            return $this->__headers;
        }
        public function hasHeader($header): bool{
            return isset($this->__headerNames[strtolower($header)]);
        }
        public function getHeader($header): array{
            $header = strtolower($header);
            if (!isset($this->__headerNames[$header])) return [];
            $header = $this->__headerNames[$header];
            return $this->__headers[$header];
        }
        public function getHeaderLine($header): string{
            return implode(', ', $this->getHeader($header));
        }
        public function withHeader($header, $value): MessageInterface{
            $this->__assertHeader($header);
            $value = $this->__normalizeHeaderValue($value);
            $normalized = strtolower($header);
            $new_clone = clone $this;
            $new = null;
            if($new_clone instanceof MessageInterface){
                $new = $new_clone;
            }
            if (isset($new->__headerNames[$normalized]))
                unset($new->__headers[$new->__headerNames[$normalized]]);
            $new->__headerNames[$normalized] = $header;
            $new->__headers[$header] = $value;

            return $new;
        }
        public function withAddedHeader($header, $value): MessageInterface{
            $this->__assertHeader($header);
            $value = $this->__normalizeHeaderValue($value);
            $normalized = strtolower($header);
            $new_clone = clone $this;
            $new = null;
            if($new_clone instanceof MessageInterface){
                $new = $new_clone;
            }
            if (isset($new->__headerNames[$normalized])) {
                $header = $this->__headerNames[$normalized];
                $new->__headers[$header] = array_merge($this->__headers[$header], $value);
            } else {
                $new->__headerNames[$normalized] = $header;
                $new->__headers[$header] = $value;
            }
            return $new;
        }
        public function withoutHeader($header): MessageInterface{
            $normalized = strtolower($header);
            if (!isset($this->__headerNames[$normalized]) && $this instanceof MessageInterface) return $this;
            $header = $this->__headerNames[$normalized];
            $new = clone $this;
            if($new instanceof MessageInterface){
                unset($new->__headers[$header], $new->__headerNames[$normalized]);
            }
            return $new;
        }
        public function getBody(): StreamInterface{
            if (!$this->__stream)
                $this->__stream = Utils::streamFor('');
            return $this->__stream;
        }
        public function withBody(StreamInterface $body): MessageInterface{
            if ($body === $this->__stream  && $this instanceof MessageInterface) return $this;
            $new = clone $this;
            if($new instanceof MessageInterface){
                $new->__stream = $body;
            }
            return $new;
        }
        private function __setHeaders(array $headers): void{
            $this->__headerNames = $this->__headers = [];
            foreach ($headers as $header => $value) {
                if (is_int($header)) $header = (string) $header;
                $this->__assertHeader($header);
                $value = $this->__normalizeHeaderValue($value);
                $normalized = strtolower($header);
                if (isset($this->__headerNames[$normalized])) {
                    $header = $this->__headerNames[$normalized];
                    $this->__headers[$header] = array_merge($this->__headers[$header], $value);
                } else {
                    $this->__headerNames[$normalized] = $header;
                    $this->__headers[$header] = $value;
                }
            }
            return null;
        }
        private function __normalizeHeaderValue($value): array{
            if (!is_array($value))
                return $this->__trimAndValidateHeaderValues([$value]);
            if (count($value) === 0)
                throw new \InvalidArgumentException('Header value can not be an empty array.');
            return $this->__trimAndValidateHeaderValues($value);
        }
        private function __trimAndValidateHeaderValues(array $values): array{
            return array_map(function ($value) {
                if (!is_scalar($value) && null !== $value) {
                    throw new \InvalidArgumentException(sprintf(
                        'Header value must be scalar or null but %s provided.',
                        is_object($value) ? get_class($value) : gettype($value)
                    ));
                }
                $trimmed = trim((string) $value, " \t");
                $this->__assertValue($trimmed);
                return $trimmed;
            }, array_values($values));
        }
        private function __assertHeader($header): void{
            if (!is_string($header))
                throw new \InvalidArgumentException(sprintf(
                    'Header name must be a string but %s provided.',
                    is_object($header) ? get_class($header) : gettype($header)
                ));
            if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $header))
                throw new \InvalidArgumentException(
                    sprintf(
                        '"%s" is not valid header name',
                        $header
                    )
                );
            return null;
        }
        private function __assertValue(string $value): void{
            if (! preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/', $value)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not valid header value', $value));
            }
            return null;
        }
    }
}else die;