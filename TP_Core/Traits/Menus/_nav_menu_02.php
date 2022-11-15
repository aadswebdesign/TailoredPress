<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-5-2022
 * Time: 12:31
 */
namespace TP_Core\Traits\Menus;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    trait _nav_menu_02{
        /**
         * @description Creates a navigation menu.
         * @param $menu_name
         * @return string
         */
        protected function _tp_create_nav_menu( $menu_name ):string{
            return $this->_tp_update_nav_menu_object( 0, ['menu-name' => $menu_name] );
        }//252
        /**
         * @description Deletes a navigation menu.
         * @param $menu
         * @return bool
         */
        protected function _tp_delete_nav_menu( $menu ):bool{
            $menu = $this->_tp_get_nav_menu_object( $menu );
            if ( ! $menu ) return false;
            $menu_objects = $this->_get_objects_in_term( $menu->term_id, 'nav_menu' );
            if ( ! empty( $menu_objects ) ) {
                foreach ( $menu_objects as $item ) $this->_tp_delete_post( $item );
            }
            $result = $this->_tp_delete_term( $menu->term_id, 'nav_menu' );
            $locations = $this->_get_nav_menu_locations();
            foreach ( $locations as $location => $menu_id ) {
                if ( $menu_id === $menu->term_id ) $locations[ $location ] = 0;
            }
            $this->_set_theme_mod( 'nav_menu_locations', $locations );
            if ( $result && ! $this->_init_error( $result ) )
                $this->_do_action( 'tp_delete_nav_menu', $menu->term_id );
            return $result;
        }//265
        /**
         * @description Saves the properties of a menu or create a new menu with those properties.
         * @param int $menu_id
         * @param \array[] ...$menu_data
         * @return int|TP_Error
         */
        protected function _tp_update_nav_menu_object( $menu_id = 0, array ...$menu_data){
            $menu_id = (int) $menu_id;
            $_menu = $this->_tp_get_nav_menu_object( $menu_id );
            $args = [
                'description' => ($menu_data['description'] ?? ''),
                'name'        => ($menu_data['menu-name'] ?? ''),
                'parent'      => ( isset( $menu_data['parent'] ) ? (int) $menu_data['parent'] : 0 ),
                'slug'        => null,
            ];
            $_possible_existing = $this->_get_term_by( 'name', $menu_data['menu-name'], 'nav_menu' );
            if ( $_possible_existing && isset( $_possible_existing->term_id ) && $_possible_existing->term_id !== $menu_id && !$this->_init_error( $_possible_existing ) )
                return new TP_Error( 'menu_exists',
                    sprintf( $this->__( 'The menu name %s conflicts with another menu name. Please try another.' ),
                        '<strong>' . $this->_esc_html( $menu_data['menu-name'] ) . '</strong>'
                    ));
            if ( ! $_menu || $this->_init_error( $_menu ) ) {
                $menu_exists = $this->_get_term_by( 'name', $menu_data['menu-name'], 'nav_menu' );
                if ( $menu_exists )
                    return new TP_Error('menu_exists',
                        sprintf($this->__( 'The menu name %s conflicts with another menu name. Please try another.' ),
                            '<strong>' . $this->_esc_html( $menu_data['menu-name'] ) . '</strong>'
                        ));
                $_menu = $this->_tp_insert_term( $menu_data['menu-name'], 'nav_menu', $args );
                if ( $this->_init_error( $_menu ) ) return $_menu;
                $this->_do_action( 'tp_create_nav_menu', $_menu['term_id'], $menu_data );
                return (int) $_menu['term_id'];
            }
            if ( ! $_menu || ! isset( $_menu->term_id ) ) return 0;
            $menu_id = (int) $_menu->term_id;
            $update_response = $this->_tp_update_term( $menu_id, 'nav_menu', $args );
            if ( $this->_init_error( $update_response ) ) return $update_response;
            $menu_id = (int) $update_response['term_id'];
            $this->_do_action( 'tp_update_nav_menu', $menu_id, $menu_data );
            return $menu_id;
        }//315
        /**
         * @description Saves the properties of a menu item or create a new one.
         * @param int $menu_id
         * @param int $menu_item_db_id
         * @param array $menu_item_data
         * @param bool $fire_after_hooks
         * @return int
         */
        protected function _tp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0, $menu_item_data = [], $fire_after_hooks = true ):int{
            $menu_id         = (int) $menu_id;
            $menu_item_db_id = (int) $menu_item_db_id;
            if ( ! empty( $menu_item_db_id ) && ! $this->_is_nav_menu_item( $menu_item_db_id ) )
                return  (int) new TP_Error('update_nav_menu_item_failed', $this->__( 'The given object ID is not that of a menu item.' ) );
            $menu = $this->_tp_get_nav_menu_object( $menu_id );
            if ( ! $menu && 0 !== $menu_id )
                return (int) new TP_Error( 'invalid_menu_id', $this->__( 'Invalid menu ID.' ) );
            if ( $this->_init_error( $menu ) ) return $menu;
            $defaults = ['menu-item-db-id' => $menu_item_db_id,'menu-item-object-id' => 0,
                'menu-item-object' => '','menu-item-parent-id' => 0,'menu-item-position' => 0,
                'menu-item-type' => 'custom','menu-item-title' => '','menu-item-url' => '',
                'menu-item-description' => '','menu-item-attr-title' => '','menu-item-target' => '',
                'menu-item-classes' => '','menu-item-xfn' => '','menu-item-status' => '',
                'menu-item-post-date' => '','menu-item-post-date-gmt' => '',];
            $args = $this->_tp_parse_args( $menu_item_data, $defaults );
            if ( 0 === $menu_id ) {
                $args['menu-item-position'] = 1;
            } elseif ( 0 === (int) $args['menu-item-position'] ) {
                $menu_items                 = 0 === $menu_id ? [] : (array) $this->_tp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish,draft' ) );
                $last_item                  = array_pop( $menu_items );
                $args['menu-item-position'] = ( $last_item && isset( $last_item->menu_order ) ) ? 1 + $last_item->menu_order : count( $menu_items );
            }
            $original_parent = 0 < $menu_item_db_id ? $this->_get_post_field( 'post_parent', $menu_item_db_id ) : 0;
            if ( 'custom' === $args['menu-item-type'] ) $args['menu-item-url'] = trim( $args['menu-item-url'] );
            else {
                $args['menu-item-url'] = '';
                $original_title = '';
                if ( 'taxonomy' === $args['menu-item-type'] ) {
                    $original_parent = $this->_get_term_field( 'parent', $args['menu-item-object-id'], $args['menu-item-object'], 'raw' );
                    $original_title  = $this->_get_term_field( 'name', $args['menu-item-object-id'], $args['menu-item-object'], 'raw' );
                } elseif ( 'post_type' === $args['menu-item-type'] ) {
                    $original_object = $this->_get_post( $args['menu-item-object-id'] );
                    $original_parent = (int) $original_object->post_parent;
                    $original_title  = $original_object->post_title;
                } elseif ( 'post_type_archive' === $args['menu-item-type'] ) {
                    $original_object = $this->_get_post_type_object( $args['menu-item-object'] );
                    if ( $original_object ) $original_title = $original_object->labels->archives;
                }
                if ( $this->_tp_unslash( $args['menu-item-title'] ) === $this->_tp_special_chars_decode( $original_title ) )
                    $args['menu-item-title'] = '';
                if ( '' === $args['menu-item-title'] && '' === $args['menu-item-description'] ) $args['menu-item-description'] = ' ';
            }
            $post = ['menu_order' => $args['menu-item-position'],'ping_status' => 0,'post_content' => $args['menu-item-description'],
                'post_excerpt' => $args['menu-item-attr-title'],'post_parent' => $original_parent,'post_title' => $args['menu-item-title'],
                'post_type' => 'nav_menu_item',];
            $post_date = $this->_tp_resolve_post_date( $args['menu-item-post-date'], $args['menu-item-post-date-gmt'] );
            if ( $post_date ) $post['post_date'] = $post_date;
            $update = 0 !== $menu_item_db_id;
            if ( ! $update ) {
                $post['ID'] = 0;
                $post['post_status'] = 'publish' === $args['menu-item-status'] ? 'publish' : 'draft';
                $menu_item_db_id     = $this->_tp_insert_post( $post, true, $fire_after_hooks );
                if ( ! $menu_item_db_id || $this->_init_error( $menu_item_db_id ) ) {
                    return $menu_item_db_id;
                }
                $this->_do_action( 'tp_add_nav_menu_item', $menu_id, $menu_item_db_id, $args );
            }
            if ( $menu_id && ( ! $update || ! $this->_is_object_in_term( $menu_item_db_id, 'nav_menu', (int) $menu->term_id ) ) ) {
                $update_terms = $this->_tp_set_object_terms( $menu_item_db_id, array( $menu->term_id ), 'nav_menu' );
                if ( $this->_init_error( $update_terms ) ) return $update_terms;
            }
            if ( 'custom' === $args['menu-item-type'] ) {
                $args['menu-item-object-id'] = $menu_item_db_id;
                $args['menu-item-object']    = 'custom';
            }
            $menu_item_db_id = (int) $menu_item_db_id;
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_type', $this->_sanitize_key( $args['menu-item-type'] ) );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_menu_item_parent', (string) ( (int) $args['menu-item-parent-id'] ) );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_object_id', (string) ($args['menu-item-object-id'] ) );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_object', $this->_sanitize_key( $args['menu-item-object'] ) );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_target', $this->_sanitize_key( $args['menu-item-target'] ) );
            $args['menu-item-classes'] = array_map( 'sanitize_html_class', explode( ' ', $args['menu-item-classes'] ) );
            $args['menu-item-xfn']     = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['menu-item-xfn'] ) ) );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_classes', $args['menu-item-classes'] );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_xfn', $args['menu-item-xfn'] );
            $this->_update_post_meta( $menu_item_db_id, '_menu_item_url', $this->_esc_url_raw( $args['menu-item-url'] ) );
            if ( 0 === $menu_id ) $this->_update_post_meta( $menu_item_db_id, '_menu_item_orphaned', (string) time() );
            elseif ( $this->_get_post_meta( $menu_item_db_id, '_menu_item_orphaned' ) ) $this->_delete_post_meta( $menu_item_db_id, '_menu_item_orphaned' );
            if ( $update ) {
                $post['ID']          = $menu_item_db_id;
                $post['post_status'] = ( 'draft' === $args['menu-item-status'] ) ? 'draft' : 'publish';
                $update_post = $this->_tp_update_post( $post, true );
                if ( $this->_init_error( $update_post ) ) return $update_post;
            }
            $this->_do_action( 'tp_update_nav_menu_item', $menu_id, $menu_item_db_id, $args );
            return $menu_item_db_id;
        }//421
        /**
         * @description Returns all navigation menu objects.
         * @param array $args
         * @return mixed
         */
        protected function _tp_get_nav_menus( $args = [] ){
            $defaults = ['taxonomy' => 'nav_menu', 'hide_empty' => false,'orderby' => 'name',];
            $args     = $this->_tp_parse_args( $args, $defaults );
            return $this->_apply_filters( 'tp_get_nav_menus', $this->_get_terms( $args ), $args );
        }//622
       /**
         * @description Determines whether a menu item is valid.
         * @param $item
         * @return bool
         */
        protected function _is_valid_nav_menu_item( $item ):bool{
            return empty( $item->_invalid );
        }//654
        /**
         * @description Retrieves all menu items of a navigation menu.
         * @param $menu
         * @param array $args
         * @return bool
         */
        protected function _tp_get_nav_menu_items( $menu,array $args):bool{
            $menu = $this->_tp_get_nav_menu_object( $menu );
            if ( ! $menu ) return false;
            static $fetched = [];
            $items = $this->_get_objects_in_term( $menu->term_id, 'nav_menu' );
            if ( $this->_init_error( $items ) ) return false;
            $defaults        = ['order' => 'ASC','orderby' => 'menu_order','post_type' => 'nav_menu_item',
                'post_status' => 'publish','output' => ARRAY_A,'output_key' => 'menu_order','nopaging' => true,];
            $args            = $this->_tp_parse_args( $args, $defaults );
            $args['include'] = $items;
            if ( ! empty( $items ) ) $items = $this->_get_posts( $args );
            else $items = array();
            if ( empty( $fetched[ $menu->term_id ] ) && ! $this->_tp_using_ext_object_cache() ) {
                $fetched[ $menu->term_id ] = true;
                $posts                     = [];
                $terms                     = [];
                foreach ( $items as $item ) {
                    $object_id = $this->_get_post_meta( $item->ID, '_menu_item_object_id', true );
                    $object    = $this->_get_post_meta( $item->ID, '_menu_item_object', true );
                    $type      = $this->_get_post_meta( $item->ID, '_menu_item_type', true );
                    if ( 'post_type' === $type ) $posts[ $object ][] = $object_id;
                    elseif ( 'taxonomy' === $type ) $terms[ $object ][] = $object_id;
                }
                if ( ! empty( $posts ) ) {
                    foreach ( array_keys( $posts ) as $post_type )
                        $this->_get_posts(['post__in' => $posts[ $post_type ],'post_type' => $post_type,
                                'nopaging' => true,'update_post_term_cache' => false,] );
                }
                unset( $posts );
                if ( ! empty( $terms ) ) {
                    foreach ( array_keys( $terms ) as $taxonomy )
                        $this->_get_terms(['taxonomy' => $taxonomy,'include' => $terms[ $taxonomy ],'hierarchical' => false,]);
                }
                unset( $terms );
            }
            $items = array_map( 'tp_setup_nav_menu_item', $items );
            if ( ! $this->_is_admin() ) $items = array_filter( $items, '_is_valid_nav_menu_item' );
            if ( ARRAY_A === $args['output'] ) {
                $items = $this->_tp_list_sort($items,[$args['output_key'] => 'ASC',]);
                $i = 1;
                foreach ( $items as $k => $item ) $items[ $k ]->{$args['output_key']} = $i++;
            }
            return $this->_apply_filters( 'tp_get_nav_menu_items', $items, $menu, $args );
        }//687
        /**
         * @description Decorates a menu item object with the shared navigation menu item properties.
         * @param $menu_item
         * @return mixed
         */
        protected function _tp_setup_nav_menu_item( $menu_item ){
            if ( isset( $menu_item->post_type ) ) {
                if ( 'nav_menu_item' === $menu_item->post_type ) {
                    $menu_item->db_id            = (int) $menu_item->ID;
                    $menu_item->menu_item_parent = $menu_item->menu_item_parent ?? $this->_get_post_meta($menu_item->ID, '_menu_item_menu_item_parent', true);
                    $menu_item->object_id        = $menu_item->object_id ?? $this->_get_post_meta($menu_item->ID, '_menu_item_object_id', true);
                    $menu_item->object           = $menu_item->object ?? $this->_get_post_meta($menu_item->ID, '_menu_item_object', true);
                    $menu_item->type             = $menu_item->type ?? $this->_get_post_meta($menu_item->ID, '_menu_item_type', true);
                    if ( 'post_type' === $menu_item->type ) {
                        $object = $this->_get_post_type_object( $menu_item->object );
                        if ( $object ) {
                            $menu_item->type_label = $object->labels->singular_name;
                            if ( function_exists( 'get_post_states' ) ) {
                                $menu_post   = $this->_get_post( $menu_item->object_id );
                                $post_states = get_post_states( $menu_post );
                                if ( $post_states ) $menu_item->type_label = $this->_tp_strip_all_tags( implode( ', ', $post_states ) );
                            }
                        } else {
                            $menu_item->type_label = $menu_item->object;
                            $menu_item->_invalid   = true;
                        }
                        if ( 'trash' === $this->_get_post_status( $menu_item->object_id ) )  $menu_item->_invalid = true;
                        $original_object = $this->_get_post( $menu_item->object_id );
                        if ( $original_object ) {
                            $menu_item->url = $this->_get_permalink( $original_object->ID );
                            $original_title = $this->_apply_filters( 'the_title', $original_object->post_title, $original_object->ID );
                        } else {
                            $menu_item->url      = '';
                            $original_title      = '';
                            $menu_item->_invalid = true;
                        }
                        if ( '' === $original_title )
                            $original_title = sprintf( $this->__( '#%d (no title)' ), $menu_item->object_id );
                        $menu_item->title = ( '' === $menu_item->post_title ) ? $original_title : $menu_item->post_title;
                    } elseif ( 'post_type_archive' === $menu_item->type ) {
                        $object = $this->_get_post_type_object( $menu_item->object );
                        if ( $object ) {
                            $menu_item->title      = ( '' === $menu_item->post_title ) ? $object->labels->archives : $menu_item->post_title;
                            $post_type_description = $object->description;
                        } else {
                            $post_type_description = '';
                            $menu_item->_invalid   = true;
                        }
                        $menu_item->type_label = $this->__( 'Post Type Archive' );
                        $post_content          = $this->_tp_trim_words( $menu_item->post_content, 200 );
                        $post_type_description = ( '' === $post_content ) ? $post_type_description : $post_content;
                        $menu_item->url        = $this->_get_post_type_archive_link( $menu_item->object );
                        $menu_item->content = $post_type_description; //or this is right???

                    } elseif ( 'taxonomy' === $menu_item->type ) {
                        $object = $this->_get_taxonomy( $menu_item->object );
                        if ( $object ) $menu_item->type_label = $object->labels->singular_name;
                        else {
                            $menu_item->type_label = $menu_item->object;
                            $menu_item->_invalid   = true;

                        }
                        $original_object = $this->_get_term( (int) $menu_item->object_id, $menu_item->object );
                        if ( $original_object && ! $this->_init_error( $original_object ) ) {
                            $menu_item->url = $this->_get_term_link( (int) $menu_item->object_id, $menu_item->object );
                            $original_title = $original_object->name;
                        } else {
                            $menu_item->url      = '';
                            $original_title      = '';
                            $menu_item->_invalid = true;
                        }
                        if ( '' === $original_title )
                            $original_title = sprintf( $this->__( '#%d (no title)' ), $menu_item->object_id );
                        $menu_item->title = ( '' === $menu_item->post_title ) ? $original_title : $menu_item->post_title;
                    } else {
                        $menu_item->type_label = $this->__( 'Custom Link' );
                        $menu_item->title      = $menu_item->post_title;
                        $menu_item->url        = $menu_item->url ?? $this->_get_post_meta($menu_item->ID, '_menu_item_url', true);
                    }
                    $menu_item->target = $menu_item->target ?? $this->_get_post_meta($menu_item->ID, '_menu_item_target', true);
                    $menu_item->attr_title = $menu_item->attr_title ?? $this->_apply_filters('nav_menu_attr_title', $menu_item->post_excerpt);
                    if ( ! isset( $menu_item->description ) )
                        $menu_item->description = $this->_apply_filters( 'nav_menu_description', $this->_tp_trim_words( $menu_item->post_content, 200 ) );
                    $menu_item->classes = $menu_item->classes ?? (array)$this->_get_post_meta($menu_item->ID, '_menu_item_classes', true);
                    $menu_item->xfn     = $menu_item->xfn ?? $this->_get_post_meta($menu_item->ID, '_menu_item_xfn', true);
                } else {
                    $menu_item->db_id            = 0;
                    $menu_item->menu_item_parent = 0;
                    $menu_item->object_id        = (int) $menu_item->ID;
                    $menu_item->type             = 'post_type';
                    $object                = $this->_get_post_type_object( $menu_item->post_type );
                    $menu_item->object     = $object->name;
                    $menu_item->type_label = $object->labels->singular_name;
                    if ( '' === $menu_item->post_title )
                        $menu_item->post_title = sprintf( $this->__( '#%d (no title)' ), $menu_item->ID );
                    $menu_item->title  = $menu_item->post_title;
                    $menu_item->url    = $this->_get_permalink( $menu_item->ID );
                    $menu_item->target = '';
                    $menu_item->attr_title = $this->_apply_filters( 'nav_menu_attr_title', '' );
                    $menu_item->description = $this->_apply_filters( 'nav_menu_description', '' );
                    $menu_item->classes     = [];
                    $menu_item->xfn         = '';
                }
            } elseif ( isset( $menu_item->taxonomy ) ) {
                $menu_item->ID               = $menu_item->term_id;
                $menu_item->db_id            = 0;
                $menu_item->menu_item_parent = 0;
                $menu_item->object_id        = (int) $menu_item->term_id;
                $menu_item->post_parent      = (int) $menu_item->parent;
                $menu_item->type             = 'taxonomy';
                $object                = $this->_get_taxonomy( $menu_item->taxonomy );
                $menu_item->object     = $object->name;
                $menu_item->type_label = $object->labels->singular_name;
                $menu_item->title       = $menu_item->name;
                $menu_item->url         = $this->_get_term_link( $menu_item, $menu_item->taxonomy );
                $menu_item->target      = '';
                $menu_item->attr_title  = '';
                $menu_item->description = $this->_get_term_field( 'description', $menu_item->term_id, $menu_item->taxonomy );
                $menu_item->classes     = array();
                $menu_item->xfn         = '';
            }
            return $this->_apply_filters( 'tp_setup_nav_menu_item', $menu_item );
        }//824
        /**
         * @description Returns the menu items associated with a particular object.
         * @param int $object_id
         * @param string $object_type
         * @param array $taxonomy
         * @return array
         */
        protected function _tp_get_associated_nav_menu_items(int $object_id = 0, $object_type = 'post_type',array $taxonomy):array{
            $menu_item_ids = [];
            $query      = new TP_Query;
            $menu_items = $query->query_main(
                ['meta_key' => '_menu_item_object_id','meta_value' => $object_id,'post_status' => 'any',
                    'post_type' => 'nav_menu_item','posts_per_page' => -1,]
            );
            foreach ( (array) $menu_items as $menu_item ) {
                if ( isset( $menu_item->ID ) && $this->_is_nav_menu_item( $menu_item->ID ) ) {
                    $menu_item_type = $this->_get_post_meta( $menu_item->ID, '_menu_item_type', true );
                    if ('post_type' === $object_type && 'post_type' === $menu_item_type)
                        $menu_item_ids[] = (int) $menu_item->ID;
                    elseif ('taxonomy' === $object_type && 'taxonomy' === $menu_item_type && $this->_get_post_meta( $menu_item->ID, '_menu_item_object', true ) === $taxonomy )
                        $menu_item_ids[] = (int) $menu_item->ID;
                }
            }
            return array_unique( $menu_item_ids );
        }//1016
        /**
         * @description Callback for handling a menu item when its original object is deleted.
         * @param $object_id
         */
        protected function _tp_delete_post_menu_item( $object_id ):void{
            $object_id = (int) $object_id;
            $menu_item_ids = $this->_tp_get_associated_nav_menu_items( $object_id, 'post_type' );
            foreach ($menu_item_ids as $menu_item_id ) $this->_tp_delete_post( $menu_item_id, true );
        }//1059
    }
}else die;
