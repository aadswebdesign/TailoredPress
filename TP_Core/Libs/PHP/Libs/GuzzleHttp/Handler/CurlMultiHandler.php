<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-4-2022
 * Time: 05:28
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Handler;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\Promise;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise\PromiseInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Utils as Http_Utils;
use TP_Core\Libs\PHP\Libs\Http_Message\RequestInterface;
if(ABSPATH){
    class CurlMultiHandler{
        private $__factory;
        private $__selectTimeout;
        private $__active = 0;
        private $__handles = [];
        private $__delays = [];
        private $__options;
        protected $_mh;
        public function __construct(array $options = []){
            $this->__factory = $options['handle_factory'] ?? new CurlFactory(50);
            if (isset($options['select_timeout'])) {
                $this->__selectTimeout = $options['select_timeout'];
            } elseif ($selectTimeout = Http_Utils::getenv('GUZZLE_CURL_SELECT_TIMEOUT')) {
                @trigger_error('Since guzzlehttp/guzzle 7.2.0: Using environment variable GUZZLE_CURL_SELECT_TIMEOUT is deprecated. Use option "select_timeout" instead.', \E_USER_DEPRECATED);
                $this->__selectTimeout = (int) $selectTimeout;
            } else {$this->__selectTimeout = 1;}
            $this->__options = $options['options'] ?? [];
        }
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param $name
         * @return resource
         */
        public function __get($name){
            if ($name !== '_mh'){throw new \BadMethodCallException("Can not get other property as '_mh'.");}
            $multiHandle = \curl_multi_init();
            if (false === $multiHandle){throw new \RuntimeException('Can not initialize curl multi handle.');}
            $this->_mh = $multiHandle;
            foreach ($this->__options as $option => $value){curl_multi_setopt($this->_mh, $option, $value);}
            return $this->_mh;
        }
        public function __destruct(){
            if (isset($this->_mh)) {
                \curl_multi_close($this->_mh);
                unset($this->_mh);
            }
        }
        public function __invoke(RequestInterface $request, array $options): PromiseInterface{
            $easy = $this->__factory->create($request, $options);
            $id = (int) $easy->handle;
            $promise = new Promise(
                [$this, 'execute'],
                function () use ($id) {
                    return $this->__cancel($id);
                }
            );
            $this->__addRequest(['easy' => $easy, 'deferred' => $promise]);
            return $promise;
        }
        public function tick(): void{
            if ($this->__delays) {
                $currentTime = Http_Utils::currentTime();
                foreach ($this->__delays as $id => $delay) {
                    if ($currentTime >= $delay) {
                        unset($this->__delays[$id]);
                        \curl_multi_add_handle(
                            $this->_mh,
                            $this->__handles[$id]['easy']->handle
                        );
                    }
                }
            }
            P\Utils::queue()->run();
            if ($this->__active && \curl_multi_select($this->_mh, $this->__selectTimeout) === -1){\usleep(250);}
            while ( \curl_multi_exec($this->_mh, $this->__active) === \CURLM_CALL_MULTI_PERFORM){
                $this->__processMessages();
            }
            return null;
        }
        public function execute(): void{
            $queue = P\Utils::queue();
            while ($this->__handles || !$queue->isEmpty()) {
                if (!$this->__active && $this->__delays){\usleep($this->__timeToNext());}
                $this->tick();
            }
            return null;
        }
        /** @noinspection MagicMethodsValidityInspection */
        /**
         * @param array $entry
         */
        private function __addRequest(array $entry): void{
            $easy = $entry['easy'];
            $id = (int) $easy->handle;
            $this->__handles[$id] = $entry;
            if (empty($easy->options['delay'])) {
                \curl_multi_add_handle($this->_mh, $easy->handle);
            } else {
                $this->__delays[$id] = Http_Utils::currentTime() + ($easy->options['delay'] / 1000);
            }
            return null;
        }
        private function __cancel($id): bool{
            if (!is_int($id)) {/** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing an integer to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);}
            if (!isset($this->__handles[$id])){ return false;}
            $handle = $this->__handles[$id]['easy']->handle;
            unset($this->__delays[$id], $this->__handles[$id]);
            \curl_multi_remove_handle($this->_mh, $handle);
            \curl_close($handle);
            return true;
        }
        private function __processMessages(): void{
            while ($done = \curl_multi_info_read($this->_mh)) {
                if ($done['msg'] !== \CURLMSG_DONE){ continue;}
                $id = (int) $done['handle'];
                \curl_multi_remove_handle($this->_mh, $done['handle']);
                if (!isset($this->__handles[$id])){ continue;}
                $entry = $this->__handles[$id];
                unset($this->__handles[$id], $this->__delays[$id]);
                $entry['easy']->err_no = $done['result'];
                $deferred = $entry['deferred'];
                if($deferred instanceof PromiseInterface){
                    $deferred->resolve(
                        CurlFactory::finish($this, $entry['easy'], $this->__factory)
                    );
                }
            }
            return null;
        }
        private function __timeToNext(): int{
            $currentTime = Http_Utils::currentTime();
            $nextTime = \PHP_INT_MAX;
            foreach ($this->__delays as $time) {
                if ($time < $nextTime){$nextTime = $time;}
            }
            return ((int) \max(0, $nextTime - $currentTime)) * 1000000;
        }
    }
}else{die;}