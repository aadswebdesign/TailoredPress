<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:43
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    final class Utils{
        public static function queue(TaskQueueInterface $assign = null){
            static $queue;
            if ($assign){$queue = $assign;}
            elseif (!$queue){$queue = new TaskQueue();}
            return $queue;
        }
        public static function task(callable $task): Promise
        {
            $queue = self::queue();
            $promise = new Promise([$queue, 'run']);
            $queue->add(function () use ($task, $promise) {
                try {
                    if (PromiseIs::pending($promise)){ $promise->resolve($task());}
                } catch (\Throwable $e) {
                    $promise->reject($e);
                } catch (\Exception $e) {
                    $promise->reject($e);
                }
            });
            return $promise;
        }
        public static function inspect(PromiseInterface $promise): ?array
        {
            try {
                return [
                    'state' => PromiseInterface::FULFILLED,
                    'value' => $promise->wait()
                ];
            } catch (RejectionException $e) {
                return ['state' => PromiseInterface::REJECTED, 'reason' => $e->getReason()];
            } catch (\Throwable $e) {
                return ['state' => PromiseInterface::REJECTED, 'reason' => $e];
            } catch (\Exception $e) {
                return ['state' => PromiseInterface::REJECTED, 'reason' => $e];
            }
        }
        public static function inspectAll($promises): array
        {
            $results = [];
            foreach ($promises as $key => $promise) {$results[$key] = static::inspect($promise);}
            return $results;
        }
        public static function unwrap($promises): array
        {
            $results = [];
            foreach ($promises as $key => $promise) {
                $_promise = null;
                if($promise instanceof PromiseInterface){
                    $_promise = $promise;
                }
                $results[$key] = $_promise->wait();
            }
            return $results;
        }
        public static function all($promises, $recursive = false){
            $results = [];
            /** @noinspection PhpUndefinedMethodInspection *///todo
            $promise = PromiseEach::of(
                $promises,
                static function ($value, $idx) use (&$results) {
                    $results[$idx] = $value;
                },
                static function ($reason, $idx, Promise $aggregate) {
                    $aggregate->reject($reason);
                    $aggregate->reject($idx);
                }
            )->then(function () use (&$results) {
                ksort($results);
                return $results;
            });
            if (true === $recursive) {
                $_promise = null;
                if($promise instanceof PromiseInterface){
                    $_promise = $promise;
                }
                $promise = $_promise->then(function ($results) use ($recursive, &$promises) {
                    foreach ($promises as $promise) {
                        if (PromiseIs::pending($promise)){return self::all($promises, $recursive);}
                    }
                    return $results;
                });
            }
            return $promise;
        }
        public static function some($count, $promises){
            $results = [];
            $rejections = [];
            /** @noinspection PhpUndefinedMethodInspection *///todo
            return PromiseEach::of(
                $promises,
                static function ($value, $idx, PromiseInterface $p) use (&$results, $count) {
                    if (PromiseIs::settled($p)){return;}
                    $results[$idx] = $value;
                    if (count($results) >= $count){ $p->resolve(null);}
                },
                static function ($reason) use (&$rejections) {
                    $rejections[] = $reason;
                }
            )->then(
                function () use (&$results, &$rejections, $count) {
                    if (count($results) !== $count) {
                        throw new AggregateException(
                            'Not enough promises to fulfill count',
                            $rejections
                        );
                    }
                    ksort($results);
                    return array_values($results);
                }
            );
        }
        public static function any($promises){
            $_promises = null;
            if($promises instanceof PromiseInterface){
                $_promises = $promises;
            }
            return self::some(1, $_promises->then(function ($values) {
                return $values[0];
            }));
        }
        public static function settle($promises){
            $results = [];
            /** @noinspection PhpUndefinedMethodInspection *///todo
            return PromiseEach::of(
                $promises,
                static function ($value, $idx) use (&$results) {
                    $results[$idx] = ['state' => PromiseInterface::FULFILLED, 'value' => $value];
                },
                static function ($reason, $idx) use (&$results) {
                    $results[$idx] = ['state' => PromiseInterface::REJECTED, 'reason' => $reason];
                }
            )->then(function () use (&$results) {
                ksort($results);
                return $results;
            });
        }
    }
}else{die;}