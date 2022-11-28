<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-9-2022
 * Time: 16:30
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Links extends Adm_Partials{
        public function __construct( ...$args){
            parent::__construct(['plural' => 'bookmarks','screen' => $args['screen'] ?? null,]);
        }//29
        public function async_user_can() {
            return $this->_current_user_can( 'manage_links' );
        }//41
        public function prepare_items(){
            $this->_tp_reset_vars(['action', 'cat_id', 'link_id', 'orderby', 'order', 's']);
            $args = ['hide_invisible' => 0,'hide_empty' => 0,];
            if ( 'all' !== $this->tp_cat_id ) {$args['category'] = $this->tp_cat_id;}
            if ( ! empty( $this->tp_search ) ) {$args['search'] = $this->tp_search;}
            if ( ! empty( $this->tp_orderby ) ) {$args['orderby'] = $this->tp_orderby;}
            if ( ! empty( $this->tp_order ) ) {$args['order'] = $this->tp_order;}
            $this->items = $this->_get_bookmarks( $args );
        }//51
        public function get_no_items():string {
            return 'No links found.';
        }//79
        protected function _get_bulk_actions():array {
            $actions           = [];
            $actions['delete'] = $this->__( 'Delete' );
            return $actions;
        }//86
        protected function _get_extra_nav_block( $which ){
            if ('top'!== $which ){return;}
            $dropdown_options = ['selected' => $this->tp_cat_id,'name' => 'cat_id',
                'taxonomy' => 'link_category','show_option_all' => $this->_get_taxonomy( 'link_category' )->labels->all_items,
                'hide_empty' => true,'hierarchical' => 1,'show_count' => 0,'orderby' => 'name',];
            $output  = "<div class='adm-segment extra-nav actions'><ul><li>";
            $output .= "<dt><label for='cat_id' class='screen-reader-text'>{$this->_get_taxonomy( 'link_category' )->labels->filter_by_item}</label></dt>";
            $output .= "<dd>{$this->_tp_get_dropdown_categories( $dropdown_options )}</dd>";
            $output .= "</li><li><dd>";
            $output .= $this->_get_submit_button( $this->__( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
            $output .= "</dd></li></ul></div><!-- adm-segment extra-nav -->";
            return $output;
        }//97
        public function get_blocks(){
            return ['cb' => "<input type='checkbox' />",
                'name' => $this->_x('Name','link name'),
                'url' => $this->__('URL'),
                'categories' => $this->__('Categories'),
                'rel' => $this->__('Relationship'),
                'visible' => $this->__('Visible'),
                'rating' => $this->__('Rating'),];
        }//130
        protected function _get_sortable_blocks():array{
            return ['name' => 'name', 'url' => 'url', 'visible' => 'visible', 'rating' => 'rating',];
        }//145
        protected function _get_default_primary_name():string {
            return 'name';
        }//161
        protected function _get_cb_block( $item ):string{
            $link = $item;
            $output  = "<div class='adm-segment block-cb'><ul><li>";
            $output .= "<dt><label for='cb_select_{$link->link_id}' class='screen-reader-text'>";
            $output .= sprintf($this->__('Select %s'), $link->link_name);
            $output .= "</label></dt><dd>";
            $output .= "<input id='cb_select_{$link->link_id}' name='link_check[]' type='checkbox' value='{$this->_esc_attr( $link->link_id )}' /></dd>";
            $output .= "</li></ul></div><!-- adm-segment  block-cb -->";
            return $output;
        }//173
        public function _get_primary_name( $link ):string{
            $edit_link = $this->_get_edit_bookmark_link( $link );
            return sprintf($this->__("<dd><strong><a href='%s' aria-label='%s' class='row-title'>%s</a></strong></dd>").$edit_link,
                $this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $link->link_name ) ),$link->link_name);
        }//195
        public function get_block_url( $link ):string{
            $short_url = $this->_url_shorten( $link->link_url );
            return "<dd><a href='$link->link_url'>$short_url</a></dd>";
        }//213
        public function get_block_categories( $link ):string{
            $cat_names = [];
            foreach ((array) $link->link_category as $category ) {
                $cat = $this->_get_term( $category, 'link_category', OBJECT, 'display' );
                if ( $this->_init_error( $cat ) ) {
                    return $cat->get_error_message();
                }
                $cat_name = $cat->name;
                if ( (int) $this->tp_cat_id !== $category ) {
                    $cat_name = "<dd><a href='link-manager.php?cat_id=$category'>$cat_name</a></dd>";
                }
                $cat_names[] = $cat_name;
            }
            return implode( ', ', $cat_names );
        }//227
        public function get_block_rel( $link ):string{
            return empty( $link->link_rel ) ? '<br />' : $link->link_rel;
        }//252
        public function get_block_visible( $link ):bool{
            $output  = "";
            if ( 'Y' === $link->link_visible ) {
                $output .= $this->__('Yes');
            }else{$output .= $this->__('No');}
            return $output;
        }//263
        public function get_block_rating( $link ):string {
            return $link->link_rating;
        }//278
        public function get_column_default( $item, $column_name ):string {
            return $this->_do_action( 'manage_link_custom_column', $column_name, $item->link_id );
        }//291
        public function get_display_blocks():string{
            $output  = "";
            foreach ((array) $this->items as $link ) {
                $link                = $this->_sanitize_bookmark( $link );
                $link->link_name     = $this->_esc_attr( $link->link_name );
                $link->link_category = $this->_tp_get_link_cats( $link->link_id );
                $output .= $this->_get_single_blocks( $link );
            }
            return $output;
        }//303
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            if ( $primary !== $column_name ) { return '';}
            $link      = $item;
            $edit_link = $this->_get_edit_bookmark_link( $link );
            $actions           = [];
            $actions['edit']   = "<dd><a href='$edit_link'>{$this->__( 'Edit' )}</a></dd>";
            $actions['delete'] = sprintf(
                "<dd><a class='submit-delete' href='%s' onclick='" . 'return window.confirm("%s")' ."' >%s</a></dd>",
                $this->_tp_nonce_url( "link.php?action=delete&amp;link_id=$link->link_id", 'delete-bookmark_' . $link->link_id ),
                $this->_esc_js( sprintf( $this->__( "You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete." ), $link->link_name ) ),
                $this->__( 'Delete' )
            );
            return $this->_get_actions( $actions );
        }//328
    }
}else{die;}