<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 06:47
 */
namespace TP_Admin\Traits\AdminUser;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_db;
use TP_Admin\TP_Profile;
if(ABSPATH){
    trait _adm_user_01{
        use _init_db;
        /**
         * @description Creates a new user from the "Users" form using $_POST information.
         * @return string
         */
        protected function _add_user():string{
            return $this->_edit_user();
        }//16
        /**
         * @description Edit user settings based on contents of $_POST
         * @param int $user_id
         * @return int|TP_Error
         */
        protected function _edit_user( $user_id = 0 ){
            $tp_roles = $this->_tp_roles();
            $user = new \stdClass;
            $user_id  = (int) $user_id;
            if ( $user_id ) {
                $update = true;
                $user->ID = $user_id;
                $userdata = $this->_get_user_data( $user_id );
                $user->user_login = $this->_tp_slash( $userdata->user_login );
            } else { $update = false;}
            if ( ! $update && isset( $_POST['user_login'] ) ) {
                $user->user_login = $this->_sanitize_user( $this->_tp_unslash( $_POST['user_login'] ), true );
            }
            $pass1 = '';
            $pass2 = '';
            if ( isset( $_POST['pass1'] ) ) { $pass1 = trim( $_POST['pass1'] );}
            if ( isset( $_POST['pass2'] ) ) { $pass2 = trim( $_POST['pass2'] );}
            if ( isset( $_POST['role'] ) && $this->_current_user_can( 'promote_users' ) && ( ! $user_id || $this->_current_user_can( 'promote_user', $user_id ) ) ) {
                $new_role = $this->_sanitize_text_field( $_POST['role'] );
                $editable_roles = $this->_get_editable_roles();
                if ( ! empty( $new_role ) && empty( $editable_roles[ $new_role ] ) ) {
                    $this->_tp_die( $this->__( 'Sorry, you are not allowed to give users that role.' ), 403 );
                }
                $potential_role = $tp_roles->role_objects[ $new_role ] ?? false;
                if ($this->_get_current_user_id() !== $user_id  || ( $potential_role instanceof TP_User && $potential_role->has_cap( 'promote_users' ) )||( $this->_is_multisite() && $this->_current_user_can( 'manage_network_users' ) )){
                    $user->role = $new_role;
                }
            }
            if ( isset( $_POST['email'] ) ) {
                $user->user_email = $this->_sanitize_text_field( $this->_tp_unslash( $_POST['email'] ) );
            }
            if ( isset( $_POST['url'] ) ) {
                if ( empty( $_POST['url'] ) || 'http://' === $_POST['url'] ) { $user->user_url = '';}
                else {
                    $user->user_url = $this->_esc_url_raw( $_POST['url'] );
                    $protocols      = implode( '|', array_map( 'preg_quote', $this->_tp_allowed_protocols() ) );
                    $user->user_url = preg_match( '/^(' . $protocols . '):/is', $user->user_url ) ? $user->user_url : 'http://' . $user->user_url;
                }
            }
            if ( isset( $_POST['first_name'] ) ) { $user->first_name = $this->_sanitize_text_field( $_POST['first_name'] );}
            if ( isset( $_POST['last_name'] ) ) { $user->last_name = $this->_sanitize_text_field( $_POST['last_name'] );}
            if ( isset( $_POST['nickname'] ) ) { $user->nickname = $this->_sanitize_text_field( $_POST['nickname'] );}
            if ( isset( $_POST['display_name'] ) ) {$user->display_name = $this->_sanitize_text_field( $_POST['display_name'] );}
            if ( isset( $_POST['description'] ) ) { $user->description = trim( $_POST['description'] );}
            foreach ( $this->_tp_get_user_contact_methods( $user ) as $method => $name ) {
                if ( isset( $_POST[ $method ] ) ) { $user->$method = $this->_sanitize_text_field( $_POST[ $method ] );}
            }
            if ( isset( $_POST['locale'] ) ) {
                $locale = $this->_sanitize_text_field( $_POST['locale'] );
                if ( 'site-default' === $locale ) { $locale = '';}
                elseif ( '' === $locale ) {$locale = 'en_US';}
                elseif ( ! in_array( $locale, $this->_get_available_languages(), true ) ) { $locale = '';}
                $user->locale = $locale;
            }
            if ( $update ) {
                $user->rich_editing         = isset( $_POST['rich_editing'] ) && 'false' === $_POST['rich_editing'] ? 'false' : 'true';
                $user->syntax_highlighting  = isset( $_POST['syntax_highlighting'] ) && 'false' === $_POST['syntax_highlighting'] ? 'false' : 'true';
                $user->admin_color          = isset( $_POST['admin_color'] ) ? $this->_sanitize_text_field( $_POST['admin_color'] ) : 'fresh';
                $user->show_admin_bar_front = isset( $_POST['admin_bar_front'] ) ? 'true' : 'false';
            }
            $user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && 'true' === $_POST['comment_shortcuts'] ? 'true' : '';
            $user->use_ssl = 0;
            if ( ! empty( $_POST['use_ssl'] ) ) { $user->use_ssl = 1;}
            $errors = new TP_Error();
            if ( '' === $user->user_login ) {
                $errors->add( 'user_login', $this->__( '<strong>Error</strong>: Please enter a username.' ) );}
            if ( $update && empty( $user->nickname ) ) {
                $errors->add( 'nickname', $this->__( '<strong>Error</strong>: Please enter a nickname.' ) );}
            $this->_do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );
            if ( ! $update && empty( $pass1 ) ) {
                $errors->add( 'pass', $this->__( '<strong>Error</strong>: Please enter a password.' ), array( 'form-field' => 'pass1' ) );
            }
            if ( false !== strpos( $this->_tp_unslash( $pass1 ), '\\' ) ) {
                $errors->add( 'pass', $this->__( '<strong>Error</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );
            }
            if ( ( $update || ! empty( $pass1 ) ) && $pass1 !== $pass2 ) {
                $errors->add( 'pass', $this->__( '<strong>Error</strong>: Passwords don&#8217;t match. Please enter the same password in both password fields.' ), array( 'form-field' => 'pass1' ) );
            }
            if ( ! empty( $pass1 ) ) { $user->user_pass = $pass1;}
            if ( ! $update && isset( $_POST['user_login'] ) && ! $this->_validate_username( $_POST['user_login'] ) ) {
                $errors->add( 'user_login', $this->__( '<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
            }
            if ( ! $update && $this->_username_exists( $user->user_login ) ) {
                $errors->add( 'user_login', $this->__( '<strong>Error</strong>: This username is already registered. Please choose another one.' ) );
            }
            $illegal_logins = (array) $this->_apply_filters( 'illegal_user_logins', array() );
            if ( in_array( strtolower( $user->user_login ), array_map( 'strtolower', $illegal_logins ), true ) ) {
                $errors->add( 'invalid_username', $this->__( '<strong>Error</strong>: Sorry, that username is not allowed.' ) );
            }
            if ( empty( $user->user_email ) ) {
                $errors->add( 'empty_email', $this->__( '<strong>Error</strong>: Please enter an email address.' ), array( 'form-field' => 'email' ) );
            } elseif ( ! $this->_is_email( $user->user_email ) ) {
                $errors->add( 'invalid_email', $this->__( '<strong>Error</strong>: The email address isn&#8217;t correct.' ), array( 'form-field' => 'email' ) );
            } else {
                $owner_id = $this->_email_exists( $user->user_email );
                if ( $owner_id && ( ! $update || ( $owner_id !== $user->ID ) ) ) {
                    $errors->add( 'email_exists', $this->__( '<strong>Error</strong>: This email is already registered. Please choose another one.' ), array( 'form-field' => 'email' ) );
                }
            }
            $this->_do_action_ref_array( 'user_profile_update_errors', array( &$errors, $update, &$user ) );
            if ( $errors->has_errors() ) { return $errors;}
            if ( $update ) {
                $user_id = (int)$this->_tp_update_user($user);
            } else {
                $user_id = (int)$this->_tp_insert_user( $user );
                $notify  = isset( $_POST['send_user_notification'] ) ? 'both' : 'admin';
                $this->_do_action( 'edit_user_created_user', $user_id, $notify );
            }
            return $user_id;
        }//30
        /**
         * @description Fetch a filtered list of user roles that the current user is
         * @description . allowed to edit.
         * @return mixed
         */
        protected function _get_editable_roles(){
            $all_roles = $this->_tp_roles()->roles;
            return $this->_apply_filters( 'editable_roles', $all_roles );
        }//262
        /**
         * @description Retrieve user data and filter it.
         * @param $user_id
         * @return mixed
         */
        protected function _get_user_to_edit( $user_id ){
            $user = $this->_get_user_data( $user_id );
            if ( $user ) { $user->filter = 'edit';}
            return $user;
        }//285
        /**
         * @description Retrieve the user's drafts.
         * @param $user_id
         * @return array|null
         */
        protected function _get_users_drafts( $user_id ):?array{
            $this->tpdb = $this->_init_db();
            $query = $this->tpdb->prepare( TP_SELECT . " ID, post_title FROM $this->tpdb->posts WHERE post_type = 'post' AND post_status = 'draft' AND post_author = %d ORDER BY post_modified DESC", $user_id );
            $query = $this->_apply_filters( 'get_users_drafts', $query );
            return $this->tpdb->get_results( $query );
        }//305
        /**
         * @description Remove user and optionally reassign posts and links to another user.
         * @param $id
         * @param null $reassign
         * @return bool
         */
        protected function _tp_delete_user( $id, $reassign = null ):bool{
            $this->tpdb = $this->_init_db();
            if ( ! is_numeric( $id)){ return false;}
            $id   = (int) $id;
            $user = new TP_User( $id );
            if ( ! $user->exists()){ return false;}
            if ( 'novalue' === $reassign ) { $reassign = null;}
            elseif ( null !== $reassign ) { $reassign = (int) $reassign;}
            $this->_do_action( 'delete_user', $id, $reassign, $user );
            if ( null === $reassign ) {
                $post_types_to_delete = [];
                foreach ( $this->_get_post_types( [], 'objects' ) as $post_type ) {
                    if ( $post_type->delete_with_user ) { $post_types_to_delete[] = $post_type->name;}
                    elseif ( null === $post_type->delete_with_user && $this->_post_type_supports( $post_type->name, 'author' ) ) {
                        $post_types_to_delete[] = $post_type->name; }
                }
                $post_types_to_delete = $this->_apply_filters( 'post_types_to_delete_with_user', $post_types_to_delete, $id );
                $post_types_to_delete = implode( "', '", $post_types_to_delete );
                $post_ids             = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE post_author = %d AND post_type IN ('$post_types_to_delete')", $id ) );
                if ( $post_ids ) {
                    foreach ( $post_ids as $post_id ) { $this->_tp_delete_post( $post_id ); }
                }
                $link_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " link_id FROM $this->tpdb->links WHERE link_owner = %d", $id ) );
                if ( $link_ids ) {
                    foreach ( $link_ids as $link_id ) { $this->_tp_delete_link( $link_id );}
                }
            } else {
                $post_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE post_author = %d", $id ) );
                $this->tpdb->update( $this->tpdb->posts, array( 'post_author' => $reassign ), array( 'post_author' => $id ) );
                if ( ! empty( $post_ids ) ) {
                    foreach ( $post_ids as $post_id ) { $this->_clean_post_cache( $post_id );}
                }
                $link_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " link_id FROM $this->tpdb->links WHERE link_owner = %d", $id ) );
                $this->tpdb->update( $this->tpdb->links, array( 'link_owner' => $reassign ), array( 'link_owner' => $id ) );
                if ( ! empty( $link_ids ) ) {
                    foreach ( $link_ids as $link_id ) { $this->_clean_bookmark_cache( $link_id );}
                }
            }
            if ( $this->_is_multisite() ) { $this->_remove_user_from_blog( $id, $this->_get_current_blog_id() );}
            else {
                $meta = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " umeta_id FROM $this->tpdb->user_meta WHERE user_id = %d", $id ) );
                foreach ( $meta as $mid ) { $this->_delete_metadata_by_mid( 'user', $mid );}
                $this->tpdb->delete( $this->tpdb->users, array( 'ID' => $id ) );
            }
            $this->_clean_user_cache( $user );
            $this->_do_action( 'deleted_user', $id, $reassign, $user );
            return true;
        }//336
        /**
         * @description Remove all capabilities from user.
         * @param int $id
         */
        protected function _tp_revoke_user(int $id ):void{
            $user = new TP_User( $id );
            $user->remove_all_caps();
        }//459
        protected function _default_password_nag_handler():void{ //not used $errors = false
            if ( ! $this->_get_user_option( 'default_password_nag' ) ) { return;}
            if ((isset($_GET['default_password_nag']) && '0' === $_GET['default_password_nag']) || 'hide' === $this->_get_user_setting( 'default_password_nag' )){
                $this->_delete_user_setting( 'default_password_nag' );
                $this->_update_user_meta( $this->tp_user_ID, 'default_password_nag', false );
            }
        }//473
        protected function _default_password_nag_edit_user( $user_ID, $old_data ):void{
            if ( ! $this->_get_user_option( 'default_password_nag', $user_ID ) ) { return;}
            $new_data = $this->_get_user_data( $user_ID );
            if ( $new_data->user_pass !== $old_data->user_pass ) {
                $this->_delete_user_setting( 'default_password_nag' );
                $this->_update_user_meta( $user_ID, 'default_password_nag', false );
            }
        }//495
        protected function _get_default_password_nag():string{
            $profile = TP_Profile::get_profile();
            if ( $profile === $this->tp_pagenow || ! $this->_get_user_option( 'default_password_nag' ) ) {
                return false;}
            $output  = "<div class='error default-password-nag'><p><strong>{$this->__('Notice:')}</strong></p><p>";
            $output .= sprintf("<a href='%s'>{$this->__('Yes, take me to my profile page')}</a>|", $this->_get_edit_profile_url() . '#password');
            $output .= sprintf("<a href='%s' id='default_password_nag_no'>{$this->__('No thanks, do not remind me again')}</a>",'?default_password_nag=0');
            $output .= "</p></div>";
            return $output;
        }//515
        protected function _default_password_nag():void{
            echo $this->_get_default_password_nag();
        }//515
    }
}else die;