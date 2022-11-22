<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:23
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
use TP_Core\Libs\PHP\Libs\HTTP_Client\ClientExceptionInterface;
if(ABSPATH){
    interface GuzzleException extends ClientExceptionInterface{}
}else{die;}