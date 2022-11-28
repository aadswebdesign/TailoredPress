<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Post_Comments extends Adm_Partials  {
        protected function _get_block_info():string {
            return [['author'  => $this->__( 'Author' ),'comment' => $this->_x( 'Comment', 'column name' ),],
                [],[],'comment',];
        }//23
        protected function _get_classes():array {
            $classes   = parent::_get_classes();
            $classes[] = 'tp-list-block';
            $classes[] = 'comments-box';
            return $classes;
        }//38
        public function get_display( $output_empty = false ):string{
            $singular = $this->_args['singular'];
            $imploded_classes = implode( ' ', $this->_get_classes() );
            $data_list = null;
            if ( $singular ) { $data_list = " data-tp_lists='list:$singular'";}
            $data_blocks = null;
            if ( ! $output_empty ) {$data_blocks = $this->get_display_placeholder();}
            $output  = "<div class='adm-segment post-comments-display'><ul class='$imploded_classes'><li>";
            $output .= $this->_tp_get_nonce_field( 'fetch-list-' . get_class( $this ), '_async_fetch_list_nonce' );
            $output .= "</li><li id='the_comment_list' $data_list>$data_blocks</li></ul></div><!-- adm-segment post-comments-display -->";
            return $output;
        }//48
        public function get_per_page( $comment_status = false ):int {
            if($comment_status === true)
                return 10;
        }//75
    }
}else{die;}