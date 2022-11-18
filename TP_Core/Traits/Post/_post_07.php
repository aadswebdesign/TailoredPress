<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    trait _post_07{
        use _init_db;
        /**
         * @description Reset the page_on_front, show_on_front, and page_for_post settings when
         * @param $post_id
         */
        protected function _reset_front_page_settings_for_post( $post_id ):void{
            $post = $this->_get_post( $post_id );
            if ( 'page' === $post->post_type ) {
                if ( $this->_get_option( 'page_on_front' ) === $post->ID ) {
                    $this->_update_option( 'show_on_front', 'posts' );
                    $this->_update_option( 'page_on_front', 0 );
                }
                if ( $this->_get_option( 'page_for_posts' ) === $post->ID ) $this->_update_option( 'page_for_posts', 0 );
            }
            $this->_unstick_post( $post->ID );
        }//3516
        /**
         * @description Move a post or page to the Trash
         * @param int $post_id
         * @return string
         */
        protected function _tp_trash_post( $post_id = 0 ):string{
            if ( ! EMPTY_TRASH_DAYS ) return $this->_tp_delete_post( $post_id, true );
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return $post;
            if ( 'trash' === $post->post_status ) return false;
            $check = $this->_apply_filters( 'pre_trash_post', null, $post );
            if ( null !== $check ) return $check;
            $this->_do_action( 'wp_trash_post', $post_id );
            $this->_add_post_meta( $post_id, '_tp_trash_meta_status', $post->post_status );
            $this->_add_post_meta( $post_id, '_tp_trash_meta_time', time() );
            $post_updated = $this->_tp_update_post(
                ['ID' => $post_id,'post_status' => 'trash',]
            );
            if ( ! $post_updated ) return false;
            $this->_tp_trash_post_comments( $post_id );
            $this->_do_action( 'trashed_post', $post_id );
            return $post;
        }//3549
        /**
         * @description Restores a post from the Trash.
         * @param int $post_id
         * @return bool
         */
        protected function _tp_untrash_post( $post_id = 0 ):bool{
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return $post;
            $post_id = $post->ID;
            if ( 'trash' !== $post->post_status ) return false;
            $previous_status = $this->_get_post_meta( $post_id, '_tp_trash_meta_status', true );
            $check = $this->_apply_filters( 'pre_untrash_post', null, $post, $previous_status );
            if ( null !== $check ) return $check;
            $this->_do_action( 'untrash_post', $post_id, $previous_status );
            $new_status = ( 'attachment' === $post->post_type ) ? 'inherit' : 'draft';
            $post_status = $this->_apply_filters( 'tp_untrash_post_status', $new_status, $post_id, $previous_status );
            $this->_delete_post_meta( $post_id, '_tp_trash_meta_status' );
            $this->_delete_post_meta( $post_id, '_tp_trash_meta_time' );
            $post_updated = $this->_tp_update_post(['ID'=> $post_id,'post_status' => $post_status,]);
            if ( ! $post_updated ) return false;
            $this->_tp_untrash_post_comments( $post_id );
            $this->_do_action( 'untrashed_post', $post_id, $previous_status );
            return $post;
        }//3625
        /**
         * @description Moves comments for a post to the Trash.
         * @param null $post
         * @return bool
         */
        protected function _tp_trash_post_comments( $post = null ):bool{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $post_id = $post->ID;
            $this->_do_action( 'trash_post_comments', $post_id );
            $comments = $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " comment_ID, comment_approved FROM $this->tpdb->comments WHERE comment_post_ID = %d", $post_id ) );
            if ( ! $comments ) return false;
            $statuses = array();
            foreach ( $comments as $comment )  $statuses[ $comment->comment_ID ] = $comment->comment_approved;
            $this->_add_post_meta( $post_id, '_tp_trash_meta_comments_status', $statuses );
            $result = $this->tpdb->update( $this->tpdb->comments, array( 'comment_approved' => 'post-trashed' ), array( 'comment_post_ID' => $post_id ) );
            $this->_clean_comment_cache( array_keys( $statuses ) );
            $this->_do_action( 'trashed_post_comments', $post_id, $statuses );
            return $result;
        }//3725
        /**
         * @description  Restore comments for a post from the Trash.
         * @param null $post
         * @return bool
         */
        protected function _tp_untrash_post_comments( $post = null ):bool{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $post_id = $post->ID;
            $statuses = $this->_get_post_meta( $post_id, '_tp_trash_meta_comments_status', true );
            if ( ! $statuses ) return true;
            $this->_do_action( 'untrash_post_comments', $post_id );
            $group_by_status = [];
            foreach ( $statuses as $comment_id => $comment_status ) $group_by_status[ $comment_status ][] = $comment_id;
            foreach ( $group_by_status as $status => $comments ) {
                if ( 'post-trashed' === $status ) $status = '0';
                $comments_in = implode( ', ', array_map( 'intval', $comments ) );
                $this->tpdb->query( $this->tpdb->prepare( TP_UPDATE . " $this->tpdb->comments SET comment_approved = %s WHERE comment_ID IN ($comments_in)", $status ) );
            }
            $this->_clean_comment_cache( array_keys( $statuses ) );
            $this->_delete_post_meta( $post_id, '_tp_trash_meta_comments_status' );
            $this->_do_action( 'untrashed_post_comments', $post_id );
            return true;
        }//3786
        /**
         * @description Retrieve the list of categories for a post.
         * @param int $post_id
         * @param array $args
         * @return mixed
         */
        protected function _tp_get_post_categories( $post_id = 0, ...$args){
            $post_id = (int) $post_id;
            $defaults = array( 'fields' => 'ids' );
            $args     = $this->_tp_parse_args( $args, $defaults );
            return $this->_tp_get_object_terms( $post_id, 'category', $args );
        }//3860
        /**
         * @description Retrieve the tags for a post.
         * @param int $post_id
         * @param array $args
         * @return string
         */
        protected function _tp_get_post_tags( $post_id = 0, ...$args):string{
            return $this->_tp_get_post_terms( $post_id, 'post_tag', $args );
        }//3886
        /**
         * @description Retrieves the terms for a post.
         * @param int $post_id
         * @param string $taxonomy
         * @param array $args
         * @return mixed
         */
        protected function _tp_get_post_terms( $post_id = 0, $taxonomy = 'post_tag', $args = [] ){
            $post_id = (int) $post_id;
            $defaults = ['fields' => 'all'];
            $args     = $this->_tp_parse_args( $args, $defaults );
            return $this->_tp_get_object_terms( $post_id, $taxonomy, $args );
        }//3907
        /**
         * @description Retrieve a number of recent posts.
         * @param array $args
         * @return bool
         */
        protected function _tp_get_recent_posts( ...$args):bool{
            $defaults = ['numberposts' => 10,'offset' => 0,'category' => 0,'orderby' => 'post_date','order' => 'DESC',
                'include' => '','exclude' => '','meta_key' => '','meta_value' => '','post_type' => 'post',
                'post_status' => 'draft, publish, future, pending, private','suppress_filters' => true,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $results = $this->_get_posts( $parsed_args );
            return $results ?: false;
        }//3932
        /**
         * @description Insert or update a post.
         * @param $postarr
         * @param bool $tp_error
         * @param bool $fire_after_hooks
         * @return int
         */
        protected function _tp_insert_post( $postarr, $tp_error = false, $fire_after_hooks = true ):int{
            $this->tpdb = $this->_init_db();
            $unsanitized_postarr = $postarr;
            $user_id = $this->_get_current_user_id();
            $defaults = ['post_author' => $user_id,'post_content' => '','post_content_filtered' => '',
                'post_title' => '','post_excerpt' => '','post_status' => 'draft','post_type' => 'post',
                'comment_status' => '','ping_status' => '','post_password' => '','to_ping' => '',
                'pinged' => '','post_parent' => 0,'menu_order' => 0,'guid' => '','import_id' => 0,
                'context' => '','post_date' => '','post_date_gmt' => '',];
            $postarr = $this->_tp_parse_args( $postarr, $defaults );
            unset( $postarr['filter'] );
            $postarr = $this->_sanitize_post( $postarr, 'db' );
            $post_ID = 0;
            $update  = false;
            $guid    = $postarr['guid'];
            if ( ! empty( $postarr['ID'] ) ) {
                $update = true;
                $post_ID     = $postarr['ID'];
                $post_before = $this->_get_post( $post_ID );
                if ( is_null( $post_before ) ) {
                    if ( $tp_error ) return (int) new TP_Error( 'invalid_post', $this->__( 'Invalid post ID.' ) );
                    return 0;
                }
                $guid = $this->_get_post_field( 'guid', $post_ID );
                $previous_status = $this->_get_post_field( 'post_status', $post_ID );
            } else {
                $previous_status = 'new';
                $post_before     = null;
            }
            $post_type = empty( $postarr['post_type'] ) ? 'post' : $postarr['post_type'];
            $post_title   = $postarr['post_title'];
            $post_content = $postarr['post_content'];
            $post_excerpt = $postarr['post_excerpt'];
            if ( isset( $postarr['post_name'] ) ) $post_name = $postarr['post_name'];
            elseif ( $update ) $post_name = $post_before->post_name;
            $maybe_empty = 'attachment' !== $post_type
                && ! $post_content && ! $post_title && ! $post_excerpt
                && $this->_post_type_supports( $post_type, 'editor' )
                && $this->_post_type_supports( $post_type, 'title' )
                && $this->_post_type_supports( $post_type, 'excerpt' );
            if ( $this->_apply_filters( 'tp_insert_post_empty_content', $maybe_empty, $postarr ) ) {
                if ( $tp_error ) return (int) new TP_Error( 'empty_content', $this->__( 'Content, title, and excerpt are empty.' ) );
                else return 0;
            }
            $post_status = empty( $postarr['post_status'] ) ? 'draft' : $postarr['post_status'];
            if ( 'attachment' === $post_type && ! in_array( $post_status, array( 'inherit', 'private', 'trash', 'auto-draft' ), true ) )
                $post_status = 'inherit';
            if ( ! empty( $postarr['post_category'] ) ) $post_category = array_filter( $postarr['post_category'] );
            if ( empty( $post_category ) || ! is_array( $post_category ) || 0 === count( $post_category )) {
                if ( 'post' === $post_type && 'auto-draft' !== $post_status )
                    $post_category = [$this->_get_option( 'default_category' )];
                else $post_category = [];
            }
            $post_type_object = $this->_get_post_type_object( $post_type );
            if ( ! $update && 'pending' === $post_status && ! $this->_current_user_can( $post_type_object->cap->publish_posts ) )
                $post_name = '';
            elseif ( $update && 'pending' === $post_status && ! $this->_current_user_can( 'publish_post', $post_ID ) )
                $post_name = '';
            if ( empty( $post_name ) ) {
                if ( ! in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ), true ) )
                    $post_name = $this->_sanitize_title( $post_title );
                else $post_name = '';
            } else {
                $check_name = $this->_sanitize_title( $post_name, '', 'old-save' );
                if ( $update && ($this->_get_post_field( 'post_name', $post_ID ) === $check_name) && strtolower( urlencode( $post_name ) ) === $check_name)
                    $post_name = $check_name;
                else  $post_name = $this->_sanitize_title( $post_name );
            }
            $post_date = $this->_tp_resolve_post_date( $postarr['post_date'], $postarr['post_date_gmt'] );
            if ( ! $post_date ) {
                if ( $tp_error ) return (int) new TP_Error( 'invalid_date', $this->__( 'Invalid date.' ) );
                else return 0;
            }
            if ( empty( $postarr['post_date_gmt'] ) || '0000-00-00 00:00:00' === $postarr['post_date_gmt'] ) {
                if ( ! in_array( $post_status, $this->_get_post_stati( array( 'date_floating' => true ) ), true ) )
                    $post_date_gmt = $this->_get_gmt_from_date( $post_date );
                else $post_date_gmt = '0000-00-00 00:00:00';
            } else {
                $post_date_gmt = $postarr['post_date_gmt'];
            }
            if ( $update || '0000-00-00 00:00:00' === $post_date ) {
                $post_modified     = $this->_current_time( 'mysql' );
                $post_modified_gmt = $this->_current_time( 'mysql', 1 );
            } else {
                $post_modified     = $post_date;
                $post_modified_gmt = $post_date_gmt;
            }
            if ( 'attachment' !== $post_type ) {
                $now = gmdate( 'Y-m-d H:i:s' );
                if ( 'publish' === $post_status ) {
                    if ( strtotime( $post_date_gmt ) - strtotime( $now ) >= MINUTE_IN_SECONDS )
                        $post_status = 'future';
                } elseif ( 'future' === $post_status ) {
                    if ( strtotime( $post_date_gmt ) - strtotime( $now ) < MINUTE_IN_SECONDS )
                        $post_status = 'publish';
                }
            }
            if ( empty( $postarr['comment_status'] ) ) {
                if ( $update ) $comment_status = 'closed';
                else $comment_status = $this->_get_default_comment_status( $post_type );
            } else  $comment_status = $postarr['comment_status'];
            $post_content_filtered = $postarr['post_content_filtered'];
            $post_author           = $postarr['post_author'] ?? $user_id;
            $ping_status           = empty( $postarr['ping_status'] ) ? $this->_get_default_comment_status( $post_type, 'pingback' ) : $postarr['ping_status'];
            $to_ping               = isset( $postarr['to_ping'] ) ? $this->_sanitize_trackback_urls( $postarr['to_ping'] ) : '';
            $pinged                = $postarr['pinged'] ?? '';
            $import_id             = $postarr['import_id'] ?? 0;
            if ( isset( $postarr['menu_order'] ) ) $menu_order = (int) $postarr['menu_order'];
            else $menu_order = 0;
            $post_password = $postarr['post_password'] ?? '';
            if ( 'private' === $post_status ) $post_password = '';
            if ( isset( $postarr['post_parent'] ) ) $post_parent = (int) $postarr['post_parent'];
            else $post_parent = 0;
            $new_postarr = array_merge(
                array('ID' => $post_ID,),
                compact( array_diff( array_keys( $defaults ), array( 'context', 'filter' ) ) )
            );
            $post_parent = $this->_apply_filters( 'tp_insert_post_parent', $post_parent, $post_ID, $new_postarr, $postarr );
            if ( 'trash' === $previous_status && 'trash' !== $post_status ) {
                $desired_post_slug = $this->_get_post_meta( $post_ID, '_tp_desired_post_slug', true );
                if ( $desired_post_slug ) {
                    $this->_delete_post_meta( $post_ID, '_tp_desired_post_slug' );
                    $post_name = $desired_post_slug;
                }
            }
            if ( 'trash' !== $post_status && $post_name ) {
                $add_trashed_suffix = $this->_apply_filters( 'add_trashed_suffix_to_trashed_posts', true, $post_name, $post_ID );
                if ( $add_trashed_suffix ) $this->_tp_add_trashed_suffix_to_post_name_for_trashed_posts( $post_name, $post_ID );
            }
            if ( 'trash' === $post_status && 'trash' !== $previous_status && 'new' !== $previous_status )
                $post_name = $this->_tp_add_trashed_suffix_to_post_name_for_post( $post_ID );
            $post_name = $this->_tp_unique_post_slug( $post_name, $post_ID, $post_status, $post_type, $post_parent );
            $post_mime_type = $postarr['post_mime_type'] ?? '';
            $data = compact( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'guid' );
            if ( 'attachment' === $post_type )
                $data = $this->_apply_filters( 'tp_insert_attachment_data', $data, $postarr, $unsanitized_postarr );
            else $data = $this->_apply_filters( 'tp_insert_post_data', $data, $postarr, $unsanitized_postarr );
            $data  = $this->_tp_unslash( $data );
            $where = array( 'ID' => $post_ID );
            if ( $update ) {
                $this->_do_action( 'pre_post_update', $post_ID, $data );
                if ( false === $this->tpdb->update( $this->tpdb->posts, $data, $where ) ) {
                    if ( $tp_error ) {
                        if ( 'attachment' === $post_type )
                            $message = $this->__( 'Could not update attachment in the database.' );
                        else $message = $this->__( 'Could not update post in the database.' );
                        return (int) new TP_Error( 'db_update_error', $message, $this->tpdb->last_error );
                    } else return 0;
                }
            } else {
                if ( ! empty( $import_id ) ) {
                    $import_id_value = $import_id;
                    if ( ! $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE ID = %d", $import_id_value ) ) )
                        $data['ID'] = $import_id_value;
                }
                if ( false === $this->tpdb->insert( $this->tpdb->posts, $data ) ) {
                    if ( $tp_error ) {
                        if ( 'attachment' === $post_type ) $message = $this->__( 'Could not insert attachment into the database.' );
                        else  $message = $this->__( 'Could not insert post into the database.' );
                        return (int) new TP_Error( 'db_insert_error', $message, $this->tpdb->last_error );
                    } else return 0;
                }
                $post_ID = (int) $this->tpdb->insert_id;
                $where = ['ID' => $post_ID];
            }
            if ( empty( $data['post_name'] ) && ! in_array( $data['post_status'], array( 'draft', 'pending', 'auto-draft' ), true ) ) {
                $data['post_name'] = $this->_tp_unique_post_slug( $this->_sanitize_title( $data['post_title'], $post_ID ), $post_ID, $data['post_status'], $post_type, $post_parent );
                $this->tpdb->update( $this->tpdb->posts, array( 'post_name' => $data['post_name'] ), $where );
                $this->_clean_post_cache( $post_ID );
            }
            if ( $this->_is_object_in_taxonomy( $post_type, 'category' ) )
                $this->_tp_set_post_categories( $post_ID, $post_category );
            if ( isset( $postarr['tags_input'] ) && $this->_is_object_in_taxonomy( $post_type, 'post_tag' ) )
                $this->_tp_set_post_tags( $post_ID, $postarr['tags_input'] );
            if ( 'auto-draft' !== $post_status ) {
                foreach ( $this->_get_object_taxonomies( $post_type, 'object' ) as $taxonomy => $tax_object ) {
                    if ( ! empty( $tax_object->default_term ) ) {
                        if ( isset( $postarr['tax_input'][ $taxonomy ] ) && is_array( $postarr['tax_input'][ $taxonomy ] ) )
                            $postarr['tax_input'][ $taxonomy ] = array_filter( $postarr['tax_input'][ $taxonomy ] );
                        $terms = $this->_tp_get_object_terms( $post_ID, $taxonomy, ['fields' => 'ids'] );
                        if ( ! empty( $terms ) && empty( $postarr['tax_input'][ $taxonomy ] ) )
                            $postarr['tax_input'][ $taxonomy ] = $terms;
                        if ( empty( $postarr['tax_input'][ $taxonomy ] ) ) {
                            $default_term_id = $this->_get_option( 'default_term_' . $taxonomy );
                            if ( ! empty( $default_term_id ) ) $postarr['tax_input'][ $taxonomy ] = array( (int) $default_term_id );
                        }
                    }
                }
            }
            if ( ! empty( $postarr['tax_input'] ) ) {
                foreach ( $postarr['tax_input'] as $taxonomy => $tags ) {
                    $taxonomy_obj = $this->_get_taxonomy( $taxonomy );
                    if ( ! $taxonomy_obj ) {
                        $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );
                        continue;
                    }
                    if ( is_array( $tags ) ) $tags = array_filter( $tags );
                    if ( $this->_current_user_can( $taxonomy_obj->cap->assign_terms ) )
                        $this->_tp_set_post_terms( $post_ID, $tags, $taxonomy );
                }
            }
            if ( ! empty( $postarr['meta_input'] ) ) {
                foreach ( $postarr['meta_input'] as $field => $value )
                    $this->_update_post_meta( $post_ID, $field, $value );
            }
            $current_guid = $this->_get_post_field( 'guid', $post_ID );
            if ( ! $update && '' === $current_guid )
                $this->tpdb->update( $this->tpdb->posts, array( 'guid' => $this->_get_permalink( $post_ID ) ), $where );
            if ( 'attachment' === $postarr['post_type'] ) {
                if ( ! empty( $postarr['file'] ) )
                    $this->_update_attached_file( $post_ID, $postarr['file'] );
                if ( ! empty( $postarr['context'] ) )
                    $this->_add_post_meta( $post_ID, '_tp_attachment_context', $postarr['context'], true );
            }
            if ( isset( $postarr['_thumbnail_id'] ) ) {
                $thumbnail_support = ($this->_current_theme_supports('post-thumbnails', $post_type) && $this->_post_type_supports($post_type, 'thumbnail')) || 'revision' === $post_type;
                if ( ! $thumbnail_support && 'attachment' === $post_type && $post_mime_type ) {
                    if ( $this->_tp_attachment_is( 'audio', $post_ID ) )
                        $thumbnail_support = $this->_post_type_supports( 'attachment:audio', 'thumbnail' ) || $this->_current_theme_supports( 'post-thumbnails', 'attachment:audio' );
                    elseif ( $this->_tp_attachment_is( 'video', $post_ID ) )
                        $thumbnail_support = $this->_post_type_supports( 'attachment:video', 'thumbnail' ) || $this->_current_theme_supports( 'post-thumbnails', 'attachment:video' );
                }
                if ( $thumbnail_support ) {
                    $thumbnail_id = (int) $postarr['_thumbnail_id'];
                    if ( -1 === $thumbnail_id ) $this->_delete_post_thumbnail( $post_ID );
                     else $this->_set_post_thumbnail( $post_ID, $thumbnail_id );
                }
            }
            $this->_clean_post_cache( $post_ID );
            $post = $this->_get_post( $post_ID );
            if ( ! empty( $postarr['page_template'] ) ) {
                $post->page_template = $postarr['page_template'];
                $theme = $this->_tp_get_theme();
                if( $theme instanceof TP_Theme ){
                    $page_templates      = $theme->get_page_templates( $post );
                }
                if ( 'default' !== $postarr['page_template'] && ! isset( $page_templates[ $postarr['page_template'] ] ) ) {
                    if ( $tp_error )
                        return (int) new TP_Error( 'invalid_page_template', $this->__( 'Invalid page template.' ) );
                    $this->_update_post_meta( $post_ID, '_tp_page_template', 'default' );
                } else $this->_update_post_meta( $post_ID, '_tp_page_template', $postarr['page_template'] );
            }
            if ( 'attachment' !== $postarr['post_type'] )
                $this->_tp_transition_post_status( $data['post_status'], $previous_status, $post );
            else {
                if ( $update ) {
                    $this->_do_action( 'edit_attachment', $post_ID );
                    $post_after = $this->_get_post( $post_ID );
                    $this->_do_action( 'attachment_updated', $post_ID, $post_after, $post_before );
                } else $this->_do_action( 'add_attachment', $post_ID );
                return $post_ID;
            }
            if ( $update ) {
                $this->_do_action( "edit_post_{$post->post_type}", $post_ID, $post );
                $this->_do_action( 'edit_post', $post_ID, $post );
                $post_after = $this->_get_post( $post_ID );
                $this->_do_action( 'post_updated', $post_ID, $post_after, $post_before );
            }
            $this->_do_action( "save_post_{$post->post_type}", $post_ID, $post, $update );
            $this->_do_action( 'save_post', $post_ID, $post, $update );
            $this->_do_action( 'tp_insert_post', $post_ID, $post, $update );
            if ( $fire_after_hooks )  $this->_tp_after_insert_post( $post, $update, $post_before );
            return $post_ID;
        }//4043
        /**
         * @description Update a post with new post data.
         * @param mixed $postarr
         * @param bool $tp_error
         * @param bool $fire_after_hooks
         * @return int
         */
        protected function _tp_update_post($postarr = [], $tp_error = false, $fire_after_hooks = true ):int{
            if ( is_object((object)$postarr ) ) {
                $postarr = get_object_vars((object) $postarr );
                $postarr = $this->_tp_slash($postarr );
            }
            $post = $this->_get_post( $postarr['ID'], ARRAY_A );
            if ( is_null( $post ) ) {
                if ( $tp_error ) return (int) new TP_Error( 'invalid_post', $this->__( 'Invalid post ID.' ) );
                return 0;
            }
            $post = $this->_tp_slash( $post );
            if ( isset( $postarr['post_category'] ) && is_array( $postarr['post_category'] ) && count( $postarr['post_category'] ) > 0 )
                $post_cats = $postarr['post_category'];
            else $post_cats = $post['post_category'];
            if ( isset( $post['post_status'] ) && empty( $postarr['edit_date'] ) && ( '0000-00-00 00:00:00' === $post['post_date_gmt'] ) && in_array( $post['post_status'], array( 'draft', 'pending', 'auto-draft' ), true ))
                $clear_date = true;
            else $clear_date = false;
            $postarr                  = array_merge( $post, $postarr );
            $postarr['post_category'] = $post_cats;
            if ( $clear_date ) {
                $postarr['post_date']     = $this->_current_time( 'mysql' );
                $postarr['post_date_gmt'] = '';
            }
            if ( 'attachment' === $postarr['post_type'] )
                return $this->_tp_insert_attachment( $postarr, false, 0, $tp_error );
            if ( isset( $postarr['tags_input'] ) && $this->_is_object_in_taxonomy( $postarr['post_type'], 'post_tag' ) ) {
                $tags      = $this->_get_the_terms( $postarr['ID'], 'post_tag' );
                $tag_names = [];
                if ( $tags && ! $this->_init_error( $tags ) ) $tag_names = $this->_tp_list_pluck( $tags, 'name' );
                if ( $postarr['tags_input'] === $tag_names ) unset( $postarr['tags_input'] );
            }
            return $this->_tp_insert_post( $postarr, $tp_error, $fire_after_hooks );
        }//4724
    }
}else die;