<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _post_08{
        use _init_db;
        use _init_rewrite;
        /**
         * @param $post@description Publish a post by transitioning the post status.
         */
        protected function _tp_publish_post( $post ):void{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            if ( ! $post ) return;
            if ( 'publish' === $post->post_status ) return;
            $post_before = $this->_get_post( $post->ID );
            foreach ( $this->_get_object_taxonomies( $post->post_type, 'object' ) as $taxonomy => $tax_object ) {
                // Skip taxonomy if no default term is set.
                if ('category' !== $taxonomy && empty( $tax_object->default_term ))
                    continue;
                if ( ! empty( $this->_get_the_terms( $post, $taxonomy ) ) ) continue;
                if ( 'category' === $taxonomy )
                    $default_term_id = (int) $this->_get_option( 'default_category', 0 );
                else $default_term_id = (int) $this->_get_option( 'default_term_' . $taxonomy, 0 );
                if ( ! $default_term_id ) continue;
                $this->_tp_set_post_terms( $post->ID, array( $default_term_id ), $taxonomy );
            }
            $this->tpdb->update( $this->tpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $post->ID ) );
            $this->_clean_post_cache( $post->ID );
            $old_status        = $post->post_status;
            $post->post_status = 'publish';
            $this->_tp_transition_post_status( 'publish', $old_status, $post );
            $this->_do_action( "edit_post_{$post->post_type}", $post->ID, $post );
            $this->_do_action( 'edit_post', $post->ID, $post );
            $this->_do_action( "save_post_{$post->post_type}", $post->ID, $post, true );
            $this->_do_action( 'save_post', $post->ID, $post, true );
            $this->_do_action( 'tp_insert_post', $post->ID, $post, true );
            $this->_tp_after_insert_post( $post, $post_before, $post_before );//todo , true
        }//4801
        /**
         * @description Publish future post and make sure post ID has future post status.
         * @param $post_id
         */
        protected function _check_and_publish_future_post( $post_id ):void{
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return;
            if ( 'future' !== $post->post_status ) return;
            $time = strtotime( $post->post_date_gmt . ' GMT' );
            if ( $time > time() ) {
                $this->_tp_clear_scheduled_hook( 'publish_future_post', array( $post_id ) ); // Clear anything else in the system.
                $this->_tp_schedule_single_event( $time, 'publish_future_post', array( $post_id ) );
                return;
            }
            $this->_tp_publish_post( $post_id );
        }//4879
        /**
         * @description Uses tp_check_date to return a valid Gregorian-calendar value for post_date.
         * @description . If post_date is not provided, this first checks post_date_gmt if provided,
         * @description . then falls back to use the current time.
         * @param string $post_date
         * @param string $post_date_gmt
         * @return bool|string
         */
        protected function _tp_resolve_post_date( $post_date = '', $post_date_gmt = '' ){
            if ( empty( $post_date ) || '0000-00-00 00:00:00' === $post_date ) {
                if ( empty( $post_date_gmt ) || '0000-00-00 00:00:00' === $post_date_gmt )
                    $post_date = (string)$this->_current_time( 'mysql' );
                else $post_date = (string)$this->_get_date_from_gmt( $post_date_gmt );
            }
            $month = substr( $post_date, 5, 2 );
            $day   = substr( $post_date, 8, 2 );
            $year  = substr( $post_date, 0, 4 );
            $valid_date = $this->_tp_check_date( $month, $day, $year, $post_date );
            if ( ! $valid_date ) return false;
            return $post_date;
        }//4917
        /**
         * @description Computes a unique slug for the post, when given the desired slug and some post details.
         * @param $slug
         * @param $post_ID
         * @param $post_status
         * @param $post_type
         * @param $post_parent
         * @return string
         */
        protected function _tp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent ):string{
            $this->tpdb = $this->_init_db();
            $tp_rewrite = $this->_init_rewrite();
            if ('user_request' === $post_type || ( 'inherit' === $post_status && 'revision' === $post_type ) || in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ), true ) )
                return $slug;
            $override_slug = $this->_apply_filters( 'pre_tp_unique_post_slug', null, $slug, $post_ID, $post_status, $post_type, $post_parent );
            if ( null !== $override_slug ) return $override_slug;
            $original_slug = $slug;
            $feeds = $tp_rewrite->feeds;
            if ( ! is_array( $feeds ) ) $feeds = [];
            if ( 'attachment' === $post_type ) {
                $check_sql       = TP_SELECT . " post_name FROM $this->tpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1";
                $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $slug, $post_ID ) );
                $is_bad_attachment_slug = $this->_apply_filters( 'tp_unique_post_slug_is_bad_attachment_slug', false, $slug );
                if ('embed' === $slug || $is_bad_attachment_slug ||  $post_name_check || in_array( $slug, $feeds, true )){
                    $suffix = 2;
                    do {
                        $alt_post_name   = $this->_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                        $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $alt_post_name, $post_ID ) );
                        $suffix++;
                    } while ( $post_name_check );
                    $slug = $alt_post_name;
                }
            }elseif ( $this->_is_post_type_hierarchical( $post_type ) ) {
                if ( 'nav_menu_item' === $post_type ) {
                    return $slug;
                }
                $check_sql= TP_SELECT . " post_name FROM $this->tpdb->posts WHERE post_name = %s AND post_type IN ( %s, 'attachment' ) AND ID != %d AND post_parent = %d LIMIT 1";
                $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $slug, $post_type, $post_ID, $post_parent ) );
                $is_bad_hierarchical_slug = $this->_apply_filters( 'tp_unique_post_slug_is_bad_hierarchical_slug', false, $slug, $post_type, $post_parent );
                if ( $post_name_check  || 'embed' === $slug|| $is_bad_hierarchical_slug || in_array( $slug, $feeds, true ) || preg_match( "@^($tp_rewrite->pagination_base)?\d+$@", $slug )) {
                    $suffix = 2;
                    do {
                        $alt_post_name   = $this->_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                        $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_ID, $post_parent ) );
                        $suffix++;
                    } while ( $post_name_check );
                    $slug = $alt_post_name;
                }
            }else{
                $check_sql       = TP_SELECT . " post_name FROM $this->tpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
                $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $slug, $post_type, $post_ID ) );
                $post = $this->_get_post( $post_ID );
                $conflicts_with_date_archive = false;
                if ( 'post' === $post_type && ( ! $post || $post->post_name !== $slug ) && preg_match( '/^\d+$/', $slug ) ) {
                    $slug_num = (int) $slug;
                    if ( $slug_num ) {
                        $permastructs   = array_values( array_filter( explode( '/', $this->_get_option( 'permalink_structure' ) ) ) );
                        $postname_index = array_search( '%postname%', $permastructs, true );
                        if ( 0 === $postname_index ||
                            ( $postname_index && '%year%' === $permastructs[ $postname_index - 1 ] && 13 > $slug_num ) ||
                            ( $postname_index && '%monthnum%' === $permastructs[ $postname_index - 1 ] && 32 > $slug_num )
                        )  $conflicts_with_date_archive = true;

                    }
                }
                $is_bad_flat_slug = $this->_apply_filters( 'tp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type );
                if ( 'embed' === $slug || $post_name_check || $conflicts_with_date_archive || $is_bad_flat_slug || in_array( $slug, $feeds, true )) {
                    $suffix = 2;
                    do {
                        $alt_post_name   = $this->_truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                        $post_name_check = $this->tpdb->get_var( $this->tpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_ID ) );
                        $suffix++;
                    } while ( $post_name_check );
                    $slug = $alt_post_name;
                }
            }
            return $this->_apply_filters( 'tp_unique_post_slug', $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug );
        }//4955
        /**
         * @description Truncate a post slug.
         * @param $slug
         * @param int $length
         * @return string
         */
        protected function _truncate_post_slug( $slug, $length = 200 ):string{
            if ( strlen( $slug ) > $length ) {
                $decoded_slug = urldecode( $slug );
                if ( $decoded_slug === $slug ) $slug = substr( $slug, 0, $length );
                else  $slug = $this->_utf8_uri_encode( $decoded_slug, $length, true );
            }
            return rtrim( $slug, '-' );
        }//5140
        /**
         * @description Add tags to a post.
         * @param int $post_id
         * @param string $tags
         * @return string
         */
        protected function _tp_add_post_tags( $post_id = 0, $tags = '' ):string{
            return $this->_tp_set_post_tags( $post_id, $tags, true );
        }//5165
        /**
         * @description Set the tags for a post.
         * @param int $post_id
         * @param string $tags
         * @param bool $append
         * @return string
         */
        protected function _tp_set_post_tags( $post_id = 0, $tags = '', $append = false ):string{
            return $this->_tp_set_post_terms( $post_id, $tags, 'post_tag', $append );
        }//5183
        /**
         * @description Set the terms for a post.
         * @param int $post_id
         * @param mixed $tags
         * @param string $taxonomy
         * @param bool $append
         * @return bool
         */
        protected function _tp_set_post_terms( $post_id = 0, $tags = '', $taxonomy = 'post_tag', $append = false ):bool{
            $post_id = (int) $post_id;
            if ( ! $post_id ) return false;
            if ( empty( $tags ) ) $tags =[];
            if ( ! is_array( $tags ) ) {
                $comma = $this->_x( ',', 'tag delimiter' );
                if ( ',' !== $comma ) $tags = str_replace( $comma, ',', $tags );
                $tags = explode( ',', trim( $tags, " \n\t\r\0\x0B," ) );
            }
            if ( $this->_is_taxonomy_hierarchical( $taxonomy ) )
                $tags = array_unique( array_map( 'intval', $tags ) );
            return $this->_tp_set_object_terms( $post_id, $tags, $taxonomy, $append );
        }//5204
        /**
         * @description Set categories for a post.
         * @param int $post_ID
         * @param array $post_categories
         * @param bool $append
         * @return bool
         */
        protected function _tp_set_post_categories( $post_ID = 0, $post_categories = [], $append = false ):bool{
            $post_ID     = (int) $post_ID;
            $post_type   = $this->_get_post_type( $post_ID );
            $post_status = $this->_get_post_status( $post_ID );
            $post_categories = (array) $post_categories;
            if ( empty( $post_categories ) ) {
                $default_category_post_types = $this->_apply_filters( 'default_category_post_types', array() );
                $default_category_post_types = array_merge( $default_category_post_types, array( 'post' ) );
                if ( 'auto-draft' !== $post_status && in_array( $post_type, $default_category_post_types, true ) && $this->_is_object_in_taxonomy( $post_type, 'category' )) {
                    $post_categories = [$this->_get_option( 'default_category' )];
                    $append = false;
                } else  $post_categories = [];
            } elseif ( 1 === count( $post_categories ) && '' === reset( $post_categories ) ) return true;
            return $this->_tp_set_post_terms( $post_ID, $post_categories, 'category', $append );
        }//5249
        /**
         * @description Fires actions related to the transitioning of a post's status.
         * @param $new_status
         * @param $old_status
         * @param $post
         */
        protected function _tp_transition_post_status( $new_status, $old_status, $post ):void{
            $this->_do_action( 'transition_post_status', $new_status, $old_status, $post );
            $this->_do_action( "{$old_status}_to_{$new_status}", $post );
            $this->_do_action( "{$new_status}_{$post->post_type}", $post->ID, $post, $old_status );
        }//5307
        /**
         * @description Fires actions after a post, its terms and meta data has been saved.
         * @param $post
         * @param $update
         * @param $post_before
         */
        protected function _tp_after_insert_post( $post, $update, $post_before ):void{
            $post = $this->_get_post( $post );
            if ( ! $post ) return;
            $post_id = $post->ID;
            $this->_do_action( 'tp_after_insert_post', $post_id, $post, $update, $post_before );
        }//5388
    }
}else die;