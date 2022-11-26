<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-9-2022
 * Time: 04:59
 */
namespace TP_Core\Libs\Walkers;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Admin\Traits\NavMenu\_adm_nav_menu_01;

if(ABSPATH){
    class TP_Walker_Nav_Menu_Checklist extends TP_Walker_Nav_Menu {
        use _adm_nav_menu_01,_adm_template_04;
        public $_nav_menu_placeholder, $nav_menu_selected_id;
        public function __construct( $fields = false ) {
            if ( $fields ) { $this->db_fields = $fields;}
        }
        public function start_lvl( &$output, $depth = 0, ...$args):void{
            $indent  = str_repeat( "\t", $depth );
            $output .= "\n$indent<ul class='children'>\n";
        }
        public function end_lvl( &$output, $depth = 0, ...$args):void {
            $indent  = str_repeat( "\t", $depth );
            $output .= "\n$indent</ul>";
        }
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string{
            $menu_item = $data_object;
            $this->_nav_menu_placeholder = ( 0 > $this->_nav_menu_placeholder ) ? (int) $this->_nav_menu_placeholder - 1 : -1;
            $possible_object_id    = isset( $menu_item->post_type ) && 'nav_menu_item' === $menu_item->post_type ? $menu_item->object_id : $this->_nav_menu_placeholder;
            $possible_db_id        = ( ! empty( $menu_item->ID ) ) && ( 0 < $possible_object_id ) ? (int) $menu_item->ID : 0;
            $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
            $output .= "$indent<li>";
            $output .= "<dt><label class='menu-item-title'>";
            if ( ! empty( $menu_item->label)){ $title = $menu_item->label;}
            elseif ( isset( $menu_item->post_type ) ) {
                $title = $this->_apply_filters( 'the_title', $menu_item->post_title, $menu_item->ID );
            }
            $output .= isset( $title ) ? $this->_esc_html( $title ) : $this->_esc_html( $menu_item->title );
            if ( empty( $menu_item->label ) && isset( $menu_item->post_type ) && 'page' === $menu_item->post_type ) {
                $output .= $this->_post_states( $menu_item, false );
            }
            $output .= "</label></dt><dd>";
            $output .= "<input {$this->_tp_nav_menu_disabled_check( $this->nav_menu_selected_id, false )} class='menu-item-checkbox' type='checkbox'";
            if ( ! empty( $menu_item->front_or_home ) ) {
                $output .= ' add-to-top';
            }
            $output .= "name='menu-item[{$possible_object_id}][menu_item_object_id]' value='{$this->_esc_attr( $menu_item->object_id )}' /></dd>";
            $output .= "<input type='hidden' class='menu-item-db-id' name='menu-item[{$possible_object_id}][menu-item-db-id]' value='{$possible_db_id}'/>";
            $output .= "<input type='hidden' class='menu-item-object' name='menu-item[{$possible_object_id}][menu-item-object]' value='{$this->_esc_attr( $menu_item->object )}'/>";
            $output .= "<input type='hidden' class='menu-item-parent-id' name='menu-item[{$possible_object_id}][menu-item-parent-id]' value='{$this->_esc_attr( $menu_item->menu_item_parent )}'/>";
            $output .= "<input type='hidden' class='menu-item-type' name='menu-item[{$possible_object_id}][menu-item-type]' value='{$this->_esc_attr( $menu_item->type )}'/>";
            $output .= "<input type='hidden' class='menu-item-title' name='menu-item[{$possible_object_id}][menu-item-title]' value='{$this->_esc_attr( $menu_item->title ) }'/>";
            $output .= "<input type='hidden' class='menu-item-url' name='menu-item[{$possible_object_id}][menu-item-url]' value='{$this->_esc_attr( $menu_item->url )}'/>";
            $output .= "<input type='hidden' class='menu-item-target' name='menu-item[{$possible_object_id}][menu-item-target]' value='{$this->_esc_attr( $menu_item->target )}'/>";
            $output .= "<input type='hidden' class='menu-item-attr-title' name='menu-item[{$possible_object_id}][menu-item-attr-title]' value='{$this->_esc_attr( $menu_item->attr_title ) }'/>";
            $output .= "<input type='hidden' class='menu-item-classes' name='menu-item[{$possible_object_id}][menu-item-classes]' value='{$this->_esc_attr( implode( ' ', $menu_item->classes ) )}'/>";
            $output .= "<input type='hidden' class='menu-item-xfn' name='menu-item[{$possible_object_id}][menu-item-xfn]' value='{$this->_esc_attr( $menu_item->xfn )}'/>";
        }
    }
}else{die;}

