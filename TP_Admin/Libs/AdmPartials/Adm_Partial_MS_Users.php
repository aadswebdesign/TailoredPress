<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 12:51
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\Queries\TP_User_Query;
if(ABSPATH){
    class Adm_Partial_MS_Users extends Adm_Partials{
        public function async_user_can() {
            return $this->_current_user_can( 'manage_network_themes' );
        }//22
        public function prepare_items():void{
            if ( ! empty( $_REQUEST['mode'] ) ) {
                $this->tp_mode = 'excerpt' === $_REQUEST['mode'] ? 'excerpt' : 'list';
                $this->_set_user_setting( 'network_users_list_mode', $this->tp_mode );
            } else {
                $this->tp_mode = $this->_get_user_setting( 'network_users_list_mode', 'list' );
            }
            $this->tp_user_search = isset( $_REQUEST['s'] ) ? $this->_tp_unslash( trim( $_REQUEST['s'] ) ) : '';
            $users_per_page = $this->_get_items_per_page( 'users_network_per_page' );
            $this->tp_role = $_REQUEST['role'] ?? '';
            $paged = $this->get_pagenum();
            $args = ['number' => $users_per_page,'offset' => ( $paged - 1 ) * $users_per_page,
                'search' => $this->tp_user_search,'blog_id' => 0,'fields' => 'all_with_meta',];
            if ( $this->_tp_is_large_network( 'users' ) ) $args['search'] = ltrim( $args['search'], '*' );
            elseif ( '' !== $args['search'] ) {
                $args['search'] = trim( $args['search'], '*' );
                $args['search'] = '*' . $args['search'] . '*';
            }
            if ( 'super' === $this->tp_role ) { $args['login__in'] = $this->_get_super_admins();}
            if ( ! $this->tp_user_search && $this->_tp_is_large_network( 'users' ) ) {
                if ( ! isset( $_REQUEST['orderby'] ) ) {
                    $_GET['orderby']     = 'id';
                    $_REQUEST['orderby'] = 'id';
                }
                if ( ! isset( $_REQUEST['order'] ) ) {
                    $_GET['order']     = 'DESC';
                    $_REQUEST['order'] = 'DESC';
                }
                $args['count_total'] = false;
            }
            if ( isset( $_REQUEST['orderby'] ) ) {$args['orderby'] = $_REQUEST['orderby'];}
            if ( isset( $_REQUEST['order'] ) ) { $args['order'] = $_REQUEST['order'];}
            $args = $this->_apply_filters( 'users_list_query_args', $args );
            $tp_user_search = new TP_User_Query( $args );
            $this->items = $tp_user_search->get_results();
            $this->_set_pagination_args(['total_items' => $tp_user_search->get_total(),'per_page' => $users_per_page,]);
        }//31
        protected function _get_bulk_actions():array{
            $actions = [];
            if($this->_current_user_can('delete_users')){ $actions['delete'] = $this->__( 'Delete' );}
            $actions['spam']    = $this->_x( 'Mark as spam', 'user' );
            $actions['not_spam'] = $this->_x( 'Not spam', 'user' );
            return $actions;
        }//112
        public function get_no_items(){
            return $this->__('No users found.');
        }//125
        protected function _get_views():array{
            $total_users  = $this->_get_user_count();
            $super_admins = $this->_get_super_admins();
            $total_admins = count( $super_admins );
            $current_link_attributes = 'super' !== $this->tp_role ? " class='current' aria-current='page' " : '';
            $role_links = [];
            $role_links['all'] = sprintf("<dd><a href='%s' %s>%s</a></dd>",$this->_network_admin_url( 'users.php' ),
                $current_link_attributes,sprintf($this->_nx("<dt>All <span class='count'>(%s)</span></dt>","<dt>All <span class='count'>(%s)</span></dt>",$total_users,'users'),
                    $this->_number_format_i18n( $total_users )));
            $current_link_attributes = 'super' === $this->_tp_role ? " class='current' aria-current='page'" : '';
            $role_links['super'] = sprintf("<dd><a href='%s' %s>%s</a></dd>",$this->_network_admin_url('users.php?role=super'),$current_link_attributes,
                sprintf($this->_n("<dt>Super Admin <span class='count'>(%s)</span></dt>","<dt>Super Admin <span class='count'>(%s)</span></dt>",$total_admins),$this->_number_format_i18n( $total_admins  )));
            return $role_links;
        }//133
        protected function _get_pagination( $which ):string{
            parent::_get_pagination( $which );
            if ( 'top' === $which ) { $this->_get_view_switcher( $this->tp_mode );}
        }//181
        public function get_blocks(){
            $users_blocks = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => '<dt>','username' => $this->__( 'Username' ),
                'name' => $this->__( 'Name' ),'email' => $this->__( 'Email' ),
                'registered' => $this->_x( 'Registered', 'user' ),'blogs' => $this->__( 'Sites' ),'dt_close' => '</dt>',];
            return $this->_apply_filters( 'tp_mu_users_blocks', $users_blocks );
        }//194
        protected function _get_sortable_blocks():array{
            return ['username' => 'login','name' => 'name','email' => 'email','registered' => 'id',];
        }//217
        public function _get_cb_block( $item ):string{
            $user = $item;
            if ($this->_is_super_admin( $user->ID )){ return false;}
            $output  = "<dd><input name='all_users[]' id='blog_{$user->ID}' type='checkbox' value='{$this->_esc_attr( $user->ID)}' /></dd>";
            $output .= "<dt><label for='blog_{$user->ID}' class='screen-reader-text'>";
            $output .= sprintf($this->__('Select: %s'), $user->user_login);
            $output .= "</label></dt>";
            return $output;
        }//234
        public function get_block_id( $user ):string {
            return $user->ID;
        }//259
        public function get_block_username($user ):string{
            $super_admins = $this->_get_super_admins();
            $output = $this->_get_avatar( $user->user_email, 32 );
            if ( $this->_current_user_can( 'edit_user', $user->ID ) ) {
                $edit_link = $this->_esc_url( $this->_add_query_arg( 'tp_http_referer', urlencode( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->_get_edit_user_link( $user->ID ) ) );
                $edit      = "<dd><a href='{$edit_link}'>{$user->user_login}</a></dd>";
            } else { $edit = $user->user_login;}
            $super_login = null;
            if ( in_array( $user->user_login, $super_admins, true ) ){
                $super_login = " &mdash; {$this->__('Super Admin')}";
            }
            $output .= "<dt><strong>{$edit}:{$super_login}</strong></dt>";
            return $output;
        }//270
        public function get_block_name($user ):string{
            $output  = "";
            if ( $user->first_name && $user->last_name ) { $output .= "$user->first_name $user->last_name";}
            elseif ( $user->first_name ){$output .= $user->first_name;}
            elseif ($user->last_name ) { $output .= $user->last_name;}
            else{ $output .= "<dt><span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>{$this->_x('Unknown', 'name')}</span></dt>";}
            return $output;
        }//303
        public function get_block_email( $user ):string{
            return "<dd><a href='{$this->_esc_url('mailto:$user->user_email')}'>{$user->user_email}</a></dd>";
        }//322
        public function get_block_registered( $user ):string{
            if ( 'list' === $this->tp_mode ) { $date = $this->__('<dt>Y/m/d</dt>');}
            else {$date = $this->__('<dt>Y/m/d g:i:s a</dt>');}
            return $this->_mysql2date( $date, $user->user_registered );
        }//335
        protected function _get_blogs( $user, $classes ='', $data='', $primary=true ):string{
            $output  = "<li class='wrapper $classes has-block-actions, $data'>";
            $output .= $this->_get_blogs( $user );
            $output .= $this->_get_handle_block_actions( $user, 'blogs', $primary );
            $output .= "</li><!-- wrapper has-block-actions -->";
            return $output;
        }//353
        public function get_user_blogs( ):string{// $user
            $output  = "";
            $output .= "<p>todo</p>";
            $output .= "";
            return $output;
        }//367 todo
        public function get_block_default( $item, $block_name ):string{
            return $this->_apply_filters( 'manage_users_custom_block','', $block_name,$item->ID );
        }//454
        public function get_display_rows():string {
            $output  = "<p>todo</p>";
            foreach ((array) $this->items as $user ) {
                $class = '';
                $status_list = ['spam' => 'site-spammed', 'deleted' => 'site-deleted',];
                foreach ($status_list as $status => $block ) {
                    if ( $user->$status ) { $class .= " $block";}
                }
                $row_class = trim( $class );
                $output .= "<li class='wrapper display-rows $row_class'>{$this->_get_single_blocks( $user )}</li><!-- wrapper display-rows -->";
            }
            return $output;
        }//464
        protected function _get_default_primary_name():string{
            return 'username';
        }//494
        protected function _get_handle_block_actions( $item, $block_name, $primary ):string{
            if ( $primary !== $block_name ) { return '';}
            $user = $item;
            $super_admins = $this->_get_super_admins();
            $actions = [];
            if ( $this->_current_user_can( 'edit_user', $user->ID ) ) {
                $edit_link       = $this->_esc_url( $this->_add_query_arg( 'tp_http_referer', urlencode( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->_get_edit_user_link( $user->ID ) ) );
                $actions['edit'] = "<dd><a href='$edit_link'>{$this->__( 'Edit' )}</a></dd>";
            }
            if ( $this->_current_user_can( 'delete_user', $user->ID ) && ! in_array( $user->user_login, $super_admins, true ) ) {
                $action_string = $this->_esc_url( $this->_network_admin_url($this->_add_query_arg('_tp_http_referer',urlencode( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->_tp_nonce_url( 'users.php', 'delete_user' ) . '&amp;action=delete_user&amp;id=' . $user->ID )));
                $actions['delete'] = "<dd><a href='{$action_string}' class='delete'>{$this->__( 'Delete' )}</a></dd>";
            }
            $actions = $this->_apply_filters( 'ms_user_row_actions', $actions, $user );
            return $this->_get_actions( $actions );
        }//510
    }
}else{die;}