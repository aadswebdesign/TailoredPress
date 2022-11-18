<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:02
 */
namespace TP_Core\Traits\Pluggables;
use TP_Core\Libs\TP_Session_Tokens;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _pluggable_02 {
        /**
         * @description Log the current user out.
         */
        protected function _tp_logout():void{
            $user_id = $this->_get_current_user_id();
            $this->_tp_destroy_current_session();
            $this->_tp_clear_auth_cookie();
            $this->_tp_set_current_user( 0 );
            $this->_do_action( 'tp_logout', $user_id );
        }//644
        /**
         * @description Validates authentication cookie.
         * @param string $cookie
         * @param string $scheme
         * @return bool
         */
        protected function _tp_validate_auth_cookie( $cookie = '', $scheme = '' ):bool{
            $cookie_elements = $this->_tp_parse_auth_cookie( $cookie, $scheme );
            if ( ! $cookie_elements ) {
                $this->_do_action('auth_cookie_malformed', $cookie, $scheme);
                return false;
            }
            $scheme     = $cookie_elements['scheme'];
            $username   = $cookie_elements['username'];
            $hmac       = $cookie_elements['hmac'];
            $token      = $cookie_elements['token'];
            $expired    = $cookie_elements['expiration'];
            $expiration = $cookie_elements['expiration'];
            //todo should become the fetch way
            if ('POST' === $_SERVER['REQUEST_METHOD'] || $this->_tp_doing_async() )
                $expired += HOUR_IN_SECONDS;
            if ( $expired < time() ) {
                $this->_do_action( 'auth_cookie_expired', $cookie_elements );
                return false;
            }
            $user = $this->_get_user_by( 'login', $username );
            if ( ! $user ) {
                $this->_do_action( 'auth_cookie_bad_username', $cookie_elements );
                return false;
            }
            $pass_frag = substr( $user->user_pass, 8, 4 );
            $key = $this->_tp_hash( $username . '|' . $pass_frag . '|' . $expiration . '|' . $token, $scheme );
            $algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
            $hash = hash_hmac( $algo, $username . '|' . $expiration . '|' . $token, $key );
            if ( ! $this->_hash_equals( $hash, $hmac ) ) {
                $this->_do_action( 'auth_cookie_bad_hash', $cookie_elements );
                return false;
            }
            $manager = TP_Session_Tokens::get_instance( $user->ID );
            if ( ! $manager->verify( $token ) ) {
                $this->_do_action( 'auth_cookie_bad_session_token', $cookie_elements );
                return false;
            }
            if ( $expiration < time() ) $this->tp_login_grace_period = 1;
            $this->_do_action( 'auth_cookie_valid', $cookie_elements, $user );
            return $user->ID;
        }//681
        /**
         * @description Generates authentication cookie contents.
         * @param $user_id
         * @param $expiration
         * @param string $scheme
         * @param string $token
         * @return string
         */
        protected function _tp_generate_auth_cookie( $user_id, $expiration, $scheme = 'auth', $token = '' ):string{
            $user = $this->_get_user_data( $user_id );
            if ( ! $user ) return '';
            if ( ! $token ) {
                $manager = TP_Session_Tokens::get_instance( $user_id );
                $token   = $manager->create( $expiration );
            }
            $pass_frag = substr( $user->user_pass, 8, 4 );
            $key = $this->_tp_hash( $user->user_login . '|' . $pass_frag . '|' . $expiration . '|' . $token, $scheme );
            $algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
            $hash = hash_hmac( $algo, $user->user_login . '|' . $expiration . '|' . $token, $key );
            $cookie = $user->user_login . '|' . $expiration . '|' . $token . '|' . $hash;
            return $this->_apply_filters( 'auth_cookie', $cookie, $user_id, $expiration, $scheme, $token );
        }//801
        /**
         * @description Parses a cookie into its components.
         * @param mixed $cookie
         * @param string $scheme
         * @return array|bool
         */
        protected function _tp_parse_auth_cookie( $cookie = '', $scheme = '' ){
            if ( empty( $cookie ) ) {
                switch ( $scheme ) {
                    case 'auth':
                        $cookie_name = AUTH_COOKIE;
                        break;
                    case 'secure_auth':
                        $cookie_name = SECURE_AUTH_COOKIE;
                        break;
                    case 'logged_in':
                        $cookie_name = LOGGED_IN_COOKIE;
                        break;
                    default:
                        if ( $this->_is_ssl() ) {
                            $cookie_name = SECURE_AUTH_COOKIE;
                            $scheme      = 'secure_auth';
                        } else {
                            $cookie_name = AUTH_COOKIE;
                            $scheme      = 'auth';
                        }
                }
                if ( empty( $_COOKIE[ $cookie_name ] ) ) return false;
                $cookie = $_COOKIE[$cookie_name ];
            }
            $cookie_elements = explode( '|', $cookie );
            if ( count( $cookie_elements ) !== 4 ) return false;
            @list( $username, $expiration, $token, $hmac ) = $cookie_elements;
            return compact( 'username', 'expiration', 'token', 'hmac', 'scheme' );
        }//848
        /**
         * @description Sets the authentication cookies based on user ID.
         * @param $user_id
         * @param bool $remember
         * @param string $secure
         * @param string $token
         */
        protected function _tp_set_auth_cookie( $user_id, $remember = false, $secure = '', $token = '' ):void{
            if ( $remember ) {
                $expiration = time() + $this->_apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_id, $remember );
                $expire = $expiration + ( 12 * HOUR_IN_SECONDS );
            } else {
                $expiration = time() + $this->_apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember );
                $expire     = 0;
            }
            if ( '' === $secure ) $secure = (string)$this->_is_ssl();
            // Front-end cookie is secure when the auth cookie is secure and the site's home URL uses HTTPS.
            $secure_logged_in_cookie = $secure && 'https' === parse_url( $this->_get_option( 'home' ), PHP_URL_SCHEME );
            $secure = $this->_apply_filters( 'secure_auth_cookie', $secure, $user_id );
            $secure_logged_in_cookie = $this->_apply_filters( 'secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure );
            if ( $secure ) {
                $auth_cookie_name = SECURE_AUTH_COOKIE;
                $scheme           = 'secure_auth';
            } else {
                $auth_cookie_name = AUTH_COOKIE;
                $scheme           = 'auth';
            }
            if ( '' === $token ) {
                $manager = TP_Session_Tokens::get_instance( $user_id );
                $token   = $manager->create( $expiration );
            }
            $auth_cookie      = $this->_tp_generate_auth_cookie( $user_id, $expiration, $scheme, $token );
            $logged_in_cookie = $this->_tp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token );
            $this->_do_action( 'set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme, $token );
            $this->_do_action( 'set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in', $token );
            if ( ! $this->_apply_filters( 'send_auth_cookies', true ) ) return;
            setcookie( $auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true );
            setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIE_PATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
            if ( COOKIE_PATH !== SITE_COOKIE_PATH )
                setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITE_COOKIE_PATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
        }//904
        /**
         * @description Removes all of the cookies associated with authentication.
         */
        protected function _tp_clear_auth_cookie():void{
            $this->_do_action( 'clear_auth_cookie' );
            if ( ! $this->_apply_filters( 'send_auth_cookies', true ) ) return;
            // Auth cookies.
            setcookie( AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( SECURE_AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( LOGGED_IN_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( LOGGED_IN_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
            // Settings cookies.
            setcookie( 'tp-settings-' . $this->_get_current_user_id(), ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH );
            setcookie( 'tp-settings-time-' . $this->_get_current_user_id(), ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH );
            // Old cookies.
            setcookie( AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( SECURE_AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( SECURE_AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
            // Even older cookies.
            setcookie( USER_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( PASS_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( USER_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
            setcookie( PASS_COOKIE, ' ', time() - YEAR_IN_SECONDS, SITE_COOKIE_PATH, COOKIE_DOMAIN );
            // Post password cookie.
            setcookie( 'tp-post_pass_' . COOKIE_HASH, ' ', time() - YEAR_IN_SECONDS, COOKIE_PATH, COOKIE_DOMAIN );
        }//1026
        /**
         * @description Determines whether the current visitor is a logged in user.
         * @return mixed
         */
        protected function _is_user_logged_in(){
            $_user = $this->_tp_get_current_user();
            $user = null;
            if($_user  instanceof TP_User ){
                $user = $_user;
            }
            return $user->exists();
        }//1086
        /**
         * @description Checks if a user is logged in, if not it redirects them to the login page.
         */
        protected function _auth_redirect():void{
            $secure = ( $this->_is_ssl() || $this->_force_ssl_admin() );
            $secure = $this->_apply_filters( 'secure_auth_redirect', $secure );
            if ( $secure && ! $this->_is_ssl() && false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) ) {
                if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
                    $this->_tp_redirect( $this->_set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
                    exit;
                }
                $this->_tp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                exit;
            }
            $scheme = $this->_apply_filters( 'auth_redirect_scheme', '' );
            $user_id = $this->_tp_validate_auth_cookie( '', $scheme );
            if ( $user_id ) {
                $this->_do_action( 'auth_redirect', $user_id );
                if ( ! $secure && $this->_get_user_option( 'use_ssl', $user_id ) && false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) ) {
                    if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
                        $this->_tp_redirect( $this->_set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
                        exit;
                    }
                    $this->_tp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                    exit;
                }
                return;
            }
            $this->_nocache_headers();
            //todo
            $redirect = ( strpos( $_SERVER['REQUEST_URI'], '/options.php' ) && $this->_tp_get_referer() ) ? $this->_tp_get_referer() : $this->_set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $login_url = $this->_tp_login_url( $redirect, true );
            $this->_tp_redirect( $login_url );
            exit;
        }//1104
        /**
         * @description Ensures intent by verifying that a user,
         * @description . was referred from another admin page with the correct security nonce.
         * @param int $action
         * @param string $query_arg
         * @return bool
         */
        protected function _check_admin_referer( $action = -1, $query_arg = '_tp_nonce' ):bool{
            if ( -1 === $action )
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'You should specify an action to be verified by using the first parameter.' ), '0.0.1' );
            $admin_url = strtolower( $this->_admin_url() );
            $referer  = strtolower( $this->_tp_get_referer() );
            $result   = isset( $_REQUEST[ $query_arg ] ) ? $this->_tp_verify_nonce( $_REQUEST[ $query_arg ], $action ) : false;
            $this->_do_action( 'check_admin_referer', $action, $result );
            if ( ! $result && ! ( -1 === $action && strpos( $referer, $admin_url ) === 0 ) ) {
                $this->_tp_nonce_ays( $action );
                die();
            }
            return $result;
        }//1192
    }
}else die;