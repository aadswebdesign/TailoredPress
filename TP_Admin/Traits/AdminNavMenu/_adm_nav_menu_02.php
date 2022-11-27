<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 19:11
 */
namespace TP_Admin\Traits\AdminNavMenu;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_nav_menu_02{
        use _init_db;
        /**
         * @description Adds custom arguments to some of the meta box object types.
         * @param null|\stdClass $object
         * @return null
         */
        protected function _tp_nav_menu_meta_box_object( $object = null ){
            if ( isset( $object->name ) ) {
                if ( 'page' === $object->name ) {
                    $object->_default_query = ['orderby' => 'menu_order title','post_status' => 'publish',];
                } elseif ( 'post' === $object->name ) {
                    $object->_default_query = ['post_status' => 'publish',];
                } elseif ( 'category' === $object->name ) {
                    $object->_default_query = ['orderby' => 'id','order' => 'DESC',];
                } else {
                    $object->_default_query = ['post_status' => 'publish',];
                }
            }
            return $object;
        }//995
        /**
         * @description Returns the menu formatted to edit.
         * @param int $menu_id
         * @return string
         */
        protected function _tp_get_nav_menu_to_edit( $menu_id = 0 ):string{
            $menu = $this->_tp_get_nav_menu_object( $menu_id );
            if ( $this->_is_nav_menu( $menu ) ){
                $menu_items = $this->_tp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) );
                $_menu_instructions = (! empty( $menu_items ) ) ? ' menu-instructions-inactive' : '';
                $output  = "<div id='menu_instructions' class='post-body-plain $_menu_instructions'>";
                $output .= "<p>{$this->__('Add menu items from the column on the left.')}</p>";
                $output .= "</div>";
                if ( empty( $menu_items ) ) {
                    return "$output<ul class='menu' id='menu_to_edit'></ul>";
                }
                $walker_class_name = $this->_apply_filters( 'tp_edit_nav_menu_walker', TP_NS_CORE_LIBS.'Walkers\\TP_Walker_Nav_Menu_Edit', $menu_id );
                if ( class_exists( $walker_class_name ) ) {
                    $walker = new $walker_class_name;
                }else{
                    $_walker_error = sprintf($this->__('The Walker class named %s does not exist.'),"<strong>$walker_class_name</strong>");
                    return new TP_Error('menu_walker_not_exist',$_walker_error);
                }
                $some_pending_menu_items = false;
                $some_invalid_menu_items = false;
                foreach ( (array) $menu_items as $menu_item ) {
                    if ( isset( $menu_item->post_status ) && 'draft' === $menu_item->post_status ) {
                        $some_pending_menu_items = true;
                    }
                    if ( ! empty( $menu_item->_invalid ) ) {
                        $some_invalid_menu_items = true;
                    }
                }
                if ( $some_pending_menu_items ) {
                    $output .= "<div class='notice notice-info notice-alt inline'><p>{$this->__('Click Save Menu to make pending menu items public.')}</p></div>";
                }
                if ( $some_invalid_menu_items ) {
                    $output .= "<div class='notice notice-info notice-alt inline'><p>{$this->__('There are some invalid menu items. Please check or delete them.')}</p></div>";
                }
                $output .= "<ul id='menu_to_edit' class='menu'>";
                $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $menu_items ), 0, (object) array( 'walker' => $walker ) );
                $output .= "</ul>";
                return $output;
            }
            if ( $this->_init_error( $menu ) ){
                return $menu;
            }
        }//1036
        /**
         * @description Returns the columns for the nav menus page.
         * @return array
         */
        protected function _tp_nav_menu_manage_columns():array{
            return ['_title' => $this->__( 'Show advanced menu properties' ),'cb' => "<input type='checkbox' />",'link-target' => $this->__( 'Link Target' ),
                'title-attribute' => $this->__( 'Title Attribute' ),'css-classes' => $this->__( 'CSS Classes' ),'xfn' => $this->__( 'Link Relationship (XFN)' ),'description' => $this->__( 'Description' ),];
        }//1110
        /**
         * @description Deletes orphaned draft menu items
         */
        protected function _tp_delete_orphaned_draft_menu_items():void{
            $this->tpdb = $this->_init_db();
            $delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
            $menu_items_to_delete = $this->tpdb->get_col( $this->tpdb->prepare(TP_SELECT . " ID FROM $this->tpdb->posts AS p LEFT JOIN $this->tpdb->postmeta AS m ON p.ID = m.post_id WHERE post_type = 'nav_menu_item' AND post_status = 'draft' AND meta_key = '_menu_item_orphaned' AND meta_value < %d", $delete_timestamp ) );
            foreach ($menu_items_to_delete as $menu_item_id ) {
                $this->_tp_delete_post( $menu_item_id, true );
            }
        }//1130
        /**
         * @description Saves nav menu items
         * @param $nav_menu_selected_id
         * @param $nav_menu_selected_title
         * @return array
         */
        protected function _tp_nav_menu_update_menu_items( $nav_menu_selected_id, $nav_menu_selected_title ):array{
            $unsorted_menu_items = $this->_tp_get_nav_menu_items($nav_menu_selected_id,['orderby' => 'ID','output' => ARRAY_A,'output_key' => 'ID','post_status' => 'draft,publish',]);
            $messages   = [];
            $menu_items = [];
            foreach ( $unsorted_menu_items as $_item ) { $menu_items[ $_item->db_id ] = $_item;}
            $post_fields = ['menu-item-db-id','menu-item-object-id','menu-item-object','menu-item-parent-id','menu-item-position',
                'menu-item-type','menu-item-title','menu-item-url','menu-item-description','menu-item-attr-title','menu-item-target','menu-item-classes', 'menu-item-xfn',];
            $this->_tp_defer_term_counting( true );
            if ( ! empty( $_POST['menu-item-db-id'] ) ) {
                foreach ( (array) $_POST['menu-item-db-id'] as $_key => $k ) {
                    if ( ! isset( $_POST['menu-item-title'][ $_key ] ) || '' === $_POST['menu-item-title'][ $_key ] ) { continue;}
                    $args = [];
                    foreach ( $post_fields as $field ) { $args[ $field ] = $_POST[ $field ][ $_key ] ?? '';}
                    $menu_item_db_id = $this->_tp_update_nav_menu_item( $nav_menu_selected_id, ( $_POST['menu-item-db-id'][ $_key ] !== $_key ? 0 : $_key ), $args );
                    if ($menu_item_db_id instanceof TP_Error && $this->_init_error( $menu_item_db_id ) ) {
                        $messages[] = "<div id='message' class='error'><p>{$menu_item_db_id->get_error_message()}</p></div>";
                    } else {
                        unset( $menu_items[(string)$menu_item_db_id ] );
                    }
                }
            }
            if ( ! empty( $menu_items ) ) {
                foreach ( array_keys( $menu_items ) as $menu_item_id ) {
                    if ( $this->_is_nav_menu_item( $menu_item_id ) ) { $this->_tp_delete_post( $menu_item_id );}
                }
            }
            $auto_add = ! empty( $_POST['auto-add-pages'] );
            $nav_menu_option = (array) $this->_get_option( 'nav_menu_options' );
            if ( ! isset( $nav_menu_option['auto_add'] ) ) { $nav_menu_option['auto_add'] = [];}
            if ( $auto_add ) {
                if ( ! in_array( $nav_menu_selected_id, $nav_menu_option['auto_add'], true ) ) {
                    $nav_menu_option['auto_add'][] = $nav_menu_selected_id;
                }
            } else {
                $key = array_search( $nav_menu_selected_id, $nav_menu_option['auto_add'], true );
                if ( false !== $key ) { unset( $nav_menu_option['auto_add'][ $key ] ); }
            }
            $nav_menu_option['auto_add'] = array_intersect( $nav_menu_option['auto_add'], $this->_tp_get_nav_menus( array( 'fields' => 'ids' ) ) );
            $this->_update_option( 'nav_menu_options', $nav_menu_option );
            $this->_tp_defer_term_counting( false );
            $this->_do_action( 'tp_update_nav_menu', $nav_menu_selected_id );
            $messages[]  = "<div id='message' class='updated notice is-dismissible'><p>";
            $messages[] .= sprintf($this->__('%s has been updated.'),"<strong>$nav_menu_selected_title</strong>");
            $messages[] .= "</p></div>";
            unset( $menu_items, $unsorted_menu_items );
            return $messages;
        }//1151
        /**
         * @description If a JSON blob of navigation menu data is in POST data, expand it and inject
         * @description . it into `$_POST` to avoid PHP `max_input_vars` limitations. See #14134.
         */
        protected function _tp_expand_nav_menu_post_data():void{
            if ( ! isset( $_POST['nav-menu-data'] ) ) { return;}
            $data = json_decode(stripslashes($_POST['nav-menu-data']), false);
            if ( ! is_null( $data ) && $data ) {
                foreach ( $data as $post_input_data ) {
                    preg_match( '#([^\[]*)(\[(.+)\])?#', $post_input_data->name, $matches );
                    $array_bits = array( $matches[1] );
                    if ( isset( $matches[3] ) ) {
                        $array_bits = $this->_tp_array_merge( $array_bits, explode( '][', $matches[3] ) );
                    }
                    $new_post_data = [];
                    for ( $i = count( $array_bits ) - 1; $i >= 0; $i-- ) {
                        if ( count( $array_bits ) - 1 === $i ) {
                            $new_post_data[ $array_bits[ $i ] ] = $this->_tp_slash( $post_input_data->value );
                        } else {
                            $new_post_data = array( $array_bits[ $i ] => $new_post_data );
                        }
                    }
                    $_POST = $this->_tp_array_replace_recursive( $_POST, $new_post_data );
                }
            }
        }//1269
    }
}else die;