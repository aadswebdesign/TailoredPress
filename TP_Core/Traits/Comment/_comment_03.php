<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Comment;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _comment_03 {
        use _init_db;
        use _init_comment;
        use _init_error;
        use _init_queries;
        /**
         * @description Calculate what page number a comment will appear on for comment paging.
         * @param TP_Comment $comment_ID
         * @param array $args
         * @return array|null|TP_Comment|void
         */
        protected function _get_page_of_comment($comment_ID, ...$args ){
            $tpdb = $this->_init_db();
            $page = null;
            $_comment = $this->_get_comment( $comment_ID );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return null;
            $defaults = ['type' => 'all', 'page' => '', 'per_page' => '', 'max_depth' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $original_args = $args;
            if ( $this->_get_option( 'page_comments' ) ) {
                if ( '' === $args['per_page'] )
                    $args['per_page'] = $this->_get_query_var( 'comments_per_page' );
                if ( '' === $args['per_page'] )
                    $args['per_page'] = $this->_get_option( 'comments_per_page' );
            }
            if ( empty( $args['per_page'] ) ) {
                $args['per_page'] = 0;
                $args['page']     = 0;
            }
            if ( $args['per_page'] < 1 ) $page = 1;
            if ( null === $page ) {
                if ( '' === $args['max_depth'] ) {
                    if ( $this->_get_option( 'thread_comments' ) )
                        $args['max_depth'] = $this->_get_option( 'thread_comments_depth' );
                    else $args['max_depth'] = -1;
                }
                if ( $args['max_depth'] > 1 && 0 !== $comment->comment_parent )
                    return $this->_get_page_of_comment( $comment->comment_parent, $args );
                $comment_args = [
                    'type'       => $args['type'],
                    'post_id'    => $comment->comment_post_ID,
                    'fields'     => 'ids',
                    'count'      => true,
                    'status'     => 'approve',
                    'parent'     => 0,
                    'date_query' => [['column' => "$tpdb->comments.comment_date_gmt",
                        'before' => $comment->comment_date_gmt,],],
                ];
                if ( $this->_is_user_logged_in() )
                    $comment_args['include_unapproved'] = [$this->_get_current_user_id()];
                else {
                    $unapproved_email = $this->_tp_get_unapproved_comment_author_email();
                    if ( $unapproved_email )
                        $comment_args['include_unapproved'] = array( $unapproved_email );
                }
                $comment_args = $this->_apply_filters( 'get_page_of_comment_query_args', $comment_args );
                $comment_query       = $this->_init_comment_query();
                $older_comment_count = $comment_query->query_comment( $comment_args );
                if ( 0 === $older_comment_count ) $page = 1;
                else $page = ceil( ( $older_comment_count + 1 ) / $args['per_page'] );
            }
            return $this->_apply_filters( 'get_page_of_comment', (int) $page, $args, $original_args, $comment_ID );
        }//1058 old1084
        /**
         * @description Retrieves the maximum character lengths for the comment form fields.
         * @return mixed
         */
        protected function _tp_get_comment_fields_max_lengths(){
            $tpdb = $this->_init_db();
            $lengths = ['comment_author' => 245, 'comment_author_email' => 100, 'comment_author_url' => 200, 'comment_content' => 65525, ];
            if ( $tpdb->is_mysql ) {
                foreach ( $lengths as $column => $length ) {
                    $col_length = $tpdb->get_col_length( $tpdb->comments, $column );
                    $max_length = 0;
                    if ( $this->_init_error( $col_length ) ) break;
                    if ( ! is_array( $col_length ) && (int) $col_length > 0 )
                        $max_length = (int) $col_length;
                    elseif ( is_array( $col_length ) && isset( $col_length['length'] ) && (int) $col_length['length'] > 0 ) {
                        $max_length = (int) $col_length['length'];
                        if ( ! empty( $col_length['type'] ) && 'byte' === $col_length['type'] ) $max_length .= $max_length - 10;
                    }
                    if ( $max_length > 0 ) $lengths[ $column ] = $max_length;
                }
            }
            return $this->_apply_filters( 'tp_get_comment_fields_max_lengths', $lengths );
        }//1214
        /**
         * @description Compares the lengths of comment data against the maximum character limits.
         * @param $comment_data
         * @return bool|TP_Error
         */
        protected function _tp_check_comment_data_max_lengths( $comment_data ){
            $max_lengths = $this->_tp_get_comment_fields_max_lengths();
            if ( isset( $comment_data['comment_author'] ) && mb_strlen( $comment_data['comment_author'], '8bit' ) > $max_lengths['comment_author'] )
                return new TP_Error( 'comment_author_column_length', $this->__( '<strong>Error</strong>: Your name is too long.' ), 200 );
            if ( isset( $comment_data['comment_author_email'] ) && strlen( $comment_data['comment_author_email'] ) > $max_lengths['comment_author_email'] )
                return new TP_Error( 'comment_author_email_column_length', $this->__( '<strong>Error</strong>: Your email address is too long.' ), 200 );
            if ( isset( $comment_data['comment_author_url'] ) && strlen( $comment_data['comment_author_url'] ) > $max_lengths['comment_author_url'] )
                return new TP_Error( 'comment_author_url_column_length', $this->__( '<strong>Error</strong>: Your URL is too long.' ), 200 );
            if ( isset( $comment_data['comment_content'] ) && mb_strlen( $comment_data['comment_content'], '8bit' ) > $max_lengths['comment_content'] )
                return new TP_Error( 'comment_content_column_length', $this->__( '<strong>Error</strong>: Your comment is too long.' ), 200 );
            return true;
        }//1269
        /**
         * @description Checks if a comment contains disallowed characters or words.
         * @param $author
         * @param $email
         * @param $url
         * @param $comment
         * @param $user_ip
         * @param $user_agent
         * @return string
         */
        protected function _tp_check_comment_disallowed_list( $author, $email, $url, $comment, $user_ip, $user_agent ):string{
            $this->_do_action(
                'tp_blacklist_check',
                [$author, $email, $url, $comment, $user_ip, $user_agent],
                '0.0.1','tp_check_comment_disallowed_list',
                $this->__( 'Please consider writing more inclusive code.' )
            );
            $this->_do_action( 'tp_check_comment_disallowed_list', $author, $email, $url, $comment, $user_ip, $user_agent );
            $mod_keys = trim( $this->_get_option( 'disallowed_keys' ) );
            if ( '' === $mod_keys ) return false; // If moderation keys are empty.
            $comment_without_html = $this->_tp_strip_all_tags( $comment );
            $words = explode( "\n", $mod_keys );
            foreach ($words as $word ) {
                $word = trim( $word );
                if ( empty( $word ) ) continue;
                $word = preg_quote( $word, '#' );
                $pattern = "#$word#i";
                if ( preg_match( $pattern, $author )
                    || preg_match( $pattern, $email )
                    || preg_match( $pattern, $url )
                    || preg_match( $pattern, $comment )
                    || preg_match( $pattern, $comment_without_html )
                    || preg_match( $pattern, $user_ip )
                    || preg_match( $pattern, $user_agent )
                ) return true;
            }
            return false;
        }//1304
        /**
         * @description Retrieves the total comment counts for the whole site or a single post.
         * @param int $post_id
         * @return mixed
         */
        protected function _tp_count_comments( $post_id = 0 ){
            $post_id = (int) $post_id;
            $filtered = $this->_apply_filters( 'tp_count_comments', array(), $post_id );
            if ( ! empty( $filtered ) ) return $filtered;
            $count = $this->_tp_cache_get( "comments_{$post_id}", 'counts' );
            if ( false !== $count ) return $count;
            $stats = $this->_get_comment_count( $post_id );
            $stats['moderated'] = $stats['awaiting_moderation'];
            unset( $stats['awaiting_moderation'] );
            $stats_object = (object) $stats;
            $this->_tp_cache_set( "comments_{$post_id}", $stats_object, 'counts' );
            return $stats_object;
        }//1400
        /**
         * @description Trashes or deletes a comment.
         * @param $comment_id
         * @param bool $force_delete
         * @return bool|string
         */
        protected function _tp_delete_comment( $comment_id, $force_delete = false ){
            $tpdb = $this->_init_db();
            $_comment = $this->_get_comment( $comment_id );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return false;
            if ( ! $force_delete && EMPTY_TRASH_DAYS && ! in_array( $this->_tp_get_comment_status( $comment ), array( 'trash', 'spam' ), true ) )
                return $this->_tp_trash_comment( $comment_id );
            $this->_do_action( 'delete_comment', $comment->comment_ID, $comment );
            $children = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " comment_ID FROM $tpdb->comments WHERE comment_parent = %d", $comment->comment_ID ) );
            if ( ! empty( $children ) ) {
                $tpdb->update( $tpdb->comments, array( 'comment_parent' => $comment->comment_parent ), array( 'comment_parent' => $comment->comment_ID ) );
                $this->_clean_comment_cache( $children );
            }
            $meta_ids = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " meta_id FROM $tpdb->comment_meta WHERE comment_id = %d", $comment->comment_ID ) );
            foreach ( $meta_ids as $mid ) $this->_delete_metadata_by_mid( 'comment', $mid );
            if ( ! $tpdb->delete( $tpdb->comments, array( 'comment_ID' => $comment->comment_ID ) ) )
                return false;
            $this->_do_action( 'deleted_comment', $comment->comment_ID, $comment );
            $post_id = $comment->comment_post_ID;
            if ( $post_id && 1 === $comment->comment_approved )
                $this->_tp_update_comment_count( $post_id );
            $this->_clean_comment_cache( $comment->comment_ID );
            $this->_do_action( 'tp_set_comment_status', $comment->comment_ID, 'delete' );
            $this->_tp_transition_comment_status( 'delete', $comment->comment_approved, $comment );
            return true;
        }//1448
        /**
         * @description Moves a comment to the Trash
         * @param $comment_id
         * @return bool|string
         */
        protected function _tp_trash_comment( $comment_id ){
            if ( ! EMPTY_TRASH_DAYS )
                return $this->_tp_delete_comment( $comment_id, true );
            $_comment = $this->_get_comment( $comment_id );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return false;
            $this->_do_action( 'trash_comment', $comment->comment_ID, $comment );
            if ( $this->_tp_set_comment_status( $comment, 'trash' ) ) {
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_status' );
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_time' );
                $this->_add_comment_meta( $comment->comment_ID, '_tp_trash_meta_status', $comment->comment_approved );
                $this->_add_comment_meta( $comment->comment_ID, '_tp_trash_meta_time', time() );
                $this->_do_action( 'trashed_comment', $comment->comment_ID, $comment );
                return true;
            }
            return false;
        }//1523
        /**
         * @description Removes a comment from the Trash
         * @param $comment_id
         * @return bool
         */
        protected function _tp_untrash_comment( $comment_id ):bool{
            $_comment = $this->_get_comment( $comment_id );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return false;
            $this->_do_action( 'untrash_comment', $comment->comment_ID, $comment );
            $status = (string) $this->_get_comment_meta( $comment->comment_ID, '_tp_trash_meta_status', true );
            if ( empty( $status ) ) $status = '0';
            if ( $this->_tp_set_comment_status( $comment, $status ) ) {
                $this->_delete_comment_meta($comment->comment_ID, '_tp_trash_meta_time');
                $this->_delete_comment_meta($comment->comment_ID, '_tp_trash_meta_status');
                $this->_do_action('untrashed_comment', $comment->comment_ID, $comment);
                return true;
            }
            return false;
        }//1575
        /**
         * @description Marks a comment as Spam
         * @param $comment_id
         * @return bool
         */
        protected function _tp_spam_comment( $comment_id ):bool{
            $_comment = $this->_get_comment( $comment_id );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return false;
            $this->_do_action( 'spam_comment', $comment->comment_ID, $comment );
            if ( $this->_tp_set_comment_status( $comment, 'spam' ) ) {
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_status' );
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_time' );
                $this->_add_comment_meta( $comment->comment_ID, '_tp_trash_meta_status', $comment->comment_approved );
                $this->_add_comment_meta( $comment->comment_ID, '_tp_trash_meta_time', time() );
                $this->_do_action( 'spammed_comment', $comment->comment_ID, $comment );
                return true;
            }
            return false;
        }//1626
        /**
         * @description Removes a comment from the Spam
         * @param $comment_id
         * @return bool
         */
        protected function _tp_unspam_comment( $comment_id ):bool{
            $_comment = $this->_get_comment( $comment_id );
            $comment = null;
            if($_comment instanceof TP_Comment){
                $comment = $_comment;
            }
            if ( ! $comment ) return false;
            $this->_do_action( 'unspam_comment', $comment->comment_ID, $comment );
            $status = (string) $this->_get_comment_meta( $comment->comment_ID, '_tp_trash_meta_status', true );
            if ( empty( $status ) ) $status = '0';
            if ( $this->_tp_set_comment_status( $comment, $status ) ) {
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_status' );
                $this->_delete_comment_meta( $comment->comment_ID, '_tp_trash_meta_time' );
                $this->_do_action( 'unspammed_comment', $comment->comment_ID, $comment );
                return true;
            }
            return false;
        }//1674
    }
}else die;