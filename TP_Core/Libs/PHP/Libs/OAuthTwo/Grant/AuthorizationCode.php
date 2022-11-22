<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 02:51
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
if(ABSPATH){
    class AuthorizationCode extends AbstractGrant{
        protected function _getName(){
            return 'authorization_code';
        }
        protected function _getRequiredRequestParameters(){
            return ['code',];
        }
    }
}else die;