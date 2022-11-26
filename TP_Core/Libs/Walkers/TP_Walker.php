<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 22:51
 */
namespace TP_Core\Libs\Walkers;
if(ABSPATH){
    abstract class TP_Walker implements Walker_Interface {
        public $tree_type;
        public $db_fields;
        public $max_pages = 1;
        public $has_children;
        public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ):string{
            if ( ! $element ) return;
            $id_field = $this->db_fields['id'];
            $id       = $element->$id_field;
            $this->has_children = ! empty( $children_elements[ $id ] );
            if ( isset( $args[0] ) && is_array( $args[0] ) )
                $args[0]['has_children'] = $this->has_children; // Back-compat.
            $this->start_el( $output, $element, $depth, ...array_values( $args ) );
            if ( ( 0 === $max_depth || $max_depth > $depth + 1 ) && isset( $children_elements[ $id ] ) ) {
                foreach ( $children_elements[ $id ] as $child ) {
                    if ( ! isset( $new_level ) ) {
                        $new_level = true;
                        $this->start_lvl( $output, $depth, ...array_values( $args ) );
                    }
                    $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
                }
                unset( $children_elements[ $id ] );
            }
           if ( isset( $new_level ) && $new_level )
                $this->end_lvl( $output, $depth, ...array_values( $args ) );
            $this->end_el( $output, $element, $depth, ...array_values( $args ) );
        }//132
        public function walk( $elements, $max_depth, ...$args ):string {
            $output = '';
            if ( $max_depth < -1 || empty( $elements ) ) return $output;
            $parent_field = $this->db_fields['parent'];
            if ( -1 === $max_depth ) {
                $empty_array = array();
                foreach ( $elements as $e )
                    $this->display_element( $e, $empty_array, 1, 0, $args, $output );
                return $output;
            }
            $top_level_elements = [];
            $children_elements  = [];
            foreach ( $elements as $e ) {
                if ( empty( $e->$parent_field ) ) $top_level_elements[] = $e;
                else $children_elements[ $e->$parent_field ][] = $e;
            }
            if ( empty( $top_level_elements ) ) {
                $first = array_slice( $elements, 0, 1 );
                $root  = $first[0];
                $top_level_elements = [];
                $children_elements  = [];
                foreach ( $elements as $e ) {
                    if ( $root->$parent_field === $e->$parent_field )
                        $top_level_elements[] = $e;
                    else $children_elements[ $e->$parent_field ][] = $e;
                }
            }
            foreach ( $top_level_elements as $e )
                $this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
            if ( ( 0 === $max_depth ) && count( $children_elements ) > 0 ) {
                $empty_array = [];
                foreach ( $children_elements as $orphans ) {
                    foreach ( $orphans as $op ) $this->display_element( $op, $empty_array, 1, 0, $args, $output );
                }
            }
            return $output;
        }//190
        public function paged_walk( $elements, $max_depth, $page_num, $per_page, ...$args ):string{
            static $total_top, $end;
            if ( empty( $elements ) || $max_depth < -1 ) return '';
            $output = '';
            $parent_field = $this->db_fields['parent'];
            $count = -1;
            if ( -1 === $max_depth ) $total_top = count( $elements );
            if ( $page_num < 1 || $per_page < 0 ) {
                $paging = false;
                $start  = 0;
                if ( -1 === $max_depth )  $end = $total_top;
                $this->max_pages = 1;
            } else {
                $paging = true;
                $start  = ( (int) $page_num - 1 ) * (int) $per_page;
                $end    = $start + $per_page;
                if ( -1 === $max_depth )
                    $this->max_pages = ceil( $total_top / $per_page );
            }
            if ( -1 === $max_depth ) {
                if ( ! empty( $args[0]['reverse_top_level'] ) ) {
                    $elements = array_reverse( $elements );
                    $old_start = $start;
                    $start    = $total_top - $end;
                    $end      = $total_top - $old_start;
                }
                $empty_array = array();
                foreach ( $elements as $e ) {
                    $count++;
                    if ( $count < $start ) continue;
                    if ( $count >= $end ) break;
                    $this->display_element( $e, $empty_array, 1, 0, $args, $output );
                }
                return $output;
            }
            $top_level_elements = array();
            $children_elements  = array();
            foreach ( $elements as $e ) {
                if ( empty( $e->$parent_field ) ) $top_level_elements[] = $e;
                else $children_elements[ $e->$parent_field ][] = $e;
            }
            $total_top = count( $top_level_elements );
            if ( $paging ) $this->max_pages = ceil( $total_top / $per_page );
            else  $end = $total_top;
            if ( ! empty( $args[0]['reverse_top_level'] ) ) {
                $top_level_elements = array_reverse( $top_level_elements );
                $old_start           = $start;
                $start              = $total_top - $end;
                $end                = $total_top - $old_start;
            }
            if ( ! empty( $args[0]['reverse_children'] ) ) {
                foreach ( $children_elements as $parent => $children )
                    $children_elements[ $parent ] = array_reverse( $children );
            }
            foreach ( $top_level_elements as $e ) {
                $count++;
                if ( $end >= $total_top && $count < $start )
                    $this->unset_children( $e, $children_elements );
                if ( $count < $start ) continue;
                if ( $count >= $end ) break;
                 $this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
            }
            if ( $end >= $total_top && count( $children_elements ) > 0 ) {
                $empty_array = array();
                foreach ( $children_elements as $orphans ) {
                    foreach ( $orphans as $op )
                        $this->display_element( $op, $empty_array, 1, 0, $args, $output );
                }
            }
            return $output;
        }//286
        public function get_number_of_root_elements( $elements ):string{
            $num          = 0;
            $parent_field = $this->db_fields['parent'];
            foreach ( $elements as $e ) {
                if ( empty( $e->$parent_field ) ) $num++;
            }
            return $num;
        }//412
        public function unset_children( $element, &$children_elements ):string{
            if ( ! $element || ! $children_elements ) return;
            $id_field = $this->db_fields['id'];
            $id       = $element->$id_field;
            if ( ! empty( $children_elements[ $id ] ) && is_array( $children_elements[ $id ] ) ) {
                foreach ( (array) $children_elements[ $id ] as $child )
                    $this->unset_children( $child, $children_elements );
            }
            unset( $children_elements[ $id ] );
        }//432
    }
}else die;