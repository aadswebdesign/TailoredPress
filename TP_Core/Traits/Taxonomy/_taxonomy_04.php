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
if(ABSPATH){
    trait _taxonomy_04 {
        use _init_db;
        use _init_error;
        /**
         * @description Sanitize all term fields.
         * @param $term
         * @param $taxonomy
         * @param string $context
         * @return mixed
         */
        protected function _sanitize_term( $term, $taxonomy, $context = 'display' ){
            $fields = array( 'term_id', 'name', 'description', 'slug', 'count', 'parent', 'term_group', 'term_taxonomy_id', 'object_id' );
            $do_object = is_object( $term );
            $nested_term_id = ($term['term_id'] ?? 0 );
            $term_id = $do_object ? $term->term_id : $nested_term_id;
            foreach ( $fields as $field ) {
                if ( $do_object ) {
                    if ( isset( $term->$field ) ) $term->$field = $this->_sanitize_term_field( $field, $term->$field, $term_id, $taxonomy, $context );
                } else if ( isset( $term[ $field ] ) ) $term[ $field ] = $this->_sanitize_term_field( $field, $term[ $field ], $term_id, $taxonomy, $context );
            }
            if ( $do_object ) $term->filter = $context;
            else $term['filter'] = $context;
            return $term;
        }//1643
        /**
         * @description  Cleanse the field value in the term based on the context.
         * @param $field
         * @param $value
         * @param $term_id
         * @param $taxonomy
         * @param $context
         * @return int
         */
        protected function _sanitize_term_field( $field, $value, $term_id, $taxonomy, $context ):int{
            $int_fields = [ 'parent', 'term_id', 'count', 'term_group', 'term_taxonomy_id', 'object_id' ];
            if ( in_array( $field, $int_fields, true ) ) {
                $value = (int) $value;
                if ( $value < 0 ) $value = 0;
            }
            $context = strtolower( $context );
            if ( 'raw' === $context ) return $value;
            if ( 'edit' === $context ) {
                $value = $this->_apply_filters( "edit_term_{$field}", $value, $term_id, $taxonomy );
                $value = $this->_apply_filters( "edit_{$taxonomy}_{$field}", $value, $term_id );
                if ( 'description' === $field ) $value = $this->_esc_html( $value ); // textarea_escaped
                else $value = $this->_esc_attr( $value );
            } elseif ( 'db' === $context ) {
                $value = $this->_apply_filters( "pre_term_{$field}", $value, $taxonomy );
                $value = $this->_apply_filters( "pre_{$taxonomy}_{$field}", $value );
            } elseif ( 'rss' === $context ) {
                $value = $this->_apply_filters( "term_{$field}_rss", $value, $taxonomy );
                $value = $this->_apply_filters( "{$taxonomy}_{$field}_rss", $value );
            }else{
                $value = $this->_apply_filters( "term_{$field}", $value, $term_id, $taxonomy, $context );
                $value = $this->_apply_filters( "{$taxonomy}_{$field}", $value, $term_id, $context );
            }
            if ( 'attribute' === $context ) $value = $this->_esc_attr( $value );
            elseif ( 'js' === $context ) $value = $this->_esc_js( $value );
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            return $value;
        }//1695
        /**
         * @description Count how many terms are in Taxonomy.
         * @param mixed $args
         * @return mixed
         */
        protected function _tp_count_terms(array ...$args){
            $use_legacy_args = false;
            if ( $args && ( (is_string($args) && $this->_taxonomy_exists($args)) || (is_array($args) && $this->_tp_is_numeric_array($args))))
                $use_legacy_args = true;
            $defaults = array( 'hide_empty' => false );
            if ( $use_legacy_args ) $defaults['taxonomy'] = $args;
            $args = $this->_tp_parse_args( $args, $defaults );
            $args['fields'] = 'count';
            return $this->_get_terms( $args );
        }//1872
        /**
         *  @description Will unlink the object from the taxonomy or taxonomies.
         * @param $object_id
         * @param $taxonomies
         * @return bool
         */
        protected function _tp_delete_object_term_relationships( $object_id, $taxonomies ):bool{
            $object_id = (int) $object_id;
            if ( ! is_array( $taxonomies ) ) $taxonomies = [$taxonomies];
            foreach ( (array) $taxonomies as $taxonomy ) {
                $term_ids = $this->_tp_get_object_terms( $object_id, $taxonomy, ['fields' => 'ids'] );
                $term_ids = array_map( 'intval', $term_ids );
                $this->_tp_remove_object_terms( $object_id, $term_ids, $taxonomy );
            }
        }//1915
        /**
         * @description Removes a term from the database.
         * @param $term
         * @param $taxonomy
         * @param \array[] ...$args
         * @return bool
         */
        protected function _tp_delete_term( $term, $taxonomy, ...$args):bool{
            $this->tpdb = $this->_init_db();
            $term = (int) $term;
            $ids = $this->_term_exists( $term, $taxonomy );
            if ( ! $ids ) return false;
            if ( $this->_init_error( $ids ) ) return $ids;
            $tt_id = $ids['term_taxonomy_id'];
            $defaults = [];
            if ( 'category' === $taxonomy ) {
                $defaults['default'] = (int) $this->_get_option( 'default_category' );
                if ( $defaults['default'] === $term ) return 0; // Don't delete the default category.
            }
            $taxonomy_object = $this->_get_taxonomy( $taxonomy );
            if ( ! empty( $taxonomy_object->default_term ) ) {
                $defaults['default'] = (int) $this->_get_option( 'default_term_' . $taxonomy );
                if ( $defaults['default'] === $term ) return 0;
            }
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( isset( $args['default'] ) ) {
                $default = (int) $args['default'];
                if ( ! $this->_term_exists( $default, $taxonomy ) ) unset( $default );
            }
            if ( isset( $args['force_default'] ) ) $force_default = $args['force_default'];
            $this->_do_action( 'pre_delete_term', $term, $taxonomy );
            if ( $this->_is_taxonomy_hierarchical( $taxonomy ) ) {
                $term_obj = $this->_get_term( $term, $taxonomy );
                if ( $this->_init_error( $term_obj ) ) return $term_obj;
                $parent = $term_obj->parent;
                $edit_ids    = $this->tpdb->get_results( TP_SELECT . " term_id, term_taxonomy_id FROM $this->tpdb->term_taxonomy WHERE `parent` = " . (int) $term_obj->term_id );
                $edit_tt_ids = $this->_tp_list_pluck( $edit_ids, 'term_taxonomy_id' );
                $this->_do_action( 'edit_term_taxonomies', $edit_tt_ids );
                $this->tpdb->update( $this->tpdb->term_taxonomy, compact( 'parent' ), array( 'parent' => $term_obj->term_id ) + compact( 'taxonomy' ) );
                $edit_term_ids = $this->_tp_list_pluck( $edit_ids, 'term_id' );
                $this->_clean_term_cache( $edit_term_ids, $taxonomy );
                $this->_do_action( 'edited_term_taxonomies', $edit_tt_ids );
            }
            $deleted_term = $this->_get_term( $term, $taxonomy );
            $object_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " object_id FROM $this->tpdb->term_relationships WHERE term_taxonomy_id = %d", $tt_id ) );
            foreach ( $object_ids as $object_id ) {
                if ( ! isset( $default ) ) {
                    $this->_tp_remove_object_terms( $object_id, $term, $taxonomy );
                    continue;
                }
                $terms = $this->_tp_get_object_terms($object_id,$taxonomy,['fields' => 'ids','orderby' => 'none',]);
                if ( isset( $default ) && 1 === count( $terms )) $terms = [$default];
                else {
                    $terms = array_diff( $terms, array( $term ) );
                    if ( isset( $default, $force_default ) && $force_default ) $terms = $this->_tp_array_merge( $terms, [$default] );
                }
                $terms = array_map( 'intval', $terms );
                $this->_tp_set_object_terms( $object_id, $terms, $taxonomy );
            }
            $tax_object = $this->_get_taxonomy( $taxonomy );
            foreach ( $tax_object->object_type as $object_type ) $this->_clean_object_term_cache( $object_ids, $object_type );
            $term_meta_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " meta_id FROM $this->tpdb->term_meta WHERE term_id = %d ", $term ) );
            foreach ( $term_meta_ids as $mid ) $this->_delete_metadata_by_mid( 'term', $mid );
            $this->_do_action( 'delete_term_taxonomy', $tt_id );
            $this->tpdb->delete( $this->tpdb->term_taxonomy, array( 'term_taxonomy_id' => $tt_id ) );
            $this->_do_action( 'deleted_term_taxonomy', $tt_id );
            // Delete the term if no taxonomies use it.
            if ( ! $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_taxonomy WHERE term_id = %d", $term ) ) )
                $this->tpdb->delete( $this->tpdb->terms, array( 'term_id' => $term ) );
            $this->_clean_term_cache( $term, $taxonomy );
            $this->_do_action( 'delete_term', $term, $tt_id, $taxonomy, $deleted_term, $object_ids );
            $this->_do_action( "delete_{$taxonomy}", $term, $tt_id, $deleted_term, $object_ids );
            return true;
        }//1956
        /**
         * @description Deletes one existing category.
         * @param $cat_ID
         * @return bool
         */
        protected function _tp_delete_category( $cat_ID ):bool{
            return $this->_tp_delete_term( $cat_ID, 'category' );
        }//2169
        /**
         * @description Retrieves the terms associated with the given object(s), in the supplied taxonomies.
         * @param $object_ids
         * @param $taxonomies
         * @param array $args
         * @return string
         */
        protected function _tp_get_object_terms( $object_ids, $taxonomies, ...$args):string{
            if ( empty( $object_ids ) || empty( $taxonomies ) ) return [];
            if ( ! is_array( $taxonomies ) ) $taxonomies = array( $taxonomies );
            foreach ( $taxonomies as $taxonomy ) {
                if ( ! $this->_taxonomy_exists( $taxonomy ) )
                    return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            }
            if ( ! is_array( $object_ids ) ) $object_ids = array( $object_ids );
            $object_ids = array_map( 'intval', $object_ids );
            $args = $this->_tp_parse_args( $args );
            $args = $this->_apply_filters( 'tp_get_object_terms_args', $args, $object_ids, $taxonomies );
            $terms = array();
            if ( count( $taxonomies ) > 1 ) {
                foreach ( $taxonomies as $index => $taxonomy ) {
                    $t = $this->_get_taxonomy( $taxonomy );
                    if ( isset( $t->args ) && is_array( $t->args ) && array_merge( $args, $t->args ) !== $args ) {
                        unset( $taxonomies[ $index ] );
                        $terms = $this->_tp_array_merge( $terms, $this->_tp_get_object_terms( $object_ids, $taxonomy, array_merge( $args, $t->args )));
                    }
                }
            } else {
                $t = $this->_get_taxonomy( $taxonomies[0] );
                if ( isset( $t->args ) && is_array( $t->args ) ) $args = array_merge( $args, $t->args );
            }
            $args['taxonomy']   = $taxonomies;
            $args['object_ids'] = $object_ids;
            if ( ! empty( $taxonomies ) ) {
                $terms_from_remaining_taxonomies = $this->_get_terms( $args );
                if ( ! empty( $args['fields'] ) && 0 === strpos( $args['fields'], 'id=>' ) )
                    $terms += $terms_from_remaining_taxonomies;
                else $terms = array_merge( $terms, $terms_from_remaining_taxonomies );
            }
            $terms = $this->_apply_filters( 'get_object_terms', $terms, $object_ids, $taxonomies, $args );
            $object_ids = implode( ',', $object_ids );
            $taxonomies = "'" . implode( "', '", array_map( 'esc_sql', $taxonomies ) ) . "'";
            return $this->_apply_filters( 'tp_get_object_terms', $terms, $object_ids, $taxonomies, $args );
        }//2189
        /**
         *  @description Add a new term to the database.
         * @param $term
         * @param $taxonomy
         * @param \array[] ...$args
         * @return array| TP_Error
         */
        protected function _tp_insert_term( $term, $taxonomy, array ...$args){
            $this->tpdb = $this->_init_db();
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            $term = $this->_apply_filters( 'pre_insert_term', $term, $taxonomy );
            if ( $this->_init_error( $term ) ) return $term;
            if ( is_int( $term ) && 0 === $term )
                return new TP_Error( 'invalid_term_id', $this->__( 'Invalid term ID.' ) );
            if ( '' === trim( $term ) )
                return new TP_Error( 'empty_term_name', $this->__( 'A name is required for this term.' ) );
            $defaults = ['alias_of' => '', 'description' => '','parent' => 0,'slug' => '',];
            $args     = $this->_tp_parse_args( $args, $defaults );
            if ( (int) $args['parent'] > 0 && ! $this->_term_exists( (int) $args['parent'] ) )
                return new TP_Error( 'missing_parent', $this->__( 'Parent term does not exist.' ) );
            $args['name']     = $term;
            $args['taxonomy'] = $taxonomy;
            $args['description'] = (string) $args['description'];
            $args = $this->_sanitize_term( $args, $taxonomy, 'db' );
            $name        = $this->_tp_unslash( $args['name'] );
            $description = $this->_tp_unslash( $args['description'] );
            $parent      = (int) $args['parent'];
            $slug_provided = ! empty( $args['slug'] );
            if ( ! $slug_provided ) $slug = $this->_sanitize_title( $name );
            else  $slug = $args['slug'];
            $term_group = 0;
            if ( $args['alias_of'] ) {
                $alias = $this->_get_term_by( 'slug', $args['alias_of'], $taxonomy );
                if ( ! empty( $alias->term_group ) ) $term_group = $alias->term_group;
                elseif ( ! empty( $alias->term_id ) ) {
                    $term_group = $this->tpdb->get_var( TP_SELECT . " MAX(term_group) FROM $this->tpdb->terms" ) + 1;
                    $this->_tp_update_term($alias->term_id,$taxonomy, ['term_group' => $term_group,]);
                }
            }
            $name_matches = $this->_get_terms(
                ['taxonomy' => $taxonomy,'name' => $name,'hide_empty' => false, 'parent' => $args['parent'], 'update_term_meta_cache' => false,]
            );
            $name_match = null;
            if ( $name_matches ) {
                foreach ( $name_matches as $_match ) {
                    if ( strtolower( $name ) === strtolower( $_match->name ) ) {
                        $name_match = $_match;
                        break;
                    }
                }
            }
            if ( $name_match ) {
                $slug_match = $this->_get_term_by( 'slug', $slug, $taxonomy );
                if ( ! $slug_provided || $name_match->slug === $slug || $slug_match ) {
                    if ( $this->_is_taxonomy_hierarchical( $taxonomy ) ) {
                        $siblings = $this->_get_terms(['taxonomy' => $taxonomy,'get' => 'all','parent' => $parent,'update_term_meta_cache' => false,]);
                        $existing_term = null;
                        $sibling_names = $this->_tp_list_pluck( $siblings, 'name' );
                        $sibling_slugs = $this->_tp_list_pluck( $siblings, 'slug' );
                        if ( ( ! $slug_provided || $name_match->slug === $slug ) && in_array( $name, $sibling_names, true ) ) $existing_term = $name_match;
                        elseif ( $slug_match && in_array( $slug, $sibling_slugs, true ) ) $existing_term = $slug_match;
                        if ( $existing_term ) return (string) new TP_Error( 'term_exists', $this->__( 'A term with the name provided already exists with this parent.' ), $existing_term->term_id );
                    } else return new TP_Error( 'term_exists', $this->__( 'A term with the name provided already exists in this taxonomy.' ), $name_match->term_id );
                }
            }
            $slug = $this->_tp_unique_term_slug( $slug, (object) $args );
            $data = compact( 'name', 'slug', 'term_group' );
            $data = $this->_apply_filters( 'tp_insert_term_data', $data, $taxonomy, $args );
            if ( false === $this->tpdb->insert( $this->tpdb->terms, $data ) )
                return new TP_Error( 'db_insert_error', $this->__( 'Could not insert term into the database.' ), $this->tpdb->last_error );
            $term_id = (int) $this->tpdb->insert_id;
            if ( empty( $slug ) ) {
                $slug = $this->_sanitize_title( $slug, $term_id );
                $this->_do_action( 'edit_terms', $term_id, $taxonomy );
                $this->tpdb->update( $this->tpdb->terms, compact( 'slug' ), compact( 'term_id' ) );
                $this->_do_action( 'edited_terms', $term_id, $taxonomy );
            }
            $tt_id = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " tt.term_taxonomy_id FROM $this->tpdb->term_taxonomy AS tt INNER JOIN $this->tpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d", $taxonomy, $term_id ) );
            if ( ! empty( $tt_id ) ) return ['term_id' => $term_id,'term_taxonomy_id' => $tt_id,];
            if ( false === $this->tpdb->insert( $this->tpdb->term_taxonomy, compact( 'term_id', 'taxonomy', 'description', 'parent' ) + array( 'count' => 0 ) ) )
                return new TP_Error( 'db_insert_error', $this->__( 'Could not insert term taxonomy into the database.' ), $this->tpdb->last_error );
            $tt_id = (int) $this->tpdb->insert_id;
            $duplicate_term = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " t.term_id, t.slug, tt.term_taxonomy_id, tt.taxonomy FROM $this->tpdb->terms t INNER JOIN $this->tpdb->term_taxonomy tt ON ( tt.term_id = t.term_id ) WHERE t.slug = %s AND tt.parent = %d AND tt.taxonomy = %s AND t.term_id < %d AND tt.term_taxonomy_id != %d", $slug, $parent, $taxonomy, $term_id, $tt_id ) );
            $duplicate_term = $this->_apply_filters( 'tp_insert_term_duplicate_term_check', $duplicate_term, $term, $taxonomy, $args, $tt_id );
            if ( $duplicate_term ) {
                $this->tpdb->delete( $this->tpdb->terms, array( 'term_id' => $term_id ) );
                $this->tpdb->delete( $this->tpdb->term_taxonomy, array( 'term_taxonomy_id' => $tt_id ) );
                $term_id = (int) $duplicate_term->term_id;
                $tt_id   = (int) $duplicate_term->term_taxonomy_id;
                $this->_clean_term_cache( $term_id, $taxonomy );
                return ['term_id' => $term_id, 'term_taxonomy_id' => $tt_id,];
            }
            $this->_do_action( 'create_term', $term_id, $tt_id, $taxonomy );
            $this->_do_action( "create_{$taxonomy}", $term_id, $tt_id );
            $term_id = $this->_apply_filters( 'term_id_filter', $term_id, $tt_id );
            $this->_clean_term_cache( $term_id, $taxonomy );
            $this->_do_action( 'created_term', $term_id, $tt_id, $taxonomy );
            $this->_do_action( "created_{$taxonomy}", $term_id, $tt_id );
            $this->_do_action( 'saved_term', $term_id, $tt_id, $taxonomy, false );
            $this->_do_action( "saved_{$taxonomy}", $term_id, $tt_id, false );
            return ['term_id'=> $term_id, 'term_taxonomy_id' => $tt_id,];
        }//2338
        /**
         * @description Create Term and Taxonomy Relationships.
         * @param $object_id
         * @param $terms
         * @param $taxonomy
         * @param bool $append
         * @return array|TP_Error
         */
        protected function _tp_set_object_terms( $object_id, $terms, $taxonomy, $append = false ){
            $this->tpdb = $this->_init_db();
            $object_id = (int) $object_id;
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            if ( ! is_array( $terms ) )
                $terms = [$terms];
            if ( ! $append ) {
                $old_tt_ids = $this->_tp_get_object_terms(
                    $object_id,
                    $taxonomy,
                    ['fields' => 'tt_ids','orderby' => 'none','update_term_meta_cache' => false,]
                );
            } else $old_tt_ids = [];
            $tt_ids     = [];
            //$term_ids   = []; //todo, needs a meaning
            $new_tt_ids = [];
            foreach ( (array) $terms as $term ) {
                if ( '' === trim( $term ) ) continue;
                $term_info = $this->_term_exists( $term, $taxonomy );
                if ( ! $term_info ) {
                    if ( is_int( $term ) ) continue;
                    $term_info = $this->_tp_insert_term( $term, $taxonomy );
                }
                if ( $this->_init_error( $term_info ) ) return $term_info;
                //$term_ids[] = $term_info['term_id']; //todo, needs a meaning
                $tt_id      = $term_info['term_taxonomy_id'];
                $tt_ids[]   = $tt_id;
                if ( $this->tpdb->get_var( $this->tpdb->prepare( TP_INSERT . " term_taxonomy_id FROM $this->tpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $object_id, $tt_id ) ) )
                    continue;
                $this->_do_action( 'add_term_relationship', $object_id, $tt_id, $taxonomy );
                $this->tpdb->insert($this->tpdb->term_relationships,['object_id' => $object_id, 'term_taxonomy_id' => $tt_id,]);
                $this->_do_action( 'added_term_relationship', $object_id, $tt_id, $taxonomy );
                $new_tt_ids[] = $tt_id;
            }
            if ( $new_tt_ids ) $this->_tp_update_term_count( $new_tt_ids, $taxonomy );
            if ( ! $append ) {
                $delete_tt_ids = array_diff( $old_tt_ids, $tt_ids );
                if ( $delete_tt_ids ) {
                    $in_delete_tt_ids = "'" . implode( "', '", $delete_tt_ids ) . "'";
                    $delete_term_ids  = $this->tpdb->get_col( $this->tpdb->prepare( TP_INSERT . " tt.term_id FROM $this->tpdb->term_taxonomy AS tt WHERE tt.taxonomy = %s AND tt.term_taxonomy_id IN ($in_delete_tt_ids)", $taxonomy ) );
                    $delete_term_ids  = array_map( 'intval', $delete_term_ids );
                    $remove = $this->_tp_remove_object_terms( $object_id, $delete_term_ids, $taxonomy );
                    if ( $this->_init_error( $remove ) ) return $remove;
                }
            }
            $t = $this->_get_taxonomy( $taxonomy );
            if ( ! $append && isset( $t->sort ) && $t->sort ) {
                $values     = [];
                $term_order = 0;
                $final_tt_ids = $this->_tp_get_object_terms( $object_id, $taxonomy, ['fields' => 'tt_ids', 'update_term_meta_cache' => false,]);
                foreach ( $tt_ids as $tt_id ) {
                    if ( in_array( (int) $tt_id, $final_tt_ids, true ) )
                        $values[] = $this->tpdb->prepare( '(%d, %d, %d)', $object_id, $tt_id, ++$term_order );
                }
                if ($values && false === $this->tpdb->query(TP_INSERT . " INTO $this->tpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES " . implode(',', $values) . ' ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)')) return new TP_Error( 'db_insert_error', $this->__( 'Could not insert term relationship into the database.' ), $this->tpdb->last_error );
            }
            $this->_tp_cache_delete( $object_id, $taxonomy . '_relationships' );
            $this->_tp_cache_delete( 'last_changed', 'terms' );
            $this->_do_action( 'set_object_terms', $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids );
            return $tt_ids;
        }//2709
        /**
         * @description Add term(s) associated with a given object.
         * @param $object_id
         * @param $terms
         * @param $taxonomy
         * @return array|TP_Error
         */
        protected function _tp_add_object_terms( $object_id, $terms, $taxonomy ){
            return $this->_tp_set_object_terms( $object_id, $terms, $taxonomy, true );
        }//2880
    }
}else die;