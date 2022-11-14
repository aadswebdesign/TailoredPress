<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_methods{
        public $tp_base_paths;
        public $tp_mime;
        public $tp_header_to_desc;
        public $tp_filename;
        public $tp_filetype;
        public $tp_msg;
        public $tp_view_args;
        public $tp_util;
        public $tp_evanescent_hare;
        //to be moved to ?
        public $tp_login_grace_period;
        public $tp_hasher;
        public $tp_rand_value;
        public $tp_shortcode_tags;
        protected function _construct_methods():void{
            $this->tp_base_paths = [];//plugins.php 711
            $this->tp_mime =[];
            $this->tp_header_to_desc;
            $this->tp_filename;
            $this->tp_filetype;
            $this->tp_msg;
            $this->tp_view_args = [];
            $this->tp_util;
            $this->tp_evanescent_hare;
            //to be moved to ?
            $this->tp_login_grace_period;
            $this->tp_hasher;
            $this->tp_rand_value;
            $this->tp_shortcode_tags;
        }
    }
}else die;