<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-5-2022
 * Time: 16:03
 */
namespace TP_Core\Libs\Queries;
if(ABSPATH){
    class TP_Network_Query extends Query_Base{
        protected $_sql_clauses = ['select' => '','from' => '','where' => [],'groupby' => '','orderby' => '','limits' => '',];
        public function __construct( $query = '' ) {
            $this->query_var_defaults = ['network__in' => '','network__not_in' => '','count' => false,
                'fields' => '','number' => '','offset' => '','no_found_rows' => true,'orderby' => 'id',
                'order' => 'ASC','domain' => '','domain__in' => '','domain__not_in' => '','path' => '',
                'path__in' => '','path__not_in' => '','search' => '','update_network_cache' => true,];
            if ( ! empty( $query ) ) $this->query_network( $query );
        }//116
        public function parse_query( $query = '' ): void{
            if ( empty( $query ) ) $query = $this->query_vars;
            $this->query_vars = $this->_tp_parse_args( $query, $this->query_var_defaults );
            $this->_do_action_ref_array( 'parse_network_query', array( &$this ) );
        }//149
        public function query_network( $query ) {
            $this->query_vars = $this->_tp_parse_args( $query );
            return $this->get_networks();
        }//175
        public function get_networks() {
            $this->parse_query();
            $this->_do_action_ref_array( 'pre_get_networks', [&$this] );
            $network_data = null;
            $network_data = $this->_apply_filters_ref_array( 'networks_pre_query', [$network_data, &$this] );
            if ( null !== $network_data ) {
                if ( is_array( $network_data ) && ! $this->query_vars['count'] )
                    $this->networks = $network_data;
                return $network_data;
            }
            $_args = $this->_tp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) );
            unset( $_args['fields'], $_args['update_network_cache'] );
            $key          = md5( serialize( $_args ) );
            $last_changed = $this->_tp_cache_get_last_changed( 'networks' );
            $cache_key   = "get_network_ids:$key:$last_changed";
            $cache_value = $this->_tp_cache_get( $cache_key, 'networks' );
            if ( false === $cache_value ) {
                $network_ids = $this->_get_network_ids();
                if ( $network_ids ) $this->__set_found_networks();
                $cache_value = ['network_ids' => $network_ids,'found_networks' => $this->found_networks,];
                $this->_tp_cache_add( $cache_key, $cache_value, 'networks' );
            } else {
                $network_ids          = $cache_value['network_ids'];
                $this->found_networks = $cache_value['found_networks'];
            }
            if ( $this->found_networks && $this->query_vars['number'] )
                $this->max_num_pages = ceil( $this->found_networks / $this->query_vars['number'] );
            if ( $this->query_vars['count'] ) return (int) $network_ids;
            $network_ids = array_map( 'intval', $network_ids );
            if ( 'ids' === $this->query_vars['fields'] ) {
                $this->networks = $network_ids;
                return $this->networks;
            }
            if ( $this->query_vars['update_network_cache'] )
                $this->_prime_network_caches( $network_ids );
            $_networks = [];
            foreach ( $network_ids as $network_id ) {
                $_network = $this->_get_network( $network_id );
                if ( $_network )  $_networks[] = $_network;
            }
            $_networks = $this->_apply_filters_ref_array( 'the_networks', [$_networks, &$this] );
            $this->networks = array_map( 'get_network', $_networks );
            return $this->networks;
        }//188
        protected function _get_network_ids(){
            $tpdb = $this->_init_db();
            $order = $this->_parse_order( $this->query_vars['order'] );
            if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) )
                $this->_orderby = null;
            elseif ( ! empty( $this->query_vars['orderby'] ) ) {
                $ordersby = is_array( $this->query_vars['orderby'] ) ? $this->query_vars['orderby'] : preg_split( '/[,\s]/', $this->query_vars['orderby'] );
                $orderby_array = [];
                foreach ( $ordersby as $_key => $_value ) {
                    if ( ! $_value ) continue;
                    if ( is_int( $_key ) ) {
                        $_orderby = $_value;
                        $_order   = $order;
                    } else {
                        $_orderby = $_key;
                        $_order   = $_value;
                    }
                    $parsed = $this->_parse_orderby( $_orderby );
                    if ( ! $parsed ) continue;
                    if ( 'network__in' === $_orderby ) {
                        $orderby_array[] = $parsed;
                        continue;
                    }
                    $orderby_array[] = $parsed . ' ' . $this->_parse_order( $_order );
                }
                $this->_orderby = implode( ', ', $orderby_array );
            }else $this->_orderby = "$tpdb->site.id $order";
            $number = $this->_abs_int( $this->query_vars['number'] );
            $offset = $this->_abs_int( $this->query_vars['offset'] );
            $this->_limits = null;
            if ( ! empty( $number ) ) {
                if ( $offset ) $this->_limits = 'LIMIT ' . $offset . ',' . $number;
                else $this->_limits = 'LIMIT ' . $number;
            }
            if ( $this->query_vars['count'] ) $this->_fields = 'COUNT(*)';
            else $this->_fields = "$tpdb->site.id";
            if ( ! empty( $this->query_vars['network__in'] ) )
                $this->_sql_clauses['where']['network__in'] = "$tpdb->site.id IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['network__in'] ) ) . ' )';
            if ( ! empty( $this->query_vars['network__not_in'] ) )
                $this->_sql_clauses['where']['network__not_in'] = "$tpdb->site.id NOT IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['network__not_in'] ) ) . ' )';
            if ( ! empty( $this->query_vars['domain'] ) ) {
                $this->_sql_clauses['where']['domain'] = $tpdb->prepare( "$tpdb->site.domain = %s", $this->query_vars['domain'] );
            }
            if ( is_array( $this->query_vars['domain__in'] ) )
                $this->_sql_clauses['where']['domain__in'] = "$tpdb->site.domain IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['domain__in'] ) ) . "' )";
            if ( is_array( $this->query_vars['domain__not_in'] ) )
                $this->_sql_clauses['where']['domain__not_in'] = "$tpdb->site.domain NOT IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['domain__not_in'] ) ) . "' )";
            if ( ! empty( $this->query_vars['path'] ) ) {
                $this->_sql_clauses['where']['path'] = $tpdb->prepare( "$tpdb->site.path = %s", $this->query_vars['path'] );
            }
            if ( is_array( $this->query_vars['path__in'] ) )
                $this->_sql_clauses['where']['path__in'] = "$tpdb->site.path IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['path__in'] ) ) . "' )";
            if ( is_array( $this->query_vars['path__not_in'] ) )
                $this->_sql_clauses['where']['path__not_in'] = "$tpdb->site.path NOT IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['path__not_in'] ) ) . "' )";
            if ($this->query_vars['search'] !== '') {
                $this->_sql_clauses['where']['search'] = $this->_get_search_sql(
                    $this->query_vars['search'],
                    array( "$tpdb->site.domain", "$tpdb->site.path" )
                );
            }
            $join = null;
            $this->_where = implode( ' AND ', $this->_sql_clauses['where'] );
            $groupby = null;
            $clauses = array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' );
            $clauses = $this->_apply_filters_ref_array( 'networks_clauses', [compact( $clauses ), &$this ] );
            $fields  = $clauses['fields'] ?? '';
            $join    = $clauses['join'] ?? '';
            $this->_where   = $clauses['where'] ?? '';
            $orderby = $clauses['orderby'] ?? '';
            $limits  = $clauses['limits'] ?? '';
            $groupby = $clauses['groupby'] ?? '';
            if ( $this->_where ) $this->_where = 'WHERE ' . $this->_where;
            if ( $groupby ) $groupby = 'GROUP BY ' . $groupby;
            if ( $orderby ) $orderby = "ORDER BY $orderby";
            $found_rows = '';
            if ( ! $this->query_vars['no_found_rows'] )
                $found_rows = 'SQL_CALC_FOUND_ROWS';
            $this->_sql_clauses['select']  = "SELECT $found_rows $fields";
            $this->_sql_clauses['from']    = "FROM $tpdb->site $join";
            $this->_sql_clauses['groupby'] = $groupby;
            $this->_sql_clauses['orderby'] = $orderby;
            $this->_sql_clauses['limits']  = $limits;
            $this->request = "
			{$this->_sql_clauses['select']}
			{$this->_sql_clauses['from']}
			{$this->_where}
			{$this->_sql_clauses['groupby']}
			{$this->_sql_clauses['orderby']}
			{$this->_sql_clauses['limits']}
		    ";
            if ( $this->query_vars['count'] )
                return (int) $tpdb->get_var( $this->request );
            $network_ids = $tpdb->get_col( $this->request );
            return array_map( 'intval', $network_ids );
        }//325
        private function __set_found_networks(): void{
            $tpdb = $this->_init_db();
            if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) {
                $found_networks_query = $this->_apply_filters( 'found_networks_query', 'SELECT FOUND_ROWS()', $this );
                $this->found_networks = (int) $tpdb->get_var( $found_networks_query );
            }
        }//509
        protected function _get_search_sql( $search, $columns ): string{
            $tpdb = $this->_init_db();
            $like = '%' . $tpdb->esc_like( $search ) . '%';
            $searches = [];
            foreach ( $columns as $column )
                $searches[] = $tpdb->prepare( "$column LIKE %s", $like );
            return '(' . implode( ' OR ', $searches ) . ')';
        }//538
        protected function _parse_orderby( $orderby ){
            $tpdb = $this->_init_db();
            $allowed_keys = ['id','domain','path',];
            $parsed = false;
            if ( 'network__in' === $orderby ) {
                $network__in = implode( ',', array_map( 'absint', $this->query_vars['network__in'] ) );
                $parsed      = "FIELD( {$tpdb->site}.id, $network__in )";
            } elseif ( 'domain_length' === $orderby || 'path_length' === $orderby ) {
                $field  = substr( $orderby, 0, -7 );
                $parsed = "CHAR_LENGTH($tpdb->site.$field)";
            } elseif ( in_array( $orderby, $allowed_keys, true ) ) $parsed = "$tpdb->site.$orderby";
            return $parsed;
        }//561
        protected function _parse_order( $order ): ?string{
            if ( ! is_string( $order ) || empty( $order ) ) return 'ASC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
            else return 'DESC';
        }
    }
}else die;