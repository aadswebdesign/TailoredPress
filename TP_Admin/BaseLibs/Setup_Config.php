<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-10-2022
 * Time: 21:43
 */
namespace TP_Admin\BaseLibs;
if(ABSPATH){
    class Setup_Config{
        public static function setup(){
            define( 'TP_INSTALLING', true );
            define( 'TP_SETUP_CONFIG', true );
            error_reporting( 0 );


        }

    }
}else{die;}
