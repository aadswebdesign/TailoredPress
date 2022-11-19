<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */

namespace TP_Core\Traits\Templates;
//use TP_Managers\Core_Manager\TP_Comment;
if(ABSPATH){
    trait _comment_template_01 {
        /**
         * @description Retrieves the author of the current comment.
         * @param $comment_ID
         * @return mixed
         */
        protected function _get_comment_author($comment_ID = 0 ){
            $comment    = $this->_get_comment( $comment_ID );
            $comment_ID = ! empty( $comment->comment_ID ) ? $comment->comment_ID : $comment_ID;
            if ( empty( $comment->comment_author ) ) {
                $user = ! empty( $comment->user_id ) ? $this->_get_user_data( $comment->user_id ) : false;
                if ( $user ) $author = $user->display_name;
                else  $author = $this->__( 'Anonymous' );
            } else $author = $comment->comment_author;
            return $this->_apply_filters( 'get_comment_author', $author, $comment_ID, $comment );
        }//24 from comment-template
        /**
         * @description Displays the author of the current comment.
         * @param int $comment_ID
         */
        protected function _comment_author( $comment_ID = 0 ):void{
            $comment = $this->_get_comment( $comment_ID );
            $author  = $this->_get_comment_author( $comment );
            echo $this->_apply_filters( 'comment_author', $author, $comment->comment_ID );
        }//61 from comment-template
        /**
         * @description Retrieves the email of the author of the current comment.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_author_email( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            return $this->_apply_filters( 'get_comment_author_email', $comment->comment_author_email, $comment->comment_ID, $comment );
        }//87 from comment-template
        /**
         * @description Displays the email of the author of the current global $comment.
         * @param int $comment_ID
         */
        protected function _comment_author_email( $comment_ID = 0 ):void{
            $comment      = $this->_get_comment( $comment_ID );
            $author_email = $this->_get_comment_author_email( $comment );
            echo $this->_apply_filters( 'author_email', $author_email, $comment->comment_ID );
        }//118 from comment-template
        /**
         * @description Displays the HTML email link to the author of the current comment.
         * @param string $linktext
         * @param string $before
         * @param string $after
         * @param null $comment
         */
        protected function _comment_author_email_link( $linktext = '', $before = '', $after = '', $comment = null ):void{
            $link = $this->_get_comment_author_email_link( $linktext, $before, $after, $comment );
            if ( $link ) echo $link;
        }//152 from comment-template
        /**
         * @description Returns the HTML email link to the author of the current comment.
         * @param string $linktext
         * @param string $before
         * @param string $after
         * @param null $comment
         * @return string
         */
        protected function _get_comment_author_email_link( $linktext = '', $before = '', $after = '', $comment = null ):string{
            $comment = $this->_get_comment( $comment );
            $email = $this->_apply_filters( 'comment_email', $comment->comment_author_email, $comment );
            if ( ( ! empty( $email ) ) && ( '@' !== $email ) ) {
                $display = ( '' !== $linktext ) ? $linktext : $email;
                $return  = $before;
                $return .= sprintf( "<a href='%1\$s'>%2\$s</a>", $this->_esc_url( 'mailto:' . $email ), $this->_esc_html( $display ) );
                $return .= $after;
                return $return;
            } else  return '';
        }//179
        /**
         * @description Retrieves the HTML link to the URL of the author of the current comment.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_author_link( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            $url     = $this->_get_comment_author_url( $comment );
            $author  = $this->_get_comment_author( $comment );
            if ( empty( $url ) || 'http://' === $url ) $return = $author;
            else $return = "<a href='$url' rel='external nofollow ugc' class='url'>$author</a>";
            return $this->_apply_filters( 'get_comment_author_link', $return, $author, $comment->comment_ID );
        }//220 from comment-template
        /**
         * @description Displays the HTML link to the URL of the author of the current comment.
         * @param int $comment_ID
         */
        public function comment_author_link( $comment_ID = 0 ):void{
            echo $this->_get_comment_author_link( $comment_ID );
        }//254 from comment-template
        /**
         * @description Retrieve the IP address of the author of the current comment.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_author_IP( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            return $this->_apply_filters( 'get_comment_author_IP', $comment->comment_author_IP, $comment->comment_ID, $comment );  // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
        }//268 from comment-template
        /**
         * @description Displays the IP address of the author of the current comment.
         * @param int $comment_ID
         */
        public function comment_author_IP( $comment_ID = 0 ):void{
            echo $this->_esc_html( $this->_get_comment_author_IP( $comment_ID ) );
        }//293 from comment-template
    }
}else die;