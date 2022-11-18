<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _post_06{
        use _init_db;
        /**
         * @description Sanitizes every post field.
         * @param $post
         * @param string $context
         * @return mixed
         */
        protected function _sanitize_post( $post, $context = 'display' ){
            if ( is_object( $post ) ) {
                if ( isset( $post->filter ) && $context === $post->filter )
                    return $post;
                if ( ! isset( $post->ID ) ) $post->ID = 0;
                foreach ( array_keys( get_object_vars( $post ) ) as $field )
                    $post->$field = $this->_sanitize_post_field( $field, $post->$field, $post->ID, $context );
                $post->filter = $context;
            } elseif ( is_array( $post ) ) {
                if ( isset( $post['filter'] ) && $context === $post['filter'] )
                    return $post;
                if ( ! isset( $post['ID'] ) ) $post['ID'] = 0;
                foreach ( array_keys( $post ) as $field )
                    $post[ $field ] = $this->_sanitize_post_field( $field, $post[ $field ], $post['ID'], $context );
                $post['filter'] = $context;
            }
            return $post;
        }//2709
        /**
         * @description Sanitizes a post field based on context.
         * @param $field
         * @param $value
         * @param $post_id
         * @param string $context
         * @return array|int
         */
        protected function _sanitize_post_field($field, $value, $post_id, $context = 'display' ){
            $int_fields = array( 'ID', 'post_parent', 'menu_order' );
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            $array_int_fields = array( 'ancestors' );
            if ( in_array( $field, $array_int_fields, true ) ) {
                $value = array_map( 'absint',(array) $value );
                return $value;
            }
            if ( 'raw' === $context ) return $value;
            $prefixed = false;
            $field_no_prefix = null;
            if ( false !== strpos( $field, 'post_' ) ) {
                $prefixed        = true;
                $field_no_prefix = str_replace( 'post_', '', $field );
            }
            if ( 'edit' === $context ) {
                $format_to_edit = array( 'post_content', 'post_excerpt', 'post_title', 'post_password' );
                if ( $prefixed ) {
                    $value = $this->_apply_filters( "edit_{$field}", $value, $post_id );
                    $value = $this->_apply_filters( "{$field_no_prefix}_edit_pre", $value, $post_id );
                } else $value = $this->_apply_filters( "edit_post_{$field}", $value, $post_id );
                if ( in_array( $field, $format_to_edit, true ) ) {
                    if ( 'post_content' === $field )
                        $value = $this->_format_to_edit( $value, $this->_user_can_rich_edit() );
                    else $value = $this->_format_to_edit( $value );
                } else $value = $this->_esc_attr( $value );
            } elseif ( 'db' === $context ) {
                if ( $prefixed ) {
                    $value = $this->_apply_filters( "pre_{$field}", $value );
                    $value = $this->_apply_filters( "{$field_no_prefix}_save_pre", $value );
                } else {
                    $value = $this->_apply_filters( "pre_post_{$field}", $value );
                    $value = $this->_apply_filters( "{$field}_pre", $value );
                }
            } else {
                if ( $prefixed ) $value = $this->_apply_filters("{(string)$field}", $value, $post_id, $context );
                else $value = $this->_apply_filters( "post_{$field}", $value, $post_id, $context );
                if ( 'attribute' === $context ) $value = $this->_esc_attr( $value );
                elseif ( 'js' === $context ) $value = $this->_esc_js( $value );
            }
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            return $value;
        }//2755
        /**
         * @description Make a post sticky.
         * @param $post_id
         */
        protected function _stick_post( $post_id ):void{
            $post_id  = (int) $post_id;
            $stickies = $this->_get_option( 'sticky_posts' );
            $updated  = false;
            if ( ! is_array( $stickies ) ) $stickies = array( $post_id );
            else $stickies = array_unique( array_map( 'intval', $stickies ) );
            if ( ! in_array( $post_id, $stickies, true ) ) {
                $stickies[] = $post_id;
                $updated    = $this->_update_option( 'sticky_posts', array_values( $stickies ) );
            }
            if ( $updated ) $this->_do_action( 'post_stuck', $post_id );
        }//2910
        /**
         * @description Un-stick a post.
         * @param $post_id
         */
        protected function _unstick_post( $post_id ):void{
            $post_id  = (int) $post_id;
            $stickies = $this->_get_option( 'sticky_posts' );
            if ( ! is_array( $stickies ) ) return;
            $stickies = array_values( array_unique( array_map( 'intval', $stickies ) ) );
            if ( ! in_array( $post_id, $stickies, true ) ) return;
            $offset = array_search( $post_id, $stickies, true );
            if ( false === $offset ) return;
            array_splice( $stickies, $offset, 1 );
            $updated = $this->_update_option( 'sticky_posts', $stickies );
            if ( $updated ) $this->_do_action( 'post_unstuck', $post_id );
        }//2947
        /**
         * @description Return the cache key for tp_count_posts() based on the passed arguments.
         * @param string $type
         * @param string $perm
         * @return string
         */
        protected function _count_posts_cache_key( $type = 'post', $perm = '' ):string{
            $cache_key = 'posts-' . $type;
            if ( 'readable' === $perm && $this->_is_user_logged_in() ) {
                $post_type_object = $this->_get_post_type_object( $type );
                if ( $post_type_object && ! $this->_current_user_can( $post_type_object->cap->read_private_posts ) )
                    $cache_key .= '_' . $perm . '_' . $this->_get_current_user_id();
            }
            return $cache_key;
        }//2992
        /**
         * @description Count number of posts of a post type and if user has permissions to view.
         * @param string $type
         * @param string $perm
         * @return \stdClass
         */
        protected function _tp_count_posts( $type = 'post', $perm = '' ):\stdClass{
            $this->tpdb = $this->_init_db();
            if ( ! $this->_post_type_exists( $type ) ) return new \stdClass;
            $cache_key = $this->_count_posts_cache_key( $type, $perm );
            $counts = $this->_tp_cache_get( $cache_key, 'counts' );
            if ( false !== $counts ) {
                foreach ( $this->_get_post_stati() as $status ) {
                    if ( ! isset( $counts->{$status} ) ) $counts->{$status} = 0;
                }
                return $this->_apply_filters( 'tp_count_posts', $counts, $type, $perm );
            }
            $query = TP_SELECT . " post_status, COUNT( * ) AS num_posts FROM {$this->tpdb->posts} WHERE post_type = %s";
            if ( 'readable' === $perm && $this->_is_user_logged_in() ) {
                $post_type_object = $this->_get_post_type_object( $type );
                if ( ! $this->_current_user_can( $post_type_object->cap->read_private_posts ) )
                    $query .= $this->tpdb->prepare(" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
                        $this->_get_current_user_id()
                    );
            }
            $query .= ' GROUP BY post_status';
            $results = (array) $this->tpdb->get_results( $this->tpdb->prepare( $query, $type ), ARRAY_A );
            $counts  = array_fill_keys( $this->_get_post_stati(), 0 );
            foreach ( $results as $row ) $counts[ $row['post_status'] ] = $row['num_posts'];
            $counts = (object) $counts;
            $this->_tp_cache_set( $cache_key, $counts, 'counts' );
            return $this->_apply_filters( 'tp_count_posts', $counts, $type, $perm );
        }//3025
        /**
         * @description Count number of attachments for the mime type(s).
         * @param string $mime_type
         * @return mixed
         */
        protected function _tp_count_attachments( $mime_type = '' ){
            //$this->tpdb = $this->_init_db();
            //$and   = $this->_tp_post_mime_type_where( $mime_type );
            //$count = (string)$this->tpdb->get_results( TP_SELECT . " post_mime_type, COUNT( * ) AS num_posts FROM $this->tpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' $and GROUP BY post_mime_type", ARRAY_A );
            //$counts = [];
            //foreach ( (array) $count as $row ) $counts[ $row['post_mime_type'] ] = $row['num_posts'];
            //$counts['trash'] = $this->tpdb->get_var( TP_SELECT . " COUNT( * ) FROM $this->tpdb->posts WHERE post_type = 'attachment' AND post_status = 'trash' $and" );
            //return $this->_apply_filters( 'tp_count_attachments', (object) $counts, $mime_type );
            return ''; // is for when the db runs with data
        }//3101
        /**
         * @description Get default post mime types.
         * @return mixed
         */
        protected function _get_post_mime_types(){
            $post_mime_types = [   // array( adj, noun )
                'image' => [$this->__( 'Images' ),$this->__( 'Manage Images' ),
                    /* translators: %s: Number of images. */
                    $this->_n_noop("Image <span class='count'>(%s)</span>","Images <span class='count'>(%s)</span>"),
                ],
                'audio' => [ $this->_x( 'Audio', 'file type group' ), $this->__( 'Manage Audio' ),
                    /* translators: %s: Number of audio files. */
                    $this->_n_noop("Audio <span class='count'>(%s)</span>","Audio <span class='count'>(%s)</span>"),
                ],
                'video' => [$this->_x( 'Video', 'file type group' ), $this->__( 'Manage Video' ),
                    /* translators: %s: Number of video files. */
                    $this->_n_noop("Video <span class='count'>(%s)</span>","Video <span class='count'>(%s)</span>" ),
                ],
                'document'    => [$this->__( 'Documents' ),$this->__( 'Manage Documents' ),
                    /* translators: %s: Number of documents. */
                    $this->_n_noop("Document <span class='count'>(%s)</span>", "Documents <span class='count'>(%s)</span>"),
                ],
                'spreadsheet' => [$this->__( 'Spreadsheets' ),$this->__( 'Manage Spreadsheets' ),
                    /* translators: %s: Number of spreadsheets. */
                    $this->_n_noop("Spreadsheet <span class='count'>(%s)</span>","Spreadsheets <span class='count'>(%s)</span>"),
                ],
                'archive' => [
                    $this->_x( 'Archives', 'file type group' ),$this->__( 'Manage Archives' ),
                    /* translators: %s: Number of archives. */
                    $this->_n_noop("Archive <span class='count'>(%s)</span>","Archives <span class='count'>(%s)</span>" ),
                ],
            ];
            $ext_types  = $this->_tp_get_ext_types();
            $mime_types = $this->_tp_get_mime_types();
            foreach ( $post_mime_types as $group => $labels ) {
                if ( in_array( $group, array( 'image', 'audio', 'video' ), true ) )
                    continue;
                if ( ! isset( $ext_types[ $group ] ) ) {
                    unset( $post_mime_types[ $group ] );
                    continue;
                }
                $group_mime_types = array();
                foreach ( $ext_types[ $group ] as $extension ) {
                    foreach ( $mime_types as $exts => $mime ) {
                        if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
                            $group_mime_types[] = $mime;
                            break;
                        }
                    }
                }
                $group_mime_types = implode( ',', array_unique( $group_mime_types ) );
                $post_mime_types[ $group_mime_types ] = $labels;
                unset( $post_mime_types[ $group ] );
            }
            return $this->_apply_filters( 'post_mime_types', $post_mime_types );
        }//3133
        /**
         * @description Check a MIME-Type against a list.
         * @param $wildcard_mime_types
         * @param $real_mime_types
         * @return array
         */
        protected function _tp_match_mime_types( $wildcard_mime_types, $real_mime_types ):array{
            $matches = [];
            if ( is_string( $wildcard_mime_types ) )
                $wildcard_mime_types = array_map( 'trim', explode( ',', $wildcard_mime_types ) );
            if ( is_string( $real_mime_types ) )
                $real_mime_types = array_map( 'trim', explode( ',', $real_mime_types ) );
            $patterns_main = [];
            $wild = '[-._a-z0-9]*';
            foreach ( (array) $wildcard_mime_types as $type ) {
                $mimes = array_map( 'trim', explode( ',', $type ) );
                foreach ( $mimes as $mime ) {
                    $regex = str_replace( '__wildcard__', $wild, preg_quote( str_replace( '*', '__wildcard__', $mime ),' ') );
                    $patterns_main[][ $type ] = "^$regex$";
                    if ( false === strpos( $mime, '/' ) ) {
                        $patterns_main[][ $type ] = "^$regex/";
                        $patterns_main[][ $type ] = $regex;
                    }
                }
            }
            asort( $patterns_main );
            foreach ( $patterns_main as $patterns ) {
                foreach ( $patterns as $type => $pattern ) {
                    foreach ( (array) $real_mime_types as $real ) {
                        if ( preg_match( "#$pattern#", $real ) && ( empty( $matches[ $type ] ) || !in_array( $real, $matches[ $type ], true )))
                            $matches[ $type ][] = $real;
                    }
                }
            }
            return $matches;
        }//3243
        /**
         * @description Convert MIME types into SQL.
         * @param $post_mime_types
         * @param string $table_alias
         * @return string
         */
        protected function _tp_post_mime_type_where( $post_mime_types, $table_alias = '' ):string{
            $where     = '';
            $wildcards = [ '', '%', '%/%' ];
            if ( is_string( $post_mime_types ) )
                $post_mime_types = array_map( 'trim', explode( ',', $post_mime_types ) );
            $wheres = [];
            foreach ( (array) $post_mime_types as $mime_type ) {
                $mime_type = preg_replace( '/\s/', '', $mime_type );
                $slash_pos  = strpos( $mime_type, '/' );
                if ( false !== $slash_pos ) {
                    $mime_group    = preg_replace( '/[^-*.a-zA-Z0-9]/', '', substr( $mime_type, 0, $slash_pos ) );
                    $mime_subgroup = preg_replace( '/[^-*.+a-zA-Z0-9]/', '', substr( $mime_type, $slash_pos + 1 ) );
                    if ( empty( $mime_subgroup ) ) $mime_subgroup = '*';
                    else $mime_subgroup = str_replace( '/', '', $mime_subgroup );
                    $mime_pattern = "$mime_group/$mime_subgroup";
                } else {
                    $mime_pattern = preg_replace( '/[^-*.a-zA-Z0-9]/', '', $mime_type );
                    if ( false === strpos( $mime_pattern, '*' ) )
                        $mime_pattern .= '/*';
                }
                $mime_pattern = preg_replace( '/\*+/', '%', $mime_pattern );
                if ( in_array( $mime_type, $wildcards, true ) ) return '';
                if ( false !== strpos( $mime_pattern, '%' ) )
                    $wheres[] = empty( $table_alias ) ? "post_mime_type LIKE '$mime_pattern'" : "$table_alias.post_mime_type LIKE '$mime_pattern'";
                else $wheres[] = empty( $table_alias ) ? "post_mime_type = '$mime_pattern'" : "$table_alias.post_mime_type = '$mime_pattern'";
            }
            if ( ! empty( $wheres ) ) $where = ' AND (' . implode( ' OR ', $wheres ) . ') ';
            return $where;
        }//3296
        /**
         * @description Trash or delete a post or page.
         * @param int $postid
         * @param bool $force_delete
         * @return array|bool|null
         */
        protected function _tp_delete_post( $postid = 0, $force_delete = false ){
            $this->tpdb = $this->_init_db();
            $children = null;
            $post = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->posts WHERE ID = %d", $postid ) );
            if ( ! $post ) return $post;
            $post = $this->_get_post( $post );
            if ( ! $force_delete && EMPTY_TRASH_DAYS && ( 'post' === $post->post_type || 'page' === $post->post_type ) && 'trash' !== $this->_get_post_status( $postid ))
                return $this->_tp_trash_post( $postid );
            if ( 'attachment' === $post->post_type ) return $this->_tp_delete_attachment( $postid, $force_delete );
            $check = $this->_apply_filters( 'pre_delete_post', null, $post, $force_delete );
            if ( null !== $check ) return $check;
            $this->_do_action( 'before_delete_post', $postid, $post );
            $this->_delete_post_meta( $postid, '_tp_trash_meta_status' );
            $this->_delete_post_meta( $postid, '_tp_trash_meta_time' );
            $this->_tp_delete_object_term_relationships( $postid, $this->_get_object_taxonomies( $post->post_type ) );
            $parent_data  = array( 'post_parent' => $post->post_parent );
            $parent_where = array( 'post_parent' => $postid );
            if ( $this->_is_post_type_hierarchical( $post->post_type ) ) {
                $children_query = $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->posts WHERE post_parent = %d AND post_type = %s", $postid, $post->post_type );
                $children       = $this->tpdb->get_results( $children_query );
                if ( $children ) $this->tpdb->update( $this->tpdb->posts, $parent_data, $parent_where + array( 'post_type' => $post->post_type ) );
            }
            $revision_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $postid ) );
            // Use wp_delete_post (via wp_delete_post_revision) again. Ensures any meta/misplaced data gets cleaned up.
            foreach ( $revision_ids as $revision_id ) $this->_tp_delete_post_revision( $revision_id );
            $this->tpdb->update( $this->tpdb->posts, $parent_data, $parent_where + array( 'post_type' => 'attachment' ) );
            $this->_tp_defer_comment_counting( true );
            $comment_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " comment_ID FROM $this->tpdb->comments WHERE comment_post_ID = %d ORDER BY comment_ID DESC", $postid ) );
            foreach ( $comment_ids as $comment_id ) $this->_tp_delete_comment( $comment_id, true );
            $this->_tp_defer_comment_counting( false );
            $post_meta_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " meta_id FROM $this->tpdb->post_meta WHERE post_id = %d ", $postid ) );
            foreach ( $post_meta_ids as $mid )  $this->_delete_metadata_by_mid( 'post', $mid );
            $this->_do_action( 'delete_post', $postid, $post );
            $result = $this->tpdb->delete( $this->tpdb->posts, array( 'ID' => $postid ) );
            if ( ! $result ) return false;
            $this->_do_action( 'deleted_post', $postid, $post );
            $this->_clean_post_cache( $post );
            if ($children && $this->_is_post_type_hierarchical( $post->post_type )) {
                foreach ( $children as $child ) $this->_clean_post_cache( $child );
            }
            $this->_tp_clear_scheduled_hook( 'publish_future_post', array( $postid ) );
            $this->_do_action( 'after_delete_post', $postid, $post );
            return $post;
        }//3365
    }
}else die;