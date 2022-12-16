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
    $test_php = 'http://SERVER-IP/phptest.php';
    if (!$test_php ){
        ?>
        <!DOCTYPE html><html><head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        </head><body class='tp-core-ui'>
            <p id='tp_logo'>placeholder tp logo.</p>
            <h1>Error: PHP is not running</h1>
            <p>TailoredPress requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
        </body></html>
        <?php
    }else{
        echo new \TP_Admin\Libs\AdmPanels\Adm_Install_Panel();
    }
}else{die;}