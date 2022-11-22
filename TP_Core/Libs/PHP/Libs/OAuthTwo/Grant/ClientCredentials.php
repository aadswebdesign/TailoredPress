<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 02:54
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Grant;
if(ABSPATH){
    class ClientCredentials extends AbstractGrant{
        protected function _getName(){
            return 'client_credentials';
        }
        protected function _getRequiredRequestParameters(){
            return [];
        }
    }
}else die;