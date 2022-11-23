<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 14:45
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    interface Requests_Proxy{
        public function register(Requests_Hooks $hooks);
    }
}else die;