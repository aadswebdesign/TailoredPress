<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-10-2022
 * Time: 22:37
 */
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/../' );//let see
if( ! defined('TP_MULTI_SITE')) define('TP_MULTI_SITE', false);//todo set false when the time is right
/** @noinspection PhpIncludeInspection */
require_once(ABSPATH.'tp_autoload.php');
if(ABSPATH){
    new tp_autoload();
    echo new \TP_Admin\Libs\AdmPanels\Adm_Upgrade_Panel();
}else{die;}