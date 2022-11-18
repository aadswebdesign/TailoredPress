<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _ms_methods_02{
        use _init_db;
        use _init_error;
        /**
         * @description Sanitizes and validates data required for a user sign-up.
         * @param $user_name
         * @param $user_email
         * @return mixed
         */
        protected function _tp_mu_validate_user_signup( $user_name, $user_email ){
            $this->tpdb = $this->_init_db();
            $errors = $this->_init_error();
            $orig_username = $user_name;
            $user_name     = preg_replace( '/\s+/', '', $this->_sanitize_user( $user_name, true ) );
            if ( $user_name !== $orig_username || preg_match( '/[^a-z0-9]/', $user_name ) ) {
                $errors->add( 'user_name', $this->__( 'User names can only contain lowercase letters (a-z) and numbers.' ) );
                $user_name = $orig_username;
            }
            $user_email = $this->_sanitize_email( $user_email );
            if ( empty( $user_name ) )
                $errors->add( 'user_name', $this->__( 'Please enter a username.' ) );
            $illegal_names = $this->_get_site_option( 'illegal_names' );
            if ( ! is_array( $illegal_names ) ) {
                $illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
                $this->_add_site_option( 'illegal_names', $illegal_names );
            }
            if ( in_array( $user_name, $illegal_names, true ) )
                $errors->add( 'user_name', $this->__( 'Sorry, that username is not allowed.' ) );
            $illegal_logins = (array) $this->_apply_filters( 'illegal_user_logins', array() );
            if ( in_array( strtolower( $user_name ), array_map( 'strtolower', $illegal_logins ), true ) )
                $errors->add( 'user_name', $this->__( 'Sorry, that username is not allowed.' ) );
            if ( ! $this->_is_email( $user_email ) )
                $errors->add( 'user_email', $this->__( 'Please enter a valid email address.' ) );
            elseif ( $this->_is_email_address_unsafe( $user_email ) )
                $errors->add( 'user_email', $this->__( 'You cannot use that email address to signup. There are problems with them blocking some emails from WordPress. Please use another email provider.' ) );
            if ( strlen( $user_name ) < 4 ) $errors->add( 'user_name', $this->__( 'Username must be at least 4 characters.' ) );
            if ( strlen( $user_name ) > 60 ) $errors->add( 'user_name', $this->__( 'Username may not be longer than 60 characters.' ) );
            if ( preg_match( '/^\d*$/', $user_name ) )  $errors->add( 'user_name', $this->__( 'Sorry, user names must have letters too!' ) );
            $limited_email_domains = $this->_get_site_option( 'limited_email_domains' );
            if ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) ) {
                $limited_email_domains = array_map( 'strtolower', $limited_email_domains );
                $emaildomain           = strtolower( substr( $user_email, 1 + strpos( $user_email, '@' ) ) );
                if ( ! in_array( $emaildomain, $limited_email_domains, true ) )
                    $errors->add( 'user_email', $this->__( 'Sorry, that email address is not allowed!' ) );
            }
            if ( $this->_username_exists( $user_name ) ) $errors->add( 'user_name', $this->__( 'Sorry, that username already exists!' ) );
            if ( $this->_email_exists( $user_email ) )
                $errors->add('user_email',
                    sprintf( $this->__( "<strong>Error:</strong> This email address is already registered. <a href='%s'>Log in</a> with this address or choose another one." ), $this->_tp_login_url() )); /* translators: %s: Link to the login page. */
            $signup = $this->tpdb->get_row( $this->tpdb->prepare(TP_SELECT . " * FROM $this->tpdb->signups WHERE user_login = %s", $user_name ) );
            if ( $signup instanceof \stdClass ) {
                $registered_at = $this->_mysql2date( 'U', $signup->registered );
                $now           = time();
                $diff          = $now - $registered_at;
                // If registered more than two days ago, cancel registration and let this signup go through.
                if ( $diff > 2 * DAY_IN_SECONDS ) $this->tpdb->delete( $this->tpdb->signups, array( 'user_login' => $user_name ) );
                else $errors->add( 'user_name', $this->__( 'That username is currently reserved but may be available in a couple of days.' ) );
            }
            $signup = $this->tpdb->get_row( $this->tpdb->prepare(TP_SELECT . " * FROM $this->tpdb->signups WHERE user_email = %s", $user_email ) );
            if ( $signup instanceof \stdClass ) {
                $diff = time() - $this->_mysql2date( 'U', $signup->registered );
                // If registered more than two days ago, cancel registration and let this signup go through.
                if ( $diff > 2 * DAY_IN_SECONDS ) $this->tpdb->delete( $this->tpdb->signups, array( 'user_email' => $user_email ) );
                else $errors->add( 'user_email', $this->__( 'That email address has already been used. Please check your inbox for an activation email. It will become available in a couple of days if you do nothing.' ) );
            }
            $result = ['user_name' => $user_name,'orig_username' => $orig_username,'user_email' => $user_email,'errors' => $errors,];
            return $this->_apply_filters( 'tp_mu_validate_user_signup', $result );
        }//471
        /**
         * @description Processes new site registrations.
         * @param $blogname
         * @param $blog_title
         * @param TP_User $user
         * @return mixed
         */
        protected function _tp_mu_validate_blog_signup( $blogname, $blog_title,TP_User $user = null ){
            $this->tpdb = $this->_init_db();
            $domain = $this->tp_domain;
            $current_network = $this->_get_network();
            $base            = $current_network->path;
            $blog_title = strip_tags( $blog_title );
            $errors        = $this->_init_error();
            $illegal_names = $this->_get_site_option( 'illegal_names' );
            if ( false === $illegal_names ) {
                $illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
                $this->_add_site_option( 'illegal_names', $illegal_names );
            }
            if ( ! $this->_is_subdomain_install() )
                $illegal_names = array_merge( $illegal_names, $this->_get_subdirectory_reserved_names() );
            if ( empty( $blogname ) )
                $errors->add( 'blogname', $this->__( 'Please enter a site name.' ) );
            if ( preg_match( '/[^a-z0-9]+/', $blogname ) )
                $errors->add( 'blogname', $this->__( 'Site names can only contain lowercase letters (a-z) and numbers.' ) );
            if ( in_array( $blogname, $illegal_names, true ) )
                $errors->add( 'blogname', $this->__( 'That name is not allowed.' ) );
            $minimum_site_name_length = $this->_apply_filters( 'minimum_site_name_length', 4 );
            if ( strlen( $blogname ) < $minimum_site_name_length )
                $errors->add( 'blogname', sprintf( $this->_n( 'Site name must be at least %s character.', 'Site name must be at least %s characters.', $minimum_site_name_length ), $this->_number_format_i18n( $minimum_site_name_length ) ) );
            if ( ! $this->_is_subdomain_install() && $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . ' post_name FROM ' . $this->tpdb->get_blog_prefix( $current_network->site_id ) . "posts WHERE post_type = 'page' AND post_name = %s", $blogname ) ) )
                $errors->add( 'blogname', $this->__( 'Sorry, you may not use that site name.' ) );
            if ( preg_match( '/^\d*$/', $blogname ) ) $errors->add( 'blogname', $this->__( 'Sorry, site names must have letters too!' ) );
            $blogname = $this->_apply_filters( 'newblogname', $blogname );
            $blog_title = $this->_tp_unslash( $blog_title );
            if ( empty( $blog_title ) ) $errors->add( 'blog_title', $this->__( 'Please enter a site title.' ) );
            if ( $this->_is_subdomain_install() ) {
                $mydomain = $blogname . '.' . preg_replace( '|^www\.|', '', $domain );
                $path     = $base;
            } else {
                $mydomain = $domain;
                $path     = $base . $blogname . '/';
            }
            if ( $this->_domain_exists( $mydomain, $path, $current_network->id ) )
                $errors->add( 'blogname', $this->__( 'Sorry, that site already exists!' ) );
            if ( $this->_username_exists( $blogname ) ) {
                if ( ! is_object((object) $user ) || ( is_object((object) $user ) && ($user !== null && $user->user_login !== $blogname ) ) )
                    $errors->add( 'blogname', $this->__( 'Sorry, that site is reserved!' ) );
            }
            // Has someone already signed up for this domain?
            // TODO: Check email too?
            $signup = $this->tpdb->get_row( $this->tpdb->prepare(  TP_SELECT . " * FROM $this->tpdb->signups WHERE domain = %s AND path = %s", $mydomain, $path ) );
            if ( $signup instanceof \stdClass ) {
                $diff = time() - $this->_mysql2date( 'U', $signup->registered );
                if ( $diff > 2 * DAY_IN_SECONDS ) {
                    $this->tpdb->delete(
                        $this->tpdb->signups,
                        ['domain' => $mydomain,'path' => $path,]
                    );
                } else $errors->add( 'blogname', $this->__( 'That site is currently reserved but may be available in a couple days.' ) );
            }
            $result = ['domain' => $mydomain,'path' => $path,'blogname' => $blogname,'blog_title' => $blog_title,'user' => $user,'errors' => $errors,];
            return $this->_apply_filters( 'tp_mu_validate_blog_signup', $result );
        }//636
        /**
         * @description Records site signup information for future activation.
         * @param $domain
         * @param $path
         * @param $title
         * @param $user
         * @param $user_email
         * @param array $meta
         */
        protected function _tp_mu_signup_blog( $domain, $path, $title, $user, $user_email, ...$meta):void{
            $this->tpdb = $this->_init_db();
            $key = substr( md5( time() . $this->_tp_rand() . $domain ), 0, 16 );
            $meta = $this->_apply_filters( 'signup_site_meta', $meta, $domain, $path, $title, $user, $user_email, $key );
            $this->tpdb->insert(
                $this->tpdb->signups,
                ['domain' => $domain,'path' => $path,'title' => $title,'user_login' => $user,'user_email' => $user_email,
                    'registered' => $this->_current_time( 'mysql', true ),'activation_key' => $key,'meta' => serialize( $meta ),]
            );
            $this->_do_action( 'after_signup_site', $domain, $path, $title, $user, $user_email, $key, $meta );
        }//792
        /**
         * @description Records user signup information for future activation.
         * @param $user
         * @param $user_email
         * @param array ...$meta
         */
        protected function _tp_mu_signup_user( $user, $user_email, ...$meta):void{
            $this->tpdb = $this->_init_db();
            $user       = preg_replace( '/\s+/', '', $this->_sanitize_user( $user, true ) );
            $user_email = $this->_sanitize_email( $user_email );
            $key        = substr( md5( time() . $this->_tp_rand() . $user_email ), 0, 16 );
            $meta = $this->_apply_filters( 'signup_user_meta', $meta, $user, $user_email, $key );
            $this->tpdb->insert(
                $this->tpdb->signups,
                ['domain' => '','path' => '','title' => '','user_login' => $user,'user_email' => $user_email,
                    'registered' => $this->_current_time( 'mysql', true ),'activation_key' => $key,'meta' => serialize( $meta ),]
            );
            $this->_do_action( 'after_signup_user', $user, $user_email, $key, $meta );
        }//858
        /**
         * @description Sends a confirmation request email to a user when they sign up for a new site. The new site will not become active
         * @description . until the confirmation link is clicked.
         * @param $domain
         * @param $path
         * @param $title
         * @param $user_login
         * @param $user_email
         * @param $key
         * @param array $meta
         * @return bool
         */
        protected function _tp_mu_signup_blog_notification( $domain, $path, $title, $user_login, $user_email, $key, $meta = [] ):bool{
            if ( ! $this->_apply_filters( 'tp_mu_signup_blog_notification', $domain, $path, $title, $user_login, $user_email, $key, $meta ) )
                return false;
            if ( ! $this->_is_subdomain_install() || $this->_get_current_network_id() !== 1 )
                $activate_url = $this->_network_site_url( "tp-activate.php?key=$key" );
            else $activate_url = "http://{$domain}{$path}tp-activate.php?key=$key"; // @todo Use *_url() API.
            $activate_url = $this->_esc_url( $activate_url );
            $admin_email = $this->_get_site_option( 'admin_email' );
            if ( '' === $admin_email ) $admin_email = 'support@' . $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST );
            $from_name       = ( '' !== $this->_get_site_option( 'site_name' ) ) ? $this->_esc_html( $this->_get_site_option( 'site_name' ) ) : 'WordPress';
            $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . $this->_get_option( 'blog_charset' ) . "\"\n";
            $user            = $this->_get_user_by( 'login', $user_login );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user ) );
            $message = sprintf($this->_apply_filters("tp_mu_signup_blog_notification_email",
                $this->__( "To activate your site, please click the following link:\n\n%1\$s\n\nAfter you activate, you will receive *another email* with your login.\n\nAfter you activate, you can visit your site here:\n\n%2\$s" ),
                $domain,$path, $title,$user_login,$user_email,$key,$meta),$activate_url,$this->_esc_url( "http://{$domain}{$path}" ), $key);
            $subject = sprintf($this->_apply_filters("tp_mu_signup_blog_notification_subject",$this->_x( '[%1$s] Activate %2$s', 'New site notification email subject' ),
                $domain,$path,$title,$user_login,$user_email,$key,$meta),$from_name, $this->_esc_url( 'http://' . $domain . $path ));
            $this->_tp_mail( $user_email, $this->_tp_special_chars_decode( $subject ), $message, $message_headers );
            if ( $switched_locale ) $this->_restore_previous_locale();
            return true;
            }//932
        /**
         * @description Sends a confirmation request email to a user when they sign up for a new user account (without signing up for a site
         *  @description . at the same time). The user account will not become active until the confirmation link is clicked.
         * @param $user_login
         * @param $user_email
         * @param $key
         * @param array $meta
         * @return bool
         */
        protected function _tp_mu_signup_user_notification( $user_login, $user_email, $key, $meta = [] ):bool{
            if ( ! $this->_apply_filters( 'tp_mu_signup_user_notification', $user_login, $user_email, $key, $meta ) )
                return false;
            $user            = $this->_get_user_by( 'login', $user_login );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user ) );
            $admin_email = $this->_get_site_option( 'admin_email' );
            if ( '' === $admin_email ) $admin_email = 'support@' . $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST );
            $from_name       = ( '' !== $this->_get_site_option( 'site_name' ) ) ? $this->_esc_html( $this->_get_site_option( 'site_name' ) ) : 'TailoredPress';
            $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . $this->_get_option( 'blog_charset' ) . "\"\n";
            $message  = sprintf( $this->_apply_filters('tp_mu_signup_user_notification_email',
                    $this->__( "To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login." ),
                    $user_login,$user_email,$key,$meta),$this->_site_url( "tp-activate.php?key=$key" ));/* translators: New user notification email. %s: Activation URL. */
            $subject = sprintf($this->_apply_filters('tp_mu_signup_user_notification_subject',
                $this->_x( '[%1$s] Activate %2$s', 'New user notification email subject' ), $user_login,$user_email, $key, $meta), $from_name,$user_login);
            $this->_tp_mail( $user_email, $this->_tp_special_chars_decode( $subject ), $message, $message_headers );
            /* translators: New user notification email subject. 1: Network title, 2: New user login. */
            if ( $switched_locale ) $this->_restore_previous_locale();
            return true;
        }//1067
        /**
         * @description Activates a signup.
         * @param $key
         * @return array|string|TP_Error
         */
        protected function _tp_mu_activate_signup( $key ){
            $this->tpdb = $this->_init_db();
            $_signup = $this->tpdb->get_row( $this->tpdb->prepare(TP_SELECT .  " * FROM $this->tpdb->signups WHERE activation_key = %s", $key ) );
            $signup = null;
            if($_signup instanceof \stdClass ){$signup =  $_signup;}
            if ( empty( $signup ) ) return new TP_Error( 'invalid_key', $this->__( 'Invalid activation key.' ) );
            if ( $signup->active ) {
                if ( empty( $signup->domain ) ) return new TP_Error( 'already_active', $this->__( 'The user is already active.' ), $signup );
                else return new TP_Error( 'already_active', $this->__( 'The site is already active.' ), $signup );
            }
            $meta     = $this->_maybe_unserialize( $signup->meta );
            $password = $this->_tp_generate_password( 12, false );
            $user_id = $this->_username_exists( $signup->user_login );
            if ( ! $user_id ) {
                $user_id = $this->_tp_mu_create_user( $signup->user_login, $password, $signup->user_email );
            } else $user_already_exists = true;
            if ( ! $user_id ) return new TP_Error( 'create_user', $this->__( 'Could not create user' ), $signup );
            $now = $this->_current_time( 'mysql', true );
            if ( empty( $signup->domain ) ) {
                $this->tpdb->update($this->tpdb->signups,[ 'active' => 1,'activated' => $now,],['activation_key' => $key]);
                if ( isset( $user_already_exists ) )
                    return new TP_Error( 'user_already_exists', $this->__( 'That username is already activated.' ), $signup );
                $this->_do_action( 'tp_mu_activate_user', $user_id, $password, $meta );
                return ['user_id' => $user_id,'password' => $password,'meta'=> $meta,];
            }
            $blog_id = $this->_tp_mu_create_blog( $signup->domain, $signup->path, $signup->title, $user_id, $meta, $this->_get_current_network_id() );
            // TODO: What to do if we create a user but cannot create a blog?
            if ( $this->_init_error( $blog_id ) ) {
                if ( 'blog_taken' === $blog_id->get_error_code() ) {
                    $blog_id->add_data( $signup );
                    $this->tpdb->update($this->tpdb->signups,['active'=> 1, 'activated' => $now,],[ 'activation_key' => $key]);
                }
                return $blog_id;
            }
            $this->tpdb->update( $this->tpdb->signups, ['active' => 1,'activated' => $now,],['activation_key' => $key]);
            $this->_do_action( 'tp_mu_activate_blog', $blog_id, $user_id, $password, $signup->title, $meta );
            return ['blog_id'=> $blog_id, 'user_id'=> $user_id,'password' => $password,'title'=> $signup->title, 'meta'=> $meta,];
        }//1169
        /**
         * @description Deletes an associated signup entry when a user is deleted from the database.
         * @param $id
         * @param $reassign
         * @param $user
         */
        protected function _tp_delete_signup_on_user_delete( $id, $reassign, $user ):void{
            $this->tpdb = $this->_init_db();
            $this->tpdb->delete( $this->tpdb->signups, ['user_login' => $user->user_login, 'user_id' => $id, 'user_re_assign' => $reassign] );
        }//1298
        /**
         * @description Creates a user.
         * @param $user_name
         * @param $password
         * @param $email
         * @return bool
         */
        protected function _tp_mu_create_user( $user_name, $password, $email ):bool{
            $user_name = preg_replace( '/\s+/', '', $this->_sanitize_user( $user_name, true ) );
            $user_id = $this->_tp_create_user( $user_name, $password, $email );
            if ( $this->_init_error( $user_id ) ) return false;
            $this->_delete_user_option( $user_id, 'capabilities' );
            $this->_delete_user_option( $user_id, 'user_level' );
            $this->_do_action( 'tp_mu_new_user', $user_id );
            return $user_id;
        }//1319
        /**
         * @description Creates a site.
         * @param $domain
         * @param $path
         * @param $title
         * @param $user_id
         * @param int $network_id
         * @param array ...$options
         * @return TP_Error
         */
        protected function _tp_mu_create_blog( $domain, $path, $title, $user_id, $network_id = 1, ...$options):TP_Error{
            $defaults = ['public' => 0,];
            $options  = $this->_tp_parse_args( $options, $defaults );
            $title   = strip_tags( $title );
            $user_id = (int) $user_id;
            if ( $this->_domain_exists( $domain, $path, $network_id ) )
                return new TP_Error( 'blog_taken', $this->__( 'Sorry, that site already exists!' ) );
            if ( ! $this->_tp_installing() ) $this->_tp_installing( true );
            $allowed_data_fields = array( 'public', 'archived', 'mature', 'spam', 'deleted', 'lang_id' );
            $site_data = array_merge(
                ['domain' => $domain, 'path' => $path,'network_id' => $network_id,],
                array_intersect_key( $options, array_flip( $allowed_data_fields ) )
            );
            $site_initialization_data = ['title' => $title, 'user_id' => $user_id, 'options' => array_diff_key( $options, array_flip( $allowed_data_fields ) ),];
            $blog_id = $this->_tp_insert_site( array_merge( $site_data, $site_initialization_data ) );
            if ( $this->_init_error( $blog_id ) ) return $blog_id;
            $this->_tp_cache_set( 'last_changed', microtime(), 'sites' );
            return $blog_id;
        }//1369
    }
}else die;