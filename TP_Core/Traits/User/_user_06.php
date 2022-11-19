<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _user_06 {
        use _init_error,_init_queries;
        /**
         * @description Send a confirmation request email when a change of user email address is attempted.
         * @return bool
         */
        protected function _send_confirmation_on_profile_email():bool{
            $_current_user = $this->_tp_get_current_user();
            $current_user = null;
            if( $_current_user instanceof TP_User ){
                $current_user = $_current_user;
            }
            if ( ! is_object( $this->_tp_errors ) ) $this->_tp_errors = new TP_Error();
            if ( $current_user->ID !== $_POST['user_id'] ) return false;
            if ( $current_user->user_email !== $_POST['email'] ) {
                if ( ! $this->_is_email( $_POST['email'] ) ) {
                    $this->_tp_errors->add('user_email',
                        $this->__( '<strong>Error</strong>: The email address isn&#8217;t correct.' ),
                        ['form-field' => 'email',]);
                    return false;
                }
                if ( $this->_email_exists( $_POST['email'] ) ) {
                    $this->_tp_errors->add('user_email',
                        $this->__( '<strong>Error</strong>: The email address is already used.' ),
                        ['form-field' => 'email',]);
                    $this->_delete_user_meta( $current_user->ID, '_new_email' );
                    return false;
                }
                $hash           = md5( $_POST['email'] . time() . $this->_tp_rand() );
                $new_user_email = ['hash' => $hash,'new_email' => $_POST['email'],];
                $this->_update_user_meta( $current_user->ID, '_new_email', $new_user_email );
                $sitename = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
                $email_text = $this->__(
                    'Howdy ###USERNAME###,
You recently requested to have the email address on your account changed.
If this is correct, please click on the following link to change it:
###ADMIN_URL###
You can safely ignore and delete this email if you do not want to
take this action.
This email has been sent to ###EMAIL###
Regards,
All at ###SITENAME###
###SITEURL###'
                );
                $content = $this->_apply_filters( 'new_user_email_content', $email_text, $new_user_email );
                $content = str_replace( '###USERNAME###', $current_user->user_login, $content );
                $content .= str_replace( '###ADMIN_URL###', $this->_esc_url( $this->_admin_url( 'profile.php?newuseremail=' . $hash ) ), $content );
                $content .= str_replace( '###EMAIL###', $_POST['email'], $content );
                $content .= str_replace( '###SITENAME###', $sitename, $content );
                $content .= str_replace( '###SITEURL###', $this->_home_url(), $content );
                $this->_tp_mail( $_POST['email'], sprintf( $this->__( '[%s] Email Change Request' ), $sitename ), $content );
                $_POST['email'] = $current_user->user_email;
            }
            return true;
        }//3392
        /**
         * @description Adds an admin notice alerting the user to check for confirmation request email
         * @return string
         */
        protected function _get_new_user_email_admin_notice():string{
            $html = '';
            if ( 'profile.php' === $this->tp_pagenow && isset( $_GET['updated'] ) ) {
                $email = $this->_get_user_meta( $this->_get_current_user_id(), '_new_email', true );
                if ( $email ) {
                    $html = "<div class='notice notice-info'><p>";
                    ob_start();
                    sprintf( $this->__( 'Your email address has not been updated yet. Please check your inbox at %s for a confirmation email.' ), '<code>' . $this->_esc_html( $email['new_email'] ) . '</code>' );
                    $html .= ob_get_clean();
                    $html .= "</p></div>";
                }
            }
            return $html;
        }//3503
        protected function _new_user_email_admin_notice():void{
            echo $this->_get_new_user_email_admin_notice();
        }
        /**
         * @description Get all personal data request types.
         * @return array
         */
        protected function _tp_privacy_action_request_types():array{
            return ['export_personal_data','remove_personal_data',];
        }//3523
        /**
         * @description Registers the personal data exporter for users.
         * @param $exporters
         * @return mixed
         */
        protected function _tp_register_user_personal_data_exporter( $exporters ){
            $exporters['tailoredpress-user'] = ['exporter_friendly_name' => $this->__( 'TailoredPress User' ),'callback' => 'tp_user_personal_data_exporter',];
            return $exporters;
        }//3538
        /**
         * @description Finds and exports personal data associated with,
         * @description . an email address from the user and user_meta table.
         * @param $email_address
         * @return array
         */
        protected function _tp_user_personal_data_exporter( $email_address ):array{
            $email_address = trim( $email_address );
            $data_to_export = [];
            $user = $this->_get_user_by( 'email', $email_address );
            if ( ! $user ) return ['data' => [],'done' => true,];
            $user_meta = $this->_get_user_meta( $user->ID );
            $user_props_to_export = [
                'ID' => $this->__( 'User ID' ),'user_login' => $this->__( 'User Login Name' ),
                'user_nicename' => $this->__( 'User Nice Name' ),'user_email' => $this->__( 'User Email' ),
                'user_url' => $this->__( 'User URL' ),'user_registered' => $this->__( 'User Registration Date' ),
                'display_name' => $this->__( 'User Display Name' ),'nickname' => $this->__( 'User Nickname' ),
                'first_name' => $this->__( 'User First Name' ),'last_name' => $this->__( 'User Last Name' ),
                'description' => $this->__( 'User Description' ),
            ];
            $user_data_to_export = [];
            foreach ( $user_props_to_export as $key => $name ) {
                $value = '';
                switch ( $key ) {
                    case 'ID':
                    case 'user_login':
                    case 'user_nicename':
                    case 'user_email':
                    case 'user_url':
                    case 'user_registered':
                    case 'display_name':
                        $value = $user->data->$key;
                        break;
                    case 'nickname':
                    case 'first_name':
                    case 'last_name':
                    case 'description':
                        $value = $user_meta[ $key ][0];
                        break;
                }
                if ( ! empty( $value ) ) $user_data_to_export[] = ['name' => $name,'value' => $value,];
            }
            $reserved_names = array_values( $user_props_to_export );
            $_extra_data = $this->_apply_filters( 'tp_privacy_additional_user_profile_data', array(), $user, $reserved_names );
            if ( is_array( $_extra_data ) && ! empty( $_extra_data ) ) {
                $extra_data = array_filter(
                    $_extra_data,
                    static function( $item ) use ( $reserved_names ) {
                        return ! in_array( $item['name'], $reserved_names, true );
                    }
                );
                if ( count( $extra_data ) !== count( $_extra_data ) ) {
                    $this->_doing_it_wrong(
                        __FUNCTION__,
                        sprintf(
                            $this->__( 'Filter %s returned items with reserved names.' ),
                            '<code>tp_privacy_additional_user_profile_data</code>'
                        ),
                        '0.0.1'
                    );
                }
                if ( ! empty( $extra_data ) )
                    $user_data_to_export = array_merge( $user_data_to_export, $extra_data );
            }
            $data_to_export[] = ['group_id' => 'user','group_label' => $this->__( 'User' ),
                'group_description' => $this->__( 'User&#8217;s profile data.' ),
                'item_id' => "user-{$user->ID}",'data' => $user_data_to_export,];
            if ( isset( $user_meta['community-events-location'] ) ) {
                $location = $this->_maybe_unserialize( $user_meta['community-events-location'][0] );
                $location_props_to_export = ['description' => $this->__( 'City' ),'country' => $this->__( 'Country' ),
                    'latitude' => $this->__( 'Latitude' ),'longitude' => $this->__( 'Longitude' ),'ip' => $this->__( 'IP' ),];
                $location_data_to_export = [];
                foreach ( $location_props_to_export as $key => $name ) {
                    if ( ! empty( $location[ $key ] ) )
                        $location_data_to_export[] = ['name' => $name,'value' => $location[ $key ],];
                }
                $data_to_export[] = ['group_id' => 'community-events-location','group_label' => $this->__( 'Community Events Location' ),
                    'group_description' => $this->__( 'User&#8217;s location data used for the Community Events in the WordPress Events and News dashboard widget.' ),
                    'item_id' => "community-events-location-{$user->ID}",'data' => $location_data_to_export,];
            }
            if ( isset( $user_meta['session_tokens'] ) ) {
                $session_tokens = $this->_maybe_unserialize( $user_meta['session_tokens'][0] );
                $session_tokens_props_to_export = ['expiration' => $this->__( 'Expiration' ),'ip' => $this->__( 'IP' ),
                    'ua' => $this->__( 'User Agent' ),'login' => $this->__( 'Last Login' ),];
                foreach ( $session_tokens as $token_key => $session_token ) {
                    $session_tokens_data_to_export = array();
                    foreach ( $session_tokens_props_to_export as $key => $name ) {
                        if ( ! empty( $session_token[ $key ] ) ) {
                            $value = $session_token[ $key ];
                            if ( in_array( $key, array( 'expiration', 'login' ), true ) )
                                $value = $this->_date_i18n( 'F d, Y H:i A', $value );
                            $session_tokens_data_to_export[] = ['name' => $name,'value' => $value,];
                        }
                    }
                    $data_to_export[] = ['group_id' => 'session-tokens','group_label' => $this->__( 'Session Tokens' ),
                        'group_description' => $this->__( 'User&#8217;s Session Tokens data.' ),
                        'item_id' => "session-tokens-{$user->ID}-{$token_key}",'data' => $session_tokens_data_to_export,];
                }
            }
            return ['data' => $data_to_export,'done' => true,];
        }//3557
        /**
         * @description Update log when privacy request is confirmed.
         * @param $request_id
         */
        protected function _tp_privacy_account_request_confirmed( $request_id ):void{
            $request = $this->_tp_get_user_request( $request_id );
            if ( ! $request ) return;
            if ( ! in_array( $request->status, array( 'request-pending', 'request-failed' ), true ) )
                return;
            $this->_update_post_meta( $request_id, '_tp_user_request_confirmed_timestamp', time() );
            $this->_tp_update_post(['ID' => $request_id, 'post_status' => 'request-confirmed',]);
        }//3753
        /**
         * @description Notify the site administrator via email when a request is confirmed.
         * @param $request_id
         */
        protected function _tp_privacy_send_request_confirmation_notification( $request_id ):void{
            $request = $this->_tp_get_user_request( $request_id );
            $manage_url = null;
            if ( ! is_a( $request, 'TP_User_Request' ) || 'request-confirmed' !== $request->status )
                return;
            $already_notified = (bool) $this->_get_post_meta( $request_id, '_tp_admin_notified', true );
            if ( $already_notified ) return;
            if ( 'export_personal_data' === $request->action_name )//todo
                $manage_url = $this->_admin_url( 'export-personal-data.php' );
            elseif ( 'remove_personal_data' === $request->action_name )
                $manage_url = $this->_admin_url( 'erase-personal-data.php' );
            $action_description = $this->_tp_user_request_action_description( $request->action_name );
            $admin_email = $this->_apply_filters( 'user_request_confirmed_email_to', $this->_get_site_option( 'admin_email' ), $request );
            $email_data = ['request' => $request,'user_email' => $request->email,'description' => $action_description,
                'manage_url' => $manage_url,'sitename' => $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES ),
                'siteurl' => $this->_home_url(),'admin_email' => $admin_email,];
            $subject = sprintf($this->__( '[%1$s] Action Confirmed: %2$s' ),$email_data['sitename'],$action_description);
            $subject = $this->_apply_filters( 'user_request_confirmed_email_subject', $subject, $email_data['sitename'], $email_data );
            $content = $this->__(
                'Howdy,
A user data privacy request has been confirmed on ###SITENAME###:
User: ###USER_EMAIL###
Request: ###DESCRIPTION###
You can view and manage these data privacy requests here:
###MANAGE_URL###
Regards,
All at ###SITENAME###
###SITEURL###'
            );
            $content = $this->_apply_filters( 'user_request_confirmed_email_content', $content, $email_data );
            $content = str_replace( '###SITENAME###', $email_data['sitename'], $content );
            $content .= str_replace( '###USER_EMAIL###', $email_data['user_email'], $content );
            $content .= str_replace( '###DESCRIPTION###', $email_data['description'], $content );
            $content .= str_replace( '###MANAGE_URL###', $this->_esc_url_raw( $email_data['manage_url'] ), $content );
            $content .= str_replace( '###SITEURL###', $this->_esc_url_raw( $email_data['siteurl'] ), $content );
            $headers = '';
            $headers = $this->_apply_filters( 'user_request_confirmed_email_headers', $headers, $subject, $content, $request_id, $email_data );
            $email_sent = $this->_tp_mail( $email_data['admin_email'], $subject, $content, $headers );
            if ( $email_sent ) $this->_update_post_meta( $request_id, '_tp_admin_notified', true );
        }//3783
        /**
         * @description Notify the user when their erasure request is fulfilled.
         * @param $request_id
         */
        protected function _tp_privacy_send_erasure_fulfillment_notification( $request_id ):void{
            $request = $this->_tp_get_user_request( $request_id );
            if ( ! is_a( $request, 'TP_User_Request' ) || 'request-completed' !== $request->status ) return;
            $already_notified = (bool) $this->_get_post_meta( $request_id, '_tp_user_notified', true );
            if ( $already_notified ) return;
            if ( ! empty( $request->user_id ) ) $locale = $this->_get_user_locale( $request->user_id );
            else $locale = $this->_get_locale();
            $switched_locale = $this->_switch_to_locale( $locale );
            $user_email = $this->_apply_filters( 'user_erasure_fulfillment_email_to', $request->email, $request );
            $email_data = ['request'=> $request,'message_recipient'  => $user_email,'privacy_policy_url' => $this->_get_privacy_policy_url(),
                'sitename' => $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES ),
                'siteurl' => $this->_home_url(),];
            $subject = sprintf($this->__( '[%s] Erasure Request Fulfilled' ),$email_data['sitename']);
            $subject = $this->_apply_filters( 'user_erasure_fulfillment_email_subject', $subject, $email_data['sitename'], $email_data );
            $content = $this->__(
                'Howdy,
Your request to erase your personal data on ###SITENAME### has been completed.
If you have any follow-up questions or concerns, please contact the site administrator.
Regards,
All at ###SITENAME###
###SITEURL###'
            );
            if ( ! empty( $email_data['privacy_policy_url'] ) ) {
                /* translators: Do not translate SITENAME, SITEURL, PRIVACY_POLICY_URL; those are placeholders. */
                $content = $this->__(
                    'Howdy,
Your request to erase your personal data on ###SITENAME### has been completed.
If you have any follow-up questions or concerns, please contact the site administrator.
For more information, you can also read our privacy policy: ###PRIVACY_POLICY_URL###
Regards,
All at ###SITENAME###
###SITEURL###'
                );
            }
            $content = $this->_apply_filters( 'user_erasure_fulfillment_email_content', $content, $email_data );
            $content = str_replace( '###SITENAME###', $email_data['sitename'], $content );
            $content .= str_replace( '###PRIVACY_POLICY_URL###', $email_data['privacy_policy_url'], $content );
            $content .= str_replace( '###SITEURL###', $this->_esc_url_raw( $email_data['siteurl'] ), $content );
            $headers = '';
            $headers = $this->_apply_filters( 'user_erasure_fulfillment_email_headers', $headers, $subject, $content, $request_id, $email_data );
            $email_sent = $this->_tp_mail( $user_email, $subject, $content, $headers );
            if ( $switched_locale ) $this->_restore_previous_locale();
            if ( $email_sent ) $this->_update_post_meta( $request_id, '_tp_user_notified', true );
        }//3995
        /**
         * @description Return request confirmation message HTML.
         * @param $request_id
         * @return string
         */
        protected function _tp_privacy_account_request_confirmed_message( $request_id ):string{
            $request = $this->_tp_get_user_request( $request_id );
            $message  = "<p class='success'>{$this->__( 'Action has been confirmed.' )}</p>";
            $message  .= "<p>{$this->__('The site administrator has been notified and will fulfill your request as soon as possible.')}</p>";
            if ( $request && in_array( $request->action_name, $this->_tp_privacy_action_request_types(), true ) ) {
                if ( 'export_personal_data' === $request->action_name ) {
                    $message  = "<p class='success'>{$this->__('Thanks for confirming your export request.')}</p>";
                    $message  .= "<p>{$this->__('The site administrator has been notified. You will receive a link to download your export via email when they fulfill your request.')}</p>";
                }elseif ( 'remove_personal_data' === $request->action_name ) {
                    $message  = "<p class='success'>{$this->__('Thanks for confirming your erasure request.')}</p>";
                    $message  .= "<p>{$this->__('The site administrator has been notified. You will receive an email confirmation when they erase your data.')}</p>";
                }
            }
            $message = $this->_apply_filters( 'user_request_action_confirmed_message', $message, $request_id );
            return $message;
        }//4268
        /**
         * @description Create and log a user request to perform a specific action.
         * @param string $email_address
         * @param string $action_name
         * @param string $status
         * @param \array[] ...$request_data
         * @return TP_Error
         */
        protected function _tp_create_user_request( $email_address = '', $action_name = '', $status = 'pending',array ...$request_data):TP_Error{
            $email_address = $this->_sanitize_email( $email_address );
            $action_name   = $this->_sanitize_key( $action_name );
            if ( ! $this->_is_email( $email_address ) )
                return new TP_Error( 'invalid_email', $this->__( 'Invalid email address.' ) );
            if ( ! in_array( $action_name, $this->_tp_privacy_action_request_types(), true ) )
                return new TP_Error( 'invalid_action', $this->__( 'Invalid action name.' ) );
            if ( ! in_array( $status, array( 'pending', 'confirmed' ), true ) )
                return new TP_Error( 'invalid_status', $this->__( 'Invalid request status.' ) );
            $user    = $this->_get_user_by( 'email', $email_address );
            $user_id = $user && ! $this->_init_error( $user ) ? $user->ID : 0;
            $requests_query = $this->_init_query(['post_type' => 'user_request','post_name__in' => [$action_name ],'title' => $email_address,
                    'post_status' => ['request-pending','request-confirmed',],'fields' => 'ids',]);
            if ( $requests_query->found_posts )
                return new TP_Error( 'duplicate_request', $this->__( 'An incomplete personal data request for this email address already exists.' ) );
            $request_id = $this->_tp_insert_post(['post_author' => $user_id,'post_name' => $action_name,
                'post_title' => $email_address,'post_content' => $this->_tp_json_encode( $request_data ),
                'post_status' => 'request-' . $status,'post_type' => 'user_request',
                'post_date' => $this->_current_time( 'mysql', false ),'post_date_gmt' => $this->_current_time( 'mysql', true ),],
                true);
            return $request_id;
        }//4314
    }
}else die;