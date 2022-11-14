<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-10-2022
 * Time: 16:18
 */
namespace TP_Core\Traits\Constructs;
use TP_Core\Libs\TP_Hook;
if(ABSPATH){
    trait _construct_filter {
        public $tp_filter;
        public $tp_action;
        public $tp_actions;
        public $tp_current_filter;
        public $tp_hook_suffix;
        protected function _construct_filters(): void{
            global $tp_filter, $tp_actions, $tp_current_filter;
            if ( $tp_filter ) $tp_filter = TP_Hook::build_pre_initialized_hooks( $tp_filter );
            else $tp_filter = [];
            if ( ! isset( $tp_actions ) )$tp_actions = [];
            if ( ! isset( $tp_current_filter ) )$tp_current_filter = [];
        }
    }
}else{die;}