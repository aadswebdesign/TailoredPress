<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 20:57
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Libs\TP_Comment;
if(ABSPATH){
    class TP_Comment_Query extends Query_Base {
        protected $_sql_clauses = ['select'  => '','from' => '','where' => [],'groupby' => '','orderby' => '','limits' => '',];
        public function __construct( $query = '' ) {
            $this->query_var_defaults = [
                'author_email' => '','author_url' => '','author__in' => '','author__not_in' => '',
                'include_unapproved' => '','fields' => '','ID' => '','comment__in' => '','comment__not_in' => '',
                'karma' => '','number' => '','offset' => '','no_found_rows' => true,'orderby' => '',
                'order' => 'DESC','paged' => 1,'parent' => '','parent__in' => '','parent__not_in' => '',
                'post_author__in' => '','post_author__not_in' => '','post_ID' => '','post_id' => 0,'post__in' => '',
                'post__not_in' => '','post_author' => '','post_name' => '','post_parent' => '','post_status' => '',
                'post_type' => '','status' => 'all','type' => '','type__in' => '','type__not_in' => '',
                'user_id' => '','search' => '','count' => false,'meta_key' => '','meta_value' => '','meta_query' => '',
                'date_query' => null,'hierarchical' => false,'cache_domain' => 'core','update_comment_meta_cache' => true,'update_comment_post_cache' => false,
            ];
            if ( ! empty( $query ) ) $this->query_comment( $query );
        }//274
        public function parse_query( $query = '' ): void{
            if ( empty( $query ) ) $query = $this->query_vars;
            $this->query_vars = $this->_tp_parse_args( $query, $this->query_var_defaults );
            $this->_do_action_ref_array( 'parse_comment_query', array( &$this ) );
        }//335
        public function query_comment( $query ){
            $this->query_vars = $this->_tp_parse_args( $query );
            return $this->get_comments();
        }//365
        public function get_comments(){
            $tpdb = $this->_init_db();
            $this->parse_query();
            $this->meta_query = new TP_Meta_Query();
            $this->meta_query->parse_query_vars( $this->query_vars );
            $this->_do_action_ref_array( 'pre_get_comments', array( &$this ) );
            $this->meta_query->parse_query_vars( $this->query_vars );
            if ( ! empty( $this->meta_query->queries ) )
                $this->_meta_query_clauses = $this->meta_query->get_sql( 'comment', $tpdb->comments, 'comment_ID', $this );
            $comment_data = null;
            $comment_data = $this->_apply_filters_ref_array( 'comments_pre_query', [ $comment_data, &$this] );
            if ( null !== $comment_data ) {
                if ( is_array( $comment_data ) && ! $this->query_vars['count'] )
                    $this->comments = $comment_data;
                return $comment_data;
            }
            $_args = $this->_tp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) );
            unset( $_args['fields'], $_args['update_comment_meta_cache'], $_args['update_comment_post_cache'] );
            $key          = md5( serialize( $_args ) );
            $last_changed = $this->_tp_cache_get_last_changed( 'comment' );
            $cache_key   = "get_comments:$key:$last_changed";
            $cache_value = $this->_tp_cache_get( $cache_key, 'comment' );
            if ( false === $cache_value ) {
                $comment_ids = $this->_get_comment_ids();
                if ( $comment_ids ) $this->__set_found_comments();
                $cache_value = ['comment_ids'=> $comment_ids,'found_comments' => $this->found_comments,];
                $this->_tp_cache_add( $cache_key, $cache_value, 'comment' );
            }else {
                $comment_ids          = $cache_value['comment_ids'];
                $this->found_comments = $cache_value['found_comments'];
            }
            if ( $this->found_comments && $this->query_vars['number'] )
                $this->max_num_pages = ceil( $this->found_comments / $this->query_vars['number'] );
            // If querying for a count only, there's nothing more to do.
            if ( $this->query_vars['count'] ) return (int) $comment_ids;
            $comment_ids = array_map( 'intval', $comment_ids );
            if ( 'ids' === $this->query_vars['fields'] ) {
                $this->comments = $comment_ids;
                return $this->comments;
            }
            $this->_prime_comment_caches( $comment_ids, $this->query_vars['update_comment_meta_cache'] );
            $_comments = [];
            foreach ( $comment_ids as $comment_id ) {
                $_comment = $this->_get_comment( $comment_id );
                if ( $_comment ) $_comments[] = $_comment;
            }
            if ( $this->query_vars['update_comment_post_cache'] ) {
                $comment_post_ids = array();
                foreach ( $_comments as $_comment ) $comment_post_ids[] = $_comment->comment_post_ID;
                $this->_prime_post_caches( $comment_post_ids, false, false );
            }
            $_comments = $this->_apply_filters_ref_array( 'the_comments', array( $_comments, &$this ) );
            // Convert to WP_Comment instances.
            $comments = array_map( [$this,'_get_comment'], $_comments );
            if ( $this->query_vars['hierarchical'] )
                $comments = $this->_fill_descendants( $comments );
            $this->comments = $comments;
            return $this->comments;
        }//379
        protected function _get_comment_ids(){
            $tpdb = $this->_init_db();
            $approved_clauses = [];
            $status_clauses = [];
            $statuses       = $this->_tp_parse_list( $this->query_vars['status'] );
            if ( empty( $statuses ) ) $statuses = array( 'all' );
            if ( ! in_array( 'any', $statuses, true ) ) {
                foreach ( $statuses as $status ) {
                    switch ( $status ) {
                        case 'hold':
                            $status_clauses[] = "comment_approved = '0'";
                            break;
                        case 'approve':
                            $status_clauses[] = "comment_approved = '1'";
                            break;
                        case 'all':
                        case '':
                            $status_clauses[] = "( comment_approved = '0' OR comment_approved = '1' )";
                            break;
                        default:
                            $status_clauses[] = $tpdb->prepare( 'comment_approved = %s', $status );
                            break;
                    }
                }
                if ( ! empty( $status_clauses ) )
                    $approved_clauses[] = '( ' . implode( ' OR ', $status_clauses ) . ' )';
            }
            if ( ! empty( $this->query_vars['include_unapproved'] ) ) {
                $include_unapproved = $this->_tp_parse_list( $this->query_vars['include_unapproved'] );
                //$unapproved_ids    = [];
                //$unapproved_emails = [];
                foreach ( $include_unapproved as $unapproved_identifier ) {
                    if ( is_numeric( $unapproved_identifier ) )
                        $approved_clauses[] = $tpdb->prepare( "( user_id = %d AND comment_approved = '0' )", $unapproved_identifier );
                    else if ( ! empty( $_GET['unapproved'] ) && ! empty( $_GET['moderation-hash'] ) )
                        $approved_clauses[] = $tpdb->prepare( "( comment_author_email = %s AND comment_approved = '0' AND {$tpdb->comments}.comment_ID = %d )", $unapproved_identifier, (int) $_GET['unapproved'] );
                    else $approved_clauses[] = $tpdb->prepare( "( comment_author_email = %s AND comment_approved = '0' )", $unapproved_identifier );
                }
            }
            if ( ! empty( $approved_clauses ) ) {
                if ( 1 === count( $approved_clauses ) )
                    $this->_sql_clauses['where']['approved'] = $approved_clauses[0];
                else $this->_sql_clauses['where']['approved'] = '( ' . implode( ' OR ', $approved_clauses ) . ' )';
            }
            $order = ( 'ASC' === strtoupper( $this->query_vars['order'] ) ) ? 'ASC' : 'DESC';
            if ( in_array( $this->query_vars['orderby'], ['none', [], false ], true ) )
                $this->_orderby = '';
            elseif ( ! empty( $this->query_vars['orderby'] ) ) {
                $ordersby = is_array( $this->query_vars['orderby'] ) ?
                    $this->query_vars['orderby'] :
                    preg_split( '/[,\s]/', $this->query_vars['orderby'] );
                $orderby_array = [];
                $found_orderby_comment_id = false;
                foreach ( $ordersby as $_key => $_value ) {
                    if ( ! $_value )  continue;
                    if ( is_int( $_key ) ) {
                        $_orderby = $_value;
                        $_order   = $order;
                    } else {
                        $_orderby = $_key;
                        $_order   = $_value;
                    }
                    if ( ! $found_orderby_comment_id && in_array( $_orderby, array( 'comment_ID', 'comment__in' ), true ) )
                        $found_orderby_comment_id = true;
                    $parsed = $this->_parse_orderby( $_orderby );
                    if ( ! $parsed ) continue;
                    if ( 'comment__in' === $_orderby ) {
                        $orderby_array[] = $parsed;
                        continue;
                    }
                    $orderby_array[] = $parsed . ' ' . $this->_parse_order( $_order );
                }
                if ( empty( $orderby_array ) )
                    $orderby_array[] = "$tpdb->comments.comment_date_gmt $order";
                if ( ! $found_orderby_comment_id ) {
                    $comment_id_order = '';
                    foreach ( $orderby_array as $orderby_clause ) {
                        if ( preg_match( '/comment_date(?:_gmt)*\ (ASC|DESC)/', $orderby_clause, $match ) ) {
                            $comment_id_order = $match[1];
                            break;
                        }
                    }
                    if ( ! $comment_id_order ) {
                        /** @noinspection LoopWhichDoesNotLoopInspection *///todo
                        foreach ($orderby_array as $orderby_clause ) {
                            if ( false !== strpos( 'ASC', $orderby_clause ) )
                                $comment_id_order = 'ASC';
                            else $comment_id_order = 'DESC';
                            break;
                        }
                    }
                    if ( ! $comment_id_order )  $comment_id_order = 'DESC';
                    $orderby_array[] = "$tpdb->comments.comment_ID $comment_id_order";
                }
                $this->_orderby = implode( ', ', $orderby_array );
            }else $this->_orderby = "$tpdb->comments.comment_date_gmt $order";
            $number = $this->_abs_int( $this->query_vars['number'] );
            $offset = $this->_abs_int( $this->query_vars['offset'] );
            $paged  = $this->_abs_int( $this->query_vars['paged'] );
            $this->_limits = '';
            if ( ! empty( $number ) ) {
                if ( $offset ) $this->_limits = 'LIMIT ' . $offset . ',' . $number;
                else $this->_limits = 'LIMIT ' . ( $number * ( $paged - 1 ) ) . ',' . $number;
            }
            if ( $this->query_vars['count'] ) $this->_fields = 'COUNT(*)';
            else $this->_fields = "$tpdb->comments.comment_ID";
            $post_id = $this->_abs_int( $this->query_vars['post_id'] );
            if ( ! empty( $post_id ) )
                $this->_sql_clauses['where']['post_id'] = $tpdb->prepare( 'comment_post_ID = %d', $post_id );
            // Parse comment IDs for an IN clause.
            if ( ! empty( $this->query_vars['comment__in'] ) )
                $this->_sql_clauses['where']['comment__in'] = "$tpdb->comments.comment_ID IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['comment__in'] ) ) . ' )';
            // Parse comment IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['comment__not_in'] ) )
                $this->_sql_clauses['where']['comment__not_in'] = "$tpdb->comments.comment_ID NOT IN ( " . implode( ',', $this->_tp_parse_id_list( $this->query_vars['comment__not_in'] ) ) . ' )';
            // Parse comment parent IDs for an IN clause.
            if ( ! empty( $this->query_vars['parent__in'] ) )
                $this->_sql_clauses['where']['parent__in'] = 'comment_parent IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['parent__in'] ) ) . ' )';
            // Parse comment parent IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['parent__not_in'] ) )
                $this->_sql_clauses['where']['parent__not_in'] = 'comment_parent NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['parent__not_in'] ) ) . ' )';
            // Parse comment post IDs for an IN clause.
            if ( ! empty( $this->query_vars['post__in'] ) )
                $this->_sql_clauses['where']['post__in'] = 'comment_post_ID IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['post__in'] ) ) . ' )';
            // Parse comment post IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['post__not_in'] ) )
                $this->_sql_clauses['where']['post__not_in'] = 'comment_post_ID NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['post__not_in'] ) ) . ' )';
            if ( '' !== $this->query_vars['author_email'] )
                $this->_sql_clauses['where']['author_email'] = $tpdb->prepare( 'comment_author_email = %s', $this->query_vars['author_email'] );
            if ( '' !== $this->query_vars['author_url'] )
                $this->_sql_clauses['where']['author_url'] = $tpdb->prepare( 'comment_author_url = %s', $this->query_vars['author_url'] );
            if ( '' !== $this->query_vars['karma'] )
                $this->_sql_clauses['where']['karma'] = $tpdb->prepare( 'comment_karma = %d', $this->query_vars['karma'] );
            // Filtering by comment_type: 'type', 'type__in', 'type__not_in'.
            $raw_types = [
                'IN'     => array_merge( (array) $this->query_vars['type'], (array) $this->query_vars['type__in'] ),
                'NOT IN' => (array) $this->query_vars['type__not_in'],
            ];
            $comment_types = [];
            foreach ( $raw_types as $operator => $_raw_types ) {
                $_raw_types = array_unique( $_raw_types );
                foreach ( $_raw_types as $type ) {
                    switch ( $type ) {
                        case 'all':
                            break;
                        case 'comment':
                        case 'comments':
                            $comment_types[ $operator ][] = "''";
                            $comment_types[ $operator ][] = "'comment'";
                            break;
                        case 'pings':
                            $comment_types[ $operator ][] = "'pingback'";
                            $comment_types[ $operator ][] = "'trackback'";
                            break;
                        default:
                            $comment_types[ $operator ][] = $tpdb->prepare( '%s', $type );
                            break;
                    }
                }
                if ( ! empty( $comment_types[ $operator ] ) ) {
                    $types_sql = implode( ', ', $comment_types[ $operator ] );
                    $this->_sql_clauses['where'][ 'comment_type__' . strtolower( str_replace( ' ', '_', $operator ) ) ] = "comment_type $operator ($types_sql)";
                }
            }
            $parent = $this->query_vars['parent'];
            if ( $this->query_vars['hierarchical'] && ! $parent ) $parent = 0;
            if ( '' !== $parent )
                $this->_sql_clauses['where']['parent'] = $tpdb->prepare( 'comment_parent = %d', $parent );
            if ( is_array( $this->query_vars['user_id'] ) )
                $this->_sql_clauses['where']['user_id'] = 'user_id IN (' . implode( ',', array_map( 'absint', $this->query_vars['user_id'] ) ) . ')';
            elseif ( '' !== $this->query_vars['user_id'] )
                $this->_sql_clauses['where']['user_id'] = $tpdb->prepare( 'user_id = %d', $this->query_vars['user_id'] );
            if ( isset( $this->query_vars['search'] ) && $this->query_vars['search'] !== '') {
                $search_sql = $this->_get_search_sql(
                    $this->query_vars['search'],
                    ['comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_content' ]
                );
                $this->_sql_clauses['where']['search'] = preg_replace( '/^\s*AND\s*/', '', $search_sql );
            }
            $join_posts_table = false;
            $plucked          = $this->_tp_array_slice_assoc( $this->query_vars, array( 'post_author', 'post_name', 'post_parent' ) );
            $post_fields      = array_filter( $plucked );
            if ( ! empty( $post_fields ) ) {
                $join_posts_table = true;
                foreach ( $post_fields as $field_name => $field_value ) {
                    // $field_value may be an array.
                    $assets = array_fill( 0, count( (array) $field_value ), '%s' );
                    $this->_sql_clauses['where'][ $field_name ] = $tpdb->prepare( " {$tpdb->posts}.{$field_name} IN (" . implode( ',', $assets ) . ')', $field_value );
                }
            }
            foreach ( array( 'post_status', 'post_type' ) as $field_name ) {
                if ( ! empty( $this->query_vars[ $field_name ] ) ) {
                    $q_values = $this->query_vars[ $field_name ];
                    if ( ! is_array( $q_values ) ) $q_values = explode( ',', $q_values );
                    if ( empty( $q_values ) || in_array( 'any', $q_values, true )) continue;
                    $join_posts_table = true;
                    $esses = array_fill( 0, count( $q_values ), '%s' );
                    $this->_sql_clauses['where'][ $field_name ] = $tpdb->prepare( " {$tpdb->posts}.{$field_name} IN (" . implode( ',', $esses ) . ')', $q_values );
                }
            }
            // Comment author IDs for an IN clause.
            if ( ! empty( $this->query_vars['author__in'] ) )
                $this->_sql_clauses['where']['author__in'] = 'user_id IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['author__in'] ) ) . ' )';
            // Comment author IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['author__not_in'] ) )
                $this->_sql_clauses['where']['author__not_in'] = 'user_id NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['author__not_in'] ) ) . ' )';
            // Post author IDs for an IN clause.
            if ( ! empty( $this->query_vars['post_author__in'] ) ) {
                $join_posts_table = true;
                $this->_sql_clauses['where']['post_author__in'] = 'post_author IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['post_author__in'] ) ) . ' )';
            }
            // Post author IDs for a NOT IN clause.
            if ( ! empty( $this->query_vars['post_author__not_in'] ) ) {
                $join_posts_table = true;
                $this->_sql_clauses['where']['post_author__not_in'] = 'post_author NOT IN ( ' . implode( ',', $this->_tp_parse_id_list( $this->query_vars['post_author__not_in'] ) ) . ' )';
            }
            $this->_join    = '';
            $this->_groupby = '';
            if ( $join_posts_table )
                $this->_join .= "JOIN $tpdb->posts ON $tpdb->posts.ID = $tpdb->comments.comment_post_ID";
            if ( ! empty( $this->meta_query_clauses ) ) {
                $this->_join .= $this->meta_query_clauses['join'];
                $this->_sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $this->meta_query_clauses['where'] );
                if ( ! $this->query_vars['count'] )
                    $this->_groupby = "{$tpdb->comments}.comment_ID";
            }
            if ( ! empty( $this->query_vars['date_query'] ) && is_array( $this->query_vars['date_query'] ) ) {
                $this->date_query = new TP_Date_Query( $this->query_vars['date_query'], 'comment_date' );
                $this->_sql_clauses['where']['date_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_query->get_sql() );
            }
            $this->_where = implode( ' AND ', $this->_sql_clauses['where'] );
            $clauses = ['fields', 'join', 'where', 'orderby', 'limits', 'groupby'];
            $clauses = $this->_apply_filters_ref_array( 'comments_clauses', [compact( $clauses ), &$this] );
            $this->_fields  = $clauses['fields'] ?? '';
            $this->_join    = $clauses['join'] ?? '';
            $this->_where   = $clauses['where'] ?? '';
            $this->_orderby = $clauses['orderby'] ?? '';
            $this->_limits  = $clauses['limits'] ?? '';
            $this->_groupby = $clauses['groupby'] ?? '';
            $this->_filtered_where_clause = $this->_where;
            if ( $this->_where )  $this->_where = 'WHERE ' . $this->_where;
            if ( $this->_groupby ) $this->_groupby = 'GROUP BY ' . $this->_groupby;
            if ( $this->_orderby ) $this->_orderby = "ORDER BY $this->_orderby";
            $found_rows = '';
            if ( ! $this->query_vars['no_found_rows'] ) $found_rows = 'SQL_CALC_FOUND_ROWS';
            $this->_sql_clauses['select']  = "SELECT $found_rows $this->_fields";
            if($this->_tpdb->comments) //fixme 1
            $this->_sql_clauses['from']    = "FROM $this->_tpdb->comments $this->_join";
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
            if ( $this->query_vars['count'] )
                return (int) $tpdb->get_var( $this->request );
            else {
                $comment_ids = $tpdb->get_col( $this->request );
                return array_map( 'intval', $comment_ids );
            }
        }//541
        private function __set_found_comments(): void{
            $tpdb = $this->_init_db();
            if ( $this->query_vars['number'] && ! $this->query_vars['no_found_rows'] ) {
                $found_comments_query = $this->_apply_filters( 'found_comments_query', 'SELECT FOUND_ROWS()', $this );
                $this->found_comments = (int) $tpdb->get_var( $found_comments_query );
            }
        }//989
        protected function _fill_descendants( $comments ){
            $levels = [0 => $this->_tp_list_pluck( $comments, 'comment_ID' ),];
            $key          = md5( serialize( $this->_tp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
            $last_changed = $this->_tp_cache_get_last_changed( 'comment' );
            $level        = 0;
            $exclude_keys = array( 'parent', 'parent__in', 'parent__not_in' );
            do {
                $child_ids           = [];
                $un_cached_parent_ids = [];
                $_parent_ids         = $levels[ $level ];
                foreach ( $_parent_ids as $parent_id ) {
                    $cache_key        = "get_comment_child_ids:$parent_id:$key:$last_changed";
                    $parent_child_ids = $this->_tp_cache_get( $cache_key, 'comment' );
                    if ( false !== $parent_child_ids )
                        $child_ids = $this->_tp_array_merge($child_ids, $parent_child_ids);
                    else $un_cached_parent_ids[] = $parent_id;
                }
                if ( $un_cached_parent_ids ) {
                    // Fetch this level of comments.
                    $parent_query_args = $this->query_vars;
                    foreach ( $exclude_keys as $exclude_key )
                        $parent_query_args[ $exclude_key ] = '';
                    $parent_query_args['parent__in']    = $un_cached_parent_ids;
                    $parent_query_args['no_found_rows'] = true;
                    $parent_query_args['hierarchical']  = false;
                    $parent_query_args['offset']        = 0;
                    $parent_query_args['number']        = 0;
                    $level_comments = $this->_get_comments( $parent_query_args );
                    $parent_map = array_fill_keys( $un_cached_parent_ids, array() );
                    foreach ( $level_comments as $level_comment ) {
                        $parent_map[ $level_comment->comment_parent ][] = $level_comment->comment_ID;
                        $child_ids[]                                    = $level_comment->comment_ID;
                    }
                    $data = [];
                    foreach ( $parent_map as $parent_id => $children ) {
                        $cache_key          = "get_comment_child_ids:$parent_id:$key:$last_changed";
                        $data[ $cache_key ] = $children;
                    }
                    $this->_tp_cache_set_multiple( $data, 'comment' );
                }
                $level++;
                $levels[ $level ] = $child_ids;
            } while ( $child_ids );
            $descendant_ids = [];
            for ( $i = 1, $c = count( $levels ); $i < $c; $i++ )
                $descendant_ids = $this->_tp_array_merge($descendant_ids, $levels[ $i ]);
            $this->_prime_comment_caches( $descendant_ids, $this->query_vars['update_comment_meta_cache'] );
            $all_comments = $comments;
            foreach ( $descendant_ids as $descendant_id )
                $all_comments[] = $this->_get_comment( $descendant_id );
            if ( 'threaded' === $this->query_vars['hierarchical'] ) {
                $threaded_comments = [];
                $ref = [];
                foreach ($all_comments as $k => $c ) {
                    $_comment_thing = $this->_get_comment( $c->comment_ID );
                    $comment_thing = null;
                    if( $_comment_thing instanceof TP_Comment ){
                        $comment_thing = $_comment_thing;
                    }
                    if ( ! isset( $ref[ $c->comment_parent ] ) ) {
                        $threaded_comments[ $comment_thing->comment_ID ] = $comment_thing;
                        $ref[ $comment_thing->comment_ID ] = $threaded_comments[ $comment_thing->comment_ID ];
                    } else {
                        $ref[ $comment_thing->comment_parent ]->add_child( $comment_thing );
                        $ref[ $comment_thing->comment_ID ] = $ref[ $comment_thing->comment_parent ]->get_child( $comment_thing->comment_ID );
                    }
                }
                foreach ( $ref as $_ref ) $_ref->populated_children( true );
                $comments = $threaded_comments;
            } else $comments = $all_comments;
            return $comments;
        }//1020
        protected function _get_search_sql( $search, $columns ): string{
            $tpdb = $this->_init_db();
            $like = '%' . $tpdb->esc_like( $search ) . '%';
            $searches = array();
            foreach ( $columns as $column )
                $searches[] = $tpdb->prepare( "$column LIKE %s", $like );
            return ' AND (' . implode( ' OR ', $searches ) . ')';
        }//1139
        protected function _parse_orderby( $orderby ){
            $tpdb = $this->_init_db();
            $allowed_keys = [
                'comment_agent','comment_approved','comment_author','comment_author_email','comment_author_IP',
                'comment_author_url','comment_content','comment_date','comment_date_gmt','comment_ID',
                'comment_karma','comment_parent','comment_post_ID','comment_type','user_id',
            ];
            if ( ! empty( $this->query_vars['meta_key'] ) ) {
                $allowed_keys[] = $this->query_vars['meta_key'];
                $allowed_keys[] = 'meta_value';
                $allowed_keys[] = 'meta_value_num';
            }
            $_meta_query = $this->meta_query;
            $meta_query = null;
            if($_meta_query instanceof TP_Meta_Query){
                $meta_query = $_meta_query;
            }
            $meta_query_clauses = $meta_query->get_clauses();
            if ( $meta_query_clauses )
                $allowed_keys = array_merge( $allowed_keys, array_keys( $meta_query_clauses ) );
            $parsed = false;
            if ( $this->query_vars['meta_key'] === $orderby || 'meta_value' === $orderby ) {
                $parsed = "$tpdb->comment_meta.meta_value";
            } elseif ( 'meta_value_num' === $orderby ) {
                $parsed = "$tpdb->comment_meta.meta_value+0";
            } elseif ( 'comment__in' === $orderby ) {
                $comment__in = implode( ',', array_map( 'absint', $this->query_vars['comment__in'] ) );
                $parsed      = "FIELD( {$tpdb->comments}.comment_ID, $comment__in )";
            } elseif ( in_array( $orderby, $allowed_keys, true ) ) {
                if ( isset( $meta_query_clauses[ $orderby ] ) ) {
                    $meta_clause = $meta_query_clauses[ $orderby ];
                    $parsed      = sprintf( 'CAST(%s.meta_value AS %s)', $this->_esc_sql( $meta_clause['alias'] ), $this->_esc_sql( $meta_clause['cast'] ) );
                } else $parsed = "$tpdb->comments.$orderby";
            }
            return $parsed;
        }//1162
        protected function _parse_order( $order ): ?string{
            if (!is_string($order)|| empty($order)) return 'DESC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
            else return 'DESC';
        }//1223
    }
}else die;