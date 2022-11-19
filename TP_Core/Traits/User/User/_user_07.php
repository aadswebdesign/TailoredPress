<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Traits\Inits\_init_hasher;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User_Request;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _user_07 {
        use _init_error;
        use _init_db;
        use _init_user;
        use _init_hasher;
        /**
         * @description Get action description from the name and return a string.
         * @param $action_name
         * @return mixed
         */
        protected function _tp_user_request_action_description( $action_name ){
            switch ( $action_name ) {
                case 'export_personal_data':
                    $description = $this->__( 'Export Personal Data' );
                    break;
                case 'remove_personal_data':
                    $description = $this->__( 'Erase Personal Data' );
                    break;
                default:
                    /* translators: %s: Action name. */
                    $description = sprintf( $this->__( 'Confirm the "%s" action' ), $action_name );
                    break;
            }
            return $this->_apply_filters( 'user_request_action_description', $description, $action_name );
        }//4376
        /**
         * @description Send a confirmation request email to confirm an action.
         * @param $request_id
         * @return bool|TP_Error
         */
        protected function _tp_send_user_request( $request_id ){
            $request_id = $this->_abs_int( $request_id );
            $request    = $this->_tp_get_user_request( $request_id );
            if ( ! $request ) return new TP_Error( 'invalid_request', $this->__( 'Invalid personal data request.' ) );
            if ( ! empty( $request->user_id ) ) $locale = $this->_get_user_locale( $request->user_id );
            else $locale = $this->_get_locale();
            $switched_locale = $this->_switch_to_locale( $locale );
            $email_data = ['request' => $request,'email' => $request->email,
                'description' => $this->_tp_user_request_action_description( $request->action_name ),
                'confirm_url' => $this->_add_query_arg(['action' => 'confirm_action','request_id' => $request_id,
                    'confirm_key' => $this->_tp_generate_user_request_key( $request_id ),],
                    $this->_tp_login_url()
                ),
                'sitename'    => $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES ),
                'siteurl'     => $this->_home_url(),];
            $subject = sprintf( $this->__( '[%1$s] Confirm Action: %2$s' ), $email_data['sitename'], $email_data['description'] );
            $subject = $this->_apply_filters( 'user_request_action_email_subject', $subject, $email_data['sitename'], $email_data );
            $content = $this->__(
                'Howdy,
A request has been made to perform the following action on your account:
     ###DESCRIPTION###
To confirm this, please click on the following link:
###CONFIRM_URL###
You can safely ignore and delete this email if you do not want to
take this action.
Regards,
All at ###SITENAME###
###SITEURL###'
            );
            $content = $this->_apply_filters( 'user_request_action_email_content', $content, $email_data );
            $content = str_replace( '###DESCRIPTION###', $email_data['description'], $content );
            $content .= str_replace( '###CONFIRM_URL###', $this->_esc_url_raw( $email_data['confirm_url'] ), $content );
            $content .= str_replace( '###EMAIL###', $email_data['email'], $content );
            $content .= str_replace( '###SITENAME###', $email_data['sitename'], $content );
            $content .= str_replace( '###SITEURL###', $this->_esc_url_raw( $email_data['siteurl'] ), $content );
            $headers = '';
            $headers = $this->_apply_filters( 'user_request_action_email_headers', $headers, $subject, $content, $request_id, $email_data );
            $email_sent = $this->_tp_mail( $email_data['email'], $subject, $content, $headers );
            if ( $switched_locale ) $this->_restore_previous_locale();
            if ( ! $email_sent ) return new TP_Error( 'privacy_email_error', $this->__( 'Unable to send personal data export confirmation email.' ) );
            return true;
        }//4411
        /**
         * @description Returns a confirmation key for a,
         * @description . user action and stores the hashed version for future comparison.
         * @param $request_id
         * @return mixed
         */
        protected function _tp_generate_user_request_key( $request_id ){
            $tp_hasher = $this->_init_hasher(8,true);
            $key = $this->_tp_generate_password( 20, false );
            $this->_tp_update_post(['ID' => $request_id,'post_status' => 'request-pending','post_password' => $tp_hasher->HashPassword( $key ),]);
            return $key;
        }//4563
        /**
         * @description Validate a user request by comparing the key with the request's key.
         * @param $request_id
         * @param $key
         * @return bool|TP_Error
         */
        protected function _tp_validate_user_request_key( $request_id, $key ){
            $tp_hasher = $this->_init_hasher(8,true);
            $request_id       = $this->_abs_int( $request_id );
            $request          = $this->_tp_get_user_request( $request_id );
            $saved_key        = $request->confirm_key;
            $key_request_time = $request->modified_timestamp;
            if ( ! $request || ! $saved_key || ! $key_request_time )
                return new TP_Error( 'invalid_request', $this->__( 'Invalid personal data request.' ) );
            if ( ! in_array( $request->status, array( 'request-pending', 'request-failed' ), true ) )
                return new TP_Error( 'expired_request', $this->__( 'This personal data request has expired.' ) );
            if ( empty( $key ) )
                return new TP_Error( 'missing_key', $this->__( 'The confirmation key is missing from this personal data request.' ) );
            $expiration_duration = (int) $this->_apply_filters( 'user_request_key_expiration', DAY_IN_SECONDS );
            $expiration_time     = $key_request_time + $expiration_duration;
            if ( ! $tp_hasher->CheckPassword( $key, $saved_key ) )
                return new TP_Error( 'invalid_key', $this->__( 'The confirmation key is invalid for this personal data request.' ) );
            if ( ! $expiration_time || time() > $expiration_time )
                return new TP_Error( 'expired_key', $this->__( 'The confirmation key has expired for this personal data request.' ) );
            return true;
        }//4595
        /**
         * @description Return the user request object for the specified request ID.
         * @param $request_id
         * @return bool|TP_User_Request
         */
        protected function _tp_get_user_request( $request_id ){
            $request_id = $this->_abs_int( $request_id );
            $post       = $this->_get_post( $request_id );
            if ( ! $post || 'user_request' !== $post->post_type ) return false;
            return new TP_User_Request( $post );
        }//4649
        /**
         * @description Checks if Application Passwords is supported.
         * @return bool
         */
        protected function _tp_is_application_passwords_supported():bool{
            return $this->_is_ssl() || 'local' === $this->_tp_get_environment_type();
        }//4670
        /**
         * @description Checks if Application Passwords is globally available.
         * @return mixed
         */
        protected function _tp_is_application_passwords_available(){
            return $this->_apply_filters( 'tp_is_application_passwords_available', [$this,'_tp_is_application_passwords_supported'] );
        }//4684
        /**
         * @description Checks if Application Passwords is available for a specific user.
         * @param $user
         * @return bool
         */
        protected function _tp_is_application_passwords_available_for_user( $user ):bool{
            if ( ! $this->_tp_is_application_passwords_available() ) return false;
            if ( ! is_object( $user ) ) $user = $this->_get_user_data( $user );
            //if( $user instanceof TP_User );
            if ( ($user instanceof TP_User) && (! $user || ! $user->exists()) ) return false;
            return $this->_apply_filters( 'tp_is_application_passwords_available_for_user', true, $user );
        }//4706
    }
}else die;