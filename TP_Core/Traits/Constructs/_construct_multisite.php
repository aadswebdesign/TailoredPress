<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-5-2022
 * Time: 20:51
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_multisite{
        public $tp_ms_blog = [];
        public $tp_domain;
        public $tp_current_site, $tp_current_blog;
        protected function _construct_multisite():void{
            $this->tp_ms_blog;
            $this->tp_domain;
            $this->tp_current_site;
            $this->tp_current_blog;

        }


    }
}else die;