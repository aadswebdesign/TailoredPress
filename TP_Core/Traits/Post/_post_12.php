<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _post_12{
        use _init_db;
        use _init_cache;
        /**
         * @description Gets the timestamp of the last time any post was modified or published.
         * @param $timezone
         * @param $field
         * @param string $post_type
         * @return bool|null
         */
        protected function _get_last_post_time( $timezone, $field, $post_type = 'any' ):bool{
            $this->tpdb = $this->_init_db();
            if ( ! in_array( $field, array( 'date', 'modified' ), true ) ) return false;
            $timezone = strtolower( $timezone );
            $key = "lastpost{$field}:$timezone";
            if ( 'any' !== $post_type ) $key .= ':' . $this->_sanitize_key( $post_type );
            $date = $this->_tp_cache_get( $key, 'timeinfo' );
            if ( false !== $date ) return $date;

            if ( 'any' === $post_type ) {
                $post_types = $this->_get_post_types( array( 'public' => true ) );
                array_walk( $post_types, array( $this->tpdb, 'escape_by_ref' ) );
                $post_types = "'" . implode( "', '", $post_types ) . "'";
            } else $post_types = "'" . $this->_sanitize_key( $post_type ) . "'";
            switch ( $timezone ) {
                case 'gmt':
                    $date = $this->tpdb->get_var( TP_SELECT . " post_{$field}_gmt FROM $this->tpdb->posts WHERE post_status = 'publish' AND post_type IN ({$post_types}) ORDER BY post_{$field}_gmt DESC LIMIT 1" );
                    break;
                case 'blog':
                    $date = $this->tpdb->get_var( TP_SELECT . " post_{$field} FROM $this->tpdb->posts WHERE post_status = 'publish' AND post_type IN ({$post_types}) ORDER BY post_{$field}_gmt DESC LIMIT 1" );
                    break;
                case 'server':
                    $add_seconds_server = gmdate( 'Z' );
                    $date  = $this->tpdb->get_var( TP_SELECT . " DATE_ADD(post_{$field}_gmt, INTERVAL '$add_seconds_server' SECOND) FROM $this->tpdb->posts WHERE post_status = 'publish' AND post_type IN ({$post_types}) ORDER BY post_{$field}_gmt DESC LIMIT 1" );
                    break;
            }
            if ( $date ) {
                $this->_tp_cache_set( $key, $date, 'timeinfo' );
                return $date;
            }
            return false;
        }//7307
        /**
         * @description Updates posts in cache.
         * @param $posts
         */
        protected function _update_post_cache( &$posts ):void{
            if ( ! $posts ) return;
            foreach ( $posts as $post ) $this->_tp_cache_add( $post->ID, $post, 'posts' );
        }//7363
        /**
         * @description Will clean the post in the cache.
         * @param $post
         */
        protected function _clean_post_cache( $post ):void{
            if ( ! empty( $this->__tp_suspend_cache_invalidation ) ) return;
            $post = $this->_get_post( $post );
            if ( ! $post ) return;
            $this->_tp_cache_delete( $post->ID, 'posts' );
            $this->_tp_cache_delete( $post->ID, 'post_meta' );
            $this->_clean_object_term_cache( $post->ID, $post->post_type );
            $this->_tp_cache_delete( 'tp_get_archives', 'general' );
            $this->_do_action( 'clean_post_cache', $post->ID, $post );
            if ( 'page' === $post->post_type ) {
                $this->_tp_cache_delete( 'all_page_ids', 'posts' );
                $this->_do_action( 'clean_page_cache', $post->ID );
            }
            $this->_tp_cache_set( 'last_changed', microtime(), 'posts' );
        }//7388
        /**
         * @description Call major cache updating functions for list of Post objects.
         * @param $posts
         * @param mixed $post_type
         * @param bool $update_term_cache
         * @param bool $update_meta_cache
         */
        protected function _update_post_caches( &$posts, $post_type = 'post', $update_term_cache = true, $update_meta_cache = true ):void{
            if ( ! $posts ) return;
            $this->_update_post_cache( $posts );
            $post_ids = [];
            foreach ( $posts as $post ) $post_ids[] = $post->ID;
            if ( ! $post_type ) $post_type = 'any';
            if ( $update_term_cache ) {
                if ( is_array( $post_type ) ) $ptypes = $post_type;
                elseif ( 'any' === $post_type ) {
                    $ptypes = array();
                    foreach ( $posts as $post ) $ptypes[] = $post->post_type;
                    $ptypes = array_unique( $ptypes );
                } else  $ptypes = array( $post_type );

                if ( ! empty( $ptypes ) ) $this->_update_object_term_cache( $post_ids, $ptypes );
            }
            if ( $update_meta_cache ) $this->_update_post_meta_cache( $post_ids );
        }//7444
        /**
         * @description* Updates metadata cache for list of post IDs.
         * @param $post_ids
         * @return mixed
         */
        protected function _update_post_meta_cache( $post_ids ){
            return $this->_update_meta_cache( 'post', $post_ids );
        }//7497
        /**
         * @description Will clean the attachment in the cache.
         * @param $id
         * @param bool $clean_terms
         */
        protected function _clean_attachment_cache( $id, $clean_terms = false ):void{
            if ( ! empty( $this->__tp_suspend_cache_invalidation ) ) return;
            $id = (int) $id;
            $this->_tp_cache_delete( $id, 'posts' );
            $this->_tp_cache_delete( $id, 'post_meta' );
            if ( $clean_terms ) $this->_clean_object_term_cache( $id, 'attachment' );
            $this->_do_action( 'clean_attachment_cache', $id );
        }//7516
        /**
         * @description Hook for managing future post transitions to published.
         * @param $new_status
         * @param $old_status
         * @param $post
         */
        protected function _transition_post_status( $new_status, $old_status, $post ):void{
            $this->tpdb = $this->_init_db();
            if ('publish' !== $old_status && 'publish' === $new_status && '' === $this->_get_the_guid($post->ID)) $this->tpdb->update( $this->tpdb->posts, array( 'guid' => $this->_get_permalink( $post->ID ) ), array( 'ID' => $post->ID ) );
            if ( 'publish' === $new_status || 'publish' === $old_status ) {
                foreach ( array( 'server', 'gmt', 'blog' ) as $timezone ) {
                    $this->_tp_cache_delete( "lastpostmodified:$timezone", 'timeinfo' );
                    $this->_tp_cache_delete( "lastpostdate:$timezone", 'timeinfo' );
                    $this->_tp_cache_delete( "lastpostdate:$timezone:{$post->post_type}", 'timeinfo' );
                }
            }
            if ( $new_status !== $old_status ) {
                $this->_tp_cache_delete( $this->_count_posts_cache_key( $post->post_type ), 'counts' );
                $this->_tp_cache_delete( $this->_count_posts_cache_key( $post->post_type, 'readable' ), 'counts' );
            }
            $this->_tp_clear_scheduled_hook( 'publish_future_post', array( $post->ID ) );
        }//7559
        /**
         * @description Hook used to schedule publication for a post marked for the future.
         * @param $post
         */
        protected function _future_post_hook( $post ):void{
            $this->_tp_clear_scheduled_hook( 'publish_future_post', array( $post->ID ) );
            $this->_tp_schedule_single_event( strtotime( $this->_get_gmt_from_date( $post->post_date ) . ' GMT' ), 'publish_future_post', array( $post->ID ) );
        }//7610
        /**
         * @description Hook to schedule pings and enclosures when a post is published.
         * @param $post_id
         */
        protected function _publish_post_hook( $post_id ):void{
            if ( defined( 'XMLRPC_REQUEST' ) ) $this->_do_action( 'xmlrpc_publish_post', $post_id );
            if ( defined( 'TP_IMPORTING' ) ) return;
            if ( $this->_get_option( 'default_pingback_flag' ) ) $this->_add_post_meta( $post_id, '_pingme', '1', true );
            $this->_add_post_meta( $post_id, '_encloseme', '1', true );
            $to_ping = $this->_get_to_ping( $post_id );
            if ( ! empty( $to_ping ) ) $this->_add_post_meta( $post_id, '_trackbackme', '1' );
            if ( ! $this->_tp_next_scheduled( 'do_pings' ) ) $this->_tp_schedule_single_event( time(), 'do_pings' );
        }//7625
        /**
         * @description Returns the ID of the post's parent.
         * @param null $post
         * @return bool|int
         */
        protected function _tp_get_post_parent_id( $post = null ){
            $post = $this->_get_post( $post );
            if ( ! $post || $this->_init_error( $post ) ) return false;
            return (int) $post->post_parent;
        }//7666
        /**
         * @description Check the given subset of the post hierarchy for hierarchy loops.
         * @param $post_parent
         * @param $post_ID
         * @return int
         */
        protected function _tp_check_post_hierarchy_for_loops( $post_parent, $post_ID ):int{
            if ( ! $post_parent ) return 0;
            if ( ! $post_ID ) return $post_parent;
            if ( $post_parent === $post_ID ) return 0;
            $loop = $this->_tp_find_hierarchy_loop( 'tp_get_post_parent_id', $post_ID, $post_parent );
            if ( ! $loop ) return $post_parent; // No loop.
            if ( isset( $loop[ $post_ID ] ) ) return 0;
            foreach ( array_keys( $loop ) as $loop_member )
                $this->_tp_update_post(['ID' => $loop_member,'post_parent' => 0,]);
            return $post_parent;
        }//7688
    }
}else die;