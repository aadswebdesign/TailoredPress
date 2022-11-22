<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 17:09
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
if(ABSPATH){
    class ClientException extends BadResponseException{}
}else{
    die;
}