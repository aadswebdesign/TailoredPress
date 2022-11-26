<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-5-2022
 * Time: 23:27
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class TP_Walker_CategoryDropdown extends TP_Walker{
        use _filter_01, _formats_07, _formats_08,_methods_01;
        public $tree_type = 'category';
        public $db_fields = ['parent' => 'parent','id' => 'term_id',];
        public function start_lvl( &$output, $depth = 0, ...$args ):string{}//not needed
        public function end_lvl( &$output, $depth = 0, ...$args ):string{}//not needed
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string{
            //todo might need a select element for holding the options?
            $category = $data_object;
            $pad      = str_repeat( '&nbsp;', $depth * 3 );
            $cat_name = $this->_apply_filters( 'list_cats', $category->name, $category );
            $output ='';
            if ( isset( $args['value_field'], $category->{$args['value_field']} ) )
                $value_field = $args['value_field'];
            else $value_field = 'term_id';
            $output .= "\t<option class='level-$depth' value='{$this->_esc_attr( $category->{$value_field} )}'>";
            if ( (string) $category->{$value_field} === (string) $args['selected'] )
                $output .= " selected='selected'";
            $output .= '>';
            $output .= $pad . $cat_name;
            if ( $args['show_count'] )
                $output .= "&nbsp;&nbsp;({$this->_number_format_i18n( $category->count )})";
            $output .= "</option>\n";
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):string{}//not needed
    }
}else die;