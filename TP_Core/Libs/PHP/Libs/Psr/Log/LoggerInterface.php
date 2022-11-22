<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 18:08
 */
namespace TP_Core\Libs\PHP\Libs\Psr\Log;
if(ABSPATH){
    interface LoggerInterface{
        public function emergency($message, array $context = array());
        public function alert($message, array $context = array());
        public function critical($message, array $context = array());
        public function error($message, array $context = array());
        public function warning($message, array $context = array());
        public function notice($message, array $context = array());
        public function info($message, array $context = array());
        public function debug($message, array $context = array());
        public function log($level, $message, array $context = array());
    }
}else die;