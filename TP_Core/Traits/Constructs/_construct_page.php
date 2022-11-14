<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_page{
        public $tp_library_page;
        public $tp_multi_page;
        public $tp_num_pages;
        public $tp_page;
        public $tp_paged;
        public $tp_pages;
        public $tp_pagenow;
        public $tp_parent_file;
        public $tp_parent_pages;
        public $tp_real_parent_file;
        public $tp_registered_pages;
        public $tp_title;
        public $tp_typenow;
        public $tp_update_title;
        protected function _construct_page():void{
            $this->tp_library_page;
            $this->tp_multi_page;
            $this->tp_num_pages;
            $this->tp_page;
            $this->tp_paged;
            $this->tp_pages;
            $this->tp_pagenow;
            $this->tp_parent_file;
            $this->tp_parent_pages;
            $this->tp_real_parent_file;
            $this->tp_registered_pages;
            $this->tp_typenow;
            $this->tp_title;
            $this->tp_update_title;
        }
    }
}else die;