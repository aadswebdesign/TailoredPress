<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-2-2022
 * Time: 19:51
 */
namespace TP_Core\Traits\Capabilities;
use TP_Core\Libs\Users\TP_Roles;
use TP_Core\Traits\Inits\_init_post_type;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _capability_01 {
        use _init_user;
        use _init_error;
        use _init_post;
        use _init_post_type;
        /**
         * @description Maps a capability to the primitive capabilities required of the given user to
         * @description . satisfy the capability being checked.
         * @param $cap
         * @param $user_id
         * @param array ...$args
         * @return mixed
         */
        protected function _map_meta_cap( $cap, $user_id, ...$args ){
            $caps = [];
            $tp_post_types = $this->_init_post_types();
            switch ( $cap ) {
                case 'remove_user':
                    // In multisite the user must be a super admin to remove themselves.
                    if ( isset( $args[0] ) && $user_id === $args[0] && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = 'remove_users';
                    break;
                case 'promote_user':
                case 'add_users':
                    $caps[] = 'promote_users';
                    break;
                case 'edit_user':
                case 'edit_users':
                    if ( 'edit_user' === $cap && isset( $args[0] ) && $user_id === $args[0] ) break;
                    // In multisite the user must have manage_network_users caps. If editing a super admin, the user must be a super admin.
                    if ( $this->_is_multisite() && ( ( ! $this->_is_super_admin( $user_id ) && 'edit_user' === $cap && $this->_is_super_admin( $args[0] ) ) || ! $this->_user_can( $user_id, 'manage_network_users' ) ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = 'edit_users'; // edit_user maps to edit_users.
                    break;
                case 'delete_post':
                case 'delete_page':
                    $post = $this->_get_post( $args[0] );
                    if ( ! $post ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    if ( 'revision' === $post->post_type ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    if ( ( $this->_get_option( 'page_for_posts' ) === $post->ID ) || ( $this->_get_option( 'page_on_front' ) === $post->ID ) ) {
                        $caps[] = 'manage_options';
                        break;
                    }
                    $post_type = $this->_get_post_type_object( $post->post_type );
                    if ( ! $post_type ) {
                        /* translators: 1: Post type, 2: Capability name. */
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The post type %1$s is not registered, so it may not be reliable to check the capability "%2$s" against a post of that type.' ), $post->post_type, $cap ), '0.0.1' );
                        $caps[] = 'edit_others_posts';
                        break;
                    }
                    // If the post author is set and the user is the author...
                    if ( $post->post_author && $user_id === $post->post_author ) {
                        // If the post is published or scheduled...
                        if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
                            $caps[] = $post_type->cap->delete_published_posts;
                        } elseif ( 'trash' === $post->post_status ) {
                            $status = $this->_get_post_meta( $post->ID, '_tp_trash_meta_status', true );
                            if ( in_array( $status, array( 'publish', 'future' ), true ) ) {
                                $caps[] = $post_type->cap->delete_published_posts;
                            } else {
                                $caps[] = $post_type->cap->delete_posts;
                            }
                        } else {
                            // If the post is draft...
                            $caps[] = $post_type->cap->delete_posts;
                        }
                    } else {
                        // The user is trying to edit someone else's post.
                        $caps[] = $post_type->cap->delete_others_posts;
                        // The post is published or scheduled, extra cap required.
                        if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
                            $caps[] = $post_type->cap->delete_published_posts;
                        } elseif ( 'private' === $post->post_status ) {
                            $caps[] = $post_type->cap->delete_private_posts;
                        }
                    }
                    /*
                     * Setting the privacy policy page requires `manage_privacy_options`,
                     * so deleting it should require that too.
                     */
                    if ( (int) $this->_get_option( 'wp_page_for_privacy_policy' ) === $post->ID ) {
                        $caps = array_merge( $caps, $this->_map_meta_cap( 'manage_privacy_options', $user_id ) );
                    }
                    break;
                case 'edit_post':
                case 'edit_page':
                    $post = $this->_get_post( $args[0] );
                    if ( ! $post ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    if ( 'revision' === $post->post_type ) {
                        $post = $this->_get_post( $post->post_parent );
                        if ( ! $post ) {
                            $caps[] = 'do_not_allow';
                            break;
                        }
                    }
                    $post_type = $this->_get_post_type_object( $post->post_type );
                    if ( ! $post_type ) {
                        /* translators: 1: Post type, 2: Capability name. */
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The post type %1$s is not registered, so it may not be reliable to check the capability "%2$s" against a post of that type.' ), $post->post_type, $cap ), '4.4.0' );
                        $caps[] = 'edit_others_posts';
                        break;
                    }
                    if ( ! $post_type->map_meta_cap ) {
                        $caps[] = $post_type->cap->$cap;
                        // Prior to 3.1 we would re-call map_meta_cap here.
                        if ( 'edit_post' === $cap ) $cap = $post_type->cap->$cap;
                        break;
                    }
                    if ( $post->post_author && $user_id === $post->post_author ) {
                        // If the post is published or scheduled...
                        if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
                            $caps[] = $post_type->cap->edit_published_posts;
                        } elseif ( 'trash' === $post->post_status ) {
                            $status = $this->_get_post_meta( $post->ID, '_tp_trash_meta_status', true );
                            if ( in_array( $status, array( 'publish', 'future' ), true ) )
                                $caps[] = $post_type->cap->edit_published_posts;
                            else  $caps[] = $post_type->cap->edit_posts;
                        } else $caps[] = $post_type->cap->edit_posts;// If the post is draft...
                    } else {
                        // The user is trying to edit someone else's post.
                        $caps[] = $post_type->cap->edit_others_posts;
                        // The post is published or scheduled, extra cap required.
                        if ( in_array( $post->post_status, array( 'publish', 'future' ), true ) )
                            $caps[] = $post_type->cap->edit_published_posts;
                        elseif ( 'private' === $post->post_status )
                            $caps[] = $post_type->cap->edit_private_posts;
                    }
                    if ( (int) $this->_get_option( 'tp_page_for_privacy_policy' ) === $post->ID )
                        $caps = array_merge( $caps, $this->_map_meta_cap( 'manage_privacy_options', $user_id ) );
                    break;
                case 'read_post':
                case 'read_page':
                    $post = $this->_get_post( $args[0] );
                    if ( ! $post ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    if ( 'revision' === $post->post_type ) {
                        $post = $this->_get_post( $post->post_parent );
                        if ( ! $post ) {
                            $caps[] = 'do_not_allow';
                            break;
                        }
                    }
                    $post_type = $this->_get_post_type_object( $post->post_type );
                    if ( ! $post_type ) {
                        /* translators: 1: Post type, 2: Capability name. */
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The post type %1$s is not registered, so it may not be reliable to check the capability "%2$s" against a post of that type.' ), $post->post_type, $cap ), '0.0.1' );
                        $caps[] = 'edit_others_posts';
                        break;
                    }
                    $status_obj = $this->_get_post_status_object( $this->_get_post_status( $post ) );
                    if ( ! $status_obj ) {
                        /* translators: 1: Post status, 2: Capability name. */
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The post status %1$s is not registered, so it may not be reliable to check the capability "%2$s" against a post with that status.' ), $this->_get_post_status( $post ), $cap ), '0.0.1' );
                        $caps[] = 'edit_others_posts';
                        break;
                    }
                    if ( $status_obj->public ) {
                        $caps[] = $post_type->cap->read;
                        break;
                    }
                    if ( $post->post_author && $user_id === $post->post_author )
                        $caps[] = $post_type->cap->read;
                    elseif ( $status_obj->private ) $caps[] = $post_type->cap->read_private_posts;
                    else $caps = $this->_map_meta_cap( 'edit_post', $user_id, $post->ID );
                    break;
                case 'publish_post':
                    $post = $this->_get_post( $args[0] );
                    if ( ! $post ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    $post_type = $this->_get_post_type_object( $post->post_type );
                    if ( ! $post_type ) {
                        /* translators: 1: Post type, 2: Capability name. */
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The post type %1$s is not registered, so it may not be reliable to check the capability "%2$s" against a post of that type.' ), $post->post_type, $cap ), '4.4.0' );
                        $caps[] = 'edit_others_posts';
                        break;
                    }
                    $caps[] = $post_type->cap->publish_posts;
                    break;
                case 'edit_post_meta':
                case 'delete_post_meta':
                case 'add_post_meta':
                case 'edit_comment_meta':
                case 'delete_comment_meta':
                case 'add_comment_meta':
                case 'edit_term_meta':
                case 'delete_term_meta':
                case 'add_term_meta':
                case 'edit_user_meta':
                case 'delete_user_meta':
                case 'add_user_meta':
                    $object_type = explode( '_', $cap )[1];
                    $object_id   = (int) $args[0];
                    $object_subtype = $this->_get_object_subtype( $object_type, $object_id );
                    if ( empty( $object_subtype ) ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    $caps = $this->_map_meta_cap( "edit_{$object_type}", $user_id, $object_id );
                    $meta_key = $args[1] ?? false;
                    if ( $meta_key ) {
                        $allowed = ! $this->_is_protected_meta( $meta_key, $object_type );
                        if ( ! empty( $object_subtype ) && $this->_has_filter( "auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}" ) )
                            $allowed = $this->_apply_filters( "auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $allowed, $meta_key, $object_id, $user_id, $cap, $caps );
                        else $allowed = $this->_apply_filters( "auth_{$object_type}_meta_{$meta_key}", $allowed, $meta_key, $object_id, $user_id, $cap, $caps );
                        if ( ! $allowed ) $caps[] = $cap;
                    }
                    break;
                case 'edit_comment':
                    $comment = $this->_get_comment( $args[0] );
                    if ( ! $comment ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    $post = $this->_get_post( $comment->comment_post_ID );
                    if ( $post ) $caps = $this->_map_meta_cap( 'edit_post', $user_id, $post->ID );
                    else $caps = $this->_map_meta_cap( 'edit_posts', $user_id );
                    break;
                case 'unfiltered_upload':
                    if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) && ALLOW_UNFILTERED_UPLOADS && ( ! $this->_is_multisite() || $this->_is_super_admin( $user_id ) ) )
                        $caps[] = $cap;
                    else $caps[] = 'do_not_allow';
                    break;
                case 'edit_css':
                case 'unfiltered_html':
                    // Disallow unfiltered_html for all users, even admins and super admins.
                    if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML )
                        $caps[] = 'do_not_allow';
                    elseif ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = 'unfiltered_html';
                    break;
                case 'edit_files':
                case 'edit_themes':
                    // Disallow the file editors.
                    if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT )
                        $caps[] = 'do_not_allow';
                    elseif ( ! $this->_tp_is_file_mod_allowed( 'capability_edit_themes' ) )
                        $caps[] = 'do_not_allow';
                    elseif ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = $cap;
                    break;
                case 'update_themes':
                case 'delete_themes':
                case 'install_themes':
                case 'upload_themes':
                case 'update_core':
                    if ( ! $this->_tp_is_file_mod_allowed( 'capability_update_core' ) )
                        $caps[] = 'do_not_allow';
                    elseif ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    elseif ( 'upload_themes' === $cap )
                        $caps[] = 'install_themes';
                    else  $caps[] = $cap;
                    break;
                case 'install_languages':
                case 'update_languages':
                    if ( ! $this->_tp_is_file_mod_allowed( 'can_install_language_pack' ) )
                        $caps[] = 'do_not_allow';
                    elseif ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = 'install_languages';
                    break;
                case 'resume_theme':
                    $caps[] = 'resume_themes';
                    break;
                case 'delete_user':
                case 'delete_users':
                    // If multisite only super admins can delete users.
                    if ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else $caps[] = 'delete_users'; // delete_user maps to delete_users.
                    break;
                case 'create_users':
                    if ( ! $this->_is_multisite() ) $caps[] = $cap;
                    elseif ( $this->_is_super_admin( $user_id ) || $this->_get_site_option( 'add_new_users' ) )
                        $caps[] = $cap;
                    else $caps[] = 'do_not_allow';
                    break;
                case 'manage_links':
                    if ( $this->_get_option( 'link_manager_enabled' ) )
                        $caps[] = $cap;
                    else $caps[] = 'do_not_allow';
                    break;
                case 'customize':
                    $caps[] = 'edit_theme_options';
                    break;
                case 'delete_site':
                    if ( $this->_is_multisite() )
                        $caps[] = 'manage_options';
                    else  $caps[] = 'do_not_allow';
                    break;
                case 'edit_term':
                case 'delete_term':
                case 'assign_term':
                    $term_id = (int) $args[0];
                    $term    = $this->_get_term( $term_id );
                    if ( ! $term || $this->_init_error( $term ) ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    $tax = $this->_get_taxonomy( $term->taxonomy );
                    if ( ! $tax ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    if ( 'delete_term' === $cap
                        && ( $this->_get_option( 'default_' . $term->taxonomy ) === $term->term_id
                            || $this->_get_option( 'default_term_' . $term->taxonomy ) === $term->term_id )
                    ) {
                        $caps[] = 'do_not_allow';
                        break;
                    }
                    $taxonomy_cap = $cap . 's';
                    $caps = $this->_map_meta_cap( $tax->cap->$taxonomy_cap, $user_id, $term_id );
                    break;
                case 'manage_post_tags':
                case 'edit_categories':
                case 'edit_post_tags':
                case 'delete_categories':
                case 'delete_post_tags':
                    $caps[] = 'manage_categories';
                    break;
                case 'assign_categories':
                case 'assign_post_tags':
                    $caps[] = 'edit_posts';
                    break;
                case 'create_sites':
                case 'delete_sites':
                case 'manage_network':
                case 'manage_sites':
                case 'manage_network_users':
                case 'manage_network_plugins':
                case 'manage_network_themes':
                case 'manage_network_options':
                case 'upgrade_network':
                    $caps[] = $cap;
                    break;
                case 'setup_network':
                    if ( $this->_is_multisite() )
                        $caps[] = 'manage_network_options';
                    else  $caps[] = 'manage_options';
                    break;
                case 'update_php':
                    if ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else  $caps[] = 'update_core';
                    break;
                case 'update_https':
                    if ( $this->_is_multisite() && ! $this->_is_super_admin( $user_id ) )
                        $caps[] = 'do_not_allow';
                    else {
                        $caps[] = 'manage_options';
                        $caps[] = 'update_core';
                    }
                    break;
                case 'export_others_personal_data':
                case 'erase_others_personal_data':
                case 'manage_privacy_options':
                    $caps[] = $this->_is_multisite() ? 'manage_network' : 'manage_options';
                    break;
                case 'create_app_password':
                case 'list_app_passwords':
                case 'read_app_password':
                case 'edit_app_password':
                case 'delete_app_passwords':
                case 'delete_app_password':
                    $caps = $this->_map_meta_cap( 'edit_user', $user_id, $args[0] );
                    break;
                default:
                    if ( isset( $tp_post_types->post_type_meta_caps[ $cap ] ) )//todo
                        return $this->_map_meta_cap( $tp_post_types->post_type_meta_caps[ $cap ], $user_id, ...$args );
                    $block_caps = array(
                        'edit_blocks','edit_others_blocks','publish_blocks','read_private_blocks','delete_blocks','delete_private_blocks',
                        'delete_published_blocks','delete_others_blocks','edit_private_blocks','edit_published_blocks',
                    );
                    if ( in_array( $cap, $block_caps, true ) )
                        $cap = str_replace( '_blocks', '_posts', $cap );
                    $caps[] = $cap;
            }
            return $this->_apply_filters( 'map_meta_cap', $caps, $cap, $user_id, $args );
        }//44
        /**
         * @description Returns whether the current user has the specified capability.
         * @param $capability
         * @param array ...$args
         * @return string
         */
        protected function _current_user_can( $capability, ...$args ):string{
            return $this->_user_can( $this->_tp_get_user_current(), $capability, ...$args );
        }//692
        /**
         * @description Returns whether the current user has the specified capability for a given site.
         * @param $blog_id
         * @param $capability
         * @param array ...$args
         * @return string
         */
        protected function _current_user_can_for_blog( $blog_id, $capability, ...$args ):string{
            $switched = $this->_is_multisite() ? $this->_switch_to_blog( $blog_id ) : false;
            $can = $this->_current_user_can( $capability, ...$args );
            if ( $switched ) $this->_restore_current_blog();
            return $can;
        }//719
        /**
         * @param $post
         * @param $capability
         * @param array ...$args
         * @return bool
         */
        protected function _author_can( $post, $capability, ...$args ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $_author = $this->_get_user_data( $post->post_author );
            $author = null;
            if($_author instanceof TP_User){
                $author = $_author;
            }
            if ( ! $author ) return false;
            return $author->has_cap( $capability, ...$args );
        }//753
        /**
         * @description Returns whether a particular user has the specified capability.
         * @param $user
         * @param $capability
         * @param array ...$args
         * @return bool
         */
        protected function _user_can( $user, $capability, ...$args ): bool{
            if ( ! is_object( $user ) ) $user = $this->_get_user_data( $user );
            if ( empty( $user ) ) {
                $user = $this->_init_user(0);
                $user->init( new \stdClass );
            }
            return $user->has_cap( $capability, ...$args );
        }//790
        protected function _tp_roles():TP_Roles{
            return $this->_init_roles();
        }//813
        /**
         * @description Retrieve role object.
         * @param $role
         * @return mixed|null
         */
        protected function _get_role( $role ){
            return $this->_init_roles()->get_role( $role );
        }//830
        /**
         * @description Add role, if it does not exist.
         * @param $role
         * @param $display_name
         * @param array $capabilities
         * @return mixed|null
         */
        protected function _add_role( $role, $display_name, $capabilities = [] ){
            if ( empty( $role ) ) return null;
            return $this->_init_roles()->add_role( $role, $display_name, $capabilities );
        }//845
        /**
         * @description Remove role, if it exists
         * @param $role
         */
        protected function _remove_role( $role ):void{
            $this->_init_roles()->remove_role($role);
        }//859
        /**
         * @description Retrieve a list of super admins.
         * @return mixed
         */
        protected function _get_super_admins(){
            if (isset( $this->__super_admins )) return $this->__super_admins;
            else return $this->_get_site_option( 'site_admins', array( 'admin' ) );
        }//872
    }
}else die;