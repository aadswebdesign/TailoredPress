<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminConstructs;
if(ABSPATH){
    trait _adm_construct_screen{
        public $tp_current_screen;
        public $tp_screen;
        public $tp_screen_id;
        public $tp_screen_layout_columns;
        public $tp_tax_now;
        protected function _adm_construct_screen():void{
            $this->tp_current_screen;
            $this->tp_screen_id;
            $this->tp_screen_layout_columns;
            $this->tp_tax_now;
        }
    }
}else{die;}

