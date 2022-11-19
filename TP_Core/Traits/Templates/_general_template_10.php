<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _general_template_10 {
        /**
         * @description Default settings for heartbeat
         * @param $settings
         * @return mixed
         */
        protected function _tp_heartbeat_settings( $settings ){//todo micht be double
            if ( ! $this->_is_admin() ) $settings['ajaxurl'] = $this->_admin_url( 'admin-ajax.php', 'relative' );//todo
            if ( $this->_is_user_logged_in() ) $settings['nonce'] = $this->_tp_create_nonce( 'heartbeat-nonce' );
            return $settings;
        }//4910 from general-template
    }
}else die;