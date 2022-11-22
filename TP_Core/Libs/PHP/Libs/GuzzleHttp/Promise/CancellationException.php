<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:08
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class CancellationException extends RejectionException{}
}else{die;}