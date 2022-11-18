<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _ms_methods_05{
        /**
         * @description Corrects From host on outgoing mail to match the site domain
         * @param $php_mailer
         */
        protected function _fix_php_mailer_msg_id( $php_mailer ):void{
            $php_mailer->Hostname = $this->_get_network()->domain;
        }//2372
        /**
         * @description Determines whether a user is marked as a spammer, based on user login.
         * @param null $user
         * @return bool
         */
        protected function _is_user_spammy( $user = null ):bool{
            if ( ! ( $user instanceof TP_User ) ) {
                if ( $user ) $user = $this->_get_user_by( 'login', $user );
                else $user = $this->_tp_get_current_user();
            }
            return $user && isset( $user->spam ) && 1 === $user->spam;
        }//2385
        /**
         * @description Updates this blog's 'public' setting in the global blogs table.
         * @param $value
         */
        protected function _update_blog_public($value ):void{
            $this->_update_blog_status( $this->_get_current_blog_id(), 'public', (int) $value );
        }//2407
        /**
         * @description Determines whether users can self-register, based on Network settings.
         * @return bool
         */
        protected function _users_can_register_signup_filter():bool{
            $registration = $this->_get_site_option( 'registration' );
            return ( 'all' === $registration || 'user' === $registration );
        }//2418
        /**
         * @description Ensures that the welcome message is not empty. Currently unused.
         * @param $text
         * @return mixed
         */
        protected function _welcome_user_msg_filter( $text ){
            if ( ! $text ) {
                $this->_remove_filter( 'site_option_welcome_user_email', 'welcome_user_msg_filter' );
                $text = $this->__("'Howdy USER_NAME, Your new account has been set up. 
                You can log in with the following information:Username: USER_NAME, Password: PASSWORD, LOGIN_LINK
                Thanks! --The Team @ SITE_NAME");
                //todo, add str_replace
                $this->_update_site_option( 'welcome_user_email', $text );
            }
            return $text;
        }//2431
        /**
         * @description Determines whether to force SSL on content.
         * @param string $force
         * @return bool|string
         */
        protected function _force_ssl_content( $force = '' ){
            static $forced_content = false;
            if ( ! $force ) {
                $old_forced     = $forced_content;
                $forced_content = $force;
                return $old_forced;
            }
            return $forced_content;
        }//2463
        /**
         * @description Formats a URL to use https.
         * @param $url
         * @return mixed
         */
        protected function _filter_SSL( $url ){
            if ( ! is_string( $url ) ) return $this->_get_bloginfo( 'url' ); // Return home blog URL with proper scheme.
            if ( $this->_force_ssl_content() && $this->_is_ssl() )  $url = $this->_set_url_scheme( $url, 'https' );
            return $url;
        }//2485
        /**
         * @description Schedules update of the network-wide counts for the current network.
         */
        protected function _tp_schedule_update_network_counts():void{
            if ( ! $this->_is_main_site() ) return;
            if ( ! $this->_tp_next_scheduled( 'update_network_counts' ) && ! $this->_tp_installing() )
                $this->_tp_schedule_event( time(), 'twice_daily', 'update_network_counts' );
        }//2502
        /**
         * @description Updates the network-wide counts for the current network.
         * @param null $network_id
         */
        protected function _tp_update_network_counts( $network_id = null ):void{
            $this->_tp_update_network_user_counts( $network_id );
            $this->_tp_update_network_site_counts( $network_id );
        }//2520
        /**
         * @description Updates the count of sites for the current network.
         * @param null $network_id
         */
        protected function _tp_maybe_update_network_site_counts( $network_id = null ):void{
            $is_small_network = ! $this->_tp_is_large_network( 'sites', $network_id );
            if ( ! $this->_apply_filters( 'enable_live_network_counts', $is_small_network, 'sites' ) )
                return;
            $this->_tp_update_network_site_counts( $network_id );
        }//2536
    }
}else die;