<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:37
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class TaskQueue implements TaskQueueInterface{
        private $__enableShutdown = true;
        private $__queue = [];
        public function __construct($withShutdown = true){
            if ($withShutdown) {
                register_shutdown_function(function () {
                    if ($this->__enableShutdown) {
                        // Only run the tasks if an E_ERROR didn't occur.
                        $err = error_get_last();
                        if (!$err || ($err['type'] ^ E_ERROR)){$this->run();}
                    }
                });
            }
        }
        public function isEmpty():string{
            return !$this->__queue;
        }
        public function add(callable $task):string{
            $this->__queue[] = $task;
            return null;
        }
        /** @noinspection ReturnTypeCanBeDeclaredInspection */
        public function run(){
            while ($task = array_shift($this->__queue)) {
                /** @var callable $task */
                $task();
            }
        }
        public function disableShutdown(): void{
            $this->__enableShutdown = false;
            return null;
        }
    }
}else{die;}
