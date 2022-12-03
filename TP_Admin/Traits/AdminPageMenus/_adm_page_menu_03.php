<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-10-2022
 * Time: 18:07
 */
namespace TP_Admin\Traits\AdminPageMenus;
if(ABSPATH){
    trait _adm_page_menu_03{
        /**
         * @param $libs_page
         * @param $parent_page
         * @return string
         */
        protected function _get_adm_library_page_hookname( $libs_page, $parent_page ):string{
            $parent = $this->_get_adm_page_parent( $parent_page );
            $page_type = 'admin';
            if ( empty( $parent_page ) || 'admin.php' === $parent_page || isset( $admin_page_hooks[ $libs_page ] ) ) {//todo
                if ( isset( $admin_page_hooks[ $libs_page ] ) ) { $page_type = 'top_level';
                } elseif ( isset( $admin_page_hooks[ $parent ] ) ) { $page_type = $admin_page_hooks[ $parent ]; }
            } elseif ( isset( $admin_page_hooks[ $parent ] ) ) {  $page_type = $admin_page_hooks[ $parent ];}
            $libs_page = preg_replace( '!\.php!', '', $libs_page );
            return $page_type . '_page_' . $libs_page;
        }//2046
        /**
         * @return bool
         */
        protected function _user_can_access_admin_page():bool{
            $parent = $this->_get_adm_page_parent();
            if (!isset($this->tp_library_page) && isset($this->tp_submenu_nopriv[ $parent][$this->tp_pagenow ])){ return false;}
            if ( isset( $this->tp_library_page ) ) {
                if ( isset( $this->tp_submenu_nopriv[ $parent ][ $this->tp_library_page ] ) ) { return false;}
                $hookname = $this->_get_adm_library_page_hookname( $this->tp_library_page, $parent );
                if ( ! isset( $this->tp_registered_pages[ $hookname ] ) ) { return false; }
            }
            if ( empty( $parent ) ) {
                if ( isset( $this->tp_menu_nopriv[ $this->tp_pagenow ])){ return false;}
                if ( isset( $this->tp_submenu_nopriv[ $this->tp_pagenow ][ $this->tp_pagenow ])){ return false;}
                if (isset($this->tp_library_page, $this->tp_submenu_nopriv[$this->tp_pagenow][$this->tp_library_page])){ return false;}
                if (isset($this->tp_library_page, $this->tp_menu_nopriv[$this->tp_library_page])) { return false;}
                foreach ( array_keys( $this->tp_submenu_nopriv ) as $key ) {
                    if ( isset( $this->tp_submenu_nopriv[ $key ][ $this->tp_pagenow ])){ return false;}
                    if (isset($this->tp_library_page, $this->tp_submenu_nopriv[$key][$this->tp_library_page])) { return false;}
                }
                return true;
            }
            if (isset($this->tp_library_page, $this->tp_menu_nopriv[$this->tp_library_page]) && $this->tp_library_page === $parent) {
                return false;}
            if ( isset( $submenu[ $parent ] ) ) {
                foreach ( $submenu[ $parent ] as $submenu_array ) {
                    if ( isset( $this->tp_library_page ) && $submenu_array[2] === $this->tp_library_page ) { return $this->_current_user_can( $submenu_array[1] ); }
                    if ( $submenu_array[2] === $this->tp_pagenow ) { return $this->_current_user_can( $submenu_array[1] );}
                }
            }
            foreach ( $this->tp_menu as $menu_array ) {
                if ( $menu_array[2] === $parent ) { return $this->_current_user_can( $menu_array[1] );}
            }
            return true;
        }//2107
        /**
         * @description Outputs nonce, action, and option_page fields for a settings page.
         * @param $option_group
         * @return string
         */
        protected function _get_hidden_input_settings_fields( $option_group ):string{
            $output_option  = "<input name='option_page' type='hidden' value='{$this->_esc_attr($option_group )}'/>";
            $output_option .= "<input name='action' type='hidden' value='update' />";
            $output_option .= $this->_tp_get_nonce_field( "$option_group-options" );
            return $output_option;
        }//2254 from admin/plugin.php settings fields
        protected function _hidden_input_settings_fields( $option_group ):void{
            echo $this->_get_hidden_input_settings_fields( $option_group );
        }//2254 from admin/plugin.php
        /*
         * _add_allowed_options
         * _remove_allowed_options
         * in  TP_Core\Traits\Options\_option_04;
         * _tp_add_privacy_policy_content
         * in TP_Admin\Traits\AdminMisc\_misc
         */
    }
}else{die;}