<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 03:10
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
if(ABSPATH){
    class RefreshToken extends AbstractGrant{
        protected function _getName(){
            return 'refresh_token';
        }
        protected function _getRequiredRequestParameters(){
            return ['refresh_token',];
        }
    }
}else die;