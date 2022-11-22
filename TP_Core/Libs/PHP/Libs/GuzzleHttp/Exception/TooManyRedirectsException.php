<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 17:58
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
if(ABSPATH){
    class TooManyRedirectsException extends RequestException{}
}else{die;}