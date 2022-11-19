<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Templates\Components\comments_popup_template_view;
if(ABSPATH){
    trait _comment_template_05 {
        use _init_comment;
        /**
         * @description Displays the link to the comments for the current post ID.
         * @param bool $zero
         * @param bool $one
         * @param bool $more
         * @param string $css_class
         * @param bool $none
         * @return string
         */
        protected function _comments_popup_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ):string{
            $post_id    = $this->_get_the_ID();
            $post_title = $this->_get_the_title();
            $number     = $this->_get_comments_number($post_id );
            if ( false === $zero ) $this->args['zero'] = sprintf("No Comments<span class='screen-reader-text'>on %s</span>, $post_title");
            if ( false === $one ) $this->args['one'] = sprintf("1 Comment<span class='screen-reader-text'>on %s</span>, $post_title");
            if ( false === $more ) {
                $string['single'] = "%1$\s Comment<span class='screen-reader-text'>on %2\$s</span>";
                $string['plural'] = "%1$\s Comments<span class='screen-reader-text'>on %2\$s</span>";
                $strings = $this->_n($string['single'],$string['plural'], $number );
                $this->args['more'] = sprintf($strings, $this->_number_format_i18n( $number ), $post_title);
            }
            if ( false === $none )$none =(bool) sprintf("Comments Off<span class='screen-reader-text'>on %s</span>, $post_title");
            if ( 0 === $number && ! $this->_comments_open() && ! $this->_pings_open() ){
                $css =  !empty( $css_class ) ? " class='{$this->_esc_attr( $css_class )}'" : '';
                $this->args['empty'] = "<span $css> $none</span>";
                return false;
            }
            if ( $this->_post_password_required() ) {
                $this->args['enter_password'] = $this->__( 'Enter your password to view comments.' );
                return false;
            }
            if ( 0 === $number ){
                $respond_link = $this->_get_permalink() . '#respond';
                $this->args['respond_link'] = $this->_apply_filters( 'respond_link', $respond_link, $post_id );
            }else $this->args['respond_link'] = $this->_get_comments_link();
            if ( ! empty( $css_class ) ) $this->args['respond_css'] = " class='$css_class'";
            $attributes = "";
            $this->args['attributes'] = $this->_apply_filters('comments_popup_link_attributes', $attributes );
            $this->args['comments_number'] = $this->_get_comments_number_text( $zero, $one, $more );
            return new comments_popup_template_view($this->args);
        }//1575 from comment-template
        /**
         * @description Retrieves HTML content for reply to comment link.
         * @param null $comment
         * @param null $post
         * @param array ...$args
         * @return bool
         */
        protected function _get_comment_reply_link( $comment = null, $post = null, ...$args ):bool{
            $defaults = [/* translators: Comment reply button text. %s: Comment author name. */
                'add_below' => 'comment','respond_id' => 'respond','reply_text' => $this->__( 'Reply' ),'reply_to_text' => $this->__( 'Reply to %s' ),
                'login_text' => $this->__( 'Log in to Reply' ),'max_depth' => 0,'depth' => 0,'before' => '','after' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( 0 === $args['depth'] || $args['max_depth'] <= $args['depth'] )
                return false;
            $comment = $this->_get_comment( $comment );
            if ( empty( $comment ) ) return false;
            if ( empty( $post ) )  $post = $comment->comment_post_ID;
            $post = $this->_get_post( $post );
            if ( ! $this->_comments_open( $post->ID ) ) return false;
            if ( $this->_get_option( 'page_comments' ) )
                $permalink = str_replace( '#comment-' . $comment->comment_ID, '', $this->_get_comment_link( $comment ) );
            else $permalink = $this->_get_permalink( $post->ID );
            $args = $this->_apply_filters( 'comment_reply_link_args', $args, $comment, $post );
            if ( $this->_get_option( 'comment_registration' ) && ! $this->_is_user_logged_in() ) {
                $link = sprintf(
                    "<a rel='nofollow' class='comment-reply-login' href='%s'>%s</a>",
                    $this->_esc_url( $this->_tp_login_url( $this->_get_permalink() ) ),
                    $args['login_text']
                );
            }else{
                $data_attributes = [
                    'comment_id'      => $comment->comment_ID,
                    'post_id'         => $post->ID,
                    'below_element'   => $args['add_below'] . '_' . $comment->comment_ID,
                    'respond_element' => $args['respond_id'],
                    'reply_to'        => sprintf( $args['reply_to_text'], $this->_get_comment_author( $comment ) ),
                ];
                $data_attribute_string = '';
                foreach ( $data_attributes as $name => $value )
                    $data_attribute_string .= " data-${name}='{$this->_esc_attr( $value )}'";
                $data_attribute_string = trim( $data_attribute_string );
                $link = sprintf("<a rel='nofollow' class='comment-reply-link' href='%s' %s aria-label='%s'>%s</a>",
                    $this->_esc_url($this->_add_query_arg(
                        ['reply_to_com' => $comment->comment_ID,'unapproved' => false,'moderation-hash' => false,],
                        $permalink
                    ))."#{$args['respond_id']}",
                    $data_attribute_string,
                    $this->_esc_attr( sprintf( $args['reply_to_text'], $this->_get_comment_author( $comment ) ) ),
                    $args['reply_text']
                );
            }
            return $this->_apply_filters( 'comment_reply_link', $args['before'] . $link . $args['after'], $args, $comment, $post );
        }//1675 from comment-template
        /**
         * @description Displays the HTML content for reply to comment link.
         * @param null $comment
         * @param null $post
         * @param array ...$args
         */
        public function comment_reply_link( $comment = null, $post = null, ...$args):void{
            echo $this->_get_comment_reply_link( $comment, $post, $args );
        }//1795 from comment-template
        /**
         * @description Retrieves HTML content for reply to post link.
         * @param null $post
         * @param array ...$args
         * @return bool
         */
        protected function _get_post_reply_link($post = null, ...$args):bool{
            $defaults = [
                'add_below'  => 'post','respond_id' => 'respond','reply_text' => $this->__( 'Leave a Comment' ),
                'login_text' => $this->__( 'Log in to leave a Comment' ),'before' => '','after' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $post = $this->_get_post( $post );
            if ( ! $this->_comments_open( $post->ID ) ) return false;
            if ( $this->_get_option( 'comment_registration' ) && ! $this->_is_user_logged_in() ) {
                $link = sprintf(
                    "<a rel='nofollow' class='comment-reply-login' href='%s'>%s</a>",
                    $this->_tp_login_url( $this->_get_permalink() ), $args['login_text']
                );
            }else {
                $onclick = sprintf(
                    'return addComment.moveForm( "%1$s-%2$s", "0", "%3$s", "%2$s" )',
                    $args['add_below'],$post->ID,$args['respond_id']
                );
                $s='%s';
                $link = sprintf(
                    "<a rel='nofollow' class='comment-reply-link' href='%s' onclick='".$s."'>%s</a>",
                    $this->_get_permalink( $post->ID ) . '#' . $args['respond_id'],
                    $onclick,$args['reply_text']
                );
            }
            $formatted_link = $args['before'] . $link . $args['after'];
            return $this->_apply_filters( 'post_comments_link', $formatted_link, $post );
        }//1822 from comment-template
        /**
         * @description Displays the HTML content for reply to post link.
         * @param null $post
         * @param array ...$args
         */
        public function post_reply_link($post = null, ...$args):void{
            echo $this->_get_post_reply_link($post, $args);
        }//1885 from comment-template
        /**
         * @description Retrieves HTML content for cancel comment reply link.
         * @param string $text
         * @return mixed
         */
        protected function _get_cancel_comment_reply_link( $text = '' ){
            if ( empty( $text ) ) $text = $this->__( 'Click here to cancel reply.' );
            $style = isset( $_GET['reply_to_com'] ) ? '' : ' style="display:none;"';
            $link  = $this->_esc_html( $this->_remove_query_arg( array( 'reply_to_com', 'unapproved', 'moderation-hash' ) ) ) . '#respond';
            $formatted_link = "<a rel='nofollow' class='cancel-comment-reply-link' href='$link' $style > $text </a>";
            return $this->_apply_filters( 'cancel_comment_reply_link', $formatted_link, $link, $text );
        }//1898 from comment-template
        /**
         * @description Displays HTML content for cancel comment reply link.
         * @param string $text
         */
        public function cancel_comment_reply_link( $text = '' ):void{
            echo $this->_get_cancel_comment_reply_link( $text );
        }//1928 from comment-template
        /**
         * @description Retrieves hidden input HTML for replying to comments.
         * @param mixed $post_id
         * @return mixed
         */
        protected function _get_comment_id_fields( $post_id = 0 ){
            if ( empty( $post_id ) ) $post_id = $this->_get_the_ID();
            $reply_to_id = isset( $_GET['reply_to_com'] ) ? (int) $_GET['reply_to_com'] : 0;
            $result      = "<input type='hidden' name='comment_post_ID' value='$post_id' id='comment_post_ID' />\n";
            $result     .= "<input type='hidden' name='comment_parent' id='comment_parent' value='$reply_to_id' />\n";
            return $this->_apply_filters( 'comment_id_fields', $result, $post_id, $reply_to_id );
        }//1938 from comment-template
        /**
         * @description Outputs hidden input HTML for replying to comments.
         * @param int $post_id
         */
        public function comment_id_fields( $post_id = 0 ):void{
            echo $this->_get_comment_id_fields( $post_id );
        }//1973 from comment-template
        /**
         * @description Displays text based on comment reply status.
         * @param bool $no_reply_text
         * @param bool $reply_text
         * @param bool $link_to_parent
         */
        protected function _comment_form_title( $no_reply_text = false, $reply_text = false, $link_to_parent = true ):void{
            $this->tp_comment = $this->_init_comment();
            if ( false === $no_reply_text )
                $no_reply_text = $this->__( 'Leave a Reply' );
            if ( false === $reply_text ) $reply_text = $this->__( 'Leave a Reply to %s' );
            $reply_to_id = isset( $_GET['reply_to_com'] ) ? (int) $_GET['reply_to_com'] : 0;
            if ( 0 === $reply_to_id ) echo $no_reply_text;
            else {
                $this->tp_comment = $this->_get_comment( $reply_to_id );
                $comment_id ='#comment_';
                if ( $link_to_parent )
                    $author = "<a href='$comment_id{$this->_get_comment_ID()}'>{$this->_get_comment_author( $this->tp_comment )}</a>";
                else $author = $this->_get_comment_author( $this->tp_comment );
                printf( $reply_text, $author );
            }
        }//1990 from comment-template
    }
}else die;