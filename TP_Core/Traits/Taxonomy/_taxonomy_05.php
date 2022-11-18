<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Libs\TP_Term;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _taxonomy_05 {
        use _init_db;
        use _init_error;
        use _init_cache;
        /**
         * @description Remove term(s) associated with a given object.
         * @param $object_id
         * @param $terms
         * @param $taxonomy
         * @return bool|TP_Error
         */
        protected function _tp_remove_object_terms( $object_id, $terms, $taxonomy ){
            $this->tpdb = $this->_init_db();
            $object_id = (int) $object_id;
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            if ( ! is_array( $terms ) ) $terms = array( $terms );
            $tt_ids = [];
            foreach ( (array) $terms as $term ) {
                if ( '' === trim( $term ) ) continue;
                $term_info = $this->_term_exists( $term, $taxonomy );
                if (!$term_info && is_int($term)) continue;
                if ( $this->_init_error( $term_info ) ) return $term_info;
                $tt_ids[] = $term_info['term_taxonomy_id'];
            }
            if ( $tt_ids ) {
                $in_tt_ids = "'" . implode( "', '", $tt_ids ) . "'";
                $this->_do_action( 'delete_term_relationships', $object_id, $tt_ids, $taxonomy );
                $deleted = $this->tpdb->query( $this->tpdb->prepare( TP_DELETE . " FROM $this->tpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $object_id ) );
                $this->_tp_cache_delete( $object_id, $taxonomy . '_relationships' );
                $this->_tp_cache_delete( 'last_changed', 'terms' );
                $this->_do_action( 'deleted_term_relationships', $object_id, $tt_ids, $taxonomy );
                $this->_tp_update_term_count( $tt_ids, $taxonomy );
                return (bool) $deleted;
            }
            return false;
        }//2869
        /**
         * @description Will make slug unique, if it isn't already.
         * @param $slug
         * @param $term
         * @return mixed
         */
        protected function _tp_unique_term_slug( $slug, $term ){
            $this->tpdb = $this->_init_db();
            $needs_suffix  = true;
            $original_slug = $slug;
            if ( ! $this->_term_exists( $slug ) || ($this->_get_option('db_version') >= 30133 && !$this->_get_term_by('slug', $slug, $term->taxonomy)))
                $needs_suffix = false;
            $parent_suffix = '';
            if ( $needs_suffix && ! empty( $term->parent ) && $this->_is_taxonomy_hierarchical( $term->taxonomy ) ) {
                $the_parent = $term->parent;
                while ( ! empty( $the_parent ) ) {
                    $parent_term = $this->_get_term( $the_parent, $term->taxonomy );
                    if ( empty( $parent_term ) || $this->_init_error( $parent_term )) break;
                    $parent_suffix .= '-' . $parent_term->slug;
                    if ( ! $this->_term_exists( $slug . $parent_suffix ) ) break;
                    if ( empty( $parent_term->parent ) ) break;
                    $the_parent = $parent_term->parent;
                }
            }
            if ( $this->_apply_filters( 'tp_unique_term_slug_is_bad_slug', $needs_suffix, $slug, $term ) ) {
                if ( $parent_suffix ) $slug .= $parent_suffix;
                if ( ! empty( $term->term_id ) )
                    $query = $this->tpdb->prepare( TP_SELECT . " slug FROM $this->tpdb->terms WHERE slug = %s AND term_id != %d", $slug, $term->term_id );
                else $query = $this->tpdb->prepare( TP_SELECT . " slug FROM $this->tpdb->terms WHERE slug = %s", $slug );
                if ( $this->tpdb->get_var( $query ) ) {
                    $num = 2;
                    do {
                        $alt_slug = $slug . "-$num";
                        $num++;
                        $slug_check = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " slug FROM $this->tpdb->terms WHERE slug = %s", $alt_slug ) );
                    } while ( $slug_check );
                    $slug = $alt_slug;
                }
            }
            return $this->_apply_filters( 'tp_unique_term_slug', $slug, $term, $original_slug );
        }//2994
        /**
         * @description Update term based on arguments provided.
         * @param $term_id
         * @param $taxonomy
         * @param \array[] ...$args
         * @return array|TP_Error
         */
        protected function _tp_update_term( $term_id, $taxonomy,array ...$args){
            $this->tpdb = $this->_init_db();
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            $term_id = (int) $term_id;
            $term = $this->_get_term( $term_id, $taxonomy );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! $term ) return new TP_Error( 'invalid_term', $this->__( 'Empty Term.' ) );
            $term = (array) $term->data;
            $term = $this->_tp_slash( $term );
            $args = array_merge( $term, $args );
            $defaults    = ['alias_of' => '','description' => '','parent' => 0,'slug' => '',];
            $args        = $this->_tp_parse_args( $args, $defaults );
            $args        = $this->_sanitize_term( $args, $taxonomy, 'db' );
            $parsed_args = $args;
            $name        = $this->_tp_unslash( $args['name'] );
            $description = $this->_tp_unslash( $args['description'] );
            $parsed_args['name']        = $name;
            $parsed_args['description'] = $description;
            if ( '' === trim( $name ) )
                return new TP_Error( 'empty_term_name', $this->__( 'A name is required for this term.' ) );
            if ( (int) $parsed_args['parent'] > 0 && ! $this->_term_exists( (int) $parsed_args['parent'] ) )
                return new TP_Error( 'missing_parent', $this->__( 'Parent term does not exist.' ) );
            $empty_slug = false;
            if ( empty( $args['slug'] ) ) {
                $empty_slug = true;
                $slug       = $this->_sanitize_title( $name );
            } else $slug = $args['slug'];
            $parsed_args['slug'] = $slug;
            $term_group = $parsed_args['term_group'] ?? 0;
            if ( $args['alias_of'] ) {
                $alias = $this->_get_term_by( 'slug', $args['alias_of'], $taxonomy );
                if ( ! empty( $alias->term_group ) ) $term_group = $alias->term_group;
                elseif ( ! empty( $alias->term_id ) ) {
                    $term_group = $this->tpdb->get_var( TP_SELECT . " MAX(term_group) FROM $this->tpdb->terms" ) + 1;
                    $this->_tp_update_term( $alias->term_id, $taxonomy,['term_group' => $term_group,]);
                }
                $parsed_args['term_group'] = $term_group;
            }
            $parent = (int) $this->_apply_filters( 'tp_update_term_parent', $args['parent'], $term_id, $taxonomy, $parsed_args, $args );
            $duplicate = $this->_get_term_by( 'slug', $slug, $taxonomy );
            if ( $duplicate && $duplicate->term_id !== $term_id ) {
                if ( $empty_slug || ( $parent !== (int) $term['parent'] ) ) $slug = $this->_tp_unique_term_slug( $slug, (object) $args );
                else return new TP_Error( 'duplicate_term_slug', sprintf( $this->__( 'The slug &#8220;%s&#8221; is already in use by another term.' ), $slug ) );
            }
            $tt_id = (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " tt.term_taxonomy_id FROM $this->tpdb->term_taxonomy AS tt INNER JOIN $this->tpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d", $taxonomy, $term_id ) );
            $_term_id = null;
            if($term_id instanceof TP_Term && $tt_id instanceof TP_Term){
                $_term_id = $this->_split_shared_term( $term_id, $tt_id );
            }
            if ( ! $this->_init_error( $_term_id ) ) $term_id = $_term_id;
            $this->_do_action( 'edit_terms', $term_id, $taxonomy );
            $data = compact( 'name', 'slug', 'term_group' );
            $data = $this->_apply_filters( 'tp_update_term_data', $data, $term_id, $taxonomy, $args );
            $this->tpdb->update( $this->tpdb->terms, $data, compact( 'term_id' ) );
            if ( empty( $slug ) ) {
                $slug = $this->_sanitize_title( $name, $term_id );
                $this->tpdb->update( $this->tpdb->terms, compact( 'slug' ), compact( 'term_id' ) );
            }
            $this->_do_action( 'edited_terms', $term_id, $taxonomy );
            $this->_do_action( 'edit_term_taxonomy', $tt_id, $taxonomy );
            $this->tpdb->update( $this->tpdb->term_taxonomy, compact( 'term_id', 'taxonomy', 'description', 'parent' ), array( 'term_taxonomy_id' => $tt_id ) );
            $this->_do_action( 'edited_term_taxonomy', $tt_id, $taxonomy );
            $this->_do_action( 'edit_term', $term_id, $tt_id, $taxonomy );
            $this->_do_action( "edit_{$taxonomy}", $term_id, $tt_id );
            $term_id = $this->_apply_filters( 'term_id_filter', $term_id, $tt_id );
            $this->_clean_term_cache( $term_id, $taxonomy );
            $this->_do_action( 'edited_term', $term_id, $tt_id, $taxonomy );
            $this->_do_action( "edited_{$taxonomy}", $term_id, $tt_id );
            $this->_do_action( 'saved_term', $term_id, $tt_id, $taxonomy, true );
            $this->_do_action( "saved_{$taxonomy}", $term_id, $tt_id, true );
            return ['term_id' => $term_id, 'term_taxonomy_id' => $tt_id,];
        }//3109
        /**
         * @description Enable or disable term counting.
         * @param null $defer
         * @return bool
         */
        protected function _tp_defer_term_counting( $defer = null ):bool{
            static $_defer = false;
            if ( is_bool( $defer ) ) {
                $_defer = $defer;
                if ( ! $defer ) $this->_tp_update_term_count( null, null, true );
            }
            return $_defer;
        }//3386
        /**
         * @description Updates the amount of terms in taxonomy.
         * @param $terms
         * @param $taxonomy
         * @param bool $do_deferred
         * @return bool|string
         */
        protected function _tp_update_term_count( $terms, $taxonomy, $do_deferred = false ){
            static $_deferred = array();
            if ( $do_deferred ) {
                foreach ( (array) array_keys( $_deferred ) as $tax ) {
                    $this->_tp_update_term_count_now( $_deferred[ $tax ], $tax );
                    unset( $_deferred[ $tax ] );
                }
            }
            if ( empty( $terms ) ) return false;
            if ( ! is_array( $terms ) ) $terms = array( $terms );
            if ( $this->_tp_defer_term_counting() ) {
                if ( ! isset( $_deferred[ $taxonomy ] ) ) $_deferred[ $taxonomy ] = [];
                $_deferred[ $taxonomy ] = array_unique( array_merge( $_deferred[ $taxonomy ], $terms ) );
                return true;
            }
            return $this->_tp_update_term_count_now( $terms, $taxonomy );
        }//3416
        /**
         * @description Perform term count update immediately.
         * @param $terms
         * @param $taxonomy
         * @return bool
         */
        protected function _tp_update_term_count_now( $terms, $taxonomy ):bool{
            $terms = array_map( 'intval', $terms );
            $taxonomy = $this->_get_taxonomy( $taxonomy );
            if ( ! empty( $taxonomy->update_count_callback ) )
                call_user_func( $taxonomy->update_count_callback, $terms, $taxonomy );
            else {
                $object_types = (array) $taxonomy->object_type;
                foreach ( $object_types as &$object_type ) {
                    if ( 0 === strpos( $object_type, 'attachment:' ) )
                        @list( $object_type ) = explode( ':', $object_type );
                }
                unset($object_type);
                if ( array_filter( $object_types, 'post_type_exists' ) === $object_types )
                    $this->_update_post_term_count( $terms, $taxonomy );
                else $this->_update_generic_term_count( $terms, $taxonomy );
            }
            $this->_clean_term_cache( $terms, '', false );
            return true;
        }//3454
        /**
         * @description Removes the taxonomy relationship to terms from the cache.
         * @param $object_ids
         * @param $object_type
         */
        protected function _clean_object_term_cache( $object_ids, $object_type ):void{
            if ( ! empty( $this->tp_suspend_cache_invalidation ) ) return;
            if ( ! is_array( $object_ids ) ) $object_ids = [$object_ids];
            $taxonomies = $this->_get_object_taxonomies( $object_type );
            foreach ( $object_ids as $id ) {
                foreach ( $taxonomies as $taxonomy )
                    $this->_tp_cache_delete( $id, "{$taxonomy}_relationships" );
            }
            $this->_do_action( 'clean_object_term_cache', $object_ids, $object_type );
        }//3502
        /**
         * @description Will remove all of the term IDs from the cache.
         * @param $ids
         * @param string $taxonomy
         * @param bool $clean_taxonomy
         */
        protected function _clean_term_cache( $ids, $taxonomy = '', $clean_taxonomy = true ):void{
            $this->tpdb = $this->_init_db();
            if ( ! empty( $this->__tp_suspend_cache_invalidation ) ) return;
            if ( ! is_array( $ids ) ) $ids = [$ids];
            $taxonomies = [];
            if ( empty( $taxonomy ) ) {
                $tt_ids = array_map( 'intval', $ids );
                $tt_ids = implode( ', ', $tt_ids );
                $terms  = $this->tpdb->get_results( TP_SELECT . " term_id, taxonomy FROM $this->tpdb->term_taxonomy WHERE term_taxonomy_id IN ($tt_ids)" );
                $ids    = [];
                foreach ( (array) $terms as $term ) {
                    $taxonomies[] = $term->taxonomy;
                    $ids[]        = $term->term_id;
                    $this->_tp_cache_delete( $term->term_id, 'terms' );
                }
                $taxonomies = array_unique( $taxonomies );
            }else {
                foreach ( $taxonomies as $sub_taxonomy1 ){
                    $taxonomies = [$sub_taxonomy1];
                    foreach ( $ids as $id )
                        $this->_tp_cache_delete( $id, 'terms' );
                }
            }
            foreach ( $taxonomies as $sub_taxonomy2 ) {
                if ( $clean_taxonomy ) $this->_clean_taxonomy_cache( $sub_taxonomy2 );
                $this->_do_action( 'clean_term_cache', $ids, $sub_taxonomy2, $clean_taxonomy );
            }
            $this->_tp_cache_set( 'last_changed', microtime(), 'terms' );
        }//3546
        /**
         * @description Clean the caches for a taxonomy.
         * @param $taxonomy
         */
        protected function _clean_taxonomy_cache( $taxonomy ):void{
            $this->_tp_cache_delete( 'all_ids', $taxonomy );
            $this->_tp_cache_delete( 'get', $taxonomy );
            $this->_delete_option( "{$taxonomy}_children" );
            $this->_get_term_hierarchy( $taxonomy );
            $this->_do_action( 'clean_taxonomy_cache', $taxonomy );
        }//3610
        /**
         * todo
         * @description Retrieves the cached term objects for the given object ID.
         * @param $id
         * @param $taxonomy
         * @return array|bool
         */
        protected function _get_object_term_cache( $id, $taxonomy ){
            $_term_ids = $this->_tp_cache_get( $id, "{$taxonomy}_relationships" );
            if ( false === $_term_ids ) return false;
            // Backward compatibility for if a plugin is putting objects into the cache, rather than IDs.
            $term_ids = [];
            foreach ( $_term_ids as $term_id ) {
                if ( is_numeric( $term_id ) )  $term_ids[] = (int) $term_id;
                elseif ( isset( $term_id->term_id ) ) $term_ids[] = (int) $term_id->term_id;
            }
            $this->_prime_term_caches( $term_ids );
            $terms = [];
            foreach ( $term_ids as $term_id ) {
                $term = $this->_get_term( $term_id, $taxonomy );
                if ( $this->_init_error( $term ) ) return $term;
                $terms[] = $term;
            }
            return $terms;
        }//3645
    }
}else die;