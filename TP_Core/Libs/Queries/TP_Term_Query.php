<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 13:00
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    class TP_Term_Query extends Query_Base{
        protected $_sql_clauses = ['select' => '','from' => '','where' => [],'orderby' => '','limits' => '',];
        public function __construct( $query = '' ){
            $this->meta_query = $this->_init_meta_query();
            $this->query_var_defaults = [
                'taxonomy' => null,'object_ids' => null,'orderby' => 'name','order' => 'ASC','hide_empty' => true,'include' => [],
                'exclude' => [],'exclude_tree' => [],'number' => '','offset' => '','fields' => 'all','count' => false,
                'name' => '','slug' => '','term_taxonomy_id' => '','hierarchical' => true,'search' => '','name__like' => '',
                'description__like' => '','pad_counts' => false,'get' => '','child_of' => 0,'parent' => '','childless' => false,
                'cache_domain' => 'core','update_term_meta_cache' => true,'meta_query' => '','meta_key' => '','meta_value' => '','meta_type' => '','meta_compare' => '',
            ];
            if ( ! empty( $query ) ) $this->query_term( $query );
        }//195
        /**
         * @description Parse arguments passed to the term query with default query parameters.
         * @param array ...$query
         */
        public function parse_query( ...$query): void{
            if ( empty( $query ) ) $query = $this->query_vars;
            $taxonomies = isset( $query['taxonomy'] ) ? (array) $query['taxonomy'] : null;
            $this->query_var_defaults = $this->_apply_filters( 'get_terms_defaults', $this->query_var_defaults, $taxonomies );
            $query = $this->_tp_parse_args($query, $this->query_var_defaults );
            $query['number'] = $this->_abs_int( $query['number'] );
            $query['offset'] = $this->_abs_int( $query['offset'] );
            if ( 0 < (int) $query['parent'] ) $query['child_of'] = false;
            if ( 'all' === $query['get'] ) {
                $query['childless']    = false;
                $query['child_of']     = 0;
                $query['hide_empty']   = 0;
                $query['hierarchical'] = false;
                $query['pad_counts']   = false;
            }
            $query['taxonomy'] = $taxonomies;
            $this->query_vars = $query;
            $this->_do_action( 'parse_term_query', $this );
        }//242
        /**
         *  @description Sets up the query and retrieves the results.
         * @param $query
         * @return array|null|string
         */
        public function query_term( $query ){
            $this->query_vars = $this->_tp_parse_args( $query );
            return $this->get_terms();
        }//305
        /**
         * @description Retrieves the query results.
         * @return array|null|string
         */
        public function get_terms(){
            $this->parse_query( $this->query_vars );
            $tpdb = $this->_init_db();
            $args = &$this->query_vars;
            $this->meta_query->parse_query_vars( $args );
            $this->_do_action_ref_array( 'pre_get_terms', array( &$this ) );
            $taxonomies = (array) $args['taxonomy'];
            $has_hierarchical_tax = false;
            if ( $taxonomies ) {
                foreach ( $taxonomies as $_tax )  if ($this->_is_taxonomy_hierarchical( $_tax )) $has_hierarchical_tax = true;
            }else $has_hierarchical_tax = true;
            if ( ! $has_hierarchical_tax ) {
                $args['hierarchical'] = false;
                $args['pad_counts']   = false;
            }
            if ( 0 < (int) $args['parent'] ) $args['child_of'] = false;
            if ( 'all' === $args['get'] ) {
                $args['childless']    = false;
                $args['child_of']     = 0;
                $args['hide_empty']   = 0;
                $args['hierarchical'] = false;
                $args['pad_counts']   = false;
            }
            $args = $this->_apply_filters( 'get_terms_args', $args, $taxonomies );
            $child_of = $args['child_of'];
            $parent   = $args['parent'];
            if ( $child_of ) $_parent = $child_of;
            elseif ( $parent ) $_parent = $parent;
            else $_parent = false;
            if ( $_parent ) {
                $in_hierarchy = false;
                foreach ( $taxonomies as $_tax ) {
                    $hierarchy = $this->_get_term_hierarchy( $_tax );
                    if ( isset( $hierarchy[ $_parent ] ) ) $in_hierarchy = true;
                }
                if ( ! $in_hierarchy ) {
                    if ( 'count' === $args['fields'] ) return 0;
                    else {
                        $this->terms = [];
                        return $this->terms;
                    }
                }
            }
            $_orderby = $this->query_vars['orderby'];
            if ( 'term_order' === $_orderby && empty( $this->query_vars['object_ids'] ) )
                $_orderby = 'term_id';
            $orderby = $this->_parse_orderby( $_orderby );
            if ( $orderby ) $orderby = "ORDER BY $orderby";
            $order = $this->_parse_order( $this->query_vars['order'] );
            if ( $taxonomies ) $this->_sql_clauses['where']['taxonomy'] = "tt.taxonomy IN ('" . implode( "', '", array_map( 'esc_sql', $taxonomies ) ) . "')";
            $exclude      = $args['exclude'];
            $exclude_tree = $args['exclude_tree'];
            $include      = $args['include'];
            $inclusions = '';
            if ( ! empty( $include ) ) {
                $exclude      = '';
                $exclude_tree = '';
                $inclusions   = implode( ',', $this->_tp_parse_id_list( $include ) );
            }
            if (!empty( $inclusions )) $this->_sql_clauses['where']['inclusions'] = 't.term_id IN ( ' . $inclusions . ' )';
            $exclusions = [];
            if ( ! empty( $exclude_tree ) ) {
                $exclude_tree      = $this->_tp_parse_id_list( $exclude_tree );
                $excluded_children = $exclude_tree;
                foreach ($exclude_tree as $ex_trunk ) {
                    $_get_terms = (array) $this->_get_terms(['taxonomy' => reset( $taxonomies ),'child_of' => (int) $ex_trunk,'fields' => 'ids','hide_empty' => 0,]);
                    $excluded_children = $this->_tp_array_merge($excluded_children,$_get_terms);
                }
                $exclusions = array_merge( $excluded_children, $exclusions );
            }
            if ( ! empty( $exclude ) ) $exclusions = array_merge( $this->_tp_parse_id_list( $exclude ), $exclusions );
            $childless = (bool) $args['childless'];
            if ( $childless ) {
                foreach ( $taxonomies as $_tax ) {
                    $term_hierarchy = $this->_get_term_hierarchy( $_tax );
                    $exclusions     = $this->_tp_array_merge(array_keys( $term_hierarchy ), $exclusions);
                }
            }
            if ( ! empty( $exclusions ) )
                $exclusions = 't.term_id NOT IN (' . implode( ',', array_map( 'intval', $exclusions ) ) . ')';
            else $exclusions = '';
            $exclusions = $this->_apply_filters( 'list_terms_exclusions', $exclusions, $args, $taxonomies );
            if ( ! empty( $exclusions ) )
                $this->_sql_clauses['where']['exclusions'] = preg_replace( '/^\s*AND\s*/', '', $exclusions );
            if (( ! empty( $args['name'] ) ) ||( is_string( $args['name'] ) && $args['name'] !== '')){
                $this->_names = (array) $args['name'];
                foreach ( $this->_names as &$_name )
                    $_name = stripslashes( $this->_sanitize_term_field( 'name', $_name, 0, reset( $taxonomies ), 'db' ) );
                unset($_name);
                $this->_sql_clauses['where']['name'] = "t.name IN ('" . implode( "', '", array_map( 'esc_sql', $this->_names ) ) . "')";
            }
            if (( ! empty( $args['slug'] ) ) ||( is_string( $args['slug'] ) && $args['slug'] !== '')){
                if ( is_array( $args['slug'] ) ) {
                    $slug = array_map( 'sanitize_title', $args['slug'] );
                    $this->_sql_clauses['where']['slug'] = "t.slug IN ('" . implode( "', '", $slug ) . "')";
                } else {
                    $slug = $this->_sanitize_title( $args['slug'] );
                    $this->_sql_clauses['where']['slug'] = "t.slug = '$slug'";
                }
            }
            if ( ! empty( $args['term_taxonomy_id'] ) ) {
                if ( is_array( $args['term_taxonomy_id'] ) ) {
                    $tt_ids = implode( ',', array_map( 'intval', $args['term_taxonomy_id'] ) );
                    $this->_sql_clauses['where']['term_taxonomy_id'] = "tt.term_taxonomy_id IN ({$tt_ids})";
                } else $this->_sql_clauses['where']['term_taxonomy_id'] = $tpdb->prepare( 'tt.term_taxonomy_id = %d', $args['term_taxonomy_id'] );
            }
            if ( ! empty( $args['name__like'] ) )
                $this->_sql_clauses['where']['name__like'] = $tpdb->prepare( 't.name LIKE %s', '%' . $tpdb->esc_like( $args['name__like'] ) . '%' );
            if ( ! empty( $args['description__like'] ) )
                $this->_sql_clauses['where']['description__like'] = $tpdb->prepare( 'tt.description LIKE %s', '%' . $tpdb->esc_like( $args['description__like'] ) . '%' );
            if ( ! empty( $args['object_ids'] ) ) {
                $object_ids = $args['object_ids'];
                if ( ! is_array( $object_ids ) ) $object_ids = array( $object_ids );
                $object_ids = implode( ', ', array_map( 'intval', $object_ids ) );
                $this->_sql_clauses['where']['object_ids'] = "tr.object_id IN ($object_ids)";
            }
            if ( ! empty( $args['object_ids'] ) ) $args['hide_empty'] = false;
            if ( '' !== $parent ) {
                $parent = (int) $parent;
                $this->_sql_clauses['where']['parent'] = "tt.parent = '$parent'";
            }
            $hierarchical = $args['hierarchical'];
            if ( 'count' === $args['fields'] ) {
                $hierarchical = false;
            }
            if ( $args['hide_empty'] && ! $hierarchical ) {
                $this->_sql_clauses['where']['count'] = 'tt.count > 0';
            }
            $number = $args['number'];
            $offset = $args['offset'];
            if ( $number && ! $hierarchical && ! $child_of && '' === $parent ) {
                if ( $offset ) $limits = 'LIMIT ' . $offset . ',' . $number;
                else  $limits = 'LIMIT ' . $number;
            } else $limits = '';
            if ( ! empty( $args['search'] ) )
                $this->_sql_clauses['where']['search'] = $this->_get_search_sql( $args['search'] );
            $join     = '';
            $distinct = '';
            $this->meta_query->parse_query_vars( $this->query_vars );
            $mq_sql       = $this->meta_query->get_sql( 'term', 't', 'term_id' );
            $meta_clauses = $this->meta_query->get_clauses();
            if ( ! empty( $meta_clauses ) ) {
                $join .= $mq_sql['join'];
                $this->_sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $mq_sql['where'] );
                $distinct .= 'DISTINCT';
            }
            $selects = array();
            switch ( $args['fields'] ) {
                case 'all':
                case 'all_with_object_id':
                case 'tt_ids':
                case 'slugs':
                    $selects = array( 't.*', 'tt.*' );
                    if ( 'all_with_object_id' === $args['fields'] && ! empty( $args['object_ids'] ) )
                        $selects[] = 'tr.object_id';
                    break;
                case 'ids':
                case 'id=>parent':
                    $selects = array( 't.term_id', 'tt.parent', 'tt.count', 'tt.taxonomy' );
                    break;
                case 'names':
                    $selects = array( 't.term_id', 'tt.parent', 'tt.count', 't.name', 'tt.taxonomy' );
                    break;
                case 'count':
                    $orderby = '';
                    $order   = '';
                    $selects = array( 'COUNT(*)' );
                    break;
                case 'id=>name':
                    $selects = array( 't.term_id', 't.name', 'tt.parent', 'tt.count', 'tt.taxonomy' );
                    break;
                case 'id=>slug':
                    $selects = array( 't.term_id', 't.slug', 'tt.parent', 'tt.count', 'tt.taxonomy' );
                    break;
            }
            $_fields = $args['fields'];
            $fields = implode( ', ', $this->_apply_filters( 'get_terms_fields', $selects, $args, $taxonomies ) );
            $join .= " INNER JOIN ". $tpdb->term_taxonomy. " AS tt ON t.term_id = tt.term_id";
            if ( ! empty( $this->query_vars['object_ids'] ) )
                $join .= " INNER JOIN {$tpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id";
            $where = implode( ' AND ', $this->_sql_clauses['where'] );
            $clauses = $this->_apply_filters( 'terms_clauses', compact( 'fields', 'join', 'where', 'distinct', 'orderby', 'order', 'limits' ), $taxonomies, $args );
            $fields   = $clauses['fields'] ?? '';
            $join     = $clauses['join'] ?? '';
            $where    = $clauses['where'] ?? '';
            $distinct = $clauses['distinct'] ?? '';
            $orderby  = $clauses['orderby'] ?? '';
            $order    = $clauses['order'] ?? '';
            $limits   = $clauses['limits'] ?? '';
            if ( $where ) $where = "WHERE $where";
            $this->_sql_clauses['select']  = "SELECT $distinct $fields";
            $this->_sql_clauses['from']    = "FROM ". $tpdb->terms ." AS t $join";
            $this->_sql_clauses['orderby'] = $orderby ? "$orderby $order" : '';
            $this->_sql_clauses['limits']  = $limits;
            $this->request = "{$this->_sql_clauses['select']} {$this->_sql_clauses['from']} {$where} {$this->_sql_clauses['orderby']} {$this->_sql_clauses['limits']}";
            $this->terms = null;
            $this->terms = $this->_apply_filters_ref_array( 'terms_pre_query', array( $this->terms, &$this ) );
            if ( null !== $this->terms ) return $this->terms;
            $key          = md5( serialize( $this->_tp_array_slice_assoc( $args, array_keys( $this->query_var_defaults ) ) ) . serialize( $taxonomies ) . $this->request );
            $last_changed = $this->_tp_cache_get_last_changed( 'terms' );
            $cache_key    = "get_terms:$key:$last_changed";
            $cache        = $this->_tp_cache_get( $cache_key, 'terms' );
            if ( false !== $cache ) {
                if ( 'all' === $_fields || 'all_with_object_id' === $_fields )
                    $cache = $this->_populate_terms( $cache );
                $this->terms = $cache;
                return $this->terms;
            }
            if ( 'count' === $_fields ) {
                $count = $tpdb->get_var( $this->request );
                $this->_tp_cache_set( $cache_key, $count, 'terms' );
                return $count;
            }
            $terms = $tpdb->get_results( $this->request );
            if ( 'all' === $_fields || 'all_with_object_id' === $_fields )
                $this->_update_term_cache( $terms );
            if ( $args['update_term_meta_cache'] ) {
                $term_ids = $this->_tp_list_pluck( $terms, 'term_id' );
                $this->_update_term_meta_cache( $term_ids );
            }
            if ( empty( $terms ) ) {
                $this->_tp_cache_add( $cache_key, array(), 'terms', DAY_IN_SECONDS );
                return array();
            }
            if ( $child_of ) {
                foreach ( $taxonomies as $_tax ) {
                    $children = $this->_get_term_hierarchy( $_tax );
                    if ( ! empty( $children ) ) $terms = $this->_get_term_children( $child_of, $terms, $_tax );
                }
            }
            if ( $args['pad_counts'] && 'all' === $_fields ) {
                foreach ( $taxonomies as $_tax ) $this->_pad_term_counts( $terms, $_tax );
            }
            if ( $hierarchical && $args['hide_empty'] && is_array( $terms ) ) {
                foreach ($terms as $k => $term ) {
                    if ( ! $term->count ) {
                        $children[] = $this->_get_term_children( null,$term->term_id, $term->taxonomy );
                        if ( is_array( $children ) ) {
                            foreach ( $children as $child_id ) {
                                $child = $this->_get_term( $child_id, $term->taxonomy );
                                if ( $child->count ) continue 2;
                            }
                        }
                        unset( $terms[ $k ] );
                    }
                }
            }
            if ( ! empty( $args['object_ids'] ) && 'all_with_object_id' !== $_fields ) {
                $_tt_ids = [];
                $_terms  = [];
                foreach ( $terms as $term ) {
                    if (isset( $_tt_ids[ $term->term_id ])) continue;
                    $_tt_ids[ $term->term_id ] = 1;
                    $_terms[] = $term;
                }
                $terms = $_terms;
            }
            $_terms = array();
            if ( 'id=>parent' === $_fields ) {
                foreach ( $terms as $term )  $_terms[ $term->term_id ] = $term->parent;
            } elseif ( 'ids' === $_fields ) {
                foreach ( $terms as $term ) $_terms[] = (int) $term->term_id;
            } elseif ( 'tt_ids' === $_fields ) {
                foreach ( $terms as $term ) $_terms[] = (int) $term->term_taxonomy_id;
            } elseif ( 'names' === $_fields ) {
                foreach ( $terms as $term ) $_terms[] = $term->name;
            } elseif ( 'slugs' === $_fields ) {
                foreach ( $terms as $term ) $_terms[] = $term->slug;
            } elseif ( 'id=>name' === $_fields ) {
                foreach ( $terms as $term )  $_terms[ $term->term_id ] = $term->name;
            } elseif ( 'id=>slug' === $_fields ) {
                foreach ( $terms as $term ) $_terms[ $term->term_id ] = $term->slug;
            }
            if ( ! empty( $_terms ) ) $terms = $_terms;
            if ( $hierarchical && $number && is_array( $terms ) ) {
                if ( $offset >= count( $terms ) ) $terms = array();
                else $terms = array_slice( $terms, $offset, $number, true );
            }
            $this->_tp_cache_add( $cache_key, $terms, 'terms', DAY_IN_SECONDS );
            if ( 'all' === $_fields || 'all_with_object_id' === $_fields )
                $terms = $this->_populate_terms( $terms );
            $this->terms = $terms;
            return $this->terms;
        }//347
        /**
         * @description Parse and sanitize 'orderby' keys passed to the term query.
         * @param $orderby_raw
         * @return string
         */
        protected function _parse_orderby( $orderby_raw ): string{
            $_orderby = strtolower( $orderby_raw );
            $maybe_orderby_meta = false;
            if ( in_array( $_orderby, array( 'term_id', 'name', 'slug', 'term_group' ), true ) )
                $orderby = "t.$_orderby";
            elseif ( in_array( $_orderby, array( 'count', 'parent', 'taxonomy', 'term_taxonomy_id', 'description' ), true ) )
                $orderby = "tt.$_orderby";
            elseif ( 'term_order' === $_orderby ) $orderby = 'tr.term_order';
            elseif ( 'include' === $_orderby && ! empty( $this->query_vars['include'] ) ) {
                $include = implode( ',', $this->_tp_parse_id_list( $this->query_vars['include'] ) );
                $orderby = "FIELD( t.term_id, $include )";
            } elseif ( 'slug__in' === $_orderby && ! empty( $this->query_vars['slug'] ) && is_array( $this->query_vars['slug'] ) ) {
                $slugs   = implode( "', '", array_map( 'sanitize_title_for_query', $this->query_vars['slug'] ) );
                $orderby = "FIELD( t.slug, '" . $slugs . "')";
            } elseif ( 'none' === $_orderby ) $orderby = '';
            elseif ( empty( $_orderby ) || 'id' === $_orderby || 'term_id' === $_orderby ) $orderby = 't.term_id';
            else {
                $orderby = 't.name';
                $maybe_orderby_meta = true;
            }
            $orderby = $this->_apply_filters( 'get_terms_orderby', $orderby, $this->query_vars, $this->query_vars['taxonomy'] );
            if ( $maybe_orderby_meta ) {
                $maybe_orderby_meta = $this->_parse_orderby_meta( $_orderby );
                if ( $maybe_orderby_meta ) $orderby = $maybe_orderby_meta;
           }
            return $orderby;
        }//903
        /**
         * @description Generate the ORDER BY clause for an 'orderby' param that is potentially related to a meta query.
         * @param $orderby_raw
         * @return string
         */
        protected function _parse_orderby_meta( $orderby_raw ): string{
            $orderby = '';
            $this->meta_query->get_sql( 'term', 't', 'term_id' );
            $meta_clauses = $this->meta_query->get_clauses();
            if ( ! $meta_clauses || ! $orderby_raw ) return $orderby;
            $allowed_keys       = array();
            $primary_meta_key   = null;
            $primary_meta_query = reset( $meta_clauses );
            if ( ! empty( $primary_meta_query['key'] ) ) {
                $primary_meta_key = $primary_meta_query['key'];
                $allowed_keys[]   = $primary_meta_key;
            }
            $allowed_keys[] = 'meta_value';
            $allowed_keys[] = 'meta_value_num';
            $allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
            if ( ! in_array( $orderby_raw, $allowed_keys, true ) ) return $orderby;
            switch ( $orderby_raw ) {
                case $primary_meta_key:
                case 'meta_value':
                    if ( ! empty( $primary_meta_query['type'] ) )
                        $orderby = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
                    else $orderby = "{$primary_meta_query['alias']}.meta_value";
                    break;
                case 'meta_value_num':
                    $orderby = "{$primary_meta_query['alias']}.meta_value+0";
                    break;
                default:
                    if ( array_key_exists( $orderby_raw, $meta_clauses ) ) {
                        $meta_clause = $meta_clauses[ $orderby_raw ];
                        $orderby     = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
                    }
                    break;
            }
            return $orderby;
        }//960
        /**
         * @description Parse an 'order' query variable and cast it to ASC or DESC as necessary.
         * @param $order
         * @return string
         */
        protected function _parse_order( $order ): ?string{
            if ( ! is_string( $order ) || empty( $order ) ) return 'DESC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
            else  return 'DESC';
        }//1019
        /**
         * @description Used internally to generate a SQL string related to the 'search' parameter.
         * @param $string
         * @return null|string
         */
        protected function _get_search_sql( $string ): ?string{
            $tpdb = $this->_init_db();
            $like = '%' . $tpdb->esc_like( $string ) . '%';
            return $tpdb->prepare( '((t.name LIKE %s) OR (t.slug LIKE %s))', $like, $like );
        }//1041
        /**
         * @description Creates an array of term objects from an array of term IDs.
         * @param $term_ids
         * @return array
         */
        protected function _populate_terms( $term_ids ): array {
            $terms = array();
            if (!is_array( $term_ids )) return $terms;
            foreach ( $term_ids as $key => $term_id ) {
                $term = $this->_get_term( $term_id );
                if ( $term instanceof TP_Term ) $terms[ $key ] = $term;
            }
            return $terms;
        }//1059
    }
}else die;