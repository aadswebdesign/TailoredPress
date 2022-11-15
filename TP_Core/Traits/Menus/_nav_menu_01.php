<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-5-2022
 * Time: 12:31
 */
namespace TP_Core\Traits\Menus;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _nav_menu_01{
        use _init_error;
        /**
         * @description Returns a navigation menu object.
         * @param $menu
         * @return mixed
         */
        protected function _tp_get_nav_menu_object( $menu ){
            $menu_obj = false;
            if ( is_object( $menu ) ) $menu_obj = $menu;
            if ( $menu && ! $menu_obj ) {
                $menu_obj =  $this->_get_term( $menu, 'nav_menu' );
                if ( ! $menu_obj ) $menu_obj =  $this->_get_term_by( 'slug', $menu, 'nav_menu' );
                if ( ! $menu_obj ) $menu_obj =  $this->_get_term_by( 'name', $menu, 'nav_menu' );
            }
            if ( ! $menu_obj || $this->_init_error( $menu_obj ) ) $menu_obj = false;
            return $this->_apply_filters( 'tp_get_nav_menu_object', $menu_obj, $menu );
        }//18
        /**
         * @description Determines whether the given ID is a navigation menu.
         * @param $menu
         * @return bool
         */
        protected function _is_nav_menu( $menu ):bool{
            if ( ! $menu ) return false;
            $menu_obj = $this->_tp_get_nav_menu_object( $menu );
            if ($menu_obj &&! empty( $menu_obj->taxonomy ) && 'nav_menu' === $menu_obj->taxonomy && ! $this->_init_error( $menu_obj ))
                return true;
            return false;
        }//62
        /**
         * @description Registers navigation menu locations for a theme.
         * @param array $locations
         * @return array
         */
        protected function _register_nav_menus(array $locations):array{
            $this->_add_theme_support( 'menus' );
            foreach ( $locations as $key => $value ) {
                if ( is_int( $key ) ) {
                    $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Nav menu locations must be strings.' ), '5.3.0' );
                    break;
                }
            }
            $this->tp_registered_nav_menus = array_merge( (array) $this->tp_registered_nav_menus, $locations );
        }//90
        /**
         * @description Unregisters a navigation menu location for a theme.
         * @param $location
         * @return bool
         */
        protected function _unregister_nav_menu( $location ):bool{
            if ( is_array( $this->tp_registered_nav_menus ) && isset( $this->tp_registered_nav_menus[ $location ] ) ) {
                unset( $this->tp_registered_nav_menus[ $location ] );
                if ( empty( $this->tp_registered_nav_menus ) ) $this->_remove_theme_support( 'menus' );
                return true;
            }
            return false;
        }//115
        /**
         * @description Registers a navigation menu location for a theme.
         * @param $location
         * @param $description
         */
        protected function _register_nav_menu( $location, $description ):void{
            $this->_register_nav_menus( [$location => $description] );
        }//136
        /**
         * @description Retrieves all registered navigation menu locations in a theme.
         * @return string
         */
        protected function _get_registered_nav_menus():string{
            if ( isset( $this->__tp_registered_nav_menus ) ) return $this->__tp_registered_nav_menus;
            return [];
        }//149
        /**
         * @description Retrieves all registered navigation menu locations and the menus assigned to them.
         * @return array
         */
        protected function _get_nav_menu_locations():array{
            $locations = $this->_get_theme_mod( 'nav_menu_locations' );
            return ( is_array( $locations ) ) ? $locations : [];
        }//165
        /**
         * @description Determines whether a registered nav menu location has a menu assigned to it.
         * @param $location
         * @return mixed
         */
        protected function _has_nav_menu( $location ){
            $has_nav_menu = false;
            $registered_nav_menus = $this->_get_registered_nav_menus();
            if ( isset( $registered_nav_menus[ $location ] ) ) {
                $locations    = $this->_get_nav_menu_locations();
                $has_nav_menu = ! empty( $locations[ $location ] );
            }
            return $this->_apply_filters( 'has_nav_menu', $has_nav_menu, $location );
        }//178
        /**
         * @description Returns the name of a navigation menu.
         * @param $location
         * @return mixed
         */
        protected function _tp_get_nav_menu_name( $location ){
            $menu_name = '';
            $locations = $this->_get_nav_menu_locations();
            if ( isset( $locations[ $location ] ) ) {
                $menu = $this->_tp_get_nav_menu_object( $locations[ $location ] );
                if ( $menu && $menu->name ) $menu_name = $menu->name;
            }
            return $this->_apply_filters( 'tp_get_nav_menu_name', $menu_name, $location );
        }//206
        /**
         * @description Determines whether the given ID is a nav menu item.
         * @param int $menu_item_id
         * @return bool
         */
        protected function _is_nav_menu_item( $menu_item_id = 0 ):bool{
            return ( ! $this->_init_error( $menu_item_id ) && ( 'nav_menu_item' === $this->_get_post_type( $menu_item_id ) ) );
        }//238
    }
}else die;