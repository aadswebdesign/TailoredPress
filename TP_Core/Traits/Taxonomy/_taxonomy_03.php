<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _taxonomy_03 {
        use _init_db;
        /**
         * @description Adds metadata to a term.
         * @param $term_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return TP_Error
         */
        protected function _add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ):TP_Error{
            if ( $this->_tp_term_is_shared( $term_id ) )
                return new TP_Error( 'ambiguous_term_id', $this->__( 'Term meta cannot be added to terms that are shared between taxonomies.' ), $term_id );
            return $this->_add_metadata( 'term', $term_id, $meta_key, $meta_value, $unique );
        }//1371
        /**
         * @description Removes metadata matching criteria from a term.
         * @param $term_id
         * @param $meta_key
         * @param string $meta_value
         * @return mixed
         */
        protected function _delete_term_meta( $term_id, $meta_key, $meta_value = '' ){
            return $this->_delete_metadata( 'term', $term_id, $meta_key, $meta_value );
        }//1391
        /**
         * @description Retrieves metadata for a term.
         * @param $term_id
         * @param string $key
         * @param bool $single
         * @return mixed
         */
        protected function _get_term_meta( $term_id, $key = '', $single = false ){
            return $this->_get_metadata( 'term', $term_id, $key, $single );
        }//1411
        /**
         * @description Updates term metadata.
         * @param $term_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return TP_Error
         */
        protected function _update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ):TP_Error{
            if ( $this->_tp_term_is_shared( $term_id ) )
                return new TP_Error( 'ambiguous_term_id', $this->__( 'Term meta cannot be added to terms that are shared between taxonomies.' ), $term_id );
            return $this->_update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
        }//1435
        /**
         * @description Updates metadata cache for list of term IDs.
         * @param $term_ids
         * @return mixed
         */
        protected function _update_term_meta_cache( $term_ids ){
            return $this->_update_meta_cache( 'term', $term_ids );
        }//1454
        /**
         * @description Get all meta data, including meta IDs, for the given term ID.
         * @param $term_id
         * @return array|null
         */
        protected function _has_term_meta( $term_id ):array{
            $this->tpdb = $this->_init_db();
            $check = $this->_tp_check_term_meta_support_pre_filter( null );
            if ( null !== $check ) return $check;
            return $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " meta_key, meta_value, meta_id, term_id FROM $this->tpdb->term_meta WHERE term_id = %d ORDER BY meta_key,meta_id", $term_id ), ARRAY_A );
        }//1468
        /**
         * @description Registers a meta key for terms.
         * @param $taxonomy
         * @param $meta_key
         * @param array $args
         * @return string
         */
        protected function _register_term_meta( $taxonomy, $meta_key, array $args ):string{
            $args['object_subtype'] = $taxonomy;
            return $this->_register_meta( 'term', $meta_key, $args );
        }//1491
        /**
         * @description Unregisters a meta key for terms.
         * @param $taxonomy
         * @param $meta_key
         * @return mixed
         */
        protected function _unregister_term_meta( $taxonomy, $meta_key ){
            return $this->_unregister_meta_key( 'term', $meta_key, $taxonomy );
        }//1508
        /**
         * @description Determines whether a taxonomy term exists.
         * @param $term
         * @param string $taxonomy
         * @param null $parent
         * @return array|null
         */
        protected function _term_exists($term, $taxonomy = '', $parent = null ):array{
            $this->tpdb = $this->_init_db();
            if ( null === $term ) return null;
            $select     = TP_SELECT . " term_id FROM $this->tpdb->terms as t WHERE ";
            $tax_select = TP_SELECT . " tt.term_id, tt.term_taxonomy_id FROM $this->tpdb->terms AS t INNER JOIN $this->tpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE ";
            if ( is_int( $term ) ) {
                if ( 0 === $term ) return null;
                $where = 't.term_id = %d';
                if ( ! empty( $taxonomy ) )
                    return $this->tpdb->get_row( $this->tpdb->prepare( $tax_select . $where . ' AND tt.taxonomy = %s', $term, $taxonomy ), ARRAY_A );
                else return $this->tpdb->get_var( $this->tpdb->prepare( $select . $where, $term ) );
            }
            $term = trim( $this->_tp_unslash( $term ) );
            $slug = $this->_sanitize_title( $term );
            $where             = 't.slug = %s';
            $else_where        = 't.name = %s';
            $where_fields      = [$slug];
            $else_where_fields = [$term];
            $orderby           = 'ORDER BY t.term_id ASC';
            $limit             = 'LIMIT 1';
            if ( ! empty( $taxonomy ) ) {
                if ( is_numeric( $parent ) ) {
                    $parent              = (int) $parent;
                    $where_fields[]      = $parent;
                    $else_where_fields[] = $parent;
                    $where              .= ' AND tt.parent = %d';
                    $else_where         .= ' AND tt.parent = %d';
                }
                $where_fields[]      = $taxonomy;
                $else_where_fields[] = $taxonomy;
                $result = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " tt.term_id, tt.term_taxonomy_id FROM $this->tpdb->terms AS t INNER JOIN $this->tpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE $where AND tt.taxonomy = %s $orderby $limit", $where_fields ), ARRAY_A );
                if ( $result ) return $result;
                return $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " tt.term_id, tt.term_taxonomy_id FROM $this->tpdb->terms AS t INNER JOIN $this->tpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE $else_where AND tt.taxonomy = %s $orderby $limit", $else_where_fields ), ARRAY_A );
            }
            $result = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " term_id FROM $this->tpdb->terms as t WHERE $where $orderby $limit", $where_fields ) );
            if ( $result ) return $result;
            return $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " term_id FROM $this->tpdb->terms as t WHERE $else_where $orderby $limit", $else_where_fields ) );
        }//1533
        /**
         * @description Check if a term is an ancestor of another term.
         * @param $term1
         * @param $term2
         * @param $taxonomy
         * @return bool
         */
        protected function _term_is_ancestor_of( $term1, $term2, $taxonomy ):bool{
            if ( ! isset( $term1->term_id ) )
                $term1 = $this->_get_term( $term1, $taxonomy );
            if ( ! isset( $term2->parent ) )
                $term2 = $this->_get_term( $term2, $taxonomy );
            if ( empty( $term1->term_id ) || empty( $term2->parent ) )
                return false;
            if ( $term2->parent === $term1->term_id ) return true;
            return $this->_term_is_ancestor_of( $term1, $this->_get_term( $term2->parent, $taxonomy ), $taxonomy );
        }//1607
    }
}else die;