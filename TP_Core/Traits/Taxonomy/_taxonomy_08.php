<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _taxonomy_08 {
        use _init_error;
        /**
         * @description Determine if the given object is associated with any of the given terms.
         * @param $object_id
         * @param $taxonomy
         * @param null $terms
         * @return bool|TP_Error
         */
        protected function _is_object_in_term( $object_id, $taxonomy, $terms = null ){
            $object_id = (int) $object_id;
            if ( ! $object_id ) return new TP_Error( 'invalid_object', $this->__( 'Invalid object ID.' ) );
            $object_terms = $this->_get_object_term_cache( $object_id, $taxonomy );
            if ( false === $object_terms ) {
                $object_terms = $this->_tp_get_object_terms( $object_id, $taxonomy, array( 'update_term_meta_cache' => false ) );
                if ( $this->_init_error( $object_terms ) ) return $object_terms;
                $this->_tp_cache_set( $object_id, $this->_tp_list_pluck( $object_terms, 'term_id' ), "{$taxonomy}_relationships" );
            }
            if ( $this->_init_error( $object_terms ) ) return $object_terms;
            if ( empty( $object_terms ) ) return false;
            if ( empty( $terms ) ) return ( ! empty( $object_terms ) );
            $terms = (array) $terms;
            $ints = array_filter( $terms, 'is_int' );
            if ( $ints ) $strs = array_diff( $terms, $ints );
            else $strs =& $terms;
            foreach ( $object_terms as $object_term ) {
                if ( $ints && in_array( $object_term->term_id, $ints, true ) ) return true;
                if ( $strs ) {
                    $numeric_strs = array_map( 'intval', array_filter( $strs, 'is_numeric' ) );
                    if ( in_array( $object_term->term_id, $numeric_strs, true ) ) return true;
                    if ( in_array( $object_term->name, $strs, true ) ) return true;
                    if ( in_array( $object_term->slug, $strs, true ) ) return true;
                }
            }
            return false;
        }//4754
        /**
         * @description Determine if the given object type is associated with the given taxonomy.
         * @param $object_type
         * @param $taxonomy
         * @return bool
         */
        protected function _is_object_in_taxonomy( $object_type, $taxonomy ):bool{
            $taxonomies = $this->_get_object_taxonomies( $object_type );
            if ( empty( $taxonomies ) ) return false;
            return in_array( $taxonomy, $taxonomies, true );
        }//4822
        /**
         * @description Get an array of ancestor IDs for a given object.
         * @param int $object_id
         * @param string $object_type
         * @param string $resource_type
         * @return mixed
         */
        protected function _get_ancestors( $object_id = 0, $object_type = '', $resource_type = '' ){
            $object_id = (int) $object_id;
            $ancestors = [];
            if ( empty( $object_id ) ) return $this->_apply_filters( 'get_ancestors', $ancestors, $object_id, $object_type, $resource_type );
            if ( ! $resource_type ) {
                if ( $this->_is_taxonomy_hierarchical( $object_type ) ) $resource_type = 'taxonomy';
                elseif ( $this->_post_type_exists( $object_type ) ) $resource_type = 'post_type';
            }
            if ( 'taxonomy' === $resource_type ) {
                $term = $this->_get_term( $object_id, $object_type );
                while ( ! $this->_init_error( $term ) && ! empty( $term->parent ) && ! in_array( $term->parent, $ancestors, true ) ) {
                    $ancestors[] = (int) $term->parent;
                    $term        = $this->_get_term( $term->parent, $object_type );
                }
            } elseif ( 'post_type' === $resource_type ) $ancestors = $this->_get_post_ancestors( $object_id );
            return $this->_apply_filters( 'get_ancestors', $ancestors, $object_id, $object_type, $resource_type );
        }//4843
        /**
         * @description Returns the term's parent's term_ID.
         * @param $term_id
         * @param $taxonomy
         * @return bool|int
         */
        protected function _tp_get_term_taxonomy_parent_id( $term_id, $taxonomy ){
            $term = $this->_get_term( $term_id, $taxonomy );
            if ( ! $term || $this->_init_error( $term ) ) {
                return false;
            }
            return (int) $term->parent;
        }//4895
        /**
         * @description  Checks the given subset of the term hierarchy for hierarchy loops.
         * @param $parent
         * @param $term_id
         * @param $taxonomy
         * @return int
         */
        protected function _tp_check_term_hierarchy_for_loops( $parent, $term_id, $taxonomy ):int{
            if ( ! $parent ) return 0;
            if ( $parent === $term_id ) return 0;
            $loop = $this->_tp_find_hierarchy_loop( 'tp_get_term_taxonomy_parent_id', $term_id, $parent, array( $taxonomy ) );
            if ( ! $loop ) return $parent; // No loop.
            if ( isset( $loop[ $term_id ] ) ) return 0;
            foreach ( array_keys( $loop ) as $loop_member )
                $this->_tp_update_term( $loop_member, $taxonomy, array( 'parent' => 0 ) );
            return $parent;
        }//4916
        /**
         * @description Determines whether a taxonomy is considered "viewable".
         * @param $taxonomy
         * @return bool
         */
        protected function _is_taxonomy_viewable( $taxonomy ):bool{
            if ( is_scalar( $taxonomy ) ) {
                $taxonomy = $this->_get_taxonomy( $taxonomy );
                if ( ! $taxonomy ) return false;
            }
            return $taxonomy->publicly_queryable;
        }//4954
        /**
         * @description Sets the last changed time for the 'terms' cache group.
         */
        protected function _tp_cache_set_terms_last_changed():void{
            $this->_tp_cache_set( 'last_changed', microtime(), 'terms' );
        }//4970
        /**
         * @description Aborts calls to term meta if it is not supported.
         * @param $check
         * @return bool
         */
        protected function _tp_check_term_meta_support_pre_filter( $check ):bool{
            if ( $this->_get_option( 'db_version' ) < 34370 ) return false;
            return $check;
        }//4982
    }
}else die;