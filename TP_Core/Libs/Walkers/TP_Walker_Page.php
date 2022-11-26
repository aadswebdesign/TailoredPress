<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 09:25
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Templates\_link_template_01;
if(ABSPATH){
    class TP_Walker_Page extends TP_Walker {
        use _filter_01, _option_01, _I10n_01, _formats_07;
        use _formats_08, _post_01, _methods_01, _link_template_01;
        public $tree_type = 'page';
        public $db_fields = ['parent' => 'post_parent','id' => 'ID',];
        public function start_lvl( &$output, $depth = 0, ...$args ):string {
            if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
                $t = "\t";
                $n = "\n";
            } else {
                $t = '';
                $n = '';
            }
            $indent  = str_repeat( $t, $depth );
            $output .= "{$n}{$indent}<ul class='children'>{$n}";
        }
        public function end_lvl( &$output, $depth = 0, ...$args ):string {
            if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
                $t = "\t";
                $n = "\n";
            } else {
                $t = '';
                $n = '';
            }
            $indent  = str_repeat( $t, $depth );
            $output .= "{$indent}</ul>{$n}";
        }
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string{
            $page            = $data_object;
            $current_page_id = $current_object_id;
            if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) $t = "\t";
            else $t = '';
            if ( $depth ) $indent = str_repeat( $t, $depth );
             else $indent = '';
            $css_class = array( 'page_item', 'page-item-' . $page->ID );
            if ( isset( $args['pages_with_children'][ $page->ID ] ) )
                $css_class[] = 'page_item_has_children';
            if ( ! empty( $current_page_id ) ) {
                $_current_page = $this->_get_post( $current_page_id );
                $current_page = null;
                if($_current_page instanceof \stdClass){
                    $current_page = $_current_page;
                }
                if ( $current_page && in_array( $page->ID, $current_page->ancestors, true ) )
                    $css_class[] = 'current_page_ancestor';
                if ( $page->ID === $current_page_id ) $css_class[] = 'current_page_item';
                elseif ( $current_page && $page->ID === $current_page->post_parent )
                    $css_class[] = 'current_page_parent';
            } elseif ( $this->_get_option( 'page_for_posts' ) === $page->ID )
                $css_class[] = 'current_page_parent';
            $css_classes = implode( ' ', $this->_apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page_id ) );
            $css_classes = $css_classes ? " class='{$this->_esc_attr( $css_classes )}'" : '';
            if ( '' === $page->post_title )
                $page->post_title = sprintf( $this->__( '#%d (no title)' ), $page->ID );
            $args['link_before'] = empty( $args['link_before'] ) ? '' : $args['link_before'];
            $args['link_after']  = empty( $args['link_after'] ) ? '' : $args['link_after'];
            $atts                 = [];
            $atts['href']         = $this->_get_permalink( $page->ID );
            $atts['aria-current'] = ( $page->ID === $current_page_id ) ? 'page' : '';
            $atts = $this->_apply_filters( 'page_menu_link_attributes', $atts, $page, $depth, $args, $current_page_id );
            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
                    $value       = ( 'href' === $attr ) ? $this->_esc_url( $value ) : $this->_esc_attr( $value );
                    $attributes .= " $attr=$value ";
                }
            }
            $output .= $indent . sprintf(
                    '<li%s><a%s>%s%s%s</a>',
                    $css_classes,$attributes,$args['link_before'],
                    $this->_apply_filters( 'the_title', $page->post_title, $page->ID ),
                    $args['link_after']
                );
            if ( ! empty( $args['show_date'] ) ) {
                if ( 'modified' === $args['show_date'] ) $time = $page->post_modified;
                else $time = $page->post_date;
                $date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
                $output     .= ' ' . $this->_mysql2date( $date_format, $time );
            }
        }
        public function end_el( &$output, $data_object, $depth = 0, ...$args ):string{
            if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) $n = "\n";
            else $n = '';
            $output .= "</li>{$n}";
        }
    }
}else die;