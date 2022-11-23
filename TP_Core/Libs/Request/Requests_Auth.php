<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 18:20
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    interface Requests_Auth{
        public function register(Requests_Hooks $hooks);
    }
}else die;