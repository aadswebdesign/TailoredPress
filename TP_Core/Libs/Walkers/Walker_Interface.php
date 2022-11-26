<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\Walkers;
if(ABSPATH){
    interface Walker_Interface{
        public function start_lvl( &$output, $depth = 0, ...$args);
        public function end_lvl( &$output, $depth = 0, ...$args );
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args);
        public function end_el( &$output, $data_object, $depth = 0, ...$args );
        public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output );
        public function walk( $elements, $max_depth, ...$args );
        public function paged_walk( $elements, $max_depth, $page_num, $per_page, ...$args );
        public function get_number_of_root_elements( $elements );
        public function unset_children( $element, &$children_elements );
    }
}else die;