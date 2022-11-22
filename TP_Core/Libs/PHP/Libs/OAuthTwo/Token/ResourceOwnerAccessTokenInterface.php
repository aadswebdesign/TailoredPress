<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:26
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Token;
if(ABSPATH){
    interface ResourceOwnerAccessTokenInterface extends AccessTokenInterface{
        public function getResourceOwnerId();
    }
}else die;