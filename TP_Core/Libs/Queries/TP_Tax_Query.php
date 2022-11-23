<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-4-2022
 * Time: 23:00
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_Tax_Query extends Query_Base{
        private static $__no_results = ['join'=> [''],'where' => ['0 = 1'],];
        protected $_table_aliases = [];
        public $primary_id_column;
        public $primary_table;
        public $queries = [];
        public $queried_terms = [];
        public $relation;
        /**
         * @description TP_Tax_Query constructor.
         * @param $tax_query
         */
        public function __construct( $tax_query ) {
            if ( isset( $tax_query['relation'] ) )
                $this->relation = $this->sanitize_relation( $tax_query['relation'] );
            else  $this->relation = 'AND';
            $this->queries = $this->sanitize_query( $tax_query );
        }//122
        /**
         * @description Ensure the 'tax_query' argument passed to the class constructor is well-formed.
         * @param $queries
         * @return array
         */
        public function sanitize_query( $queries ): array{
            $cleaned_query = [];
            $defaults = ['taxonomy' => '','terms' => [],'field' => 'term_id','operator' => 'IN','include_children' => true,];
            foreach ((array)$queries as $key => $query ){
                if ( 'relation' === $key )
                    $cleaned_query['relation'] = $this->sanitize_relation( $query );
                elseif(self::_is_first_order_clause($query)){
                    $cleaned_clause          = array_merge( $defaults, $query );
                    $cleaned_clause['terms'] = (array) $cleaned_clause['terms'];
                    $cleaned_query[]         = $cleaned_clause;
                    if ( ! empty( $cleaned_clause['taxonomy'] ) && 'NOT IN' !== $cleaned_clause['operator'] ) {
                        $taxonomy = $cleaned_clause['taxonomy'];
                        if ( ! isset( $this->queried_terms[ $taxonomy ] ) )
                            $this->queried_terms[ $taxonomy ] = [];
                        if ( ! empty( $cleaned_clause['terms'] ) && ! isset( $this->queried_terms[ $taxonomy ]['terms']))
                            $this->queried_terms[ $taxonomy ]['terms'] = $cleaned_clause['terms'];
                        if ( ! empty( $cleaned_clause['field'] ) && ! isset( $this->queried_terms[ $taxonomy ]['field'] ) )
                            $this->queried_terms[ $taxonomy ]['field'] = $cleaned_clause['field'];
                    }
                }elseif ( is_array( $query )){
                    $cleaned_sub_query = $this->sanitize_query( $query );
                    if ( ! empty( $cleaned_sub_query ) ) {
                        if ( ! isset( $cleaned_sub_query['relation']))
                            $cleaned_sub_query['relation'] = 'AND';
                        $cleaned_query[] = $cleaned_sub_query;
                    }
                }
            }
            return $cleaned_query;
        }//135
        /**
         * @description Sanitize a 'relation' operator.
         * @param $relation
         * @return string
         */
        public function sanitize_relation( $relation ): ?string{
            if ( 'OR' === strtoupper( $relation ) ) return 'OR';
            else return 'AND';
        }//206
        /**
         * @description Generates SQL clauses to be appended to a main query.
         * @param $primary_table
         * @param $primary_id_column
         * @return array
         */
        public function get_sql( $primary_table, $primary_id_column ): array{
            $this->primary_table     = $primary_table;
            $this->primary_id_column = $primary_id_column;
            return $this->_get_sql_clauses();
        }//246
        /**
         * @description Generate SQL JOIN and WHERE clauses for a "first-order" query clause.
         * @param $clause
         * @param $parent_query
         * @return array
         */
        public function get_sql_for_clause( &$clause, $parent_query ): array{
            $tpdb = $this->_init_db();
            $sql = ['where' => [],
                'join'  => [],];
            $join  = '';
            $where = '';
            $this->__clean_query( $clause );
            if ( $this->_init_error( $clause ) ) return self::$__no_results;
            $terms    = $clause['terms'];
            $operator = strtoupper( $clause['operator'] );
            if ( 'IN' === $operator ) {
                if ( empty( $terms ) ) return self::$__no_results;
                $terms = implode( ',', $terms );
                $alias = $this->_find_compatible_table_alias( $clause, $parent_query );
                if ( false === $alias ) {
                    $i     = count( $this->_table_aliases );
                    $alias = $i ? 'tt' . $i : $tpdb->term_relationships;
                    $this->_table_aliases[] = $alias;
                    $clause['alias'] = $alias;
                    $join .= " LEFT JOIN $tpdb->term_relationships";
                    $join .= $i ? " AS $alias" : '';
                    $join .= " ON ($this->primary_table.$this->primary_id_column = $alias.object_id)";
                }
                $where = "$alias.term_taxonomy_id $operator ($terms)";
            }elseif ( 'NOT IN' === $operator ){
                if ( empty( $terms ) ) return $sql;
                $terms = implode( ',', $terms );
                $where = "$this->primary_table.$this->primary_id_column NOT IN(SELECT object_id FROM $tpdb->term_relationships WHERE term_taxonomy_id IN ($terms))";
            } elseif ( 'AND' === $operator ) {
                if (empty($terms)) return $sql;
                $num_terms = count( $terms );
                $terms = implode( ',', $terms );
                $where = "(SELECT COUNT(1) FROM $tpdb->term_relationships WHERE term_taxonomy_id IN ($terms) AND object_id = $this->primary_table.$this->primary_id_column) = $num_terms";
            } elseif ( 'NOT EXISTS' === $operator || 'EXISTS' === $operator )
                //todo prepare
                $where = $tpdb->prepare(
                    "$operator (
				SELECT 1
				FROM $tpdb->term_relationships
				INNER JOIN $tpdb->term_taxonomy
				ON $tpdb->term_taxonomy.term_taxonomy_id = $tpdb->term_relationships.term_taxonomy_id
				WHERE $tpdb->term_taxonomy.taxonomy = %s
				AND $tpdb->term_relationships.object_id = $this->primary_table.$this->primary_id_column
			)",$clause['taxonomy']);
            $sql['join'][]  = $join;
            $sql['where'][] = $where;
            return $sql;
        }//383
        /**
         * @description Transforms a single query, from one field to another.
         * @param $query
         * @param $resulting_field
         */
        public function transform_query( &$query, $resulting_field ): void{
            if ( empty( $query['terms'] ) ) return;
            if ( $query['field'] === $resulting_field ) return;
            $resulting_field = $this->_sanitize_key( $resulting_field );
            $terms = array_filter( $query['terms'] );
            if ( empty( $terms ) ) {
                $query['terms'] = [];
                $query['field'] = $resulting_field;
                return;
            }
            $args = [
                'get' => 'all', 'number' => 0, 'taxonomy' => $query['taxonomy'],
                'update_term_meta_cache' => false, 'orderby' => 'none',];
            switch ( $query['field'] ) {
                case 'slug':
                    $args['slug'] = $terms;
                    break;
                case 'name':
                    $args['name'] = $terms;
                    break;
                case 'term_taxonomy_id':
                    $args['term_taxonomy_id'] = $terms;
                    break;
                default:
                    $args['include'] = $this->_tp_parse_id_list( $terms );
                    break;
            }
            $term_query = new TP_Term_Query();
            $term_list  = $term_query->query_term( $args );
            if ( $this->_init_error( $term_list ) ) {
                $query = $term_list;
                return;
            }
            if ( 'AND' === $query['operator'] && count( $term_list ) < count( $query['terms'] ) ) {
                $query = new TP_Error( 'in_existent_terms', $this->__( 'In Existent terms.' ) );
                return;
            }
            $query['terms'] = $this->_tp_list_pluck( $term_list, $resulting_field );
            $query['field'] = $resulting_field;
        }//597
        /**
         * @description Determine whether a clause is first-order.
         * @param $query
         * @return bool
         */
        protected static function _is_first_order_clause( $query ): bool{
            return is_array( $query ) && ( empty( $query ) || array_key_exists( 'terms', $query ) || array_key_exists( 'taxonomy', $query ) || array_key_exists( 'include_children', $query ) || array_key_exists( 'field', $query ) || array_key_exists( 'operator', $query ) );
        }//228
        /**
         * @description Generate SQL clauses to be appended to a main query.
         */
        protected function _get_sql_clauses(): array{
            $queries = $this->queries;
            $sql     = $this->_get_sql_for_query( $queries );
            if ( ! empty( $sql['where'] ) ) $sql['where'] = ' AND ' . $sql['where'];
            return $sql;
        }//268
        /**
         * @description Generate SQL clauses for a single query array.
         * @param $query
         * @param int $depth
         * @return array
         */
        protected function _get_sql_for_query( &$query, $depth = 0 ): array{
            $sql = ['join'  => '','where' => '',];
            $sql_chunks = ['join'  => [],'where' => []];
            $indent = '';
            for ( $i = 0; $i < $depth; $i++ ) $indent .= '  ';
            foreach ( $query as $key => &$clause ){
                if ( 'relation' === $key )$relation = $query['relation'];
                elseif ( is_array( $clause ) ) {
                    if ( self::_is_first_order_clause( $clause ) ) {
                        $clause_sql = $this->get_sql_for_clause( $clause, $query );
                        $where_count = count( $clause_sql['where'] );
                        if ( ! $where_count ) $sql_chunks['where'][] = '';
                        elseif ( 1 === $where_count ) $sql_chunks['where'][] = $clause_sql['where'][0];
                        else $sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
                        $sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );
                    }else{
                        $clause_sql = $this->_get_sql_for_query( $clause, $depth + 1 );
                        $sql_chunks['where'][] = $clause_sql['where'];
                        $sql_chunks['join'][]  = $clause_sql['join'];
                    }
                }
            }
            unset($clause);
            $sql_chunks['join']  = array_filter( $sql_chunks['join'] );
            $sql_chunks['where'] = array_filter( $sql_chunks['where'] );
            if ( empty( $relation ) ) $relation = 'AND';
            if ( ! empty( $sql_chunks['join'] ) ) $sql['join'] = implode( ' ', array_unique( $sql_chunks['join'] ) );
            if ( ! empty( $sql_chunks['where'] ) )
                $sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
            return $sql;
        }//301
        /**
         * @description Identify an existing table alias that is compatible with the current query clause.
         * @param $clause
         * @param $parent_query
         * @return bool|mixed
         */
        protected function _find_compatible_table_alias( $clause, $parent_query ){
            $alias = false;
            if ( ! isset( $clause['operator'] ) || 'IN' !== $clause['operator'] ) return $alias;
            if ( ! isset( $parent_query['relation'] ) || 'OR' !== $parent_query['relation'] ) return $alias;
            $compatible_operators = array( 'IN' );
            foreach ( $parent_query as $sibling ) {
                if ( ! is_array( $sibling ) || ! self::_is_first_order_clause( $sibling)) continue;
                if ( empty( $sibling['alias'] ) || empty( $sibling['operator'] ) ) continue;
                if ( in_array( strtoupper( $sibling['operator'] ), $compatible_operators, true ) ) {
                    $alias = preg_replace( '/\W/', '_', $sibling['alias'] );
                    break;
                }
            }
            return $alias;
        }//504
        /**
         * @description Validates a single query.
         * @param $query
         */
        private function __clean_query( &$query ): void{
            if ( empty( $query['taxonomy'] ) ) {
                if ( 'term_taxonomy_id' !== $query['field'] ) {
                    $query = new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
                    return;
                }
                $query['include_children'] = false;
            } elseif ( ! $this->_taxonomy_exists( $query['taxonomy'] ) ) {
                $query = new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
                return;
            }
            if ( 'slug' === $query['field'] || 'name' === $query['field'] )
                $query['terms'] = array_unique( (array) $query['terms'] );
            else $query['terms'] = $this->_tp_parse_id_list( $query['terms'] );
            if ($query['include_children'] ?? $this->_is_taxonomy_hierarchical( $query['taxonomy'] )) {
                $this->transform_query( $query, 'term_id' );
                if ( $this->_init_error( $query ) ) return;
                $children = [];
                foreach ( $query['terms'] as $term ) {
                    $children   = $this->_tp_array_merge($children, $this->_get_term_tax_children( $term, $query['taxonomy']) );
                    $children[] = $term;
                }
                $query['terms'] = $children;
            }
            $this->transform_query( $query, 'term_taxonomy_id' );
        }//545
    }
}else die;