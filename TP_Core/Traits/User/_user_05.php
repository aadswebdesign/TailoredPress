<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Session_Tokens;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _user_05{
        use _init_error;
        use _init_db;
        use _init_user;
        /**
         * @description Handles resetting the user's password.
         * @param $user
         * @param $new_pass
         */
        protected function _reset_password( $user, $new_pass ):void{
            $this->_do_action( 'password_reset', $user, $new_pass );
            $this->_tp_set_password( $new_pass, $user->ID );
            $this->_update_user_meta( $user->ID, 'default_password_nag', false );
            $this->_do_action( 'after_password_reset', $user, $new_pass );
        }//3042
        /**
         * @description Handles registering a new user.
         * @param $user_login
         * @param $user_email
         * @return TP_Error
         */
        protected function _register_new_user( $user_login, $user_email ):TP_Error{
            $errors = new TP_Error();
            $sanitized_user_login = $this->_sanitize_user( $user_login );
            $user_email = $this->_apply_filters( 'user_registration_email', $user_email );
            if ( '' === $sanitized_user_login )
                $errors->add( 'empty_username', $this->__( '<strong>Error</strong>: Please enter a username.' ) );
            elseif ( ! $this->_validate_username( $user_login ) ) {
                $errors->add( 'invalid_username', $this->__( '<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
                $sanitized_user_login = '';
            } elseif ( $this->_username_exists( $sanitized_user_login ) )
                $errors->add( 'username_exists', $this->__( '<strong>Error</strong>: This username is already registered. Please choose another one.' ) );
            else {
                $illegal_user_logins = (array) $this->_apply_filters( 'illegal_user_logins', [] );
                if ( in_array( strtolower( $sanitized_user_login ), array_map( 'strtolower', $illegal_user_logins ), true ) )
                    $errors->add( 'invalid_username', $this->__( '<strong>Error</strong>: Sorry, that username is not allowed.' ) );
            }
            if ( '' === $user_email )
                $errors->add( 'empty_email', $this->__( '<strong>Error</strong>: Please type your email address.' ) );
            elseif ( ! $this->_is_email( $user_email ) ) {
                $errors->add( 'invalid_email', $this->__( '<strong>Error</strong>: The email address isn&#8217;t correct.' ) );
                $user_email = '';
            } elseif ( $this->_email_exists( $user_email ) )
                $errors->add( 'email_exists',
                    sprintf($this->__( '<strong>Error:</strong> This email address is already registered. <a href="%s">Log in</a> with this address or choose another one.' ),
                        $this->_tp_login_url()));
            $this->_do_action( 'register_post', $sanitized_user_login, $user_email, $errors );
            $errors = $this->_apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );
            if ($errors instanceof TP_Error && $errors->has_errors() ) return $errors;
            $user_pass = $this->_tp_generate_password( 12, false );
            $user_id   = $this->_tp_create_user( $sanitized_user_login, $user_pass, $user_email );
            if ( ! $user_id || $this->_init_error( $user_id ) ) {
                $errors->add('register_fail',
                    sprintf($this->__( '<strong>Error</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">site admin</a>!' ),
                        $this->_get_option( 'admin_email' )
                    ));
                return $errors;
            }
            $this->_update_user_meta( $user_id, 'default_password_nag', true ); // Set up the password change nag.
            if ( ! empty( $_COOKIE['tp_lang'] ) ) {
                $tp_lang = $this->_sanitize_text_field( $_COOKIE['wp_lang'] );
                if ( in_array( $tp_lang, $this->_get_available_languages(), true ) )
                    $this->_update_user_meta( $user_id, 'locale', $tp_lang ); // Set user locale if defined on registration.
            }
            $this->_do_action( 'register_new_user', $user_id );
            return $user_id;
        }//3076
        /**
         * @description Initiates email notifications related to the creation of new users.
         * @param $user_id
         * @param string $notify
         */
        protected function _tp_send_new_user_notifications( $user_id, $notify = 'both' ):void{
            $this->_tp_new_user_notification( $user_id, $notify );
        }//3207
        /**
         * @description Retrieve the current session token from the logged_in cookie.
         * @return string
         */
        protected function _tp_get_session_token():string{
            $cookie = $this->_tp_parse_auth_cookie( '', 'logged_in' );
            return ! empty( $cookie['token'] ) ? $cookie['token'] : '';
        }//3218
        /**
         * @description Retrieve a list of sessions for the current user.
         * @return mixed
         */
        protected function _tp_get_all_sessions(){
            $this->tp_manager = TP_Session_Tokens::get_instance( $this->_get_current_user_id() );
            return $this->tp_manager->get_all();
        }//3230
        /**
         * @description Remove the current session token from the database.
         */
        protected function _tp_destroy_current_session():void{
            $token = $this->_tp_get_session_token();
            if ( $token ) {
                $this->tp_manager = TP_Session_Tokens::get_instance( $this->_get_current_user_id() );
                $this->tp_manager->destroy( $token );
            }
        }//3240
        /**
         * @description Remove all but the current session token for the current user for the database.
         */
        protected function _tp_destroy_other_sessions():void{
            $token = $this->_tp_get_session_token();
            if ( $token ) {
                $this->tp_manager = TP_Session_Tokens::get_instance( $this->_get_current_user_id() );
                $this->tp_manager->destroy_others( $token );
            }
        }//3253
        /**
         * @description Remove all session tokens for the current user from the database.
         */
        protected function _tp_destroy_all_sessions():void{
            $this->tp_manager = TP_Session_Tokens::get_instance( $this->_get_current_user_id() );
            $this->tp_manager->destroy_all();
        }//3266
        /**
         * @description Get the user IDs of all users with no role on this site.
         * @param null $site_id
         * @return array
         */
        protected function _tp_get_users_with_no_role( $site_id = null ):array{
            $this->tpdb = $this->_init_db();
            if ( ! $site_id ) $site_id = $this->_get_current_blog_id();
            $prefix = $this->tpdb->get_blog_prefix( $site_id );
            $roles = $this->_init_roles();
            if ( $this->_is_multisite() && $this->_get_current_blog_id() !== $site_id ) {
                $this->_switch_to_blog( $site_id );
                $role_names = $roles->get_names();
                $this->_restore_current_blog();
            } else  $role_names = $roles->get_names();
            $regex = implode( '|', array_keys( $role_names ) );
            $regex = preg_replace( '/[^a-zA-Z_\|-]/', '', $regex );
            $users = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " user_id FROM $this->tpdb->user_meta WHERE meta_key = '{$prefix}capabilities' AND meta_value NOT REGEXP %s ", $regex ));
            return $users;
        }//3280
        /**
         * @description Retrieves the current user object.
         * @return null|TP_User
         */
        protected function _tp_get_current_user():?TP_User{
            if ( ! empty( $this->tp_current_user ) ){
                if ( $this->tp_current_user instanceof TP_User ) return $this->tp_current_user;
                if ( is_object( $this->tp_current_user ) && isset( $this->tp_current_user->ID ) ) {
                    $cur_id       = $this->tp_current_user->ID;
                    $this->tp_current_user = null;
                    $this->_tp_set_current_user( $cur_id );
                    return $this->tp_current_user;
                }
                $this->tp_current_user = null;
                $this->_tp_set_current_user( 0 );
                return $this->tp_current_user;
            }
            if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
                $this->_tp_set_current_user( 0 );
                return $this->tp_current_user;
            }
            $user_id = $this->_apply_filters( 'determine_current_user', false );
            if ( ! $user_id ) {
                $this->_tp_set_current_user( 0 );
                return $this->tp_current_user;
            }
            $this->_tp_set_current_user( $user_id );
            return $this->tp_current_user;
        }//3333
    }
}else die;