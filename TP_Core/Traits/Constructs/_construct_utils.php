<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 14:22
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_utils{
        public $tp_action;
        public $tp_actions;
        public $tp_current_filter;
        public $tp_customize;
        public $tp_filter;
        public $tp_hook_suffix;
        public $tp_link;
        public $tp_mode;
        public $tp_more;
        public $tp_number;
        public $tp_preview;
        /* @deprecated */
        public $tp_s;
        public $tp_self;
        public $tp_search;
        public $tp_status;
        public $tp_total_update_count;
        public $tp_totals;
        public $tp_zip;
        protected function _construct_utils():void{
            $this->tp_action;
            $this->tp_actions;
            $this->tp_current_filter;
            $this->tp_customize;
            $this->tp_filter;
            $this->tp_hook_suffix;
            $this->tp_link;
            $this->tp_mode;
            $this->tp_more;
            $this->tp_number;
            $this->tp_preview;
            $this->tp_self;
            $this->tp_status;
            $this->tp_totals;
            $this->tp_total_update_count;
            $this->tp_zip;
        }
    }
}else{die;}