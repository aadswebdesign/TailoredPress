<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-9-2022
 * Time: 04:59
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Templates\_general_template_09;
if(ABSPATH){
    class TP_Walker_Category_Checklist extends TP_Walker {
        use _filter_01;
        use _formats_08;
        use _general_template_09;
        public function __construct(){
            $this->db_fields['parent'] = 'parent';
            $this->db_fields['id'] = ['term_id'];
        }
        public function start_lvl( &$output, $depth = 0, ...$args):void{
            $indent  = str_repeat( "\t", $depth );
            $output .= "$indent<ul class='children'>\n";
        }
        public function end_lvl( &$output, $depth = 0, ...$args ):void{
            $indent  = str_repeat( "\t", $depth );
            $output .= "$indent</ul>\n";
        }
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):void{
            $category = $data_object;
            if ( empty( $args['taxonomy'] ) ) { $taxonomy = 'category';}
            else { $taxonomy = $args['taxonomy'];}
            if ( 'category' === $taxonomy ){ $name = 'post_category';}
            else {$name = 'tax_input[' . $taxonomy . ']';}
            $args['popular_cats'] = ! empty( $args['popular_cats'] ) ? array_map( 'intval', $args['popular_cats'] ) : [];
            $class = in_array( $category->term_id, $args['popular_cats'], true ) ? ' class="popular-category"' : '';
            $args['selected_cats'] = ! empty( $args['selected_cats'] ) ? array_map( 'intval', $args['selected_cats'] ) : [];
            if ( ! empty( $args['list_only'] ) ) {
                $aria_checked = " aria-checked='false'";
                $inner_class  = 'category';

                if ( in_array( $category->term_id, $args['selected_cats'], true ) ) {
                    $inner_class .= ' selected';
                    $aria_checked = " aria-checked='true'";
                }
                $output .= "\n <li  $class>";
                $output .= "<div class='{$inner_class}' data-term_id='{$category->term_id}' tabindex='0' role='checkbox' $aria_checked>";
                $output .= $this->_esc_html( $this->_apply_filters( 'the_category', $category->name, '', '' ) );
                $output .= "</div>";
            }else{
                $is_selected = in_array( $category->term_id, $args['selected_cats'], true );
                $is_disabled = ! empty( $args['disabled'] );
                $output .= "\n<li id='{$taxonomy}-{$category->term_id}' $class>";
                $output .= "<dt><label class='select-item'>{$this->_esc_html( $this->_apply_filters( 'the_category', $category->name, '', '' ) )}</label></dt>";
                $output .= "<dd><input id='in_{$taxonomy}_{$category->term_id}' name='{$name}[]' value='{$category->term_id}' type='checkbox'";
                $output .= $this->_get_checked( $is_selected, true );
                $output .= $this->_get_disabled( $is_disabled, true );
                $output .= "/></dd>";
            }
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):void{
            $output .= "</li>\n";
        }
    }
}else{die;}