<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    trait _ms_methods_04{
        use _init_db;
        /**
         * @description Updates the network-wide users count.
         * @param null $network_id
         */
        protected function _tp_maybe_update_network_user_counts( $network_id = null ):void{
            $is_small_network = ! $this->_tp_is_large_network( 'users', $network_id );
            if ( ! $this->_apply_filters( 'enable_live_network_counts', $is_small_network, 'users' ) )
                return;
            $this->_tp_update_network_user_counts( $network_id );
        }//2567
        /**
         * @description Logs the user email, IP, and registration date of a new site.
         * @param $blog_id
         * @param $user_id
         */
        protected function _tp_mu_log_new_registrations( $blog_id, $user_id ):void{
            $this->_tpdb = $this->_init_db();
            if ( is_object( $blog_id ) ) $blog_id = $blog_id->blog_id;
            if ( is_array( $user_id ) ) $user_id = ! empty( $user_id['user_id'] ) ? $user_id['user_id'] : 0;
            $user = $this->_get_user_data( (int) $user_id );
            if ( $user )
                $this->_tpdb->insert(
                    $this->_tpdb->registration_log,
                    ['email' => $user->user_email,
                        'IP' => preg_replace( '/[^0-9., ]/', '', $this->_tp_unslash( $_SERVER['REMOTE_ADDR'] ) ),
                        'blog_id' => $blog_id,'date_registered' => $this->_current_time( 'mysql' ),]
                );
        }//2039
        /**
         * @description Maintains a canonical list of terms by syncing terms created for
         * @description . each blog with the global terms table.
         * @param $term_id
         * @return int|null
         */
        protected function _global_terms( $term_id):int{
            $this->tpdb = $this->_init_db();
            static $global_terms_recurse = null;
            if ( ! $this->_global_terms_enabled() ) return $term_id;
            $recurse_start = false;
            if ( null === $global_terms_recurse ) {
                $recurse_start        = true;
                $global_terms_recurse = 1;
            } elseif ( 10 < $global_terms_recurse++ ) return $term_id;
            $term_id = (int) $term_id;
            $_get_row       = $this->tpdb->get_row( $this->tpdb->prepare(TP_SELECT . " * FROM $this->tpdb->terms WHERE term_id = %d", $term_id ) );
            $get_row = null;
            if( $_get_row instanceof TP_Term ){
                $get_row = $_get_row;
            }//todo
            $global_id = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " cat_ID FROM $this->tpdb->site_categories WHERE category_nicename = %s", $get_row->slug ) );
            if ( null === $global_id ) {
                $used_global_id = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " cat_ID FROM $this->tpdb->site_categories WHERE cat_ID = %d", $get_row->term_id ) );
                if (null === $used_global_id ) {
                    $this->tpdb->insert(
                        $this->tpdb->site_categories,['cat_ID' => $term_id, 'cat_name' => $get_row->name,'category_nicename' => $get_row->slug,]
                    );
                    $global_id = $this->tpdb->insert_id;
                    if ( empty( $global_id ) ) return $term_id;
                }else{
                    $max_global_id = $this->tpdb->get_var( TP_SELECT . " MAX(cat_ID) FROM $this->tpdb->site_categories" );
                    $max_local_id  = $this->tpdb->get_var( TP_SELECT . " MAX(term_id) FROM $this->tpdb->terms" );
                    $new_global_id = max( $max_global_id, $max_local_id ) + random_int( 100, 400 );
                    $this->tpdb->insert(
                        $this->tpdb->site_categories,
                        ['cat_ID' => $new_global_id,'cat_name' => $_get_row->name,'category_nicename' => $_get_row->slug,]
                    );
                    $global_id = $this->tpdb->insert_id;
                }
            }elseif ( $global_id !== $term_id ) {
                $local_id = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " term_id FROM $this->tpdb->terms WHERE term_id = %d", $global_id ) );
                if ( null !== $local_id ) {
                    $this->_global_terms( $local_id );
                    if ( 10 < $global_terms_recurse ) $global_id = $term_id;
                }
            }
            if ( $global_id !== $term_id ) {
                if ( $this->_get_option( 'default_category' ) === $term_id )
                    $this->_update_option( 'default_category', $global_id );
                $this->tpdb->update( $this->tpdb->terms, array( 'term_id' => $global_id ), array( 'term_id' => $term_id ) );
                $this->tpdb->update( $this->tpdb->term_taxonomy, array( 'term_id' => $global_id ), array( 'term_id' => $term_id ) );
                $this->tpdb->update( $this->tpdb->term_taxonomy, array( 'parent' => $global_id ), array( 'parent' => $term_id ) );
                $this->_clean_term_cache( $term_id );
            }
            if ( $recurse_start ) $global_terms_recurse = null;
            return $global_id;
        }//2077
        /**
         * @description Ensures that the current site's domain is listed in the allowed redirect host list.
         * @return array
         */
        protected function _redirect_this_site():array{
            return array( $this->_get_network()->domain );
        }//2168
        /**
         * @description Checks whether an upload is too big.
         * @param $upload
         * @return string
         */
        protected function _upload_is_file_too_big( $upload ):string{
            if ( ! is_array( $upload ) || defined( 'TP_IMPORTING' ) || $this->_get_site_option( 'upload_space_check_disabled' ) )
                return $upload;
            if ( strlen( $upload['bits'] ) > ( KB_IN_BYTES * $this->_get_site_option( 'file_upload_max', 1500 ) ) )
                /* translators: %s: Maximum allowed file size in kilobytes. */
                return sprintf( $this->__( 'This file is too big. Files must be less than %s KB in size.' ) . '<br />', $this->_get_site_option( 'file_upload_max', 1500 ) );
            return $upload;
        }// 2182
        /**
         * @description Adds a nonce field to the signup page.
         */
        protected function _signup_nonce_fields():void{
            $id = mt_rand();
            echo "<input type='hidden' name='signup_form_id' value='{$id}' />";
            $this->_tp_nonce_field( 'signup_form_' . $id, '_signup_form', false );
        }//2200
        /**
         * @description Processes the signup nonce created in signup_nonce_fields().
         * @param $result
         * @return mixed
         */
        protected function _signup_nonce_check($result ){
            if ( ! strpos( $_SERVER['PHP_SELF'], 'tp-signup.php' ) ) return $result;
            if ($result['errors'] instanceof TP_Error && !$this->_tp_verify_nonce($_POST['_signup_form'], 'signup_form_' . $_POST['signup_form_id'])) {
                $result['errors']->add( 'invalid_nonce', $this->__( 'Unable to submit this form, please try again.' ) );
            }
            return $result;
        }//2214
        /**
         * @description Corrects 404 redirects when NO_BLOG_REDIRECT is defined.
         */
        protected function _maybe_redirect_404():void{
            if (defined( 'NO_BLOG_REDIRECT' ) && $this->_is_main_site() && $this->_is_404()) {
                $destination = $this->_apply_filters( 'blog_redirect_404', NO_BLOG_REDIRECT );
                if ( $destination ) {
                    if ( '%siteurl%' === $destination ) $destination = $this->_network_home_url();
                    $this->_tp_redirect( $destination );
                    exit;
                }
            }
        }//2231
        /**
         * @description Adds a new user to a blog by visiting /new blog user/{key}/.
         */
        protected function _maybe_add_existing_user_to_blog():void{
            if ( false === strpos( $_SERVER['REQUEST_URI'], '/newblog_user/' ) )
                return;
            $parts = explode( '/', $_SERVER['REQUEST_URI'] );
            $key   = array_pop( $parts );
            if ( '' === $key ) $key = array_pop( $parts );
            $details = $this->_get_option( 'new_user_' . $key );
            if ( ! empty( $details ) ) $this->_delete_option( 'new_user_' . $key );
            if ( empty( $details ) || $this->_init_error( $this->_add_existing_user_to_blog( $details ) ) )
                $this->_tp_die(sprintf($this->__("An error occurred adding you to this site. Go back to the <a href='%s'>homepage</a>"),
                        $this->_home_url()));/* translators: %s: Home URL. */
            $this->_tp_die(/* translators: 1: Home URL, 2: Admin URL. */
                sprintf($this->__("You have been added to this site. Please visit the <a href='%1\$s'>homepage</a> or <a href='%2\$s'>log in</a> using your username and password."),
                    $this->_home_url(), $this->_admin_url()), $this->__( 'TailoredPress &rsaquo; Success' ), ['response' => 200]
            );

        }//2264
        /**
         * @description Adds a user to a blog based on details from maybe_add_existing_user_to_blog().
         * @param bool $details
         * @return bool
         */
        protected function _add_existing_user_to_blog( $details = false ):bool{
            if ( is_array( (array)$details ) ) {
                $blog_id = $this->_get_current_blog_id();
                $result  = $this->_add_user_to_blog( $blog_id, $details['user_id'], $details['role'] );
                $this->_do_action( 'added_existing_user', $details['user_id'], $result );
                return $result;
            }
            return false;
        }//2317
        /**
         * @description Adds a newly created user to the appropriate blog
         * @param $user_id
         * @param $meta
         * @param $password
         */
        protected function _add_new_user_to_blog( $user_id, $meta, $password ):void{
            if ( ! empty( $meta['add_to_blog'] ) ) {
                $blog_id = $meta['add_to_blog'];
                $role    = $meta['new_role'];
                $this->_remove_user_from_blog( $user_id, $password, $this->_get_network()->site_id ); // Remove user from main blog.
                $result = $this->_add_user_to_blog( $blog_id, $user_id, $role );
                if ( ! $this->_init_error( $result ) )
                    $this->_update_user_meta( $user_id, 'primary_blog', $blog_id );
            }
        }//2351
    }
}else die;