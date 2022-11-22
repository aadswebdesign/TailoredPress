<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 21:55
 */
namespace TP_Core\Libs\PHP\Mailer;
if(ABSPATH){
    interface OAuthTokenProvider{
        public function getOauth64();
    }
}else die;