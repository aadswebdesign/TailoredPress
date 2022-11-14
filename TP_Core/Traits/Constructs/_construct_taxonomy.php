<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_taxonomy{
        public $tp_tax;
        public $tp_taxonomies;
        public $tp_taxonomy;
        public $tp_tax_query_obj;
        protected function _construct_taxonomy():void{
            $this->tp_taxonomies = [];
            $this->tp_tax_query_obj;
        }
    }
}else die;