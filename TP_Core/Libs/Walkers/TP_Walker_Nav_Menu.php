<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-5-2022
 * Time: 23:32
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;

if(ABSPATH){
    class TP_Walker_Nav_Menu extends TP_Walker {
        use _filter_01;
        use _formats_07;
        use _formats_08;
        public $tree_type = ['post_type','taxonomy','custom'];
        public $db_fields = ['parent' => 'menu_item_parent','id' => 'db_id',];
        public function start_lvl( &$output, $depth = 0, ...$args):void {
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = str_repeat( $t, $depth );
            $classes = array( 'sub-menu' );
            $class_names = implode( ' ', $this->_apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
            $class_names = $class_names ? " class='{$this->_esc_attr( $class_names )}'" : '';
            $output .= "{$n}{$indent}<ul$class_names>{$n}";
        }
        public function end_lvl( &$output, $depth = 0, ...$args):void{
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent  = str_repeat( $t, $depth );
            $output .= "$indent</ul>{$n}";
        }
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):string {
            $menu_item = $data_object;
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) $t = '';
            else $t = "\t";
            $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';
            $classes   = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;
            $classes[] = 'menu-item-' . $menu_item->ID;
            $args = $this->_apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );
            $class_names = implode( ' ', $this->_apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );
            $class_names = $class_names ? " class='{$this->_esc_attr( $class_names )}'" : '';
            $id = $this->_apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );
            $id = $id ? " id='{$this->_esc_attr( $id )}'" : '';
            $output .= $indent . '<li' . $id . $class_names . '>';
            $atts           = array();
            $atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
            $atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
            if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) )
                $atts['rel'] = 'noopener';
            else $atts['rel'] = $menu_item->xfn;
            $atts['href']         = ! empty( $menu_item->url ) ? $menu_item->url : '';
            $atts['aria-current'] = $menu_item->current ? 'page' : '';
            $atts = $this->_apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );
            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
                    $value       = ( 'href' === $attr ) ? $this->_esc_url( $value ) : $this->_esc_attr( $value );
                    $attributes .= " $attr='$value'";
                }
            }
            $title = $this->_apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
            $title = $this->_apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );
            $item_output  = $args->before;
            $item_output .= '<a' . $attributes . '>';
            $item_output .= $args->link_before . $title . $args->link_after;
            $item_output .= '</a>';
            $item_output .= $args->after;
            $output .= $this->_apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):void{
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) $n = '';
            else $n = "\n";
            $output .= "</li>{$n}";
        }
    }
}else die;