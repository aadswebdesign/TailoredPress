<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 08:45
 */
 if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/../' );//let see
/** @noinspection PhpIncludeInspection */
require_once(ABSPATH.'tp_autoload.php');
if(ABSPATH){
    new tp_autoload();
    //echo new \TP_Admin\Libs\; //todo
}else{die;}