<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _comment_template_03 {
        use _init_rewrite;
        /**
         * @description Retrieves the comment ID of the current comment.
         * @return mixed
         */
        protected function _get_comment_ID(){
            $comment    = $this->_get_comment();
            $comment_ID = ! empty( $comment->comment_ID ) ? $comment->comment_ID : '0';
            return $this->_apply_filters( 'get_comment_ID', $comment_ID, $comment );
        }//666 from comment-template
        /**
         * @description Displays the comment ID of the current comment.
         */
        public function comment_ID():void{
            echo $this->_get_comment_ID();
        }//687 from comment-template
        /**
         * @description Retrieves the link to a given comment.
         * @param null $comment
         * @param array $args
         * @return mixed
         */
        protected function _get_comment_link( $comment = null, $args = [] ){
            $tp_rewrite = $this->_init_rewrite();
            $comment = $this->_get_comment( $comment );
            $defaults = ['type' => 'all','page' => '','per_page' => '','max_depth' => '','cpage' => null,];
            $args = $this->_tp_parse_args( $args, $defaults );
            $link = $this->_get_permalink( $comment->comment_post_ID );
            if ( ! is_null( $args['cpage'] ) ) $cpage = $args['cpage'];
            else {
                if ( '' === $args['per_page'] && $this->_get_option( 'page_comments' ) )
                    $args['per_page'] = $this->_get_option( 'comments_per_page' );
                if ( empty( $args['per_page'] ) ) {
                    $args['per_page'] = 0;
                    $args['page']     = 0;
                }
                $cpage = $args['page'];
                if ( '' === $cpage ) {
                    if ( ! empty( $this->in_comment_loop ) )  $cpage = $this->_get_query_var( 'cpage' );
                     else $cpage = $this->_get_page_of_comment( $comment->comment_ID, $args );
                }
                if (1 === $cpage && 'oldest' === $this->_get_option( 'default_comments_page' )) $cpage = '';
            }
            if ( $cpage && $this->_get_option( 'page_comments' ) ) {
                if ( $tp_rewrite->using_permalinks() ) {
                    if ( $cpage ) $link = $this->_trailingslashit( $link ) . $tp_rewrite->comments_pagination_base . '-' . $cpage;
                    $link = $this->_user_trailingslashit( $link, 'comment' );
                } elseif ( $cpage ) $link = $this->_add_query_arg( 'cpage', $cpage, $link );
            }
            if ( $tp_rewrite->using_permalinks() ) $link = $this->_user_trailingslashit( $link, 'comment' );
            $link .= '#comment-' . $comment->comment_ID;
            return $this->_apply_filters( 'get_comment_link', $link, $comment, $args, $cpage );
        }//716 from comment-template todo is somewhere double
        /**
         * @description Retrieves the link to the current post comments.
         * @param $post_id
         * @return mixed
         */
        protected function _get_comments_link($post_id = 0 ){
            $hash = $this->_get_comments_number( $post_id ) ? '#comments' : '#respond';
            $comments_link = $this->_get_permalink( $post_id ) . $hash;
            return $this->_apply_filters( 'get_comments_link', $comments_link, $post_id );
        }//814 from comment-template
        /**
         * @description Retrieves the amount of comments a post has.
         * @param int $post_id
         * @return mixed
         */
        protected function _get_comments_number( $post_id = 0 ){
            $post = $this->_get_post( $post_id );
            if ( ! $post ) $count = 0;
            else {
                $count   = $post->comment_count;
                $post_id = $post->ID;
            }
            return $this->_apply_filters( 'get_comments_number', $count, $post_id );
        }//856 from comment-template
        /**
         * @description Displays the language string for the number of comments the current post has.
         * @param bool $zero
         * @param bool $one
         * @param bool $more
         * @param int $post_id
         */
        public function comments_number( $zero = false, $one = false, $more = false, $post_id = 0 ):void{
            echo $this->_get_comments_number_text( $zero, $one, $more, $post_id );
        }//888 from comment-template
        /**
         * @description Displays the language string for the number of comments the current post has.
         * @param bool $zero
         * @param bool $one
         * @param mixed $more
         * @param int $post_id
         * @return mixed
         */
        protected function _get_comments_number_text( $zero = false, $one = false, $more = false, $post_id = 0 ){
            $number = $this->_get_comments_number( $post_id );
            if ( $number > 1 ) {
                if ( false === $more )
                    $output = sprintf( $this->_n( '%s Comment', '%s Comments', $number ), $this->_number_format_i18n( $number ) );
                else {
                    if ( 'on' === $this->_x( 'off', 'Comment number declension: on or off' ) ) {
                        $text = preg_replace( '/#<span class="screen-reader-text">.+?</span>#/s', '', $more );
                        $text = preg_replace( '/&.+?;/', '', $text ); // Kill entities.
                        $text = trim( strip_tags( $text ), '% ' );
                        if ( $text && ! preg_match( '/\d+/', $text ) && false !== strpos( $more, '%' ) ) {
                            $new_text = $this->_n( '%s Comment', '%s Comments', $number );
                            $new_text = trim( sprintf( $new_text, '' ) );
                            $more = str_replace( $text, $new_text, $more );
                            if ( false === strpos( $more, '%' ) ) $more = '% ' . $more;
                        }
                    }
                    $output = str_replace( '%', $this->_number_format_i18n( $number ), $more );
                }
            } elseif ( 0 === $number )
                $output = ( false === $zero ) ? $this->__( 'No Comments' ) : $zero;
            else $output = ( false === $one ) ? $this->__( '1 Comment' ) : $one;
            return $this->_apply_filters( 'comments_number', $output, $number );
        }//904 from comment-template
        /**
         * @description Retrieves the text of the current comment.
         * @param int $comment_ID
         * @param array $args
         * @return mixed
         */
        protected function _get_comment_text( $comment_ID = 0, ...$args ){
            $comment = $this->_get_comment( $comment_ID );
            $comment_content = $comment->comment_content;
            if ($comment->comment_parent && $this->_is_comment_feed()) {
                $parent = $this->_get_comment( $comment->comment_parent );
                if ( $parent ) {
                    $parent_link = $this->_esc_url( $this->_get_comment_link( $parent ) );
                    $name        = $this->_get_comment_author( $parent );
                    $comment_content = sprintf($this->_ent2ncr( $this->__( 'In reply to %s.' ) ),
                            "<a href='$parent_link'>$name</a>") . "\n\n" . $comment_content;
                }
            }
            return $this->_apply_filters( 'get_comment_text', $comment_content, $comment, $args );
        }//970 from comment-template
        /**
         * @description Displays the text of the current comment.
         * @param int $comment_ID
         * @param array ...$args
         * @return mixed
         */
        protected function _get_text_comment( $comment_ID = 0, ...$args){
            $comment = $this->_get_comment( $comment_ID );
            $comment_text = $this->_get_comment_text( $comment, $args );
            return $this->_apply_filters( 'comment_text', $comment_text, $comment, $args );
        }//1013
        protected function _comment_text( $comment_ID = 0, ...$args):void{
            echo $this->_get_text_comment( $comment_ID,$args);
        }//1013 from comment-template
        /**
         * @description Retrieves the comment time of the current comment.
         * @param string $format
         * @param bool $gmt
         * @param bool $translate
         * @return mixed
         */
        protected function _get_comment_time( $format = '', $gmt = false, $translate = true ){
            $comment = $this->_get_comment();
            $comment_date = $gmt ? $comment->comment_date_gmt : $comment->comment_date;
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'time_format' );
            $date = $this->_mysql2date( $_format, $comment_date, $translate );
            return $this->_apply_filters( 'get_comment_time', $date, $format, $gmt, $translate, $comment );
        }//1044 from comment-template
    }
}else die;