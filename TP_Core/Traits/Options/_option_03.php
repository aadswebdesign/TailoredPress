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
    trait _option_03 {
        use _init_db;
        protected function _add_site_option( $option, $value ) {return '';}//1321
        protected function _delete_site_option( $option ){return '';}//1336
        protected function _update_site_option( $option, $value ) {return '';}
        protected function _get_network_option( $network_id, $option, $default = false ){return '';}//1370
        protected function _add_network_option( $network_id, $option, $value ) {return '';}//1504
        protected function _delete_network_option( $network_id, $option ){return '';}//1628
        protected function _update_network_option( $network_id, $option, $value ){return '';}//1722
        protected function _delete_site_transient( $transient ){return '';}//1848
        protected function _get_site_transient( $transient ){return '';}//1901
        protected function _set_site_transient( $transient, $value, $expiration = 0 ){return '';}//1976
    }
}else die;