<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Forms\TP_Comment_Form;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Walkers\TP_Walker_Comment;
if(ABSPATH){
    trait _comment_template_06 {
        use _init_queries;
        use _init_comment;
        private $__html;
        public $form_args = [];
        /**
         * @description Displays a list of comments.
         * @param null $comments
         * @param array ...$args
         * @return null|string
         */
        protected function _tp_list_comments($comments = null, ...$args):string{
            $this->tp_query = $this->_init_query();
            $this->tp_in_comment_loop = true;
            $this->tp_comment_alt        = 0;
            $this->tp_comment_thread_alt = 0;
            $this->tp_comment_depth      = 1;
            $defaults = ['walker' => null,'max_depth' => '','style' => 'ul','callback' => null,'end-callback' => null,
                'type' => 'all','page' => '','per_page' => '','avatar_size' => 32,'reverse_top_level' => null,
                'reverse_children' => '','format' => 'html5','short_ping' => false,'echo' => true,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $parsed_args = $this->_apply_filters( 'tp_list_comments_args', $parsed_args );
            $_comments = null;
            if ( null !== $comments ) {
                $comments = (array) $comments;
                if ( empty( $comments ) ) return false;
                if ( 'all' !== $parsed_args['type'] ) {
                    $comments_by_type = $this->_separate_comments( $comments );
                    if(empty( $comments_by_type[ $parsed_args['type']])) return false;
                    $_comments = $comments_by_type[ $parsed_args['type'] ];
                } else $_comments = $comments;
            }else if ( $parsed_args['page'] || $parsed_args['per_page'] ) {
                $current_cpage = $this->_get_query_var( 'cpage' );
                if ( ! $current_cpage )
                    $current_cpage = 'newest' === $this->_get_option( 'default_comments_page' ) ? 1 : $this->tp_query->max_num_comment_pages;
                $current_per_page = $this->_get_query_var( 'comments_per_page' );
                if ( $parsed_args['page'] !== $current_cpage || $parsed_args['per_page'] !== $current_per_page ) {
                    $comment_args = ['post_id' => $this->_get_the_ID(),'orderby' => 'comment_date_gmt',
                        'order'   => 'ASC','status'  => 'approve',];
                    if ( $this->_is_user_logged_in() )
                        $comment_args['include_unapproved'] = array( $this->_get_current_user_id() );
                    else {
                        $unapproved_email = $this->_tp_get_unapproved_comment_author_email();
                        if ( $unapproved_email )  $comment_args['include_unapproved'] = [$unapproved_email];
                    }
                    $comments = $this->_get_comments( $comment_args );
                    if ( 'all' !== $parsed_args['type'] ) {
                        $comments_by_type = $this->_separate_comments( $comments );
                        if ( empty( $comments_by_type[ $parsed_args['type'] ] ) ) return false;
                        $_comments = $comments_by_type[ $parsed_args['type'] ];
                    } else $_comments = $comments;
                }
            }else{
                if ( empty( $this->tp_query->comments ) ) return false;
                if ( 'all' !== $parsed_args['type'] ) {
                    if ( $this->tp_query->comments_by_type === null )
                        $this->tp_query->comments_by_type = $this->_separate_comments( $this->tp_query->comments );
                    if ( empty( $this->tp_query->comments_by_type[ $parsed_args['type'] ] ) ) return false;
                    $_comments = $this->tp_query->comments_by_type[ $parsed_args['type'] ];
                } else $_comments = $this->tp_query->comments;
                if ( $this->tp_query->max_num_comment_pages ) {
                    $default_comments_page = $this->_get_option( 'default_comments_page' );
                    $cpage                 = $this->_get_query_var( 'cpage' );
                    if ( 'newest' === $default_comments_page ) {
                        $parsed_args['cpage'] = $cpage;
                    } elseif ( 1 === $cpage ) $parsed_args['cpage'] = '';
                    else  $parsed_args['cpage'] = $cpage;
                    $parsed_args['page']     = 0;
                    $parsed_args['per_page'] = 0;
                }
            }
            if ( '' === $parsed_args['per_page'] && $this->_get_option( 'page_comments' ) )
                $parsed_args['per_page'] = $this->_get_query_var( 'comments_per_page' );
            if ( empty( $parsed_args['per_page'] ) ) {
                $parsed_args['per_page'] = 0;
                $parsed_args['page']     = 0;
            }
            if ( '' === $parsed_args['max_depth'] ) {
                if ( $this->_get_option( 'thread_comments' ) )
                    $parsed_args['max_depth'] = $this->_get_option( 'thread_comments_depth' );
                else $parsed_args['max_depth'] = -1;
            }
            if ( '' === $parsed_args['page'] ) {
                if ( empty( $this->tp_overridden_cpage ) ) {
                    $parsed_args['page'] = $this->_get_query_var( 'cpage' );
                } else {
                    $threaded            = ( -1 !== $parsed_args['max_depth'] );
                    $parsed_args['page'] = ( 'newest' === $this->_get_option( 'default_comments_page' ) ) ? $this->_get_comment_pages_count( $_comments, $parsed_args['per_page'], $threaded ) : 1;
                    $this->_set_query_var( 'cpage', $parsed_args['page'] );
                }
            }
            // Validation check.
            //$parsed_args['page'] = (int) $parsed_args['page'];
            if ( 0 === $parsed_args['page'] && 0 !== $parsed_args['per_page'] ) $parsed_args['page'] = 1;
            if ( null === $parsed_args['reverse_top_level'] )
                $parsed_args['reverse_top_level'] = ( 'desc' === $this->_get_option( 'comment_order' ) );
            $this->_tp_queue_comments_for_comment_meta_lazy_load( $_comments );
            if ( empty( $parsed_args['walker'] ) ) $walker = new TP_Walker_Comment();
            else $walker = $parsed_args['walker'];
            $output = $walker->paged_walk( $_comments, $parsed_args['max_depth'], $parsed_args['page'], $parsed_args['per_page'], $parsed_args );
            $this->tp_in_comment_loop = false;
            if ( $parsed_args['echo'] ){
                echo $output;
                return null;
            }
            return $output;
        }//2071 from comment-template
        /**
         * @description Outputs a complete commenting form for use within a template.
         * @param null $post_id
         * @param array ...$args
         * @return bool|string
         */
        protected function _setup_comment_form($post_id = null, ...$args ){
            if ( null === $post_id ) $post_id = $this->_get_the_ID();
            if ( ! $this->_comments_open( $post_id ) ) {
                $this->_do_action( 'comment_form_comments_closed' );
                return false;
            }
            $commenter     = $this->_tp_get_current_commenter();
            $user          = $this->_tp_get_current_user();
            if($user instanceof TP_User){} //todo for now, until get_current_user has been setup
            $required_attribute = ' required';
            $checked_attribute  = 'checked';
            $user_identity = $user->exists() ? $user->display_name : '';
            $args['user_identity'] = $user_identity;
            $args = $this->_tp_parse_args( $args );
            $required   = $this->_get_option( 'require_name_email' );
            $required_indicator = "<span class='required' aria-hidden='true'>*</span>";
            $fields = [
                'author' => sprintf("<details open class='comment-field-block author'>%s %s</details>",
                    sprintf("<summary><label for='author'>%s%s</label></summary>",
                        $this->__( 'Name:' ),( $required ? $required_indicator : '' )),
                    sprintf("<div>%s</div>",
                        sprintf("<input id='author' name='author' type='text' value='%s' size='30' maxlength='245' %s/>",
                            $this->_esc_attr( $commenter['comment_author'] ),( $required ? $required_attribute : '' )))
                ),
                'email' => sprintf("<details open class='comment-field-block email'>%s %s</details>",
                    sprintf("<summary><label for='email'>%s%s</label></summary>",
                        $this->__( 'Email:' ), ( $required ? $required_indicator : '' )),
                    sprintf("<div>%s</div>",
                        sprintf("<input  id='email' name='email' type='email' value='%s' size='30' maxlength='100' aria-describedby='email-notes' %s/>",
                            $this->_esc_attr( $commenter['comment_author_email'] ),( $required ? $required_attribute : '' )))
                ),
                'url' => sprintf("<details open class='comment-field-block url'>%s %s</details>",
                    sprintf("<summary><label for='url'>%s</label></summary>", $this->__( 'Website:' )),
                    sprintf("<div>%s</div>",
                        sprintf("<input  id='url' name='url' type='url' value='%s' size='30' maxlength='200'/>",
                            $this->_esc_attr( $commenter['comment_author_url'] )))
                )
            ];
            if ( $this->_has_action( 'set_comment_cookies', 'tp_set_comment_cookies' ) && $this->_get_option( 'show_comments_cookies_opt_in' ) ){
                $consent = empty( $commenter['comment_author_email'] ) ? '' : $checked_attribute;
                $fields['cookies'] = sprintf("<details open class='comment-field-block cookie'>%s %s</details>",
                    sprintf("<div>%s</div>", sprintf("<input  id='tp_comment_cookie_consent' name='tp_comment_cookie_consent' type='checkbox' value='yes' %s/>", $consent)),
                    sprintf("<summary><label for='tp_comment_cookie_consent'>%s</label></summary>",
                        $this->__( 'Save my name, email, and website in this browser for the next time I comment.'))
                );
                if ( isset( $args['fields'] ) && ! isset( $args['fields']['cookies'] ) )
                    $args['fields']['cookies'] = $fields['cookies'];
            }
            $required_text = sprintf(
                "<span class='required-field-message' aria-hidden='true'>{$this->__( 'Required fields are marked %s' )}</span>",
                trim( $required_indicator )
            );
            $fields = $this->_apply_filters( 'comment_form_default_fields', $fields );
            $defaults = [
                'fields' => $fields,
                'comment_field' => sprintf("<details open class='comment-field-block comment'>%s %s</details>",
                    sprintf("<summary><label for='author'>%s%s</label></summary>", $this->_x( 'Comment', 'noun' ),$required_indicator),
                    sprintf("<div>%s</div>", sprintf("<textarea id='comment' name='comment' cols='45' rows='8' maxlength='65525' $required_attribute></textarea>"))
                ),
                'must_log_in' => sprintf("<p class='must-log-in'>%s</p>",
                    sprintf($this->__("You must be <a href='%s'>logged in</a> to post a comment."),
                        $this->_tp_login_url( $this->_apply_filters( 'the_permalink', $this->_get_permalink( $post_id ), $post_id)))
                ),
                'logged_in_as' => sprintf("<p class='logged-in-as'>%s</p>",
                    sprintf($this->__("<a href='%1\$s' aria-label='%2\$s'>Logged in as %3\$s</a>. <a href='%4\$s'>Log out?</a>"),
                        $this->_get_edit_user_link(),$this->_esc_attr( sprintf( $this->__( 'Logged in as %s. Edit your profile.' ),$user_identity)),$user_identity,
                        $this->_tp_logout_url( $this->_apply_filters( 'the_permalink', $this->_get_permalink( $post_id ), $post_id )),$required_text)
                ),
                'comment_notes_before' => sprintf("<p class='comment-notes'>%s</p>",
                    sprintf("<span class='email-notes'>%s</span>", $this->__( 'Your email address will not be published.' ))),
                'comment_notes_after'  => '',
                'action'               => $this->_site_url( '/tp_comments_post.php' ),//todo
                'id_form'              => 'comment_form',
                'id_submit'            => 'submit',
                'class_container'      => 'comment-respond',
                'class_form'           => 'comment-form',
                'class_submit'         => 'submit',
                'name_submit'          => 'submit',
                'title_reply'          => $this->__( 'Leave a Reply' ),
                /* translators: %s: Author of the comment being replied to. */
                'title_reply_to'       => $this->__( 'Leave a Reply to %s' ),
                'title_reply_before'   => "<h3 id='reply_title' class='comment-reply-title'>",
                'title_reply_after'    => '</h3>',
                'cancel_reply_before'  => ' <small>',
                'cancel_reply_after'   => '</small>',
                'cancel_reply_link'    => $this->__( 'Cancel reply' ),
                'label_submit'         => $this->__( 'Post Comment' ),
                'submit_button'        => "<input name='%1\$s' type='submit' id='%2\$s' class='%3\$s' value='%4\$s' />",
                'submit_field'         => "<p class='form-submit'>%1\$s %2\$s</p>",
                'format'               => 'html5', //todo get rid of this 'html5' is standard and no other formats
            ];
            $args = $this->_tp_parse_args( $args, $this->_apply_filters( 'comment_form_defaults', $defaults ) );
            $args = array_merge( $defaults, $args );
            if ( isset( $args['fields']['email'] ) && false === strpos( $args['comment_notes_before'], "id='email_notes'" ) )
                $args['fields']['email'] = str_replace(' aria-describedby="email-notes"','', $args['fields']['email']);
            $this->__html = $this->_get_action( 'comment_form_before' );
            $this->__html .= "<div id='respond' class='{$this->_esc_attr( $args['class_container'] )}'>";
            $this->__html .= $args['title_reply_before'];
            ob_start();
            $this->_comment_form_title( $args['title_reply'], $args['title_reply_to'] );
            $this->__html .= ob_get_clean();
            if($this->_get_option( 'thread_comments' )){
                $this->__html .= $args['cancel_reply_before'];
                $this->__html .= $this->_get_cancel_comment_reply_link( $args['cancel_reply_link'] );
                $this->__html .= $args['cancel_reply_after'];
            }
            $this->__html .= $args['title_reply_after'];
            if ( $this->_get_option( 'comment_registration' ) && ! $this->_is_user_logged_in() ){
                $this->__html .= $args['must_log_in'];
                ob_start();
                $this->_do_action( 'comment_form_must_log_in_after' );
                $this->__html .= ob_get_clean();
            }else $this->__html .= new TP_Comment_Form($args);
            $this->__html .= "</div>"; //end form container
            $this->__html .= $this->_get_action( 'comment_form_after' );
            return (string) $this->__html;
        }//2327 from comment-template
        public function comment_form($post_id = null, ...$args ):void{
            echo $this->_setup_comment_form($post_id, $args );
        }
    }
}else die;