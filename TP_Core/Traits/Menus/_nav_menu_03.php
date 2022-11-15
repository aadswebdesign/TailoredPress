<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-5-2022
 * Time: 12:31
 */
namespace TP_Core\Traits\Menus;
if(ABSPATH){
    trait _nav_menu_03{
        /**
         * @description Serves as a callback for handling a menu item when its original object is deleted.
         * @param $object_id
         * @param $taxonomy
         */
        protected function _tp_delete_tax_menu_item(int $object_id, $taxonomy ):void{ //not used ,int $tt_id
            $menu_item_ids = $this->_tp_get_associated_nav_menu_items( $object_id, 'taxonomy', $taxonomy );
            foreach ( (array) $menu_item_ids as $menu_item_id ) $this->_tp_delete_post( $menu_item_id, true );
        }//1079
        /**
         * @description Automatically add newly published page objects to menus with that as an option.
         * @param $new_status
         * @param $old_status
         * @param $post
         */
        protected function _tp_auto_add_pages_to_menu( $new_status, $old_status, $post ):void{
            if ( 'publish' !== $new_status || 'publish' === $old_status || 'page' !== $post->post_type )
                return;
            if ( ! empty( $post->post_parent ) ) return;
            $auto_add = $this->_get_option( 'nav_menu_options' );
            if ( empty( $auto_add ) || ! is_array( $auto_add ) || ! isset( $auto_add['auto_add'] ) )
                return;
            $auto_add = $auto_add['auto_add'];
            if ( empty( $auto_add ) || ! is_array( $auto_add ) ) return;
            $args = ['menu-item-object-id' => $post->ID,'menu-item-object' => $post->post_type,
                'menu-item-type' => 'post_type', 'menu-item-status' => 'publish',];
            foreach ( $auto_add as $menu_id ) {
                $items = $this->_tp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish,draft' ) );
                if ( ! is_array( $items ) ) continue;
                foreach ( $items as $item ) {
                    if ( $post->ID === $item->object_id ) continue 2;
                }
                $this->_tp_update_nav_menu_item( $menu_id, 0, $args );
            }
        }//1099
        /**
         * @description Deletes auto-draft posts associated with the supplied changeset.
         * @param $post_id
         */
        protected function _tp_delete_customize_changeset_dependent_auto_drafts( $post_id ):void{
            $post = $this->_get_post( $post_id );
            if ( ! $post || 'customize_changeset' !== $post->post_type ) return;
            $data = json_decode( $post->post_content, true );
            if ( empty( $data['nav_menus_created_posts']['value'] ) ) return;
            $this->_remove_action( 'delete_post', '_tp_delete_customize_changeset_dependent_auto_drafts' );
            foreach ( $data['nav_menus_created_posts']['value'] as $stub_post_id ) {
                if ( empty( $stub_post_id ) ) continue;
                if ( 'auto-draft' === $this->_get_post_status( $stub_post_id ) ) {
                    $this->_tp_delete_post( $stub_post_id, true );
                } elseif ( 'draft' === $this->_get_post_status( $stub_post_id ) ) {
                    $this->_tp_trash_post( $stub_post_id );
                    $this->_delete_post_meta( $stub_post_id, '_customize_changeset_uuid' );
                }
            }
            $this->_add_action( 'delete_post', '_tp_delete_customize_changeset_dependent_auto_drafts' );
        }//1144
        /**
         * @description Handles menu config after theme change.
         */
        protected function _tp_menus_changed():void{
            $old_nav_menu_locations    = $this->_get_option( 'theme_switch_menu_locations',[]);
            $new_nav_menu_locations    = $this->_get_nav_menu_locations();
            $mapped_nav_menu_locations = $this->_tp_map_nav_menu_locations( $new_nav_menu_locations, $old_nav_menu_locations );
            $this->_set_theme_mod( 'nav_menu_locations', $mapped_nav_menu_locations );
            $this->_delete_option( 'theme_switch_menu_locations' );
        }//1176
        /**
         * @description Maps nav menu locations according to assignments in previously active theme.
         * @param $new_nav_menu_locations
         * @param $old_nav_menu_locations
         * @return array
         */
        protected function _tp_map_nav_menu_locations( $new_nav_menu_locations, $old_nav_menu_locations ):array{
            $registered_nav_menus   = $this->_get_registered_nav_menus();
            $new_nav_menu_locations = array_intersect_key( $new_nav_menu_locations, $registered_nav_menus );
            if ( empty( $old_nav_menu_locations ) ) return $new_nav_menu_locations;
            if ( 1 === count( $old_nav_menu_locations ) && 1 === count( $registered_nav_menus ) ) {
                $new_nav_menu_locations[ key( $registered_nav_menus ) ] = array_pop( $old_nav_menu_locations );
                return $new_nav_menu_locations;
            }
            $old_locations = array_keys( $old_nav_menu_locations );
            foreach ( $registered_nav_menus as $location => $name ) {
                if ( in_array( $location, $old_locations, true ) ) {
                    $new_nav_menu_locations[ $location ] = $old_nav_menu_locations[ $location ];
                    unset( $old_nav_menu_locations[ $location ] );
                }
            }
            if ( empty( $old_nav_menu_locations ) ) return $new_nav_menu_locations;
            $common_slug_groups = [['primary', 'menu-1', 'main', 'header', 'navigation', 'top'],
                ['secondary', 'menu-2', 'footer', 'subsidiary', 'bottom'], ['social'],];
            foreach ( $common_slug_groups as $slug_group ) {
                foreach ( $slug_group as $slug ) {
                    foreach ( $registered_nav_menus as $new_location => $name ) {
                        if ( is_string( $new_location ) && false === stripos( $new_location, $slug ) && false === stripos( $slug, $new_location ) )
                            continue;
                        elseif ( is_numeric( $new_location ) && $new_location !== $slug ) continue;
                        foreach ( $old_nav_menu_locations as $location => $menu_id ) {
                            foreach ( $slug_group as $sub_slug ) {
                                if ( is_string( $location ) && false === stripos( $location, $sub_slug ) && false === stripos( $sub_slug, $location ) )
                                    continue;
                                elseif ( is_numeric( $location ) && $location !== $sub_slug ) continue;
                                if ( ! empty( $old_nav_menu_locations[ $location ] ) ) {
                                    $new_nav_menu_locations[ $new_location ] = $old_nav_menu_locations[ $location ];
                                    unset( $old_nav_menu_locations[ $location ] );
                                    continue 3;
                                }
                            } // End foreach ( $slug_group as $slug ).
                        } // End foreach ( $old_nav_menu_locations as $location => $menu_id ).
                    } // End foreach foreach ( $registered_nav_menus as $new_location => $name ).
                } // End foreach ( $slug_group as $slug ).
            } // End foreach ( $common_slug_groups as $slug_group ).
            return $new_nav_menu_locations;
        }//1194
    }
}else die;