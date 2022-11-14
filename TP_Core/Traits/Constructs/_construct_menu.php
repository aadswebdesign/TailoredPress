<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-6-2022
 * Time: 18:39
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_menu{
        public $tp_menu;
        public $tp_menu_nopriv;
        public $tp_registered_menus;
        public $tp_submenu;
        public $tp_submenu_file;
        public $tp_submenu_nopriv;
        public $tp_one_theme_location_no_menus;
        public $tp_nav_menu_placeholder;
        public $tp_nav_menu_selected_id;
        public $tp_real_parent_file;

        protected function _construct_menu():void{
            $this->tp_menu = [];
            $this->tp_menu_nopriv;
            $this->tp_registered_menus = [];
            $this->tp_submenu =[];
            $this->tp_submenu_file;
            $this->tp_submenu_nopriv;
            $this->tp_one_theme_location_no_menus;
            $this->tp_nav_menu_placeholder;
            $this->tp_nav_menu_selected_id;
            $this->tp_real_parent_file;
        }
    }
}else die;