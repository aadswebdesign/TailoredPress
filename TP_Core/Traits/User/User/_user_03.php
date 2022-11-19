<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_user;
if(ABSPATH){
    trait _user_03 {
        use _init_db;
        use _init_user;
        /**
         * @description Retrieve user meta field for a user.
         * @param $user_id
         * @param string $key
         * @param bool $single
         * @return mixed
         */
        protected function _get_user_meta( $user_id, $key = '', $single = false ){
            return $this->_get_metadata( 'user', $user_id, $key, $single );
        }//1142
        /**
         * @description Update user meta field based on user ID.
         * @param $user_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return mixed
         */
        protected function _update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ){
            return $this->_update_metadata( 'user', $user_id, $meta_key, $meta_value, $prev_value );
        }//1168
        /**
         * @description Count number of users who have each of the user roles.
         * @param string $strategy
         * @param null $site_id
         * @return array
         */
        protected function _count_users( $strategy = 'time', $site_id = null ):array{
            $this->tpdb = $this->_init_db();
            if ( ! $site_id ) $site_id = $this->_get_current_blog_id();
            $pre = $this->_apply_filters( 'pre_count_users', null, $strategy, $site_id );
            if ( null !== $pre ) return $pre;
            $blog_prefix = $this->tpdb->get_blog_prefix( $site_id );
            $result      = [];
            if ( 'time' === $strategy ) {
                $roles = $this->_init_roles();
                if ( $this->_is_multisite() && $this->_get_current_blog_id() !== $site_id ) {
                    $this->_switch_to_blog( $site_id );
                    $avail_roles = $roles->get_names();
                    $this->_restore_current_blog();
                } else $avail_roles = $roles->get_names();
                $select_count = [];
                foreach ( $avail_roles as $this_role => $name )
                    $select_count[] = $this->tpdb->prepare( 'COUNT(NULLIF(`meta_value` LIKE %s, false))', '%' . $this->tpdb->esc_like( '"' . $this_role . '"' ) . '%' );
                $select_count[] = "COUNT(NULLIF(`meta_value` = 'a:0:{}', false))";
                $select_count   = implode( ', ', $select_count );
                $row = $this->tpdb->get_row(TP_SELECT . " {$select_count}, COUNT(*) FROM {$this->tpdb->user_meta} INNER JOIN {$this->tpdb->users} ON user_id = ID WHERE meta_key = '{$blog_prefix}capabilities'", ARRAY_N );
                $col         = 0;
                $role_counts = [];
                foreach ( $avail_roles as $this_role => $name ) {
                    $count = (int) $row[ $col++ ];
                    if ( $count > 0 ) $role_counts[ $this_role ] = $count;
                }
                $role_counts['none'] = (int) $row[ $col++ ];
                $total_users = (int) $row[ $col ];
                $result['total_users'] = $total_users;
                $result['avail_roles'] =& $role_counts;
            } else {
                $avail_roles = ['none' => 0,];
                $users_of_blog = $this->tpdb->get_col(TP_SELECT . " meta_value FROM {$this->tpdb->user_meta}	INNER JOIN {$this->tpdb->users} ON user_id = ID WHERE meta_key = '{$blog_prefix}capabilities'");
                foreach ( $users_of_blog as $caps_meta ) {
                    $b_roles = $this->_maybe_unserialize( $caps_meta );
                    if ( ! is_array( $b_roles ) ) continue;
                    if ( empty( $b_roles ) ) $avail_roles['none']++;
                    foreach ( $b_roles as $b_role => $val ) {
                        if ( isset( $avail_roles[ $b_role ] ) ) $avail_roles[ $b_role ]++;
                        else $avail_roles[ $b_role ] = 1;
                    }
                }
                $result['total_users'] = count( $users_of_blog );
                $result['avail_roles'] =& $avail_roles;
            }
            return $result;
        }//1196
        /**
         * @description Set up global user vars.
         * @param int $for_user_id
         */
        protected function _setup_user_data( $for_user_id = 0 ):void{
            if ( ! $for_user_id ) $for_user_id = $this->_get_current_user_id();
            $user = $this->_get_user_data( $for_user_id );
            if ( ! $user ) {
                $this->tp_user_ID       = 0;
                $this->tp_user_level    = 0;
                $this->tp_user_data     = null;
                $this->tp_user_login    = '';
                $this->tp_user_email    = '';
                $this->tp_user_url      = '';
                $this->tp_user_identity = '';
                return;
            }
            $this->tp_user_ID       = (int) $user->ID;
            $this->tp_user_level    = (int) $user->user_level;
            $this->tp_user_data     = $user;
            $this->tp_user_login    = $user->user_login;
            $this->tp_user_email    = $user->user_email;
            $this->tp_user_url      = $user->user_url;
            $this->tp_user_identity = $user->display_name;
        }//1329
        /**
         * @description Create dropdown HTML content of users.
         * @param array ...$args
         * @return mixed
         */
        protected function _tp_get_dropdown_users(...$args){
            $defaults = ['show_option_all' => '','show_option_none' => '','hide_if_only_one_author' => '',
                'orderby' => 'display_name','order' => 'ASC','include' => '','exclude' => '','multi' => 0,
                'show' => 'display_name','selected' => 0,'name' => 'user','class' => '','id' => '',
                'blog_id' => $this->_get_current_blog_id(),'who' => '','include_selected' => false,
                'option_none_value' => -1,'role' => '','role__in' => [],'role__not_in' => [],
                'capability' => '','capability__in' => [],'capability__not_in' => [],];
            $defaults['selected'] = $this->_is_author() ? $this->_get_query_var( 'author' ) : 0;
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $query_args = $this->_tp_array_slice_assoc($parsed_args,[
                'blog_id','include','exclude','orderby','order','who','role','role__in',
                'role__not_in','capability','capability__in','capability__not_in',
            ]);
            $fields = ['ID', 'user_login'];
            $show = ! empty( $parsed_args['show'] ) ? $parsed_args['show'] : 'display_name';
            if ( 'display_name_with_login' === $show ) $fields[] = 'display_name';
            else $fields[] = $show;
            $query_args['fields'] = $fields;
            $show_option_all   = $parsed_args['show_option_all'];
            $show_option_none  = $parsed_args['show_option_none'];
            $option_none_value = $parsed_args['option_none_value'];
            $query_args = $this->_apply_filters( 'tp_dropdown_users_args', $query_args, $parsed_args );
            $users = $this->_get_users( $query_args );
            $output = '';
            if ( ! empty( $users ) && ( empty( $parsed_args['hide_if_only_one_author'] ) || count( $users ) > 1 ) ) {
                $name = $this->_esc_attr( $parsed_args['name'] );
                if ( $parsed_args['multi'] && ! $parsed_args['id'] ) $id = '';
                else $id = $parsed_args['id'] ? " id='{$this->_esc_attr( $parsed_args['id'] )}'" : " id='$name'";
                $output = "<select name='{$name}' {$id} class='{$parsed_args['class']}'>\n";
                if ( $show_option_all ) $output .= "\t<option value='0'>$show_option_all</option>\n";
                if ( $show_option_none ) {
                    $_selected = $this->_get_selected( $option_none_value, $parsed_args['selected'] );
                    $output   .= "\t<option value='{$this->_esc_attr( $option_none_value )}' $_selected>$show_option_none</option>\n";
                }
                if ( $parsed_args['include_selected'] && ( $parsed_args['selected'] > 0 ) ) {
                    $found_selected          = false;
                    $parsed_args['selected'] = (int) $parsed_args['selected'];
                    foreach ( (array) $users as $user ) {
                        $user->ID = (int) $user->ID;
                        if ( $user->ID === $parsed_args['selected'] ) $found_selected = true;
                    }
                    if ( ! $found_selected ) {
                        $selected_user = $this->_get_user_data( $parsed_args['selected'] );
                        if ( $selected_user ) $users[] = $selected_user;
                    }
                }
                foreach ($users as $user ) {
                    if ( 'display_name_with_login' === $show )
                        $display = sprintf( $this->_x( '%1$s (%2$s)', 'user dropdown' ), $user->display_name, $user->user_login );
                    elseif ( ! empty( $user->$show ) ) $display = $user->$show;
                    else $display = '(' . $user->user_login . ')';
                    $_selected = $this->_get_selected( $user->ID, $parsed_args['selected'] );
                    $output   .= "\t<option value='$user->ID' $_selected >{$this->_esc_html( $display )}</option>\n";
                }
                $output .= '</select>';
            }
            return $this->_apply_filters( 'tp_dropdown_users', $output );
        }//1420
        protected function _tp_dropdown_users(...$args):void{
            echo $this->_tp_get_dropdown_users($args);
        }
        /**
         * @description Sanitize user field based on context.
         * @param $field
         * @param $value
         * @param $user_id
         * @param $context
         * @return int
         */
        protected function _sanitize_user_field( $field, $value, $user_id, $context ):int{
            $int_fields = array( 'ID' );
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            if ( 'raw' === $context ) return $value;
            if ( ! is_string( $value ) && ! is_numeric( $value ) ) return $value;
            $prefixed = false !== strpos( $field, 'user_' );
            if ( 'edit' === $context ) {
                if ( $prefixed ) $value = $this->_apply_filters( "edit_{$field}", $value, $user_id );
                else $value = $this->_apply_filters( "edit_user_{$field}", $value, $user_id );
                if ( 'description' === $field ) $value = $this->_esc_html( $value ); // textarea_escaped?
                else $value = $this->_esc_attr( $value );
            } elseif ( 'db' === $context ) {
                if ( $prefixed ) $value = $this->_apply_filters( "pre_{$field}", $value );
                else $value = $this->_apply_filters( "pre_user_{$field}", $value );
            } else if ( $prefixed ) $value = $this->_apply_filters( "{string $field}", $value, $user_id, $context );
            else $value = $this->_apply_filters( "user_{$field}", $value, $user_id, $context );
            if ( 'user_url' === $field ) $value = $this->_esc_url( $value );
            if ( 'attribute' === $context ) $value = $this->_esc_attr( $value );
            elseif ( 'js' === $context ) $value = $this->_esc_js( $value );
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            return $value;
        }//1583
        /**
         * @description Update all user caches
         * @param $user
         */
        protected function _update_user_caches( $user ):void{
            if ( $user instanceof TP_User ) {
                if ( ! $user->exists() ) return;
                $user = $user->data;
            }
            $this->_tp_cache_add( $user->ID, $user, 'users' );
            $this->_tp_cache_add( $user->user_login, $user->ID, 'userlogins' );
            $this->_tp_cache_add( $user->user_email, $user->ID, 'useremail' );
            $this->_tp_cache_add( $user->user_nicename, $user->ID, 'userslugs' );
        }//1693
        /**
         * @description Clean all user caches
         * @param $user
         */
        protected function _clean_user_cache( $user ):void{
            if ( is_numeric( $user ) ) $user = new TP_User( $user );
            if ( ! $user->exists() ) return;
            $this->_tp_cache_delete( $user->ID, 'users' );
            $this->_tp_cache_delete( $user->user_login, 'userlogins' );
            $this->_tp_cache_delete( $user->user_email, 'useremail' );
            $this->_tp_cache_delete( $user->user_nicename, 'userslugs' );
            $this->_do_action( 'clean_user_cache', $user->ID, $user );
            if ( $this->_get_current_user_id() === (int) $user->ID ) {
                $user_id      = (int) $user->ID;
                $this->tp_current_user = null;
                $this->_tp_set_current_user( $user_id, '' );
            }
        }//1719
        /**
         * @description Determines whether the given username exists.
         * @param $username
         * @return mixed
         */
        protected function _username_exists( $username ){
            $user = $this->_get_user_by( 'login', $username );
            if ( $user ) $user_id = $user->ID;
            else $user_id = false;
            return $this->_apply_filters( 'username_exists', $user_id, $username );
        }//1765
        /**
         * @description Determines whether the given email exists.
         * @param $email
         * @return mixed
         */
        protected function _email_exists( $email ){
            $user = $this->_get_user_by( 'email', $email );
            if ( $user ) $user_id = $user->ID;
            else  $user_id = false;
            return $this->_apply_filters( 'email_exists', $user_id, $email );
        }//1797
    }
}else die;