<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 10:43
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    class TP_User_Query extends Query_Base{
        public function __construct( $query = null ){
            $this->_results = $this->get_results();
            if ( ! empty( $query ) ) {
                $this->prepare_query( $query );
                $this->query_user();
            }
        }//75
        public static function fill_query_vars(array ...$args ){
            $defaults = ['blog_id' => (new static)->_get_current_blog_id(),
                'role' => '','role__in' => [],'role__not_in' => [],'capability' => '',
                'capability__in' => [],'capability__not_in' => [],'meta_key' => '','meta_value' => '',
                'meta_compare' => '','include' => [],'exclude' => [],'search' => '',
                'search_columns' => [],'orderby' => 'login','order' => 'ASC', 'offset' => '',
                'number' => '','paged' => 1,'count_total' => true,'fields' => 'all',
                'who' => '','has_published_posts' => null,'nicename' => '','nicename__in' => [],
                'nicename__not_in' => [],'login' => '','login__in' => [],'login__not_in' => [],
            ];
            return (new static)->_tp_parse_args( $args, $defaults );
        }//90
        public function prepare_query(array ...$query): void{
            $tpdb = $this->_init_db();
            if ( empty( $this->query_vars ) || ! empty( $query ) ) {
                $this->_limits = null;
                $this->query_vars  = self::fill_query_vars( $query );
            }
            $this->_do_action_ref_array( 'pre_get_users', array( &$this ) );
            $qv =& $this->query_vars;
            $qv = self::fill_query_vars( $qv );
            if ( is_array( $qv['fields'] ) ) {
                $qv['fields'] = array_unique( $qv['fields'] );
                $this->_fields = array();
                foreach ( $qv['fields'] as $field ) {
                    $field                = 'ID' === $field ? 'ID' : $this->_sanitize_key( $field );
                    $this->_fields[] = "$tpdb->users.$field";
                }
                $this->_fields = implode( ',', $this->_fields );
            } elseif ( 'all' === $qv['fields'] ) $this->_fields = "$tpdb->users.*";
            else $this->_fields = "$tpdb->users.ID";
            if ( isset( $qv['count_total'] ) && $qv['count_total'] )
                $this->_fields = 'SQL_CALC_FOUND_ROWS ' . $this->_fields;
            $this->_from  = "FROM $tpdb->users";
            $this->_where = 'WHERE 1=1';
            if ( ! empty( $qv['include'] ) ) $include = $this->_tp_parse_id_list( $qv['include'] );
            else  $include = false;
            $blog_id = 0;
            if ( isset( $qv['blog_id'] ) ) $blog_id = $this->_abs_int( $qv['blog_id'] );
            if ( $qv['has_published_posts'] && $blog_id ) {
                if ( true === $qv['has_published_posts'] )
                    $post_types = $this->_get_post_types(['public' => true]);
                else $post_types = (array) $qv['has_published_posts'];
                foreach ( $post_types as &$post_type ) $post_type = $tpdb->prepare( '%s', $post_type );
                unset($post_type);
                $posts_table        = $tpdb->get_blog_prefix( $blog_id ) . 'posts';
                $this->_where .= " AND $tpdb->users.ID IN ( SELECT DISTINCT $posts_table.post_author FROM $posts_table WHERE $posts_table.post_status = 'publish' AND $posts_table.post_type IN ( " . implode( ', ', $post_types ) . ' ) )';
            }
            if ( '' !== $qv['nicename'] ) $this->_where .= $tpdb->prepare( ' AND user_nicename = %s', $qv['nicename'] );
            if ( ! empty( $qv['nicename__in'] ) ) {
                $sanitized_nicename__in = array_map( 'esc_sql', $qv['nicename__in'] );
                $nicename__in           = implode( "','", $sanitized_nicename__in );
                $this->_where     .= " AND user_nicename IN ( '$nicename__in' )";
            }
            if ( ! empty( $qv['nicename__not_in'] ) ) {
                $sanitized_nicename__not_in = array_map( 'esc_sql', $qv['nicename__not_in'] );
                $nicename__not_in           = implode( "','", $sanitized_nicename__not_in );
                $this->_where         .= " AND user_nicename NOT IN ( '$nicename__not_in' )";
            }
            if ( '' !== $qv['login'] ) $this->_where .= $tpdb->prepare( ' AND user_login = %s', $qv['login'] );
            if ( ! empty( $qv['login__in'] ) ) {
                $sanitized_login__in = array_map( 'esc_sql', $qv['login__in'] );
                $login__in           = implode( "','", $sanitized_login__in );
                $this->_where  .= " AND user_login IN ( '$login__in' )";
            }
            if ( ! empty( $qv['login__not_in'] ) ) {
                $sanitized_login__not_in = array_map( 'esc_sql', $qv['login__not_in'] );
                $login__not_in           = implode( "','", $sanitized_login__not_in );
                $this->_where      .= " AND user_login NOT IN ( '$login__not_in' )";
            }
            $this->meta_query = new TP_Meta_Query();
            $this->meta_query->parse_query_vars( $qv );
            $roles = [];
            // 'who' is deprecated use 'capabilities'
            if ( isset( $qv['role'] ) ) {
                if ( is_array( $qv['role'] ) ) $roles = $qv['role'];
                elseif ( is_string( $qv['role'] ) && ! empty( $qv['role'] ) )
                    $roles = array_map( 'trim', explode( ',', $qv['role'] ) );
            }
            $role__in = [];
            if ( isset( $qv['role__in'] ) ) $role__in = (array) $qv['role__in'];
            $role__not_in = [];
            if ( isset( $qv['role__not_in'] ) )  $role__not_in = (array) $qv['role__not_in'];
            $available_roles = [];
            if ( ! empty( $qv['capability'] ) || ! empty( $qv['capability__in'] ) || ! empty( $qv['capability__not_in'] ) ) {
                $tp_roles = $this->_init_roles();
                $tp_roles->for_site( $blog_id );
                $available_roles = $tp_roles->roles;
            }
            $capabilities = [];
            if ( ! empty( $qv['capability'] ) ) {
                if ( is_array( $qv['capability'] ) ) $capabilities = $qv['capability'];
                elseif ( is_string( $qv['capability'] ) )
                    $capabilities = array_map( 'trim', explode( ',', $qv['capability'] ) );
            }
            $capability__in = [];
            if ( ! empty( $qv['capability__in'] ) ) $capability__in = (array) $qv['capability__in'];
            $capability__not_in = [];
            if ( ! empty( $qv['capability__not_in'] ) ) $capability__not_in = (array) $qv['capability__not_in'];
            $caps_with_roles = [];
            foreach ( $available_roles as $role => $role_data ) {
                $role_caps = array_keys( array_filter( $role_data['capabilities'] ) );
                foreach ( $capabilities as $cap ) {
                    if ( in_array( $cap, $role_caps, true ) ) {
                        $caps_with_roles[ $cap ][] = $role;
                        break;
                    }
                }
                foreach ( $capability__in as $cap ) {
                    if ( in_array( $cap, $role_caps, true ) ) {
                        $role__in[] = $role;
                        break;
                    }
                }
                foreach ( $capability__not_in as $cap ) {
                    if ( in_array( $cap, $role_caps, true ) ) {
                        $role__not_in[] = $role;
                        break;
                    }
                }
            }
            $role__in     = array_merge( $role__in, $capability__in );
            $role__not_in = array_merge( $role__not_in, $capability__not_in );
            $roles        = array_unique( $roles );
            $role__in     = array_unique( $role__in );
            $role__not_in = array_unique( $role__not_in );
            if ( $blog_id && ! empty( $capabilities ) ) {
                $capabilities_clauses = ['relation' => 'AND'];
                foreach ( $capabilities as $cap ) {
                    $clause = ['relation' => 'OR'];
                    $clause[] = ['key' => $tpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                        'value' => '"' . $cap . '"','compare' => 'LIKE',];
                    if ( ! empty( $caps_with_roles[ $cap ] ) ) {
                        foreach ( $caps_with_roles[ $cap ] as $role )
                            $clause[] = ['key'=> $tpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                                'value'=> '"' . $role . '"','compare' => 'LIKE',];
                    }
                    $capabilities_clauses[] = $clause;
                }
                $role_queries[] = $capabilities_clauses;
                if ( empty( $this->meta_query->queries ) ) $this->meta_query->queries[] = $capabilities_clauses;
                else $this->meta_query->queries = ['relation' => 'AND',[$this->meta_query->queries, [$capabilities_clauses]],];
                $this->meta_query->parse_query_vars( $this->meta_query->queries );
            }
            if ( $blog_id && ( ! empty( $roles ) || ! empty( $role__in ) || ! empty( $role__not_in ) || $this->_is_multisite() ) ) {
                $role_queries = [];
                $roles_clauses = ['relation' => 'AND'];
                if ( ! empty( $roles ) ) {
                    foreach ( $roles as $role )
                        $roles_clauses[] = ['key' => $tpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                            'value' => '"' . $role . '"','compare' => 'LIKE',];
                    $role_queries[] = $roles_clauses;
                }
                $role__in_clauses = array( 'relation' => 'OR' );
                if ( ! empty( $role__in ) ) {
                    foreach ( $role__in as $role )
                        $role__in_clauses[] = ['key' => $tpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                            'value'   => '"' . $role . '"','compare' => 'LIKE',];
                    $role_queries[] = $role__in_clauses;
                }
                $role__not_in_clauses = array( 'relation' => 'AND' );
                if ( ! empty( $role__not_in ) ) {
                    foreach ( $role__not_in as $role )
                        $role__not_in_clauses[] = ['key' => $tpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                            'value' => '"' . $role . '"','compare' => 'NOT LIKE',];
                    $role_queries[] = $role__not_in_clauses;
                }
                if ( empty( $role_queries ) )
                    $role_queries[] = ['key' => $tpdb->get_blog_prefix( $blog_id ) . 'capabilities','compare' => 'EXISTS',];
                $role_queries['relation'] = 'AND';
                if ( empty( $this->meta_query->queries ) ) $this->meta_query->queries = $role_queries;
                else $this->meta_query->queries = ['relation' => 'AND',[$this->meta_query->queries, $role_queries],];
                $this->meta_query->parse_query_vars( $this->meta_query->queries );
            }
            if ( ! empty( $this->meta_query->queries ) ) {
                $clauses = $this->meta_query->get_sql( 'user', $tpdb->users, 'ID', $this );
                $this->_from  .= $clauses['join'];
                $this->_where .= $clauses['where'];
                if ( $this->meta_query->has_or_relation() ) $this->_fields = 'DISTINCT ' . $this->_fields;
            }
            $qv['order'] = isset( $qv['order'] ) ? strtoupper( $qv['order'] ) : '';
            $order = $this->_parse_order( $qv['order'] );
            if ( empty( $qv['orderby'] ) ) $ordersby = array( 'user_login' => $order );
            elseif ( is_array( $qv['orderby'] ) ) $ordersby = $qv['orderby'];
            else $ordersby = preg_split( '/[,\s]+/', $qv['orderby'] );
            $orderby_array = array();
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
                if ( 'nicename__in' === $_orderby || 'login__in' === $_orderby ) $orderby_array[] = $parsed;
                else  $orderby_array[] = $parsed . ' ' . $this->_parse_order( $_order );
            }
            if ( empty( $orderby_array ) ) $orderby_array[] = "user_login $order";
            $this->_orderby = 'ORDER BY ' . implode( ', ', $orderby_array );
            if ( isset( $qv['number'] ) && $qv['number'] > 0 ) {
                if ( $qv['offset'] ) $this->_limits = $tpdb->prepare( 'LIMIT %d, %d', $qv['offset'], $qv['number'] );
                else $this->_limits = $tpdb->prepare( 'LIMIT %d, %d', $qv['number'] * ( $qv['paged'] - 1 ), $qv['number'] );
            }
            $search = '';
            if ( isset( $qv['search'] ) ) $search = trim( $qv['search'] );
            if ( $search ) {
                $leading_wild  = ( ltrim( $search, '*' ) !== $search );
                $trailing_wild = ( rtrim( $search, '*' ) !== $search );
                if ( $leading_wild && $trailing_wild ) $wild = 'both';
                elseif ( $leading_wild ) $wild = 'leading';
                elseif ( $trailing_wild ) $wild = 'trailing';
                else  $wild = false;
                if ( $wild ) $search = trim( $search, '*' );
                $search_columns = array();
                if ( $qv['search_columns'] )
                    $search_columns = array_intersect( $qv['search_columns'], array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name' ) );
                if ( ! $search_columns ) {
                    if ( false !== strpos( $search, '@' ) ) $search_columns = array( 'user_email' );
                    elseif ( is_numeric( $search ) ) $search_columns = array( 'user_login', 'ID' );
                    elseif ( preg_match( '|^https?://|', $search ) && ! ( $this->_is_multisite() && $this->_tp_is_large_network( 'users' ) ) )
                        $search_columns = array( 'user_url' );
                    else $search_columns = array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' );
                }
                $search_columns = $this->_apply_filters( 'user_search_columns', $search_columns, $search, $this );
                $this->_where .= $this->_get_search_sql( $search, $search_columns, $wild );
            }
            if ( ! empty( $include ) ) {
                $ids                = implode( ',', $include );
                $this->_where .= " AND $tpdb->users.ID IN ($ids)";
            } elseif ( ! empty( $qv['exclude'] ) ) {
                $ids                = implode( ',', $this->_tp_parse_id_list( $qv['exclude'] ) );
                $this->_where .= " AND $tpdb->users.ID NOT IN ($ids)";
            }
            if ( ! empty( $qv['date_query'] ) && is_array( $qv['date_query'] ) ) {
                $date_query         = new TP_Date_Query( $qv['date_query'], 'user_registered' );
                $this->_where .= $date_query->get_sql();
            }
            $this->_do_action_ref_array( 'pre_user_query', array( &$this ) );
        }//253
        public function query_user(): void{
            $tpdb = $this->_init_db();
            $qv =& $this->query_vars;
            $this->_results = $this->_apply_filters_ref_array( 'users_pre_query', array( null, &$this ) );
            if ( null === $this->_results ) {
                $this->request = TP_SELECT . " $this->_fields $this->_from $this->_where $this->_orderby $this->_limits";
                if ( is_array( $qv['fields'] ) || 'all' === $qv['fields'] )
                    $this->_results = $tpdb->get_results( $this->request );
                else  $this->_results = $tpdb->get_col( $this->request );
                if ( isset( $qv['count_total'] ) && $qv['count_total'] ) {
                    $found_users_query = $this->_apply_filters( 'found_users_query', 'SELECT FOUND_ROWS()', $this );
                    $this->_total_users = (int) $tpdb->get_var( $found_users_query );
                }
            }
            if ( ! $this->_results ) return;
            if ( 'all_with_meta' === $qv['fields'] ) {
                $this->_cache_users( $this->_results );
                $r = [];
                foreach ( $this->_results as $userid ) $r[ $userid ] = new TP_User( $userid, '', $qv['blog_id'] );
                $this->_results = $r;
            } elseif ( 'all' === $qv['fields'] ) {
                foreach ( $this->_results as $key => $user )
                    $this->_results[ $key ] = new TP_User( $user, '', $qv['blog_id'] );
            }
        }//749
        public function get( $query_var ){
            if ( isset( $this->query_vars[ $query_var ] ) )
                return $this->query_vars[ $query_var ];
            return null;
        }//827
        public function set( $query_var, $value ): void{
            $this->query_vars[ $query_var ] = $value;
        }//843
        protected function _get_search_sql( $string, $cols, $wild = false ):string{
            $tpdb = $this->_init_db();
            $searches      = [];
            $leading_wild  = ( 'leading' === $wild || 'both' === $wild ) ? '%' : '';
            $trailing_wild = ( 'trailing' === $wild || 'both' === $wild ) ? '%' : '';
            $like          = $leading_wild . $tpdb->esc_like( $string ) . $trailing_wild;
            foreach ( $cols as $col ) {
                if ( 'ID' === $col ) $searches[] = $tpdb->prepare( "$col = %s", $string );
                else $searches[] = $tpdb->prepare( "$col LIKE %s", $like );
            }
            return ' AND (' . implode( ' OR ', $searches ) . ')';
        }//860
        public function get_results() {
            return $this->_results;
        }//886
        public function get_total(): int{
            return $this->_total_users;
        }//897
        protected function _parse_orderby( $orderby ){
            $tpdb = $this->_init_db();
            if( $this->meta_query instanceof TP_Meta_Query ){}
            $meta_query_clauses = $this->meta_query->get_clauses();
            $_orderby = '';
            if ( in_array( $orderby, array( 'login', 'nicename', 'email', 'url', 'registered' ), true ) ) {
                $_orderby = 'user_' . $orderby;
            } elseif ( in_array( $orderby, array( 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered' ), true ) ) {
                $_orderby = $orderby;
            } elseif ( 'name' === $orderby || 'display_name' === $orderby ) {
                $_orderby = 'display_name';
            } elseif ( 'post_count' === $orderby ) {
                // @todo Avoid the JOIN.
                $where             = $this->_get_posts_by_author_sql( 'post' );
                $this->_from .= " LEFT OUTER JOIN (
				SELECT post_author, COUNT(*) as post_count
				FROM $tpdb->posts
				$where
				GROUP BY post_author
			) p ON ({$tpdb->users}.ID = p.post_author)
			";
                $_orderby          = 'post_count';
            } elseif ( 'ID' === $orderby || 'id' === $orderby ) {
                $_orderby = 'ID';
            } elseif ( 'meta_value' === $orderby || $this->get( 'meta_key' ) === $orderby ) {
                $_orderby = "$tpdb->user_meta.meta_value";
            } elseif ( 'meta_value_num' === $orderby ) {
                $_orderby = "$tpdb->user_meta.meta_value+0";
            } elseif ( 'include' === $orderby && ! empty( $this->query_vars['include'] ) ) {
                $include     = $this->_tp_parse_id_list( $this->query_vars['include'] );
                $include_sql = implode( ',', $include );
                $_orderby    = "FIELD( $tpdb->users.ID, $include_sql )";
            } elseif ( 'nicename__in' === $orderby ) {
                $sanitized_nicename__in = array_map( 'esc_sql', $this->query_vars['nicename__in'] );
                $nicename__in           = implode( "','", $sanitized_nicename__in );
                $_orderby               = "FIELD( user_nicename, '$nicename__in' )";
            } elseif ( 'login__in' === $orderby ) {
                $sanitized_login__in = array_map( 'esc_sql', $this->query_vars['login__in'] );
                $login__in           = implode( "','", $sanitized_login__in );
                $_orderby            = "FIELD( user_login, '$login__in' )";
            } elseif ( isset( $meta_query_clauses[ $orderby ] ) ) {
                $meta_clause = $meta_query_clauses[ $orderby ];
                $_orderby    = sprintf( 'CAST(%s.meta_value AS %s)', $this->_esc_sql( $meta_clause['alias'] ), $this->_esc_sql( $meta_clause['cast'] ) );
            }
            return $_orderby;
        }//911
        protected function _parse_order( $order ): ?string{
            if ( ! is_string( $order ) || empty( $order ) )  return 'DESC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
             else  return 'DESC';
        }//968
    }
}else die;