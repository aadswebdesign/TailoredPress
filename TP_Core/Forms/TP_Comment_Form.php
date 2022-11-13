<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-5-2022
 * Time: 16:28
 */
namespace TP_Core\Forms;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class TP_Comment_Form{
        use _action_01, _filter_01, _option_01;
        private $__html;
        private $__args;
        public function __construct(...$args){
            $this->__args = $args;
        }
        private function __to_string():string{
            ob_start();
            $this->__args['form_action_begin'];
            $this->_do_action('comment_form_top');
            $this->__html = ob_get_clean();
            if($this->__args['is_user_logged_in']){
                $this->__html .= $this->_apply_filters( 'comment_form_logged_in', $this->__args['logged_in_as'], $this->__args['commenter'], $this->__args['user_identity'] );
                $this->_do_action( 'comment_form_logged_in_after', $this->__args['commenter'], $this->__args['user_identity'] );
            } else $this->__html .= $this->__args['comment_notes_before'];
            $comment_fields = ['comment' => $this->__args['comment_field']] + (array) $this->__args['fields'];
            $comment_fields = $this->_apply_filters( 'comment_form_fields', $comment_fields );
            $comment_field_keys = array_diff( array_keys( $comment_fields ), ['comment']);
            $first_field = reset( $comment_field_keys );
            $last_field  = end( $comment_field_keys );
            foreach ( $comment_fields as $name => $field ) {
                if ( 'comment' === $name ) {
                    $this->__html .= $this->_apply_filters( 'comment_form_field_comment', $field );
                    $this->__html .= $this->__args['comment_notes_after'];
                }elseif(! $this->__args['is_user_logged_in']){
                    ob_start();
                    if ( $first_field === $name ) $this->_do_action( 'comment_form_before_fields' );
                    $this->__html .= ob_get_clean();
                    $this->__html .= $this->_apply_filters( "comment_form_field_{$name}", $field ) . "\n";
                    ob_start();
                    if ( $last_field === $name ) $this->_do_action( 'comment_form_after_fields' );
                    $this->__html .= ob_get_clean();
                }
            }
            ob_start();
            $this->__args['submit_button '];
            $submit_button = $this->_apply_filters( 'comment_form_submit_button', $this->__args['submit_button '], $this->__args );
            $submit_field = sprintf(
                $this->__args['submit_field'],
                $submit_button,
                $this->__args['comment_id_fields ']
            );
            $this->__html .= ob_get_clean();
            $this->__html .= $this->_apply_filters( 'comment_form_submit_field', $submit_field, $this->__args );
            $this->_do_action( 'comment_form', $this->__args['post_id'] );
            $this->__html .= "</form>";
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;