<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-7-2022
 * Time: 17:23
 */
namespace TP_Admin\Traits\AdminInits;
if(ABSPATH){
    trait _init_admin{
        protected $_adm_page_hooks;
        protected $_adm_library_page;
        protected $_adm_menu;
        protected $_adm_pagenow;
        protected $_adm_parent_file;
        protected $_adm_parent_pages;
        protected $_adm_registered_pages;
        protected $_adm_submenu;
        protected $_adm_submenu_file;
        protected $_adm_tp_menu_nopriv;
        protected $_adm_real_parent_file;
        protected $_adm_submenu_nopriv;
        protected $_adm_type_now;
        protected $_adm_title;
        protected $_adm_hook_suffix;
        protected $_adm_current_screen;
        protected $_adm_locale;
        protected $_adm_update_title;
        protected $_adm_total_update_count;
        protected $_adm_self;
        public function getAdminPageHooks(){
            return $this->_adm_page_hooks;
        }
        public function getLibraryPage(){
            return $this->_adm_library_page;
        }
        public function getMenu(){
            return $this->_adm_menu;
        }
        public function getPagenow(){
            return $this->_adm_pagenow;
        }
        public function getParentFile(){
            return $this->_adm_parent_file;
        }
        public function getParentPages(){
            return $this->_adm_parent_pages;
        }
        public function getRegisteredPages(){
            return $this->_adm_registered_pages;
        }
        public function getSubmenu(){
            return $this->_adm_submenu;
        }
        public function getTpMenuNopriv(){
            return $this->_adm_menu_nopriv;
        }
        public function getTpRealParentFile(){
            return $this->_adm_real_parent_file;
        }
        public function getTpSubmenuNopriv(){
            return $this->_adm_submenu_nopriv;
        }
        public function getTypeNow(){
            return $this->_adm_type_now;
        }
        public function getTitle()
        {
            return $this->_adm_title;
        }
        public function getHookSuffix()
        {
            return $this->_adm_hook_suffix;
        }
        public function getCurrentScreen()
        {
            return $this->_adm_current_screen;
        }
        public function getTpLocale()
        {
            return $this->_adm_locale;
        }
        public function getUpdateTitle()
        {
            return $this->_adm_update_title;
        }
        public function getTotalUpdateCount()
        {
            return $this->_adm_total_update_count;
        }
    }
}else die;