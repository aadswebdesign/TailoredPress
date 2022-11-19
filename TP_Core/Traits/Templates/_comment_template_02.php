<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_comment;
if(ABSPATH){
    trait _comment_template_02 {
        use _init_comment;
        /**
         * @description Retrieves the URL of the author of the current comment, not linked.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_author_url( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            $url     = '';
            $id      = 0;
            if ( ! empty( $comment ) ) {
                $author_url = ( 'http://' === $comment->comment_author_url ) ? '' : $comment->comment_author_url;
                $url        = $this->_esc_url( $author_url, array( 'http', 'https' ) );
                $id         = $comment->comment_ID;
            }
            return $this->_apply_filters( 'get_comment_author_url', $url, $id, $comment );
        }// from comment-template
        /**
         * @description Displays the URL of the author of the current comment, not linked.
         * @param int $comment_ID
         */
        public function comment_author_url( $comment_ID = 0 ):void{
            $comment    = $this->_get_comment( $comment_ID );
            $author_url = $this->_get_comment_author_url( $comment );
            echo $this->_apply_filters( 'comment_url', $author_url, $comment->comment_ID );
        }//340 from comment-template
        /**
         * @description Retrieves the HTML link of the URL of the author of the current comment.
         * @param string $linktext
         * @param string $before
         * @param string $after
         * @param int $comment
         * @return mixed
         */
        protected function _get_comment_author_url_link( $linktext = '', $before = '', $after = '', $comment = 0 ){
            $url     = $this->_get_comment_author_url( $comment );
            $display = ( '' !== $linktext ) ? $linktext : $url;
            $display = str_replace( 'http://www.', '', $display );
            $display .= str_replace( 'http://', '', $display );
            if ( '/' === substr( $display, -1 ) ) $display = substr( $display, 0, -1 );
            $return = "$before<a href='$url' rel='external'>$display</a>$after";
            return $this->_apply_filters( 'get_comment_author_url_link', $return );
        }//379 from comment-template
        /**
         * @description Displays the HTML link of the URL of the author of the current comment.
         * @param string $linktext
         * @param string $before
         * @param string $after
         * @param int $comment
         */
        public function comment_author_url_link( $linktext = '', $before = '', $after = '', $comment = 0 ):void{
            echo $this->_get_comment_author_url_link( $linktext, $before, $after, $comment );
        }//416 from comment-template
        /**
         * @description Generates semantic classes for each comment element.
         * @param string $css_class
         * @param null $comment
         * @param null $post_id
         * @return null|string
         */
        protected function _comment_class( $css_class  = '', $comment = null, $post_id = null ):string{
            return "class='". implode( ' ', $this->_get_comment_class( $css_class, $comment, $post_id ) ) . "'";
        }//434 from comment-template
        /**
         * @description Returns the classes for the comment div as an array.
         * @param mixed $css_class
         * @param null $comment_id
         * @param null $post_id
         * @return array
         */
        protected function _get_comment_class( $css_class = '', $comment_id = null, $post_id = null ):array{
            $classes = [];
            $comment = $this->_get_comment( $comment_id );
            if ( ! $comment ) return $classes;
            $classes[] = ( empty( $comment->comment_type ) ) ? 'comment' : $comment->comment_type;
            $user = $comment->user_id ? $this->_get_user_data( $comment->user_id ) : false;
            if ( $user ) {
                $classes[] = 'by-user';
                $classes[] = 'comment-author-' . $this->_sanitize_html_class( $user->user_nicename, $comment->user_id );
                $post = $this->_get_post( $post_id );
                if ($post && $comment->user_id === $post->post_author) $classes[] = 'by-post-author';
            }
            if ( empty( $this->tp_comment_alt ) ) $this->tp_comment_alt = 0;
            if ( empty( $this->tp_comment_depth ) ) $this->tp_comment_depth = 1;
            if ( empty( $this->tp_comment_thread_alt ) ) $this->tp_comment_thread_alt = 0;
            if ( $this->tp_comment_alt % 2 ) {
                $classes[] = 'odd';
                $classes[] = 'alt';
            } else  $classes[] = 'even';
            $this->tp_comment_alt++;
            if ( 1 === $this->tp_comment_depth ) {
                if ( $this->tp_comment_thread_alt % 2 ) {
                    $classes[] = 'thread-odd';
                    $classes[] = 'thread-alt';
                } else $classes[] = 'thread-even';
                $this->tp_comment_thread_alt++;
            }
            $classes[] = "depth-$this->tp_comment_depth";
            if ( ! empty( $css_class ) ) {
                if ( ! is_array( $css_class ) ) $css_class = preg_split( '#\s+#', $css_class );
                $classes = array_merge( $classes, $css_class );
            }
            $classes = array_map( 'esc_attr', $classes );
            return $this->_apply_filters( 'comment_class', $classes, $css_class, $comment->comment_ID, $comment, $post_id );
        }//460 from comment-template
        /**
         * @description Retrieves the comment date of the current comment.
         * @param string $format
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_date( $format = '', $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'date_format' );
            $date = $this->_mysql2date( $_format, $comment->comment_date );
            return $this->_apply_filters( 'get_comment_date', $date, $format, $comment );
        }//553 from comment-template
        /**
         * @description Displays the comment date of the current comment.
         * @param string $format
         * @param int $comment_ID
         */
        public function comment_date( $format = '', $comment_ID = 0 ):void{
            echo $this->_get_comment_date( $format, $comment_ID );
        }//582 from comment-template
        /**
         * @description Retrieves the excerpt of the given comment.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_excerpt( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            if ( ! $this->_post_password_required( $comment->comment_post_ID ) )
                $comment_text = strip_tags( str_replace( array( "\n", "\r" ), ' ', $comment->comment_content ) );
            else $comment_text = $this->__( 'Password protected' );
            $comment_excerpt_length = (int) $this->_x( '20', 'comment_excerpt_length' );
            $comment_excerpt_length = $this->_apply_filters( 'comment_excerpt_length', $comment_excerpt_length );
            $excerpt = $this->_tp_trim_words( $comment_text, $comment_excerpt_length, '&hellip;' );
            return $this->_apply_filters( 'get_comment_excerpt', $excerpt, $comment->comment_ID, $comment );
        }//598 from comment-template
        /**
         * @description Displays the excerpt of the current comment.
         * @param int $comment_ID
         */
        protected function _comment_excerpt( $comment_ID = 0 ):void{
            $comment         = $this->_get_comment( $comment_ID );
            $comment_excerpt = $this->_get_comment_excerpt( $comment );
            echo $this->_apply_filters( 'comment_excerpt', $comment_excerpt, $comment->comment_ID );
        }// 643 from comment-template
    }
}else die;