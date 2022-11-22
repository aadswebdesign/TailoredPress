<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 12:08
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception\RequestException;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp as H;
use TP_Core\Libs\PHP\Libs\Http_Message as M;
if(ABSPATH){
    class MockHandler implements \Countable{
        private $__queue = [];
        private $__lastRequest;
        private $__lastOptions = [];
        private $__onFulfilled;
        private $__onRejected;
        public static function createWithMiddleware(array $queue = null, callable $onFulfilled = null, callable $onRejected = null): H\HandlerStack{
            return H\HandlerStack::create(new self($queue, $onFulfilled, $onRejected));
        }
        public function __construct(array $queue = null, callable $onFulfilled = null, callable $onRejected = null){
            $this->__onFulfilled = $onFulfilled;
            $this->__onRejected = $onRejected;
            if ($queue){$this->append(...array_values($queue));}
        }
        public function __invoke(M\RequestInterface $request, array $options): P\PromiseInterface{
            if (!$this->__queue) {throw new \OutOfBoundsException('Mock queue is empty');}
            if (isset($options['delay']) && \is_numeric($options['delay'])){\usleep((int) $options['delay'] * 1000);}

            $this->__lastRequest = $request;
            $this->__lastOptions = $options;
            $response = \array_shift($this->__queue);
            if (isset($options['on_headers'])) {
                if (!\is_callable($options['on_headers'])){ throw new \InvalidArgumentException('on_headers must be callable');}
                try {
                    $options['on_headers']($response);
                } catch (\Exception $e) {
                    $msg = 'An error was encountered during the on_headers event';
                    $response = new RequestException($msg, $request, $response, $e);
                }
            }
            if (\is_callable($response)){$response = $response($request, $options);}
            $response = $response instanceof \Throwable
                ? P\Create::rejectionFor($response)
                : P\Create::promiseFor($response);
            return $response->then(
                function (?M\ResponseInterface $value) use ($request, $options) {
                    $this->__invokeStats($request, $options, $value);
                    if ($this->__onFulfilled){($this->__onFulfilled)($value);}
                    if ($value !== null && isset($options['sink'])) {
                        $contents = null;
                        if($value instanceof M\ResponseInterface){
                            $contents = (string) $value->getBody();
                        }
                        $sink = $options['sink'];
                        if (\is_resource($sink)){ \fwrite($sink, $contents);}
                        elseif (\is_string($sink)) {\file_put_contents($sink, $contents);}
                        elseif ($sink instanceof M\StreamInterface){$sink->write($contents);}
                    }
                    return $value;
                },
                function ($reason) use ($request, $options) {
                    if($request instanceof M\RequestInterface){$this->__invokeStats($request, $options, null, $reason);}
                    if ($this->__onRejected){($this->__onRejected)($reason);}
                    return P\Create::rejectionFor($reason);
                }
            );
        }//80
        public function append(...$values): void{
            foreach ($values as $value) {
                if ($value instanceof M\ResponseInterface
                    || $value instanceof \Throwable
                    || $value instanceof P\PromiseInterface
                    || \is_callable($value)
                ){$this->__queue[] = $value;}
                else{throw new \TypeError('Expected a Response, Promise, Throwable or callable. Found ' . H\Utils::describeType($value));}
            }
            return null;
        }//152
        public function getLastRequest(): ?M\RequestInterface{
            return $this->__lastRequest;
        }//170
        public function getLastOptions(): array{
            return $this->__lastOptions;
        }//178
        public function count(): int{
            return \count($this->__queue);
        }//186
        public function reset(): void{
            $this->__queue = [];
            return null;
        }//191
        private function __invokeStats(M\RequestInterface $request,array $options,M\ResponseInterface $response = null,$reason = null): void {
            if (isset($options['on_stats'])) {
                $transferTime = $options['transfer_time'] ?? 0;
                $stats = new H\TransferStats($request, $response, $transferTime, $reason);
                ($options['on_stats'])($stats);
            }
            return null;
        }//199
    }
}else {die;}