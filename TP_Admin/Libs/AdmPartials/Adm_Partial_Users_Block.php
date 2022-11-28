<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Queries\TP_User_Query;
if(ABSPATH){
    class Adm_Partial_Users_Block extends Adm_Partials  {
        public $site_id;
        public $is_site_users;
        public function __construct( $args = array() ) {
            parent::__construct();
        }//45
        public function async_user_can() {
            if ( $this->is_site_users ) { return $this->_current_user_can( 'manage_sites' );}
            return $this->_current_user_can( 'list_users' );
        }//68
        public function prepare_items() {
            $this->tp_user_search = isset( $_REQUEST['s'] ) ? $this->_tp_unslash( trim( $_REQUEST['s'] ) ) : '';
            $this->tp_role = $_REQUEST['role'] ?? '';
            $per_page       = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';
            $users_per_page = $this->_get_items_per_page( $per_page );
            $paged = $this->get_pagenum();
            if ( 'none' === $this->tp_role ) {
                $args = ['number' => $users_per_page,'offset' => ( $paged - 1 ) * $users_per_page,'include' => $this->_tp_get_users_with_no_role( $this->site_id ),
                    'search' => $this->tp_user_search,'fields' => 'all_with_meta',];
            } else {
                $args = ['number' => $users_per_page,'offset' => ( $paged - 1 ) * $users_per_page,'role' => $this->tp_role,
                    'search' => $this->tp_user_search,'fields' => 'all_with_meta',];
            }
            if ( '' !== $args['search'] ) { $args['search'] = '*' . $args['search'] . '*';}
            if ( $this->is_site_users ) { $args['blog_id'] = $this->site_id;}
            if ( isset( $_REQUEST['orderby'] ) ) { $args['orderby'] = $_REQUEST['orderby'];}
            if ( isset( $_REQUEST['order'] ) ) { $args['order'] = $_REQUEST['order'];}
            $args = $this->_apply_filters( 'users_list_table_query_args', $args );
            $this->tp_user_search = new TP_User_Query( $args );
            $this->items = $this->tp_user_search->get_results();
            $this->_set_pagination_args(['total_items' => $this->tp_user_search->get_total(),'per_page' => $users_per_page,]);
        }//84
        public function get_no_items() {
            return $this->__('No users found.');
        }//158
        protected function _get_views():array{
            $tp_roles = $this->_tp_roles();
            if ( $this->is_site_users ) {
                $url = 'site_users.php?id=' . $this->site_id;
                $this->_switch_to_blog( $this->site_id );
                $users_of_blog = $this->_count_users( 'time', $this->site_id );
                $this->_restore_current_blog();
            } else {
                $url = 'users.php';
                $users_of_blog = $this->_count_users();
            }
            $total_users = $users_of_blog['total_users'];
            $avail_roles =& $users_of_blog['avail_roles'];
            unset( $users_of_blog );
            $current_link_attributes = empty( $this->tp_role ) ? ' class="current" aria-current="page"' : '';
            $role_links        = [];
            $role_links['all'] = sprintf("<a href='%s' %s>%s</a>",$url,$current_link_attributes,sprintf($this->_nx("All<span class='count'>('%s')</span>","All<span class='count'>('%s')</span>",$total_users,'users'),$this->_number_format_i18n( $total_users )));
            foreach ( $tp_roles->get_names() as $this_role => $name ) {
                if ( ! isset( $avail_roles[ $this_role ] ) ) { continue;}
                $current_link_attributes = '';
                if ( $this_role === $this->tp_role ) { $current_link_attributes = " class='current' aria-current='page'";}
                $name = $this->_translate_user_role( $name );
                $name = sprintf($this->__("%1\$s <span class='count'>(%2\$s)</span>"), $name, $this->_number_format_i18n( $avail_roles[ $this_role ] ) );
                $role_links[ $this_role ] = "<a href='{$this->_esc_url( $this->_add_query_arg( 'role', $this_role, $url ) )}' $current_link_attributes>$name</a>";
            }
            if ( ! empty( $avail_roles['none'] ) ) {
                $current_link_attributes = '';
                if ( 'none' === $this->tp_role ) { $current_link_attributes = " class='current' aria-current='page'";}
                $name = $this->__( 'No role' );
                $name = sprintf( $this->__("%1\$s <span class='count'>(%2\$s)</span>"), $name, $this->_number_format_i18n( $avail_roles['none'] ));
                $role_links['none'] = "<a href='" . $this->_esc_url( $this->_add_query_arg( 'role', 'none', $url ) ) . "'$current_link_attributes>$name</a>";
            }
            return $role_links;
        }//175
        protected function _get_multi_bulk_actions( $which = ''){
            $actions = [];
            if ( $this->_is_multisite() ) {
                if ( $this->_current_user_can( 'remove_users' ) ) { $actions['remove'] = $this->__( 'Remove' );}
            } else if ( $this->_current_user_can( 'delete_users' ) ){ $actions['delete'] = $this->__( 'Delete' );}
            if ( $this->_current_user_can( 'edit_users' ) ) {  $actions['resetpassword'] = $this->__( 'Send password reset' );}
            return $actions;
        }//264
        protected function _get_extra_nav_block( $which ):string{
            $id        = 'bottom' === $which ? 'new_role2' : 'new_role';
            $button_id = 'bottom' === $which ? 'changeit2' : 'changeit';
            $output  = "<div class='block-left'><ul><li>";
            if ( $this->_current_user_can( 'promote_users' ) && $this->has_items() ){
                $output .= "<dt><label for='$id' class='screen-reader-text'>{$this->__('Change role to&hellip;')}</label></dt>";
                $output .= "<dd><select id='$id' name='$id' >";
                $output .= "<option value=''>{$this->__('Change role to&hellip;')}</option>";
                $output .= $this->_tp_get_dropdown_roles();
                $output .= "<option value='none'>{$this->__('&mdash; No role for this site &mdash;')}</option>";
                $output .= "</select></dd></li><li>";
                $output .= $this->_get_submit_button( $this->__( 'Change' ), '', $button_id, false );
                $output .= "</li><li>";
            }
            $output .= $this->_get_action( 'restrict_manage_users', $which );
            $output .= "</li></ul></div>";
            $output .= $this->_get_action( 'manage_users_extra_tablenav', $which );
            $output .= "";
            return $output;
        }//239
        public function get_current_action():string {
            if ( isset( $_REQUEST['changeit'] ) && ! empty( $_REQUEST['new_role'] ) ) {
                return 'promote';
            }
            return parent::get_current_action();
        }//343
        public function get_blocks() {
            $column = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => "<dt>",'username' => $this->__( 'Username' ),'name' => $this->__( 'Name' ),
                'email' => $this->__( 'Email' ),'role' => $this->__( 'Role' ),'posts' => $this->_x( 'Posts', 'post type general name' ),'dt_close' => "</dt>",];
            if ( $this->is_site_users ) { unset( $column['posts'] );}
            return $column;
        }//358
        protected function _get_sortable_blocks():array{
            return ['username' => 'login','email' => 'email',];
        }//382
        public function get_display_blocks():string{
            $output  = "<p>todo, needs db first</p>";
            if ( ! $this->is_site_users ) {
                $post_counts = $this->_count_many_users_posts( array_keys( (array)$this->items ) );
            }
            foreach ( (array)$this->items as $userid => $user_object ) {
                $output .= "\n\t". $this->get_single_block( $user_object, '', '', isset( $post_counts ) ? $post_counts[ $userid ] : 0 );
            }
            return $output;
        }//396
        public function get_single_block( $user_object, $style = '', $role = '', $num_posts = 0 ):string{
            if ( ! ( $user_object instanceof TP_User ) ) {
                $user_object = $this->_get_user_data( (int) $user_object );
            }
            $user_object->filter = 'display';
            $email = $user_object->user_email;
            if ( $this->is_site_users ) { $url = "site_users.php?id={$this->site_id}&amp;";}
            else { $url = 'users.php?';}
            $user_roles = $this->_get_role_block( $user_object );
            $actions     = [];
            $checkbox    = '';
            $super_admin = '';
            if ($this->_is_multisite() && $this->_current_user_can('manage_network_users') && in_array($user_object->user_login, $this->_get_super_admins(), true)) {
                $super_admin = "&mdash;{$this->__( 'Super Admin' )}";
            }
            if ( $this->_current_user_can( 'list_users' ) ) {
                $edit_link = $this->_esc_url( $this->_add_query_arg( 'tp_http_referer', urlencode( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->_get_edit_user_link( $user_object->ID ) ) );
                if ( $this->_current_user_can( 'edit_user', $user_object->ID ) ) {
                    $edit            = "<dd><strong><a href='{$edit_link}'>{$user_object->user_login}</a>{$super_admin}</strong></dd>";
                    $actions['edit'] = "<dd><a href='$edit_link'>{$this->__( 'Edit' )}</a></dd>";
                } else { $edit = "<dd><strong>{$user_object->user_login}{$super_admin}</strong></dd>";}
                if ( ! $this->_is_multisite() && $this->_get_current_user_id() !== $user_object->ID && $this->_current_user_can( 'delete_user', $user_object->ID ) ) {
                    $actions['delete'] = "<dd><a class='submit-delete' href='{$this->_tp_nonce_url( "users.php?action=delete&amp;user=$user_object->ID", 'bulk-users' )}'>{$this->__( 'Delete' )}</a></dd>";
                }
                if ( $this->_is_multisite() && $this->_current_user_can( 'remove_user', $user_object->ID ) ) {
                    $actions['remove'] = "<dd><a class='submit-delete' href='{$this->_tp_nonce_url( $url . "action=remove&amp;user=$user_object->ID", 'bulk-users' )}'>{$this->__( 'Remove' )}</a></dd>";
                }
                $author_posts_url = $this->_get_author_posts_url( $user_object->ID );
                if ( $author_posts_url ) {
                    $actions['view'] = sprintf("<dd><a href='%s' aria-label='%s'>%s</a></dd>", $this->_esc_url( $author_posts_url ), $this->_esc_attr( sprintf( $this->__( 'View posts by %s'), $user_object->display_name )), $this->__('View'));
                }
                if ( $this->_get_current_user_id() !== $user_object->ID && $this->_current_user_can( 'edit_user', $user_object->ID ) ) {
                    $actions['reset-password'] = "<dd><a class='reset-password' href='{$this->_tp_nonce_url( "users.php?action=reset-password&amp;users=$user_object->ID", 'bulk-users' )}'>{$this->__( 'Send password reset' )}</a></dd>";
                }
                $actions = $this->_apply_filters( 'user_row_actions', $actions, $user_object );
                $role_classes = $this->_esc_attr( implode( ' ', array_keys( $user_roles ) ) );
                $checkbox = sprintf("<dt class='check-column'><label for='user_%1\$s' class='screen-reader-text'>%2\$s</label></dt><dd><input id='user_%1\$s' name='users[]' class='%3\$s' type='checkbox' value='%1\$s'/></dd>",
                    $user_object->ID,sprintf( $this->__( 'Select %s' ), $user_object->user_login ),$role_classes);
            } else { $edit = "<strong>{$user_object->user_login}{$super_admin}</strong>";}
            $avatar = $this->_get_avatar( $user_object->ID, 32 );
            $roles_list = implode( ', ', $user_roles );
            $row = "<ul id='user-$user_object->ID'>";
            @list( $columns, $hidden, $primary ) = $this->_get_block_info();//todo not used , $sortable
            foreach ( $columns as $column_name => $column_display_name ) {
                $classes = "$column_name column-$column_name";
                if ( $primary === $column_name ) { $classes .= ' has-row-actions column-primary';}
                if ( 'posts' === $column_name ) { $classes .= ' num'; }
                if ( in_array( $column_name, $hidden, true )){ $classes .= ' hidden';}
                $data = "data-col_name='{$this->_esc_attr($this->_tp_strip_all_tags( $column_display_name ))}'";
                $attributes = "class='$classes' $data";
                if ( 'cb' === $column_name ) {
                    $row .= "<li class='check-column'>$checkbox</li>";
                } else {
                    $row .= "<li $attributes>";
                    switch ( $column_name ) {
                        case 'username':
                            $row .= "$avatar $edit";
                            break;
                        case 'name':
                            if ( $user_object->first_name && $user_object->last_name ) {
                                $row .= "$user_object->first_name $user_object->last_name";
                            } elseif ( $user_object->first_name ) {
                                $row .= $user_object->first_name;
                            } elseif ( $user_object->last_name ) {
                                $row .= $user_object->last_name;
                            } else {
                                $row .=  sprintf("<span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>%s</span>",$this->_x( 'Unknown', 'name' ));
                            }
                            break;
                        case 'email':
                            $row .= "<dd><a href='{$this->_esc_url( "mailto:$email" )}'>$email</a></dd>";
                            break;
                        case 'role':
                            $row .= $this->_esc_html( $roles_list );
                            break;
                        case 'posts':
                            if ( $num_posts > 0 ) {
                                $row .= sprintf("<dd><a href='%s' class='edit'><span aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></a></dd>",
                                    "edit.php?author={$user_object->ID}",$num_posts,sprintf($this->_n( '%s post by this author', '%s posts by this author', $num_posts ),$this->_number_format_i18n( $num_posts )));
                            } else {
                                $row .= 0;
                            }
                            break;
                        default:
                            $row .= $this->_apply_filters( 'manage_users_custom_column', '', $column_name, $user_object->ID );
                    }
                    if ( $primary === $column_name ) {
                        $row .= $this->_get_actions( $actions );
                    }
                    $row .= '</li>';
                }
            }
            $row .= "</ul>";
            return $row;
        }//421
        protected function _get_default_primary_name():string{
            return 'username';
        }//613
        protected function _get_role_block( $user_object ):string{
            $tp_roles =  $this->_tp_roles();
            $role_list = [];
            foreach ( $user_object->roles as $role ) {
                if ( isset( $tp_roles->role_names[ $role ] ) ) {
                    $role_list[ $role ] = $this->_translate_user_role( $tp_roles->role_names[ $role ] );
                }
            }
            if ( empty( $role_list ) ) { $role_list['none'] = $this->_x( 'None', 'no user roles' );}
            return $this->_apply_filters( 'get_role_list', $role_list, $user_object );
        }
    }
}else{die;}