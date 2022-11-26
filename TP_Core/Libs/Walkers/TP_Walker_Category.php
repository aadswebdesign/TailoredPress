<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-5-2022
 * Time: 22:26
 */
namespace TP_Core\Libs\Walkers;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Taxonomy\_taxonomy_07;
use TP_Core\Traits\Templates\_link_template_02;
if(ABSPATH){
    class TP_Walker_Category extends TP_Walker {
        use _filter_01, _I10n_01, _formats_07, _formats_08;
        use _methods_01, _taxonomy_02, _taxonomy_07, _link_template_02;
        private $__alt;
        public $tree_type = 'category';
        public $db_fields = ['parent' => 'parent','id' => 'term_id',];
        public function start_lvl( &$output, $depth = 0, ...$args):string {
            if ( 'list' !== $args['style'] ) return;
            $indent  = str_repeat( "\t", $depth );
            $output .= "$indent<ul class='children'>\n";
        }//55
        public function end_lvl( &$output, $depth = 0, ...$args ):string {
            if ( 'list' !== $args['style'] ) return;
            $indent  = str_repeat( "\t", $depth );
            $output .= "$indent</ul>\n";
        }//76
        public function start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string{
            $name = '';
            $category = $data_object;
            $cat_name = $this->_apply_filters( 'list_cats', $this->_esc_attr( $category->name ), $category );
            if ( '' === $cat_name ) return;
            $atts = [];
            $atts['href'] = $this->_get_term_link( $category );
            if ( $args['use_desc_for_title'] && ! empty( $category->description ) )
                $atts['title'] = strip_tags( $this->_apply_filters( 'category_description', $category->description, $category ) );
            $atts = $this->_apply_filters( 'category_list_link_attributes', $atts, $category, $depth, $args, $current_object_id );
            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
                    $value = ( 'href' === $attr ) ? $this->_esc_url( $value ) : $this->_esc_attr( $value );
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $link = sprintf('<a%s>%s</a>',$attributes, $cat_name);
            if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
                $link .= ' ';
                if ( empty( $args['feed_image'] ) ) $link .= '(';
                $link .= "<a href='{$this->_esc_url( $this->_get_term_feed_link( $category, $category->taxonomy, $args['feed_type'] ) )}'";
                if ( empty( $args['feed'] ) ) {
                    /* translators: %s: Category name. */
                    $alt_data = sprintf( $this->__( 'Feed for all posts filed under %s' ), $cat_name );
                    $this->__alt = " alt='$alt_data'";
                } else {
                    $this->__alt   = " alt='{$args['feed']}'";
                    $name  = $args['feed'];
                    $link .= empty( $args['title'] ) ? '' : $args['title'];
                }
                $link .= '>';
                if ( empty( $args['feed'] ) ) {
                    /* translators: %s: Category name. */
                    $alt_data = sprintf( $this->__( 'Feed for all posts filed under %s' ), $cat_name );
                    $this->__alt = " alt='$alt_data'";
                } else {
                    $this->__alt   = ' alt="' . $args['feed'] . '"';
                    $name  = $args['feed'];
                    $link .= empty( $args['title'] ) ? '' : $args['title'];
                }
                $link .= '>';
                if ( empty( $args['feed_image'] ) ) $link .= $name;
                else $link .= "<img src='{$this->_esc_url( $args['feed_image'] )}' $this->__alt />";
                $link .= '</a>';
                if ( empty( $args['feed_image'] ) ) $link .= ')';
            }
            if ( ! empty( $args['show_count'] ) )
                $link .= " ({$this->_number_format_i18n( $category->count )})";
            if ( 'list' === $args['style'] ) {
                $output     .= "\t<li";
                $css_classes = ['cat-item','cat-item-' . $category->term_id,];
                if ( ! empty( $args['current_category'] ) ) {
                    $_current_terms = $this->_get_terms(
                        ['taxonomy' => $category->taxonomy,'include' => $args['current_category'],'hide_empty' => false,]
                    );
                    foreach ((array)$_current_terms as $_current_term ) {
                        if ( $category->term_id === $_current_term->term_id ) {
                            $css_classes[] = 'current-cat';
                            $link          = str_replace( '<a', '<a aria-current="page"', $link );
                        } elseif ( $category->term_id === $_current_term->parent )
                            $css_classes[] = 'current-cat-parent';
                        while ( $_current_term->parent ) {
                            if ( $category->term_id === $_current_term->parent ) {
                                $css_classes[] = 'current-cat-ancestor';
                                break;
                            }
                            $_current_term = $this->_get_term( $_current_term->parent, $category->taxonomy );
                        }
                    }
                }
                $css_classes = implode( ' ', $this->_apply_filters( 'category_css_class', $css_classes, $category, $depth, $args ) );
                $css_classes = $css_classes ? ' class="' . $this->_esc_attr( $css_classes ) . '"' : '';
                $output .= $css_classes;
                $output .= ">$link\n";
            }elseif ( isset( $args['separator'] ) )
                $output .= "\t$link" . $args['separator'] . "\n";
            else  $output .= "\t$link<br />\n";
        }//101
        public function end_el( &$output, $data_object, $depth = 0, ...$args):string{
            if ( 'list' !== $args['style'] ) return;
            $output .= "</li>\n";
        }//267
    }
}else die;