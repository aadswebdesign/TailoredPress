<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-7-2022
 * Time: 05:30
 */

namespace TP_Core\Traits\Templates\TemplateClasses;
if(ABSPATH){
    trait _nav_menu_template{
        //@description Displays a navigation menu.
        protected function _tp_nav_menu(array ...$args){return '';}//57
        //@description Adds the class property classes for the current context, if applicable.
        protected function _tp_menu_item_classes_by_context( &$menu_items ){return '';}//314
        //@description Retrieves the HTML list content for nav menu items.
        protected function _walk_nav_menu_tree( $items, $depth, $r ){return '';}//595
        //@description Prevents a menu item ID from being used more than once.
        protected function _nav_menu_item_id_use_once( $id, $item ){return '';}//621
    }
}else die;