<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-9-2022
 * Time: 04:59
 */

namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Templates\_post_template_01;

if(ABSPATH){
    class TP_Walker_Nav_Menu_Edit extends TP_Walker_Nav_Menu {
        use _init_error;
        use _taxonomy_02;
        use _post_01,_post_03;
        use _post_template_01;
        public $tp_nav_menu_max_depth;
        public function getTpNavMenuMaxDepth(){
            return $this->tp_nav_menu_max_depth;
        }
        public function start_lvl( &$output, $depth = 0, ...$args):void {}
        public function end_lvl( &$output, $depth = 0, ...$args):void {}
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):string{
            $menu_item              = $data_object;
            $this->tp_nav_menu_max_depth = $depth > $this->tp_nav_menu_max_depth ? $depth : $this->tp_nav_menu_max_depth;
            $removed_args = ['action','custom-link-tab','edit-menu-item','menu-item','page-tab', '_tpnonce',];
            $original_title = false;
            if ( 'taxonomy' === $menu_item->type ) {
                $original_object = $this->_get_term( (int) $menu_item->object_id, $menu_item->object );
                if ( $original_object && ! $this->_init_error( $original_object ) ) {
                    $original_title = $original_object->name;
                }
            } elseif ( 'post_type' === $menu_item->type ) {
                $original_object = $this->_get_post( $menu_item->object_id );
                if ( $original_object ) {
                    $original_title = $this->_get_the_title( $original_object->ID );
                }
            } elseif ( 'post_type_archive' === $menu_item->type ) {
                $original_object = $this->_get_post_type_object( $menu_item->object );
                if ( $original_object ) {
                    $original_title = $original_object->labels->archives;
                }
            }
            $item_id = null;
            $_menu_item_edit = ( ( isset( $_GET['edit-menu-item'] ) && $item_id === $_GET['edit-menu-item'] ) ? 'active' : 'inactive' );
            $classes = [
                "menu-item menu-item-depth-{$depth}",
                "menu-item-{$this->_esc_attr( $menu_item->object )}",
                "menu-item-edit-{$_menu_item_edit}",
            ];
            $title = $menu_item->title;
            if ( ! empty( $menu_item->_invalid ) ) {
                $classes[] = 'menu-item-invalid';
                $title = sprintf( $this->__( '%s (Invalid)' ), $menu_item->title );
            } elseif ( isset( $menu_item->post_status ) && 'draft' === $menu_item->post_status ) {
                $classes[] = 'pending';
                $title = sprintf( $this->__( '%s (Pending)' ), $menu_item->title );
            }
            $title = ( ! isset( $menu_item->label ) || '' === $menu_item->label ) ? $title : $menu_item->label;
            $submenu_text = '';
            if ( 0 === $depth ) {$submenu_text = 'style="display: none;"';}
            $li_classes = implode( ' ', $classes );
            $edit_output = "<li id='menu_item_$item_id' class='$li_classes' $submenu_text $title></li>";
            $edit_output .= "";
            $edit_output .= $original_title;//todo just parked here
            $edit_output .= $removed_args;//todo just parked here
            $edit_output .= "";
            $edit_output .= "";
            $edit_output .= "";
            $edit_output .= "";
            $edit_output .= "<ul class='menu-item-transport'></ul>";
            $output .= $edit_output;
        }
    }
}else{die;}

