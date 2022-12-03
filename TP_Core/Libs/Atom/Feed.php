<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 19:16
 */
namespace  TP_Core\Libs\Atom;
if(ABSPATH){
    class Feed{
        public $links = [];
        public $categories = [];
        public  $entries = [];
    }
}else die;