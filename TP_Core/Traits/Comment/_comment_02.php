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
use TP_Core\Traits\Inits\_init_meta;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\TP_Comment;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Walkers\TP_Walker_Comment;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _comment_02 {
        use _init_comment;
        use _init_db;
        use _init_error;
        use _init_meta;
        use _init_queries;
        /**
         * @description Retrieve comment meta field for a comment.
         * @param $comment_id
         * @param string $key
         * @param bool $single
         * @return mixed
         */
        protected function _get_comment_meta( $comment_id, $key = '', $single = false ){
            return $this->_get_metadata( 'comment', $comment_id, $key, $single );
        }//477 old503
        /**
         * @description Update comment meta field based on comment ID.
         * @param $comment_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return mixed
         */
        protected function _update_comment_meta( $comment_id, $meta_key, $meta_value, $prev_value = '' ){
            return $this->_update_metadata( 'comment', $comment_id, $meta_key, $meta_value, $prev_value );
        }//503 old529
        /**
         * @description Queues comments for metadata lazy-loading.
         * @param $comments
         */
        protected function _tp_queue_comments_for_comment_meta_lazy_load($comments ):void{
            $comment_ids = [];
            $lazyloader = $this->_init_meta_data_lazy_loader();
            if ( is_array( $comments ) ) {
                foreach ( $comments as $comment )
                   if ( $comments instanceof TP_Comment ) $comment_ids[] = $comment->comment_ID;
            }
            if ( $comment_ids ) {
                $lazyloader->queue_objects( 'comment', $comment_ids );
            }
        }//514 old 540
        /**
         * @description Sets the cookies used to store an unauthenticated commentator's identity.
         * @description . Typically used to recall previous comments by this commentator that are still held in moderation.
         * @param $comment
         * @param $user
         * @param bool $cookies_consent
         */
        protected function _tp_set_comment_cookies( $comment,TP_User $user, $cookies_consent = true ):void{
            if($user->exists()) return;
            if ( false === $cookies_consent ) {
                $past = time() - YEAR_IN_SECONDS;
                setcookie( 'comment_author_' . COOKIE_HASH, ' ', $past, COOKIE_PATH, COOKIE_DOMAIN );
                setcookie( 'comment_author_email_' . COOKIE_HASH, ' ', $past, COOKIE_PATH, COOKIE_DOMAIN );
                setcookie( 'comment_author_url_' . COOKIE_HASH, ' ', $past, COOKIE_PATH, COOKIE_DOMAIN );
                return;
            }
            $comment_cookie_lifetime = time() + $this->_apply_filters( 'comment_cookie_lifetime', 30000000 );
            $secure = ( 'https' === parse_url( $this->_home_url(), PHP_URL_SCHEME ) );
            setcookie( 'comment_author_' . COOKIE_HASH, $comment->comment_author, $comment_cookie_lifetime, COOKIE_PATH, COOKIE_DOMAIN, $secure );
            setcookie( 'comment_author_email_' . COOKIE_HASH, $comment->comment_author_email, $comment_cookie_lifetime, COOKIE_PATH, COOKIE_DOMAIN, $secure );
            setcookie( 'comment_author_url_' . COOKIE_HASH, $this->_esc_url( $comment->comment_author_url ), $comment_cookie_lifetime, COOKIE_PATH, COOKIE_DOMAIN, $secure );
        }//542 old568
        /**
         * @description Sanitizes the cookies sent to the user already.
         */
        protected function _sanitize_comment_cookies():void{
            $_cookie_author = $_COOKIE[ 'comment_author_' . COOKIE_HASH ];
            if ( isset($_cookie_author) ) {
                $comment_author = $this->_apply_filters( 'pre_comment_author_name', $_COOKIE[ 'comment_author_' . COOKIE_HASH ] );
                $comment_author = $this->_tp_unslash( $comment_author );
                $comment_author = $this->_esc_attr( $comment_author );
                $_COOKIE[ 'comment_author_' . COOKIE_HASH ] = $comment_author;
            }
            $_cookie_author_email =  $_COOKIE[ 'comment_author_email_' . COOKIE_HASH ];
            if ( isset($_cookie_author_email ) ) {
                $comment_author_email = $this->_apply_filters( 'pre_comment_author_email', $_COOKIE[ 'comment_author_email_' . COOKIE_HASH ] );
                $comment_author_email = $this->_tp_unslash( $comment_author_email );
                $comment_author_email = $this->_esc_attr( $comment_author_email );
                $_COOKIE[ 'comment_author_email_' . COOKIE_HASH ] = $comment_author_email;
            }
            $_cookie_author_url = $_COOKIE[ 'comment_author_url_' . COOKIE_HASH ];
            if ( isset( $_cookie_author_url ) ) {
                $comment_author_url = $this->_apply_filters( 'pre_comment_author_url', $_COOKIE[ 'comment_author_url_' . COOKIE_HASH ] );
                $comment_author_url = $this->_tp_unslash( $comment_author_url );
                $_COOKIE[ 'comment_author_url_' . COOKIE_HASH ] = $comment_author_url;
            }
        }//582 old608
        /**
         * @description Validates whether this comment is allowed to be made.
         * @param $comment_data
         * @param bool $tp_error
         * @return TP_Error
         */
        protected function _tp_allow_comment( $comment_data, $tp_error = false ): TP_Error{
            $tpdb = $this->_init_db();
            $dupe = $tpdb->prepare( TP_SELECT . " comment_ID FROM $tpdb->comments WHERE comment_post_ID = %d AND comment_parent = %s AND comment_approved != 'trash' AND ( comment_author = %s ",
                $this->_tp_unslash( $comment_data['comment_post_ID'] ),
                $this->_tp_unslash( $comment_data['comment_parent'] ),
                $this->_tp_unslash( $comment_data['comment_author'] )
            );
            if ( $comment_data['comment_author_email'] )
                $dupe .= $tpdb->prepare('AND comment_author_email = %s ', $this->_tp_unslash( $comment_data['comment_author_email'] ));
            $dupe .= $tpdb->prepare(') AND comment_content = %s LIMIT 1', $this->_tp_unslash( $comment_data['comment_content']));
            $dupe_id = $tpdb->get_var( $dupe );
            $dupe_id = $this->_apply_filters( 'duplicate_comment_id', $dupe_id, $comment_data );
            if ( $dupe_id ) {
                $this->_do_action( 'comment_duplicate_trigger', $comment_data );
                $comment_duplicate_message = $this->_apply_filters( 'comment_duplicate_message', $this->__( 'Duplicate comment detected; it looks as though you&#8217;ve already said that!' ) );
                if ( $tp_error ) {
                    return new TP_Error( 'comment_duplicate', $comment_duplicate_message, 409 );
                }
                if ( $this->_tp_doing_async() ) die( $comment_duplicate_message );
                $this->_tp_die( $comment_duplicate_message, 409 );
            }
            $this->_do_action(
                'check_comment_flood',$comment_data['comment_author_IP'],
                $comment_data['comment_author_email'],$comment_data['comment_date_gmt'],
                $tp_error
            );
            $is_flood = $this->_apply_filters(
                'tp_is_comment_flood',false,
                $comment_data['comment_author_IP'],$comment_data['comment_author_email'],
                $comment_data['comment_date_gmt'],$this->_tp_error
            );
            if ( $is_flood ) {
                $comment_flood_message = $this->_apply_filters( 'comment_flood_message', $this->__( 'You are posting comments too quickly. Slow down.' ) );
                return new TP_Error( 'comment_flood', $comment_flood_message, 429 );
            }
            $post_author = '';
            if ( ! empty( $comment_data['user_id'] ) ) {
                $_user = $this->_get_user_data( $comment_data['user_id'] );
                $user = null;
                if($_user instanceof TP_User){
                    $user = $_user;
                }
                $post_author = $tpdb->get_var($tpdb->prepare(TP_SELECT ." post_author FROM $tpdb->posts WHERE ID = %d LIMIT 1", $comment_data['comment_post_ID']));
            }
            if ( isset( $user ) && ( $comment_data['user_id'] === $post_author || $user->has_cap( 'moderate_comments' ) ) )
                $approved = 1;
            else{
                if ( $this->_check_comment(
                    $comment_data['comment_author'],$comment_data['comment_author_email'],$comment_data['comment_author_url'],
                    $comment_data['comment_content'],$comment_data['comment_author_IP'],$comment_data['comment_agent'],
                    $comment_data['comment_type']
                ) ) $approved = 1;
                else $approved = 0;
                if ( $this->_tp_check_comment_disallowed_list(
                    $comment_data['comment_author'],$comment_data['comment_author_email'],
                    $comment_data['comment_author_url'],$comment_data['comment_content'],
                    $comment_data['comment_author_IP'], $comment_data['comment_agent']
                ) ) $approved = EMPTY_TRASH_DAYS ? 'trash' : 'spam';
            }
            return $this->_apply_filters( 'pre_comment_approved', $approved, $comment_data );
        }//654 old680
        /**
         * @description Hooks TP's native database-based comment-flood check.
         */
        protected function _check_comment_flood_db():void{
            $this->_add_filter( 'tp_is_comment_flood', [$this,'_tp_check_comment_flood'], 10, 5 );
        }//839 old 865
        /**
         * @description Checks whether comment flooding is occurring.
         * @param $is_flood
         * @param $ip
         * @param $email
         * @param $date
         * @param bool $avoid_die
         * @return bool
         */
        protected function _tp_check_comment_flood( $is_flood, $ip, $email, $date, $avoid_die = false ):bool{
            $tpdb = $this->_init_db();
            if ( true === $is_flood ) return $is_flood;
            if ( $this->_current_user_can( 'manage_options' ) || $this->_current_user_can( 'moderate_comments' ) )
                return false;
            $hour_ago = gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS );
            if ( $this->_is_user_logged_in() ) {
                $user         = $this->_get_current_user_id();
                $check_column = '`user_id`';
            } else {
                $user         = $ip;
                $check_column = '`comment_author_IP`';
            }
            $sql = $tpdb->prepare(
                TP_SELECT . "`comment_date_gmt` FROM `$tpdb->comments` WHERE `comment_date_gmt` >= %s AND ( $check_column = %s OR `comment_author_email` = %s ) ORDER BY `comment_date_gmt` DESC LIMIT 1",
                $hour_ago,$user,$email);
            $last_time = $tpdb->get_var( $sql );
            if ( $last_time ) {
                $time_last_comment = $this->_mysql2date( 'U', $last_time, false );
                $time_new_comment  = $this->_mysql2date( 'U', $date, false );
                $flood_die = $this->_apply_filters( 'comment_flood_filter', false, $time_last_comment, $time_new_comment );
                if ( $flood_die ) {
                    $this->_do_action( 'comment_flood_trigger', $time_last_comment, $time_new_comment );
                    if ( $avoid_die ) return true;
                    else {
                        $comment_flood_message = $this->_apply_filters( 'comment_flood_message', $this->__( 'You are posting comments too quickly. Slow down.' ) );
                        //if ( tp_doing_ajax() ) { die( $comment_flood_message ); }
                        $this->_tp_die( $comment_flood_message, 429 );
                    }
                }
            }
            return false;
        }//861 old 887
        /**
         * @description Separates an array of comments into an array keyed by comment_type.
         * @param $comments
         * @return array
         */
        protected function _separate_comments( &$comments ):array{
            $comments_by_type = ['comment' => [],'trackback' => [],'pingback' => [],'pings' => [], ];
            foreach ($comments as $i => $iValue) {
                $type = $iValue->comment_type;
                if ( empty( $type ) ) $type = 'comment';
                $comments_by_type[ $type ][] = &$comments[ $i ];
                if ( 'trackback' === $type || 'pingback' === $type )
                    $comments_by_type['pings'][] = &$comments[ $i ];
            }
            return $comments_by_type;
        }//952 old978
        /**
         * @description Calculate the total number of comment pages.
         * @param null $comments
         * @param null $per_page
         * @param null $threaded
         * @return float|int
         */
        protected function _get_comment_pages_count( $comments = null, $per_page = null, $threaded = null ){
            $tp_query = $this->_init_query($comments);
            if ( null === $comments && null === $per_page && null === $threaded && ! empty( $tp_query->max_num_comment_pages ) )
                return $tp_query->max_num_comment_pages;
            if ( ( ! $comments || ! is_array( $comments ) ) && ! empty( $tp_query->comments ) )
                $comments = $tp_query->comments;
            if ( empty( $comments ) ) return 0;
            if ( ! $this->_get_option( 'page_comments' ) ) return 1;
            if ( ! isset( $per_page ) )
                $per_page = (int) $this->_get_query_var( 'comments_per_page' );
            if ( 0 === $per_page )
                $per_page = (int) $this->_get_option( 'comments_per_page' );
            if ( 0 === $per_page ) return 1;
            if ( ! isset( $threaded ) )
                $threaded = $this->_get_option( 'thread_comments' );
            if ( $threaded ) {
                $walker = new TP_Walker_Comment;
                $count  = ceil( $walker->get_number_of_root_elements( $comments ) / $per_page );
            } else $count = ceil( count( $comments ) / $per_page );
            return $count;
        }//993
    }
}else die;