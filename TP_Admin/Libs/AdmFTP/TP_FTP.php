<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-10-2022
 * Time: 05:18
 */
namespace TP_Admin\Libs\AdmFTP;
if(ABSPATH){
    $mod_sockets = extension_loaded( 'sockets' );
    if ( ! $mod_sockets && function_exists( 'dl' ) && is_callable( 'dl' ) ) {
        $prefix = ( PHP_SHLIB_SUFFIX === 'dll' ) ? 'php_' : '';
        /** @noinspection PhpDeprecationInspection */
        @dl( $prefix . 'sockets.' . PHP_SHLIB_SUFFIX );
        $mod_sockets = extension_loaded( 'sockets' );
    }
    if ( $mod_sockets ) {
        class TP_FTP extends FTP_Sockets{}
    }else{
        class TP_FTP extends FTP_Pure{}
    }
}else{die;}