<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 03:07
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
if(ABSPATH){
    class Password extends AbstractGrant{
        protected function _getName(){
            return 'password';
        }
        protected function _getRequiredRequestParameters(){
            return ['username','password',];
        }
    }
}else die;