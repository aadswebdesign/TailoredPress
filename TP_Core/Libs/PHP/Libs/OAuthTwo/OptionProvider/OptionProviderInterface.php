<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:17
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\OptionProvider;
if(ABSPATH){
    interface OptionProviderInterface{
        public function getAccessTokenOptions($method, array $params);
    }
}else die;