<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_18{
        /**
         * @description  Gets last changed date for the specified cache group.
         * @param $group
         * @return mixed
         */
        protected function _tp_cache_get_last_changed( $group ){
            $last_changed = $this->_tp_cache_get( 'last_changed', $group );
            if ( ! $last_changed ) {
                $last_changed = microtime();
                $this->_tp_cache_set( 'last_changed', $last_changed, $group );
            }
            return $last_changed;
        }//7563
        /**
         * @description Send an email to the old site admin email address when the site admin email address changes.
         * @param $old_email
         * @param $new_email
         * @param $option_name
         */
        protected function _tp_site_admin_email_change_notification( $old_email, $new_email, $option_name = null ):void{
            $send = true;
            // Don't send the notification to the default 'admin_email' value.
            if ( 'you@example.com' === $old_email ) $send = false;
            $send = $this->_apply_filters( 'send_site_admin_email_change_email', $send, $old_email, $new_email, $option_name );//todo will see
            if ( ! $send ) return;
            $email_change_text = $this->__(
                'Hi,
This notice confirms that the admin email address was changed on ###SITENAME###.
The new admin email address is ###NEW_EMAIL###.
This email has been sent to ###OLD_EMAIL###
Regards,
All at ###SITENAME###
###SITEURL###'
            );
            $email_change_email = array(
                'to'      => $old_email,
                'subject' => $this->__( '[%s] Admin Email Changed' ),'message' => $email_change_text,'headers' => '', );$site_name = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $email_change_email = $this->_apply_filters( 'site_admin_email_change_email', $email_change_email, $old_email, $new_email );
            $email_change_email['message'] = str_replace( '###OLD_EMAIL###', $old_email, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###NEW_EMAIL###', $new_email, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###SITENAME###', $site_name, $email_change_email['message'] );
            $email_change_email['message'] = str_replace( '###SITEURL###', $this->_home_url(), $email_change_email['message'] );
            $this->_tp_mail(
                $email_change_email['to'],
                sprintf($email_change_email['subject'], $site_name), $email_change_email['message'], $email_change_email['headers']);
        }//7583
        //@description
        //protected function _tp_unique_id( $prefix = '' ){
            //return '';
        //}//7600?


        /**
         * @description Return an anonymized IPv4 or IPv6 address.
         * @param $ip_addr
         * @param bool $ipv6_fallback
         * @return string
         */
        protected function _tp_privacy_anonymize_ip( $ip_addr, $ipv6_fallback = false ):string{
            if ( empty( $ip_addr ) ) return '0.0.0.0';
            // Detect what kind of IP address this is.
            $ip_prefix = '';
            $is_ipv6   = substr_count( $ip_addr, ':' ) > 1;
            $is_ipv4   = ( 3 === substr_count( $ip_addr, '.' ) );
            if ( $is_ipv6 && $is_ipv4 ) {
                // IPv6 compatibility mode, temporarily strip the IPv6 part, and treat it like IPv4.
                $ip_prefix = '::ffff:';
                $ip_addr   = preg_replace( '/^\[?[0-9a-f:]*:/i', '', $ip_addr );
                $ip_addr   = str_replace( ']', '', $ip_addr );
                $is_ipv6   = false;
            }
            if ( $is_ipv6 ) {
                // IPv6 addresses will always be enclosed in [] if there's a port.
                $left_bracket  = strpos( $ip_addr, '[' );
                $right_bracket = strpos( $ip_addr, ']' );
                $percent       = strpos( $ip_addr, '%' );
                $netmask       = 'ffff:ffff:ffff:ffff:0000:0000:0000:0000';
                // Strip the port (and [] from IPv6 addresses), if they exist.
                if ( false !== $left_bracket && false !== $right_bracket )
                    $ip_addr = substr( $ip_addr, $left_bracket + 1, $right_bracket - $left_bracket - 1 );
                elseif ( false !== $left_bracket || false !== $right_bracket ) return '::';
                    // The IP has one bracket, but not both, so it's malformed.
                // Strip the reachability scope.
                if ( false !== $percent )  $ip_addr = substr( $ip_addr, 0, $percent );
                if ( preg_match( '/[^0-9a-f:]/i', $ip_addr ) ) return '::';
                if ( function_exists( 'inet_pton' ) && function_exists( 'inet_ntop' ) ) {
                    $ip_addr = inet_ntop( inet_pton( $ip_addr ) & inet_pton( $netmask ) );
                    if ( false === $ip_addr ) return '::';
                } elseif ( ! $ipv6_fallback ) return '::';
            } elseif ( $is_ipv4 ) {
                $last_octet_position = strrpos( $ip_addr, '.' );
                $ip_addr             = substr( $ip_addr, 0, $last_octet_position ) . '.0';
            } else return '0.0.0.0';
            // Restore the IPv6 prefix to compatibility mode addresses.
            return $ip_prefix . $ip_addr;
        }//7681
        /**
         * @description Return uniform "anonymous" data by type.
         * @param $type
         * @param string $data
         * @return mixed
         */
        protected function _tp_privacy_anonymize_data( $type, $data = '' ){
            switch ( $type ) {
                case 'email':
                    $anonymous = 'deleted@site.invalid';
                    break;
                case 'url':
                    $anonymous = 'https://site.invalid';
                    break;
                case 'ip':
                    $anonymous = $this->_tp_privacy_anonymize_ip( $data );
                    break;
                case 'date':
                    $anonymous = '0000-00-00 00:00:00';
                    break;
                case 'text':
                    /* translators: Deleted text. */
                    $anonymous = $this->__( '[deleted]' );
                    break;
                case 'longtext':
                    /* translators: Deleted long text. */
                    $anonymous = $this->__( 'This content was deleted by the author.' );
                    break;
                default:
                    $anonymous = '';
                    break;
            }
            return $this->_apply_filters( 'wp_privacy_anonymize_data', $anonymous, $type, $data );
        }//7754
        /**
         * @description Returns the directory used to store personal data export files.
         * @return mixed
         */
        protected function _tp_privacy_exports_dir(){
            $upload_dir  = $this->_tp_upload_dir();
            $exports_dir = $this->_trailingslashit( $upload_dir['basedir'] ) . 'tp_personal_data_exports/';
            return $this->_apply_filters( 'tp_privacy_exports_dir', $exports_dir );
        }//7803
        /**
         * @description Returns the URL of the directory used to store personal data export files.
         * @return mixed
         */
        protected function _tp_privacy_exports_url(){
            $upload_dir  = $this->_tp_upload_dir();
            $exports_url = $this->_trailingslashit( $upload_dir['baseurl'] ) . 'tp_personal_data_exports/';
            return $this->_apply_filters( 'tp_privacy_exports_url', $exports_url );
        }//7828
        /**
         * @description Schedule a `TP_Cron` job to delete expired export files.
         */
        protected function _tp_schedule_delete_old_privacy_export_files():void{
            if ( $this->_tp_installing() ) return;
            if ( ! $this->_tp_next_scheduled( 'wp_privacy_delete_old_export_files' ) )
                $this->_tp_schedule_event( time(), 'hourly', 'wp_privacy_delete_old_export_files' );
        }//7849
        //todo @description Cleans up export files older than three days old.
        //protected function _tp_privacy_delete_old_export_files(){}//7870
        /**
         * @description Gets the URL to learn more about updating the PHP version the site is running on.
         * @return string|null
         */
        protected function _tp_get_update_php_url(){
            $default_url = $this->_tp_get_default_update_php_url();
            $update_url = $default_url;
            if ( false !== getenv( 'TP_UPDATE_PHP_URL' ) ) $update_url = getenv( 'TP_UPDATE_PHP_URL' );
            $update_url = $this->_apply_filters( 'tp_update_php_url', $update_url );
            if ( empty( $update_url ) ) $update_url = $default_url;
            return $update_url;
        }//7912
        /**
         * todo might not be needed
         * @description Gets the default URL to learn more about updating the PHP version the site is running on.
         * @return mixed
         */
        protected function _tp_get_default_update_php_url(){
            return $this->_x( 'https://wordpress.org/support/update-php/', 'localized PHP upgrade information page' );
        }//7951
    }
}else die;