<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-5-2022
 * Time: 10:38
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Comment\_comment_04;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\K_Ses\_k_ses_01;
use TP_Core\Traits\Templates\_comment_template_01;
use TP_Core\Traits\Templates\_comment_template_02;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_05;
use TP_Core\Traits\Templates\_link_template_04;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Pluggables\_pluggable_05;
use TP_Core\Traits\Formats\_formats_07;
if(ABSPATH){
    class TP_Walker_Comment extends TP_Walker{
        use _filter_01, _I10n_01, _k_ses_01, _formats_07, _comment_04, _comment_template_01;
        use _comment_template_02, _comment_template_03, _comment_template_05, _link_template_04;
        use _pluggable_05,_init_comment;
        private $__html;
        protected $_comment;//todo
        public $tree_type = 'comment';
        public $db_fields = ['parent' => 'comment_parent','id' => 'comment_ID',];
        public $comment_depth;
        public function start_lvl( &$output, $depth = 0, ...$args ):string {
            $GLOBALS['comment_depth'] = $depth + 1;
            switch ( $args['style'] ) {
                case 'div':
                    break;
                case 'ol':
                    $output .= "<ol class='children'>\n";
                    break;
                case 'ul':
                default:
                    $output .= "<ul class='children'>\n";
                    break;
            }
        }
        public function end_lvl( &$output, $depth = 0, ...$args ):string {
            $this->comment_depth = $depth + 1;
            switch ( $args['style'] ) {
                case 'div':
                    break;
                case 'ol':
                    $output .= "</ol><!-- .children -->\n";
                    break;
                case 'ul':
                default:
                    $output .= "</ul><!-- .children -->\n";
                    break;
            }
        }
        public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ):string  {
            if ( ! $element ) return;
            $id_field = $this->db_fields['id'];
            $id= $element->$id_field;
            parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
            if ( $max_depth <= $depth + 1 && isset( $children_elements[ $id ] ) ) {
                foreach ( $children_elements[ $id ] as $child )
                    $this->display_element( $child, $children_elements, $max_depth, $depth, $args, $output );
                unset( $children_elements[ $id ] );
            }
        }
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string  {
            $comment = $data_object;
            $depth++;
            $this->comment_depth = $depth;
            $this->_comment =  $comment;
            //if($this->__tp_comment instanceof TP_Comment);
                //$tp_comment = $this->__tp_comment;
            if ( ! empty( $args['callback'] ) ) {
                ob_start();
                call_user_func( $args['callback'], $comment, $args, $depth );
                $output .= ob_get_clean();
                return;
            }
            if ( 'comment' === $comment->comment_type )
                $this->_add_filter( 'comment_text', array( $this, 'filter_comment_text' ), 40, 2 );
            if ( ( 'pingback' === $comment->comment_type || 'trackback' === $comment->comment_type ) && $args['short_ping'] )
                $output .= $this->_ping( $comment, ...$args );
            $output .= $this->_get_comment( $comment, $depth, $args );
            if ( 'comment' === $comment->comment_type )
                $this->_remove_filter( 'comment_text', array( $this, 'filter_comment_text' ), 40 );
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):string  {
            if ( ! empty( $args['end-callback'] ) ) {
                ob_start();
                call_user_func(
                    $args['end-callback'],
                    $data_object, // The current comment object.
                    $args,$depth
                );
                $output .= ob_get_clean();
                return;
            }
            if ( 'div' === $args['style'] )
                $output .= "</div><!-- #comment-## -->\n";
            else $output .= "</li><!-- #comment-## -->\n";

        }
        protected function _ping( $comment, $args ):string  {
            $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
            $this->__html = "<$tag id='comment_{$this->_get_comment_ID()} {$this->_comment_class( '', $comment )}'>";
            $this->__html .= "<div class='comment-body'>";
            $this->__html .= "{$this->__( 'Pingback:' )} {$this->_get_comment_author_link( $comment )} ";
            $this->__html .= $this->_get_edit_comment_link_string($this->__( 'Edit' ),"<span class='edit-link'>","</span>");
            $this->__html .= "</div>";
            return $this->__html;
        }
        public function filter_comment_text( $comment_text, $comment ) {
            $commenter          = $this->_tp_get_current_commenter();
            $show_pending_links = ! empty( $commenter['comment_author'] );
            if ( $comment && '0' === $comment->comment_approved && ! $show_pending_links )
                $comment_text = $this->_tp_kses( $comment_text, [] );
            return $comment_text;
        }
        protected function _get_comment( $comment, $depth, $args ):string  {
            $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
            $commenter          = $this->_tp_get_current_commenter();
            $show_pending_links = ! empty( $commenter['comment_author'] );
            if ( $commenter['comment_author_email'] )
                $moderation_note = $this->__( 'Your comment is awaiting moderation.' );
            else $moderation_note = $this->__( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' );
            $this->__html = "<$tag id='comment_{$this->_get_comment_ID()}' {$this->_comment_class($this->has_children ? 'parent' : '', $comment)}>";
            $this->__html .= "<article id='article_comment_{$this->_get_comment_ID()}' class='comment-body'>";
            $this->__html .= "<footer class='comment-meta'>";
            $this->__html .= "<div class='comment-author vcard'>";
            if ( 0 !== $args['avatar_size'] )
                $this->__html .= $this->_get_avatar( $comment, $args['avatar_size'] );
            $comment_author = $this->_get_comment_author_link( $comment );
            if ( '0' === $comment->comment_approved && ! $show_pending_links )
                $comment_author = $this->_get_comment_author( $comment );
            ob_start();
            printf($this->__('%s'."<span class='says'>says:</span>"),
                sprintf("<b class='fn'>%s</b>",  $comment_author)
            );
            $this->__html .= ob_get_clean();
            $this->__html .= "</div><!-- .comment-author -->";
            $this->__html .= "<div class='comment-metadata'>";
            ob_start();
            printf(
                '<a href="%s"><time datetime="%s">%s</time></a>',
                $this->_esc_url( $this->_get_comment_link( $comment, $args ) ),
                $this->_get_comment_time( 'c' ),
                /* translators: 1: Comment date, 2: Comment time. */
                sprintf($this->__( '%1$s at %2$s' ),$this->_get_comment_date( '', $comment ), $this->_get_comment_time())
            );
            $this->_edit_comment_link( $this->__( '(Edit)' ), ' &nbsp;&nbsp;', '' );
            $this->__html .= ob_get_clean();
            $this->__html .= "</div>";
            if ( '0' === $comment->comment_approved )
                $this->__html .= "<em class='comment-awaiting-moderation'>$moderation_note;</em>";
            $this->__html .= "</footer>";
            $this->__html .= "<div class='comment-content'>{$this->_get_comment_text()}</div><!-- .comment-content -->";
            ob_start();
            if ( '1' === $comment->comment_approved || $show_pending_links ) {
                $this->comment_reply_link(
                    array_merge($args,[
                        'add_below' => 'div-comment','depth' => $depth,'max_depth' => $args['max_depth'],
                        'before' => "<div class='reply'>",'after' => '</div>',
                    ])
                );
            }
            $this->__html .=  ob_get_clean();
            $this->__html .= "</article><!-- .comment-body -->";
            return $this->__html;
        }
    }
}else die;