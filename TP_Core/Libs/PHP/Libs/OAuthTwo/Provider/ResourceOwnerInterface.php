<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 18:36
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Provider;
if(ABSPATH){
    interface ResourceOwnerInterface{
        public function getId();
        public function toArray();
    }
}else die;