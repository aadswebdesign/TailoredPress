<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Load;
if(ABSPATH){
    trait _load_01 {
        /**
         * @description Return the HTTP protocol sent by the server.
         * @return string
         */
        protected function _tp_get_server_protocol():string{
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
            if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3' ), true ) ) {
                $protocol = 'HTTP/1.0';
            }
            return $protocol;
        }//15
        /**
         *  @description Fix `$_SERVER` variables for various setups.
         */
        protected function _tp_fix_server_vars():void{
            $default_server_values = [
                'SERVER_SOFTWARE' => '',
                'REQUEST_URI'     => '',
            ];
            $_SERVER = array_merge( $default_server_values, $_SERVER );
            // Fix for IIS when running with PHP ISAPI.
            if ( empty( $_SERVER['REQUEST_URI'] ) || ( 'cgi-fcgi' !== PHP_SAPI && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {
                // IIS Mod-Rewrite.
                if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
                // IIS Isapi_Rewrite.
                elseif ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
                else{
                    // Use ORIG_PATH_INFO if there is no PATH_INFO.
                    if ( ! isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) ) $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
                    // Some IIS + PHP configurations put the script-name in the path-info (no need to append it twice).
                    if ( isset( $_SERVER['PATH_INFO'] ) ) {
                        if ( $_SERVER['PATH_INFO'] === $_SERVER['SCRIPT_NAME'] ) $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
                        else $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
                    }
                    // Append the query string if it exists and isn't null.
                    if ( ! empty( $_SERVER['QUERY_STRING'] ) ) $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
            // Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests.
            if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) === strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
                $_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

            // Fix for Dream host and other PHP as CGI hosts.
            if ( isset( $_SERVER['SCRIPT_NAME'] ) && ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false ) ) unset( $_SERVER['PATH_INFO'] );

            $this->PHP_SELF = $_SERVER['PHP_SELF'];
            if(empty($this->PHP_SELF)){
                $_SERVER['PHP_SELF'] = preg_replace( '/(\?.*)?$/', '', $_SERVER['REQUEST_URI'] );
                $this->PHP_SELF = $_SERVER['PHP_SELF'];
            }
            $this->_tp_populate_basic_auth_from_authorization_header();
        }//32
        /**
         * @description Populates the Basic Auth server details from the Authorization header.
         */
        protected function _tp_populate_basic_auth_from_authorization_header():void{
            // If we don't have anything to pull from, return early.
            if ( ! isset( $_SERVER['HTTP_AUTHORIZATION'] ) && ! isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) return;
            // If either PHP_AUTH key is already set, do nothing.
            if ( isset( $_SERVER['PHP_AUTH_USER'] ) || isset( $_SERVER['PHP_AUTH_PW'] ) ) return;
            // From our prior conditional, one of these must be set.
            $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            // Test to make sure the pattern matches expected.
            if ( ! preg_match( '%^Basic [a-z\d/+]*={0,2}$%i', $header ) ) return;
            // Removing `Basic ` the token would start six characters in.
            $token    = substr( $header, 6 );
            $user_pass = base64_decode( $token );
            @list( $user, $pass ) = explode( ':', $user_pass );
            // Now shove them in the proper keys where we're expecting later on.
            $_SERVER['PHP_AUTH_USER'] = $user;
            $_SERVER['PHP_AUTH_PW']   = $pass;
        }//102
        /**
         * @description Check for the required PHP version, and the MySQL extension or a database drop-in.
         */
        protected function _tp_check_php_mysql_versions():void{
            if ( version_compare( TP_PHP_VERSION, PHP_VERSION, '>' ) ) {
                $protocol = $this->_tp_get_server_protocol();
                header( sprintf( '%s 500 Internal Server Error', $protocol ), true, 500 );
                header( 'Content-Type: text/html; charset=utf-8' );
                //todo future idea is to pass this kind of messages to the console.log
                printf( 'Your server is running PHP version %1$s but TailoredPress %2$s requires at least %3$s.', PHP_VERSION, TP_VERSION, TP_REQUIRED_PHP_VERSION );
                exit( 1 );
            }
            if(extension_loaded('mysql')){
                printf("'mysql' is not supported");
            }elseif(!class_exists(TP_NS_CORE_LIBS .'TP_Db') && ! extension_loaded( 'mysqli' ) && ! extension_loaded( 'mysqlnd' )){
                $this->_tp_load_translations_early();
                $args = [
                    'exit' => false,
                    'code' => 'mysqli_not_found', //todo if I'm right?
                ];
                $this->_tp_die(
                    $this->__( 'Your PHP installation appears to be missing the MySQL extension which is required by TailoredPress.' ),
                    $this->__( 'Requirements Not Met' ),
                    $args
                );
                exit( 1 );
            }
        }//144 deprecated mysql
        /**
         * @description Retrieves the current environment type.
         * @return string
         */
        protected function _tp_get_environment_type():string{
            if ( ! defined( 'TP_RUN_CORE_TESTS' ) && $this->tp_current_env ) return $this->tp_current_env;
            $tp_environments = ['local','development','staging','production',];
            if (defined( 'TP_ENVIRONMENT_TYPES' ) && function_exists([$this,'__deprecated_argument'])){
                if ( function_exists([$this,'__'] ) )$message = sprintf( $this->__( 'The %s constant is no longer supported.' ), 'TP_ENVIRONMENT_TYPES' );
                else $message = sprintf( $this->__( 'The %s constant is no longer supported.' ), 'TP_ENVIRONMENT_TYPES' );
                $this->_deprecated_argument('define()','0.0.1',$message);
            }
            if ( function_exists( 'getenv' ) ) {
                $has_env = getenv( 'TP_ENVIRONMENT_TYPE' );
                if ( false !== $has_env ) $this->tp_current_env = $has_env;
            }
            if ( defined( 'TP_ENVIRONMENT_TYPE' ) ) $this->tp_current_env = TP_ENVIRONMENT_TYPE;
            if ( ! in_array( $this->tp_current_env, $tp_environments, true ) ) $this->tp_current_env = 'production';
            return $this->tp_current_env;
        }//191
        /**
         * @description Don't load all of TailoredPress when handling a favicon.ico request. todo do I want this?
         */
        protected function _tp_favicon_request():void{
            if ( '/favicon.ico' === $_SERVER['REQUEST_URI'] ){
                header( 'Content-Type: image/vnd.microsoft.icon' );
                exit;
            }
        }//250 todo
        protected function _maintenance_class():string{//todo testing
            return $this->_tp_load_class('maintenance_class',TP_NS_CONTENT.'DefaultTheme\\ThemeSrc\\','Maintenance_Index');
        }//added
        /**
         * @description Die with a maintenance message when conditions are met.
         */
        protected function _tp_maintenance():void{
            if ( ! $this->_tp_is_maintenance_mode() )return;
            if($this->_maintenance_class()) die();
            $this->_tp_load_translations_early();
            header( 'Retry-After: 600' ); //todo points to 'jQuery' and should become vanilla js
            $this->_tp_die(
                $this->__( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ),
                $this->__( 'Maintenance' ),
                SERVICE_UNAVAILABLE
            );
        }//266
        /**
         * @description Check if maintenance mode is enabled.
         * @return bool
         */
        protected function _tp_is_maintenance_mode():bool{
            if ( ! $this->_maintenance_class() || $this->_tp_installing() ) return false;
            $this->_maintenance_class();
            if ( ( time() - $this->tp_upgrading ) >= 10 * MINUTE_IN_SECONDS )return false;
            if ( ! $this->_apply_filters( 'enable_maintenance_mode', true, $this->tp_upgrading ) ) return false;
            return true;
        }//303
        /**
         * @description Get the time elapsed so far during this PHP script.
         * @return mixed
         */
        protected function _timer_float(){
            return microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'];
        }//345
        /**
         * @description  Start the TailoredPress micro-timer.
         * @return bool
         */
        protected function _timer_start():bool{
            $this->tp_time_start = microtime( true );
            return true;
        }//360
    }
}else die;