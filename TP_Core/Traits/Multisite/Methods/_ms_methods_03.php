<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_site;

if(ABSPATH){
    trait _ms_methods_03{
        use _init_db;
        use _init_site;
        /**
         * @description Notifies the network admin that a new site has been activated.
         * @param $blog_id
         * @return bool
         */
        protected function _new_blog_notify_site_admin( $blog_id):bool{
            if ( is_object( $blog_id ) ) $blog_id = $blog_id->blog_id;
            if ( 'yes' !== $this->_get_site_option( 'registration_notification' ) )
                return false;
            $email = $this->_get_site_option( 'admin_email' );
            if ( $this->_is_email( $email ) === false ) return false;
            $options_site_url = $this->_esc_url( $this->_network_admin_url( 'settings.php' ) );
            $this->_switch_to_blog( $blog_id );
            $blogname = $this->_get_option( 'blogname' );
            $siteurl  = $this->_site_url();
            $this->_restore_current_blog();
            $msg = sprintf($this->__("New Site: %1\$s URL: %2\$s Disable these notifications: %4\$s"),
                $blogname,$siteurl,$this->_tp_unslash( $_SERVER['REMOTE_ADDR'] ),$options_site_url);
            $msg = $this->_apply_filters( 'newblog_notify_siteadmin', $msg, $blog_id );
            $this->_tp_mail( $email, sprintf( $this->__( 'New Site Registration: %s' ), $siteurl ), $msg );
            return true;
        }//1429
        /**
         * @description Notifies the network admin that a new user has been activated.
         * @param $user_id
         * @return bool
         */
        protected function _new_user_notify_site_admin( $user_id ):bool{
            if ( 'yes' !== $this->_get_site_option( 'registration_notification' ) )
                return false;
            $email = $this->_get_site_option( 'admin_email' );
            if ( $this->_is_email( $email ) === false ) return false;
            $user = $this->_get_user_data( $user_id );
            $options_site_url = $this->_esc_url( $this->_network_admin_url( 'settings.php' ) );//todo
            $msg = sprintf($this->__("New User: %1\$s Remote IP address: %2\$s Disable these notifications: %3\$s"),
                $user->user_login,$this->_tp_unslash( $_SERVER['REMOTE_ADDR'] ),$options_site_url);
            $msg = $this->_apply_filters( 'newuser_notify_siteadmin', $msg, $user );
            $this->_tp_mail( $email, sprintf( $this->__( 'New User Registration: %s' ), $user->user_login ), $msg );
            return true;
        }//1494
        /**
         * @description Checks whether a site name is already taken.
         * @param $domain
         * @param $path
         * @param int $network_id
         * @return mixed
         */
        protected function _domain_exists( $domain, $path, $network_id = 1 ){
            $path = $this->_trailingslashit( $path );
            $args = ['network_id' => $network_id,'domain' => $domain,'path' => $path,'fields' => 'ids','number' => 1,'update_site_meta_cache' => false,];
            $result = $this->_get_sites( $args );
            $result = array_shift( $result );
            return $this->_apply_filters( 'domain_exists', $result, $domain, $path, $network_id );
        }//1555
        /**
         * @description Notifies the site administrator that their site activation was successful.
         * @param $blog_id
         * @param $user_id
         * @param $password
         * @param $title
         * @param array $meta
         * @return bool
         */
        protected function _tp_mu_welcome_notification( $blog_id, $user_id, $password, $title, ...$meta):bool{
            $current_network = $this->_get_network();
            if ( ! $this->_apply_filters( 'tp_mu_welcome_notification', $blog_id, $user_id, $password, $title, $meta ) )
                return false;
            $user = $this->_get_user_data( $user_id );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user ) );
            $welcome_email = $this->_get_site_option( 'welcome_email' );
            if ( false === $welcome_email ) $welcome_email = $this->__("Howdy USER_NAME, 
            Your new SITE_NAME site has been successfully set up at: BLOG_URL, 
            You can log in to the administrator account with the following information:
            Username: USER_NAME, Password: PASSWORD, Log in here: BLOG_URLwp-login.php
            We hope you enjoy your new site. Thanks! --The Team @ SITE_NAME");
            $url = $this->_get_blog_address_by_id( $blog_id );
            $welcome_email = str_replace( 'SITE_NAME', $current_network->site_name, $welcome_email );
            $welcome_email .= str_replace( 'BLOG_TITLE', $title, $welcome_email );
            $welcome_email .= str_replace( 'BLOG_URL', $url, $welcome_email );
            $welcome_email .= str_replace( 'USER_NAME', $user->user_login, $welcome_email );
            $welcome_email .= str_replace( 'PASSWORD', $password, $welcome_email );
            $welcome_email = $this->_apply_filters( 'update_welcome_email', $welcome_email, $blog_id, $user_id, $password, $title, $meta );
            $admin_email = $this->_get_site_option( 'admin_email' );
            if ( '' === $admin_email ) $admin_email = 'support@' . $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST );
            $from_name       = ( '' !== $this->_get_site_option( 'site_name' ) ) ? $this->_esc_html( $this->_get_site_option( 'site_name' ) ) : 'WordPress';
            $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . $this->_get_option( 'blog_charset' ) . "\"\n";
            $message         = $welcome_email;
            if ( empty( $current_network->site_name ) ) $current_network->site_name = 'TailoredPress';
            $subject = $this->__( 'New %1$s Site: %2$s' );
            $subject = $this->_apply_filters( 'update_welcome_subject', sprintf( $subject, $current_network->site_name, $this->_tp_unslash( $title ) ) );
            $this->_tp_mail( $user->user_email, $this->_tp_special_chars_decode( $subject ), $message, $message_headers );
            if ( $switched_locale )  $this->_restore_previous_locale();
            return true;
        }//1601
        /**
         * @description Notifies the Multisite network administrator that a new site was created.
         * @param $site_id
         * @param $user_id
         * @return bool
         */
        protected function _tp_mu_new_site_admin_notification( $site_id, $user_id ):bool{
            $site  = $this->_get_site( $site_id );
            $user  = $this->_get_user_data( $user_id );
            $email = $this->_get_site_option( 'admin_email' );
            if ( ! $site || ! $user || ! $email ) return false;
            if ( ! $this->_apply_filters( 'send_new_site_email', true, $site, $user ) )
                return false;
            $switched_locale = null;
            $network_admin   = $this->_get_user_by( 'email', $email );
            if ( $network_admin ) $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $network_admin ) );
            else $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
            $subject = sprintf( $this->__( '[%s] New Site Created' ), $this->_get_network()->site_name);
            $message = sprintf($this->__("New site created by %1\$s Address: %2\$s Name: %3\$s"),
                $user->user_login,$this->_get_site_url( $site->id ),$this->_get_blog_option( $site->id, 'blogname' ));
            $header = sprintf('From: "%1$s" <%2$s>', $this->_x( 'Site Admin', 'email "From" field' ), $email);
            $new_site_email = ['to' => $email,'subject' => $subject,'message' => $message,'headers' => $header,];
            $new_site_email = $this->_apply_filters( 'new_site_email', $new_site_email, $site, $user );
            $this->_tp_mail($new_site_email['to'], $this->_tp_special_chars_decode( $new_site_email['subject'] ),
                $new_site_email['message'],$new_site_email['headers']);
            if ( $switched_locale ) $this->_restore_previous_locale();
            return true;
        }//1718
        /**
         * @description Notifies a user that their account activation has been successful.
         * @param $user_id
         * @param $password
         * @param array ...$meta
         * @return bool
         */
        protected function _tp_mu_welcome_user_notification( $user_id, $password, ...$meta):bool{
            $current_network = $this->_get_network();
            if ( ! $this->_apply_filters( 'tp_mu_welcome_user_notification', $user_id, $password, $meta ) )
                return false;
            $welcome_email = $this->_get_site_option( 'welcome_user_email' );
            $user = $this->_get_user_data( $user_id );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user ) );
            $welcome_email = $this->_apply_filters( 'update_welcome_user_email', $welcome_email, $user_id, $password, $meta );
            $welcome_email = str_replace( 'SITE_NAME', $current_network->site_name, $welcome_email );
            $welcome_email .= str_replace( 'USER_NAME', $user->user_login, $welcome_email );
            $welcome_email .= str_replace( 'PASSWORD', $password, $welcome_email );
            $welcome_email .= str_replace( 'LOGIN_LINK', $this->_tp_login_url(), $welcome_email );
            $admin_email = $this->_get_site_option( 'admin_email' );
            if ( '' === $admin_email )
                $admin_email = 'support@' . $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST );
            $from_name       = ( '' !== $this->_get_site_option( 'site_name' ) ) ? $this->_esc_html( $this->_get_site_option( 'site_name' ) ) : 'TailoredPress';
            $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . $this->_get_option( 'blog_charset' ) . "\"\n";
            $message         = $welcome_email;
            if ( empty( $current_network->site_name ) )  $current_network->site_name = 'TailoredPress';
            $subject = $this->__( 'New %1$s User: %2$s' );
            $subject = $this->_apply_filters( 'update_welcome_user_subject', sprintf( $subject, $current_network->site_name, $user->user_login ) );
            $this->_tp_mail( $user->user_email, $this->_tp_special_chars_decode( $subject ), $message, $message_headers );
            if ( $switched_locale ) $this->_restore_previous_locale();
            return true;
        }//1834
        /**
         * @description Gets the current network.
         * @return mixed
         */
        protected function _get_current_site(){
            return $this->_init_current_site();
        }//1925
        /**
         * @description Gets a user's most recent post.
         * @param $user_id
         * @return array
         */
        protected function _get_most_recent_post_of_user( $user_id ):array{
            $this->tpdb = $this->_init_db();
            $user_blogs       = $this->_get_blogs_of_user( (int) $user_id );
            $most_recent_post = [];
            foreach ( (array) $user_blogs as $blog ) {
                $prefix      = $this->tpdb->get_blog_prefix( $blog->userblog_id );
                $recent_post = $this->tpdb->get_row( $this->tpdb->prepare(TP_SELECT . " ID, post_date_gmt FROM {$prefix}posts WHERE post_author = %d AND post_type = 'post' AND post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 1", $user_id ), ARRAY_A );
                if ( isset( $recent_post['ID'] ) ) {
                    $post_gmt_ts = strtotime( $recent_post['post_date_gmt'] );
                    if ( ! isset( $most_recent_post['post_gmt_ts'] ) || ( $post_gmt_ts > $most_recent_post['post_gmt_ts'] ) )
                        $most_recent_post = ['blog_id' => $blog->userblog_id,'post_id' => $recent_post['ID'],
                            'post_date_gmt' => $recent_post['post_date_gmt'],'post_gmt_ts' => $post_gmt_ts,];
                }
            }
            return $most_recent_post;
        }//1943
        /**
         * @description Checks an array of MIME types against a list of allowed types.
         * @param $mimes
         * @return array
         */
        protected function _check_upload_mimes( $mimes ):array{
            $site_exts  = explode( ' ', $this->_get_site_option( 'upload_file_types', 'webp jpg jpeg png gif' ) );
            $site_mimes = []; //webp added
            foreach ( $site_exts as $ext ) {
                foreach ( $mimes as $ext_pattern => $mime ) {
                    if ( '' !== $ext && false !== strpos( $ext_pattern, $ext ) )
                        $site_mimes[ $ext_pattern ] = $mime;
                }
            }
            return $site_mimes;
        }//1996
        /**
         * @description Updates a blog's post count.
         */
        protected function _update_posts_count():void{
            $this->tpdb = $this->_init_db();
            $this->_update_option( 'post_count', (int) $this->tpdb->get_var( TP_SELECT . " COUNT(ID) FROM {$this->tpdb->posts} WHERE post_status = 'publish' and post_type = 'post'" ) );
        }//2023
    }
}else die;