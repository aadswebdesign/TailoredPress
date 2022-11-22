<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 12:40
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise as P;
use TP_Core\Libs\PHP\Libs\Http_Message as M;
if(ABSPATH){
    class HandlerStack{
        //priv is private
        private $priv__handler;
        private $priv__stack = [];
        private $priv__cached;
        public static function create(?callable $handler = null): self{
            $stack = new self($handler ?: Utils::chooseHandler());
            $stack->push(Middleware::httpErrors(), 'http_errors');
            $stack->push(Middleware::redirect(), 'allow_redirects');
            $stack->push(Middleware::cookies(), 'cookies');
            $stack->push(Middleware::prepareBody(), 'prepare_body');
            return $stack;
        }
        public function __construct(callable $handler = null){
            $this->priv__handler = $handler;
        }
        public function __invoke(M\RequestInterface $request, array $options){
            $handler = $this->resolve();
            return $handler($request, $options);
        }
        public function __toString(){
            $depth = 0;
            $stack = [];
            if ($this->priv__handler !== null){
                $stack[] = "0) Handler: " . $this->priv__debugCallable($this->priv__handler);
            }
            $result = '';
            foreach (\array_reverse($this->priv__stack) as $tuple) {
                $depth++;
                $str = "{$depth}) Name: '{$tuple[1]}', ";
                $str .= "Function: " . $this->priv__debugCallable($tuple[0]);
                $result = "> {$str}\n{$result}";
                $stack[] = $str;
            }
            foreach (\array_keys($stack) as $k) {$result .= "< {$stack[$k]}\n";}
            return $result;
        }
        public function setHandler(callable $handler): void{
            $this->priv__handler = $handler;
            $this->priv__cached = null;
            return null;
        }
        public function hasHandler(): bool{
            return $this->priv__handler !== null ;
        }
        public function unshift(callable $middleware, ?string $name = null): void{
            \array_unshift($this->priv__stack, [$middleware, $name]);
            $this->priv__cached = null;
            return null;
        }
        public function push(callable $middleware, string $name = ''): void{
            $this->priv__stack[] = [$middleware, $name];
            $this->priv__cached = null;
            return null;
        }
        public function before(string $findName, callable $middleware, string $withName = ''): void{
            $this->priv__splice($findName, $withName, $middleware, true);
            return null;
        }
        public function after(string $findName, callable $middleware, string $withName = ''): void{
            $this->priv__splice($findName, $withName, $middleware, false);
            return null;
        }
        public function remove($remove): void{
            if (!is_string($remove) && !is_callable($remove)){
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a callable or string to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->priv__cached = null;
            $idx = \is_callable($remove) ? 0 : 1;
            $this->priv__stack = \array_values(\array_filter(
                $this->priv__stack,
                static function ($tuple) use ($idx, $remove) {
                    return $tuple[$idx] !== $remove;
                }
            ));
            return null;
        }
        public function resolve(): callable{
            if ($this->priv__cached === null) {
                if (($prev = $this->priv__handler) === null){
                    throw new \LogicException('No handler has been specified');
                }
                foreach (\array_reverse($this->priv__stack) as $fn) {$prev = $fn[0]($prev);}
                $this->priv__cached = $prev;
            }
            return $this->priv__cached;
        }
        private function priv__findByName(string $name): int{
            foreach ($this->priv__stack as $k => $v) {
                if ($v[1] === $name) {return $k;}
            }
            throw new \InvalidArgumentException("Middleware not found: $name");
        }
        private function priv__splice(string $findName, string $withName, callable $middleware, bool $before): void{
            $this->priv__cached = null;
            $idx = $this->priv__findByName($findName);
            $tuple = [$middleware, $withName];
            if ($before) {
                if ($idx === 0) {\array_unshift($this->priv__stack, $tuple);}
                else {
                    $replacement = [$tuple, $this->priv__stack[$idx]];
                    \array_splice($this->priv__stack, $idx, 1, $replacement);
                }
            } elseif ($idx === \count($this->priv__stack) - 1) {$this->priv__stack[] = $tuple;}
            else {
                $replacement = [$this->priv__stack[$idx], $tuple];
                \array_splice($this->priv__stack, $idx, 1, $replacement);
            }
            return null;
        }
        private function priv__debugCallable($fn): string{
            if (\is_string($fn)){return "callable({$fn})";}
            if (\is_array($fn)){return \is_string($fn[0]) ? "callable({$fn[0]}::{$fn[1]})": "callable(['" . \get_class($fn[0]) . "', '{$fn[1]}'])";}
            return 'callable(' . \spl_object_hash($fn) . ')';
        }
    }
}else {die;}