<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-10-2022
 * Time: 15:57
 */
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/../' );//let see
if( ! defined('TP_MULTI_SITE')) define('TP_MULTI_SITE', false);//todo set false when the time is right
/** @noinspection PhpIncludeInspection */
require_once(ABSPATH.'tp_autoload.php');
if(ABSPATH){
    new tp_autoload();
    TP_Admin\Libs\AdmComponents\Adm_Header::set_header();//todo testing
    echo new \TP_Admin\Libs\AdmPanels\Adm_Options_General_Panel();
}else{die;}