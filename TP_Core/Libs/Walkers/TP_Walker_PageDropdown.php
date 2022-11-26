<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 09:55
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class TP_Walker_PageDropdown extends TP_Walker{
        use _filter_01;
        use _I10n_01;
        use _formats_08;
        public $tree_type = 'page';
        public $db_fields = ['parent' => 'post_parent','id' => 'ID',];
        public function start_lvl( &$output, $depth = 0, ...$args ):string {}
        public function end_lvl( &$output, $depth = 0, ...$args ):string{}
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string{
            $page = $data_object;
            $pad  = str_repeat( '&nbsp;', $depth * 3 );
            if ( ! isset( $args['value_field'],$page->{$args['value_field']} ) )
                $args['value_field'] = 'ID';
             $output .= "\t<option class='level-$depth' value='{$this->_esc_attr( $page->{$args['value_field']} )}'";
            if ( $page->ID === $args['selected'] ) $output .= " selected='selected'";
            $output .= '>';
            $title = $page->post_title;
            if ( '' === $title )  $title = sprintf( $this->__( '#%d (no title)' ), $page->ID );
            $title = $this->_apply_filters( 'list_pages', $title, $page );
            $output .= $pad . $this->_esc_html( $title );
            $output .= "</option>\n";
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):string{}
    }
}else die;