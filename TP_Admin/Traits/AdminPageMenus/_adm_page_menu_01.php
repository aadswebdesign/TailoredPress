<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-5-2022
 * Time: 12:31
 */
namespace TP_Admin\Traits\AdminPageMenus;
if(ABSPATH){
    trait _adm_page_menu_01{
        /**
         * @description Adds a top-level menu page.
         * @param $page_title
         * @param $menu_title
         * @param $capability
         * @param $menu_slug
         * @param string|object $class
         * @param string $method
         * @param string $icon_url
         * @param null $position
         * @param null|array $args
         * @return mixed
         */
        protected function _add_adm_menu_page( $page_title, $menu_title, $capability, $menu_slug, $class = null, $method = null, $icon_url = '', $position = null, $args=null){
            $menu_slug = $this->_tp_get_basename_path( $menu_slug );
            $this->tp_adm_page_hooks[ $menu_slug ] = $this->_sanitize_title( $menu_title );
            $class = $this->_tp_load_class($menu_slug,TP_NS_ADMIN_MENU_PAGES,$class,$args);
            $_method = null;
            if($class !== null){
                $_method = $class->$method;//todo testing
            }
            $hookname = $this->_get_adm_library_page_hookname( $menu_slug, '' );
            if ( ! empty( $_method ) && ! empty( $hookname ) && $this->_current_user_can( $capability ) ) {
                $this->_add_action( $hookname, $_method );
            }
            if ( empty( $icon_url ) ) {
                $icon_url   = 'dashicons-admin-generic';
                $icon_class = 'menu-icon-generic ';
            } else {
                $icon_url   = $this->_set_url_scheme( $icon_url );
                $icon_class = '';
            }
            $new_menu = array( $menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $icon_class . $hookname, $hookname, $icon_url );
            if ( null === $position ) {
                $menu[] = $new_menu;
            } elseif ( isset( $menu["(string)$position" ] ) ) {
                $position            = $position + substr( base_convert( md5( $menu_slug . $menu_title ), 16, 10 ), -5 ) * 0.00001;
                $menu[ "(string)$position" ] = $new_menu;
            } else { $menu[ $position ] = $new_menu;}
            $_registered_pages[ $hookname ] = true;
            $_parent_pages[ $menu_slug ] = false;
            return $hookname;
        }//1303 from admins plugins.php
        /**
         * @description Adds a submenu page.
         * @param $parent_slug
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
        protected function _add_adm_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $class = null, $method = null, $position = null,$args=null ):bool{
            $menu_slug   = $this->_tp_get_basename_path( $menu_slug );
            $parent_slug = $this->_tp_get_basename_path( $parent_slug );
            if ( isset( $this->tp_real_parent_file[ $parent_slug ] ) ) {
                $parent_slug = $this->tp_real_parent_file[ $parent_slug ];
            }
            if ( ! $this->_current_user_can( $capability ) ) {
                $this->tp_submenu_nopriv[ $parent_slug ][ $menu_slug ] = true;
                return false;
            }
            if ( ! isset( $this->tp_submenu[ $parent_slug ] ) && $menu_slug !== $parent_slug ) {
                foreach ( (array) $this->tp_menu as $parent_menu ) {
                    if ( $parent_menu[2] === $parent_slug && $this->_current_user_can( $parent_menu[1] ) ) {
                        $this->tp_submenu[ $parent_slug ][] = array_slice( $parent_menu, 0, 4 );
                    }
                }
            }
            $new_sub_menu = array( $menu_title, $capability, $menu_slug, $page_title );
            if ( ! is_int( $position ) ) {
                if ( null !== $position ) {
                    $this->_doing_it_wrong(__METHOD__,sprintf($this->__('The eight parameter passed to %s should be an integer representing menu position.'),"<code>{[$this,'_add_submenu_page'}</code>"),'0.0.1' );//let see
                }
                $this->tp_submenu[ $parent_slug ][] = $new_sub_menu;
            }else if ( ! isset(  $this->tp_submenu[ $parent_slug ] ) || $position >= count(  $this->tp_submenu[ $parent_slug ] ) ) {
                $this->tp_submenu[ $parent_slug ][] = $new_sub_menu;
            }else{
                $position = max( $position, 0 );
                if ( 0 === $position ) {
                    array_unshift( $this->tp_submenu[ $parent_slug ], $new_sub_menu );
                }else{
                    $before_items = array_slice( $this->tp_submenu[ $parent_slug ], 0, $position, true );
                    // Grab all of the items after the insertion point.
                    $after_items = array_slice( $this->tp_submenu[ $parent_slug ], $position, null, true );
                    // Add the new item.
                    $before_items[] = $new_sub_menu;
                    // Merge the items.
                    $this->tp_submenu[ $parent_slug ] = array_merge( $before_items, $after_items );
                }
            }
            ksort( $this->tp_submenu[ $parent_slug ] );
            $hookname = $this->_get_adm_library_page_hookname( $menu_slug, $parent_slug );
            $class = $this->_tp_load_class($menu_slug,TP_NS_ADMIN_MENU_PAGES,$class,$args);
            $_method = null;
            if($class !== null){
                $_method = $class->$method;//todo testing
            }
            if ( ! empty( $_method ) && ! empty( $hookname ) ) {
                $this->_add_action( $hookname, $_method );
            }
            $this->tp_registered_pages[ $hookname ] = true;
            if ( 'tools.php' === $parent_slug ) {
                $this->tp_registered_pages[ $this->_get_adm_library_page_hookname( $menu_slug, 'edit.php' ) ] = true;
            }
            $this->tp_parent_pages[ $menu_slug ] = $parent_slug;
            return $hookname;
        }
        /**
         * @description Adds a submenu page to the Tools main menu.
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
        protected function _add_adm_management_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'tools.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1487
        /**
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
        protected function _add_adm_options_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'options_general.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1511
        /**
         * @description Adds a submenu page to the Appearance main menu.
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
        protected function _add_adm_theme_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'themes.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1535
        /**
         * @description Adds a submenu page to the Library main menu.
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
        protected function _add_adm_module_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'module.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1561 or I want this?
        /**
         * @description Adds a submenu page to the Users/Profile main menu.
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
        protected function _add_adm_users_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            if ( $this->_current_user_can( 'edit_users' ) ) { $parent = 'users.php';}
            else {$parent = 'profile.php';}
            return $this->_add_adm_submenu_page( $parent, $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1583
        /**
         * @description Adds a submenu page to the Dashboard main menu.
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
        protected function _add_adm_dashboard_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'index.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1612
        /**
         * @description Adds a submenu page to the Posts main menu.
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
        protected function _add_adm_posts_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'edit.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1636
        /**
         * @description Adds a submenu page to the Media main menu.
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
        protected function _add_adm_media_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool{
            return $this->_add_adm_submenu_page( 'upload.php', $page_title, $menu_title, $capability, $menu_slug, $class, $method , $position,$args );
        }//1660
    }
}else die;