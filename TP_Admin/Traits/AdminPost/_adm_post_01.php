<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 10:08
 */
namespace TP_Admin\Traits\AdminPost;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_post_01{
        use _init_error,_init_db;
        /**
         * @param bool $update
         * @param int|null $post_data
         * @return mixed
         */
        protected function _tp_translate_postdata( $update = false, $post_data = null ):?TP_Error{
            if ($post_data === null) {  $post_data = &$_POST;}
            if ( $update ) { $post_data['ID'] = (int) $post_data['post_ID'];}
            $ptype = $this->_get_post_type_object( $post_data['post_type'] );
            if ($update && ! $this->_current_user_can( 'edit_post', $post_data['ID'] )) {
                if ( 'page' === $post_data['post_type'] ) {
                    return new TP_Error( 'edit_others_pages', $this->__( 'Sorry, you are not allowed to edit pages as this user.' ) );
                }
                return new TP_Error( 'edit_others_posts', $this->__( 'Sorry, you are not allowed to edit posts as this user.' ) );
            }
            if (! $update && ! $this->_current_user_can( $ptype->cap->create_posts )) {
                if ( 'page' === $post_data['post_type'] ) {
                    return new TP_Error( 'edit_others_pages', $this->__( 'Sorry, you are not allowed to create pages as this user.' ) );
                }
                return new TP_Error( 'edit_others_posts', $this->__( 'Sorry, you are not allowed to create posts as this user.' ) );
            }
            if ( isset( $post_data['content'] ) ) { $post_data['post_content'] = $post_data['content'];}
            if ( isset( $post_data['excerpt'] ) ) { $post_data['post_excerpt'] = $post_data['excerpt'];}
            if ( isset( $post_data['parent_id'] ) ) { $post_data['post_parent'] = (int) $post_data['parent_id'];}
            if ( isset( $post_data['trackback_url'] ) ) { $post_data['to_ping'] = $post_data['trackback_url'];}
            $post_data['user_ID'] = $this->_get_current_user_id();
            if ( ! empty( $post_data['post_author_override'] ) ) { $post_data['post_author'] = (int) $post_data['post_author_override'];
            } else if ( ! empty( $post_data['post_author'] ) ) { $post_data['post_author'] = (int) $post_data['post_author'];
            } else { $post_data['post_author'] = (int) $post_data['user_ID']; }
            if ( isset( $post_data['user_ID'] ) && ( $post_data['post_author'] !== $post_data['user_ID'] ) && ! $this->_current_user_can( $ptype->cap->edit_others_posts ) ) {
                if ( $update ) {
                    if ( 'page' === $post_data['post_type'] ) {
                        return new TP_Error( 'edit_others_pages', $this->__( 'Sorry, you are not allowed to edit pages as this user.' ) );
                    }
                    return new TP_Error( 'edit_others_posts', $this->__( 'Sorry, you are not allowed to edit posts as this user.' ) );
                }
                if ( 'page' === $post_data['post_type'] ) {
                    return new TP_Error( 'edit_others_pages', $this->__( 'Sorry, you are not allowed to create pages as this user.' ) );
                }
                return new TP_Error( 'edit_others_posts', $this->__( 'Sorry, you are not allowed to create posts as this user.' ) );
            }
            if ( ! empty( $post_data['post_status'] ) ) {
                $post_data['post_status'] = $this->_sanitize_key( $post_data['post_status'] );
                if ( 'auto-draft' === $post_data['post_status'] ) { $post_data['post_status'] = 'draft';}
                if ( ! $this->_get_post_status_object( $post_data['post_status'] ) ) { unset( $post_data['post_status'] );}
            }
            if ( isset( $post_data['saveasdraft'] ) && '' !== $post_data['saveasdraft'] ) { $post_data['post_status'] = 'draft'; }
            if ( isset( $post_data['saveasprivate'] ) && '' !== $post_data['saveasprivate'] ) { $post_data['post_status'] = 'private';}
            if ( isset( $post_data['publish'] ) && ( '' !== $post_data['publish'] ) && ( ! isset( $post_data['post_status'] ) || 'private' !== $post_data['post_status'] )) {
                $post_data['post_status'] = 'publish';}
            if ( isset( $post_data['advanced'] ) && '' !== $post_data['advanced'] ) { $post_data['post_status'] = 'draft';}
            if ( isset( $post_data['pending'] ) && '' !== $post_data['pending'] ) { $post_data['post_status'] = 'pending';}
            $post_id = $post_data['ID'] ?? null;
            $previous_status = $post_id ? $this->_get_post_field( 'post_status', $post_id ) : false;
            if ( isset( $post_data['post_status'] ) && 'private' === $post_data['post_status'] && ! $this->_current_user_can( $ptype->cap->publish_posts ) ) {
                $post_data['post_status'] = $previous_status ?: 'pending';}
            $published_statuses = array( 'publish', 'future' );
            if ( isset( $post_data['post_status'] ) && ( in_array( $post_data['post_status'], $published_statuses, true ) && ! $this->_current_user_can( $ptype->cap->publish_posts ) )) {
                if ( ! in_array( $previous_status, $published_statuses, true ) || ! $this->_current_user_can( 'edit_post', $post_id ) ) {
                    $post_data['post_status'] = 'pending';}
            }
            if ( ! isset( $post_data['post_status'] ) ) { $post_data['post_status'] = 'auto-draft' === $previous_status ? 'draft' : $previous_status;}

            if ( isset( $post_data['post_password'] ) && ! $this->_current_user_can( $ptype->cap->publish_posts ) ) {
                unset( $post_data['post_password'] );}
            if ( ! isset( $post_data['comment_status'] ) ) { $post_data['comment_status'] = 'closed'; }
            if ( ! isset( $post_data['ping_status'] ) ) { $post_data['ping_status'] = 'closed';}

            foreach ( array( 'aa', 'mm', 'jj', 'hh', 'mn' ) as $timeunit ) {
                if ( ! empty( $post_data[ 'hidden_' . $timeunit ] ) && $post_data[ 'hidden_' . $timeunit ] !== $post_data[ $timeunit ] ) {
                    $post_data['edit_date'] = '1';
                    break;
                }
            }
            if ( ! empty( $post_data['edit_date'] ) ) {
                $aa = $post_data['aa'];
                $mm = $post_data['mm'];
                $jj = $post_data['jj'];
                $hh = $post_data['hh'];
                $mn = $post_data['mn'];
                $ss = $post_data['ss'];
                $aa = ( $aa <= 0 ) ? gmdate( 'Y' ) : $aa;
                $mm = ( $mm <= 0 ) ? gmdate( 'n' ) : $mm;
                $jj = ( $jj > 31 ) ? 31 : $jj;
                $jj = ( $jj <= 0 ) ? gmdate( 'j' ) : $jj;
                $hh = ( $hh > 23 ) ? $hh - 24 : $hh;
                $mn = ( $mn > 59 ) ? $mn - 60 : $mn;
                $ss = ( $ss > 59 ) ? $ss - 60 : $ss;
                $post_data['post_date'] = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $aa, $mm, $jj, $hh, $mn, $ss );
                $valid_date = $this->_tp_check_date( $mm, $jj, $aa, $post_data['post_date'] );
                if ( ! $valid_date ) { return new TP_Error( 'invalid_date', $this->__( 'Invalid date.' ) );}
                $post_data['post_date_gmt'] = $this->_get_gmt_from_date( $post_data['post_date'] );
            }
            if ( isset( $post_data['post_category'] ) ) {
                $category_object = $this->_get_taxonomy( 'category' );
                if ( ! $this->_current_user_can( $category_object->cap->assign_terms ) ) {
                    unset( $post_data['post_category'] );}
            }
            return $post_data;
        }//21
        /**@description Returns only allowed post data fields.
         * @param null $post_data
         * @return array|null
         */
        protected function _tp_get_allowed_postdata( $post_data = null ):array{
            if ( empty( $post_data ) ) { $post_data = $_POST;}
            if ( $this->_init_error( $post_data ) ) { return $post_data;}
            return array_diff_key( $post_data, array_flip( array( 'meta_input', 'file', 'guid' ) ) );
        }//216
        /**
         * @description Updates an existing post with values provided in `$_POST`.
         * @param null $post_data
         * @return int
         */
        protected function _edit_post( $post_data = null ):int{
            $this->tpdb = $this->_init_db();
            if ( empty( $post_data ) ) { $post_data = &$_POST;}
            unset( $post_data['filter'] );
            $post_ID = (int) $post_data['post_ID'];
            $post    = $this->_get_post( $post_ID );
            $post_data['post_type']      = $post->post_type;
            $post_data['post_mime_type'] = $post->post_mime_type;
            if ( ! empty( $post_data['post_status'] ) ) {
                $post_data['post_status'] = $this->_sanitize_key( $post_data['post_status'] );
                if ( 'inherit' === $post_data['post_status'] ) { unset( $post_data['post_status'] );}
            }
            $ptype = $this->_get_post_type_object( $post_data['post_type'] );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) ) {
                if ( 'page' === $post_data['post_type'] ) {
                    $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit this page.' ) );
                } else {$this->_tp_die( $this->__( 'Sorry, you are not allowed to edit this post.' ) );}
            }
            if ( $this->_post_type_supports( $ptype->name, 'revisions' ) ) {
                $revisions = $this->_tp_get_post_revisions( $post_ID,['order' => 'ASC','posts_per_page' => 1,]);
                $revision  = current( $revisions );
                if ( $revisions && $this->_tp_get_post_revision_version( $revision ) < 1 ) {
                    $this->_tp_upgrade_revisions_of_post( $post, $this->_tp_get_post_revisions( $post_ID ) );
                }
            }
            if ( isset( $post_data['visibility'] ) ) {
                switch ( $post_data['visibility'] ) {
                    case 'public':
                        $post_data['post_password'] = '';
                        break;
                    case 'password':
                        unset( $post_data['sticky'] );
                        break;
                    case 'private':
                        $post_data['post_status']   = 'private';
                        $post_data['post_password'] = '';
                        unset( $post_data['sticky'] );
                        break;
                }
            }
            $post_data = $this->_tp_translate_postdata( true, $post_data );
            if ( $this->_init_error( $post_data ) ) {
                $this->_tp_die( $post_data->get_error_message() );
            }
            $translated = $this->_tp_get_allowed_postdata( $post_data );
            if ( isset( $post_data['post_format'] ) ) {
                $this->_set_post_format( $post_ID, $post_data['post_format'] );
            }
            $format_meta_urls = array( 'url', 'link_url', 'quote_source_url' );
            foreach ( $format_meta_urls as $format_meta_url ) {
                $keyed = '_format_' . $format_meta_url;
                if ( isset( $post_data[ $keyed ] ) ) {
                    $this->_update_post_meta( $post_ID, $keyed, $this->_tp_slash( $this->_esc_url_raw( $this->_tp_unslash( $post_data[ $keyed ] ) ) ) );
                }
            }
            $format_keys = array( 'quote', 'quote_source_name', 'image', 'gallery', 'audio_embed', 'video_embed' );
            foreach ( $format_keys as $key ) {
                $keyed = '_format_' . $key;
                if ( isset( $post_data[ $keyed ] ) ) {
                    if ( $this->_current_user_can( 'unfiltered_html' ) ) {
                        $this->_update_post_meta( $post_ID, $keyed, $post_data[ $keyed ] );
                    } else {$this->_update_post_meta( $post_ID, $keyed, $this->_tp_filter_post_kses( $post_data[ $keyed ] ) );}
                }
            }
            if ( 'attachment' === $post_data['post_type'] && preg_match( '#^(audio|video)/#', $post_data['post_mime_type'] ) ) {
                $id3data = $this->_tp_get_attachment_metadata( $post_ID );
                if ( ! is_array( $id3data ) ) { $id3data = [];}
                foreach ( $this->_tp_get_attachment_id_3_keys( $post, 'edit' ) as $key => $label ) {
                    $_id3 = $post_data[ 'id3_' . $key ];
                    if ( isset($_id3 ) ) { $id3data[ $key ] = $this->_sanitize_text_field( $this->_tp_unslash( $post_data[ 'id3_' . $key ] ) );}
                }
                $this->_tp_update_attachment_metadata( $post_ID, $id3data );
            }
            if ( isset( $post_data['meta'] ) && $post_data['meta'] ) {
                foreach ( $post_data['meta'] as $key => $value ) {
                    $meta = $this->_get_post_meta_by_id( $key );
                    if ( ! $meta ) { continue;}
                    if ( $meta->post_id !== $post_ID ) { continue;}
                    if ( $this->_is_protected_meta( $meta->meta_key, 'post' ) || ! $this->_current_user_can( 'edit_post_meta', $post_ID, $meta->meta_key ) ) {
                        continue;}
                    if ( $this->_is_protected_meta( $value['key'], 'post' ) || ! $this->_current_user_can( 'edit_post_meta', $post_ID, $value['key'] ) ) {
                        continue;}
                    $this->_update_meta( $key, $value['key'], $value['value'] );
                }
            }
            if ( isset( $post_data['deletemeta'] ) && $post_data['deletemeta'] ) {
                foreach ( $post_data['deletemeta'] as $key => $value ) {
                    $meta = $this->_get_post_meta_by_id( $key );
                    if ( ! $meta ) { continue;}
                    if ( $meta->post_id !== $post_ID ) { continue; }
                    if ( $this->_is_protected_meta( $meta->meta_key, 'post' ) || ! $this->_current_user_can( 'delete_post_meta', $post_ID, $meta->meta_key ) ) {
                        continue;
                    }
                    $this->_delete_meta( $key );
                }
            }
            if ( 'attachment' === $post_data['post_type'] ) {
                if ( isset( $post_data['_tp_attachment_image_alt'] ) ) {
                    $image_alt = $this->_tp_unslash( $post_data['_tp_attachment_image_alt'] );
                    if ( $this->_get_post_meta( $post_ID, '_tp_attachment_image_alt', true ) !== $image_alt ) {
                        $image_alt = $this->_tp_strip_all_tags( $image_alt, true );
                        $this->_update_post_meta( $post_ID, '_tp_attachment_image_alt', $this->_tp_slash( $image_alt ) );
                    }
                }
                $attachment_data = $post_data['attachments'][ $post_ID ] ?? array();
                $translated = $this->_apply_filters( 'attachment_fields_to_save', $translated, $attachment_data );
            }
            if ( isset( $post_data['tax_input'] ) ) {
                foreach ( (array) $post_data['tax_input'] as $taxonomy => $terms ) {
                    $tax_object = $this->_get_taxonomy( $taxonomy );
                    if ( $tax_object && isset( $tax_object->meta_box_sanitize_cb ) ) {
                        $translated['tax_input'][ $taxonomy ] = call_user_func($tax_object->meta_box_sanitize_cb, $taxonomy, $terms);
                    }
                }
            }
            $this->_add_meta( $post_ID );
            $this->_update_post_meta( $post_ID, '_edit_last', $this->_get_current_user_id() );
            $success = $this->_tp_update_post( $translated );
            if ( ! $success && is_callable( array( $this->tpdb, 'strip_invalid_text_for_column' ) ) ) {
                $fields = array( 'post_title', 'post_content', 'post_excerpt' );
                foreach ( $fields as $field ) {
                    if ( isset( $translated[ $field ] ) ) {
                        $translated[ $field ] = $this->tpdb->strip_invalid_text_for_column( $this->tpdb->posts, $field, $translated[ $field ] );
                    }
                }
                $this->_tp_update_post( $translated );
            }
            $this->_fix_attachment_links( $post_ID );
            $this->_tp_set_post_lock( $post_ID );
            if ( $this->_current_user_can( $ptype->cap->edit_others_posts ) && $this->_current_user_can( $ptype->cap->publish_posts ) ) {
                if ( ! empty( $post_data['sticky'] ) ) { $this->_stick_post( $post_ID );
                } else { $this->_unstick_post( $post_ID );}
            }
            return $post_ID;
        }//245
        /**
         * @description Processes the post data for the bulk editing of posts.
         * @param null $post_data
         * @return array
         */
        protected function _bulk_edit_posts( $post_data = null ):array{
            $this->tpdb = $this->_init_db();
            if ( empty( $post_data ) ) { $post_data = &$_POST;}
            if ( isset( $post_data['post_type'] ) ) { $ptype = $this->_get_post_type_object( $post_data['post_type'] );}
            else {$ptype = $this->_get_post_type_object( 'post' );}
            if ( ! $this->_current_user_can( $ptype->cap->edit_posts ) ) {
                if ( 'page' === $ptype->name ) { $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit pages.' ));}
                else { $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit posts.' ) );}
            }
            if ( -1 === $post_data['_status'] ) {
                $post_data['post_status'] = null;
                unset( $post_data['post_status'] );
            } else { $post_data['post_status'] = $post_data['_status'];}
            unset( $post_data['_status'] );
            if ( ! empty( $post_data['post_status'] ) ) {
                $post_data['post_status'] = $this->_sanitize_key( $post_data['post_status'] );
                if ( 'inherit' === $post_data['post_status'] ) { unset( $post_data['post_status'] );}
            }
            $post_IDs = array_map( 'intval', (array) $post_data['post'] );
            $reset = ['post_author','post_status','post_password','post_parent','page_template','comment_status',
                'ping_status','keep_private','tax_input','post_category','sticky','post_format',];
            foreach ( $reset as $field ) {
                if ( isset( $post_data[ $field ] ) && ( '' === $post_data[ $field ] || -1 === $post_data[ $field ] ) ) {
                    unset( $post_data[ $field ] );}
            }
            if ( isset( $post_data['post_category'] ) ) {
                if ( is_array( $post_data['post_category'] ) && ! empty( $post_data['post_category'] ) ) {
                    $new_cats = array_map( 'absint', $post_data['post_category'] );
                } else {unset( $post_data['post_category'] );}
            }
            $tax_input = [];
            if ( isset( $post_data['tax_input'] ) ) {
                foreach ( $post_data['tax_input'] as $tax_name => $terms ) {
                    if ( empty( $terms ) ) { continue;}
                    if ( $this->_is_taxonomy_hierarchical( $tax_name ) ) {
                        $tax_input[ $tax_name ] = array_map( 'absint', $terms );
                    } else {
                        $comma = $this->_x( ',', 'tag delimiter' );
                        if ( ',' !== $comma ) { $terms = str_replace( $comma, ',', $terms );}
                        $tax_input[ $tax_name ] = explode( ',', trim( $terms, " \n\t\r\0\x0B," ) );
                    }
                }
            }
            if ( isset( $post_data['post_parent'] ) && (int) $post_data['post_parent'] ) {
                $parent   = (int) $post_data['post_parent'];
                $pages    = $this->tpdb->get_results( TP_SELECT . " ID, post_parent FROM $this->tpdb->posts WHERE post_type = 'page'" );
                $children = [];
                for ( $i = 0; $i < 50 && $parent > 0; $i++ ) {
                    $children[] = $parent;
                    foreach ( $pages as $page ) {
                        if ( (int) $page->ID === $parent ) {
                            $parent = (int) $page->post_parent;
                            break;
                        }
                    }
                }
            }
            $updated          = [];
            $skipped          = [];
            $locked           = [];
            $shared_post_data = $post_data;
            foreach ( $post_IDs as $post_ID ) {
                $post_data = $shared_post_data;
                $post_type_object = $this->_get_post_type_object( $this->_get_post_type( $post_ID ) );
                if ( ! isset( $post_type_object ) || ( isset( $children ) && in_array( $post_ID, $children, true ) ) || ! $this->_current_user_can( 'edit_post', $post_ID )){
                    $skipped[] = $post_ID;
                    continue;
                }
                if ( $this->_tp_check_post_lock( $post_ID ) ) {
                    $locked[] = $post_ID;
                    continue;
                }
                $post      = $this->_get_post( $post_ID );
                $tax_names = $this->_get_object_taxonomies( $post );
                foreach ( $tax_names as $tax_name ) {
                    $taxonomy_obj = $this->_get_taxonomy( $tax_name );
                    if ( isset( $tax_input[ $tax_name ] ) && $this->_current_user_can( $taxonomy_obj->cap->assign_terms ) ) {
                        $new_terms = $tax_input[ $tax_name ];}
                    else { $new_terms = [];}
                    if ( $taxonomy_obj->hierarchical ) {
                        $current_terms = (array) $this->_tp_get_object_terms( $post_ID, $tax_name, array( 'fields' => 'ids' ) );
                    } else { $current_terms = (array) $this->_tp_get_object_terms( $post_ID, $tax_name, array( 'fields' => 'names' ) );}
                    $post_data['tax_input'][ $tax_name ] = array_merge( $current_terms, $new_terms );
                }
                if ( isset( $new_cats ) && in_array( 'category', $tax_names, true ) ) {
                    $cats                       = (array) $this->_tp_get_post_categories( $post_ID );
                    $post_data['post_category'] = array_unique( array_merge( $cats, $new_cats ) );
                    unset( $post_data['tax_input']['category'] );
                }
                $post_data['post_ID']        = $post_ID;
                $post_data['post_type']      = $post->post_type;
                $post_data['post_mime_type'] = $post->post_mime_type;
                foreach ( array( 'comment_status', 'ping_status', 'post_author' ) as $field ) {
                    if ( ! isset( $post_data[ $field ] ) ) { $post_data[ $field ] = $post->$field;}
                }
                $post_data = $this->_tp_translate_postdata( true, $post_data );
                if ( $this->_init_error( $post_data ) ) {
                    $skipped[] = $post_ID;
                    continue;
                }
                $post_data = $this->_tp_get_allowed_postdata( $post_data );
                if ( isset( $shared_post_data['post_format'] ) ) {  $this->_set_post_format( $post_ID, $shared_post_data['post_format'] );}
                unset( $post_data['tax_input']['post_format'] );
                $post_id = $this->_tp_update_post( $post_data );
                $this->_update_post_meta( $post_id, '_edit_last', $this->_get_current_user_id() );
                $updated[] = $post_id;
                if ( isset( $post_data['sticky'] ) && $this->_current_user_can( $ptype->cap->edit_others_posts ) ) {
                    if ( 'sticky' === $post_data['sticky'] ) {$this->_stick_post( $post_ID );}
                    else {$this->_unstick_post( $post_ID );}
                }
            }
            return ['updated' => $updated,'skipped' => $skipped,'locked' => $locked,];
        }//471
        /**
         * @description Returns default post information to use when populating the "Write Post" form.
         * @param string $post_type
         * @param bool $create_in_db
         * @return \stdClass|TP_Post
         */
        protected function _get_default_post_to_edit( $post_type = 'post', $create_in_db = false ){
            $post_title = '';
            if(!empty( $_REQUEST['post_title'])){ $post_title = $this->_esc_html( $this->_tp_unslash( $_REQUEST['post_title'] ) );}
            $post_content = '';
            if ( ! empty( $_REQUEST['content'])){ $post_content = $this->_esc_html( $this->_tp_unslash( $_REQUEST['content']));}
            $post_excerpt = '';
            if ( ! empty( $_REQUEST['excerpt'])){ $post_excerpt = $this->_esc_html( $this->_tp_unslash( $_REQUEST['excerpt']));}
            if ( $create_in_db ) {
                $post_id = $this->_tp_insert_post(['post_title' => $this->__( 'Auto Draft' ), 'post_type' => $post_type,'post_status' => 'auto-draft',], false, false);
                $post    = $this->_get_post( $post_id );
                if ( $this->_current_theme_supports( 'post-formats' ) && $this->_post_type_supports( $post->post_type, 'post-formats' ) && $this->_get_option( 'default_post_format' ) ) {
                    $this->_set_post_format( $post, $this->_get_option( 'default_post_format' ) );}
                $this->_tp_after_insert_post( $post, false, null );
                if (!$this->_tp_next_scheduled('tp_scheduled_auto_draft_delete')){$this->_tp_schedule_event( time(), 'daily', 'tp_scheduled_auto_draft_delete' );}
            } else {
                $post                 = new \stdClass;
                $post->ID             = 0;
                $post->post_author    = '';
                $post->post_date      = '';
                $post->post_date_gmt  = '';
                $post->post_password  = '';
                $post->post_name      = '';
                $post->post_type      = $post_type;
                $post->post_status    = 'draft';
                $post->to_ping        = '';
                $post->pinged         = '';
                $post->comment_status = $this->_get_default_comment_status( $post_type );
                $post->ping_status    = $this->_get_default_comment_status( $post_type, 'pingback' );
                $post->post_pingback  = $this->_get_option( 'default_pingback_flag' );
                $post->post_category  = $this->_get_option( 'default_category' );
                $post->page_template  = 'default';
                $post->post_parent    = 0;
                $post->menu_order     = 0;
                $post                 = new TP_Post( $post );
            }
            $post->post_content = (string) $this->_apply_filters( 'default_content', $post_content, $post );
            $post->post_title = (string) $this->_apply_filters( 'default_title', $post_title, $post );
            $post->post_excerpt = (string) $this->_apply_filters( 'default_excerpt', $post_excerpt, $post );
            return $post;
        }//676
        /**
         * @description Determines if a post exists based on title, content, date and type.
         * @param $title
         * @param string $content
         * @param string $date
         * @param string $type
         * @param string $status
         * @return int
         */
        protected function _post_exists( $title, $content = '', $date = '', $type = '', $status = '' ):int{
            $this->tpdb = $this->_init_db();
            $post_title   = $this->_tp_unslash( $this->_sanitize_post_field( 'post_title', $title, 0, 'db' ) );
            $post_content = $this->_tp_unslash( $this->_sanitize_post_field( 'post_content', $content, 0, 'db' ) );
            $post_date    = $this->_tp_unslash( $this->_sanitize_post_field( 'post_date', $date, 0, 'db' ) );
            $post_type    = $this->_tp_unslash( $this->_sanitize_post_field( 'post_type', $type, 0, 'db' ) );
            $post_status  = $this->_tp_unslash( $this->_sanitize_post_field( 'post_status', $status, 0, 'db' ) );
            $query = TP_SELECT . " ID FROM $this->tpdb->posts WHERE 1=1";
            $args  = [];
            if ( ! empty( $date ) ) {
                $query .= ' AND post_date = %s';
                $args[] = $post_date;
            }
            if ( ! empty( $title ) ) {
                $query .= ' AND post_title = %s';
                $args[] = $post_title;
            }
            if ( ! empty( $content ) ) {
                $query .= ' AND post_content = %s';
                $args[] = $post_content;
            }
            if ( ! empty( $type ) ) {
                $query .= ' AND post_type = %s';
                $args[] = $post_type;
            }
            if ( ! empty( $status ) ) {
                $query .= ' AND post_status = %s';
                $args[] = $post_status;
            }
            if ( ! empty( $args ) ) { return (int) $this->tpdb->get_var( $this->tpdb->prepare( $query, $args ) ); }
            return 0;
        }//783
        /**
         * @description Creates a new post from the "Write Post" form using `$_POST` information.
         * @return array|int|mixed|null|TP_Error
         */
        protected function _tp_write_post(){
            if ( isset( $_POST['post_type'] ) ) { $ptype = $this->_get_post_type_object( $_POST['post_type'] ); }
            else { $ptype = $this->_get_post_type_object( 'post' );}
            if ( ! $this->_current_user_can( $ptype->cap->edit_posts ) ) {
                return 'page' === $ptype->name ? new TP_Error('edit_pages', $this->__('Sorry, you are not allowed to create pages on this site.')) : new TP_Error('edit_posts', $this->__('Sorry, you are not allowed to create posts or drafts on this site.'));
            }
            $_POST['post_mime_type'] = '';
            unset( $_POST['filter'] );
            if ( isset( $_POST['post_ID'] ) ) { return $this->_edit_post();}
            if ( isset( $_POST['visibility'] ) ) {
                switch ( $_POST['visibility'] ) {
                    case 'public':
                        $_POST['post_password'] = '';
                        break;
                    case 'password':
                        unset( $_POST['sticky'] );
                        break;
                    case 'private':
                        $_POST['post_status']   = 'private';
                        $_POST['post_password'] = '';
                        unset( $_POST['sticky'] );
                        break;
                }
            }
            $translated = $this->_tp_translate_postdata( false );
            if ( $this->_init_error( $translated ) ) { return $translated;}
            $translated = $this->_tp_get_allowed_postdata( $translated );
            // Create the post.
            $post_ID = $this->_tp_insert_post( $translated );
            if ( $this->_init_error( $post_ID ) ) { return $post_ID;}
            if ( empty( $post_ID ) ) { return 0;}
            $this->_add_meta( $post_ID );
            $this->_add_post_meta( $post_ID, '_edit_last', $GLOBALS['current_user']->ID );
            $this->_fix_attachment_links( $post_ID );
            $this->_tp_set_post_lock( $post_ID );
            return $post_ID;
        }//836
        /**
         * @description Calls wp_write_post() and handles the errors.
         * @return array|int|mixed|null|TP_Error
         */
        protected function _write_post(){
            $result = $this->_tp_write_post();
            if ( $this->_init_error( $result ) ) {
                $this->_tp_die( $result->get_error_message() );
            } else {
                return $result;
            }
        }//912
        /**
         * @description Adds post meta data defined in the `$_POST` for a post with given ID.
         * @param $post_ID
         * @return bool
         */
        protected function _add_meta( $post_ID ):bool{
            $post_ID = (int) $post_ID;
            $metakeyselect = isset( $_POST['metakeyselect'] ) ? $this->_tp_unslash( trim( $_POST['metakeyselect'] ) ) : '';
            $metakeyinput  = isset( $_POST['metakeyinput'] ) ? $this->_tp_unslash( trim( $_POST['metakeyinput'] ) ) : '';
            $metavalue     = $_POST['metavalue'] ?? '';
            $metakey = null;
            if ( is_string( $metavalue ) ) { $metavalue = trim( $metavalue );}
            if ( ( ( '#NONE#' !== $metakeyselect ) && ! empty( $metakeyselect ) ) || ! empty( $metakeyinput ) ) {
                if ( '#NONE#' !== $metakeyselect ) { $metakey = $metakeyselect;}
                if ( $metakeyinput ) { $metakey = $metakeyinput; }
                if ( $this->_is_protected_meta( $metakey, 'post' ) || ! $this->_current_user_can( 'add_post_meta', $post_ID, $metakey ) ) {
                    return false;}
                $metakey = $this->_tp_slash( $metakey );
                return $this->_add_post_meta( $post_ID, $metakey, $metavalue );
            }
            return false;
        }//933
        /**
         * @description Deletes post meta data by meta ID.
         * @param $mid
         * @return mixed
         */
        protected function _delete_meta( $mid ){
            return $this->_delete_metadata_by_mid( 'post', $mid );
        }//976
    }
}else die;

