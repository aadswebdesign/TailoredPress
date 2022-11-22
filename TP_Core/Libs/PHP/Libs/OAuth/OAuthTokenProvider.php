<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 03:57
 */

namespace TP_Core\Libs\PHP\Libs\OAuth;
if(ABSPATH){
    interface OAuthTokenProvider{
        public function getOauth64();
    }
}else die;