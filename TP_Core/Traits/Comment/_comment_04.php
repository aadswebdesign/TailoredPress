<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Comment;
use TP_Core\Libs\DB\TP_Db;
if(ABSPATH){
    trait _comment_04 {
        use _init_error;
        /**
         * @description The status of a comment by ID.
         * @param $comment_id
         * @return bool|string
         */
        protected function _tp_get_comment_status( $comment_id ){
            $comment = $this->_get_comment( $comment_id );
            if ( ! $comment ) return false;
            $approved = $comment->comment_approved;
            if ( null === $approved ) return false;
            elseif ( '1' === $approved ) return 'approved';
            elseif ( '0' === $approved ) return 'unapproved';
            elseif ( 'spam' === $approved ) return 'spam';
            elseif ( 'trash' === $approved ) return 'trash';
            else return false;
        }//1725
        /**
         * @description Call hooks for when a comment status transition occurs.
         * @param $new_status
         * @param $old_status
         * @param TP_Comment $comment
         */
        protected function _tp_transition_comment_status( $new_status, $old_status,TP_Comment $comment ):void{
            $comment_statuses = [
                0 => 'unapproved', 'hold' => 'unapproved',
                1 => 'approved', 'approve' => 'approved',
            ];
            if ( isset( $comment_statuses[ $new_status ] ) )
                $new_status = $comment_statuses[ $new_status ];
            if ( isset( $comment_statuses[ $old_status ] ) )
                $old_status = $comment_statuses[ $old_status ];
            if ( $new_status !== $old_status ) {
                $this->_do_action( 'transition_comment_status', $new_status, $old_status, $comment );
                $this->_do_action( "comment_{$old_status}_to_{$new_status}", $comment );
            }
            $this->_do_action( "comment_{$new_status}_{$comment->comment_type}", $comment->comment_ID, $comment );
        }//1766
        /** @noinspection PhpUnusedPrivateMethodInspection */
        /**
         * @description Clear the last comment modified cached value when a comment status is changed.
         * @param $new_status
         * @param $old_status
         */
        protected function _clear_modified_cache_on_transition_comment_status( $new_status, $old_status ):void{
            if ( 'approved' === $new_status || 'approved' === $old_status ) {
                $data = [];
                foreach ( [ 'server', 'gmt', 'blog'] as $timezone )
                    $data[] = "last_comment_modified:$timezone";
                $this->_tp_cache_delete_multiple( $data, 'time_info' );
            }
        }//1858
        /**
         * @description Get current commenter's name, email, and URL.
         * @return mixed
         */
        protected function _tp_get_current_commenter(){
            $comment_author = '';
            $cookie_author = $_COOKIE[ 'comment_author_' . COOKIE_HASH ];
            if ( isset($cookie_author) )
                $comment_author = $_COOKIE[ 'comment_author_' . COOKIE_HASH ];
            $comment_author_email = '';
            $cookie_author_email = $_COOKIE[ 'comment_author_email_' . COOKIE_HASH ];
            if ( isset($cookie_author_email) )
                $comment_author_email = $_COOKIE[ 'comment_author_email_' . COOKIE_HASH ];
            $comment_author_url = '';
            $_cookie_author_url = $_COOKIE[ 'comment_author_url_' . COOKIE_HASH ];
            if ( isset($_cookie_author_url) )
                $comment_author_url = $_COOKIE[ 'comment_author_url_' . COOKIE_HASH ];
            return $this->_apply_filters( 'tp_get_current_commenter', compact( 'comment_author', 'comment_author_email', 'comment_author_url' ) );
        }//1886
        /**
         * @description Get unapproved comment author's email.
         * @return string
         */
        protected function _tp_get_unapproved_comment_author_email():string{
            $commenter_email = '';
            if ( ! empty( $_GET['unapproved'] ) && ! empty( $_GET['moderation-hash'] ) ) {
                $comment_id = (int) $_GET['unapproved'];
                $comment    = $this->_get_comment( $comment_id );
                if ( $comment && hash_equals( $_GET['moderation-hash'], $this->_tp_hash( $comment->comment_date_gmt ) ) ) {
                    $comment_preview_expires = strtotime( $comment->comment_date_gmt . '+10 minutes' );
                    if ( time() < $comment_preview_expires )
                        $commenter_email = $comment->comment_author_email;
                }
            }
            if ( ! $commenter_email ) {
                $commenter       = $this->_tp_get_current_commenter();
                $commenter_email = $commenter['comment_author_email'];
            }
            return $commenter_email;
        }//1931
        /**
         * @description Inserts a comment into the database.
         * @param $comment_data
         * @return bool|int
         */
        protected function _tp_insert_comment( $comment_data ){
            $tpdb = $this->_init_db();
            $data = $this->_tp_unslash( $comment_data );
            $comment_author       = '' ?? $data['comment_author'];
            $comment_author_email = '' ?? $data['comment_author_email'];
            $comment_author_url   = '' ?? $data['comment_author_url'];
            $comment_author_IP    = '' ?? $data['comment_author_IP'];
            $comment_date     = $this->_current_time( 'mysql' ) ?? $data['comment_date'];
            $comment_date_gmt = $this->_get_gmt_from_date( $comment_date ) ?? $data['comment_date_gmt'];
            $comment_post_ID  = 0 ?? $data['comment_post_ID'];
            $comment_content  = '' ?? $data['comment_content'];
            $comment_karma    = 0 ?? $data['comment_karma'];
            $comment_approved = 1 ?? $data['comment_approved'];
            $comment_agent    = '' ?? $data['comment_agent'];
            $comment_type     = empty( $data['comment_type'] ) ? 'comment' : $data['comment_type'];
            $comment_parent   = 0 ?? $data['comment_parent'];
            $user_id = 0 ?? $data['user_id'];
            $compacted = compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent', 'comment_type', 'comment_parent', 'user_id' );
            if (($tpdb instanceof TP_Db) && ! $tpdb->insert( $tpdb->comments, $compacted ) ) return false;
            $id = (int) $tpdb->insert_id;
            if ( 1 === $comment_approved ) {
                $this->_tp_update_comment_count( $comment_post_ID );
                $data =[];
                foreach ([ 'server', 'gmt', 'blog' ] as $timezone )
                    $data[] = "last_comment_modified:$timezone";
                $this->_tp_cache_delete_multiple( $data, 'time_info' );
            }
            $this->_clean_comment_cache( $id );
            $comment = $this->_get_comment( $id );
            if ( isset( $comment_data['comment_meta'] ) && is_array( $comment_data['comment_meta'] ) ) {
                foreach ( $comment_data['comment_meta'] as $meta_key => $meta_value )
                    $this->_add_comment_meta( $comment->comment_ID, $meta_key, $meta_value, true );
            }
            $this->_do_action( 'tp_insert_comment', $id, $comment );
            return $id;
        }//1992
        /**
         * @description Filters and sanitizes comment data.
         * @param $comment_data
         * @return mixed
         */
        protected function _tp_filter_comment( $comment_data ) {
            if ( isset( $comment_data['user_ID'] ) )
                $comment_data['user_id'] = $this->_apply_filters( 'pre_user_id', $comment_data['user_ID'] );
            elseif ( isset( $comment_data['user_id'] ) )
                $comment_data['user_id'] = $this->_apply_filters( 'pre_user_id', $comment_data['user_id'] );
            $comment_data['comment_agent'] = $this->_apply_filters( 'pre_comment_user_agent', $comment_data['comment_agent'] ?? '' );
            $comment_data['comment_author'] = $this->_apply_filters( 'pre_comment_author_name', $comment_data['comment_author'] );
            $comment_data['comment_content'] = $this->_apply_filters( 'pre_comment_content', $comment_data['comment_content'] );
            $comment_data['comment_author_IP'] = $this->_apply_filters( 'pre_comment_user_ip', $comment_data['comment_author_IP'] );
            $comment_data['comment_author_url'] = $this->_apply_filters( 'pre_comment_author_url', $comment_data['comment_author_url'] );
            $comment_data['comment_author_email'] = $this->_apply_filters( 'pre_comment_author_email', $comment_data['comment_author_email'] );
            $comment_data['filtered']             = true;
            return $comment_data;
        }//2067
        /**
         * @description Whether a comment should be blocked because of comment flood.
         * @param $block
         * @param $time_last_comment
         * @param $time_new_comment
         * @return bool
         */
        protected function _tp_throttle_comment_flood( $block, $time_last_comment, $time_new_comment ):bool{
            if ($block) return $block;
            if (( $time_new_comment - $time_last_comment )< 15 )
                return true;
            return false;
        }//2129
        /**
         * @description Adds a new comment to the database.
         * @param $comment_data
         * @param bool $tp_error
         * @return bool|int
         */
        protected function _tp_new_comment( $comment_data, $tp_error = false ){
            $tpdb = $this->_init_db();
            if ( isset( $comment_data['user_ID'] ) ) {
                $comment_data['user_ID'] = (int) $comment_data['user_ID'];
                $comment_data['user_id'] = $comment_data['user_ID'];
            }
            $pre_filtered_user_id = $comment_data['user_id'] ?? 0;
            if ( ! isset( $comment_data['comment_author_IP'] ) )
                $comment_data['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
            if ( ! isset( $comment_data['comment_agent'] ) )
                $comment_data['comment_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $comment_data = $this->_apply_filters( 'pre_process_comment', $comment_data );
            $comment_data['comment_post_ID'] = (int) $comment_data['comment_post_ID'];
            if ( isset( $comment_data['user_ID'] ) && $pre_filtered_user_id !== (int) $comment_data['user_ID'] ) {
                $comment_data['user_ID'] = (int) $comment_data['user_ID'];
                $comment_data['user_id'] = $comment_data['user_ID'];
            } elseif ( isset( $comment_data['user_id'] ) )
                $comment_data['user_id'] = (int) $comment_data['user_id'];
            $comment_data['comment_parent'] = isset( $comment_data['comment_parent'] ) ? $this->_abs_int( $comment_data['comment_parent'] ) : 0;
            $parent_status = ( $comment_data['comment_parent'] > 0 ) ? $this->_tp_get_comment_status( $comment_data['comment_parent'] ) : '';
            $comment_data['comment_parent'] = ( 'approved' === $parent_status || 'unapproved' === $parent_status ) ? $comment_data['comment_parent'] : 0;
            $comment_data['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $comment_data['comment_author_IP'] );
            $comment_data['comment_agent'] = substr( $comment_data['comment_agent'], 0, 254 );
            if ( empty( $comment_data['comment_date'] ) )
                $comment_data['comment_date'] = $this->_current_time( 'mysql' );
            if ( empty( $comment_data['comment_date_gmt'] ) )
                $comment_data['comment_date_gmt'] = $this->_current_time( 'mysql', 1 );
            if ( empty( $comment_data['comment_type'] ) )
                $comment_data['comment_type'] = 'comment';
            $comment_data = $this->_tp_filter_comment( $comment_data );
            $comment_data['comment_approved'] = $this->_tp_allow_comment( $comment_data, $tp_error );
            if ( $this->_init_error( $comment_data['comment_approved'] ) )
                return $comment_data['comment_approved'];
            $comment_ID = $this->_tp_insert_comment( $comment_data );
            if ( ! $comment_ID ) {
                $fields = array( 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content' );
                foreach ( $fields as $field ) {
                    if (($tpdb instanceof TP_Db) && isset( $comment_data[ $field ] ) )
                        $comment_data[ $field ] = $tpdb->strip_invalid_text_for_column( $tpdb->comments, $field, $comment_data[ $field ] );
                }
                $comment_data = $this->_tp_filter_comment( $comment_data );
                $comment_data['comment_approved'] = $this->_tp_allow_comment( $comment_data, $tp_error );
                if ( $this->_init_error( $comment_data['comment_approved'] ) )
                    return $comment_data['comment_approved'];
                $comment_ID = $this->_tp_insert_comment( $comment_data );
                if ( ! $comment_ID ) return false;
            }
            $this->_do_action( 'comment_post', $comment_ID, $comment_data['comment_approved'], $comment_data );
            return $comment_ID;
        }//2186
        /**
         * @description Send a comment moderation notification to the comment moderator.
         * @param $comment_ID
         * @return bool
         */
        protected function _tp_new_comment_notify_moderator( $comment_ID ):bool{
            $comment = $this->_get_comment( $comment_ID );
            $maybe_notify = ( '0' === $comment->comment_approved );
            $maybe_notify = $this->_apply_filters( 'notify_moderator', $maybe_notify, $comment_ID );
            if ( ! $maybe_notify ) return false;
            return $this->_tp_notify_moderator( $comment_ID );
        }//2297
    }
}else die;