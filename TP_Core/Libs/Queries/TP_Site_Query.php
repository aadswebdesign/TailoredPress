<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 04:27
 */
namespace TP_Core\Libs\Queries;
if(ABSPATH){
    class TP_Site_Query extends Query_Base{
        protected $_sql_clauses = ['select' => '','from' => '','where' => [],'groupby' => '','orderby' => '','limits' => '',];
        public function __construct( $query = '' ) {
            $this->query_var_defaults = ['fields' => '','ID' => '','site__in' => '','site__not_in' => '','number' => 100,
                'offset' => '','no_found_rows' => true,'orderby' => 'id','order' => 'ASC','network_id' => 0,
                'network__in' => '','network__not_in' => '','domain' => '','domain__in' => '','domain__not_in' => '',
                'path' => '','path__in' => '','path__not_in' => '','public' => null,'archived' => null,'mature' => null,
                'spam' => null,'deleted' => null,'lang_id' => null,'lang__in' => '','lang__not_in' => '','search' => '',
                'search_columns' => [],'count' => false,'date_query' => null,'update_site_cache' => true,'update_site_meta_cache' => true,
                'meta_query' => '','meta_key' => '','meta_value' => '','meta_type' => '','meta_compare' => '',];
            if ( ! empty( $query ) ) $this->query_site( $query );
        }//187
        public function parse_query( $query = '' ): void{
            if ( empty( $query ) ) $query = $this->query_vars;
            $this->query_vars = $this->_tp_parse_args( $query, $this->query_var_defaults );
            $this->_do_action_ref_array( 'parse_site_query', array( &$this ) );
        }//242
        public function query_site( $query ){
            $this->query_vars = $this->_tp_parse_args( $query );
            return $this->get_sites();
        }//268
        public function get_sites(){
            $tpdb = $this->_init_db();
            $this->parse_query();
            $this->meta_query = new TP_Meta_Query();
            $this->meta_query->parse_query_vars( $this->query_vars );
            $this->_do_action_ref_array( 'pre_get_sites', array( &$this ) );
            $this->meta_query->parse_query_vars( $this->query_vars );
            if ( ! empty( $this->meta_query->queries ) ) {
                $this->_meta_query_clauses = $this->meta_query->get_sql( 'blog', $tpdb->blogs, 'blog_id', $this );
            }
            $site_data = null;
            $site_data = $this->_apply_filters_ref_array( 'sites_pre_query', array( $site_data, &$this ) );
            if ( null !== $site_data ) {
                if ( is_array( $site_data ) && ! $this->query_vars['count'] ) $this->sites = $site_data;
                return $site_data;
            }
            $_args = $this->_tp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) );
            unset( $_args['fields'], $_args['update_site_cache'], $_args['update_site_meta_cache'] );
            $key          = md5( serialize( $_args ) );
            $last_changed = $this->_tp_cache_get_last_changed( 'sites' );
            $cache_key   = "get_sites:$key:$last_changed";
            $cache_value = $this->_tp_cache_get( $cache_key, 'sites' );
            if ( false === $cache_value ) {
                $site_ids = $this->_get_site_ids();
                if ( $site_ids ) $this->__set_found_sites();
                $cache_value = array(
                    'site_ids'    => $site_ids,
                    'found_sites' => $this->found_sites,
                );
                $this->_tp_cache_add( $cache_key, $cache_value, 'sites' );
            } else {
                $site_ids          = $cache_value['site_ids'];
                $this->found_sites = $cache_value['found_sites'];
            }
            if ( $this->found_sites && $this->query_vars['number'] )
                $this->max_num_pages = ceil( $this->found_sites / $this->query_vars['number'] );
            if ( $this->query_vars['count'] ) return (int) $site_ids;
            $site_ids = array_map( 'intval', $site_ids );
            if ( 'ids' === $this->query_vars['fields'] ) {
                $this->sites = $site_ids;
                return $this->sites;
            }
            if ( $this->query_vars['update_site_cache'] )
                $this->_prime_site_caches( $site_ids, $this->query_vars['update_site_meta_cache'] );
            $_sites = [];
            foreach ( $site_ids as $site_id ) {
                $_site = $this->_get_site( $site_id );
                if ( $_site ) $_sites[] = $_site;
            }
            $_sites = $this->_apply_filters_ref_array( 'the_sites', array( $_sites, &$this ) );
            $this->sites = array_map( 'get_site', $_sites );
            return $this->sites;
        }//284
        protected function _get_site_ids(){
            $tpdb = $this->_init_db();
            $order = $this->_parse_order( $this->query_vars['order'] );
            if ( in_array( $this->query_vars['orderby'], array( 'none', [], false ), true ) ) {
                $this->_orderby = null;
            } elseif ( ! empty( $this->query_vars['orderby'] ) ) {
                $ordersby = is_array( $this->query_vars['orderby'] ) ?
                    $this->query_vars['orderby'] :
                    preg_split( '/[,\s]/', $this->query_vars['orderby'] );
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
                    if ( 'site__in' === $_orderby || 'network__in' === $_orderby ) {
                        $orderby_array[] = $parsed;
                        continue;
                    }
                    $orderby_array[] = $parsed . ' ' . $this->_parse_order( $_order );
                }
                $this->_orderby = implode( ', ', $orderby_array );
            } else $this->_orderby = "{$tpdb->blogs}.blog_id $order";
            $number = $this->_abs_int( $this->query_vars['number'] );
            $offset = $this->_abs_int( $this->query_vars['offset'] );
            $this->_limits = null;
            if ( ! empty( $number ) ) {
                if ( $offset ) $this->_limits = 'LIMIT ' . $offset . ',' . $number;
                else $this->_limits = 'LIMIT ' . $number;
            }
            if ( $this->query_vars['count'] ) $this->_fields = 'COUNT(*)';
            else $this->_fields = "{$tpdb->blogs}.blog_id";
            // Parse site IDs for an IN clause.
            $site_id = $this->_abs_int( $this->query_vars['ID'] );
            if ( ! empty( $site_id ) )
                $this->_sql_clauses['where']['ID'] = $tpdb->prepare( "{$tpdb->blogs}.blog_id = %d", $site_id );
            // Parse site IDs for an IN clause.
            if ( ! empty( $this->query_vars['site__in'] ) )
                $this->_sql_clauses['where']['site__in'] = "{$tpdb->blogs}.blog_id IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['site__in'] ) ) . ' )';
            // Parse site IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['site__not_in'] ) )
            $this->_sql_clauses['where']['site__not_in'] = "{$tpdb->blogs}.blog_id NOT IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['site__not_in'] ) ) . ' )';
            $network_id = $this->_abs_int( $this->query_vars['network_id'] );
            if ( ! empty( $network_id ) )
                $this->_sql_clauses['where']['network_id'] = $tpdb->prepare( 'site_id = %d', $network_id );
            // Parse site network IDs for an IN clause.
            if ( ! empty( $this->query_vars['network__in'] ) )
                $this->_sql_clauses['where']['network__in'] = 'site_id IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['network__in'] ) ) . ' )';
            // Parse site network IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['network__not_in'] ) )
                $this->_sql_clauses['where']['network__not_in'] = 'site_id NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['network__not_in'] ) ) . ' )';
            if ( ! empty( $this->query_vars['domain'] ) )
                $this->_sql_clauses['where']['domain'] = $tpdb->prepare( 'domain = %s', $this->query_vars['domain'] );
            // Parse site domain for an IN clause.
            if ( is_array( $this->query_vars['domain__in'] ) )
                $this->_sql_clauses['where']['domain__in'] = "domain IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['domain__in'] ) ) . "' )";
            // Parse site domain for a NOT IN clause.
            if ( is_array( $this->query_vars['domain__not_in'] ) )
                $this->_sql_clauses['where']['domain__not_in'] = "domain NOT IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['domain__not_in'] ) ) . "' )";
            if ( ! empty( $this->query_vars['path'] ) )
                $this->_sql_clauses['where']['path'] = $tpdb->prepare( 'path = %s', $this->query_vars['path'] );
            // Parse site path for an IN clause.
            if ( is_array( $this->query_vars['path__in'] ) )
                $this->_sql_clauses['where']['path__in'] = "path IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['path__in'] ) ) . "' )";
            // Parse site path for a NOT IN clause.
            if ( is_array( $this->query_vars['path__not_in'] ) )
                $this->_sql_clauses['where']['path__not_in'] = "path NOT IN ( '" . implode( "', '", $tpdb->escape( $this->query_vars['path__not_in'] ) ) . "' )";
            if ( is_numeric( $this->query_vars['archived'] ) ) {
                $archived                               = $this->_abs_int( $this->query_vars['archived'] );
                $this->_sql_clauses['where']['archived'] = $tpdb->prepare( 'archived = %s ', $this->_abs_int( $archived ) );
            }
            if ( is_numeric( $this->query_vars['mature'] ) ) {
                $mature                               = $this->_abs_int( $this->query_vars['mature'] );
                $this->_sql_clauses['where']['mature'] = $tpdb->prepare( 'mature = %d ', $mature );
            }
            if ( is_numeric( $this->query_vars['spam'] ) ) {
                $spam                               = $this->_abs_int( $this->query_vars['spam'] );
                $this->_sql_clauses['where']['spam'] = $tpdb->prepare( 'spam = %d ', $spam );
            }
            if ( is_numeric( $this->query_vars['deleted'] ) ) {
                $deleted                               = $this->_abs_int( $this->query_vars['deleted'] );
                $this->_sql_clauses['where']['deleted'] = $tpdb->prepare( 'deleted = %d ', $deleted );
            }
            if ( is_numeric( $this->query_vars['public'] ) ) {
                $public                               = $this->_abs_int( $this->query_vars['public'] );
                $this->_sql_clauses['where']['public'] = $tpdb->prepare( 'public = %d ', $public );
            }
            if ( is_numeric( $this->query_vars['lang_id'] ) ) {
                $lang_id                               = $this->_abs_int( $this->query_vars['lang_id'] );
                $this->_sql_clauses['where']['lang_id'] = $tpdb->prepare( 'lang_id = %d ', $lang_id );
            }
            // Parse site language IDs for an IN clause.
            if ( ! empty( $this->query_vars['lang__in'] ) )
                $this->_sql_clauses['where']['lang__in'] = 'lang_id IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['lang__in'] ) ) . ' )';
            // Parse site language IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['lang__not_in'] ) )
                $this->_sql_clauses['where']['lang__not_in'] = 'lang_id NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['lang__not_in'] ) ) . ' )';
            if ($this->query_vars['search'] !== '') {
                $search_columns = [];
                if ( $this->query_vars['search_columns'] )
                    $search_columns = array_intersect( $this->query_vars['search_columns'], array( 'domain', 'path' ) );
                if ( ! $search_columns ) $search_columns = array( 'domain', 'path' );
                $search_columns = $this->_apply_filters( 'site_search_columns', $search_columns, $this->query_vars['search'], $this );
                $this->_sql_clauses['where']['search'] = $this->_get_search_sql( $this->query_vars['search'], $search_columns );
            }
            $date_query = $this->query_vars['date_query'];
            if ( ! empty( $date_query ) && is_array( $date_query ) ) {
                $this->date_query                         = new TP_Date_Query( $date_query, 'registered' );
                $this->_sql_clauses['where']['date_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_query->get_sql() );
            }
            if ( ! empty( $this->_meta_query_clauses ) ) {
                $this->_join = $this->_meta_query_clauses['join'];
                $this->_sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->_meta_query_clauses['where'] );
                if ( ! $this->query_vars['count'] ) $this->_groupby = "{$tpdb->blogs}.blog_id";
            }
            $this->_where = implode( ' AND ', $this->_sql_clauses['where'] );
            $clauses = ['fields', 'join', 'where', 'orderby', 'limits', 'groupby'];
            $clauses = $this->_apply_filters_ref_array( 'sites_clauses', array( compact( $clauses ), &$this ) );
            $this->_fields  = $clauses['fields'] ?? '';
            $this->_join    = $clauses['join'] ?? '';
            $this->_where   = $clauses['where'] ?? '';
            $this->_orderby = $clauses['orderby'] ?? '';
            $this->_limits  = $clauses['limits'] ?? '';
            $this->_groupby = $clauses['groupby'] ?? '';
            if ( $this->_where ) $this->_where = 'WHERE ' . $this->_where;
            if ( $this->_groupby ) $this->_groupby = 'GROUP BY ' . $this->_groupby;
            if ( $this->_orderby ) $this->_orderby = "ORDER BY $this->_orderby";
            $found_rows = '';
            if ( ! $this->query_vars['no_found_rows'] ) $found_rows = 'SQL_CALC_FOUND_ROWS';
            $this->_sql_clauses['select']  = "SELECT $found_rows $this->_fields";
            $this->_sql_clauses['from']    = "FROM $tpdb->blogs $this->_join";
            $this->_sql_clauses['groupby'] = $this->_groupby;
            $this->_sql_clauses['orderby'] = $this->_orderby;
            $this->_sql_clauses['limits']  = $this->_limits;
            $this->request = "
			{$this->_sql_clauses['select']}
			{$this->_sql_clauses['from']}
			{$this->_where}
			{$this->_sql_clauses['groupby']}
			{$this->_sql_clauses['orderby']}
			{$this->_sql_clauses['limits']}
		";
            if ( $this->query_vars['count'] ) return (int) $tpdb->get_var( $this->request );
            $site_ids = $tpdb->get_col( $this->request );
            return array_map( 'intval', $site_ids );
        }//435
        private function __set_found_sites(): void{
            $tpdb = $this->_init_db();
            if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) {
                $found_sites_query = $this->_apply_filters( 'found_sites_query', 'SELECT FOUND_ROWS()', $this );
                $this->found_sites = (int) $tpdb->get_var( $found_sites_query );
            }
        }//717
        protected function _get_search_sql( $search, $columns ): string{
            $tpdb = $this->_init_db();
            if ( false !== strpos( $search, '*' ) )
                $like = '%' . implode( '%', array_map( array( $tpdb, 'esc_like' ), explode( '*', $search ) ) ) . '%';
            else $like = '%' . $tpdb->esc_like( $search ) . '%';
            $searches = [];
            foreach ( $columns as $column ) $searches[] = $tpdb->prepare( "$column LIKE %s", $like );
            return '(' . implode( ' OR ', $searches ) . ')';
        }//746
        protected function _parse_orderby( $orderby ){
            $tpdb = $this->_init_db();
            $parsed = false;
            switch ( $orderby ) {
                case 'site__in':
                    $site__in = implode( ',', array_map( 'absint', $this->query_vars['site__in'] ) );
                    $parsed   = "FIELD( {$tpdb->blogs}.blog_id, $site__in )";
                    break;
                case 'network__in':
                    $network__in = implode( ',', array_map( 'absint', $this->query_vars['network__in'] ) );
                    $parsed      = "FIELD( {$tpdb->blogs}.site_id, $network__in )";
                    break;
                case 'domain':
                case 'last_updated':
                case 'path':
                case 'registered':
                case 'deleted':
                case 'spam':
                case 'mature':
                case 'archived':
                case 'public':
                    $parsed = $orderby;
                    break;
                case 'network_id':
                    $parsed = 'site_id';
                    break;
                case 'domain_length':
                    $parsed = 'CHAR_LENGTH(domain)';
                    break;
                case 'path_length':
                    $parsed = 'CHAR_LENGTH(path)';
                    break;
                case 'id':
                    $parsed = "{$tpdb->blogs}.blog_id";
                    break;
            }
            if ( ! empty( $parsed ) || empty( $this->meta_query_clauses ) ) return $parsed;
            if( $this->meta_query instanceof TP_Meta_Query ){
                $meta_clauses = $this->meta_query->get_clauses();
            }
            if ( empty( $meta_clauses ) ) return $parsed;
            $primary_meta_query = reset( $meta_clauses );
            if ( ! empty( $primary_meta_query['key'] ) && $primary_meta_query['key'] === $orderby )
                $orderby = 'meta_value';
            switch ( $orderby ) {
                case 'meta_value':
                    if ( ! empty( $primary_meta_query['type'] ) )
                        $parsed = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
                    else $parsed = "{$primary_meta_query['alias']}.meta_value";
                    break;
                case 'meta_value_num':
                    $parsed = "{$primary_meta_query['alias']}.meta_value+0";
                    break;
                default:
                    if ( isset( $meta_clauses[ $orderby ] ) ) {
                        $meta_clause = $meta_clauses[ $orderby ];
                        $parsed      = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
                    }
            }
            return $parsed;
        }//773
        protected function _parse_order( $order ): ?string{
            if ( ! is_string( $order ) || empty( $order ) ) return 'ASC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
            else return 'DESC';
        }//855
    }
}else die;