<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-8-2022
 * Time: 12:08
 */
namespace TP_Core\BaseLibs;
if(ABSPATH){
    class TP_Activate extends _base_libs{
        public function __construct(){
            define( 'TP_INSTALLING', true );
            new TP_Load();
            new TP_BlogHeader();
            $this->__construct_activate();
        }
        private function __construct_activate():void{
            if ( ! $this->_is_multisite() ) {
                $this->_tp_redirect( $this->_tp_registration_url() );
                die();
            }
            $valid_error_codes = array( 'already_active', 'blog_taken' );
            @list( $activate_path ) = explode( '?', $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) );
            $activate_cookie       = 'tp-activate-' . COOKIE_HASH;
            $key    = '';
            $result = null;
            if ( isset( $_GET['key'], $_POST['key'] ) && $_GET['key'] !== $_POST['key'] ) {
                $this->_tp_die( $this->__( 'A key value mismatch has been detected. Please follow the link provided in your activation email.' ), $this->__( 'An error occurred during the activation' ), 400 );
            } elseif ( ! empty( $_GET['key'] ) ) {$key = $_GET['key'];}
            elseif ( ! empty( $_POST['key'] ) ) {$key = $_POST['key']; }
            if ( $key ) {
                $redirect_url = $this->_remove_query_arg( 'key' );
                if ( $this->_remove_query_arg( false ) !== $redirect_url ) {
                    setcookie( $activate_cookie, $key, 0, $activate_path, COOKIE_DOMAIN, $this->_is_ssl(), true );
                    $this->_tp_safe_redirect( $redirect_url );
                    exit;
                }
                $result = $this->_tp_mu_activate_signup( $key );
            }
            if ( null === $result && isset( $_COOKIE[ $activate_cookie ] ) ) {
                $key    = $_COOKIE[ $activate_cookie ];
                $result = $this->_tp_mu_activate_signup( $key );
                setcookie( $activate_cookie, ' ', time() - YEAR_IN_SECONDS, $activate_path, COOKIE_DOMAIN, $this->_is_ssl(), true );
            }
            if ( null === $result || ( $this->_init_error( $result ) && 'invalid_key' === $result->get_error_code() ) ) {
                $this->_status_header( 404 );
            } elseif ( $this->_init_error( $result ) ) {
                $error_code = $result->get_error_code();
                if ( ! in_array( $error_code, $valid_error_codes, true ) ) {$this->_status_header( 400 );}
            }
            $this->_nocache_headers();
            $tp_object_cache = $this->_init_object_cache();
            if ($tp_object_cache instanceof \stdClass && is_object( $tp_object_cache ) ) {
                $tp_object_cache->cache_enabled = false;
            }
            $tp_query = $this->_init_query();
            $tp_query->is_404 = false;
            $this->_do_action( 'activate_header' );
            $do_activate_header = static function(){
                (new self)->_do_action('activate_tp_head');
            };
            $this->_add_action('tp_head', $do_activate_header);
            $tp_mu_activate_stylesheet = static function(){
                ?>
                <style type="text/css">
                    form { margin-top: 2em; }
                    #submit, #key { width: 90%; font-size: 24px; }
                    #language { margin-top: .5em; }
                    .error { background: #f66; }
                    span.h3 { padding: 0 8px; font-size: 1.3em; font-weight: 600; }
                </style>
                <?php //todo adapt this to TailoredPress
            };
            $this->_add_action('tp_head',$tp_mu_activate_stylesheet);
            //$this->_add_action('tp_head',); //todo tp_strict_cross_origin_referrer
            //$this->_add_action('tp_head',); //todo tp_robots_sensitive_page
            $this->_get_header( 'tp-activate' );
            $blog_details = $this->_get_blog_details();
            $signup_form = static function() use($key,$blog_details,$result,$valid_error_codes){
                ?>
                <div id='signup_content' class='wide-column'>
                    <div class='tp-activate-container'>
                    <?php
                    if ( ! $key ) {
                        ?>
                        <h2><?php (new static)->_e( 'Activation Key Required' ); ?></h2>
                        <form id='activate_form' name='activate_form' method='post' action='<?php echo (new self)->_network_site_url( $blog_details->path . 'tp-activate.php' ); ?>'>
                            <ul>
                                <li>
                                    <dt><label for='key'><?php (new self)->_e( 'Activation Key:' ); ?></label></dt>
                                    <dd><input type='text' name='key' id='key' value='' size='50' /></dd>
                                </li>
                                <li>
                                    <dd><input id='submit' type='submit' name='Submit' class='submit activate'  value='<?php (new self)->_esc_attr_e( 'Activate' ); ?>' /></dd>
                                </li>
                            </ul>
                        </form>
                        <?php
                    }else if (in_array( $result->get_error_code(), $valid_error_codes, true )&& (new self)->_init_error( $result ) ) {
                        $signup = $result->get_error_data();
                        ?>
                        <h2><?php (new self)->_e( 'Your account is now active!' ); ?></h2>
                        <?php
                        echo "<p class='lead-in'>";
                        if ( '' === $signup->domain . $signup->path ) {
                            printf(
                            /* translators: 1: Login URL, 2: Username, 3: User email address, 4: Lost password URL. */
                                (new self)->__("Your account has been activated. You may now <a href='%1\$s'>log in</a> to the site using your chosen username of &#8220;%2\$s&#8221;. Please check your email inbox at %3\$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href='%4\$s'>reset your password</a>."),
                                (new self)->_network_site_url( $blog_details->path . 'tp-login.php', 'login' ),
                                $signup->user_login,$signup->user_email,(new self)->_tp_lost_password_url());
                        }else{
                            printf(
                            /* translators: 1: Site URL, 2: Username, 3: User email address, 4: Lost password URL. */
                                (new self)->__("Your site at %1\$s is active. You may now log in to your site using your chosen username of &#8220;%2\$s&#8221;. Please check your email inbox at %3\$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href='%4\$s'>reset your password</a>."),
                                sprintf("<a href='http://%1\$s%2\$s'>%1\$s%2\$s</a>", $signup->domain, $blog_details->path ),
                                $signup->user_login,$signup->user_email,(new self)->_tp_lost_password_url()
                            );
                        }
                        echo '</p>';
                    }elseif( null === $result || (new self)->_init_error( $result )){
                        ?>
                        <h2><?php (new self)->_e( 'An error occurred during the activation' ); ?></h2>
                        <?php if ( (new self)->_init_error( $result ) ) : ?>
                            <p><?php echo $result->get_error_message(); ?></p>
                        <?php endif; ?>
                        <?php
                    }else{
                        $url = isset($result['blog_id']) ? (new self)->_get_home_url((int)$result['blog_id']) : '';
                        $_user = (new self)->_get_user_data((int)$result['user_id']);
                        $user = null;
                        if($_user instanceof \stdClass){ //todo find the real dependency
                            $user = $_user;
                        }
                        ?>
                        <h2><?php (new self)->_e('Your account is now active!'); ?></h2>
                        <div id="signup-welcome">
                            <p><span class='h3'><?php (new self)->_e('Username:'); ?></span> <?php echo $user->user_login; ?>
                            </p>
                            <p><span class='h3'><?php (new self)->_e('Password:'); ?></span> <?php echo $result['password']; ?>
                            </p>
                        </div>
                        <?php
                        if ( $url && (new self)->_network_home_url( '', 'http' ) !== $url ){
                            (new self)->_switch_to_blog( (int) $result['blog_id'] );
                            $login_url = (new self)->_tp_login_url();
                            (new self)->_restore_current_blog();
                            ?>
                            <p class='view'>
                                <?php
                                /* translators: 1: Site URL, 2: Login URL. */
                                printf( (new self)->__("Your account is now activated. <a href='%1\$s'>View your site</a> or <a href='%2\$s'>Log in</a>"), $url, (new self)->_esc_url( $login_url ) );
                                ?>
                            </p>
                            <?php
                        }else{
                            ?>
                            <p class='view'>
                                <?php
                                printf(
                                /* translators: 1: Login URL, 2: Network home URL. */
                                    (new self)->__("Your account is now activated. <a href='%1\$s'>Log in</a> or go back to the <a href='%2\$s'>homepage</a>."),
                                    (new self)->_network_site_url( $blog_details->path . 'tp-login.php', 'login' ),
                                    (new self)->_network_home_url( $blog_details->path )
                                );
                                ?>
                            </p>
                            <?php
                        }
                    }
                    ?>
                    </div>
                </div>
                <script>
                    const key_input = document.getElementById('key');
                    key_input && key_input.focus();
                </script>
                <?php //todo adapt this to TailoredPress
                (new self)->_get_footer( 'tp-activate' );
            };
            //$signup_form
            (new static)->_e($signup_form);

        }




    }
}else{die;}