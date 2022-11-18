<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    trait _taxonomy_06 {
        use _init_error;
        use _init_db;
        /**
         * @description Updates the cache for the given term object ID(s).
         * @param $object_ids
         * @param $object_type
         * @return bool
         */
        protected function _update_object_term_cache( $object_ids, $object_type ):bool{
            if ( empty( $object_ids ) ) return false;
            if ( ! is_array( $object_ids ) ) $object_ids = explode( ',', $object_ids );
            $object_ids     = array_map( 'intval', $object_ids );
            $non_cached_ids = [];
            $taxonomies = $this->_get_object_taxonomies( $object_type );
            foreach ( $taxonomies as $taxonomy ) {
                $cache_values = $this->_tp_cache_get_multiple( (array) $object_ids, "{$taxonomy}_relationships" );
                foreach ( $cache_values as $id => $value ) {
                    if ( false === $value ) $non_cached_ids[] = $id;
                }
            }
            if ( empty( $non_cached_ids ) ) return false;
            $non_cached_ids = array_unique( $non_cached_ids );
            $terms = $this->_tp_get_object_terms( $non_cached_ids,$taxonomies, ['fields' => 'all_with_object_id','orderby' => 'name','update_term_meta_cache' => false,]);
            $object_terms = [];
            foreach ( (array) $terms as $term )
                $object_terms[ $term->object_id ][ $term->taxonomy ][] = $term->term_id;
            foreach ( $non_cached_ids as $id ) {
                foreach ( $taxonomies as $taxonomy ) {
                    if ( ! isset( $object_terms[ $id ][ $taxonomy ] ) ) {
                        if ( ! isset( $object_terms[ $id ] ) ) $object_terms[ $id ] = [];
                        $object_terms[ $id ][ $taxonomy ] = [];
                    }
                }
            }
            foreach ( $object_terms as $id => $value ) {
                foreach ( $value as $taxonomy => $terms )
                    $this->_tp_cache_add( $id, $terms, "{$taxonomy}_relationships" );
            }
            return true;
        }//3696
        /**
         * @description Terms to Taxonomy in cache.
         * @param $terms
         */
        protected function _update_term_cache(...$terms):void{ //not used , $taxonomy = ''
            foreach ( $terms as $term ) {
                $_term = clone $term;
                unset( $_term->object_id );
                $this->_tp_cache_add( $term->term_id, $_term, 'terms' );
            }
        }//3767
        /**
         * @description Retrieves children of taxonomy as Term IDs.
         * @param $taxonomy
         * @return array
         */
        protected function _get_term_hierarchy( $taxonomy ):array{
            if ( ! $this->_is_taxonomy_hierarchical( $taxonomy ) ) return [];
            $children = $this->_get_option( "{$taxonomy}_children" );
            if ( is_array( $children ) ) return $children;
            $children = [];
            $terms = $this->_get_terms(['taxonomy' => $taxonomy,'get' => 'all','orderby' => 'id','fields' => 'id=>parent', 'update_term_meta_cache' => false,]);
            foreach ( $terms as $term_id => $parent ) {
                if ( $parent > 0 ) $children[ $parent ][] = $term_id;
            }
            $this->_update_option( "{$taxonomy}_children", $children );
            return $children;
        }//3792
        /**
         * @description Get the subset of $terms that are descendants of $term_id.
         * @param $term_id
         * @param $terms
         * @param $taxonomy
         * @param array $ancestors
         * @return array
         */
        protected function _get_term_children( $term_id, $terms, $taxonomy, &$ancestors = [] ):array{
            $empty_array = array();
            if ( empty( $terms ) ) return $empty_array;
            $term_id      = (int) $term_id;
            $term_list    = array();
            $has_children = $this->_get_term_hierarchy( $taxonomy );
            if ( $term_id && ! isset( $has_children[ $term_id ] ) ) return $empty_array;
            if ( empty( $ancestors ) ) $ancestors[ $term_id ] = 1;
            foreach ( (array) $terms as $term ) {
                $use_id = false;
                if ( ! is_object( $term ) ) {
                    $term = $this->_get_term( $term, $taxonomy );
                    if ( $this->_init_error( $term ) ) return $term;
                    $use_id = true;
                }
                if ( isset( $ancestors[ $term->term_id ] ) ) continue;
                if ( (int) $term->parent === $term_id ) {
                    if ( $use_id ) $term_list[] = $term->term_id;
                    else $term_list[] = $term;
                    if ( ! isset( $has_children[ $term->term_id ] ) )
                        continue;
                    $ancestors[ $term->term_id ] = 1;
                    $children = $this->_get_term_children( $term->term_id, $terms, $taxonomy, $ancestors );
                    if ( $children ) {
                        $term_list = $this->_tp_array_merge( $term_list, $children );
                    }
                }
            }
            return $term_list;
        }//3840
        /**
         * @description Add count of children to parent count.
         * @param $terms
         * @param $taxonomy
         */
        protected function _pad_term_counts( &$terms, $taxonomy ):void{
            $this->tpdb = $this->_init_db();
            if ( ! $this->_is_taxonomy_hierarchical( $taxonomy ) ) return;
            $term_hier = $this->_get_term_hierarchy( $taxonomy );
            if ( empty( $term_hier ) ) return;
            $term_items  = [];
            $terms_by_id = [];
            $term_ids    = [];
            foreach ( (array) $terms as $key => $term ) {
                $terms_by_id[ $term->term_id ]       = & $terms[ $key ];
                $term_ids[ $term->term_taxonomy_id ] = $term->term_id;
            }
            $tax_obj      = $this->_get_taxonomy( $taxonomy );
            $object_types = $this->_esc_sql( $tax_obj->object_type );
            $results      = $this->tpdb->get_results(TP_SELECT . " object_id, term_taxonomy_id FROM $this->tpdb->term_relationships INNER JOIN $this->tpdb->posts ON object_id = ID WHERE term_taxonomy_id IN (" . implode( ',', array_keys( $term_ids ) ) . ") AND post_type IN ('" . implode( "', '", $object_types ) . "') AND post_status = 'publish'" );
            foreach ( $results as $row ) {
                $id = $term_ids[ $row->term_taxonomy_id ];
                $term_items[ $id ][ $row->object_id ] = isset( $term_items[ $id ][ $row->object_id ] ) ? ++$term_items[ $id ][ $row->object_id ] : 1;
            }
            foreach ( $term_ids as $term_id ) {
                $child     = $term_id;
                $ancestors = [];
                while ( ! empty( $terms_by_id[ $child ] ) && $parent = $terms_by_id[ $child ]->parent ) {
                    $ancestors[] = $child;
                    if ( ! empty( $term_items[ $term_id ] ) ) {
                        foreach ( $term_items[ $term_id ] as $item_id => $touches ) {
                            $term_items[ $parent ][ $item_id ] = isset( $term_items[ $parent ][ $item_id ] ) ? ++$term_items[ $parent ][ $item_id ] : 1;
                        }
                    }
                    $child = $parent;
                    if ( in_array( $parent, $ancestors, true ) ) break;
                }
            }
            foreach ($term_items as $id => $items )
                if ( isset( $terms_by_id[ $id ] ) ) $terms_by_id[ $id ]->count = count( $items );
        }//3911
        /**
         * @description Adds any terms from the given IDs to the cache that do not already exist in cache.
         * @param $term_ids
         * @param bool $update_meta_cache
         */
        protected function _prime_term_caches( $term_ids, $update_meta_cache = true ):void{
            $this->tpdb = $this->_init_db();
            $non_cached_ids = $this->_get_non_cached_ids( $term_ids, 'terms' );
            if ( ! empty( $non_cached_ids ) ) {
                $fresh_terms = $this->tpdb->get_results( sprintf( TP_SELECT . " t.*, tt.* FROM $this->tpdb->terms AS t INNER JOIN $this->tpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.term_id IN (%s)", implode( ',', array_map( 'intval', $non_cached_ids ) ) ) );
                $this->_update_term_cache( $fresh_terms, $update_meta_cache );
                if ( $update_meta_cache ) $this->_update_term_meta_cache( $non_cached_ids );
            }
        }//3985
        /**
         * @description Will update term count based on object types of the current taxonomy.
         * @param $terms
         * @param $taxonomy
         */
        protected function _update_post_term_count( $terms, $taxonomy ):void{
            $this->tpdb = $this->_init_db();
            $object_types = (array) $taxonomy->object_type;
            foreach ( $object_types as &$object_type ) @list( $object_type ) = explode( ':', $object_type );
            unset($object_type);
            $object_types = array_unique( $object_types );
            $check_attachments = array_search( 'attachment', $object_types, true );
            if ( false !== $check_attachments ) {
                unset( $object_types[ $check_attachments ] );
                $check_attachments = true;
            }
            if ( $object_types ) $object_types = $this->_esc_sql( array_filter( $object_types, 'post_type_exists' ) );
            $post_statuses = array( 'publish' );
            $post_statuses = $this->_esc_sql( $this->_apply_filters( 'update_post_term_count_statuses', $post_statuses, $taxonomy ) );
            foreach ( (array) $terms as $term ) {
                $count = 0;
                if ( $check_attachments )
                    $count += (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_relationships, $this->tpdb->posts p1 WHERE p1.ID = $this->tpdb->term_relationships.object_id AND ( post_status IN ('" . implode( "', '", $post_statuses ) . "') OR ( post_status = 'inherit' AND post_parent > 0 AND ( SELECT post_status FROM $this->tpdb->posts WHERE ID = p1.post_parent ) IN ('" . implode( "', '", $post_statuses ) . "') ) ) AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );
                if ( $object_types )
                    $count += (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_relationships, $this->tpdb->posts WHERE $this->tpdb->posts.ID = $this->tpdb->term_relationships.object_id AND post_status IN ('" . implode( "', '", $post_statuses ) . "') AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
                $this->_do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
                $this->tpdb->update( $this->tpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
                $this->_do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
            }
        }//4018
        /**
         * @description Will update term count based on number of objects.
         * @param $terms
         * @param $taxonomy
         */
        protected function _update_generic_term_count( $terms, $taxonomy ):void{
            $this->tpdb = $this->_init_db();
            foreach ( (array) $terms as $term ) {
                $count = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
                $this->_do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
                $this->tpdb->update( $this->tpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
                $this->_do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
            }
        }//4086
        /**
         * @description Create a new term for a term_taxonomy item that currently shares its term with another term_taxonomy.
         * @param mixed $term_id
         * @param mixed $term_taxonomy_id
         * @param bool $record
         * @return int|TP_Term|TP_Error
         */
        protected function _split_shared_term($term_id,$term_taxonomy_id, $record = true ){
            $this->tpdb = $this->_init_db();
            if ( is_object( $term_id ) ) {
                $shared_term = $term_id;
                $term_id     = $shared_term->term_id;
            }
            if ( is_object( $term_taxonomy_id ) ) {
                $term_taxonomy    = $term_taxonomy_id;
                $term_taxonomy_id = $term_taxonomy->term_taxonomy_id;
            }
            $shared_tt_count = (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_taxonomy tt WHERE tt.term_id = %d AND tt.term_taxonomy_id != %d", $term_id, $term_taxonomy_id ) );
            if ( ! $shared_tt_count )  return $term_id;
            $check_term_id = (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " term_id FROM $this->tpdb->term_taxonomy WHERE term_taxonomy_id = %d", $term_taxonomy_id ) );
            if ( $check_term_id !== $term_id ) return $check_term_id;
            if ( empty( $shared_term ) )
                $shared_term = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " t.* FROM $this->tpdb->terms t WHERE t.term_id = %d", $term_id ) );
            $new_term_data = ['name' => $shared_term->name,'slug' => $shared_term->slug,'term_group' => $shared_term->term_group,];
            if ( false === $this->tpdb->insert( $this->tpdb->terms, $new_term_data ) )
                return new TP_Error( 'db_insert_error', $this->__( 'Could not split shared term.' ), $this->tpdb->last_error );
            $new_term_id = (int) $this->tpdb->insert_id;
            $this->tpdb->update($this->tpdb->term_taxonomy, ['term_id' => $new_term_id], ['term_taxonomy_id' => $term_taxonomy_id] );
            if ( empty( $term_taxonomy ) )
                $term_taxonomy = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->term_taxonomy WHERE term_taxonomy_id = %d", $term_taxonomy_id ) );
            $children_tt_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " term_taxonomy_id FROM $this->tpdb->term_taxonomy WHERE parent = %d AND taxonomy = %s", $term_id, $term_taxonomy->taxonomy ) );
            if ( ! empty( $children_tt_ids ) ) {
                foreach ( $children_tt_ids as $child_tt_id ) {
                    $this->tpdb->update($this->tpdb->term_taxonomy,['parent' => $new_term_id], ['term_taxonomy_id' => $child_tt_id]);
                    $this->_clean_term_cache( (int) $child_tt_id, '', false );
                }
            } else  $this->_clean_term_cache( $new_term_id, $term_taxonomy->taxonomy, false );
            $this->_clean_term_cache( $term_id, $term_taxonomy->taxonomy, false );
            $taxonomies_to_clean = array( $term_taxonomy->taxonomy );
            $shared_term_taxonomies = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " taxonomy FROM $this->tpdb->term_taxonomy WHERE term_id = %d", $term_id ) );
            $taxonomies_to_clean    = array_merge( $taxonomies_to_clean, $shared_term_taxonomies );
            foreach ( $taxonomies_to_clean as $taxonomy_to_clean ) $this->_clean_taxonomy_cache( $taxonomy_to_clean );
            if ( $record ) {
                $split_term_data = $this->_get_option( '_split_terms', [] );
                if ( ! isset( $split_term_data[ $term_id ] ) ) $split_term_data[ $term_id ] = [];
                $split_term_data[ $term_id ][ $term_taxonomy->taxonomy ] = $new_term_id;
                $this->_update_option( '_split_terms', $split_term_data );
            }
            $shared_terms_exist = $this->tpdb->get_results(TP_SELECT . " tt.term_id, t.*, count(*) as term_tt_count FROM {$this->tpdb->term_taxonomy} tt LEFT JOIN {$this->tpdb->terms} t ON t.term_id = tt.term_id GROUP BY t.term_id HAVING term_tt_count > 1 LIMIT 1");
            if ( ! $shared_terms_exist ) $this->_update_option( 'finished_splitting_shared_terms', true );
            $this->_do_action( 'split_shared_term', $term_id, $new_term_id, $term_taxonomy_id, $term_taxonomy->taxonomy );
            return $new_term_id;
        }//4124
        /**
         * @description Splits a batch of shared taxonomy terms.
         */
        protected function _tp_batch_split_terms():void{
            $this->tpdb = $this->_init_db();
            $lock_name = 'term_split.lock';
            $lock_result = $this->tpdb->query( $this->tpdb->prepare( TP_INSERT . " IGNORE INTO `$this->tpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_name, time() ) );
            if ( ! $lock_result ) {
                $lock_result = $this->_get_option( $lock_name );
                if ( ! $lock_result || ( $lock_result > ( time() - HOUR_IN_SECONDS ) ) ) {
                    $this->_tp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS ), 'tp_split_shared_term_batch' );
                    return;
                }
            }
            $this->_update_option( $lock_name, time() );
            $shared_terms = $this->tpdb->get_results(TP_SELECT . " tt.term_id, t.*, count(*) as term_tt_count FROM {$this->tpdb->term_taxonomy} tt LEFT JOIN {$this->tpdb->terms} t ON t.term_id = tt.term_id GROUP BY t.term_id HAVING term_tt_count > 1 LIMIT 10" );
            if ( ! $shared_terms ) {
                $this->_update_option( 'finished_splitting_shared_terms', true );
                $this->_delete_option( $lock_name );
                return;
            }
            $this->_tp_schedule_single_event( time() + ( 2 * MINUTE_IN_SECONDS ), 'tp_split_shared_term_batch' );
            $_shared_terms = [];
            foreach ( $shared_terms as $shared_term ) {
                $term_id                   = (int) $shared_term->term_id;
                $_shared_terms[ $term_id ] = $shared_term;
            }
            $shared_terms = $_shared_terms;
            $shared_term_ids = implode( ',', array_keys( $shared_terms ) );
            $shared_tts      = $this->tpdb->get_results( TP_SELECT . " * FROM {$this->tpdb->term_taxonomy} WHERE `term_id` IN ({$shared_term_ids})" );
            $split_term_data    = $this->_get_option( '_split_terms', array() );
            $skipped_first_term = array();
            $taxonomies         = array();
            foreach ( $shared_tts as $shared_tt ) {
                $term_id = (int) $shared_tt->term_id;
                if ( ! isset( $skipped_first_term[ $term_id ] ) ) {
                    $skipped_first_term[ $term_id ] = 1;
                    continue;
                }
                if ( ! isset( $split_term_data[ $term_id ] ) ) $split_term_data[ $term_id ] = [];
                if ( ! isset( $taxonomies[ $shared_tt->taxonomy ] ) ) $taxonomies[ $shared_tt->taxonomy ] = 1;
                $split_term_data[ $term_id ][ $shared_tt->taxonomy ] = $this->_split_shared_term( $shared_terms[ $term_id ], $shared_tt, false );
            }
            foreach ( array_keys( $taxonomies ) as $tax ) {
                $this->_delete_option( "{$tax}_children" );
                $this->_get_term_hierarchy( $tax );
            }
            $this->_update_option( '_split_terms', $split_term_data );
            $this->_delete_option( $lock_name );
        }//4258
    }
}else die;