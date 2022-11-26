<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 09:01
 */
namespace TP_Admin\Traits\AdminFile;
use TP_Admin\Libs\AdmFilesystem\Adm_Filesystem_Base;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _adm_file_03{
        /**
         * @description Initializes and connects the WordPress Filesystem Abstraction classes.
         * @param bool $args
         * @param bool $context
         * @param bool $allow_relaxed_file_ownership
         * @return bool
         */
        protected function _tp_get_filesystem( $args = false, $context = false, $allow_relaxed_file_ownership = false ):bool{
            new Adm_Filesystem_Base();
            $method = $this->_get_filesystem_method( $args, $context, $allow_relaxed_file_ownership );
            if (!$method ){ return false;}
            $tp_filesystem = TP_NS_ADMIN_FILESYSTEM."Adm_Filesystem_";
            if ( ! class_exists( $tp_filesystem.$method ) ) {
                $abstraction_file = $this->_apply_filters('filesystem_method_file',$tp_filesystem.$method, $method);
                if(!file_exists($abstraction_file)){ return false;}
            }
            $method = $tp_filesystem.$method;
            $this->tp_file_system = new $method( $args );
            if ( ! defined( 'FS_CONNECT_TIMEOUT' ) ) { define( 'FS_CONNECT_TIMEOUT', 30 );}
            if ( ! defined( 'FS_TIMEOUT' ) ) { define( 'FS_TIMEOUT', 30 );}
            if (null !== $this->tp_file_system->errors && $this->_init_error( $this->tp_file_system->errors ) && $this->tp_file_system->errors->has_errors()) {
                return false;
            }
            if ($this->tp_file_system instanceof Adm_Filesystem_Base && ! $this->tp_file_system->connect() ) {
                return false;
            }
            // Set the permission constants if not already set.
            if ( ! defined( 'FS_CH_MOD_DIR' ) ) {
                define( 'FS_CH_MOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
            }
            if ( ! defined( 'FS_CH_MOD_FILE' ) ) {
                define( 'FS_CH_MOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
            }
            return true;
        }//1970
        /**
         * @description Determines which method to use for reading, writing, modifying, or deleting
         * @description files on the filesystem.
         * @param null $args
         * @param null $context
         * @param bool $allow_relaxed_file_ownership
         * @return mixed
         */
        protected function _get_filesystem_method( $args = null, $context = null, $allow_relaxed_file_ownership = false ){
            $method = defined( 'FS_METHOD' ) ? FS_METHOD : false;
            if ( ! $context ) { $context = TP_CONTENT_ASSETS;}
            if(TP_CONTENT_LANG === $context && ! is_dir( $context )){ $context = dirname( $context );}
            //$context = $this->_trailingslashit( $context );
            if ( ! $method ) {
                $temp_file_name = $context . 'temp_write_test_' . str_replace( '.', '_', uniqid( '', true ) );
                $temp_handle    = @fopen( $temp_file_name, 'wb' );
                if ( $temp_handle ) {
                    $tp_file_owner   = false;
                    $temp_file_owner = false;
                    if ( function_exists( 'fileowner' ) ) {
                        $tp_file_owner   = @fileowner( __FILE__ );
                        $temp_file_owner = @fileowner( $temp_file_name );
                    }
                    if ( false !== $tp_file_owner && $tp_file_owner === $temp_file_owner ) {
                        $method = 'direct';
                        $GLOBALS['_tp_filesystem_direct_method'] = 'file_owner';
                    } elseif ( $allow_relaxed_file_ownership ) {
                        $method = 'direct';
                        $GLOBALS['_tp_filesystem_direct_method'] = 'relaxed_ownership';
                    }
                    fclose( $temp_handle );
                    @unlink( $temp_file_name );
                }
            }
            if ( ! $method && isset( $args['connection_type'] ) && 'ssh' === $args['connection_type'] && extension_loaded( 'ssh2' ) ) {
                $method = 'ssh2';
            }
            if ( ! $method && extension_loaded( 'ftp' ) ) { $method = 'ftpext';}
            if ( ! $method && ( extension_loaded( 'sockets' ) || function_exists( 'fsockopen' ) ) ) {
                $method = 'ftpsockets'; // Sockets: Socket extension; PHP Mode: FSockopen / fwrite / fread.
            }
            return $this->_apply_filters( 'filesystem_method', $method, $args, $context, $allow_relaxed_file_ownership );
        }//2058
        /**
         * @description Displays a form to the user to request for their FTP/SSH details in order
         * @description . to connect to the filesystem.
         * @param $form_post
         * @param string $type
         * @param bool $error
         * @param string $context
         * @param null $extra_fields
         * @param bool $allow_relaxed_file_ownership
         * @return string
         */
        protected function _get_request_filesystem_credentials( $form_post, $type = '', $error = false, $context = '', $extra_fields = null, $allow_relaxed_file_ownership = false ):string{
            $req_cred = $this->_apply_filters( 'request_filesystem_credentials', '', $form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership );
            if ( '' !== $req_cred ) { return $req_cred;}
            if ( empty( $type ) ) { $type = $this->_get_filesystem_method( array(), $context, $allow_relaxed_file_ownership );}
            if ( 'direct' === $type ) { return true;}
            if ( is_null( $extra_fields ) ) { $extra_fields = ['version', 'locale'];}
            $credentials = (array) $this->_get_option('ftp_credentials',['hostname' => '', 'username' => '',]);
            $submitted_form = $this->_tp_unslash( $_POST );
            // Verify nonce, or unset submitted form field values on failure.
            if ( ! isset( $_POST['_fs_nonce'] ) || ! $this->_tp_verify_nonce( $_POST['_fs_nonce'], 'filesystem-credentials' ) ) {
                unset( $submitted_form['hostname'],$submitted_form['username'],$submitted_form['password'],$submitted_form['public_key'], $submitted_form['private_key'], $submitted_form['connection_type']);
            }
            $ftp_constants = ['hostname' => 'FTP_HOST','username' => 'FTP_USER','password' => 'FTP_PASS', 'public_key' => 'FTP_PUB_KEY', 'private_key' => 'FTP_PRI_KEY',];
            foreach ( $ftp_constants as $key => $constant ) {
                if ( defined( $constant ) ) { $credentials[ $key ] = constant( $constant );}
                elseif ( ! empty( $submitted_form[ $key ] ) ) { $credentials[ $key ] = $submitted_form[ $key ];}
                elseif (! isset( $credentials[ $key ] )) { $credentials[ $key ] = '';}
            }
            $credentials['hostname'] = preg_replace( '|\w+://|', '', $credentials['hostname'] ); // Strip any schemes off.
            if ( strpos( $credentials['hostname'], ':' ) ) {
                @list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
                if ( ! is_numeric( $credentials['port'] )){ unset( $credentials['port'] );}
            } else { unset( $credentials['port'] );}
            if ( ( defined( 'FTP_SSH' ) && FTP_SSH ) || ( defined( 'FS_METHOD' ) && 'ssh2' === FS_METHOD ) ) {
                $credentials['connection_type'] = 'ssh';
            } elseif ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' === $type ) { // Only the FTP Extension understands SSL.
                $credentials['connection_type'] = 'ftps';
            } elseif ( ! empty( $submitted_form['connection_type'] ) ) {
                $credentials['connection_type'] = $submitted_form['connection_type'];
            } elseif ( ! isset( $credentials['connection_type'] ) ) { // All else fails (and it's not defaulted to something else saved), default to FTP.
                $credentials['connection_type'] = 'ftp';
            }
            if ( ! $error && (( ! empty( $credentials['hostname'] ) && ! empty( $credentials['username'] )&& ! empty( $credentials['password'] ))||
                    ('ssh' === $credentials['connection_type'] && ! empty( $credentials['public_key'] )&& ! empty( $credentials['private_key'] )))
            ){
                $stored_credentials = $credentials;
                if ( ! empty( $stored_credentials['port'] ) ) {  $stored_credentials['hostname'] .= ':' . $stored_credentials['port'];}// Save port as part of hostname to simplify above code.
                unset($stored_credentials['password'], $stored_credentials['port'], $stored_credentials['private_key'], $stored_credentials['public_key']);
                if ( ! $this->_tp_installing() ) { $this->_update_option( 'ftp_credentials', $stored_credentials );}
                return $credentials;
            }
            $hostname        = $credentials['hostname'] ?? '';
            $username        = $credentials['username'] ?? '';
            $public_key      = $credentials['public_key'] ?? '';
            $private_key     = $credentials['private_key'] ?? '';
            $port            = $credentials['port'] ?? '';
            $connection_type = $credentials['connection_type'] ?? '';
            $output  = "<section class='module ftp'>";
            if ( $error && $error instanceof TP_Error) {
                $error_string = $this->__( '<strong>Error</strong>: Could not connect to the server. Please verify the settings are correct.' );
                if ( $this->_init_error( $error ) ) { $error_string = $this->_esc_html( $error->get_error_message() );}
                $output .= "<div class='message'>$error_string</div>";
            }
            $types = [];
            if (function_exists( 'fsockopen' ) ||  extension_loaded( 'ftp' ) || extension_loaded( 'sockets' ) ) {
                $types['ftp'] = $this->__( 'FTP' );}
            if ( extension_loaded( 'ftp' ) ) {  $types['ftps'] = $this->__( 'FTPS (SSL)' );}
            if ( extension_loaded( 'ssh2' ) ) { $types['ssh'] = $this->__( 'SSH2' );}
            $types = $this->_apply_filters( 'fs_ftp_connection_types', $types, $credentials, $type, $error, $context );
            $label_user = $this->__( 'Username' );
            $label_pass = $this->__( 'Password' );
            $label_text = null;
            if ( ( isset( $types['ftp'] ) || isset( $types['ftps'] ) ) ) {
                if ( isset( $types['ssh'] ) ) {
                    $label_text = $this->__( 'Please enter your FTP or SSH credentials to proceed.' );
                    $label_user = $this->__( 'FTP/SSH Username' );
                    $label_pass = $this->__( 'FTP/SSH Password' );
                } else {
                    $label_text = $this->__( 'Please enter your FTP credentials to proceed.' );
                    $label_user = $this->__( 'FTP Username' );
                    $label_pass = $this->__( 'FTP Password' );
                }
            }
            $hostname_value = $this->_esc_attr( $hostname );
            if ( ! empty( $port ) ) { $hostname_value .= ":$port";}
            $password_value = '';
            if ( defined( 'FTP_PASS' ) ) { $password_value = '*****';}
            $ftp_status = true ?: false;
            $disabled = $this->_get_disabled( ( defined( 'FTP_SSL' ) && FTP_SSL ) || ( defined( 'FTP_SSH' ) && FTP_SSH ), $ftp_status );
            $output .= "<form id='' class='' method='post' action='{$this->_esc_url($form_post)}'><ul id='req_filesystem_credentials_form' class='req-filesystem-credentials-form'>";
            $output .= "<li><h2>{$this->__('Connection Information')}</h2></li>";
            $output .= "<li id='request_filesystem_credentials_desc'>";
            $output .= "<dt><p>{$this->__('To perform the requested action, TailoredPress needs to access your web server.')}</p></dt>";
            $output .= "<dt><p>$label_text</p></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label for='hostname' class='field-title'>{$this->__('Hostname')}</label></dt>";
            $output .= "<dd><input id='hostname' name='hostname' class='code' type='text' value='$hostname_value' placeholder='' aria-describedby='request-filesystem-credentials-desc' {$this->_get_disabled( defined( 'FTP_HOST' ) )}/></dd>";
            $output .= "</li><li class='ftp-username'>";
            $output .= "<dt><label for='username' class='field-title'>$label_user</label></dt>";
            $output .= "<dd><input id='username' name='username' class='code' type='text' value='{$this->_esc_attr( $username)}' {$this->_get_disabled( defined( 'FTP_USER' ) )}/></dd>";
            $output .= "</li><li class='ftp-password'>";
            $output .= "<dt><label for='password' class='field-title'>$label_pass</label></dt>";
            $output .= "<dd><input id='password' name='password' type='password' class='code' value='$password_value'/></dd>";
            $output .= "</li><li><fieldset><legend>{$this->__('Connection Type')}</legend><ul>";
            foreach ( $types as $name => $text ){
                $output .= "<li><dd><input id='{$this->_esc_attr($name)}' name='connection_type' type='radio' value='{$this->_esc_attr($name)}' {$this->_get_checked( $name, $connection_type )} $disabled/></dd>";
                $output .= "<dt><label for='{$this->_esc_attr($name)}'>$text</label></dt></li>";
            }
            $output .= "</ul></fieldset></li>";
            if ( isset( $types['ssh'] ) ) {
                $hidden_class = '';
                if ('ssh' !== $connection_type || empty($connection_type)) {
                    $hidden_class = " class='hidden'";
                }
                $output .= "<li><fieldset id='ssh_keys' $hidden_class><legend>{$this->__('Authentication Keys')}</legend><ul><li>";
                $output .= "<dd><input id='public_key' name='public_key' type='text' value='{$this->_esc_attr($public_key)}' aria-describedby='auth-keys-desc' {$this->_get_disabled( defined( 'FTP_PUB_KEY' ) )}/></dd>";
                $output .= "<dt><label for='public_key' class='field-title'>{$this->__('Public Key: ')}</label></dt>";
                $output .= "</li><li>";
                $output .= "<dd><input id='private_key' name='private_key' type='text' value='{$this->_esc_attr($private_key)}' {$this->_get_disabled( defined( 'FTP_PRI_KEY' ) )}/></dd>";
                $output .= "<dt><label for='private_key' class='field-title'>{$this->__('Private Key: ')}</label></dt>";
                $output .= "</li><li>";
                $output .= "<dt><p id='auth_keys_desc' class='field-title'>{$this->__('Enter the location on the server where the public and private keys are located. If a pass phrase is needed, enter that in the password field above.')}</p></dt>";
                $output .= "</li></ul></fieldset></li>";
            }
            $output .= "<li>";
            foreach ( (array) $extra_fields as $field ) {
                $output .= "<input name='{$this->_esc_attr($field)}' type='hidden' value='{$this->_esc_attr($submitted_form[ $field ])}'/><!-- extra fields -->";
            }
            $output .= $this->_tp_get_nonce_field( 'filesystem-credentials', '_fs_nonce', $ftp_status );
            $output .= "</li><li class='request-filesystem-credentials-action-buttons'>";
            $output .= "<dd><button class='button cancel-button' data-js_action='close' type='button'>{$this->__('Cancel')}</button></dd>";
            $output .= "<dd>{$this->_get_submit_button( $this->__( 'Proceed' ), '', 'upgrade', false )}</dd>";
            $output .= "</li></ul></form></section>";
            if(true){
                return $output;
            }
            return false;
        }//2165
        /**
         * @description Prints the filesystem credentials modal when needed.
         * @return string
         */
        protected function _tp_get_request_filesystem_credentials_modal():string{
            $filesystem_method = $this->_get_filesystem_method();
            $filesystem_credentials_are_stored = $this->_get_request_filesystem_credentials( $this->_self_admin_url() );
            $request_filesystem_credentials = ( 'direct' !== $filesystem_method && ! $filesystem_credentials_are_stored );
            if ( ! $request_filesystem_credentials ) {return false;}
            $output  = "<div id='request_filesystem_credentials_dialog' class='notification-dialog-wrap request-filesystem-credentials-dialog'><ul>";
            $output .= "<li class='notification-dialog-background'></li>";
            $output .= "<li><div class='notification' role='dialog' aria-labelledby='request-filesystem-credentials-title' tabindex='0'>";
            $output .= "<div class='request-filesystem-credentials-dialog-content'>";
            $output .= $this->_get_request_filesystem_credentials( $this->_site_url() );
            $output .= "</div></div></li></ul></div><!-- notification-dialog-wrap -->";
            return $output;
        }//2463
        /**
         * @description Attempts to clear the op-code cache for an individual PHP file.
         * @param $filepath
         * @param bool $force
         * @return bool
         */
        protected function _tp_opcache_invalidate( $filepath, $force = false ):bool{
            static $can_invalidate = null;
            if ( null === $can_invalidate && function_exists( 'opcache_invalidate' ) && ( ! ini_get( 'opcache.restrict_api' )
                    || stripos( realpath( $_SERVER['SCRIPT_FILENAME'] ), ini_get( 'opcache.restrict_api' ) ) === 0 ) ) {
                $can_invalidate = true;
            }
            if ( ! $can_invalidate ) {  return false;}
            if ( '.php' !== strtolower( substr( $filepath, -4 ) ) ) { return false;}
            if ( $this->_apply_filters( 'tp_op_cache_invalidate_file', true, $filepath ) ) {
                return opcache_invalidate( $filepath, $force );
            }
            return false;
        }//2504
    }
}else die;