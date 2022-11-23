<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 21:17
 */
namespace TP_Core\Libs\Queries;
if(ABSPATH){
    class TP_Meta_Query extends Query_Base{
        protected $_clauses = [];
        protected $_has_or_relation = false;
        protected $_table_aliases = [];
        protected $_meta_compare;
        protected $_meta_value;
        protected $_non_numeric_operators = ['=','!=','LIKE','NOT LIKE','IN','NOT IN','EXISTS','NOT EXISTS','RLIKE','REGEXP','NOT REGEXP',];
        protected $_numeric_operators = ['>','>=','<','<=','BETWEEN','NOT BETWEEN',];
        protected $_string = [];
        protected $_sub_query_alias;
        protected $_where;
        public $meta_id_column;
        public $meta_table;
        public $primary_id_column;
        public $primary_table;
        public $queries = [];
        public $relation;
        public function __construct( $meta_query = false ) {
            if ( ! $meta_query ) return;
            if ( isset( $meta_query['relation'] ) && 'OR' === strtoupper( $meta_query['relation'] ) )
                $this->relation = 'OR';
            else $this->relation = 'AND';
            $this->queries = $this->sanitize_query( $meta_query );
        }//167
        public function sanitize_query( $queries ): array{
            $clean_queries = [];
            if ( ! is_array( $queries)) return $clean_queries;
            foreach ( $queries as $key => $query ) {
                if ( 'relation' === $key ) $relation = $query;
                elseif (!is_array($query)) continue;
                elseif ( $this->_is_first_order_clause( $query ) ) {
                    if ( isset( $query['value'] ) && array() === $query['value'] )
                        unset( $query['value'] );
                    $clean_queries[ $key ] = $query;
                }else {
                    $cleaned_query = $this->sanitize_query( $query );
                    if (!empty( $cleaned_query )) $clean_queries[ $key ] = $cleaned_query;
                }
            }
            if ( empty( $clean_queries ) ) return $clean_queries;
            if ( isset( $relation ) && 'OR' === strtoupper( $relation ) ) {
                $clean_queries['relation'] = 'OR';
                $this->_has_or_relation     = true;
            }elseif ( 1 === count( $clean_queries ) ) $clean_queries['relation'] = 'OR';
            else $clean_queries['relation'] = 'AND';
            return $clean_queries;
        }//191
        protected function _is_first_order_clause( $query ): bool{
            return isset( $query['key'] ) || isset( $query['value'] );
        }//259
        public function parse_query_vars( $qv ): void{
            $meta_query = [];
            $primary_meta_query = array();
            foreach ( array( 'key', 'compare', 'type', 'compare_key', 'type_key' ) as $key ) {
                if (!empty($qv[ "meta_$key"])) $primary_meta_query[ $key ] = $qv[ "meta_$key" ];
            }
            if ( isset( $qv['meta_value'] ) && '' !== $qv['meta_value'] && ( ! is_array( $qv['meta_value'] ) || $qv['meta_value'] ) )
                $primary_meta_query['value'] = $qv['meta_value'];
            $existing_meta_query = isset( $qv['meta_query'] ) && is_array( $qv['meta_query'] ) ? $qv['meta_query'] : [];
            if ( ! empty( $primary_meta_query ) && ! empty( $existing_meta_query ) )
                $meta_query = ['relation' => 'AND',$primary_meta_query, $existing_meta_query,];
            elseif ( ! empty( $primary_meta_query ) ) $meta_query = [$primary_meta_query,];
            elseif ( ! empty( $existing_meta_query ) ) $meta_query = $existing_meta_query;
            $this->__construct( $meta_query );
        }//270
        public function get_cast_for_type( $type = '' ){
            if ( empty( $type ) ) return 'CHAR';
            $meta_type = strtoupper( $type );
            if ( ! preg_match( '/^(?:BINARY|CHAR|DATE|DATETIME|SIGNED|UNSIGNED|TIME|NUMERIC(?:\(\d+(?:,\s?\d+)?\))?|DECIMAL(?:\(\d+(?:,\s?\d+)?\))?)$/', $meta_type ) )
                return 'CHAR';
            if ( 'NUMERIC' === $meta_type ) $meta_type = 'SIGNED';
            return $meta_type;
        }
        public function get_sql( $type, $primary_table, $primary_id_column, $context = null ) {
            $meta_table = $this->_get_meta_table( $type );
            if ( ! $meta_table ) return false;
            $this->_table_aliases = array();
            $this->meta_table     = $meta_table;
            $this->meta_id_column = $this->_sanitize_key( $type . '_id' );
            $this->primary_table     = $primary_table;
            $this->primary_id_column = $primary_id_column;
            $sql = $this->_get_sql_clauses();
            if ( false !== strpos( $sql['join'], 'LEFT JOIN' ) )
                $sql['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $sql['join'] );
            return $this->_apply_filters_ref_array( 'get_meta_sql', array( $sql, $this->queries, $type, $primary_table, $primary_id_column, $context ) );
        }//355
        protected function _get_sql_clauses() {
            $queries = $this->queries;
            $sql     = $this->_get_sql_for_query( $queries );
            if ( ! empty( $sql['where'] ) ) $sql['where'] = ' AND ' . $sql['where'];
            return $sql;
        }//411
        protected function _get_sql_for_query( &$query, $depth = 0 ): array{
            $sql_chunks = ['join'  => [],'where' => [],];
            $sql = ['join'  => '','where' => '',];
            $indent = '';
            for ( $i = 0; $i < $depth; $i++ ) $indent .= '  ';
            foreach ( $query as $key => &$clause ) {
                if ( 'relation' === $key ) {
                    $relation = $query['relation'];
                } elseif ( is_array( $clause ) ) {
                    if ( $this->_is_first_order_clause( $clause ) ) {
                        $clause_sql = $this->get_sql_for_clause( $clause, $query, $key );
                        $where_count = count( $clause_sql['where'] );
                        if ( ! $where_count ) $sql_chunks['where'][] = '';
                        elseif ( 1 === $where_count ) $sql_chunks['where'][] = $clause_sql['where'][0];
                        else $sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
                        $sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );
                    } else {
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
            if ( ! empty( $sql_chunks['where'] ) ) $sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
            return $sql;
        }//444
        public function get_sql_for_clause( &$clause, $parent_query, $clause_key = '' ): array{
            $tpdb = $this->_init_db();
            $sql_chunks = ['join'  => [],'where' => [],];
            if ( isset( $clause['compare'] ) ) $clause['compare'] = strtoupper( $clause['compare'] );
            else $clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] ) ? 'IN' : '=';
            if ( ! in_array( $clause['compare'], $this->_non_numeric_operators, true ) && ! in_array( $clause['compare'], $this->_numeric_operators, true ) )
                $clause['compare'] = '=';
            if ( isset( $clause['compare_key'] ) ) $clause['compare_key'] = strtoupper( $clause['compare_key'] );
            else $clause['compare_key'] = isset( $clause['key'] ) && is_array( $clause['key'] ) ? 'IN' : '=';
            if ( ! in_array( $clause['compare_key'], $this->_non_numeric_operators, true ) )
                $clause['compare_key'] = '=';
            $meta_compare     = $clause['compare']; //todo $this->_meta_compare
            $meta_compare_key = $clause['compare_key'];
            $join = '';
            $alias = $this->_find_compatible_table_alias( $clause, $parent_query );
            if ( false === $alias ) {
                $i     = count( $this->_table_aliases );
                $alias = $i ? 'mt' . $i : $this->meta_table;
                if ( 'NOT EXISTS' === $meta_compare ) {
                    $join .= " LEFT JOIN $this->meta_table";
                    $join .= $i ? " AS $alias" : '';
                    if ( 'LIKE' === $meta_compare_key ) $join .= $tpdb->prepare( " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.meta_key LIKE %s )", '%' . $tpdb->esc_like( $clause['key'] ) . '%' );
                    else  $join .= $tpdb->prepare( " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.meta_key = %s )", $clause['key'] );
                } else {
                    $join .= " INNER JOIN $this->meta_table";
                    $join .= $i ? " AS $alias" : '';
                    $join .= " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column )";
                }
                $this->_table_aliases[] = $alias;
                $sql_chunks['join'][]  = $join;
            }
            $clause['alias'] = $alias;
            $_meta_type     = isset( $clause['type'] ) ?: '';
            $meta_type      = $this->get_cast_for_type( $_meta_type );
            $clause['cast'] = $meta_type;
            if (is_int( (int) $clause_key ) || ! $clause_key ) $clause_key = $clause['alias'];
            $iterator        = 1;
            $clause_key_base = $clause_key;
            while ( isset( $this->_clauses[ $clause_key ] ) ) {
                $clause_key = $clause_key_base . '-' . $iterator;
                $iterator++;
            }
            $this->_clauses[ $clause_key ] =& $clause;
            if ( array_key_exists( 'key', $clause ) ) {
                if ( 'NOT EXISTS' === $meta_compare )
                    $sql_chunks['where'][] = $alias . '.' . $this->meta_id_column . ' IS NULL';
                else {
                    if ( in_array( $meta_compare_key, array( '!=', 'NOT IN', 'NOT LIKE', 'NOT EXISTS', 'NOT REGEXP' ), true ) ) {
                        // Negative clauses may be reused.
                        $i                     = count( $this->_table_aliases );
                        $this->_sub_query_alias        = $i ? 'mt' . $i : $this->meta_table;
                        $this->_table_aliases[] = $this->_sub_query_alias;
                        //$this->_string['meta_compare_string_start']
                        $this->_string['meta_compare_start']  = 'NOT EXISTS (';
                        $this->_string['meta_compare_start'] .= TP_SELECT ;
                        $this->_string['meta_compare_start'] .= " 1 FROM ". $tpdb->post_meta . " $this->_sub_query_alias ";
                        $this->_string['meta_compare_start'] .= "WHERE $this->_sub_query_alias.post_ID = $alias.post_ID ";
                        $this->_string['meta_compare_end'] = 'LIMIT 1';
                        $this->_string['meta_compare_end'].= ')';
                    }
                    switch ( $meta_compare_key ) {
                        case '=':
                        case 'EXISTS':
                            $this->_where = $tpdb->prepare( "$alias.meta_key = %s", trim( $clause['key'] ) );
                            break;
                        case 'LIKE':
                            $meta_compare_value = '%' . $this->_init_db()->esc_like( trim( $clause['key'] ) ) . '%';
                            $this->_where = $tpdb->prepare( "$alias.meta_key LIKE %s", $meta_compare_value );
                            break;
                        case 'IN':
                            $meta_compare_string = "$alias.meta_key IN (" . substr( str_repeat( ',%s', count( $clause['key'] ) ), 1 ) . ')';
                            $this->_where = $tpdb->prepare( $meta_compare_string, $clause['key'] );
                            break;
                        case 'RLIKE':
                        case 'REGEXP':
                            $operator = $meta_compare_key;
                            if ( isset( $clause['type_key'] ) && 'BINARY' === strtoupper( $clause['type_key'] ) )
                                $cast = 'BINARY';
                            else $cast = '';
                            $this->_where = $tpdb->prepare( "$alias.meta_key $operator $cast %s", trim( $clause['key'] ) );
                            break;
                        case '!=':
                        case 'NOT EXISTS':
                            $meta_compare_string = $this->_string['meta_compare_start'] . "AND $this->_sub_query_alias.meta_key = %s " . $this->_string['meta_compare_end'];
                            $this->_where = $tpdb->prepare( $meta_compare_string, $clause['key'] );
                            break;
                        case 'NOT LIKE':
                            $meta_compare_string = $this->_string['meta_compare_start'] . "AND $this->_sub_query_alias.meta_key LIKE %s " . $this->_string['meta_compare_end'];
                            $meta_compare_value = '%' . $tpdb->esc_like( trim( $clause['key'] ) ) . '%';
                            $this->_where = $tpdb->prepare( $meta_compare_string, $meta_compare_value );
                            break;
                        case 'NOT IN':
                            $array_sub_clause     = '(' . substr( str_repeat( ',%s', count( $clause['key'] ) ), 1 ) . ') ';
                            $meta_compare_string = $this->_string['meta_compare_start'] . "AND $this->_sub_query_alias.meta_key IN " . $array_sub_clause . $this->_string['meta_compare_end'];
                            $this->_where = $tpdb->prepare( $meta_compare_string, $clause['key'] );
                            break;
                        case 'NOT REGEXP':
                            //might not be needed here $operator = $meta_compare_key;
                            if ( isset( $clause['type_key'] ) && 'BINARY' === strtoupper( $clause['type_key'] ) ) $cast = 'BINARY';
                            else $cast = '';
                            $meta_compare_string = $this->_string['meta_compare_start'] . "AND $this->_sub_query_alias.meta_key REGEXP $cast %s " . $this->_string['meta_compare_end'];
                            $this->_where = $tpdb->prepare( $meta_compare_string, $clause['key'] );
                            break;
                    }
                    $sql_chunks['where'][] = $this->_where;
                }
            }
            if ( array_key_exists( 'value', $clause ) ) {
                $this->_meta_value = $clause['value'];
                if ( in_array( $meta_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
                    if ( ! is_array( $this->_meta_value ) ) $this->_meta_value = preg_split( '/[,\s]+/', $this->_meta_value );
                } elseif ( is_string( $this->_meta_value ) ) $this->_meta_value = trim( $this->_meta_value );
                switch ( $meta_compare ) {
                    case 'IN':
                    case 'NOT IN':
                        $meta_compare_string = '(' . substr( str_repeat( ',%s', count( $this->_meta_value ) ), 1 ) . ')';
                        $this->_where = $tpdb->prepare( $meta_compare_string, $this->_meta_value );
                        break;
                    case 'BETWEEN':
                    case 'NOT BETWEEN':
                        $this->_where = $tpdb->prepare( '%s AND %s', $this->_meta_value[0], $this->_meta_value[1] );
                        break;
                    case 'LIKE':
                    case 'NOT LIKE':
                        $this->_meta_value = '%' . $tpdb->esc_like( $this->_meta_value ) . '%';
                        $this->_where  = $tpdb->prepare( '%s', $this->_meta_value );
                        break;
                    case 'EXISTS':
                        $meta_compare = '=';
                        $this->_where = $tpdb->prepare( '%s', $this->_meta_value );
                        break;
                    case 'NOT EXISTS':
                        $this->_where = '';
                        break;
                    default:
                        $this->_where = $tpdb->prepare( '%s', $this->_meta_value );
                        break;
                }
                if ( $this->_where ) {
                    if ( 'CHAR' === $meta_type )
                        $sql_chunks['where'][] = "$alias.meta_value {$meta_compare} {$this->_where}";
                    else $sql_chunks['where'][] = "CAST($alias.meta_value AS {$meta_type}) {$meta_compare} {$this->_where}";
                }
            }
            if ( 1 < count( $sql_chunks['where'] ) )
                $sql_chunks['where'] = array( '( ' . implode( ' AND ', $sql_chunks['where'] ) . ' )' );
            return $sql_chunks;
        } //690
        public function get_clauses(): array{
            return $this->_clauses;
        }//799
        protected function _find_compatible_table_alias( $clause, $parent_query ) {
            $alias = false;
            foreach ( $parent_query as $sibling ) {
                if ( empty( $sibling['alias'] ) ) continue;
                if ( ! is_array( $sibling ) || ! $this->_is_first_order_clause( $sibling ) ) continue;
                $compatible_compares = array();
                if ( 'OR' === $parent_query['relation'] )
                    $compatible_compares = array( '=', 'IN', 'BETWEEN', 'LIKE', 'REGEXP', 'RLIKE', '>', '>=', '<', '<=' );
                elseif ( isset( $sibling['key'] ,$clause['key']) && $sibling['key'] === $clause['key'] )
                    $compatible_compares = array( '!=', 'NOT IN', 'NOT LIKE' );
                $clause_compare  = strtoupper( $clause['compare'] );
                $sibling_compare = strtoupper( $sibling['compare'] );
                if ( in_array( $clause_compare, $compatible_compares, true ) && in_array( $sibling_compare, $compatible_compares, true ) ) {
                    $alias = preg_replace( '/\W/', '_', $sibling['alias'] );
                    break;
                }
            }
            return $this->_apply_filters( 'meta_query_find_compatible_table_alias', $alias, $clause, $parent_query, $this );
        }//823
        public function has_or_relation(): bool{
            return $this->_has_or_relation;
        }//880
    }
}else die;