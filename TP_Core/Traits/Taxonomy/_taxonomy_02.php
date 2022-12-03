<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Term;
use TP_Core\Libs\Queries\TP_Tax_Query;
use TP_Core\Libs\Queries\TP_Term_Query;
use TP_Core\Traits\Inits\_init_taxonomy;

if(ABSPATH){
    trait _taxonomy_02 {
        use _init_error;
        use _init_db;
        use _init_taxonomy;
        /**
         * @description Remove an already registered taxonomy from an object type.
         * @param $taxonomy
         * @param $object_type
         * @return bool
         */
        protected function _unregister_taxonomy_for_object_type( $taxonomy, $object_type ):bool{
            $this->tp_taxonomies = $this->_init_taxonomy();
            if ( ! isset( $this->tp_taxonomies[ $taxonomy ] ) ) return false;
            if ( ! $this->_get_post_type_object( $object_type ) ) return false;
            $key = array_search( $object_type, $this->tp_taxonomies[ $taxonomy ]->object_type, true );
            if ( false === $key ) return false;
            unset( $this->tp_taxonomies[ $taxonomy ]->object_type[ $key ] );
            $this->_do_action( 'unregistered_taxonomy_for_object_type', $taxonomy, $object_type );
            return true;
        }//790
        /**
         * @description Retrieve object IDs of valid taxonomy and term.
         * @param $term_ids
         * @param $taxonomies
         * @param array $args
         * @return array|TP_Error
         */
        protected function _get_objects_in_term( $term_ids, $taxonomies, $args = array() ){
            $this->tpdb = $this->_init_db();
            if ( ! is_array( $term_ids ) ) $term_ids = array( $term_ids );
            if ( ! is_array( $taxonomies ) )  $taxonomies = array( $taxonomies );
            foreach ( (array) $taxonomies as $taxonomy ) {
                if ( ! $this->_taxonomy_exists( $taxonomy ) )
                    return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            }
            $defaults = ['order' => 'ASC'];
            $args     = $this->_tp_parse_args( $args, $defaults );
            $order = ( 'desc' === strtolower( $args['order'] ) ) ? 'DESC' : 'ASC';
            $term_ids = array_map( 'intval', $term_ids );
            $taxonomies = "'" . implode( "', '", array_map( 'esc_sql', $taxonomies ) ) . "'";
            $term_ids   = "'" . implode( "', '", $term_ids ) . "'";
            $sql = TP_SELECT . " tr.object_id FROM $this->tpdb->term_relationships AS tr INNER JOIN $this->tpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies) AND tt.term_id IN ($term_ids) ORDER BY tr.object_id $order";
            $last_changed = $this->_tp_cache_get_last_changed( 'terms' );
            $cache_key    = 'get_objects_in_term:' . md5( $sql ) . ":$last_changed";
            $cache        = $this->_tp_cache_get( $cache_key, 'terms' );
            if ( false === $cache ) {
                $object_ids = $this->tpdb->get_col( $sql );
                $this->_tp_cache_set( $cache_key, $object_ids, 'terms' );
            } else $object_ids = (array) $cache;
            if ( ! $object_ids )  return [];
            return $object_ids;
        }//847
        /**
         * @description Given a taxonomy query, generates SQL to be appended to a main query.
         * @param $tax_query
         * @param $primary_table
         * @param $primary_id_column
         * @return array
         */
        protected function _get_tax_sql( $tax_query, $primary_table, $primary_id_column ):array{
            $this->tp_tax_query_obj = new TP_Tax_Query( $tax_query );
            return $this->tp_tax_query_obj->get_sql( $primary_table, $primary_id_column );
        }//902
        /**
         * @description Get all Term data from database by Term ID.
         * @param $term
         * @param string $taxonomy
         * @param string $output
         * @param string $filter
         * @return array|bool|null|TP_Error|TP_Term
         */
        protected function _get_term( $term, $taxonomy = '', $output = OBJECT, $filter = 'raw' ){
            if ( empty( $term ) ) return new TP_Error( 'invalid_term', $this->__( 'Empty Term.' ) );
            if ( $taxonomy && ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            if ( $term instanceof TP_Term )
                $_term = $term;
            elseif ( is_object( $term ) ) {
                if ( empty( $term->filter ) || 'raw' === $term->filter ) {
                    $_term = $this->_sanitize_term( $term, $taxonomy, 'raw' );
                    $_term = new TP_Term( $_term );
                } else $_term = TP_Term::get_instance( $term->term_id );
            } else $_term = TP_Term::get_instance( $term, $taxonomy );
            if ( $this->_init_error( $_term ) ) return $_term;
            elseif ( ! $_term ) return null;
            $taxonomy = $_term->taxonomy;
            $_term = $this->_apply_filters( 'get_term', $_term, $taxonomy );
            $_term = $this->_apply_filters( "get_{$taxonomy}", $_term, $taxonomy );
            if ( ! ( $_term instanceof TP_Term ) ) return $_term;
            $_term->filter( $filter );
            if ( ARRAY_A === $output ) return $_term->to_array();
            elseif ( ARRAY_N === $output ) return array_values( $_term->to_array() );
            return $_term;
        }//953
        /**
         * @description Get all Term data from database by Term field and data.
         * @param $field
         * @param $value
         * @param string $taxonomy
         * @param string $output
         * @param string $filter
         * @return array|bool|mixed|null|TP_Error|TP_Term
         */
        protected function _get_term_by( $field, $value, $taxonomy = '', $output = OBJECT, $filter = 'raw' ){
            if ( 'term_taxonomy_id' !== $field && ! $this->_taxonomy_exists( $taxonomy ) )
                return false;
            if ( 'slug' === $field || 'name' === $field ) {
                $value = (string) $value;
                if ($value === '') return false;
            }
            if ( 'id' === $field || 'ID' === $field || 'term_id' === $field ) {
                $term = $this->_get_term( (int) $value, $taxonomy, $output, $filter );
                if (null === $term || $this->_init_error( $term )) $term = false;
                return $term;
            }
            $args = ['get' => 'all','number' => 1,'taxonomy' => $taxonomy,'update_term_meta_cache' => false,'orderby' => 'none','suppress_filter' => true,];
            switch ( $field ) {
                case 'slug':
                    $args['slug'] = $value;
                    break;
                case 'name':
                    $args['name'] = $value;
                    break;
                case 'term_taxonomy_id':
                    $args['term_taxonomy_id'] = $value;
                    unset( $args['taxonomy'] );
                    break;
                default:
                    return false;
            }
            $terms = $this->_get_terms( $args );
            if (empty( $terms ) || $this->_init_error( $terms )) return false;
            $term = array_shift( $terms );
            if ( 'term_taxonomy_id' === $field ) $taxonomy = $term->taxonomy;
            return $this->_get_term( $term, $taxonomy, $output, $filter );
        }//1072
        /**
         * @description Merge all term children into a single array of their IDs.
         * @param $term_id
         * @param $taxonomy
         * @return array|TP_Error
         */
        protected function _get_term_tax_children( $term_id, $taxonomy ){
            if ( ! $this->_taxonomy_exists( $taxonomy ) ) return new TP_Error('invalid_taxonomy', $this->__( 'Invalid taxonomy.' ));
            $term_id = (int) $term_id;
            $terms = $this->_get_term_hierarchy( $taxonomy );
            if (!isset($terms[ $term_id ])) return [];
            $children = $terms[ $term_id ];
            foreach ( (array) $terms[ $term_id ] as $child ) {
                if ( $term_id === $child ) continue;
                if ( isset( $terms[ $child ] ) ) $children = $this->_tp_array_merge( $children, $this->_get_term_tax_children( $child, $taxonomy ) );
            }
            return $children;
        }//1149
        /**
         * @description Get sanitized Term field.
         * @param $field
         * @param $term
         * @param string $taxonomy
         * @param string $context
         * @return array|bool|null|string|TP_Error|TP_Term
         */
        protected function _get_term_field( $field, $term, $taxonomy = '', $context = 'display' ){
            $term = $this->_get_term( $term, $taxonomy );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! is_object( $term ) ) return '';
            if ( ! isset( $term->$field ) ) return '';
            return $this->_sanitize_term_field( $field, $term->$field, $term->term_id, $term->taxonomy, $context );
        }//1194
        /**
         * @description Sanitizes Term for editing.
         * @param $id
         * @param $taxonomy
         * @return array|bool|null|string|TP_Error|TP_Term
         */
        protected function _get_term_to_edit( $id, $taxonomy ){
            $term = $this->_get_term( $id, $taxonomy );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! is_object( $term ) ) return '';
            return $this->_sanitize_term( $term, $taxonomy, 'edit' );
        }//1223
        /**
         * @description Retrieves the terms in a given taxonomy or list of taxonomies.
         * @param \array[] ...$args
         * @return array|null|string|TP_Error
         */
        protected function _get_terms(array ...$args){
            $term_query = new TP_Term_Query();
            $defaults = ['suppress_filter' => false,];
            $_args          = $this->_tp_parse_args( $args );
            $key_intersect  = array_intersect_key( $term_query->query_var_defaults, (array) $_args );
            if ( $key_intersect ) {
                $taxonomies       = $args;
                $args             = $this->_tp_parse_args($defaults );
                $args['taxonomy'] = $taxonomies;
            } else {
                $args = $this->_tp_parse_args( $args, $defaults );
                if ( isset( $args['taxonomy'] ) && null !== $args['taxonomy'] )
                    $args['taxonomy'] = (array) $args['taxonomy'];
            }
            if ( ! empty( $args['taxonomy'] ) ) {
                foreach ( $args['taxonomy'] as $taxonomy ) {
                    if ( ! $this->_taxonomy_exists( $taxonomy ) ) return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
                }
            }
            $suppress_filter = $args['suppress_filter'];
            unset( $args['suppress_filter'] );
            $terms = $term_query->query_term( $args );
            if ( ! is_array( $terms ) ) return $terms;
            if ( $suppress_filter ) return $terms;
            return $this->_apply_filters( 'get_terms', $terms, $term_query->query_vars['taxonomy'], $term_query->query_vars, $term_query );
        }//1271/1292
        //@description Retrieves the terms in a given taxonomy or list of taxonomies.
        //protected function _get_taxonomy_terms(array ...$args){return '';}//1292
    }
}else die;