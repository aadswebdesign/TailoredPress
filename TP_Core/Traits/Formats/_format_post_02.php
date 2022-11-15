<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-5-2022
 * Time: 00:17
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    trait _format_post_02{
        /**
         * @description Remove the post format prefix from the name property of the term objects created by get_terms().
         * @param TP_Term $terms
         * @param $taxonomies
         * @param $args
         * @return TP_Term
         */
        protected function _post_format_get_terms( TP_Term $terms, $taxonomies, $args ):TP_Term{
            if ( in_array( 'post_format', (array) $taxonomies, true ) ) {
                if ( isset( $args['fields'] ) && 'names' === $args['fields'] ) {
                    foreach ( $terms as $order => $name )
                        $terms[ $order ] = $this->_get_post_format_string( str_replace( 'post-format-', '', $name ) );
                } else {
                    foreach ( (array) $terms as $order => $term ) {
                        if ( isset( $term->taxonomy ) && 'post_format' === $term->taxonomy )
                            $terms[ $order ]->name = $this->_get_post_format_string( str_replace( 'post-format-', '', $term->slug ) );
                    }
                }
            }
            return $terms;
        }//234
        /**
         * @description Remove the post format prefix from the name property of the term objects created by tp_get_object_terms().
         * @param TP_Term $terms
         * @return TP_Term
         */
        protected function _post_format_tp_get_object_terms( TP_Term $terms ):TP_Term{
            foreach ( (array) $terms as $order => $term ) {
                if ( isset( $term->taxonomy ) && 'post_format' === $term->taxonomy )
                    $terms[ $order ]->name = $this->_get_post_format_string( str_replace( 'post-format-', '', $term->slug ) );
            }
            return $terms;
        }//260
    }
}else die;