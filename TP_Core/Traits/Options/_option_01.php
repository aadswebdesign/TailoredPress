<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 18:34
 */
namespace TP_Core\Traits\Options;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _option_01 {
        use _init_db;
        protected function _get_option($option, $default = false){return '';}//77
        protected function _tp_protect_special_option( $option ){return '';}//241
        protected function _form_option( $option ) {return '';}//262
        protected function _tp_load_all_options( $force_cache = false ){return '';}//276
        protected function _tp_load_core_site_options( $network_id = null ){return '';}//331
        protected function _update_option( $option, $value, $autoload = 'yes'){return '';}//381
        protected function _add_option( $option, $value, $autoload = 'yes'){return '';}//567
        protected function _delete_option( $option ) {return '';}//696
        protected function _delete_transient( $transient ){return '';}//774
        protected function _get_transient( $transient ){return '';}//825
    }
}else die;