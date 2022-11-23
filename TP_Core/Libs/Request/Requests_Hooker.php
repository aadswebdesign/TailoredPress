<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 16:13
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    interface Requests_Hooker{
        public function register($hook, $callback, $priority = 0);
        public function dispatch($hook, $parameters);
    }
}else die;