<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _comment_05{
        use _init_db;
        /**
         * @description Send a notification of a new comment to the post author.
         * @param $comment_ID
         * @return bool
         */
        protected function _tp_new_comment_notify_post_author( $comment_ID ):bool{
            $comment = $this->_get_comment( $comment_ID );
            $maybe_notify = $this->_get_option( 'comments_notify' );
            $maybe_notify = $this->_apply_filters( 'notify_post_author', $maybe_notify, $comment_ID );
            if ( ! $maybe_notify ) return false;
            if ( ! isset( $comment->comment_approved ) || '1' !== $comment->comment_approved )
                return false;
            return $this->_tp_notify_post_author( $comment_ID );
        }//2323
        /**
         * @description Sets the status of a comment.
         * @param $comment_id
         * @param $comment_status
         * @param bool $tp_error
         * @return bool|TP_Error
         */
        protected function _tp_set_comment_status( $comment_id, $comment_status, $tp_error = false ){
            $tpdb = $this->_init_db();
            switch ( $comment_status ) {
                case 'hold':
                case '0':
                    $status = '0';
                    break;
                case 'approve':
                case '1':
                    $status = '1';
                    $this->_add_action( 'tp_set_comment_status', 'tp_new_comment_notify_postauthor' );
                    break;
                case 'spam':
                    $status = 'spam';
                    break;
                case 'trash':
                    $status = 'trash';
                    break;
                default:
                    return false;
            }
            $comment_old = clone $this->_get_comment( $comment_id );
            if ( ! $tpdb->update( $tpdb->comments, array( 'comment_approved' => $status ), array( 'comment_ID' => $comment_old->comment_ID ) ) ) {
                if ( $tp_error )
                    return new TP_Error( 'db_update_error', $this->__( 'Could not update comment status.' ), $tpdb->last_error );
                else  return false;
            }
            $this->_clean_comment_cache( $comment_old->comment_ID );
            $comment = $this->_get_comment( $comment_old->comment_ID );
            $this->_do_action( 'wp_set_comment_status', $comment->comment_ID, $comment_status );
            $this->_tp_transition_comment_status( $comment_status, $comment_old->comment_approved, $comment );
            $this->_tp_update_comment_count( $comment->comment_post_ID );
            return true;
        }//2371
        /**
         * @description Updates an existing comment in the database.
         * @param $comment_arr
         * @param bool $tp_error
         * @return bool|TP_Error
         */
        protected function _tp_update_comment( $comment_arr, $tp_error = false ){
            $tpdb = $this->_init_db();
            $comment = $this->_get_comment( $comment_arr['comment_ID'], ARRAY_A );
            if ( empty( $comment ) ) {
                if ( $tp_error )  return new TP_Error( 'invalid_comment_id', $this->__( 'Invalid comment ID.' ) );
                else  return false;
            }
            if ( ! empty( $comment_arr['comment_post_ID'] ) && ! $this->_get_post( $comment_arr['comment_post_ID'] ) ) {
                if ( $tp_error )  return new TP_Error( 'invalid_post_id', $this->__( 'Invalid post ID.' ) );
                else return false;
            }
            $comment = $this->_tp_slash( $comment );
            $old_status = $comment['comment_approved'];
            $comment_arr = array_merge( $comment, $comment_arr );
            $comment_arr = $this->_tp_filter_comment( $comment_arr );
            $data = $this->_tp_unslash( $comment_arr );
            $data['comment_content'] = $this->_apply_filters( 'comment_save_pre', $data['comment_content'] );
            $data['comment_date_gmt'] = $this->_get_gmt_from_date( $data['comment_date'] );
            if ( ! isset( $data['comment_approved'] ) )
                $data['comment_approved'] = 1;
            elseif ( 'hold' === $data['comment_approved'] )
                $data['comment_approved'] = 0;
            elseif ( 'approve' === $data['comment_approved'] )
                $data['comment_approved'] = 1;
            $comment_ID      = $data['comment_ID'];
            $comment_post_ID = $data['comment_post_ID'];
            $data = $this->_apply_filters( 'tp_update_comment_data', $data, $comment, $comment_arr );
            if ( $this->_init_error( $data ) ) {
                if ( $tp_error ) return $data;
                else return false;
            }
            $keys = array( 'comment_post_ID', 'comment_content', 'comment_author', 'comment_author_email', 'comment_approved', 'comment_karma', 'comment_author_url', 'comment_date', 'comment_date_gmt', 'comment_type', 'comment_parent', 'user_id', 'comment_agent', 'comment_author_IP' );
            $data = $this->_tp_array_slice_assoc( $data, $keys );
            $updated_val = $tpdb->update( $tpdb->comments, $data, compact( 'comment_ID' ) );
            if ( false === $updated_val ) {
                if ( $tp_error )
                    return new TP_Error( 'db_update_error', $this->__( 'Could not update comment in the database.' ), $tpdb->last_error );
                else return false;
            }
            if ( isset( $comment_arr['comment_meta'] ) && is_array( $comment_arr['comment_meta'] ) ) {
                foreach ( $comment_arr['comment_meta'] as $meta_key => $meta_value )
                    $this->_update_comment_meta( $comment_ID, $meta_key, $meta_value );
            }
            $this->_clean_comment_cache( $comment_ID );
            $this->_tp_update_comment_count( $comment_post_ID );
            $this->_do_action( 'edit_comment', $comment_ID, $data );
            $comment = $this->_get_comment( $comment_ID );
            $this->_tp_transition_comment_status( $comment->comment_approved, $old_status, $comment );
            return $updated_val;
        }//2445
        /**
         * @description Whether to defer comment counting.
         * @param null $defer
         * @return bool
         */
        protected function _tp_defer_comment_counting( $defer = null ):bool{
            static $_defer = false;
            if ( is_bool( $defer ) ) {
                $_defer = $defer;
                if ( ! $defer ) $this->_tp_update_comment_count( null, true );
            }
            return $_defer;
        }//2582
        /**
         * @description Updates the comment count for post(s).
         * @param $post_id
         * @param bool $do_deferred
         * @return bool|null|string
         */
        protected function _tp_update_comment_count( $post_id, $do_deferred = false ){
            static $_deferred = array();
            if ( empty( $post_id ) && ! $do_deferred ) return false;
            if ( $do_deferred ) {
                $_deferred = array_unique( $_deferred );
                foreach ( $_deferred as $i => $_post_id ) {
                    $this->_tp_update_comment_count_now( $_post_id );
                    unset( $_deferred[ $i ] );
                    /** @todo Move this outside of the foreach and reset $_deferred to an array instead */
                }
            }
            if (  $this->_tp_defer_comment_counting() ) {
                $_deferred[] = $post_id;
                return true;
            }elseif ( $post_id )
                return  $this->_tp_update_comment_count_now( $post_id );
            return null;
        }//2617
        /**
         * @description Updates the comment count for the post.
         * @param $post_id
         * @return bool
         */
        protected function _tp_update_comment_count_now( $post_id ):bool{
            $tpdb = $this->_init_db();
            $post_id = (int) $post_id;
            if ( ! $post_id ) return false;
            $this->_tp_cache_delete( 'comments-0', 'counts' );
            $this->_tp_cache_delete( "comments-{$post_id}", 'counts' );
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $old = (int) $post->comment_count;
            $new = $this->_apply_filters( 'pre_wp_update_comment_count_now', null, $old, $post_id );
            if ( is_null( $new ) )
                $new = (int) $tpdb->get_var( $tpdb->prepare( TP_SELECT . " COUNT(*) FROM $tpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1'", $post_id ) );
            else $new = (int) $new;
            $tpdb->update( $tpdb->posts, array( 'comment_count' => $new ), array( 'ID' => $post_id ) );
            $this->_clean_post_cache( $post );
            $this->_do_action( 'tp_update_comment_count', $post_id, $new, $old );
            $this->_do_action( "edit_post_{$post->post_type}", $post_id, $post );
            $this->_do_action( 'edit_post', $post_id, $post );
            return true;
        }//2652
        /**
         * @description Finds a pingback server URI based on the given URL.
         * @param $url
         * @return bool|string
         */
        protected function _discover_pingback_server_uri( $url){
            $pingback_str_dquote = 'rel="pingback"';
            $pingback_str_squote = 'rel=\'pingback\'';
            $parsed_url = parse_url( $url );
            if ( ! isset( $parsed_url['host'] ) ) return false;
            $uploads_dir = $this->_tp_get_upload_dir();
            if ( 0 === strpos( $url, $uploads_dir['baseurl'] ) ) return false;
            $response = $this->_tp_safe_remote_head(
                $url, ['timeout' => 2,'httpversion' => '1.0',]
            );
            if ( $this->_init_error( $response ) ) return false;
            if ( $this->_tp_remote_retrieve_header( $response, 'x-pingback' ) )
                return $this->_tp_remote_retrieve_header( $response, 'x-pingback' );
            if ( preg_match( '#(image|audio|video|model)/.#is', $this->_tp_remote_retrieve_header( $response, 'content-type' ) ) )
                return false;
            $response = $this->_tp_safe_remote_get( $url,['timeout' => 2,'httpversion' => '1.0',]);
            if ( $this->_init_error( $response ) ) return false;
            $contents = $this->_tp_remote_retrieve_body( $response );
            $pingback_link_offset_dquote = strpos( $contents, $pingback_str_dquote );
            $pingback_link_offset_squote = strpos( $contents, $pingback_str_squote );
            if ( $pingback_link_offset_dquote || $pingback_link_offset_squote ) {
                $quote                   = ( $pingback_link_offset_dquote ) ? '"' : '\'';
                $pingback_link_offset    = ( '"' === $quote ) ? $pingback_link_offset_dquote : $pingback_link_offset_squote;
                $pingback_href_pos       = strpos( $contents, 'href=', $pingback_link_offset );
                $pingback_href_start     = $pingback_href_pos + 6;
                $pingback_href_end       = strpos( $contents, $quote, $pingback_href_start );
                $pingback_server_url_len = $pingback_href_end - $pingback_href_start;
                $pingback_server_url     = substr( $contents, $pingback_href_start, $pingback_server_url_len );
                if ( $pingback_server_url_len > 0 )  return $pingback_server_url;
            }
            return false;
        }//2727
        /**
         * @description Perform all ping backs, enclosures, trackbacks, and send to pingback services.
         */
        protected function _do_all_pings():void{
            $this->_do_action( 'do_all_pings' );
        }//2810
        /**
         * @description Perform all ping backs.
         */
        protected function _do_all_ping_backs():void{
            $pings = $this->_get_posts([
                    'post_type' => $this->_get_post_types(),'suppress_filters' => false,
                    'no_paging' => true,'meta_key' => '_ping_me','fields' => 'ids',]
            );
            foreach ( $pings as $ping ) {
                $this->_delete_post_meta( $ping, '_ping_me' );
                $this->_pingback( null, $ping );
            }
        }//2824
        /**
         * @description Performs all enclosures.
         */
        protected function _do_all_enclosures():void{
            $enclosures = $this->_get_posts(
                ['post_type' => $this->_get_post_types(),'suppress_filters' => false,
                    'no_paging' => true,'meta_key' => '_enclose_me','fields' => 'ids',] );
            foreach ( $enclosures as $enclosure ) {
                $this->_delete_post_meta( $enclosure, '_enclose_me' );
                $this->_do_enclose( null, $enclosure );
            }
        }//2846
    }
}else die;