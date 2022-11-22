<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:16
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
//use TP_Managers\PHP_Manager\Libs\OAuthTwo\Token\AccessTokenInterface;
if(ABSPATH){
    trait BearerAuthorizationTrait{
        protected function getAuthorizationHeaders($token = null): array
        {
            return ['Authorization' => 'Bearer ' . $token];
        }
    }
}else die;