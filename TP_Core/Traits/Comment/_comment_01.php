<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Comment;
if(ABSPATH){
    trait _comment_01 {
        use _init_db;
        use _init_queries;
        use _init_comment;
        /**
         * @description Check whether a comment passes internal checks to be allowed to add.
         * @param $author
         * @param $email
         * @param $url
         * @param $comment
         * @param $user_ip
         * @param $user_agent
         * @param $comment_type
         * @return bool
         */
        protected function _check_comment( $author, $email, $url, $comment, $user_ip, $user_agent, $comment_type ): bool{
            $tpdb = $this->_init_db();
            if ( 1 === $this->_get_option( 'comment_moderation' ) ) return false;
            $comment = $this->_apply_filters( 'comment_text', $comment, null, array() );
            $max_links = $this->_get_option( 'comment_max_links' );
            if ( $max_links ) {
                $num_links = preg_match_all('/<a [^>]*href/i', $comment, $out);
                $num_links = $this->_apply_filters('comment_max_links_url', $num_links, $url, $comment);
                if ($num_links >= $max_links) return false;
            }
            $mod_keys = trim( $this->_get_option( 'moderation_keys' ) );
            if ( ! empty( $mod_keys ) ) {
                $words = explode("\n", $mod_keys);
                foreach ($words as $word) {
                    $word = trim($word);
                    if (empty($word))  continue;
                    $word = preg_quote($word, '#');
                    $pattern = "#$word#i";
                    if (preg_match($pattern, $author))  return false;
                    if (preg_match($pattern, $email)) return false;
                    if (preg_match($pattern, $url)) return false;
                    if (preg_match($pattern, $comment)) return false;
                    if (preg_match($pattern, $user_ip)) return false;
                    if (preg_match($pattern, $user_agent)) return false;
                }
            }
            if ( 1 === $this->_get_option( 'comment_previously_approved' ) ) {
                if ( 'trackback' !== $comment_type && 'pingback' !== $comment_type && '' !== $author && '' !== $email ) {
                    $comment_user = $this->_get_user_by( 'email', $this->_tp_unslash( $email ) );
                    if ( ! empty( $comment_user->ID ) )
                        $ok_to_comment = $tpdb->get_var( $tpdb->prepare( TP_SELECT . " comment_approved FROM $tpdb->comments WHERE user_id = %d AND comment_approved = '1' LIMIT 1", $comment_user->ID ) );
                    else  $ok_to_comment = $tpdb->get_var( $tpdb->prepare( TP_SELECT . " comment_approved FROM $tpdb->comments WHERE comment_author = %s AND comment_author_email = %s and comment_approved = '1' LIMIT 1", $author, $email ) );
                    if ( ( 1 === $ok_to_comment ) &&
                        ( empty( $mod_keys ) || false === strpos( $email, $mod_keys ) ) ) {
                        return true;
                    } else  return false;
                } else return false;
           }
            return true;
        }//39
        /**
         * @description Retrieve the approved comments for post $post_id.
         * @param $post_id
         * @param array ...$args
         * @return array
         */
        protected function _get_approved_comments( $post_id, ...$args ): array{
            if ( ! $post_id ) return [];
            $defaults    = ['status' => 1,'post_id' => $post_id,'order' => 'ASC',];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            return $this->_init_comment_query()->query_comment( $parsed_args );
        }//162
        /**
         * @description Retrieves comment data given a comment ID or comment object.
         * @param null $comment
         * @param string $output
         * @return array|null
         */
        protected function _get_comment( $comment = null, $output = OBJECT ): ?array{
            if ( empty( $comment ) && isset( $this->_tp_comment['comment'] ) )
                $comment = $this->_tp_comment['comment'];
            if ( $comment instanceof TP_Comment ) $_comment = $comment;
            elseif ( is_object( $comment ) )
                $_comment = new TP_Comment( $comment );
            else $_comment = TP_Comment::get_instance( $comment );
            if ( ! $_comment ) return null;
            $_comment = $this->_apply_filters( 'get_comment', $_comment );
            if ( OBJECT === $output ) return $_comment;
            elseif (($_comment instanceof TP_Comment) &&  ARRAY_A === $output )  return $_comment->to_array();
            elseif ( ARRAY_N === $output ) return array_values( $_comment->to_array() );
            return $_comment;
        }//195
        /**
         * @description Retrieve a list of comments.
         * @param array ...$args
         * @return array
         */
        protected function _get_comments( ...$args):array{
            return $this->_init_comment_query()->query_comment( $args );
        }//242
        /**
         * @description Retrieve all of the TailoredPress supported comment statuses.
         * @return array
         */
        protected function _get_comment_statuses():array{
            $status = [
                'hold'    => $this->__( 'Unapproved' ),
                'approve' => $this->_x( 'Approved', 'comment status' ),
                'spam'    => $this->_x( 'Spam', 'comment status' ),
                'trash'   => $this->_x( 'Trash', 'comment status' ),
            ];
            return $status;
        }//257
        /**
         * @description Gets the default comment status for a post type.
         * @param string $post_type
         * @param string $comment_type
         * @return mixed
         */
        protected function _get_default_comment_status( $post_type = 'post', $comment_type = 'comment' ){
            switch ( $comment_type ) {
                case 'pingback':
                case 'trackback':
                    $supports = 'trackbacks';
                    $option   = 'ping';
                    break;
                default:
                    $supports = 'comments';
                    $option   = 'comment';
                    break;
            }
            if ( 'page' === $post_type ) $status = 'closed';
            elseif ( $this->_post_type_supports( $post_type, $supports ) )
                $status = $this->_get_option( "default_{$option}_status" );
            else $status = 'closed';
            return $this->_apply_filters( 'get_default_comment_status', $status, $post_type, $comment_type );
        }//277
        /**
         * @description The date the last comment was modified.
         * @param string $timezone
         * @return bool|null
         */
        protected function _get_last_comment_modified( $timezone = 'server' ):?bool{
            $tpdb = $this->_init_db();
            $timezone = strtolower( $timezone );
            $key      = "last_comment_modified:$timezone";
            $comment_modified_date = $this->_tp_cache_get( $key, 'time_info' );
            if ( false !== $comment_modified_date )
                return $comment_modified_date;
            switch ( $timezone ) {
                case 'gmt':
                    $comment_modified_date = $tpdb->get_var( TP_SELECT . " comment_date_gmt FROM $tpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT 1" );
                    break;
                case 'blog':
                    $comment_modified_date = $tpdb->get_var( TP_SELECT . " comment_date FROM $tpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT 1" );
                    break;
                case 'server':
                    $add_seconds_server = gmdate( 'Z' );
                    $comment_modified_date = $tpdb->get_var( $tpdb->prepare( TP_SELECT . " DATE_ADD(comment_date_gmt, INTERVAL %s SECOND) FROM $tpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT 1", $add_seconds_server ) );
                    break;
            }
            if ( $comment_modified_date ) {
                $this->_tp_cache_set( $key, $comment_modified_date, 'time_info' );
                return $comment_modified_date;
            }
            return false;
        }//324

        /**
         * @description Retrieves the total comment counts for the whole site or a single post.
         * @param int $post_id
         * @return array
         */
        protected function _get_comment_count( $post_id = 0 ):array{
            $post_id = (int) $post_id;
            $this->tpdb = $this->_init_db();
            $where = '';
            if ( $post_id > 0 ) {
                $where = $this->tpdb->prepare( 'WHERE comment_post_ID = %d', $post_id );
            }
            $totals = (array) $this->tpdb->get_results(
                TP_SELECT . " comment_approved, COUNT( * ) AS total FROM {$this->tpdb->comments} {$where} GROUP BY comment_approved ", ARRAY_A);
            $this->_comment_count = [
                'approved' => 0,'awaiting_moderation' => 0,'spam' => 0,'trash' => 0,
                'post-trashed' => 0,'total_comments' => 0,'all' => 0,
            ];
            $comment_count = ['approved' => 0,'awaiting_moderation' => 0,'spam' => 0,
                'trash' => 0,'post-trashed' => 0,'total_comments' => 0,'all' => 0,];
            foreach ( $totals as $row ) {
                switch ( $row['comment_approved'] ) {
                    case 'trash':
                        $comment_count['trash'] = $row['total'];
                        break;
                    case 'post-trashed':
                        $comment_count['post-trashed'] = $row['total'];
                        break;
                    case 'spam':
                        $comment_count['spam']            = $row['total'];
                        $comment_count['total_comments'] += $row['total'];
                        break;
                    case '1':
                        $comment_count['approved']        = $row['total'];
                        $comment_count['total_comments'] += $row['total'];
                        $comment_count['all']            += $row['total'];
                        break;
                    case '0':
                        $comment_count['awaiting_moderation'] = $row['total'];
                        $comment_count['total_comments']     += $row['total'];
                        $comment_count['all']                += $row['total'];
                        break;
                    default:
                        break;
                }
            }
            return array_map( 'intval', $comment_count );
        }//377 old381
        /**
         * @description Add meta data field to a comment.
         * @param $comment_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return mixed
         */
        protected function _add_comment_meta( $comment_id, $meta_key, $meta_value, $unique = false ){
            return $this->_add_metadata( 'comment', $comment_id, $meta_key, $meta_value, $unique );
        }//437 old459
        /**
         * @description Remove metadata matching criteria from a comment.
         * @param $comment_id
         * @param $meta_key
         * @param string $meta_value
         */
        protected function _delete_comment_meta( $comment_id, $meta_key, $meta_value = '' ){
            return $this->_delete_metadata( 'comment', $comment_id, $meta_key, $meta_value );
        }//455 old481
    }
}else die;