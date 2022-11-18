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
    trait _option_04 {
        use _init_db;
        protected function _register_initial_settings(){return '';}//2064
        protected function _register_setting( $option_group, $option_name,array ...$args) {return '';}//2279
        protected function _unregister_setting( $option_group, $option_name){return '';}//2390
        protected function _get_registered_settings(){return '';}//2479
        protected function _filter_default_option( $default, $option, $passed_default ){return '';}//2502
        /**
         * @description Refreshes the value of the allowed options list available via the 'allowed_options' hook.
         * @param $options
         * @return string
         */
        protected function _option_update_filter( $options ):string{
            if ( is_array( $this->tp_new_allowed_options ) ) {
                $options = $this->_add_allowed_options( $this->tp_new_allowed_options, $options );
            }
            return $options;
        }//2169 from admin/plugins.php
        /**
         * @description Adds an array of options to the list of allowed options.
         * @param $new_options
         * @param string $options
         * @return string
         */
        protected function _add_allowed_options( $new_options, $options = '' ):string{
            if ( '' === $options ) { $this->tp_allowed_options;}
            else { $this->tp_allowed_options = $options;}
            foreach ( $new_options as $page => $keys ) {
                foreach ((array) $keys as $key ) {
                    if ( ! isset( $this->tp_allowed_options[ $page ] ) || ! is_array( $this->tp_allowed_options[ $page ] ) ) {
                        $this->tp_allowed_options[ $page ]   = [];
                        $this->tp_allowed_options[ $page ][$key];
                    } else {
                        $pos = array_search( $key, $this->tp_allowed_options[ $page ], true );
                        if ( false === $pos ) {
                            $this->tp_allowed_options[ $page ][$key];
                        }
                    }
                }
            }
            return $this->tp_allowed_options;
        }//2190 from admin/plugins.php
        /**
         * @description Removes a list of options from the allowed options list.
         * @param $del_options
         * @param string $options
         * @return string
         */
        protected function _remove_allowed_options( $del_options, $options = '' ):string{
            if ( '' === $options ) { $this->tp_allowed_options;}
            else { $this->tp_allowed_options = $options;}
            foreach ( $del_options as $page => $keys ) {
                foreach ( $keys as $key ) {
                    if ( isset(  $this->tp_allowed_options[ $page ] ) && is_array(  $this->tp_allowed_options[ $page ] ) ) {
                        $pos = array_search( $key,  $this->tp_allowed_options[ $page ], true );
                        if ( false !== $pos ) {
                            unset(  $this->tp_allowed_options[ $page ][ $pos ] );
                        }
                    }
                }
            }
            return  $this->tp_allowed_options;
        }//2225 from admin/plugins.php
    }
}else die;