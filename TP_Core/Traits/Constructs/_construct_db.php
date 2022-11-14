<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_db{
        public $tp_charset_collate;
        public $tp_order;
        public $tp_orderby;
        public $tp_table_prefix;
        public $tpdb;
        protected function _construct_db():void{
            $this->tp_charset_collate;
            $this->tp_order;
            $this->tp_orderby;
            $this->tp_table_prefix = 'tp_';
            $this->tpdb;
        }
    }
}else die;