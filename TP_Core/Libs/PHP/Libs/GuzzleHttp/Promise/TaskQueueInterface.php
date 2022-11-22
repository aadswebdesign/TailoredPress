<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 21:34
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    interface TaskQueueInterface{
        public function isEmpty();
        public function add(callable $task);
        public function run();
    }
}else{die;}