<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 10:10
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    class Response implements ResponseInterface{
        use MessageTrait;
        public const PHRASES = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-status',
            208 => 'Already Reported',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
        private $__reasonPhrase;
        private $__statusCode;
        public function __construct(int $status = 200, array $headers = [],$body = null, string $version = '1.1',string $reason = null){
            $this->__assertStatusCodeRange($status);
            $this->__statusCode = $status;
            if ($body !== '' && $body !== null)
                $this->__stream = Utils::streamFor($body);
            $this->__setHeaders($headers);
            $phrases = self::PHRASES[$this->__statusCode];
            if ($reason === '' && isset($phrases))
                $this->__reasonPhrase = $phrases;
            else $this->__reasonPhrase = $reason;
            $this->__protocol = $version;
        }
        public function getStatusCode(): int{
            return $this->__statusCode;
        }
        public function getReasonPhrase(): string{
            return $this->__reasonPhrase;
        }
        public function withStatus($code, $reasonPhrase = ''): ResponseInterface {
            $this->__assertStatusCodeIsInteger($code);
            $code = (int) $code;
            $this->__assertStatusCodeRange($code);
            $new = clone $this;
            $new->__statusCode = $code;
            $phrases = self::PHRASES[$new->__statusCode];
            if ($reasonPhrase === '' && isset($phrases))
                $reasonPhrase = self::PHRASES[$new->__statusCode];
            $new->__reasonPhrase = (string) $reasonPhrase;
            return $new;
        }
        private function __assertStatusCodeIsInteger($statusCode): void{
            if (filter_var($statusCode, FILTER_VALIDATE_INT) === false)
                throw new \InvalidArgumentException('Status code must be an integer value.');
            return null;
        }
        private function __assertStatusCodeRange(int $statusCode): void{
            if ($statusCode < 100 || $statusCode >= 600) {
                throw new \InvalidArgumentException('Status code must be an integer value between 1xx and 5xx.');
            }
            return null;
        }
    }
}else die;