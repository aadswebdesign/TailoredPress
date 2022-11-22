<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 17:55
 */

namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Exception;
if(ABSPATH){
    final class InvalidArgumentException extends \InvalidArgumentException implements GuzzleException{}
}else{die;}