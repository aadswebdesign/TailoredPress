<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 06:36
 */
namespace TP_Core\Traits\Multisite\Methods;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _ms_methods_01{
        use _init_db;
        use _init_error;
        /**
         * @description Gets the network's site and user counts.
         * @return array
         */
        protected function _get_sitestats():array{
            $stats = ['blogs' => $this->_get_blog_count(), 'users' => $this->_get_user_count(),];
            return $stats;
        }//22
        /**
         * @description Gets one of a user's active blogs.
         * @param $user_id
         * @return bool|mixed|null
         */
        protected function _get_active_blog_for_user( $user_id ){
            $blogs = $this->_get_blogs_of_user( $user_id );
            $primary = null;
            if ( empty( $blogs ) ) return false;
            if ( ! $this->_is_multisite() ) return $blogs[ $this->_get_current_blog_id() ];
            $primary_blog = $this->_get_user_meta( $user_id, 'primary_blog', true );
            $first_blog   = current( $blogs );
            if ( false !== $primary_blog ) {
                if ( ! isset( $blogs[ $primary_blog ] ) ) {
                    $this->_update_user_meta( $user_id, 'primary_blog', $first_blog->userblog_id );
                    $primary = $this->_get_site( $first_blog->userblog_id );
                } else $primary = $this->_get_site( $primary_blog );
            } else {
                // TODO: Review this call to add_user_to_blog too - to get here the user must have a role on this blog?
                $result = $this->_add_user_to_blog( $first_blog->userblog_id, $user_id, 'subscriber' );
                if ( ! $this->_init_error( $result ) ) {
                    $this->_update_user_meta( $user_id, 'primary_blog', $first_blog->userblog_id );
                    $primary = $first_blog;
                }
            }
            if ( ( ! is_object( $primary ) ) || ( 1 === $primary->archived || 1 === $primary->spam || 1 === $primary->deleted ) ) {
                $blogs = $this->_get_blogs_of_user( $user_id, true ); // If a user's primary blog is shut down, check their other blogs.
                $ret   = false;
                if ( is_array( $blogs ) && count( $blogs ) > 0 ) {
                    foreach ( $blogs as $blog_id => $blog ) {
                        if ( $this->_get_current_network_id() !== $blog->site_id ) {
                            continue;
                        }
                        $details = $this->_get_site( $blog_id );
                        if ( is_object( $details ) && 0 === $details->archived && 0 === $details->spam && 0 === $details->deleted ) {
                            $ret = $details;
                            if ( $this->_get_user_meta( $user_id, 'primary_blog', true ) !== $blog_id ) {
                                $this->_update_user_meta( $user_id, 'primary_blog', $blog_id );
                            }
                            if ( ! $this->_get_user_meta( $user_id, 'source_domain', true ) ) {
                                $this->_update_user_meta( $user_id, 'source_domain', $details->domain );
                            }
                            break;
                        }
                    }
                } else {
                    return false;
                }
                return $ret;
            }
            return $primary;
        }//45
        /**
         * @description Gets the number of active sites on the installation.
         * @param null $network_id
         * @return mixed
         */
        protected function _get_user_count( $network_id = null ){
            return $this->_get_network_option( $network_id, 'blog_count' );
        }//114
        /**
         * @description Gets the number of active sites on the installation.
         * @param null $network_id
         * @return mixed
         */
        protected function _get_blog_count( $network_id = null ){
            return $this->_get_network_option( $network_id, 'blog_count' );
        }//130
        /**
         * @description Gets a blog post from any site on the network.
         * @param $blog_id
         * @param $post_id
         * @return mixed
         */
        protected function _get_blog_post( $blog_id, $post_id ){
            $this->_switch_to_blog( $blog_id );
            $post = $this->_get_post( $post_id );
            $this->_restore_current_blog();
            return $post;
        }//146
        /**
         * @description Adds a user to a blog, along with specifying the user's role.
         * @param $blog_id
         * @param $user_id
         * @param $role
         * @return bool|TP_Error
         */
        protected function _add_user_to_blog( $blog_id, $user_id, $role ){
            $this->_switch_to_blog( $blog_id );
            $_user = $this->_get_user_data( $user_id );
            $user = null;
            if( $_user instanceof TP_User ){$user = $_user;}
            if ( ! $user ) {
                $this->_restore_current_blog();
                return new TP_Error( 'user_does_not_exist', $this->__( 'The requested user does not exist.' ) );
            }
            $can_add_user = $this->_apply_filters( 'can_add_user_to_blog', true, $user_id, $role, $blog_id );
            if ( true !== $can_add_user ) {
                $this->_restore_current_blog();
                if ( $this->_init_error( $can_add_user ) ) return $can_add_user;
                return new TP_Error( 'user_cannot_be_added', $this->__( 'User cannot be added to this site.' ) );
            }
            if ( ! $this->_get_user_meta( $user_id, 'primary_blog', true ) ) {
                $this->_update_user_meta( $user_id, 'primary_blog', $blog_id );
                $site = $this->_get_site( $blog_id );
                $this->_update_user_meta( $user_id, 'source_domain', $site->domain );
            }
            $user->set_role( $role );
            $this->_do_action( 'add_user_to_blog', $user_id, $role, $blog_id );
            $this->_clean_user_cache( $user_id );
            $this->_tp_cache_delete( $blog_id . '_user_count', 'blog-details' );
            $this->_restore_current_blog();
            return true;
        }//167
        /**
         * @description Removes a user from a blog.
         * @param $user_id
         * @param int $blog_id
         * @param int $reassign
         * @return bool|TP_Error
         */
        protected function _remove_user_from_blog( $user_id, $blog_id = 0, $reassign = 0 ){
            $tpdb = $this->_init_db();
            $this->_switch_to_blog( $blog_id );
            $user_id = (int) $user_id;
            $this->_do_action( 'remove_user_from_blog', $user_id, $blog_id, $reassign );
            $primary_blog = $this->_get_user_meta( $user_id, 'primary_blog', true );
            if ( $primary_blog === $blog_id ) {
                $new_id     = '';
                $new_domain = '';
                $blogs      = $this->_get_blogs_of_user( $user_id );
                foreach ( (array) $blogs as $blog ) {
                    if ( $blog->userblog_id === $blog_id ) continue;
                    $new_id     = $blog->userblog_id;
                    $new_domain = $blog->domain;
                    break;
                }
                $this->_update_user_meta( $user_id, 'primary_blog', $new_id );
                $this->_update_user_meta( $user_id, 'source_domain', $new_domain );
            }
            $_user = $this->_get_user_data( $user_id );
            $user = null;
            if( $_user instanceof TP_User ){$user = $_user;}
            if ( ! $user ) {
                $this->_restore_current_blog();
                return new TP_Error( 'user_does_not_exist', $this->__( 'That user does not exist.' ) );
            }
            $user->remove_all_caps();
            $blogs = $this->_get_blogs_of_user( $user_id );
            if ( count( $blogs ) === 0 ) {
                $this->_update_user_meta( $user_id, 'primary_blog', '' );
                $this->_update_user_meta( $user_id, 'source_domain', '' );
            }
            if ( $reassign ) {
                $reassign = (int) $reassign;
                $post_ids = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " ID FROM $tpdb->posts WHERE post_author = %d", $user_id ) );
                $link_ids = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " link_id FROM $tpdb->links WHERE link_owner = %d", $user_id ) );
                if ( ! empty( $post_ids ) ) {
                    $tpdb->query( $tpdb->prepare( TP_UPDATE . " $tpdb->posts SET post_author = %d WHERE post_author = %d", $reassign, $user_id ) );
                    array_walk( $post_ids, 'clean_post_cache' );
                }
                if ( ! empty( $link_ids ) ) {
                    $tpdb->query( $tpdb->prepare( TP_UPDATE . " $tpdb->links SET link_owner = %d WHERE link_owner = %d", $reassign, $user_id ) );
                    array_walk( $link_ids, 'clean_bookmark_cache' );
                }
            }
            $this->_restore_current_blog();
            return true;
        }//245
        /**
         * @description Gets the permalink for a post on another blog.
         * @param $blog_id
         * @param $post_id
         * @return mixed
         */
        protected function _get_blog_permalink( $blog_id, $post_id ){
            $this->_switch_to_blog( $blog_id );
            $link = $this->_get_permalink( $post_id );
            $this->_restore_current_blog();
            return $link;
        }//328
        /**
         * @description Gets a blog's numeric ID from its URL.
         * @param $domain
         * @param string $path
         * @return int|mixed
         */
        protected function _get_blog_id_from_url( $domain, $path = '/' ){
            $domain = strtolower( $domain );
            $path   = strtolower( $path );
            $id     = $this->_tp_cache_get( md5( $domain . $path ), 'blog-id-cache' );
            if ( -1 === $id ) return 0;
            elseif ( $id ) return (int) $id;
            $args   = ['domain' => $domain,'path' => $path,'fields' => 'ids','number' => 1,'update_site_meta_cache' => false,];
            $result = $this->_get_sites( $args );
            $id     = array_shift( $result );
            if ( ! $id ) {
                $this->_tp_cache_set( md5( $domain . $path ), -1, 'blog-id-cache' );
                return 0;
            }
            $this->_tp_cache_set( md5( $domain . $path ), $id, 'blog-id-cache' );
            return $id;
        }//352
        /**
         * @description Checks an email address against a list of banned domains.
         * @param $user_email
         * @return mixed
         */
        protected function _is_email_address_unsafe( $user_email ){
            $banned_names = $this->_get_site_option( 'banned_email_domains' );
            if ( $banned_names && ! is_array( $banned_names ) )
                $banned_names = explode( "\n", $banned_names );
            $is_email_address_unsafe = false;
            if ( $banned_names && is_array( $banned_names ) && false !== strpos( $user_email, '@', 1 ) ) {
                $banned_names     = array_map( 'strtolower', $banned_names );
                $normalized_email = strtolower( $user_email );
                @list($email_domain ) = explode( '@', $normalized_email );
                foreach ( $banned_names as $banned_domain ) {//not used  $email_local_part,
                    if ( ! $banned_domain )  continue;
                    if ( $email_domain === $banned_domain ) {
                        $is_email_address_unsafe = true;
                        break;
                    }
                    $dotted_domain = ".$banned_domain";
                    if ( substr( $normalized_email, -strlen( $dotted_domain ) ) === $dotted_domain ) {
                        $is_email_address_unsafe = true;
                        break;
                    }
                }
            }
            return $this->_apply_filters( 'is_email_address_unsafe', $is_email_address_unsafe, $user_email );
        }//400
    }
}else die;