<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-8-2022
 * Time: 11:22
 */
namespace TP_Core\Libs\Recovery;
use TP_Core\Libs\TP_Core;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    final class TP_Recovery_Mode_Email_Service extends Recovery_Base{
        public const RATE_LIMIT_OPTION = 'recovery_mode_email_last_sent';
        public function __construct( TP_Recovery_Mode_Link_Service $link_service ) {
            $this->_link_service = $link_service;
        }
        public function maybe_send_recovery_mode_email( $rate_limit, $error, $extension ) {
            $last_sent = $this->_get_option( self::RATE_LIMIT_OPTION );
            if ( ! $last_sent || time() > $last_sent + $rate_limit ) {
                if ( ! $this->_update_option( self::RATE_LIMIT_OPTION, time() ) )
                    return new TP_Error( 'storage_error', $this->__( 'Could not update the email last sent time.' ) );
                $sent = $this->__send_recovery_mode_email( $rate_limit, $error, $extension );
                if ( $sent ) return true;
                return new TP_Error(
                    'email_failed',
                    sprintf($this->__( 'The email could not be sent. Possible reason: your host may have disabled the %s function.' ),'mail()'));/* translators: %s: mail() */
            }
            $err_message = sprintf(
            /* translators: 1: Last sent as a human time diff, 2: Wait time as a human time diff. */
                $this->__( 'A recovery link was already sent %1$s ago. Please wait another %2$s before requesting a new email.' ),
                $this->_human_time_diff( $last_sent ),
                $this->_human_time_diff( $last_sent + $rate_limit )
            );
            return new TP_Error( 'email_sent_already', $err_message );
        }
        public function clear_rate_limit() {
            return $this->_delete_option( self::RATE_LIMIT_OPTION );
        }
        private function __send_recovery_mode_email( $rate_limit, $error, $extension ): bool{
            $url      = $this->_link_service->generate_url();
            $blogname = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
            if ( $extension ) {
                $cause   = $this->__get_cause( $extension );
                $details = $this->_tp_strip_all_tags( $this->_tp_get_extension_error_description( $error ) );
                if ( $details ) {
                    $header  = $this->__( 'Error Details' );
                    $details = "\n\n" . $header . "\n" . str_pad( '', strlen( $header ), '=' ) . "\n" . $details;
                }
            }else{
                $cause   = '';
                $details = '';
            }
            $support = $this->_apply_filters( 'recovery_email_support_info', $this->__( 'Please contact your host for assistance with investigating this issue further.' ) );
            $debug = $this->_apply_filters( 'recovery_email_debug_info', $this->__get_debug());
            $message = $this->__('todo
###LINK###
###EXPIRES###
###CAUSE###
###DETAILS###
###SITEURL###
###PAGEURL###
###SUPPORT###
###DEBUG###
            ');
            $message = str_replace(
                ['###LINK###','###EXPIRES###','###CAUSE###','###DETAILS###','###SITEURL###','###PAGEURL###','###SUPPORT###','###DEBUG###',],
                [$url,$this->_human_time_diff( time() + $rate_limit ), $cause ? "\n{$cause}\n" : "\n", $details,
                    $this->_home_url( '/' ),$this->_home_url( $_SERVER['REQUEST_URI'] ),$support,implode( "\r\n", $debug ),],
                $message
            );
            $email = ['to' => $this->__get_recovery_mode_email_address(),
                'subject' => $this->__( '[%s] Your Site is Experiencing a Technical Issue' ),
                'message' => $message,'headers' => '','attachments' => '',];/* translators: %s: Site title. */
            $email = $this->_apply_filters( 'recovery_mode_email', $email, $url );
            $sent = $this->_tp_mail(
                $email['to'],
                $this->_tp_special_chars_decode( sprintf( $email['subject'], $blogname ) ),
                $email['message'],
                $email['headers'],
                $email['attachments']
            );
            if ( $switched_locale ) $this->_restore_previous_locale();
            return $sent;
        }
        private function __get_recovery_mode_email_address(){
            if ( defined( 'RECOVERY_MODE_EMAIL' ) && $this->_is_email( RECOVERY_MODE_EMAIL ) )
                return RECOVERY_MODE_EMAIL;
            return $this->_get_option( 'admin_email' );
        }
        private function __get_cause( $extension ){
            $theme = $this->_tp_get_theme( $extension['slug'] );
            $name  = $theme->exists() ? $theme->display( 'Name' ) : $extension['slug'];
            /* translators: %s: Theme name. */
            $cause = sprintf( $this->__( 'In this case, TailoredPress caught an error with your theme, %s.' ), $name );
            return $cause;
        }
        private function __get_debug(): array{
            $theme      = $this->_tp_get_theme();
            ///** @noinspection PhpUndefinedClassInspection */
            if( $theme instanceof TP_Core ){}
            $this->_tp_version = $this->_get_bloginfo( 'version' );
            $debug = [
                'tp' => sprintf( $this->__( 'TailoredPress version %s' ), $this->_tp_version),/* translators: %s: Current WordPress version number. */
                'theme' => sprintf($this->__( 'Current theme: %1$s (version %2$s)' ),$theme->$this->_get_header( 'Name' ),$theme->$this->_get_header( 'Version' )),];/* translators: 1: Current active theme name. 2: Current active theme version. */
            $debug['php'] = sprintf($this->__( 'PHP version %s' ), PHP_VERSION);/* translators: %s: The currently used PHP version. */
            return $debug;
        }
    }
}else die;