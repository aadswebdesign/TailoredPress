<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-8-2022
 * Time: 13:25
 */
namespace TP_Core\BaseLibs;
use TP_Core\Libs\TP_Core;

if(ABSPATH){
    class TP_BlogHeader{
        public function __construct(){
            new TP_Load();
            new TP_Core();




        }
    }
}else{die;}