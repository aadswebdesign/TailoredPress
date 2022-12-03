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
        private $__admin_page_hooks;
        private $__library_page;
        private $__menu;
        private $__pagenow;
        private $__parent_file;
        private $__parent_pages;
        private $__registered_pages;
        private $__submenu;
        private $__submenu_file;
        private $__tp_menu_nopriv;
        private $__tp_real_parent_file;
        private $__tp_submenu_nopriv;
        private $__type_now;
        private $__title;
        private $__hook_suffix;
        private $__current_screen;
        private $__tp_locale;
        private $__update_title;
        private $__total_update_count;
        private $__self;
        public function getAdminPageHooks(){
            return $this->__admin_page_hooks;
        }
        public function getLibraryPage(){
            return $this->__library_page;
        }
        public function getMenu(){
            return $this->__menu;
        }
        public function getPagenow(){
            return $this->__pagenow;
        }
        public function getParentFile(){
            return $this->__parent_file;
        }
        public function getParentPages(){
            return $this->__parent_pages;
        }
        public function getRegisteredPages(){
            return $this->__registered_pages;
        }
        public function getSubmenu(){
            return $this->__submenu;
        }
        public function getTpMenuNopriv(){
            return $this->__tp_menu_nopriv;
        }
        public function getTpRealParentFile(){
            return $this->__tp_real_parent_file;
        }
        public function getTpSubmenuNopriv(){
            return $this->__tp_submenu_nopriv;
        }
        public function getTypeNow(){
            return $this->__type_now;
        }
        public function getTitle()
        {
            return $this->__title;
        }
        public function getHookSuffix()
        {
            return $this->__hook_suffix;
        }
        public function getCurrentScreen()
        {
            return $this->__current_screen;
        }
        public function getTpLocale()
        {
            return $this->__tp_locale;
        }
        public function getUpdateTitle()
        {
            return $this->__update_title;
        }
        public function getTotalUpdateCount()
        {
            return $this->__total_update_count;
        }




    }
}else die;


