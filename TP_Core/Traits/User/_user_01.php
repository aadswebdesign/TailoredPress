<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
    use TP_Core\Traits\Inits\_init_db;
    use TP_Core\Traits\Inits\_init_error;
    use TP_Core\Libs\Users\TP_User;
    use TP_Core\Libs\TP_Error;
    use TP_Core\Libs\TP_Application_Passwords;
if(ABSPATH){
    trait _user_01 {
        use _init_error;
        use _init_db;
        /**
         * @description Authenticates and logs a user in with 'remember' capability.
         * @param mixed $secure_cookie
         * @param \array[] ...$credentials
         * @return mixed
         */
        protected function _tp_sign_on($secure_cookie = '',array ...$credentials){
            if ( empty( $credentials ) ) {
                if ( ! empty( $_POST['log'] ) ) $credentials['user_login'] = $this->_tp_unslash( $_POST['log'] );
                if ( ! empty( $_POST['pwd'] ) )  $credentials['user_password'] = $_POST['pwd'];
                if ( ! empty( $_POST['remember_me'] ) ) $credentials['remember'] = $_POST['remember_me'];
            }
            if ( ! empty( $credentials['remember'] ) ) $credentials['remember'] = true;
            else $credentials['remember'] = false;
            $this->_do_action_ref_array( 'tp_authenticate', array( &$credentials['user_login'], &$credentials['user_password'] ) );
            if ( '' === $secure_cookie ) $secure_cookie = $this->_is_ssl();
            $this->tp_auth_secure_cookie = $secure_cookie;
            $this->_add_filter( 'authenticate', 'tp_authenticate_cookie', 30, 3 );
            $user = $this->_tp_authenticate( $credentials['user_login'], $credentials['user_password'] );
            if ( $this->_init_error( $user ) ) return $user;
            $this->_tp_set_auth_cookie( $user->ID, $credentials['remember'], $secure_cookie );
            $this->_do_action( 'tp_login', $user->user_login, $user );
            return $user;
        }//33
        /**
         * @description Authenticate a user, confirming the username and password are valid.
         * @param $user
         * @param $username
         * @param $password
         * @return TP_Error|TP_User
         */
        protected function _tp_authenticate_username_password($user, $username, $password ){
            if ( empty( $username ) || empty( $password ) ) {
                if ( $this->_init_error( $user ) ) return $user;
                $error = new TP_Error();
                if ( empty( $username ) )
                    $error->add( 'empty_username', $this->__( '<strong>Error</strong>: The username field is empty.' ) );
                if ( empty( $password ) )
                    $error->add( 'empty_password', $this->__( '<strong>Error</strong>: The password field is empty.' ) );
                return $error;
            }
            $user = $this->_get_user_by( 'login', $username );
            if ( ! $user )
                return new TP_Error('invalid_username',
                    sprintf($this->__( '<strong>Error</strong>: The username <strong>%s</strong> is not registered on this site. If you are unsure of your username, try your email address instead.' ),
                        $username));
            $user = $this->_apply_filters( 'tp_authenticate_user', $user, $password );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_tp_check_password( $password, $user->user_pass, $user->ID ) )
                return new TP_Error('incorrect_password',
                    sprintf(
                        $this->__( '<strong>Error</strong>: The password you entered for the username %s is incorrect.' ),
                        '<strong>' . $username . '</strong>') . "<a href='{$this->_tp_lost_password_url()}'>{$this->__( 'Lost your password?' )}</a>"
                );
            return $user;
        }//124
        /**
         * @description Authenticates a user using the email and password.
         * @param $user
         * @param $email
         * @param $password
         * @return TP_Error|TP_User
         */
        protected function _tp_authenticate_email_password( $user, $email, $password ) {
            if ( empty( $email ) || empty( $password ) ) {
                if ( $this->_init_error( $user ) ) return $user;
                $error = new TP_Error();
                if ( empty( $email ) )
                    $error->add( 'empty_username', $this->__( '<strong>Error</strong>: The email field is empty.' ) );
                if ( empty( $password ) )
                    $error->add( 'empty_password', $this->__( '<strong>Error</strong>: The password field is empty.' ) );
                return $error;
            }
            if ( ! $this->_is_email( $email ) ) return $user;
            $user = $this->_get_user_by( 'email', $email );
            if ( ! $user ) return new TP_Error('invalid_email',$this->__( 'Unknown email address. Check again or try your username.' ));
            $user = $this->_apply_filters( 'tp_authenticate_user', $user, $password );
            if ( $this->_init_error( $user ) ) return $user;
            if ( ! $this->_tp_check_password( $password, $user->user_pass, $user->ID ) )
                return new TP_Error('incorrect_password',
                    sprintf( $this->__( '<strong>Error</strong>: The password you entered for the email address %s is incorrect.' ),
                        '<strong>' . $email . '</strong>') . "<a href='{$this->_tp_lost_password_url()}'>{$this->__( 'Lost your password?' )}</a>");
            return $user;
        }//202
        /**
         * @description Authenticate the user using the TailoredPress auth cookie.
         * @param $user
         * @param $username
         * @param $password
         * @return TP_Error| TP_User
         */
        protected function _tp_authenticate_cookie( $user, $username, $password ){
            if ( empty( $username ) && empty( $password ) ) {
                if ( $this->tp_auth_secure_cookie ) $auth_cookie = SECURE_AUTH_COOKIE;
                else $auth_cookie = AUTH_COOKIE;
                if ( ! empty( $_COOKIE[ $auth_cookie ] ) )
                    return new TP_Error( 'expired_session', $this->__( 'Please log in again.' ) );
            }
            return $user;
        }//275
        /**
         * @description Authenticates the user using an application password.
         * @param $input_user
         * @param $username
         * @param $password
         * @return mixed
         */
        protected function _tp_authenticate_application_password( $input_user, $username, $password ){
            if ( ! TP_Application_Passwords::is_in_use() ) return $input_user;
            $is_api_request = ( ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) );
            $is_api_request = $this->_apply_filters( 'application_password_is_api_request', $is_api_request );
            if ( ! $is_api_request ) return $input_user;
            $error = null;
            $user  = $this->_get_user_by( 'login', $username );
            if ( ! $user && $this->_is_email( $username ) ) $user = $this->_get_user_by( 'email', $username );
            if ( ! $user ) {
                if ( $this->_is_email( $username ) )
                    $error = new TP_Error('invalid_email',$this->__( '<strong>Error</strong>: Unknown email address. Check again or try your username.' ));
                else $error = new TP_Error('invalid_username',$this->__( '<strong>Error</strong>: Unknown username. Check again or try your email address.' ));
            }elseif ( ! $this->_tp_is_application_passwords_available() )
                $error = new TP_Error('application_passwords_disabled',$this->__( 'Application passwords are not available.' ));
            elseif ( ! $this->_tp_is_application_passwords_available_for_user( $user ) )
                $error = new TP_Error('application_passwords_disabled_for_user',
                    $this->__( 'Application passwords are not available for your account. Please contact the site administrator for assistance.' ));
            if ( $error ) {
                $this->_do_action( 'application_password_failed_authentication', $error );
                return $error;
            }
            $password = preg_replace( '/[^a-z\d]/i', '', $password );
            $hashed_passwords = TP_Application_Passwords::get_user_application_passwords( $user->ID );
            foreach ( $hashed_passwords as $key => $item ) {
                if ( ! $this->_tp_check_password( $password, $item['password'], $user->ID ) ) continue;
                $error = new TP_Error();
                $this->_do_action( 'tp_authenticate_application_password_errors', $error, $user, $item, $password );
                if ( $this->_init_error( $error ) && $error->has_errors() ) {
                    $this->_do_action( 'application_password_failed_authentication', $error );
                    return $error;
                }
                TP_Application_Passwords::record_application_password_usage( $user->ID, $item['uuid'] );
                $this->_do_action( 'application_password_did_authenticate', $user, $item );
                return $user;
            }
            $error = new TP_Error('incorrect_password',$this->__( 'The provided password is an invalid application password.' ));
            $this->_do_action( 'application_password_failed_authentication', $error );
            return $error;
        }//316
        /**
         * @description Validates the application password credentials passed via Basic Authentication.
         * @param $input_user
         * @return int
         */
        protected function _tp_validate_application_password( $input_user ):int{
            if ( ! empty( $input_user ) ) return $input_user;
            if ( ! $this->_tp_is_application_passwords_available() ) return $input_user;
            // Both $_SERVER['PHP_AUTH_USER'] and $_SERVER['PHP_AUTH_PW'] must be set in order to attempt authentication.
            if ( ! isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) return $input_user;
            $authenticated = $this->_tp_authenticate_application_password( null, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
            if ( $authenticated instanceof TP_User ) return $authenticated->ID;
            return $input_user;
        }//459
        /**
         * @description For Multisite blogs, check if the authenticated user has been marked as a * spammer,
         * @description . or if the user's primary blog has been marked as spam.
         * @param $user
         * @return TP_Error|TP_User
         */
        protected function _tp_authenticate_spam_check( $user ){
            if ($this->_is_multisite() ) {
                $spammed = $this->_apply_filters( 'check_is_user_spammed', $this->_is_user_spammy( $user ), $user );
                if ( $spammed ) return new TP_Error( 'spammer_account', $this->__( '<strong>Error</strong>: Your account has been marked as a spammer.' ) );
            }
            return $user;
        }//493
        /**
         * @description Validates the logged-in cookie.
         * @param $user_id
         * @return bool
         */
        protected function _tp_validate_logged_in_cookie( $user_id ):bool{
            if ( $user_id ) return $user_id;
            if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) || $this->_is_blog_admin() || $this->_is_network_admin() )
                return false;
            return $this->_tp_validate_auth_cookie( $_COOKIE[ LOGGED_IN_COOKIE ], 'logged_in' );
        }//527
        /**
         * @description Number of posts user has written.
         * @param $userid
         * @param string $post_type
         * @param bool $public_only
         * @return mixed
         */
        protected function _count_user_posts( $userid, $post_type = 'post', $public_only = false ){
            $this->tpdb = $this->_init_db();
            $where = $this->_get_posts_by_author_sql( $post_type, true, $userid, $public_only );
            $count = $this->tpdb->get_var( TP_SELECT . " COUNT(*) FROM $this->tpdb->posts $where" );
            return $this->_apply_filters( 'get_user_num_posts', $count, $userid, $post_type, $public_only );
        }//554
        /**
         * @description Number of posts written by a list of users.
         * @param $users
         * @param string $post_type
         * @param bool $public_only
         * @return array
         */
        protected function _count_many_users_posts( $users, $post_type = 'post', $public_only = false ):array{
            $this->tpdb = $this->_init_db();
            $count = [];
            if ( empty( $users ) || ! is_array( $users ) ) return $count;
            $user_list = implode( ',', array_map( [$this, '_abs_int'], $users ) );
            $where    = $this->_get_posts_by_author_sql( $post_type, true, null, $public_only );
            $result = $this->tpdb->get_results( TP_SELECT . " post_author, COUNT(*) FROM $this->tpdb->posts $where AND post_author IN ($user_list) GROUP BY post_author", ARRAY_N );
            foreach ( $result as $row ) $count[ $row[0] ] = $row[1];
            foreach ( $users as $id ) {
                if ( ! isset( $count[ $id ] ) ) $count[ $id ] = 0;
            }
            return $count;
        }//588
    }
}else die;