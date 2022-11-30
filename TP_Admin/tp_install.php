<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 01:24
 */
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/../' );//let see
/** @noinspection PhpIncludeInspection */
require_once(ABSPATH.'tp_autoload.php');
if(ABSPATH){
    new tp_autoload();
    //echo new \TP_Admin\Libs\; //todo
}else{die;}


