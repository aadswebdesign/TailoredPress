<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:13
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class AggregateException extends RejectionException{
        public function __construct($msg, array $reasons){
            parent::__construct(
                $reasons,
                sprintf('%s; %d rejected promises', $msg, count($reasons))
            );
        }
    }
}else{die;}