<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-6-2022
 * Time: 06:42
 */
namespace TP_Admin\Traits;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_comment{
        use _init_db;
        /**
         * @description Determine if a comment exists based on author and date.
         * @param $comment_author
         * @param $comment_date
         * @param string $timezone
         * @return null
         */
        protected function _comment_exists( $comment_author, $comment_date, $timezone = 'blog' ){
            $this->tpdb = $this->_init_db();
            $date_field = 'comment_date';
            if ( 'gmt' === $timezone ) { $date_field = 'comment_date_gmt';}
            return $this->tpdb->get_var( $this->tpdb->prepare(TP_SELECT . " comment_post_ID FROM $this->tpdb->comments WHERE comment_author = %s AND $date_field = %s", stripslashes( $comment_author ), stripslashes( $comment_date ) ));
        }//26
        /**
         * @description Update a comment with values provided in $_POST.
         * @return mixed
         */
        protected function _edit_comment(){
            if ( ! $this->_current_user_can( 'edit_comment', (int) $_POST['comment_ID'] ) ) {
                $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit comments on this post.' ) );
            }
            if ( isset( $_POST['new_comment_author'] ) ) { $_POST['comment_author'] = $_POST['new_comment_author']; }
            if ( isset( $_POST['new_comment_author_email'] ) ) {$_POST['comment_author_email'] = $_POST['new_comment_author_email'];}
            if ( isset( $_POST['new_comment_author_url'] ) ) { $_POST['comment_author_url'] = $_POST['new_comment_author_url'];}
            if ( isset( $_POST['comment_status'] ) ) {$_POST['comment_approved'] = $_POST['comment_status'];}
            if ( isset( $_POST['content'] ) ) { $_POST['comment_content'] = $_POST['content'];}
            if ( isset( $_POST['comment_ID'] ) ) { $_POST['comment_ID'] = (int) $_POST['comment_ID'];}
            foreach ( array( 'aa', 'mm', 'jj', 'hh', 'mn' ) as $timeunit ) {
                if ( ! empty( $_POST[ 'hidden_' . $timeunit ] ) && $_POST[ 'hidden_' . $timeunit ] !== $_POST[ $timeunit ] ) {
                    $_POST['edit_date'] = '1';
                    break;
                }
            }
            if ( ! empty( $_POST['edit_date'] ) ) {
                $aa = $_POST['aa'];
                $mm = $_POST['mm'];
                $jj = $_POST['jj'];
                $hh = $_POST['hh'];
                $mn = $_POST['mn'];
                $ss = $_POST['ss'];
                $jj = ( $jj > 31 ) ? 31 : $jj;
                $hh = ( $hh > 23 ) ? $hh - 24 : $hh;
                $mn = ( $mn > 59 ) ? $mn - 60 : $mn;
                $ss = ( $ss > 59 ) ? $ss - 60 : $ss;
                $_POST['comment_date'] = "$aa-$mm-$jj $hh:$mn:$ss";
            }
            return $this->_tp_update_comment( $_POST, true );
        }//53
        /**
         * @description Returns a TP_Comment object based on comment ID.
         * @param $id
         * @return bool
         */
        protected function _get_comment_to_edit( $id ):bool{
            $comment = $this->_get_comment( $id );
            if ( ! $comment ) { return false;}
            $comment->comment_ID      = (int) $comment->comment_ID;
            $comment->comment_post_ID = (int) $comment->comment_post_ID;
            $comment->comment_content = $this->_format_to_edit( $comment->comment_content );
            $comment->comment_content = $this->_apply_filters( 'comment_edit_pre', $comment->comment_content );
            $comment->comment_author       = $this->_format_to_edit( $comment->comment_author );
            $comment->comment_author_email = $this->_format_to_edit( $comment->comment_author_email );
            $comment->comment_author_url   = $this->_format_to_edit( $comment->comment_author_url );
            $comment->comment_author_url   = $this->_esc_url( $comment->comment_author_url );
            return $comment;
        }//110
        /**
         * @description Get the number of pending comments on a post or posts
         * @param $post_id
         * @return mixed
         */
        protected function _get_pending_comments_num( $post_id ){
            $this->tpdb = $this->_init_db();
            $single = false;
            if ( ! is_array( $post_id ) ) {
                $post_id_array = (array) $post_id;
                $single        = true;
            } else { $post_id_array = $post_id;}
            $post_id_array = array_map( 'intval', $post_id_array );
            $post_id_in = implode( ',', $post_id_array );
            $pending = $this->tpdb->get_results( TP_SELECT . " comment_post_ID, COUNT(comment_ID) as num_comments FROM $this->tpdb->comments WHERE comment_post_ID IN ( $post_id_in ) AND comment_approved = '0' GROUP BY comment_post_ID", ARRAY_A );
            if ( $single ) {
                if ( empty( $pending ) ) {
                    return 0;}
                return $this->_abs_int( $pending[0]['num_comments'] );
            }
            $pending_keyed = array();
            foreach ( $post_id_array as $id ) { $pending_keyed[ $id ] = 0;}
            if ( ! empty( $pending ) ) {
                foreach ((array) $pending as $pend ) {$pending_keyed[ $pend['comment_post_ID'] ] = $this->_abs_int( $pend['num_comments'] );}
            }
            return (string)$pending_keyed;
        }//147
        /**
         * @description Adds avatars to relevant places in admin.
         * @param $name
         * @return string
         */
        protected function _floated_admin_avatar( $name ):string{
            $avatar = $this->_get_avatar( $this->_get_comment(), 32, 'mystery' );
            return "$avatar $name";
        }//194
        protected function _enqueue_comment_hot_keys_js():void{
            if ( 'true' === $this->_get_user_option( 'comment_shortcuts' ) ) {
                $this->tp_enqueue_script( 'custom-table-hot_keys' );
            }
        }//202
        /**
         * @description Display error message at bottom of comments.
         * @param $msg
         * @return string
         */
        protected function _get_comment_footer_die( $msg ):string{
            if($msg !== null){
                $output  = "<div class='wrap'><p>$msg</p></div>";
                $output .= $this->_tp_load_class('admin_footer',TP_NS_ADMIN_MODULES,'Admin_Footer');
                return $output;
            }
            die;
        }//213
    }
}else die;