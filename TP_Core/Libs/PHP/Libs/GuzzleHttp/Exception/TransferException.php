<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 16:51
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
if(ABSPATH){
    class TransferException extends \RuntimeException implements GuzzleException{}
}else{die;}