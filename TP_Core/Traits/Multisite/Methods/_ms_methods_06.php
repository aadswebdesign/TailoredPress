<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
if(ABSPATH){
    trait _ms_methods_06{
        /**
         * @description Updates the network-wide site count.
         * @param null $network_id
         */
        protected function _tp_update_network_site_counts( $network_id = null ):void{
            $network_id = (int) $network_id;
            if ( ! $network_id ) $network_id = $this->_get_current_network_id();
            $count = $this->_get_sites(
                ['network_id' => $network_id,'spam' => 0,'deleted' => 0,
                    'archived' => 0,'count' => true,'update_site_meta_cache' => false,]
            );
            $this->_update_network_option( $network_id, 'blog_count', $count );
        }//2586
        /**
         * @description Updates the network-wide user count.
         * @param null $network_id
         */
        protected function _tp_update_network_user_counts( $network_id = null ):void{
            $this->_get_user_count( $network_id );//todo
        }//2616
        /**
         * @description Returns the space used by the current site.
         * @return float|int
         */
        protected function _get_space_used(){
            $space_used = $this->_apply_filters( 'pre_get_space_used', false );
            if ( false === $space_used ) {
                $upload_dir = $this->_tp_upload_dir();
                $space_used = $this->_get_dir_size( $upload_dir['basedir'] ) / MB_IN_BYTES;
            }
            return $space_used;
        }//2630
        /**
         * @description Returns the upload quota for the current blog.
         * @return mixed
         */
        protected function _get_space_allowed(){
            $space_allowed = $this->_get_option( 'blog_upload_space' );
            if ( ! is_numeric( $space_allowed ) )
                $space_allowed = $this->_get_site_option( 'blog_upload_space' );
            if ( ! is_numeric( $space_allowed ) )
                $space_allowed = 100;
            return $this->_apply_filters( 'get_space_allowed', $space_allowed );
        }//2655
        /**
         * @description Determines if there is any upload space left in the current blog's quota.
         * @return int|mixed
         */
        protected function _get_upload_space_available(){
            $allowed = $this->_get_space_allowed();
            if ( $allowed < 0 ) $allowed = 0;
            $space_allowed = $allowed * MB_IN_BYTES;
            if ( $this->_get_site_option( 'upload_space_check_disabled' ) )
                return $space_allowed;
            $space_used = $this->_get_space_used() * MB_IN_BYTES;
            if ( ( $space_allowed - $space_used ) <= 0 ) return 0;
            return $space_allowed - $space_used;
        }//2683
        /**
         * @description Determines if there is any upload space left in the current blog's quota.
         * @return bool
         */
        protected function _is_upload_space_available():bool{
            if ( $this->_get_site_option( 'upload_space_check_disabled' ) )
                return true;
            return (bool) $this->_get_upload_space_available();
        }//2708
        /**
         * @description Filters the maximum upload file size allowed, in bytes.
         * @param $size
         * @return mixed
         */
        protected function _upload_size_limit_filter( $size ){
            $file_upload_max = KB_IN_BYTES * $this->_get_site_option( 'file_upload_max', 1500 );
            if ( $this->_get_site_option( 'upload_space_check_disabled' ) )
                return min( $size, $file_upload_max );
            return min( $size, $file_upload_max, $this->_get_upload_space_available() );
        }//2724
        /**
         * @description Determines whether or not we have a large network.
         * @param string $using
         * @param null $network_id
         * @return mixed
         */
        protected function _tp_is_large_network( $using = 'sites', $network_id = null ){
            $network_id = (int) $network_id;
            if ( ! $network_id ) $network_id = $this->_get_current_network_id();
            if ( 'users' === $using ) {
                $count = $this->_get_user_count( $network_id );
                return $this->_apply_filters( 'tp_is_large_network', $count > 10000, 'users', $count, $network_id );
            }
            $count = $this->_get_blog_count( $network_id );
            return $this->_apply_filters( 'tp_is_large_network', $count > 10000, 'sites', $count, $network_id );
        }//2746
        /**
         * @description Retrieves a list of reserved site on a sub-directory Multisite installation.
         * @return mixed
         */
        protected function _get_subdirectory_reserved_names(){
            $names = ['page','comments','blog','files','feed','tp-admin','tp-content','tp-includes','tp-json','embed',];
            return $this->_apply_filters( 'subdirectory_reserved_names', $names );
        }//2781
        /**
         * @description Sends a confirmation request email when a change of network admin email address is attempted.
         * @param $value
         */
        protected function _update_network_option_new_admin_email($value ):void{
            if ( $this->_get_site_option( 'admin_email' ) === $value || ! $this->_is_email( $value ) )
                return;
            $hash = md5( $value . time() . mt_rand() );
            $new_admin_email = ['hash' => $hash, 'new_email' => $value,];
            $this->_update_site_option( 'network_admin_hash', $new_admin_email );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale() );
            $email_text = $this->__("Howdy ###USER_NAME###,You recently requested to have the network admin email address on your network changed.
            If this is correct, please click on the following link to change it: ###ADMIN_URL### 
            You can safely ignore and delete this email if you do not want to take this action.
            This email has been sent to ###EMAIL###
            Regards, All at ###SITENAME### ###SITEURL###");
            $content = $this->_apply_filters( 'new_network_admin_email_content', $email_text, $new_admin_email );
            $current_user = $this->_tp_get_current_user();
            if($current_user){
                $content       = str_replace( '###USER_NAME###', $current_user->user_login, $content );
                $content      .= str_replace( '###ADMIN_URL###', $this->_esc_url( $this->_network_admin_url( 'settings.php?network_admin_hash=' . $hash ) ), $content );
                $content      .= str_replace( '###EMAIL###', $value, $content );
                $content      .= str_replace( '###SITENAME###', $this->_tp_special_chars_decode( $this->_get_site_option( 'site_name' ), ENT_QUOTES ), $content );
                $content      .= str_replace( '###SITEURL###', $this->_network_home_url(), $content );
            }
            $this->_tp_mail( $value,
                sprintf($this->__( '[%s] Network Admin Email Change Request' ),
                    $this->_tp_special_chars_decode( $this->_get_site_option( 'site_name' ), ENT_QUOTES )
                ),/* translators: Email change notification email subject. %s: Network title. */
                $content
            );
            if ( $switched_locale ) $this->_restore_previous_locale();
        }//2817
        /**
         * @description Sends an email to the old network admin email address when the network admin email address changes.
         * @param $new_email
         * @param $old_email
         * @param $network_id
         */
        protected function _tp_network_admin_email_change_notification( $new_email, $old_email, $network_id ):void{
            $send = true;
            if ( 'you@example.com' === $old_email ) $send = false;
            $send = $this->_apply_filters( 'send_network_admin_email_change_email', $send, $old_email, $new_email, $network_id );
            if ( ! $send ) return;
            $email_change_text = $this->__("Hi, This notice confirms that the network admin email address was changed on ###SITENAME###.
            The new network admin email address is ###NEW_EMAIL###.
            This email has been sent to ###OLD_EMAIL###
            Regards, All at ###SITENAME### ###SITEURL###");
            $email_change_email = array(
                'to'      => $old_email,
                /* translators: Network admin email change notification email subject. %s: Network title. */
                'subject' => $this->__( '[%s] Network Admin Email Changed' ),
                'message' => $email_change_text,
                'headers' => '',
            );
            // Get network name.
            $network_name = $this->_tp_special_chars_decode( $this->_get_site_option( 'site_name' ), ENT_QUOTES );
            $email_change_email = $this->_apply_filters( 'network_admin_email_change_email', $email_change_email, $old_email, $new_email, $network_id );
            $email_change_email['message'] = str_replace( '###OLD_EMAIL###', $old_email, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###NEW_EMAIL###', $new_email, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###SITENAME###', $network_name, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###SITEURL###', $this->_home_url(), $email_change_email['message'] );
            $this->_tp_mail(
                $email_change_email['to'],
                sprintf($email_change_email['subject'], $network_name),
                $email_change_email['message'],
                $email_change_email['headers']
            );
        }//2905
    }
}else die;