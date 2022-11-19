<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_hasher;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _user_04 {
        use _init_error;
        use _init_db;
        use _init_hasher;
        /**
         * @description Checks whether a username is valid.
         * @param $username
         * @return mixed
         */
        protected function _validate_username( $username ){
            $sanitized = $this->_sanitize_user( $username, true );
            $valid     = ( $sanitized === $username && ! empty( $sanitized ) );
            return $this->_apply_filters( 'validate_username', $valid, $username );
        }//1826
        /**
         * @description Insert a user into the database.
         * @param $userdata
         * @return int|TP_Error
         */
        protected function _tp_insert_user( $userdata ){
            $this->tpdb = $this->_init_db();
            $old_user_data = null;
            $user_id = null;//todo
            if ( $userdata instanceof \stdClass ) {
                $userdata = get_object_vars( $userdata );
            } elseif ( $userdata instanceof TP_User ) $userdata = $userdata->to_array();
            if ( ! empty( $userdata['ID'] ) ) {
                $user_id       = (int) $userdata['ID'];
                $update        = true;
                $old_user_data = $this->_get_user_data( $user_id );
                if ( ! $old_user_data ) return new TP_Error( 'invalid_user_id', $this->__( 'Invalid user ID.' ) );
                $user_pass = ! empty( $userdata['user_pass'] ) ? $userdata['user_pass'] : $old_user_data->user_pass;
            } else {
                $update = false;
                $user_pass = $this->_tp_hash_password( $userdata['user_pass'] );
            }
            $sanitized_user_login = $this->_sanitize_user( $userdata['user_login'], true );
            $pre_user_login = $this->_apply_filters( 'pre_user_login', $sanitized_user_login );
            $user_login = trim( $pre_user_login );
            if ( empty( $user_login ) )
                return new TP_Error( 'empty_user_login', $this->__( 'Cannot create a user with an empty login name.' ) );
            elseif ( mb_strlen( $user_login ) > 60 )
                return new TP_Error( 'user_login_too_long', $this->__( 'Username may not be longer than 60 characters.' ) );
            if ( ! $update && $this->_username_exists( $user_login ) )
                return new TP_Error( 'existing_user_login', $this->__( 'Sorry, that username already exists!' ) );
            $illegal_logins = (array) $this->_apply_filters( 'illegal_user_logins', array() );
            if ( in_array( strtolower( $user_login ), array_map( 'strtolower', $illegal_logins ), true ) )
                return new TP_Error( 'invalid_username', $this->__( 'Sorry, that username is not allowed.' ) );
            if ( ! empty( $userdata['user_nicename'] ) ) {
                $user_nicename = $this->_sanitize_user( $userdata['user_nicename'], true );
                if ( mb_strlen( $user_nicename ) > 50 )
                    return new TP_Error( 'user_nicename_too_long', $this->__( 'Nicename may not be longer than 50 characters.' ) );
            } else $user_nicename = mb_substr( $user_login, 0, 50 );
            $user_nicename = $this->_sanitize_title( $user_nicename );
            $user_nicename = $this->_apply_filters( 'pre_user_nicename', $user_nicename );
            $user_nicename_check = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1", $user_nicename, $user_login ) );
            if ( $user_nicename_check ) {
                $suffix = 2;
                $alt_user_nicename = null;
                while ( $user_nicename_check ) {
                    // user_nicename allows 50 chars. Subtract one for a hyphen, plus the length of the suffix.
                    $base_length         = 49 - mb_strlen( $suffix );
                    $alt_user_nicename   = mb_substr( $user_nicename, 0, $base_length ) . "-$suffix";
                    $user_nicename_check = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1", $alt_user_nicename, $user_login ) );
                    $suffix++;
                }
                $user_nicename = $alt_user_nicename;
            }
            $raw_user_email = empty( $userdata['user_email'] ) ? '' : $userdata['user_email'];
            $user_email = $this->_apply_filters( 'pre_user_email', $raw_user_email );
            if ( ( ! $update || ( ! empty( $old_user_data )&& ! defined( 'TP_IMPORTING' ) && 0 !== strcasecmp( $user_email, $old_user_data->user_email ) ) )
                 && $this->_email_exists( $user_email ))
                return new TP_Error( 'existing_user_email', $this->__( 'Sorry, that email address is already used!' ) );
            $raw_user_url = empty( $userdata['user_url'] ) ? '' : $userdata['user_url'];
            $user_url = $this->_apply_filters( 'pre_user_url', $raw_user_url );
            $user_registered = empty( $userdata['user_registered'] ) ? gmdate( 'Y-m-d H:i:s' ) : $userdata['user_registered'];
            $user_activation_key = empty( $userdata['user_activation_key'] ) ? '' : $userdata['user_activation_key'];
            if ( ! empty( $userdata['spam'] ) && ! $this->_is_multisite() )
                return new TP_Error( 'no_spam', $this->__( 'Sorry, marking a user as spam is only supported on Multisite.' ) );
            $spam = empty( $userdata['spam'] ) ? 0 : (bool) $userdata['spam'];
            $meta = [];
            $nickname = empty( $userdata['nickname'] ) ? $user_login : $userdata['nickname'];
            $meta['nickname'] = $this->_apply_filters( 'pre_user_nickname', $nickname );
            $first_name = empty( $userdata['first_name'] ) ? '' : $userdata['first_name'];
            $meta['first_name'] = $this->_apply_filters( 'pre_user_first_name', $first_name );
            $last_name = empty( $userdata['last_name'] ) ? '' : $userdata['last_name'];
            $meta['last_name'] = $this->_apply_filters( 'pre_user_last_name', $last_name );
            if ( empty( $userdata['display_name'] ) ) {
                if ( $update ) $display_name = $user_login;
                elseif ( $meta['first_name'] && $meta['last_name'] )
                    $display_name = sprintf( $this->_x( '%1$s %2$s', 'Display name based on first name and last name' ), $meta['first_name'], $meta['last_name'] );
                elseif ( $meta['first_name'] ) $display_name = $meta['first_name'];
                elseif ( $meta['last_name'] ) $display_name = $meta['last_name'];
                else $display_name = $user_login;
            } else $display_name = $userdata['display_name'];
            $display_name = $this->_apply_filters( 'pre_user_display_name', $display_name );
            $description = empty( $userdata['description'] ) ? '' : $userdata['description'];
            $meta['description'] = $this->_apply_filters( 'pre_user_description', $description );
            $meta['rich_editing'] = empty( $userdata['rich_editing'] ) ? 'true' : $userdata['rich_editing'];
            $meta['syntax_highlighting'] = empty( $userdata['syntax_highlighting'] ) ? 'true' : $userdata['syntax_highlighting'];
            $meta['comment_shortcuts'] = empty( $userdata['comment_shortcuts'] ) || 'false' === $userdata['comment_shortcuts'] ? 'false' : 'true';
            $admin_color         = empty( $userdata['admin_color'] ) ? 'fresh' : $userdata['admin_color'];
            $meta['admin_color'] = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $admin_color );
            $meta['use_ssl'] = empty( $userdata['use_ssl'] ) ? 0 : (bool) $userdata['use_ssl'];
            $meta['show_admin_bar_front'] = empty( $userdata['show_admin_bar_front'] ) ? 'true' : $userdata['show_admin_bar_front'];
            $meta['locale'] = $userdata['locale'] ?? '';
            $compacted = compact( 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_key', 'display_name' );
            $data      = $this->_tp_unslash( $compacted );
            if ( ! $update ) $data += compact('user_login');
            if ( $this->_is_multisite() ) $data += compact('spam');
            $data = $this->_apply_filters( 'tp_pre_insert_user_data', $data, $update, ( $update ? $user_id : null ), $userdata );
            if ( empty( $data ) || ! is_array( $data ) )
                return new TP_Error( 'empty_data', $this->__( 'Not enough data to create this user.' ) );
            if ( $update ) {
                if ( $user_email !== $old_user_data->user_email || $user_pass !== $old_user_data->user_pass )
                    $data['user_activation_key'] = '';
                $this->tpdb->update( $this->tpdb->users, $data, array( 'ID' => $user_id ) );
            } else {
                $this->tpdb->insert( $this->tpdb->users, $data );
                $user_id = (int) $this->tpdb->insert_id;
            }
            $user = new TP_User( $user_id );
            $meta = $this->_apply_filters( 'insert_user_meta', $meta, $user, $update, $userdata );
            $custom_meta = [];
            if ( array_key_exists( 'meta_input', $userdata ) && is_array( $userdata['meta_input'] ) && ! empty( $userdata['meta_input'] ) )
                $custom_meta = $userdata['meta_input'];
            $custom_meta = $this->_apply_filters( 'insert_custom_user_meta', $custom_meta, $user, $update, $userdata );
            $meta = array_merge( $meta, $custom_meta );
            foreach ( $meta as $key => $value ) $this->_update_user_meta( $user_id, $key, $value );
            foreach ( $this->_tp_get_user_contact_methods( $user ) as $key => $value ) {
                if ( isset( $userdata[ $key ] ) ) $this->_update_user_meta( $user_id, $key, $userdata[ $key ] );
            }
            if ( isset( $userdata['role'] ) ) $user->set_role( $userdata['role'] );
            elseif ( ! $update ) $user->set_role( $this->_get_option( 'default_role' ) );
            $this->_clean_user_cache( $user_id );
            if ( $update ) {
                 $this->_do_action( 'profile_update', $user_id, $old_user_data, $userdata );
                if ( isset( $userdata['spam'] ) && $userdata['spam'] !== $old_user_data->spam ) {
                    if ( 1 === $userdata['spam'] ) $this->_do_action( 'make_spam_user', $user_id );
                    else $this->_do_action( 'make_ham_user', $user_id );
                }
            } else $this->_do_action( 'user_register', $user_id, $userdata );
            return $user_id;
        }//1907
        /**
         * @description Update a user in the database.
         * @param $userdata
         * @return int|TP_Error
         */
        protected function _tp_update_user( $userdata ){
            $user = null;
            if ( $userdata instanceof \stdClass )
                $userdata = get_object_vars( $userdata );
            elseif ( $userdata instanceof TP_User )
                $userdata = $userdata->to_array();
            $user_id = isset( $userdata['ID'] ) ? (int) $userdata['ID'] : 0;
            if ( ! $user_id ) return new TP_Error( 'invalid_user_id', $this->__( 'Invalid user ID.' ) );
            $user_obj = $this->_get_user_data( $user_id );
            if ( ! $user_obj ) return new TP_Error( 'invalid_user_id', $this->__( 'Invalid user ID.' ) );
            if( $user_obj instanceof TP_Post ){ $user = $user_obj->to_array();}
            foreach ( $this->_get_additional_user_keys( $user_obj ) as $key )
                $user[ $key ] = $this->_get_user_meta( $user_id, $key, true );
            $user = $this->_add_magic_quotes( $user );
            if ( ! empty( $userdata['user_pass'] ) && $userdata['user_pass'] !== $user_obj->user_pass ) {
                $plaintext_pass        = $userdata['user_pass'];
                $userdata['user_pass'] = $this->_tp_hash_password( $userdata['user_pass'] );
                $send_password_change_email = $this->_apply_filters( 'send_password_change_email', true, $user, $userdata );
            }
            if ( isset( $userdata['user_email'] ) && $user['user_email'] !== $userdata['user_email'] )
                $send_email_change_email = $this->_apply_filters( 'send_email_change_email', true, $user, $userdata );
            $this->_clean_user_cache( $user_obj );
            $userdata = array_merge( $user, $userdata );
            $user_id  = $this->_tp_insert_user( $userdata );
            if ( $this->_init_error( $user_id ) ) return $user_id;
            $blog_name = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $switched_locale = false;
            if ( ! empty( $send_password_change_email ) || ! empty( $send_email_change_email ) )
                $switched_locale = $this->_switch_to_locale( $this->_get_user_locale( $user_id ) );
            if ( ! empty( $send_password_change_email ) ) {
                $pass_change_text = $this->__(
                    'Hi ###USERNAME###,
This notice confirms that your password was changed on ###SITENAME###.
If you did not change your password, please contact the Site Administrator at
###ADMIN_EMAIL###
This email has been sent to ###EMAIL###
Regards,
All at ###SITENAME###
###SITEURL###'
                );
                $pass_change_email = ['to' => $user['user_email'],'subject' => $this->__( '[%s] Password Changed' ),
                    'message' => $pass_change_text,'headers' => '',];
                $pass_change_email = $this->_apply_filters( 'password_change_email', $pass_change_email, $user, $userdata );
                $pass_change_email['message'] = str_replace( '###USERNAME###', $user['user_login'], $pass_change_email['message'] );
                $pass_change_email['message'] = str_replace( '###ADMIN_EMAIL###', $this->_get_option( 'admin_email' ), $pass_change_email['message'] );
                $pass_change_email['message'] = str_replace( '###EMAIL###', $user['user_email'], $pass_change_email['message'] );
                $pass_change_email['message'] = str_replace( '###SITENAME###', $blog_name, $pass_change_email['message'] );
                $pass_change_email['message'] = str_replace( '###SITEURL###', $this->_home_url(), $pass_change_email['message'] );
                $this->_tp_mail( $pass_change_email['to'], sprintf( $pass_change_email['subject'], $blog_name ), $pass_change_email['message'], $pass_change_email['headers'] );
            }
            if ( ! empty( $send_email_change_email ) ) {
                /* translators: Do not translate USERNAME, ADMIN_EMAIL, NEW_EMAIL, EMAIL, SITENAME, SITEURL: those are placeholders. */
                $email_change_text = $this->__(
                    'Hi ###USERNAME###,
This notice confirms that your email address on ###SITENAME### was changed to ###NEW_EMAIL###.
If you did not change your email, please contact the Site Administrator at
###ADMIN_EMAIL###
This email has been sent to ###EMAIL###
Regards,
All at ###SITENAME###
###SITEURL###'
                );
                $email_change_email = ['to'      => $user['user_email'],'subject' => $this->__( '[%s] Email Changed' ),
                    'message' => $email_change_text,'headers' => '',];
                $email_change_email = $this->_apply_filters( 'email_change_email', $email_change_email, $user, $userdata );
                $email_change_email['message'] = str_replace( '###USERNAME###', $user['user_login'], $email_change_email['message'] );
                $email_change_email['message'] = str_replace( '###ADMIN_EMAIL###', $this->_get_option( 'admin_email' ), $email_change_email['message'] );
                $email_change_email['message'] = str_replace( '###NEW_EMAIL###', $userdata['user_email'], $email_change_email['message'] );
                $email_change_email['message'] = str_replace( '###EMAIL###', $user['user_email'], $email_change_email['message'] );
                $email_change_email['message'] = str_replace( '###SITENAME###', $blog_name, $email_change_email['message'] );
                $email_change_email['message'] = str_replace( '###SITEURL###', $this->_home_url(), $email_change_email['message'] );
                $this->_tp_mail( $email_change_email['to'], sprintf( $email_change_email['subject'], $blog_name ), $email_change_email['message'], $email_change_email['headers'] );
            }
            if ( $switched_locale ) $this->_restore_previous_locale();
            $current_user = $this->_tp_get_current_user();
            if ($current_user instanceof TP_User && $current_user->ID === $user_id && isset($plaintext_pass)) {
                $this->_tp_clear_auth_cookie();
                $logged_in_cookie = $this->_tp_parse_auth_cookie( '', 'logged_in' );
                $default_cookie_life = $this->_apply_filters( 'auth_cookie_expiration', ( 2 * DAY_IN_SECONDS ), $user_id, false );
                $remember = false;
                if ( false !== $logged_in_cookie && ( $logged_in_cookie['expiration'] - time() ) > $default_cookie_life )
                    $remember = true;
                $this->_tp_set_auth_cookie( $user_id, $remember );
            }
            return $user_id;
        }//2338
        /**
         * @description A simpler way of inserting a user into the database.
         * @param $username
         * @param $password
         * @param string $email
         * @return int
         */
        protected function _tp_create_user( $username, $password, $email = '' ):int{
            $user_login = $this->_tp_slash( $username );
            $user_email = $this->_tp_slash( $email );
            $user_pass  = $password;
            $userdata = compact( 'user_login', 'user_email', 'user_pass' );
            return $this->_tp_insert_user( $userdata );
        }//2578
        /**
         * @description Returns a list of meta keys to be (maybe) populated in tp_update_user().
         * @param $user
         * @return array
         */
        protected function _get_additional_user_keys( $user ):array{
            $keys = array( 'first_name', 'last_name', 'nickname', 'description', 'rich_editing', 'syntax_highlighting', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front', 'locale' );
            return array_merge( $keys, array_keys( $this->_tp_get_user_contact_methods( $user ) ) );
        }//2599
        /**
         * @description Set up the user contact methods.
         * @param null $user
         * @return mixed
         */
        protected function _tp_get_user_contact_methods( $user = null ){
            $methods = [];
            if ( $this->_get_site_option( 'initial_db_version' ) < 23588 ) {
                $methods = ['aim' => $this->__( 'AIM' ),'yim' => $this->__( 'Yahoo IM' ),
                    'jabber' => $this->__( 'Jabber / Google Talk' ),];
            }
            return $this->_apply_filters( 'user_contact_methods', $methods, $user );
        }//2614
        /**
         * @description Gets the text suggesting how to create strong passwords.
         * @return mixed
         */
        protected function _tp_get_password_hint(){
            $hint = $this->__( 'Hint: The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).' );
            return $this->_apply_filters( 'password_hint', $hint );
        }//2657
        /**
         * @description Creates, stores, then returns a password reset key for user.
         * @param $user
         * @return bool|int|TP_Error
         */
        protected function _get_password_reset_key( $user ){
            $tp_hasher = $this->_init_hasher( 8, true);
            if ( ! ( $user instanceof TP_User ) )
                return new TP_Error( 'invalid_combo', $this->__( '<strong>Error</strong>: There is no account with that username or email address.' ) );
            $this->_do_action( 'retrieve_password', $user->user_login );
            $allow = true;
            if ( $this->_is_multisite() && $this->_is_user_spammy( $user ) ) $allow = false;
            $allow = $this->_apply_filters( 'allow_password_reset', $allow, $user->ID );
            if ( ! $allow ) return new TP_Error( 'no_password_reset', $this->__( 'Password reset is not allowed for this user' ) );
            elseif ( $this->_init_error( $allow ) ) return $allow;
            $key = $this->_tp_generate_password( 20, false );
            $this->_do_action( 'retrieve_password_key', $user->user_login, $key );
            $hashed = time() . ':' . $tp_hasher->HashPassword( $key );
            $key_saved = $this->_tp_update_user(['ID' => $user->ID,'user_activation_key' => $hashed,]);
            if ( $this->_init_error( $key_saved ) ) return $key_saved;
            return $key;
        }//2680
        /**
         * @description Retrieves a user row based on password reset key and login
         * @param $key
         * @param $login
         * @return TP_Error
         */
        protected function _check_password_reset_key( $key, $login ):TP_Error{
            $this->_init_db();
            $tp_hasher = $this->_init_hasher(8, true);
            $key = preg_replace( '/[^a-z0-9]/i', '', $key );
            if ( empty( $key ) || ! is_string( $key ) )
                return new TP_Error( 'invalid_key', $this->__( 'Invalid key.' ) );
            if ( empty( $login ) || ! is_string( $login ) )
                return new TP_Error( 'invalid_key', $this->__( 'Invalid key.' ) );
            $user = $this->_get_user_by( 'login', $login );
            if ( ! $user ) return new TP_Error( 'invalid_key', $this->__( 'Invalid key.' ) );
            $expiration_duration = $this->_apply_filters( 'password_reset_expiration', DAY_IN_SECONDS );
            if ( false !== strpos( $user->user_activation_key, ':' ) ) {
                @list( $pass_request_time, $pass_key ) = explode( ':', $user->user_activation_key, 2 );
                $expiration_time                      = $pass_request_time + $expiration_duration;
            } else {
                $pass_key        = $user->user_activation_key;
                $expiration_time = false;
            }
            if ( ! $pass_key )
                return new TP_Error( 'invalid_key', $this->__( 'Invalid key.' ) );
            $hash_is_correct = $tp_hasher->CheckPassword( $key, $pass_key );
            if ( $hash_is_correct && $expiration_time && time() < $expiration_time )
                return $user;
            elseif ( $hash_is_correct && $expiration_time )
                return new TP_Error( 'expired_key', $this->__( 'Invalid key.' ) );
            if (( $hash_is_correct && ! $expiration_time ) || hash_equals( $user->user_activation_key, $key )) {
                $return  = new TP_Error( 'expired_key', $this->__( 'Invalid key.' ) );
                $user_id = $user->ID;
                return $this->_apply_filters( 'password_reset_key_expired', $return, $user_id );
            }
            return new TP_Error( 'invalid_key', $this->__( 'Invalid key.' ) );
        }//2781
        /**
         * @description Handles sending a password retrieval email to a user.
         * @param null $user_login
         * @return bool|TP_Error
         */
        protected function _retrieve_password( $user_login = null ){
            $errors    = new TP_Error();
            $user_data = false;
            if ( ! $user_login && ! empty( $_POST['user_login'] ) )
                $user_login = $_POST['user_login'];
            if ( empty( $user_login ) ) {
                $errors->add( 'empty_username', $this->__( '<strong>Error</strong>: Please enter a username or email address.' ) );
            } elseif ( strpos( $user_login, '@' ) ) {
                $user_data = $this->_get_user_by( 'email', trim( $this->_tp_unslash( $user_login ) ) );
                if ( empty( $user_data ) )
                    $errors->add( 'invalid_email', $this->__( '<strong>Error</strong>: There is no account with that username or email address.' ) );
            } else $user_data = $this->_get_user_by( 'login', trim( $this->_tp_unslash( $user_login ) ) );
            $user_data = $this->_apply_filters( 'lost_password_user_data', $user_data, $errors );
            $this->_do_action( 'lost_password_post', $errors, $user_data );
            $errors = $this->_apply_filters( 'lost_password_errors', $errors, $user_data );
            //if( $errors instanceof TP_Error );
            if ($errors instanceof TP_Error && $errors->has_errors() ) return $errors;
            if ( ! $user_data ) {
                $errors->add( 'invalid_combo', $this->__( '<strong>Error</strong>: There is no account with that username or email address.' ) );
                return $errors;
            }
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;
            $key        = $this->_get_password_reset_key( $user_data );
            if ( $this->_init_error( $key ) ) return $key;
            $locale = $this->_get_user_locale( $user_data );
            $switched_locale = $this->_switch_to_locale( $locale );
            if ( $this->_is_multisite() ) $site_name = $this->_get_network()->site_name;
            else $site_name = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $message = $this->__( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
            /* translators: %s: Site name. */
            $message .= sprintf( $this->__( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
            /* translators: %s: User login. */
            $message .= sprintf( $this->__( 'Username: %s' ), $user_login ) . "\r\n\r\n";
            $message .= $this->__( 'If this was a mistake, ignore this email and nothing will happen.' ) . "\r\n\r\n";
            $message .= $this->__( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
            $message .= $this->_network_site_url( "login-form.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale . "\r\n\r\n";
            if ( ! $this->_is_user_logged_in() ) {
                $requester_ip = $_SERVER['REMOTE_ADDR'];
                if ( $requester_ip )
                    $message .= sprintf($this->__( 'This password reset request originated from the IP address %s.' ), $requester_ip) . "\r\n";
            }
            $title = sprintf( $this->__( '[%s] Password Reset' ), $site_name );
            $title = $this->_apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
            $message = $this->_apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
            if ( $switched_locale ) $this->_restore_previous_locale();
            if ( $message && ! $this->_tp_mail( $user_email, $this->_tp_special_chars_decode( $title ), $message ) ) {
                $errors->add('retrieve_password_email_failure',
                    sprintf($this->__("<strong>Error</strong>:The email could not be sent.Your site may not be correctly configured to send emails.<a href='%s'>Get support for resetting your password</a>."),
                        $this->_esc_url( $this->__( 'https://wordpress.org/support/article/resetting-your-password/' ) )));
                return $errors;
            }
            return true;
        }//2869
    }
}else die;