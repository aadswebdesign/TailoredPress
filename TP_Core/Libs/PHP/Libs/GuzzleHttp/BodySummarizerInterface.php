<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 16:22
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\HTTP_Message\MessageInterface;
if(ABSPATH){
    interface BodySummarizerInterface{
        public function summarize(MessageInterface $message): ?string;
    }
}else die;