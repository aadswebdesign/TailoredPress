<?php 
/**
 * @file: index.php
 * @author: Aad Pouw 
 * @description: 
 * @note: 
 */
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' );
if( ! defined('TP_MULTI_SITE')) define('TP_MULTI_SITE', false);//todo set false when the time is right
/** @noinspection PhpIncludeInspection */
require_once(ABSPATH.'tp_autoload.php');
if(ABSPATH){
	new tp_autoload();
    echo new \TP_Content\Themes\DefaultTheme\Theme_Index();
}else die;