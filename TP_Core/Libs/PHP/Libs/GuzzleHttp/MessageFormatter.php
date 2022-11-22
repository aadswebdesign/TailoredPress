<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 13:28
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\Http_Message\MessageInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\Http_Message\ResponseInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7 as Psr;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
if(ABSPATH){
    class MessageFormatter implements MessageFormatterInterface{
        private $__template;
        public const HTTP_CLF = "{hostname} {req_header_User-Agent} - [{date_common_log}] \"{method} {target} HTTP/{version}\" {code} {res_header_Content-Length}";
        public const HTTP_DEBUG = ">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{error}";
        public const HTTP_SHORT = '[{ts}] "{method} {target} HTTP/{version}" {code}';
        public function __construct(?string $template = self::HTTP_CLF){
            $this->__template = $template ?: self::HTTP_CLF;
        }
        public function format(RequestInterface $request, ?ResponseInterface $response = null, ?\Throwable $error = null): string{
            $cache = [];
            /** @var string */
            return \preg_replace_callback(
                '/{\s*([A-Za-z_\-\.0-9]+)\s*}/',
                function (array $matches) use ($request, $response, $error, &$cache) {

                    $body_result = $request->getBody();
                    if($body_result instanceof UriInterface){}
                    if (isset($cache[$matches[1]]))return $cache[$matches[1]];
                    $result = '';
                    switch ($matches[1]) {
                        case 'request':
                            $result = Psr\Message::toString($request);
                            break;
                        case 'response':
                            $result = $response ? Psr\Message::toString($response) : '';
                            break;
                        case 'req_headers':
                            $result = \trim($request->getMethod()
                                    . ' ' . $request->getRequestTarget())
                                . ' HTTP/' . $request->getProtocolVersion() . "\r\n"
                                . $this->headers($request);
                            break;
                        case 'res_headers':
                            $result = $response ?
                                \sprintf(
                                    'HTTP/%s %d %s',
                                    $response->getProtocolVersion(),
                                    $response->getStatusCode(),
                                    $response->getReasonPhrase()
                                ) . "\r\n" . $this->headers($response)
                                : 'NULL';
                            break;
                        case 'req_body':
                            $result = $body_result->__toString();
                            break;
                        case 'res_body':
                            if (!$response instanceof ResponseInterface) {
                                $result = 'NULL';
                                break;
                            }
                            $body_stream = $response->getBody();
                            if($body_stream instanceof StreamInterface){}
                            if (!$body_stream->isSeekable()) {
                                $result = 'RESPONSE_NOT_LOGGEABLE';
                                break;
                            }
                            $result = $body_result->__toString();
                            break;
                        case 'ts':
                        case 'date_iso_8601':
                            $result = \gmdate('c');
                            break;
                        case 'date_common_log':
                            $result = \date('d/M/Y:H:i:s O');
                            break;
                        case 'method':
                            $result = $request->getMethod();
                            break;
                        case 'version':
                            $result = $request->getProtocolVersion();
                            break;
                        case 'uri':
                        case 'url':
                            $_request_url = $request->getUri();
                            $request_url = null;
                            if($_request_url instanceof UriInterface){
                                $request_url = $_request_url;
                            }
                            $result = $request_url->__toString();
                            break;
                        case 'target':
                            $result = $request->getRequestTarget();
                            break;
                        case 'req_version':
                            $result = $request->getProtocolVersion();
                            break;
                        case 'res_version':
                            $result = $response ? $response->getProtocolVersion() : 'NULL';
                            break;
                        case 'host':
                            $result = $request->getHeaderLine('Host');
                            break;
                        case 'hostname':
                            $result = \gethostname();
                            break;
                        case 'code':
                            $result = $response ? $response->getStatusCode() : 'NULL';
                            break;
                        case 'phrase':
                            $result = $response ? $response->getReasonPhrase() : 'NULL';
                            break;
                        case 'error':
                            $result = $error ? $error->getMessage() : 'NULL';
                            break;
                        default:
                            if (\strpos($matches[1], 'req_header_') === 0)
                                $result = $request->getHeaderLine(\substr($matches[1], 11));
                            elseif (\strpos($matches[1], 'res_header_') === 0)
                                $result = $response ? $response->getHeaderLine(\substr($matches[1], 11)) : 'NULL';
                    }
                    $cache[$matches[1]] = $result;
                    return $result;
                },
                $this->__template
            );
        }
        private function headers(MessageInterface $message): string{
            $result = '';
            foreach ($message->getHeaders() as $name => $values)
                $result .= $name . ': ' . \implode(', ', $values) . "\r\n";
            return \trim($result);
        }
    }
}else die;