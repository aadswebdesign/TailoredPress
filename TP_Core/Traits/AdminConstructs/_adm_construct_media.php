<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-9-2022
 * Time: 10:48
 */
namespace TP_Core\Traits\AdminConstructs;
if(ABSPATH){
    trait _adm_construct_media{
        public $tp_redirect_tab;
        protected function _adm_construct_adm_media():void{
            $this->tp_redirect_tab;
        }
    }
}else{die;}