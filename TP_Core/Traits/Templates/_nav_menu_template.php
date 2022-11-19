<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-10-2022
 * Time: 17:10
 */

namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _nav_menu_template {
        //@description Displays a navigation menu.
        protected function _tp_get_nav_menu( $args = []):string{return '';}//57
        protected function _tp_nav_menu( $args = []):void{}//57
        //@description Adds the class property classes for the current context, if applicable.
        protected function _tp_menu_item_classes_by_context( &$menu_items ):void{}//314
        //@description Retrieves the HTML list content for nav menu items.
        protected function _walk_nav_menu_tree( $items, $depth, $r ):string{return '';}//605 //must be packed in <li></li>
        //@description Prevents a menu item ID from being used more than once.
        protected function _nav_menu_item_id_use_once( $id, $item ):string{return '';}//621
    }
}else die;