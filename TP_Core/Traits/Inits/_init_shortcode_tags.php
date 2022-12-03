<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 14:24
 */
namespace TP_Core\Traits\Inits;
if(ABSPATH){
    trait _init_shortcode_tags{
        protected $_tp_shortcode_tags =[];
    }
}else die;