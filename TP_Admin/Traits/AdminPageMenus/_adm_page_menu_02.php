<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-5-2022
 * Time: 12:31
 */
namespace TP_Admin\Traits\AdminPageMenus;
if(ABSPATH){
    trait _adm_page_menu_02{
        /**
         * @description Adds a submenu page to the Links main menu.
         * @param $page_title
         * @param $menu_title
         * @param $capability
         * @param $menu_slug
         * @param string|object $class
         * @param string $method
         * @param null $position
         * @param null|array $args
         * @return bool
         */
        protected function _add_adm_links_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'link_manager.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1684
        /**
         * @description Adds a submenu page to the Pages main menu.
         * @param $page_title
         * @param $menu_title
         * @param $capability
         * @param $menu_slug
         * @param string|object $class
         * @param string $method
         * @param null $position
         * @param null|array $args
         * @return bool
         */
        protected function _add_adm_pages_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null, $args=null ):bool{
            return $this->_add_adm_submenu_page( 'edit.php?post_type=page', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position, $args );
        }//1710
        /**
         * @description Adds a submenu page to the Comments main menu.
         * @param $page_title
         * @param $menu_title
         * @param $capability
         * @param $menu_slug
         * @param string|object $class
         * @param string $method
         * @param null $position
         * @param null|array $args
         * @return bool
         */
        protected function _add_adm_comments_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null, $args=null ):bool{
            return $this->_add_adm_submenu_page( 'edit_comments.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position, $args );
        }//1732
        /**
         * @description Removes a top-level admin menu.
         * @param $menu_slug
         * @return bool
         */
        protected function _remove_adm_menu_page( $menu_slug ):bool{
            foreach ( $this->tp_menu as $i => $item ) {
                if ( $menu_slug === $item[2] ) {
                    unset( $this->tp_menu[ $i ] );
                    return $item;
                }
            }
            return false;
        }//1776
        /**
         * @description Removes an admin submenu.
         * @param $menu_slug
         * @param $submenu_slug
         * @return bool
         */
        protected function _remove_adm_submenu_page( $menu_slug, $submenu_slug ):bool{
            if ( ! isset( $this->tp_submenu[ $menu_slug ] ) ) { return false;}
            foreach ( $this->tp_submenu[ $menu_slug ] as $i => $item ) {
                if ( $submenu_slug === $item[2] ) {
                    unset( $this->tp_submenu[ $menu_slug ][ $i ] );
                    return $item;
                }
            }
            return false;
        }//1806
        /**
         * @description Gets the URL to access a particular menu page based on the slug it was registered with.
         * @param $menu_slug
         * @return mixed
         */
        protected function _get_adm_menu_page_url( $menu_slug){
            if ( isset( $_parent_pages[ $menu_slug ] ) ) {
                $parent_slug = $_parent_pages[ $menu_slug ];
                if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
                    $url = $this->_admin_url( $this->_add_query_arg( 'page', $menu_slug, $parent_slug ) ); }
                else { $url = $this->_admin_url( 'admin.php?page=' . $menu_slug ); }
            } else { $url = '';}
            return $this->_esc_url($url);
        }//1836
        protected function _adm_menu_page_url( $menu_slug):void{
            echo $this->_get_adm_menu_page_url( $menu_slug);
        }//1836
        /**
         * @description Gets the parent file of the current admin page.
         * @param string|object $parent
         * @return string
         */
        protected function _get_adm_page_parent( $parent = '' ):string{
            if ( ! empty( $parent ) && 'admin.php' !== $parent ) {
                if ( isset( $this->tp_real_parent_file[ $parent ] ) ) {
                    $parent = $this->tp_real_parent_file[ $parent ];
                }
                return $parent;
            }
            if ( 'admin.php' === $this->tp_pagenow && isset( $this->tp_library_page ) ) {
                foreach ( (array) $this->tp_menu as $parent_menu ) {
                    if ( $parent_menu[2] === $this->tp_library_page ) {
                        $this->tp_parent_file = $this->tp_real_parent_file[$this->tp_parent_file] ?? $this->tp_library_page;
                        return $this->tp_parent_file;
                    }
                }
                if ( isset( $this->tp_menu_nopriv[ $this->tp_library_page ] ) ) {
                    $this->tp_parent_file = $this->tp_library_page;
                    if ( isset( $this->tp_real_parent_file[ $this->tp_parent_file ] ) ) {
                        $this->tp_parent_file = $this->tp_real_parent_file[ $this->tp_parent_file ];
                    }
                    return $this->tp_parent_file;
                }
            }
            if ( isset( $this->tp_library_page, $this->tp_submenu_nopriv[ $this->tp_pagenow ][ $this->tp_library_page ] ) ) {
                $this->tp_parent_file = $this->tp_real_parent_file[$this->tp_parent_file] ?? $this->tp_pagenow;
                return $this->tp_parent_file;
            }
            foreach ( array_keys( (array) $this->tp_submenu ) as $_parent ) {
                $parent = $_parent;
                foreach ( $this->tp_submenu[ $parent ] as $submenu_array ) {
                    if ( isset( $this->tp_real_parent_file[ $parent ])){ $parent = $this->tp_real_parent_file[ $parent ];}
                    if ( ! empty( $this->tp_typenow ) && "$this->tp_pagenow?post_type=$this->tp_typenow" === $submenu_array[2] ) {
                        $this->tp_parent_file = $parent;
                        return $parent;
                    }
                    if (empty($this->tp_typenow ) && $this->tp_pagenow === $submenu_array[2] && ( empty( $this->tp_parent_file ) || false === strpos( $this->tp_parent_file, '?'))){
                        $this->tp_parent_file = $parent;
                        return $parent;
                    }
                    if ( isset( $this->tp_library_page ) && $this->tp_library_page === $submenu_array[2] ) {
                        $this->tp_parent_file = $parent;
                        return $parent;
                    }
                }
            }
            if (empty($this->tp_parent_file)){ $this->tp_parent_file = '';}
            return '';
        }//1857
        /**
         * @description Gets the title of the current admin page.
         * @return mixed
         */
        protected function _get_adm_page_title(){
            if ( ! empty( $this->tp_title ) ) { return $this->tp_title;}
            $hook = $this->_get_adm_library_page_hookname( $this->tp_library_page, $this->tp_pagenow );
            $parent  = $this->_get_adm_page_parent();
            $parent1 = $parent;
            if ( empty( $parent ) ) {
                foreach ( (array) $this->tp_menu as $menu_array ) {
                    if ( isset( $menu_array[3] ) ) {
                        if ( $menu_array[2] === $this->tp_pagenow ) {
                            $this->tp_title = $menu_array[3];
                            return $menu_array[3];
                        }
                        if ( isset($this->tp_library_page ) && $this->tp_library_page === $menu_array[2] && $hook === $menu_array[5] ) {
                            $this->tp_title = $menu_array[3];
                            return $menu_array[3];
                        }
                    } else {
                        $this->tp_title = $menu_array[0];
                        return $this->tp_title;
                    }
                }
            } else {
                foreach ( array_keys( $this->tp_submenu ) as $parent ) {
                    foreach ( $this->tp_submenu[ $parent ] as $submenu_array ) {
                        if ( isset( $this->tp_library_page ) && $this->tp_library_page === $submenu_array[2] && ( $this->tp_pagenow === $parent || $this->tp_library_page === $parent|| $this->tp_library_page === $hook
                                || ('admin.php' === $this->tp_pagenow && $parent1 !== $submenu_array[2])|| (!empty($$this->tp_typenow) && "$this->tp_pagenow?post_type=$$this->tp_typenow" === $parent))){
                            $this->tp_title = $submenu_array[3];
                            return $submenu_array[3];
                        }
                        if ( $submenu_array[2] !== $this->tp_pagenow || isset( $_GET['page'] ) ) {  continue;}
                        if ( isset( $submenu_array[3] ) ) {
                            $this->tp_title = $submenu_array[3];
                            return $submenu_array[3];
                        }
                        $this->tp_title = $submenu_array[0];
                        return $this->tp_title;
                    }
                }
                if ( empty( $this->tp_title ) ) {
                    foreach ( $this->tp_menu as $menu_array ) {
                        if ( isset( $this->tp_library_page ) && $this->tp_library_page === $menu_array[2] && 'admin.php' === $this->tp_pagenow && $parent1 === $menu_array[2]){
                            $this->tp_title = $menu_array[3];
                            return $menu_array[3];
                        }
                    }
                }
            }
            return $this->tp_title;
        }//1943
        /**
         * @description Gets the hook attached to the administrative page of a library
         * @param $libs_page
         * @param $parent_page
         * @return null
         */
        protected function _get_adm_library_page_hook( $libs_page, $parent_page ){
            $hook = $this->_get_adm_library_page_hookname( $libs_page, $parent_page );
            if($this->_has_action( $hook )){ return $hook;}
            return null;
        }//2025
    }
}else die;
