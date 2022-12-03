<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
if(ABSPATH){
    trait _init_compat{
        protected $_utf8_pcre;



    }
}else{die;}