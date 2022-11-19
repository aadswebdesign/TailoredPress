<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 19:18
 */
namespace TP_Core\Libs\Atom;
if(ABSPATH){
    class Entry{
        public $links = [];
        public $categories = [];
    }
}else die;