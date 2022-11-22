<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 16:34
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Exception;
if(ABSPATH){
    class MalformedUriException extends \InvalidArgumentException{}
}else{die;}