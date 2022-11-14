<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _comment_07{
        use _init_error;
        use _init_db;
        /**
         * @description Updates the comment cache of given comments.
         * @param $comments
         * @param bool $update_meta_cache
         */
        protected function _update_comment_cache( $comments, $update_meta_cache = true ):void{
            $data = [];
            foreach ( (array) $comments as $comment )
                $data[ $comment->comment_ID ] = $comment;
            $this->_tp_cache_add_multiple( $data, 'comment' );
            if ( $update_meta_cache ) {
                $comment_ids = [];
                foreach ( $comments as $comment )
                    $comment_ids[] = $comment->comment_ID;
                $this->_update_meta_cache( 'comment', $comment_ids );
            }
        }//3230
        /**
         * @description Adds any comments from the given IDs to the cache that do not already exist in cache.
         * @param $comment_ids
         * @param bool $update_meta_cache
         */
        protected function _prime_comment_caches( $comment_ids, $update_meta_cache = true ):void{
            $tpdb = $this->_init_db();
            $non_cached_ids = $this->_get_non_cached_ids( $comment_ids, 'comment' );
            if ( ! empty( $non_cached_ids ) ) {
                $fresh_comments = $tpdb->get_results( sprintf( TP_SELECT . " $tpdb->comments.* FROM $tpdb->comments WHERE comment_ID IN (%s)", implode( ',', array_map( 'intval', $non_cached_ids ) ) ) );
                $this->_update_comment_cache( $fresh_comments, $update_meta_cache );
            }
        }//3259
        /**
         * @description Close comments on old posts on the fly,
         * @description . without any extra DB queries. Hooked to the_posts.
         * @param $posts
         * @param TP_Query $query
         * @return mixed
         */
        protected function _close_comments_for_old_posts( $posts, TP_Query $query ){
            if ( empty( $posts ) || ! $query->is_singular() || ! $this->_get_option( 'close_comments_for_old_posts' ) )
                return $posts;
            $post_types = $this->_apply_filters( 'close_comments_for_post_types', array( 'post' ) );
            if ( ! in_array( $posts[0]->post_type, $post_types, true ) )
                return $posts;
            $days_old = (int) $this->_get_option( 'close_comments_days_old' );
            if ( ! $days_old ) return $posts;
            if ( time() - strtotime( $posts[0]->post_date_gmt ) > ( $days_old * DAY_IN_SECONDS ) ) {
                $posts[0]->comment_status = 'closed';
                $posts[0]->ping_status    = 'closed';
            }
            return $posts;
        }//3284
        /**
         * @description Close comments on an old post.
         * @description . Hooked to comments_open and pings_open.
         * @param $open
         * @param $post_id
         * @return bool
         */
        protected function _close_comments_for_old_post( $open, $post_id ):bool{
            if ( ! $open ) return $open;
            if ( ! $this->_get_option( 'close_comments_for_old_posts' ) )
                return $open;
            $days_old = (int) $this->_get_option( 'close_comments_days_old' );
            if ( ! $days_old ) return $open;
            $post = $this->_get_post( $post_id );
            $post_types = $this->_apply_filters( 'close_comments_for_post_types', array( 'post' ) );
            if ( ! in_array( $post->post_type, $post_types, true ) ) return $open;
            if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) return $open;
            if ( time() - strtotime( $post->post_date_gmt ) > ( $days_old * DAY_IN_SECONDS ) )
                return false;
            return $open;
        }//3324
        /**
         * @description Handles the submission of a comment,
         * @description . usually posted to tp-comments-post.php via a comment form.
         * @param $comment_data
         * @return TP_Error
         */
        protected function _tp_handle_comment_submission( $comment_data ):TP_Error{
            $comment_post_ID      = 0;
            $comment_parent       = 0;
            $user_ID              = 0;
            $comment_author       = null;
            $comment_author_email = null;
            $comment_author_url   = null;
            $comment_content      = null;
            if ( isset( $comment_data['comment_post_ID'] ) )
                $comment_post_ID = (int) $comment_data['comment_post_ID'];
            if ( isset( $comment_data['author'] ) && is_string( $comment_data['author'] ) )
                $comment_author = trim( strip_tags( $comment_data['author'] ) );
            if ( isset( $comment_data['email'] ) && is_string( $comment_data['email'] ) )
                $comment_author_email = trim( $comment_data['email'] );
            if ( isset( $comment_data['url'] ) && is_string( $comment_data['url'] ) )
                $comment_author_url = trim( $comment_data['url'] );
            if ( isset( $comment_data['comment'] ) && is_string( $comment_data['comment'] ) )
                $comment_content = trim( $comment_data['comment'] );
            if ( isset( $comment_data['comment_parent'] ) )
                $comment_parent = $this->_abs_int( $comment_data['comment_parent'] );
            $post = $this->_get_post( $comment_post_ID );
            if ( empty( $post->comment_status ) ) {
                $this->_do_action( 'comment_id_not_found', $comment_post_ID );
                return new TP_Error( 'comment_id_not_found' );
            }
            $status = $this->_get_post_status( $post );
            if ( ( 'private' === $status ) && ! $this->_current_user_can( 'read_post', $comment_post_ID ) )
                return new TP_Error( 'comment_id_not_found' );
            $status_obj = $this->_get_post_status_object( $status );
            if (! $this->_comments_open( $comment_post_ID )) {
                $this->_do_action( 'comment_closed', $comment_post_ID );
                return new TP_Error( 'comment_closed', $this->__( 'Sorry, comments are closed for this item.' ), 403 );
            }
            if ('trash' === $status) {
                $this->_do_action( 'comment_on_trash', $comment_post_ID );
                return new TP_Error( 'comment_on_trash' );
            }

            if (! $status_obj->public && ! $status_obj->private) {
                $this->_do_action( 'comment_on_draft', $comment_post_ID );
                if ( $this->_current_user_can( 'read_post', $comment_post_ID ) )
                    return new TP_Error( 'comment_on_draft', $this->__( 'Sorry, comments are not allowed for this item.' ), 403 );
                else return new TP_Error( 'comment_on_draft' );
            } elseif ( $this->_post_password_required( $comment_post_ID ) ) {
                $this->_do_action( 'comment_on_password_protected', $comment_post_ID );
                return new TP_Error( 'comment_on_password_protected' );
            } else $this->_do_action( 'pre_comment_on_post', $comment_post_ID );
            $user = $this->_tp_get_user_current();
            if($user instanceof TP_User){

            }
            if ( $user->exists() ) {
                if ( empty( $user->display_name ) )
                    $user->display_name = $user->user_login;
                $comment_author       = $user->display_name;
                $comment_author_email = $user->user_email;
                $comment_author_url   = $user->user_url;
                $user_ID              = $user->ID;
                if ( $this->_current_user_can( 'unfiltered_html' ) ) {
                    if ( ! isset( $comment_data['_tp_unfiltered_html_comment'] )
                        || ! $this->_tp_verify_nonce( $comment_data['_tp_unfiltered_html_comment'], 'unfiltered-html-comment_' . $comment_post_ID )
                    ) {
                        $this->_kses_remove_filters(); // Start with a clean slate.
                        $this->_kses_init_filters();   // Set up the filters.
                        $this->_remove_filter( 'pre_comment_content', 'tp_filter_post_kses' );
                        $this->_add_filter( 'pre_comment_content', 'tp_filter_kses' );
                    }
                }
            } else if ( $this->_get_option( 'comment_registration' ) )
                return new TP_Error( 'not_logged_in', $this->__( 'Sorry, you must be logged in to comment.' ), 403 );
            $comment_type = 'comment';
            if ( $this->_get_option( 'require_name_email' ) && ! $user->exists() ) {
                if ( '' === $comment_author_email || '' === $comment_author )
                    return new TP_Error( 'require_name_email', $this->__( '<strong>Error</strong>: Please fill the required fields.' ), 200 );
                elseif ( ! $this->_is_email( $comment_author_email ) )
                    return new TP_Error( 'require_valid_email', $this->__( '<strong>Error</strong>: Please enter a valid email address.' ), 200 );
            }
            $comment_data = compact(
                'comment_post_ID','comment_author','comment_author_email','comment_author_url',
                'comment_content','comment_type','comment_parent','user_ID'
            );
            $allow_empty_comment = $this->_apply_filters( 'allow_empty_comment', false, $comment_data );
            if ( '' === $comment_content && ! $allow_empty_comment )
                return new TP_Error( 'require_valid_comment', $this->__( '<strong>Error</strong>: Please type your comment text.' ), 200 );
            $check_max_lengths = $this->_tp_check_comment_data_max_lengths( $comment_data );
            if ( $this->_init_error( $check_max_lengths )) return $check_max_lengths;
            $comment_id = $this->_tp_new_comment( $this->_tp_slash( $comment_data ), true );
            if ( $this->_init_error( $comment_id ))return $comment_id;
            if ( ! $comment_id )
                return new TP_Error( 'comment_save_error', $this->__( '<strong>Error</strong>: The comment could not be saved. Please try again later.' ), 500 );
            return $this->_get_comment( $comment_id );
        }//3379
        /**
         * @description Registers the personal data exporter for comments.
         * @param $exporters
         * @return mixed
         */
        protected function _tp_register_comment_personal_data_exporter( $exporters ){
            $exporters['tailored_press_comments'] = [
                'exporter_friendly_name' => $this->__( 'TailoredPress Comments' ),
                'callback' => [$this,'__tp_comments_personal_data_exporter'],
            ];
            return $exporters;
        }//3587
        /**
         * @description Finds and exports personal,
         * @description . data associated with an email address from the comments table.
         * @param $email_address
         * @param int $page
         * @return array
         */
        protected function _tp_comments_personal_data_exporter( $email_address, $page = 1 ):array{
            $number = 500;
            $page   = (int) $page;
            $data_to_export = [];
            $comments = $this->_get_comments(
                ['author_email' => $email_address,'number' => $number,'paged' => $page,
                    'order_by' => 'comment_ID','order' => 'ASC','update_comment_meta_cache' => false,]
            );
            $comment_prop_to_export = [
                'comment_author'       => $this->__( 'Comment Author' ),
                'comment_author_email' => $this->__( 'Comment Author Email' ),
                'comment_author_url'   => $this->__( 'Comment Author URL' ),
                'comment_author_IP'    => $this->__( 'Comment Author IP' ),
                'comment_agent'        => $this->__( 'Comment Author User Agent' ),
                'comment_date'         => $this->__( 'Comment Date' ),
                'comment_content'      => $this->__( 'Comment Content' ),
                'comment_link'         => $this->__( 'Comment URL' ),
            ];
            foreach ( (array) $comments as $comment ) {
                $comment_data_to_export = [];
                foreach ( $comment_prop_to_export as $key => $name ) {
                    $value = '';
                    switch ( $key ) {
                        case 'comment_author':
                        case 'comment_author_email':
                        case 'comment_author_url':
                        case 'comment_author_IP':
                        case 'comment_agent':
                        case 'comment_date':
                            $value = $comment->{$key};
                            break;
                        case 'comment_content':
                            $value = $this->_get_comment_text( $comment->comment_ID );
                            break;
                        case 'comment_link':
                            $value = $this->_get_comment_link( $comment->comment_ID );
                            $value = sprintf(
                                "<a href='%s' target='_blank' rel='noopener'>%s</a>",
                                $this->_esc_url( $value ),$this->_esc_html( $value )
                            );
                            break;
                    }
                    if ( ! empty( $value ) )
                        $comment_data_to_export[] = ['name' => $name,'value' => $value,];
                }
                $data_to_export[] = [
                    'group_id' => 'comments',
                    'group_label' => $this->__( 'Comments' ),
                    'group_description' => $this->__( 'User&#8217;s comment data.' ),
                    'item_id' => "comment-{$comment->comment_ID}",
                    'data' => $comment_data_to_export,
                ];
            }
            $done = count( $comments ) < $number;
            return ['data' => $data_to_export,'done' => $done,];
        }//3605
        /**
         * @description Registers the personal data eraser for comments.
         * @param $erasers
         * @return mixed
         */
        protected function _tp_register_comment_personal_data_eraser( $erasers ){
            $erasers['tailored-press-comments'] = array(
                'eraser_friendly_name' => $this->__( 'TailoredPress Comments' ),
                'callback'=> [$this,'__tp_comments_personal_data_eraser'],
            );
            return $erasers;
        }//3697
        /**
         * @description Erases personal data associated with an email address from the comments table.
         * @param $email_address
         * @param int $page
         * @return array
         */
        protected function _tp_comments_personal_data_eraser( $email_address, $page = 1 ):array{
            $tpdb = $this->_init_db();
            if ( empty( $email_address ) )
                return ['items_removed' => false,'items_retained' => false,'messages' => [],'done' => true,];
            $number = 500;
            $page = (int) $page;
            $items_removed  = false;
            $items_retained = false;
            $comments = $this->_get_comments(
                ['author_email' => $email_address,'number' => $number,'paged' => $page,
                  'order_by' => 'comment_ID','order' => 'ASC','include_unapproved' => true,]
            );
            $anon_author = $this->__( 'Anonymous' );
            $messages  = [];
            foreach ( (array) $comments as $comment ) {
                $anonymized_comment                         = [];
                $anonymized_comment['comment_agent']        = '';
                $anonymized_comment['comment_author']       = $anon_author;
                $anonymized_comment['comment_author_email'] = '';
                $anonymized_comment['comment_author_IP']    = $this->_tp_privacy_anonymize_data( 'ip', $comment->comment_author_IP );
                $anonymized_comment['comment_author_url']   = '';
                $anonymized_comment['user_id']              = 0;
                $comment_id = (int) $comment->comment_ID;
                $anon_message = $this->_apply_filters( 'tp_anonymize_comment', true, $comment, $anonymized_comment );
                if ( true !== $anon_message ) {
                    if ( $anon_message && is_string( $anon_message ) )
                        $messages[] = $this->_esc_html( $anon_message );
                    else $messages[] = sprintf( $this->__( 'Comment %d contains personal data but could not be anonymized.' ), $comment_id );
                    $items_retained = true;
                    continue;
                }
                $args = ['comment_ID' => $comment_id,];
                $updated = $tpdb->update( $tpdb->comments, $anonymized_comment, $args );
                if ( $updated ) {
                    $items_removed = true;
                    $this->_clean_comment_cache( $comment_id );
                } else $items_retained = true;
            }
            $done = count( $comments ) < $number;
            return ['items_removed' => $items_removed,'items_retained' => $items_retained,'messages' => $messages,'done' => $done,];
        }//3715
        /**
         * @description Sets the last changed time for the 'comment' cache group
         */
        protected function _tp_cache_set_comments_last_changed():void{
            $this->_tp_cache_set( 'last_changed', microtime(), 'comment' );
        }//3813
    }
}else die;