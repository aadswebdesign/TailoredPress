<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-4-2022
 * Time: 15:38
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    class TP_Query extends Query_Base {
        /**
         * @description Resets query flags to false.
         */
        private function __init_query_flags(): void{
            $this->is_single            = false;
            $this->is_preview           = false;
            $this->is_page              = false;
            $this->is_archive           = false;
            $this->is_date              = false;
            $this->is_year              = false;
            $this->is_month             = false;
            $this->is_day               = false;
            $this->is_time              = false;
            $this->is_author            = false;
            $this->is_category          = false;
            $this->is_tag               = false;
            $this->is_tax               = false;
            $this->is_search            = false;
            $this->is_feed              = false;
            $this->is_comment_feed      = false;
            $this->is_trackback         = false;
            $this->is_home              = false;
            $this->is_privacy_policy    = false;
            $this->is_404               = false;
            $this->is_paged             = false;
            $this->is_admin             = false;
            $this->is_attachment        = false;
            $this->is_singular          = false;
            $this->is_robots            = false;
            $this->is_favicon           = false;
            $this->is_posts_page        = false;
            $this->is_post_type_archive = false;
        }//466
        /**
         * @description Initiates object properties and sets default values.
         */
        public function init(): void{
            unset( $this->posts,$this->query );
            $this->query_vars = [];
            unset( $this->queried_object,$this->queried_object_id );
            $this->post_count   = 0;
            $this->current_post = -1;
            $this->in_the_loop  = false;
            unset( $this->request,$this->post,$this->comments,$this->comment );
            $this->comment_count         = 0;
            $this->current_comment       = -1;
            $this->found_posts           = 0;
            $this->max_num_pages         = 0;
            $this->max_num_comment_pages = 0;
            $this->__init_query_flags();
        }//502
        /**
         * @description Reparse the query vars.
         */
        public function parse_query_vars(): void{
            $this->parse_query();
        }//529  old531
        /**
         * @description Fills in the query variables, which do not exist within the parameter.
         * @param $array
         * @return mixed
         */
        public function fill_query_vars( $array ){
            $keys = (array)$this->_query_keys;
            foreach ( $keys as $key ) if ( ! isset( $array[ $key ] ) ) $array[ $key ] = '';
            $array_keys = (array)$this->_array_keys;
            foreach ( $array_keys as $key ) if ( ! isset( $array[ $key ] ) ) $array[ $key ] = [];
            return $array;
        }//542
        /**
         * @description Parse a query string and set query type booleans.
         * @param string $query
         */
        public function parse_query( $query = '' ): void{
            if ( ! empty( $query ) ) {
                $this->init();
                $this->query      = $this->_tp_parse_args( $query );
                $this->query_vars = $this->query;
            }elseif ( ! isset( $this->query ) ) $this->query = $this->query_vars;
            $this->query_vars = $this->fill_query_vars( $this->query_vars );
            $qv = &$this->query_vars;
            $this->_query_vars_changed = true;
            if(!empty( $qv['robots'] )) $this->is_robots = true;
            elseif(!empty( $qv['favicon'])) $this->is_favicon = true;
            if ( ! is_scalar( $qv['p'] ) || (int) $qv['p'] < 0 ) {
                $qv['p']     = 0;
                $qv['error'] = NOT_FOUND;
            } else $qv['p'] = (int) $qv['p'];
            $qv['page_id']  = $this->_abs_int( $qv['page_id'] );
            $qv['year']     = $this->_abs_int( $qv['year'] );
            $qv['monthnum'] = $this->_abs_int( $qv['monthnum'] );
            $qv['day']      = $this->_abs_int( $qv['day'] );
            $qv['w']        = $this->_abs_int( $qv['w'] );
            $qv['m']        = is_scalar( $qv['m'] ) ? preg_replace( '|\D|', '', $qv['m'] ) : '';
            $qv['paged']    = $this->_abs_int( $qv['paged'] );
            $qv['cat']      = preg_replace( '|[^0-9,-]|', '', $qv['cat'] );    // Comma-separated list of positive or negative integers.
            $qv['author']   = preg_replace( '|[^0-9,-]|', '', $qv['author'] ); // Comma-separated list of positive or negative integers.
            $qv['page_name'] = trim( $qv['page_name'] );
            $qv['name']     = trim( $qv['name'] );
            $qv['title']    = trim( $qv['title'] );
            if (''!== $qv['hour']) $qv['hour'] = $this->_abs_int( $qv['hour'] );
            if ( '' !== $qv['minute'] ) $qv['minute'] = $this->_abs_int( $qv['minute'] );
            if ( '' !== $qv['second'] ) $qv['second'] = $this->_abs_int( $qv['second'] );
            if ( '' !== $qv['menu_order'] ) $qv['menu_order'] = $this->_abs_int( $qv['menu_order'] );
            if ( ! is_scalar( $qv['s'] ) || ( ! empty( $qv['s'] ) && strlen( $qv['s'] ) > 1600 ) ) $qv['s'] = '';
            if ( '' !== $qv['sub_post'] )  $qv['attachment'] = $qv['sub_post'];
            if ( '' !== $qv['sub_post_id'] ) $qv['attachment_id'] = $qv['sub_post_id'];
            $qv['attachment_id'] = $this->_abs_int( $qv['attachment_id'] );
            if ( ( '' !== $qv['attachment'] ) || ! empty( $qv['attachment_id'] ) ) {
                $this->is_single     = true;
                $this->is_attachment = true;
            } elseif ( '' !== $qv['name'] ) $this->is_single = true;
            elseif ( $qv['p'] ) $this->is_single = true;
            elseif ( '' !== $qv['page_name'] || ! empty( $qv['page_id'] ) ) {
                $this->is_page   = true;
                $this->is_single = false;
            }else {
                if (isset($this->query['s'])) $this->is_search = true;
                if ('' !== $qv['second']) {
                    $this->is_time = true;
                    $this->is_date = true;
                }
                if ('' !== $qv['minute']) {
                    $this->is_time = true;
                    $this->is_date = true;
                }
                if ('' !== $qv['hour']) {
                    $this->is_time = true;
                    $this->is_date = true;
                }
                if ($qv['day'] && (!$this->is_date)) {
                    $date = sprintf('%04d-%02d-%02d', $qv['year'], $qv['monthnum'], $qv['day']);
                    if ($qv['monthnum'] && $qv['year'] && !$this->_tp_check_date($qv['monthnum'], $qv['day'], $qv['year'], $date))
                        $qv['error'] = NOT_FOUND;
                    else {
                        $this->is_day = true;
                        $this->is_date = true;
                    }
                }
                if ( $qv['monthnum'] && ( ! $this->is_date )) {
                    if ( 12 < $qv['monthnum'] ) $qv['error'] = '404';
                    else {
                        $this->is_month = true;
                        $this->is_date  = true;
                    }
                }
                if ( $qv['year'] && ( ! $this->is_date )) {
                    $this->is_year = true;
                    $this->is_date = true;
                }
                if ( $qv['m'] ) {
                    $this->is_date = true;
                    if ( strlen( $qv['m'] ) > 9 ) $this->is_time = true;
                    elseif ( strlen( $qv['m'] ) > 7 ) $this->is_day = true;
                    elseif ( strlen( $qv['m'] ) > 5 ) $this->is_month = true;
                    else  $this->is_year = true;
                }
                if ( $qv['w'] ) $this->is_date = true;
                $this->_query_vars_hash = false;
                $this->parse_tax_query( $qv );
                foreach ( $this->tax_query->queries as $tax_query ) {
                    if ( ! is_array( $tax_query ) ) continue;
                    if ( isset( $tax_query['operator'] ) && 'NOT IN' !== $tax_query['operator'] ) {
                        switch ( $tax_query['taxonomy'] ) {
                            case 'category':
                                $this->is_category = true;
                                break;
                            case 'post_tag':
                                $this->is_tag = true;
                                break;
                            default:
                                $this->is_tax = true;
                        }
                    }
                }
                if ( empty( $qv['author'] ) || ( '0' === $qv['author'] ) )
                    $this->is_author = false;
                else $this->is_author = true;
                if ( '' !== $qv['author_name'] ) $this->is_author = true;
                if (!empty( $qv['post_type'] ) && ! is_array( $qv['post_type'] ) ) {
                    $post_type_obj = $this->_get_post_type_object( $qv['post_type'] );
                    if (!empty($post_type_obj->has_archive )) $this->is_post_type_archive = true;
                }
                if ( $this->is_post_type_archive || $this->is_date || $this->is_author || $this->is_category || $this->is_tag || $this->is_tax )
                    $this->is_archive = true;
            }
            if ( '' !== $qv['feed'] ) $this->is_feed = true;
            if ( '' !== $qv['embed'] ) $this->is_embed = true;
            if ( '' !== $qv['tb'] ) $this->is_trackback = true;
            if ( '' !== $qv['paged'] && ( (int) $qv['paged'] > 1 ) ) $this->is_paged = true;
            if ( '' !== $qv['preview'] ) $this->is_preview = true;
            if ( $this->_is_admin()) $this->is_admin = true;
            if ( false !== strpos( $qv['feed'], 'comments-' ) ) {
                $qv['feed']         = str_replace( 'comments-', '', $qv['feed'] );
                $qv['with_comments'] = 1;
            }
            $this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;
            if ( $this->is_feed && ( ! empty( $qv['with_comments'] ) || ( empty( $qv['without_comments'] ) && $this->is_singular )))
                $this->is_comment_feed = true;
            if ( ! ( $this->is_singular || $this->is_archive || $this->is_search || $this->is_feed ||$this->is_trackback || $this->is_404|| $this->is_admin || $this->is_robots || $this->is_favicon || ( defined( 'REST_REQUEST' ) && REST_REQUEST && $this->is_main_query()))){
                $this->is_home = true;
            }
            if ( $this->is_home && 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' ) ) {
                $_query = $this->_tp_parse_args( $this->query );
                if ( isset( $_query['page_name'] ) && '' === $_query['page_name'] ) unset( $_query['page_name'] );
                unset( $_query['embed'] );
                if ( empty( $_query ) || ! array_diff( array_keys( $_query ), array( 'preview', 'page', 'paged', 'cpage' ) ) ) {
                    $this->is_page = true;
                    $this->is_home = false;
                    $qv['page_id'] = $this->_get_option( 'page_on_front' );
                    if ( ! empty( $qv['paged'] ) ){
                        $qv['page'] = $qv['paged'];
                        unset( $qv['paged'] );
                    }
                }
            }
            if ( '' !== $qv['page_name'] ) {
                $this->queried_object = $this->_get_page_by_path( $qv['page_name'] );
                $queried_object = null;
                if( $this->queried_object instanceof TP_Post ){
                    $queried_object = $this->queried_object;
                }
                if ( $queried_object && 'attachment' === $queried_object->post_type && ( preg_match( '/^[^%]*%(?:post_name)%/', $this->_get_option( 'permalink_structure' ) ) )) {
                    $post = $this->_get_page_by_path( $qv['page_name'], OBJECT, 'post' );
                    if ( $post ) {
                        $queried_object = $post;
                        $this->is_page        = false;
                        $this->is_single      = true;
                    }
                }
                if ( $queried_object !== null ) $this->queried_object_id = (int) $queried_object->ID;
                else unset( $queried_object );
                if ( isset( $this->queried_object_id ) && 'page' === $this->_get_option( 'show_on_front' ) &&  $this->_get_option( 'page_for_posts' ) === $this->queried_object_id ) {
                    $this->is_page       = false;
                    $this->is_home       = true;
                    $this->is_posts_page = true;
                }
                if ( isset( $this->queried_object_id ) && $this->_get_option( 'tp_page_for_privacy_policy' ) === $this->queried_object_id ) $this->is_privacy_policy = true;
            }
            if ( $qv['page_id'] ) {
                if ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_for_posts' ) === $qv['page_id'] ) {
                    $this->is_page       = false;
                    $this->is_home       = true;
                    $this->is_posts_page = true;
                }
                if ( $this->_get_option( 'tp_page_for_privacy_policy' ) === $qv['page_id'] )  $this->is_privacy_policy = true;
            }
            if ( ! empty( $qv['post_type'] ) ) {
                if ( is_array( $qv['post_type'])) $qv['post_type'] = array_map( 'sanitize_key', $qv['post_type'] );
                else $qv['post_type'] = $this->_sanitize_key( $qv['post_type'] );
            }
            if ( ! empty( $qv['post_status'] ) ) {
                if ( is_array( $qv['post_status'] ) ) $qv['post_status'] = array_map( 'sanitize_key', $qv['post_status'] );
                else $qv['post_status'] = preg_replace( '|[^a-z0-9_,-]|', '', $qv['post_status'] );
            }
            if ( $this->is_posts_page && ( ! isset( $qv['with_comments']) || ! $qv['with_comments'])) $this->is_comment_feed = false;
            $this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;
            if ( '404' === $qv['error']) $this->set_404();
            $this->is_embed = $this->is_embed && ( $this->is_singular || $this->is_404 );
            $this->_query_vars_hash    = md5( serialize( $this->query_vars ) );
            $this->_query_vars_changed = false;
            $this->_do_action_ref_array( 'parse_query', array( &$this ) );
        }//766
        /**
         * @description Parses various taxonomy related query vars.
         * @param $q
         */
        public function parse_tax_query( &$q ): void{
            if (!empty( $q['tax_query']) && is_array( $q['tax_query'])) $tax_query = $q['tax_query'];
            else $tax_query = array();
            if ( ! empty( $q['taxonomy'] ) && ! empty( $q['term'] ) )
                $tax_query[] = ['taxonomy' => $q['taxonomy'],'terms' => [ $q['term']],'field' => 'slug',];
            foreach ( $this->_get_taxonomies( array(), 'objects' ) as $taxonomy => $t ) {
                if ( 'post_tag' === $taxonomy ) continue;
                if ( $t->query_var && ! empty( $q[ $t->query_var ] ) ) {
                    $tax_query_defaults = ['taxonomy' => $taxonomy, 'field' => 'slug',];
                    if ( ! empty( $t->rewrite['hierarchical'] ) )
                        $q[ $t->query_var ] = $this->_tp_basename( $q[ $t->query_var ] );
                    $term = $q[ $t->query_var ];
                    if ( is_array( $term ) ) $term = implode( ',', $term );
                    if ( strpos( $term, '+' ) !== false ) {
                        $terms = preg_split( '/[+]+/', $term );
                        foreach ( $terms as $term ) $tax_query[] = array_merge( $tax_query_defaults,['terms' => [$term],]);
                    }else  $tax_query[] = array_merge( $tax_query_defaults,['terms' => preg_split( '/[,]+/', $term )]);
                }
            }
            if ( is_array( $q['cat'] ) ) $q['cat'] = implode( ',', $q['cat'] );
            if ( ! empty( $q['cat'] ) && ! $this->is_singular ) {
                $cat_in     = [];
                $cat_not_in = [];
                $cat_array = preg_split( '/[,\s]+/', urldecode( $q['cat'] ) );
                $cat_array = array_map( 'intval', $cat_array );
                $q['cat']  = implode( ',', $cat_array );
                foreach ( $cat_array as $cat ) {
                    if ( $cat > 0 ) $cat_in[] = $cat;
                    elseif ( $cat < 0 ) $cat_not_in[] = abs( $cat );
                }
                if ( ! empty( $cat_in)) $tax_query[] = ['taxonomy' => 'category', 'terms' => $cat_in, 'field' => 'term_id', 'include_children' => true,];
                if ( ! empty( $cat_not_in)) $tax_query[] = ['taxonomy' => 'category','terms' => $cat_not_in,'field' => 'term_id','operator' => 'NOT IN','include_children' => true,];
                unset( $cat_array, $cat_in, $cat_not_in );
            }
            if ( ! empty( $q['category__and'] ) && 1 === count( (array) $q['category__and'] ) ) {
                $q['category__and'] = (array) $q['category__and'];
                if ( ! isset( $q['category__in'] ) ) $q['category__in'] = [];
                $q['category__in'][] = $this->_abs_int( reset( $q['category__and'] ) );
                unset( $q['category__and'] );
            }
            if ( ! empty( $q['category__in'] ) ) {
                $q['category__in'] = array_map( 'absint', array_unique( $q['category__in'] ) );
                $tax_query[] = ['taxonomy' => 'category','terms' => $q['category__in'], 'field' => 'term_id','include_children' => false,];
            }
            if ( ! empty( $q['category__not_in'] ) ) {
                $q['category__not_in'] = array_map( 'absint', array_unique( (array) $q['category__not_in'] ) );
                $tax_query[] = ['taxonomy' => 'category', 'terms' => $q['category__not_in'], 'operator' => 'NOT IN', 'include_children' => false,];
            }
            if ( is_array($q['tag'])) $q['tag'] = implode( ',', $q['tag'] );
            if ( '' !== $q['tag'] && ! $this->is_singular && $this->_query_vars_changed ) {
                if ( strpos( $q['tag'], ',' ) !== false ) {
                    $tags = preg_split( '/[,\r\n\t ]+/', $q['tag'] );
                    foreach ($tags as $tag ) {
                        $tag                 = $this->_sanitize_term_field( 'slug', $tag, 0, 'post_tag', 'db' );
                        $q['tag_slug__in'][] = $tag;
                    }
                } elseif (! empty( $q['cat'] ) || preg_match( '/[+\r\n\t ]+/', $q['tag'] )) {
                    $tags = preg_split( '/[+\r\n\t ]+/', $q['tag'] );
                    foreach ($tags as $tag ) {
                        $tag = $this->_sanitize_term_field( 'slug', $tag, 0, 'post_tag', 'db' );
                        $q['tag_slug__and'][] = $tag;
                    }
                } else {
                    $q['tag'] = $this->_sanitize_term_field( 'slug', $q['tag'], 0, 'post_tag', 'db' );
                    $q['tag_slug__in'][] = $q['tag'];
                }
            }
            if ( ! empty( $q['tag_id'] ) ) {
                $q['tag_id'] = $this->_abs_int( $q['tag_id'] );
                $tax_query[] = ['taxonomy' => 'post_tag','terms' => $q['tag_id'],];
            }
            if ( ! empty( $q['tag__in'] ) ) {
                $q['tag__in'] = array_map( 'absint', array_unique( (array) $q['tag__in'] ) );
                $tax_query[]  = ['taxonomy' => 'post_tag','terms' => $q['tag__in'],];
            }
            if ( ! empty( $q['tag__not_in'] ) ) {
                $q['tag__not_in'] = array_map( 'absint', array_unique( (array) $q['tag__not_in'] ) );
                $tax_query[]= [ 'taxonomy' => 'post_tag','terms' => $q['tag__not_in'],'operator' => 'NOT IN',];
            }
            if ( ! empty( $q['tag__and'] ) ) {
                $q['tag__and'] = array_map( 'absint', array_unique( (array) $q['tag__and'] ) );
                $tax_query[]   = ['taxonomy' => 'post_tag','terms' => $q['tag__and'],'operator' => 'AND',];
            }
            if ( ! empty( $q['tag_slug__in'] ) ) {
                $q['tag_slug__in'] = array_map( 'sanitize_title_for_query', array_unique($q['tag_slug__in'] ) );
                $tax_query[]= ['taxonomy' => 'post_tag','terms' => $q['tag_slug__in'],'field' => 'slug',];
            }
            if ( ! empty( $q['tag_slug__and'] ) ) {
                $q['tag_slug__and'] = array_map( 'sanitize_title_for_query', array_unique( (array) $q['tag_slug__and'] ) );
                $tax_query[] = [ 'taxonomy' => 'post_tag','terms' => $q['tag_slug__and'],'field' => 'slug','operator' => 'AND',];
            }
            $this->tax_query = new TP_Tax_Query( $tax_query );
            $this->_do_action( 'parse_tax_query', $this );
        }//1115
        /**
         * @param $q
         * @return string
         */
        protected function _parse_search( &$q ): string{
            $tpdb = $this->_init_db();
            $search = '';
            $q['s'] = stripslashes( $q['s'] );
            if (empty( $_GET['s'] )&& $this->is_main_query()) $q['s'] = urldecode( $q['s'] );
            $q['s'] = str_replace( array( "\r", "\n" ), '', $q['s'] );
            $q['search_terms_count'] = 1;
            if ( ! empty( $q['sentence'] ) ) $q['search_terms'] = array( $q['s'] );
            elseif ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
                $q['search_terms_count'] = count( $matches[0] );
                $q['search_terms']       = $this->_parse_search_terms( $matches[0] );
                if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 )
                    $q['search_terms'] = array( $q['s'] );
            } else  $q['search_terms'] = array( $q['s'] );
            $n = ! empty( $q['exact'] ) ? '' : '%';
            $search_and = '';
            $q['search_orderby_title'] =[];
            $exclusion_prefix = $this->_apply_filters( 'tp_query_search_exclusion_prefix', '-' );
            foreach ( $q['search_terms'] as $term ) {
                $exclude = $exclusion_prefix && ( strpos( $term, $exclusion_prefix ) === 0 );
                if ( $exclude ) {
                    $like_op  = 'NOT LIKE';
                    $and_or_op = 'AND';
                    $term     = substr( $term, 1 );
                } else {
                    $like_op  = 'LIKE';
                    $and_or_op = 'OR';
                }
                if ( $n && ! $exclude ) {
                    $like = '%' . $tpdb->esc_like( $term ) . '%';
                    $q['search_orderby_title'][] = $tpdb->prepare( "{$tpdb->posts}.post_title LIKE %s", $like );
                }
                $like      = $n . $tpdb->esc_like( $term ) . $n;
                $search   .= $tpdb->prepare( "{$search_and}(({$tpdb->posts}.post_title $like_op %s) $and_or_op ({$tpdb->posts}.post_excerpt $like_op %s) $and_or_op ({$tpdb->posts}.post_content $like_op %s))", $like, $like, $like );
                $search_and = ' AND ';
            }
            if ( ! empty( $search ) ) {
                $search = " AND ({$search}) ";
                if ( ! $this->_is_user_logged_in() ) {
                    $search .= " AND ({$tpdb->posts}.post_password = '') ";
                }
            }
            return $search;
        }//1357
        /**
         * @description Check if the terms are suitable for searching.
         * @param $terms
         * @return array
         */
        protected function _parse_search_terms( $terms ): array{
            $strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
            $checked    = [];
            $stop_words = $this->_get_search_stop_words();
            foreach ( $terms as $term ) {
                if ( preg_match( '/^".+"$/', $term ) )
                    $term = trim( $term, "\"'" );
                else $term = trim( $term, "\"' " );
                // Avoid single A-Z and single dashes.
                if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) )
                    continue;
                if ( in_array( call_user_func( $strtolower($term)), $stop_words, true ) )
                    continue;
                $checked[] = $term;
            }
            return $checked;
        }//1443
        /**
         * @description Retrieve stop-words used when parsing search terms.
         * @return mixed
         */
        protected function _get_search_stop_words(){
            if ( isset($this->stopwords )) return $this->stopwords;
            $words = explode(
                ',',
                $this->_x(
                    'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
                    'Comma-separated list of search stop-words in your language'
                )
            );
            $stop_words = array();
            foreach ( $words as $word ) {
                $word = trim( $word, "\r\n\t " );
                if ( $word ) $stop_words[] = $word;
            }
            $this->stopwords = $this->_apply_filters( 'tp_search_stopwords', $stop_words );
            return $this->stopwords;
        }//1479
        /**
         * @description* Generates SQL for the ORDER BY condition based on passed search terms.
         * @param $q
         * @return string
         */
        protected function _parse_search_order( &$q ): string{
            $tpdb = $this->_init_db();
            if ( $q['search_terms_count'] > 1 ) {
                $num_terms = count( $q['search_orderby_title'] );
                $like = '';
                if ( ! preg_match( '/(?:\s|^)\-/', $q['s'] ) )
                    $like = '%' . $tpdb->esc_like( $q['s'] ) . '%';
                $search_orderby = '';
                // Sentence match in 'post_title'.
                if ( $like ) $search_orderby .= $tpdb->prepare( "WHEN {$tpdb->posts}.post_title LIKE %s THEN 1 ", $like );
                if ( $num_terms < 7 ) {
                    // All words in title.
                    $search_orderby .= 'WHEN ' . implode( ' AND ', $q['search_orderby_title'] ) . ' THEN 2 ';
                    // Any word in title, not needed when $num_terms == 1.
                    if ( $num_terms > 1 ) $search_orderby .= 'WHEN ' . implode( ' OR ', $q['search_orderby_title'] ) . ' THEN 3 ';
                }
                if ( $like ) {
                    $search_orderby .= $tpdb->prepare( "WHEN {$tpdb->posts}.post_excerpt LIKE %s THEN 4 ", $like );
                    $search_orderby .= $tpdb->prepare( "WHEN {$tpdb->posts}.post_content LIKE %s THEN 5 ", $like );
                }
                if ( $search_orderby ) $search_orderby = '(CASE ' . $search_orderby . 'ELSE 6 END)';
            }else $search_orderby = reset( $q['search_orderby_title'] ) . ' DESC';
            return $search_orderby;
        }//1526
        /**
         * @description  Converts the given orderby alias (if allowed) to a properly-prefixed value.
         * @param $orderby
         * @return bool|string
         */
        protected function _parse_orderby( $orderby ){
            $tpdb = $this->_init_db();
            $meta_query = $this->_init_meta_query();
            $allowed_keys = [
                'post_name','post_author','post_date','post_title','post_modified','post_parent',
                'post_type','name','author','date','title','modified','parent','type','ID',
                'menu_order','comment_count','rand','post__in','post_parent__in','post_name__in',
            ];
            $primary_meta_key   = '';
            $primary_meta_query = false;
            $meta_clauses       = $meta_query->get_clauses();
            if ( ! empty( $meta_clauses ) ) {
                $primary_meta_query = reset( $meta_clauses );
                if ( ! empty( $primary_meta_query['key'] ) ) {
                    $primary_meta_key = $primary_meta_query['key'];
                    $allowed_keys[]   = $primary_meta_key;
                }
                $allowed_keys[] = 'meta_value';
                $allowed_keys[] = 'meta_value_num';
                $allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
            }
            $rand_with_seed = false;
            if ( preg_match( '/RAND\((\d +)\)/i', $orderby, $matches ) ) {
                $orderby        = sprintf( 'RAND(%s)', (int) $matches[1] );
                $allowed_keys[] = $orderby;
                $rand_with_seed = true;
            }
            if ( ! in_array( $orderby, $allowed_keys, true ) ) return false;
            $orderby_clause = '';
            switch ( $orderby ) {
                case 'post_name':
                case 'post_author':
                case 'post_date':
                case 'post_title':
                case 'post_modified':
                case 'post_parent':
                case 'post_type':
                case 'ID':
                case 'menu_order':
                case 'comment_count':
                    $orderby_clause = "{$tpdb->posts}.{$orderby}";
                    break;
                case 'rand':
                    $orderby_clause = 'RAND()';
                    break;
                case $primary_meta_key:
                case 'meta_value':
                    if ( ! empty( $primary_meta_query['type'] ) )
                        $orderby_clause = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
                    else $orderby_clause = "{$primary_meta_query['alias']}.meta_value";
                    break;
                case 'meta_value_num':
                    $orderby_clause = "{$primary_meta_query['alias']}.meta_value+0";
                    break;
                case 'post__in':
                    if ( ! empty( $this->query_vars['post__in'] ) )
                        $orderby_clause = "FIELD({$tpdb->posts}.ID," . implode( ',', array_map( 'absint', $this->query_vars['post__in'] ) ) . ')';
                    break;
                case 'post_parent__in':
                    if ( ! empty( $this->query_vars['post_parent__in'] ) )
                        $orderby_clause = "FIELD( {$tpdb->posts}.post_parent," . implode( ', ', array_map( 'absint', $this->query_vars['post_parent__in'] ) ) . ' )';
                    break;
                case 'post_name__in':
                    if ( ! empty( $this->query_vars['post_name__in'] ) ) {
                        $post_name__in        = array_map( 'sanitize_title_for_query', $this->query_vars['post_name__in'] );
                        $post_name__in_string = "'" . implode( "','", $post_name__in ) . "'";
                        $orderby_clause       = "FIELD( {$tpdb->posts}.post_name," . $post_name__in_string . ' )';
                    }
                    break;
                default:
                    if ( array_key_exists( $orderby, $meta_clauses ) ) {
                        // $orderby corresponds to a meta_query clause.
                        $meta_clause    = $meta_clauses[ $orderby ];
                        $orderby_clause = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
                    } elseif ( $rand_with_seed )
                        $orderby_clause = $orderby;
                    else  $orderby_clause = "{$tpdb->posts}.post_" . $this->_sanitize_key( $orderby );
                    break;
            }
            return $orderby_clause;
        }//1583
        /**
         * @description Parse an 'order' query variable and cast it to ASC or DESC as necessary.
         * @param $order
         * @return string
         */
        protected function _parse_order( $order ): ?string{
            if ( ! is_string( $order ) || empty( $order ) ) return 'DESC';
            if ( 'ASC' === strtoupper( $order ) ) return 'ASC';
            else return 'DESC';
        }//1711
        /**
         * @description Sets the 404 property and saves whether query is feed.
         */
        public function set_404(): void{
            $is_feed = $this->is_feed;
            $this->__init_query_flags();
            $this->is_404 = true;
            $this->is_feed = $is_feed;
            $this->_do_action_ref_array( 'set_404', array( $this ) );
        }//1728
        /**
         * @description Retrieves the value of a query variable.
         * @param $query_var
         * @param string $default
         * @return string
         */
        public function get( $query_var, $default = '' ): string{
            if ( isset( $this->query_vars[ $query_var ] ) ) return $this->query_vars[ $query_var ];
            return $default;
        }//1756
        /**
         * @description Sets the value of a query variable.
         * @param $query_var
         * @param $value
         */
        public function set( $query_var, $value ): void{
            $this->query_vars[ $query_var ] = $value;
        }//1772
        /**
         * @description Retrieves an array of posts based on query variables.
         * @return array|mixed|null
         */
        public function get_posts(){
            $meta_query = $this->_init_meta_query();
            $tpdb = $this->_init_db();
            static $post_type_cap;
            $this->parse_query();
            $this->_do_action_ref_array( 'pre_get_posts', array( &$this ) );
            $q = &$this->query_vars;
            $meta_query->parse_query_vars( $q );
            $hash = md5( serialize( $this->query_vars ) );
            if ( $hash !== $this->_query_vars_hash ) {
                $this->_query_vars_changed = true;
                $this->_query_vars_hash    = $hash;
            }
            unset( $hash );
            $distinct         = '';
            $which_author     = '';
            $which_mime_type  = '';
            $where            = '';
            $limits           = '';
            $join             = '';
            $search           = '';
            $groupby          = '';
            $post_status_join = false;
            $page             = 1;
            if (!isset( $q['ignore_sticky_posts'])) $q['ignore_sticky_posts'] = false;
            if (!isset( $q['suppress_filters'])) $q['suppress_filters'] = false;
            if ( ! isset( $q['cache_results'] ) ) {
                if ( $this->_tp_using_ext_object_cache() ) $q['cache_results'] = false;
                else $q['cache_results'] = true;
            }
            if (!isset( $q['update_post_term_cache'])) $q['update_post_term_cache'] = true;
            if (!isset( $q['lazy_load_term_meta'])) $q['lazy_load_term_meta'] = $q['update_post_term_cache'];
            if (!isset( $q['update_post_meta_cache'])) $q['update_post_meta_cache'] = true;
            if ( ! isset( $q['post_type'] ) ) {
                if ( $this->is_search ) $q['post_type'] = 'any';
                else $q['post_type'] = '';
            }
            $post_type = $q['post_type'];
            if ( empty( $q['posts_per_page'] ) ) $q['posts_per_page'] = $this->_get_option( 'posts_per_page' );
            if ( isset( $q['show_posts'] ) && $q['show_posts'] ) {
                $q['show_posts']      = (int) $q['show_posts'];
                $q['posts_per_page'] = $q['show_posts'];
            }
            if ( ( isset( $q['posts_per_archive_page'] ) && 0 !== $q['posts_per_archive_page'] ) && ( $this->is_archive || $this->is_search ) )
                $q['posts_per_page'] = $q['posts_per_archive_page'];
            if ( ! isset( $q['no_paging'] ) ) {
                if ( -1 === $q['posts_per_page'] ) $q['no_paging'] = true;
                else $q['no_paging'] = false;
            }
            if ( $this->is_feed ) {
                if ( ! empty( $q['posts_per_rss'] ) ) $q['posts_per_page'] = $q['posts_per_rss'];
                else $q['posts_per_page'] = $this->_get_option( 'posts_per_rss' );
                $q['no_paging'] = false;
            }
            $q['posts_per_page'] = (int) $q['posts_per_page'];
            if ( $q['posts_per_page'] < -1 ) $q['posts_per_page'] = abs( $q['posts_per_page'] );
            elseif ( 0 === $q['posts_per_page'] ) $q['posts_per_page'] = 1;
            if ( ! isset( $q['comments_per_page'] ) || 0 === $q['comments_per_page'] )
                $q['comments_per_page'] = $this->_get_option( 'comments_per_page' );
            if ( $this->is_home && ( empty( $this->query ) || 'true' === $q['preview'] ) && ( 'page' === $this->_get_option( 'show_on_front' ) ) && $this->_get_option( 'page_on_front' ) ) {
                $this->is_page = true;
                $this->is_home = false;
                $q['page_id']  = $this->_get_option( 'page_on_front' );
            }
            if ( isset( $q['page'] ) ) {
                $q['page'] = trim( $q['page'], '/' );
                $q['page'] = $this->_abs_int( $q['page'] );
            }
            if ( isset( $q['no_found_rows'] ) ) $q['no_found_rows'] = (bool) $q['no_found_rows'];
            else $q['no_found_rows'] = false;
            switch ( $q['fields'] ) {
                case 'ids':
                    $fields = "{$tpdb->posts}.ID";
                    break;
                case 'id=>parent':
                    $fields = "{$tpdb->posts}.ID, {$tpdb->posts}.post_parent";
                    break;
                default:
                    $fields = "{$tpdb->posts}.*";
            }
            if ( '' !== $q['menu_order'] )
                $where .= " AND {$tpdb->posts}.menu_order = " . $q['menu_order'];
            if ( $q['dt'] ) {
                $where .= " AND YEAR({$tpdb->posts}.post_date)=" . substr( $q['dt'], 0, 4 );
                if ( strlen( $q['dt'] ) > 5 )
                    $where .= " AND MONTH({$tpdb->posts}.post_date)=" . substr( $q['dt'], 4, 2 );
                if ( strlen( $q['dt'] ) > 7 )
                    $where .= " AND DAYOFMONTH({$tpdb->posts}.post_date)=" . substr( $q['dt'], 6, 2 );
                if ( strlen( $q['dt'] ) > 9 )
                    $where .= " AND HOUR({$tpdb->posts}.post_date)=" . substr( $q['dt'], 8, 2 );
                if ( strlen( $q['dt'] ) > 11 )
                    $where .= " AND MINUTE({$tpdb->posts}.post_date)=" . substr( $q['dt'], 10, 2 );
                if ( strlen( $q['dt'] ) > 13 )
                    $where .= " AND SECOND({$tpdb->posts}.post_date)=" . substr( $q['dt'], 12, 2 );
            }
            $date_parameters = [];
            if ( '' !== $q['hour'] )  $date_parameters['hour'] = $q['hour'];
            if ( '' !== $q['minute'] ) $date_parameters['minute'] = $q['minute'];
            if ( '' !== $q['second'] ) $date_parameters['second'] = $q['second'];
            if ( $q['year'] )  $date_parameters['year'] = $q['year'];
            if ( $q['monthnum'] ) $date_parameters['monthnum'] = $q['monthnum'];
            if ( $q['w'] ) $date_parameters['week'] = $q['w'];
            if ( $q['day'] ) $date_parameters['day'] = $q['day'];
            if ( $date_parameters ) {
                $date_query = new TP_Date_Query( array( $date_parameters ) );//todo
                $where     .= $date_query->get_sql();
            }
            unset( $date_parameters, $date_query );
            if ( ! empty( $q['date_query'] ) ) {
                $date_query = $this->_init_date_query($q['date_query']);
                $where           .= $date_query->get_sql();
            }
            if ( ! empty( $q['post_type'] ) && 'any' !== $q['post_type'] ) {
                foreach ( (array) $q['post_type'] as $_post_type ) {
                    $p_type_obj = (array)$this->_get_post_type_object( $_post_type );//$p_type_obj->query_var
                    if ( ! $p_type_obj || ! $p_type_obj['query_var'] || empty( $q[ $p_type_obj['query_var'] ]))
                        continue;
                    if ( ! $p_type_obj['hierarchical'] )
                        $q['name'] = $q[ $p_type_obj['query_var'] ];
                    else {
                        $q['page_name'] = $q[ $p_type_obj['query_var'] ];
                        $q['name']     = '';
                    }
                    break;
                } // End foreach.
                unset( $p_type_obj );
            }
            $re_queried_page = null;
            if ( '' !== $q['title'] )
                $where .= $tpdb->prepare( " AND {$tpdb->posts}.post_title = %s", stripslashes( $q['title'] ) );
            if ( '' !== $q['name'] ) {
                $q['name'] = $this->_sanitize_title_for_query( $q['name'] );
                $where    .= " AND {$tpdb->posts}.post_name = '" . $q['name'] . "'";
            }elseif ( '' !== $q['page_name'] ) {
                if ( isset( $this->queried_object_id ) )
                    $re_queried_page = $this->queried_object_id;
                else{
                    if ( 'page' !== $q['post_type'] ) {
                        foreach ( (array) $q['post_type'] as $_post_type ) {
                            $p_type_obj = $this->_get_post_type_object( $_post_type );
                            if ( ! $p_type_obj || ! $p_type_obj['hierarchical'] ) continue;
                            $re_queried_page = $this->_get_page_by_path( $q['page_name'], OBJECT, $_post_type );
                            if ( $re_queried_page ) break;
                        }
                        unset( $p_type_obj );
                    }else $re_queried_page = $this->_get_page_by_path( $q['page_name'] );
                    if ( ! empty( $re_queried_page )&& $re_queried_page instanceof TP_Post )
                        $re_queried_page = $re_queried_page->ID;
                    else $re_queried_page = 0;
                }
                $page_for_posts = $this->_get_option( 'page_for_posts' );
                if ( empty( $page_for_posts ) || ( $re_queried_page !== $page_for_posts ) || ( 'page' !== $this->_get_option( 'show_on_front' ) ) ) {
                    $q['page_name'] = $this->_sanitize_title_for_query( $this->_tp_basename( $q['page_name'] ) );
                    $q['name']     = $q['page_name'];
                    $where        .= " AND ({$tpdb->posts}.ID = '$re_queried_page')";
                    $req_page_obj   = $this->_get_post( $re_queried_page );
                    if (($req_page_obj  instanceof \stdClass ) && is_object( $req_page_obj ) && 'attachment' === $req_page_obj->post_type ) {
                        $this->is_attachment = true; //todo replace stdClass for the real dependency
                        $post_type           = 'attachment';
                        $q['post_type']      = 'attachment';
                        $this->is_page       = true;
                        $q['attachment_id']  = $re_queried_page;
                    }
                }
            }elseif ( '' !== $q['attachment'] ) {
                $q['attachment'] = $this->_sanitize_title_for_query( $this->_tp_basename( $q['attachment'] ) );
                $q['name']       = $q['attachment'];
                $where          .= " AND {$tpdb->posts}.post_name = '" . $q['attachment'] . "'";
            }elseif ( is_array( $q['post_name__in'] ) && ! empty( $q['post_name__in'] ) ) {
                $q['post_name__in'] = array_map( 'sanitize_title_for_query', $q['post_name__in'] );
                $post_name__in      = "'" . implode( "','", $q['post_name__in'] ) . "'";
                $where             .= " AND {$tpdb->posts}.post_name IN ($post_name__in)";
            }
            if ( $q['attachment_id'] ) $q['p'] = $this->_abs_int( $q['attachment_id'] );
            if ( $q['p'] )
                $where .= " AND {$tpdb->posts}.ID = " . $q['p'];
            elseif ( $q['post__in'] ) {
                $post__in = implode( ',', array_map( 'absint', $q['post__in'] ) );
                $where   .= " AND {$tpdb->posts}.ID IN ($post__in)";
            } elseif ( $q['post__not_in'] ) {
                $post__not_in = implode( ',', array_map( 'absint', $q['post__not_in'] ) );
                $where       .= " AND {$tpdb->posts}.ID NOT IN ($post__not_in)";
            }
            if ( is_numeric( $q['post_parent'] ) ) {
                $where .= $tpdb->prepare( " AND {$tpdb->posts}.post_parent = %d ", $q['post_parent'] );
            } elseif ( $q['post_parent__in'] ) {
                $post_parent__in = implode( ',', array_map( 'absint', $q['post_parent__in'] ) );
                $where          .= " AND {$tpdb->posts}.post_parent IN ($post_parent__in)";
            } elseif ( $q['post_parent__not_in'] ) {
                $post_parent__not_in = implode( ',', array_map( 'absint', $q['post_parent__not_in'] ) );
                $where              .= " AND {$tpdb->posts}.post_parent NOT IN ($post_parent__not_in)";
            }
            if ( $q['page_id'] ) {
                if ( ( 'page' !== $this->_get_option( 'show_on_front' ) ) || ( $this->_get_option( 'page_for_posts' ) !== $q['page_id'] ) ) {
                    $q['p'] = $q['page_id'];
                    $where  = " AND {$tpdb->posts}.ID = " . $q['page_id'];
                }
            }
            if ( $q['s'] !== '') $search = $this->_parse_search( $q );
            if ( ! $q['suppress_filters'] )
                $search = $this->_apply_filters_ref_array( 'posts_search', array( $search, &$this ) );
            // Taxonomies.
            if ( ! $this->is_singular ) {
                $this->parse_tax_query( $q );
                $clauses = $this->tax_query->this->get_sql( $tpdb->posts, 'ID' );
                $join  .= $clauses['join'];
                $where .= $clauses['where'];
            }
            if ( $this->is_tax ) {
                if ( empty( $post_type ) ) {
                    $post_type  = [];
                    $taxonomies = array_keys( $this->tax_query->queried_terms );
                    foreach ( $this->_get_post_types( array( 'exclude_from_search' => false ) ) as $pt ) {
                        $object_taxonomies = 'attachment' === $pt ? $this->_get_taxonomies_for_attachments() : $this->_get_object_taxonomies( $pt );
                        if ( array_intersect( $taxonomies, $object_taxonomies ) ) $post_type[$pt];
                    }
                    if ( ! $post_type ) $post_type = 'any';
                    elseif ( count( $post_type ) === 1 ) $post_type = $post_type[0];
                    $post_status_join = true;
                }elseif ( in_array( 'attachment', (array) $post_type, true ) ) $post_status_join = true;
            }//is_tax
            if ( ! empty( $this->tax_query->queried_terms ) ) {
                if ( ! isset( $q['taxonomy'] ) ) {
                    foreach ( $this->tax_query->queried_terms as $queried_taxonomy => $queried_items ) {
                        if ( empty( $queried_items['terms'][0] ) ) continue;
                        if ( ! in_array( $queried_taxonomy, array( 'category', 'post_tag' ), true ) ) {
                            $q['taxonomy'] = $queried_taxonomy;
                            if ( 'slug' === $queried_items['field'] ) $q['term'] = $queried_items['terms'][0];
                            else $q['term_id'] = $queried_items['terms'][0];
                            // Take the first one we find.
                            break;
                        }
                    }
                }//taxonomy
                // 'cat', 'category_name', 'tag_id'.
                foreach ( $this->tax_query->queried_terms as $queried_taxonomy => $queried_items ) {
                    if ( empty( $queried_items['terms'][0] ) ) continue;
                    if ( 'category' === $queried_taxonomy ) {
                        $the_cat = $this->_get_term_by($queried_items['field'], $queried_items['terms'][0], 'category');
                        if ($the_cat) {
                            $this->set('cat', $the_cat['term_id']);
                            $this->set('category_name', $the_cat['slug']);
                        }
                        unset($the_cat);
                        if ('post_tag' === $queried_taxonomy) {
                            $the_tag = $this->_get_term_by($queried_items['field'], $queried_items['terms'][0], 'post_tag');
                            if ($the_tag) $this->set('tag_id', $the_tag['term_id']);
                            unset($the_tag);
                        }
                    }
                }
            }//queried_terms
            if ( ! empty( $this->tax_query->queries ) || ! empty( $meta_query->queries ) )
                $groupby = "{$tpdb->posts}.ID";
            // Author/user stuff.
            if ( ! empty( $q['author'] ) && '0' !== $q['author'] ) {
                $q['author'] = $this->_add_slashes_gpc( '' . urldecode( $q['author'] ) );
                $authors     = array_unique( array_map( 'intval', preg_split( '/[,\s]+/', $q['author'] ) ) );
                foreach ( $authors as $author ) {
                    $key         = $author > 0 ? 'author__in' : 'author__not_in';
                    $q[ $key ][] = abs( $author );
                }
                $q['author'] = implode( ',', $authors );
            }
            if ( ! empty( $q['author__not_in'] ) ) {
                $author__not_in = implode( ',', array_map( 'absint', array_unique( (array) $q['author__not_in'] ) ) );
                $where         .= " AND {$tpdb->posts}.post_author NOT IN ($author__not_in) ";
            } elseif ( ! empty( $q['author__in'] ) ) {
                $author__in = implode( ',', array_map( 'absint', array_unique( (array) $q['author__in'] ) ) );
                $where     .= " AND {$tpdb->posts}.post_author IN ($author__in) ";
            }
            // Author stuff for nice URLs.
            if ( '' !== $q['author_name'] ) {
                if ( strpos( $q['author_name'], '/' ) !== false ) {
                    $q['author_name'] = explode( '/', $q['author_name'] );
                    if ( $q['author_name'][ count( $q['author_name'] ) - 1 ] ) {
                        $q['author_name'] = $q['author_name'][ count( $q['author_name'] ) - 1 ]; // No trailing slash.
                    } else {
                        $q['author_name'] = $q['author_name'][ count( $q['author_name'] ) - 2 ]; // There was a trailing slash.
                    }
                }
                $q['author_name'] = $this->_sanitize_title_for_query( $q['author_name'] );
                $q['author']      = $this->_get_user_by( 'slug', $q['author_name'] );
                if ( $q['author'] ) $q['author'] = $q['author']->ID;
                $which_author .= " AND ({$tpdb->posts}.post_author = " . $this->_abs_int( $q['author'] ) . ')';
            }
            // Matching by comment count.
            if ( isset( $q['comment_count'] ) ) {
                // Numeric comment count is converted to array format.
                if ( is_numeric( $q['comment_count'] ) )
                    $q['comment_count'] = array('value' => (int) $q['comment_count'],);
                if ( isset( $q['comment_count']['value'] ) ) {
                    $q['comment_count'] = array_merge( array('compare' => '=',), $q['comment_count']);
                    // Fallback for invalid compare operators is '='.
                    $compare_operators = array( '=', '!=', '>', '>=', '<', '<=' );
                    if ( ! in_array( $q['comment_count']['compare'], $compare_operators, true ) )
                        $q['comment_count']['compare'] = '=';
                    $where .= $tpdb->prepare( " AND {$tpdb->posts}.comment_count {$q['comment_count']['compare']} %d", $q['comment_count']['value'] );
                }
            }
            // MIME-Type stuff for attachment browsing.
            if ( isset( $q['post_mime_type'] ) && '' !== $q['post_mime_type'] )
                $which_mime_type = $this->_tp_post_mime_type_where( $q['post_mime_type'], $tpdb->posts );
            $where .= $search . $which_author . $which_mime_type;
            if ( ! empty( $meta_query->queries ) ) {
                $clauses = $meta_query->get_sql( 'post', $tpdb->posts, 'ID', $this );
                $join   .= $clauses['join'];
                $where  .= $clauses['where'];
            }
            $rand = ( isset( $q['orderby'] ) && 'rand' === $q['orderby'] );
            if ( ! isset( $q['order'] ) ) $q['order'] = $rand ? '' : 'DESC';
            else  $q['order'] = $rand ? '' : $this->_parse_order( $q['order'] );
            $force_asc = array( 'post__in', 'post_name__in', 'post_parent__in' );
            if ( isset( $q['orderby'] ) && in_array( $q['orderby'], $force_asc, true ) ) $q['order'] = '';
            // Order by
            if ( empty( $q['orderby'] ) ) {
                if ( isset( $q['orderby'] ) && ( is_array( $q['orderby'] ) || false === $q['orderby'] ) )
                    $orderby = '';
                else $orderby = "{$tpdb->posts}.post_date " . $q['order'];
            }elseif ( 'none' === $q['order_by'] ) $orderby = '';
            else{
                $orderby_array = [];
                if ( is_array( $q['orderby'] ) ) {
                    foreach ( $q['orderby'] as $_orderby => $order ) {
                        $orderby = $this->_add_slashes_gpc( urldecode( $_orderby ) );
                        $parsed  = $this->_parse_orderby( $orderby );
                        if ( ! $parsed ) continue;
                        $orderby_array[] = $parsed . ' ' . $this->_parse_order( $order );
                    }
                    $orderby = implode( ', ', $orderby_array );
                }else{
                    $q['orderby'] = urldecode( $q['order_by'] );
                    $q['orderby'] = $this->_add_slashes_gpc( $q['orderby'] );
                    foreach ( explode( ' ', $q['order_by'] ) as $i => $orderby ) {
                        $parsed = $this->_parse_orderby( $orderby );
                        if ( ! $parsed ) continue;
                        $orderby_array[] = $parsed;
                    }
                    $orderby = implode( ' ' . $q['order'] . ', ', $orderby_array );
                    if ( empty( $orderby ) ) $orderby = "{$tpdb->posts}.post_date " . $q['order'];
                    elseif ( ! empty( $q['order'] ) ) $orderby .= " {$q['order']}";
                }
            }
            if ( ! empty( $q['s'] ) ) {
                $search_orderby = '';
                if ( (! empty( $q['search_orderby_title'] ) && ( empty( $q['orderby'] ) && ! $this->is_feed )) || ( isset( $q['orderby'] ) && 'relevance' === $q['orderby'] ) )
                    $search_orderby = $this->_parse_search_order( $q );
                if ( ! $q['suppress_filters'] )
                    $search_orderby = $this->_apply_filters( 'posts_search_orderby', $search_orderby, $this );
                if ( $search_orderby )  $orderby = $orderby ? $search_orderby . ', ' . $orderby : $search_orderby;
            }
            if ( is_array( $post_type ) && count( $post_type ) > 1 ) $post_type_cap = 'multiple_post_type';
            else {
                if ( is_array( $post_type ) ) $post_type = reset( $post_type );
                $post_type_object = $this->_get_post_type_object( $post_type );
                if ( empty( $post_type_object ) ) $post_type_cap = $post_type;
            }
            if ( isset( $q['post_password'] ) ) {
                $where .= $tpdb->prepare( " AND {$tpdb->posts}.post_password = %s", $q['post_password'] );
                if ( empty( $q['perm'] ) ) $q['perm'] = 'readable';
            } elseif ( isset( $q['has_password'] ) )
                $where .= sprintf( " AND {$tpdb->posts}.post_password %s ''", $q['has_password'] ? '!=' : '=' );
            if ( ! empty( $q['comment_status'] ) )
                $where .= $tpdb->prepare( " AND {$tpdb->posts}.comment_status = %s ", $q['comment_status'] );
            if ( ! empty( $q['ping_status'] ) )
                $where .= $tpdb->prepare( " AND {$tpdb->posts}.ping_status = %s ", $q['ping_status'] );
            $skip_post_status = false;
            if ( 'any' === $post_type ) {
                $in_search_post_types = $this->_get_post_types( array( 'exclude_from_search' => false ) );
                if ( empty( $in_search_post_types ) ) {
                    $post_type_where  = ' AND 1=0 ';
                    $skip_post_status = true;
                } else  $post_type_where = " AND {$tpdb->posts}.post_type IN ('" . implode( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";
            }elseif ( ! empty( $post_type ) && is_array( $post_type ) )
                $post_type_where = " AND {$tpdb->posts}.post_type IN ('" . implode( "', '", $this->_esc_sql( $post_type ) ) . "')";
            elseif ( ! empty( $post_type ) ) {
                $post_type_where  = $tpdb->prepare( " AND {$tpdb->posts}.post_type = %s", $post_type );
                $post_type_object = $this->_get_post_type_object( $post_type );
            } elseif ( $this->is_attachment ) {
                $post_type_where  = " AND {$tpdb->posts}.post_type = 'attachment'";
                $post_type_object = $this->_get_post_type_object( 'attachment' );
            } elseif ( $this->is_page ) {
                $post_type_where  = " AND {$tpdb->posts}.post_type = 'page'";
                $post_type_object = $this->_get_post_type_object( 'page' );
            } else {
                $post_type_where  = " AND {$tpdb->posts}.post_type = 'post'";
                $post_type_object = $this->_get_post_type_object( 'post' );
            }
            $edit_cap = 'edit_post';
            $read_cap = 'read_post';
            if ( ! empty( $post_type_object ) ) {
                $edit_others_cap  = $post_type_object->cap->edit_others_posts;
                $read_private_cap = $post_type_object->cap->read_private_posts;
            } else {
                $edit_others_cap  = 'edit_others_' . $post_type_cap . 's';
                $read_private_cap = 'read_private_' . $post_type_cap . 's';
            }
            $user_id = $this->_get_current_user_id();
            $q_status = [];
            if ( $skip_post_status ) $where .= $post_type_where;
            elseif ( ! empty( $q['post_status'] ) ) {
                $where .= $post_type_where;
                $status_wheres = [];
                $q_status     = $q['post_status'];
                if ( ! is_array( $q_status ) ) $q_status = explode( ',', $q_status );
                $r_status = [];
                $p_status = [];
                $e_status = [];
                if ( in_array( 'any', $q_status, true ) ) {
                    $exclude = ['exclude_from_search' => true];
                    foreach ( $this->_get_post_stati($exclude ) as $status ) {
                        if ( ! in_array( $status, $q_status, true ) )
                            $e_status[] = "{$tpdb->posts}.post_status <> '$status'";
                    }
                }else {
                    foreach ( $this->_get_post_stati() as $status ) {
                        if ( in_array( $status, $q_status, true ) ) {
                            if ( 'private' === $status ) $p_status[] = "{$tpdb->posts}.post_status = '$status'";
                            else $r_status[] = "{$tpdb->posts}.post_status = '$status'";
                        }
                    }
                }
                if ( empty( $q['perm'] ) || 'readable' !== $q['perm'] ) {
                    $r_status = array_merge( $r_status, $p_status );
                    unset( $p_status );
                }
                if ( ! empty( $e_status ) ) $status_wheres[] = '(' . implode( ' AND ', $e_status ) . ')';
                if ( ! empty( $r_status ) ) {
                    if ( ! empty( $q['perm'] ) && 'editable' === $q['perm'] && ! $this->_current_user_can( $edit_others_cap ) )
                        $status_wheres[] = "({$tpdb->posts}.post_author = $user_id " . 'AND (' . implode( ' OR ', $r_status ) . '))';
                    else  $status_wheres[] = '(' . implode( ' OR ', $r_status ) . ')';
                }
                if ( ! empty( $p_status ) ) {
                    if ( ! empty( $q['perm'] ) && 'readable' === $q['perm'] && ! $this->_current_user_can( $read_private_cap ) )
                        $status_wheres[] = "({$tpdb->posts}.post_author = $user_id " . 'AND (' . implode( ' OR ', $p_status ) . '))';
                    else $status_wheres[] = '(' . implode( ' OR ', $p_status ) . ')';
                }
                if ( $post_status_join ) {
                    $join .= " LEFT JOIN {$tpdb->posts} AS p2 ON ({$tpdb->posts}.post_parent = p2.ID) ";
                    foreach ( $status_wheres as $index => $status_where )
                        $status_wheres[ $index ] = "($status_where OR ({$tpdb->posts}.post_status = 'inherit' AND " . str_replace( $tpdb->posts, 'p2', $status_where ) . '))';
                }
                $where_status = implode( ' OR ', $status_wheres );
                if ( ! empty( $where_status ) ) $where .= " AND ($where_status)";
            }elseif ( ! $this->is_singular ) {
                if ( 'any' === $post_type ) $queried_post_types = $this->_get_post_types( array( 'exclude_from_search' => false ) );
                elseif ( is_array( $post_type ) ) $queried_post_types = $post_type;
                elseif ( ! empty( $post_type ) ) $queried_post_types = array( $post_type );
                else $queried_post_types = array( 'post' );
                if ( ! empty( $queried_post_types ) ) {
                    $status_type_clauses = array();
                    foreach ( $queried_post_types as $queried_post_type ) {
                        $queried_post_type_object = $this->_get_post_type_object( $queried_post_type );
                        $type_where = '(' . $tpdb->prepare( "{$tpdb->posts}.post_type = %s AND (", $queried_post_type );
                        // Public statuses.
                        $public_statuses = $this->_get_post_stati( array( 'public' => true ) );
                        $status_clauses  = array();
                        foreach ( $public_statuses as $public_status )
                            $status_clauses[] = "{$tpdb->posts}.post_status = '$public_status'";
                        $type_where .= implode( ' OR ', $status_clauses );
                        // Add protected states that should show in the admin all list.
                        if ( $this->is_admin ) {
                            $admin_all_statuses = $this->_get_post_stati(array('protected' => true,'show_in_admin_all_list' => true,));
                            foreach ( $admin_all_statuses as $admin_all_status )
                                $type_where .= " OR {$tpdb->posts}.post_status = '$admin_all_status'";
                        }
                        // Add private states that are visible to current user.
                        if ($queried_post_type_object instanceof TP_Post_Type  &&  $this->_is_user_logged_in()) {
                            $read_private_cap = $queried_post_type_object->cap->read_private_posts;
                            $private_statuses = $this->_get_post_stati( array( 'private' => true ) );
                            foreach ( $private_statuses as $private_status )
                                $type_where .= $this->_current_user_can( $read_private_cap ) ? " \nOR {$tpdb->posts}.post_status = '$private_status'" : " \nOR ({$tpdb->posts}.post_author = $user_id AND {$tpdb->posts}.post_status = '$private_status')";
                        }
                        $type_where .= '))';
                        $status_type_clauses[] = $type_where;
                    }
                    if ( ! empty( $status_type_clauses ) ) $where .= ' AND (' . implode( ' OR ', $status_type_clauses ) . ')';
                }else $where .= ' AND 1=0 ';
            }else $where .= $post_type_where;//is_singular
            if ( ! $q['suppress_filters'] ) {
                $where = $this->_apply_filters_ref_array( 'posts_where', array( $where, &$this ) );
                $join = $this->_apply_filters_ref_array( 'posts_join', array( $join, &$this ) );
            }
            // Paging.
            if ( empty( $q['no_paging'] ) && ! $this->is_singular ) {
                $page = $this->_abs_int( $q['paged'] );
                if ( ! $page ) $page = 1;
                if ( isset( $q['offset'] ) && is_numeric( $q['offset'] ) ) {
                    $q['offset'] = $this->_abs_int( $q['offset'] );
                    $page_start      = $q['offset'] . ', ';
                } else $page_start = $this->_abs_int( ( $page - 1 ) * $q['posts_per_page'] ) . ', ';
                $limits = 'LIMIT ' . $page_start . $q['posts_per_page'];
            }
            // Comments feeds.
            if ( $this->is_comment_feed && ! $this->is_singular ) {
                if ( $this->is_archive || $this->is_search ) {
                    $c_join    = "JOIN {$tpdb->posts} ON ( {$tpdb->comments}.comment_post_ID = {$tpdb->posts}.ID ) $join ";
                    $c_where   = "WHERE comment_approved = '1' $where";
                    $c_group_by = "{$tpdb->comments}.comment_id";
                } else { // Other non-singular, e.g. front.
                    $c_join    = "JOIN {$tpdb->posts} ON ( {$tpdb->comments}.comment_post_ID = {$tpdb->posts}.ID )";
                    $c_where   = "WHERE ( post_status = 'publish' OR ( post_status = 'inherit' AND post_type = 'attachment' ) ) AND comment_approved = '1'";
                    $c_group_by = '';
                }
                if ( ! $q['suppress_filters'] ) {
                    $c_join = $this->_apply_filters_ref_array( 'comment_feed_join', array( $c_join, &$this ) );
                    $c_where = $this->_apply_filters_ref_array( 'comment_feed_where', array( $c_where, &$this ) );
                    $c_group_by = $this->_apply_filters_ref_array( 'comment_feed_groupby', array( $c_group_by, &$this ) );
                    $c_order_by = $this->_apply_filters_ref_array( 'comment_feed_orderby', array( 'comment_date_gmt DESC', &$this ) );
                    $c_limits = $this->_apply_filters_ref_array( 'comment_feed_limits', array( 'LIMIT ' . $this->_get_option( 'posts_per_rss' ), &$this ) );
                }
                $c_group_by = ( ! empty( $c_group_by ) ) ? 'GROUP BY ' . $c_group_by : '';
                $c_order_by = ( ! empty( $c_order_by ) ) ? 'ORDER BY ' . $c_order_by : '';
                $c_limits  = ( ! empty( $c_limits ) ) ? $c_limits : '';
                $comments_request = TP_SELECT . " $distinct {$tpdb->comments}.comment_ID FROM {$tpdb->comments} $c_join $c_where $c_group_by $c_order_by $c_limits";
                $key          = md5( $comments_request );
                $last_changed = $this->_tp_cache_get_last_changed( 'comment' ) . ':' . $this->_tp_cache_get_last_changed( 'posts' );
                $cache_key   = "comment_feed:$key:$last_changed";
                $comment_ids = $this->_tp_cache_get( $cache_key, 'comment' );
                if ( false === $comment_ids ) {
                    $comment_ids = $tpdb->get_col( $comments_request );
                    $this->_tp_cache_add( $cache_key, $comment_ids, 'comment' );
                }
                $this->_prime_comment_caches( $comment_ids, false );
                // Convert to TP_Comment.
                $this->comments      = array_map( 'get_comment', $comment_ids );
                $this->comment_count = count( $this->comments );
                $post_ids = [];
                foreach ( $this->comments as $comment ) $post_ids[] = (int) $comment->comment_post_ID;
                $post_ids = implode( ',', $post_ids );
                $join     = '';
                if ( $post_ids )  $where = "AND {$tpdb->posts}.ID IN ($post_ids) ";
                else  $where = 'AND 0';
            }
            $clauses  = ['where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits'];
            if ( ! $q['suppress_filters'] ) {
                $where = $this->_apply_filters_ref_array( 'posts_where_paged', array( $where, &$this ) );
                $groupby = $this->_apply_filters_ref_array( 'posts_groupby', array( $groupby, &$this ) );
                $join = $this->_apply_filters_ref_array( 'posts_join_paged', array( $join, &$this ) );
                $orderby = $this->_apply_filters_ref_array( 'posts_orderby', array( $orderby, &$this ) );
                $distinct = $this->_apply_filters_ref_array( 'posts_distinct', array( $distinct, &$this ) );
                $limits = $this->_apply_filters_ref_array( 'post_limits', array( $limits, &$this ) );
                $fields = $this->_apply_filters_ref_array( 'posts_fields', array( $fields, &$this ) );
                $clauses = (array) $this->_apply_filters_ref_array( 'posts_clauses', array( compact( $clauses  ), &$this ) );
                $where    .= $clauses['where'] ?? '';
                $groupby  .= $clauses['groupby'] ?? '';
                $join     .= $clauses['join'] ?? '';
                $orderby  .= $clauses['orderby'] ?? '';
                $distinct .= $clauses['distinct'] ?? '';
                $fields   .= $clauses['fields'] ?? '';
                $limits   .= $clauses['limits'] ?? '';
            }
            $this->_do_action( 'posts_selection', $where . $groupby . $orderby . $limits . $join );
            if ( ! $q['suppress_filters'] ) {
                $where = $this->_apply_filters_ref_array( 'posts_where_request', array( $where, &$this ) );
                $groupby = $this->_apply_filters_ref_array( 'posts_group_by_request', array( $groupby, &$this ) );
                $join = $this->_apply_filters_ref_array( 'posts_join_request', array( $join, &$this ) );
                $orderby = $this->_apply_filters_ref_array( 'posts_orderby_request', array( $orderby, &$this ) );
                $distinct = $this->_apply_filters_ref_array( 'posts_distinct_request', array( $distinct, &$this ) );
                $fields = $this->_apply_filters_ref_array( 'posts_fields_request', array( $fields, &$this ) );
                $limits = $this->_apply_filters_ref_array( 'post_limits_request', array( $limits, &$this ) );
                $clauses = (array) $this->_apply_filters_ref_array( 'posts_clauses_request', array( compact( $clauses ), &$this ) );
                $where    .= $clauses['where'] ?? '';
                $groupby  .= $clauses['groupby'] ?? '';
                $join     .= $clauses['join'] ?? '';
                $orderby  .= $clauses['orderby'] ?? '';
                $distinct .= $clauses['distinct'] ?? '';
                $fields   .= $clauses['fields'] ?? '';
                $limits   .= $clauses['limits'] ?? '';
            }
            if ( ! empty( $groupby ) ) $groupby = 'GROUP BY ' . $groupby;
            if ( ! empty( $orderby ) ) $orderby = 'ORDER BY ' . $orderby;
            $found_rows = '';
            if ( ! $q['no_found_rows'] && ! empty( $limits ) ) $found_rows = 'SQL_CALC_FOUND_ROWS';
            $old_request   = TP_SELECT . " $found_rows $distinct $fields FROM {$tpdb->posts} $join WHERE 1=1 $where $groupby $orderby $limits";
            $this->request = $old_request;
            if ( ! $q['suppress_filters'] )
                $this->request = $this->_apply_filters_ref_array( 'posts_request', array( $this->request, &$this ) );
            $this->posts = $this->_apply_filters_ref_array( 'posts_pre_query', array( null, &$this ) );
            if ( 'ids' === $q['fields'] ) {
                if ( null === $this->posts ) $this->posts = $tpdb->get_col( $this->request );
                $this->posts      = array_map( 'intval', $this->posts );
                $this->post_count = count( $this->posts );
                $this->__set_found_posts( $q, $limits );
                return $this->posts;
            }
            if ( 'id=>parent' === $q['fields'] ) {
                if ( null === $this->posts )
                    $this->posts = $tpdb->get_results( $this->request );
                $this->post_count = count( $this->posts );
                $this->__set_found_posts( $q, $limits );
                $r = array();
                foreach ( $this->posts as $key => $post ) {
                    $this->posts[ $key ]->ID          = (int) $post->ID;
                    $this->posts[ $key ]->post_parent = (int) $post->post_parent;
                    $r[ (int) $post->ID ] = (int) $post->post_parent;
                }
                return $r;
            }
            if ( null === $this->posts ) {
                $split_the_query = ( $old_request === $this->request && "{$tpdb->posts}.*" === $fields && ! empty( $limits ) && $q['posts_per_page'] < 500 );
                $split_the_query = $this->_apply_filters( 'split_the_query', $split_the_query, $this );
                if ( $split_the_query ) {
                    $this->request = TP_SELECT . " $found_rows $distinct {$tpdb->posts}.ID FROM {$tpdb->posts} $join WHERE 1=1 $where $groupby $orderby $limits";
                    $this->request = $this->_apply_filters( 'posts_request_ids', $this->request, $this );
                    $ids = $tpdb->get_col( $this->request );
                    if ( $ids ) {
                        $this->posts = $ids;
                        $this->__set_found_posts( $q, $limits );
                        $this->_prime_post_caches( $ids, $q['update_post_term_cache'], $q['update_post_meta_cache'] );
                    } else $this->posts = array();
                }else {
                    $this->posts = $tpdb->get_results( $this->request );
                    $this->__set_found_posts( $q, $limits );
                }
            }
            // Convert to TP_Post objects.
            if ( $this->posts ) /** @var TP_Post[] */ $this->posts = array_map( 'get_post', $this->posts );
            if ( ! $q['suppress_filters'] )
                $this->posts = $this->_apply_filters_ref_array( 'posts_results', array( $this->posts, &$this ) );
            if ( ! empty( $this->posts ) && $this->is_comment_feed && $this->is_singular ) {
                $c_join = $this->_apply_filters_ref_array( 'comment_feed_join', array( '', &$this ) );
                $c_where = $this->_apply_filters_ref_array( 'comment_feed_where', array( "WHERE comment_post_ID = '{$this->posts[0]->ID}' AND comment_approved = '1'", &$this ) );
                $c_group_by = $this->_apply_filters_ref_array( 'comment_feed_groupby', array( '', &$this ) );
                $c_group_by = ( ! empty( $c_group_by ) ) ? 'GROUP BY ' . $c_group_by : '';
                $c_order_by = $this->_apply_filters_ref_array( 'comment_feed_orderby', array( 'comment_date_gmt DESC', &$this ) );
                $c_order_by = ( ! empty( $c_order_by ) ) ? 'ORDER BY ' . $c_order_by : '';
                $c_limits = $this->_apply_filters_ref_array( 'comment_feed_limits', array( 'LIMIT ' . $this->_get_option( 'posts_per_rss' ), &$this ) );
                $comments_request = TP_SELECT . " $distinct {$tpdb->comments}.comment_ID FROM {$tpdb->comments} $c_join $c_where $c_group_by $c_order_by $c_limits";
                $key          = md5( $comments_request );
                $last_changed = $this->_tp_cache_get_last_changed( 'comment' ) . ':' . $this->_tp_cache_get_last_changed( 'posts' );
                $cache_key   = "comment_feed:$key:$last_changed";
                $comment_ids = $this->_tp_cache_get( $cache_key, 'comment' );
                if ( false === $comment_ids ) {
                    $comment_ids = $tpdb->get_col( $comments_request );
                    $this->_tp_cache_add( $cache_key, $comment_ids, 'comment' );
                }
                $this->_prime_comment_caches( $comment_ids, false );
                $this->comments      = array_map( 'get_comment', $comment_ids );
                $this->comment_count = count( $this->comments );
            }
            // Check post status to determine if post should be displayed.
            if ( ! empty( $this->posts ) && ( $this->is_single || $this->is_page ) ) {
                $status = $this->_get_post_status( $this->posts[0] );
                if ( 'attachment' === $this->posts[0]->post_type && 0 === (int) $this->posts[0]->post_parent ) {
                    $this->is_page       = false;
                    $this->is_single     = true;
                    $this->is_attachment = true;
                }
                // If the post_status was specifically requested, let it pass through.
                if ( ! in_array( $status, $q_status, true ) ) {
                    $post_status_obj = $this->_get_post_status_object( $status );
                    if ( $post_status_obj && ! $post_status_obj->public ) {
                        if ( ! $this->_is_user_logged_in() )
                            $this->posts = array(); // User must be logged in to view unpublished posts.
                        else if ( $post_status_obj->protected ) {
                            // User must have edit permissions on the draft to preview.
                            if ( ! $this->_current_user_can( $edit_cap, $this->posts[0]->ID ) )
                                $this->posts = [];
                            else {
                                $this->is_preview = true;
                                if ( 'future' !== $status )
                                    $this->posts[0]->post_date = $this->_current_time( 'mysql' );
                            }
                        } elseif ( $post_status_obj->private ) {
                            if ( ! $this->_current_user_can( $read_cap, $this->posts[0]->ID ) )
                                $this->posts = array();
                        } else  $this->posts = array();
                    } elseif ( ! $post_status_obj ) {
                        // Post status is not registered, assume it's not public.
                        if ( ! $this->_current_user_can( $edit_cap, $this->posts[0]->ID ) )
                            $this->posts = array();
                    }
                }
                if ( $this->is_preview && $this->posts && $this->_current_user_can( $edit_cap, $this->posts[0]->ID ) )
                    $this->posts[0] = $this->_get_post( $this->_apply_filters_ref_array( 'the_preview', array( $this->posts[0], &$this ) ) );
            }
            // Put sticky posts at the top of the posts array.
            $sticky_posts = $this->_get_option( 'sticky_posts' );
            if ( $this->is_home && $page <= 1 && is_array( $sticky_posts ) && ! empty( $sticky_posts ) && ! $q['ignore_sticky_posts'] ) {
                $sticky_offset = 0;
                foreach ($this->posts as $i => $iValue) {
                    if ( in_array( $iValue->ID, $sticky_posts, true ) ) {
                        $sticky_post = $iValue;
                        array_splice( $this->posts, $i, 1 );
                        array_splice( $this->posts, $sticky_offset, 0, array( $sticky_post ) );
                        $sticky_offset++;
                        $offset = array_search( $sticky_post->ID, $sticky_posts, true );
                        unset( $sticky_posts[ $offset ] );
                    }
                }
                if ( ! empty( $sticky_posts ) && ! empty( $q['post__not_in'] ) )
                    $sticky_posts = array_diff( $sticky_posts, $q['post__not_in'] );
                if ( ! empty( $sticky_posts ) ) {
                    $stickies = $this->_get_posts([
                        'post__in'               => $sticky_posts,
                        'post_type'              => $post_type,
                        'post_status'            => 'publish',
                        'posts_per_page'         => count( $sticky_posts ),
                        'suppress_filters'       => $q['suppress_filters'],
                        'cache_results'          => $q['cache_results'],
                        'update_post_meta_cache' => $q['update_post_meta_cache'],
                        'update_post_term_cache' => $q['update_post_term_cache'],
                        'lazy_load_term_meta'    => $q['lazy_load_term_meta'],
                    ]);
                    foreach ((array) $stickies as $sticky_post ) {
                        array_splice( $this->posts, $sticky_offset, 0, array( $sticky_post ) );
                        $sticky_offset++;
                    }
                }
            }
            if ( ! empty( $this->comments ) )
                $this->_tp_queue_comments_for_comment_meta_lazy_load( $this->comments );
            if ( ! $q['suppress_filters'] )
                $this->posts = $this->_apply_filters_ref_array( 'the_posts', array( $this->posts, &$this ) );
            if ( $this->posts ) {
                $this->post_count = count( $this->posts );
                /** @var TP_Post[] */
                $this->posts = array_map( 'get_post', $this->posts );
                if ( $q['cache_results'] )
                    $this->_update_post_caches( $this->posts, $post_type, $q['update_post_term_cache'], $q['update_post_meta_cache'] );
                /** @var TP_Post */
                $this->post = reset( $this->posts );
            } else {
                $this->post_count = 0;
                $this->posts      = array();
            }
            if ( $q['lazy_load_term_meta'] )
                $this->_tp_queue_posts_for_term_meta_lazy_load( $this->posts );
            return $this->posts;
        }//1788
        private function __set_found_posts( $q, $limits ): void{
            $tpdb = $this->_init_db();
            if ( $q['no_found_rows'] || ( is_array( $this->posts ) && ! $this->posts ) ) return;
            if ( ! empty( $limits ) ) {
                $found_posts_query = $this->_apply_filters_ref_array( 'found_posts_query', array( 'SELECT FOUND_ROWS()', &$this ) );
                $this->found_posts = (int) $tpdb->get_var( $found_posts_query );
            } elseif ( is_array( $this->posts ) )
                $this->found_posts = count( $this->posts );
            elseif ( null === $this->posts ) $this->found_posts = 0;
            else $this->found_posts = 1;
            $this->found_posts = (int) $this->_apply_filters_ref_array( 'found_posts', array( $this->found_posts, &$this ) );
            if ( ! empty( $limits ) )  $this->max_num_pages = ceil( $this->found_posts / $q['posts_per_page'] );
        }//3317
        public function next_post() {//todo
            $this->current_post++;
            //$posts = $this->_init_post();
            if($this->posts instanceof TP_Post)
                $this->post = $this->posts[ $this->current_post ];
            return $this->post;
        }//3372
        public function the_post(): void{
            $this->in_the_loop = true;
            if ( -1 === $this->current_post )
                $this->_do_action_ref_array( 'loop_start', array( &$this ) );
            $post = $this->next_post();
            $this->setup_postdata( $post );
        }//3391
        public function have_posts(): bool{
            if ( $this->current_post + 1 < $this->post_count ) return true;
            elseif ( $this->current_post + 1 === $this->post_count && $this->post_count > 0 ) {
                $this->_do_action_ref_array( 'loop_end', array( &$this ) );
                // Do some cleaning up after the loop.
                $this->rewind_posts();
            } elseif ( 0 === $this->post_count ) $this->_do_action( 'loop_no_results', $this );
            $this->in_the_loop = false;
            return false;
        }//3419
        public function rewind_posts(): void{
            $this->current_post = -1;
            if ( $this->post_count > 0 ) $this->post = $this->posts[0];
        }//3453
        public function next_comment() {
            $this->current_comment++;
            //if($this->comment instanceof TP_Comment)
            $this->comment = $this->comments[ $this->current_comment ];
            return $this->comment;
        }//3467
        public function the_comment(): void{
            $this->comment = $this->next_comment();
            if ( 0 === $this->current_comment )
                $this->_do_action( 'comment_loop_start' );
        }//3482
        public function have_comments(): bool{
            if ( $this->current_comment + 1 < $this->comment_count )
                return true;
            elseif ( $this->current_comment + 1 === $this->comment_count )
                $this->rewind_comments();
            return false;
        }//3506
        public function rewind_comments(): void{
            $this->current_comment = -1;
            if ( $this->comment_count > 0 ) {
                $this->comment = $this->comments[0];
            }
        }//3521
        public function query_main( $query ) { //original $query
            $this->init();
            $this->query      = $this->_tp_parse_args( $query );
            $this->query_vars = $this->query;
            return $this->get_posts();
        }//3538
        public function get_queried_object() {
            if ( isset( $this->queried_object ) ) {
                return $this->queried_object;
            }
            $this->queried_object    = null;
            $this->queried_object_id = null;
            if ( $this->is_category || $this->is_tag || $this->is_tax ) {
                if ( $this->is_category ) {
                    if ( $this->get( 'cat' ) ) {
                        $term = $this->_get_term( $this->get( 'cat' ), 'category' );
                    } elseif ( $this->get( 'category_name' ) ) {
                        $term = $this->_get_term_by( 'slug', $this->get( 'category_name' ), 'category' );
                    }
                } elseif ( $this->is_tag ) {
                    if ( $this->get( 'tag_id' ) ) {
                        $term = $this->_get_term( $this->get( 'tag_id' ), 'post_tag' );
                    } elseif ( $this->get( 'tag' ) ) {
                        $term = $this->_get_term_by( 'slug', $this->get( 'tag' ), 'post_tag' );
                    }
                }else if ( ! empty( $this->tax_query->queried_terms ) ) {
                    $queried_taxonomies = array_keys( $this->tax_query->queried_terms );
                    $matched_taxonomy   = reset( $queried_taxonomies );
                    $query              = $this->tax_query->queried_terms[ $matched_taxonomy ];

                    if ( ! empty( $query['terms'] ) ) {
                        if ( 'term_id' === $query['field'] ) {
                            $term = $this->_get_term( reset( $query['terms'] ), $matched_taxonomy );
                        } else {
                            $term = $this->_get_term_by( $query['field'], reset( $query['terms'] ), $matched_taxonomy );
                        }
                    }
                }
                if ( ! empty( $term ) && ! $this->_init_error( $term ) ) {
                    $this->queried_object    = $term;
                    $this->queried_object_id = (int) $term->term_id;
                    if ( $this->is_category && 'category' === $this->queried_object->taxonomy ) {
                        $this->_make_cat_compat( $this->queried_object );
                    }
                }
            }elseif ( $this->is_post_type_archive ) {
                $post_type = $this->get( 'post_type' );
                if ( is_array( $post_type ) ) {
                    $post_type = reset( $post_type );
                }
                $this->queried_object = $this->_get_post_type_object( $post_type );
            } elseif ( $this->is_posts_page ) {
                $page_for_posts          = $this->_get_option( 'page_for_posts' );
                $this->queried_object    = $this->_get_post( $page_for_posts );
                $object_id = null;
                if( $this->queried_object instanceof TP_Post ){
                    $object_id = $this->queried_object->ID;
                }
                $this->queried_object_id = (int) $object_id;
            } elseif ( $this->is_singular && ! empty( $this->post ) ) {
                $this->queried_object    = $this->post;
                $this->queried_object_id = (int) $this->post->ID;
            } elseif ( $this->is_author ) {
                $this->queried_object_id = (int) $this->get( 'author' );
                $this->queried_object    = $this->_get_user_data( $this->queried_object_id );
            }
            return $this->queried_object;
        }//3556
        public function get_queried_object_id(): int{
            $this->get_queried_object();
            if ( isset( $this->queried_object_id )) return $this->queried_object_id;
            return 0;
        }//3630
        public function __construct( $query = '' ){
            $this->_http_constants();
            $this->meta_query = $this->_init_meta_query();
        }//3651
        public function is_archive(): bool{
            return (bool) $this->is_archive;
        }//3718
        public function is_post_type_archive( $post_types = '' ){
            if ( empty( $post_types ) || ! $this->is_post_type_archive )
                return (bool) $this->is_post_type_archive;
            $post_type = $this->get( 'post_type' );
            if ( is_array( $post_type ) )
                $post_type = reset( $post_type );
            $post_type_object = $this->_get_post_type_object( $post_type );
            return in_array( $post_type_object->name, (array) $post_types, true );
        }//3731
        public function is_attachment( ...$attachment): bool{
            if ( ! $this->is_attachment )return false;
            if ( empty( $attachment ) ) return true;
            $attachment = array_map( 'string_val', $attachment);
            $post_obj = $this->get_queried_object();
            if ( in_array( (string) $post_obj->ID, $attachment, true ) )   return true;
            elseif ( in_array( $post_obj->post_title, $attachment, true ) )return true;
            elseif ( in_array( $post_obj->post_name, $attachment, true ) ) return true;
            return false;
        }//3754
        public function is_author( ...$author): bool{
            if ( ! $this->is_author ) return false;
            if ( empty( $author ) ) return true;
            $author_obj = $this->get_queried_object();
            $author = array_map( 'string_val', $author );
            if ( in_array( (string) $author_obj->ID, $author, true ) )
                return true;
            elseif ( in_array( $author_obj->nickname, $author, true ) )
                return true;
            elseif ( in_array( $author_obj->user_nicename, $author, true ) )
                return true;
            return false;
        }//3789
        public function is_category( ...$category ): bool{
            if ( ! $this->is_category ) return false;
            if ( empty( $category ) )  return true;
            $cat_obj = $this->get_queried_object();
            $category = array_map( 'string_val',$category );
            if ( in_array( (string) $cat_obj->term_id, $category, true ) )
                return true;
            elseif ( in_array( $cat_obj->name, $category, true ) )
                return true;
            elseif ( in_array( $cat_obj->slug, $category, true ) )
                return true;
            return false;
        }//3825
        public function is_tag( ...$tag): bool{
            if ( ! $this->is_tag ) return false;
            if ( empty( $tag ) ) return true;
            $tag_obj = $this->get_queried_object();
            $tag = array_map( 'string_val', $tag );
            if ( in_array( (string) $tag_obj->term_id, $tag, true ) )
                return true;
            elseif ( in_array( $tag_obj->name, $tag, true ) )
                return true;
            elseif ( in_array( $tag_obj->slug, $tag, true ) )
                return true;
            return false;
        }//3861
        public function is_tax( $taxonomy = '', $term = '' ): bool{//todo
            if ( ! $this->is_tax ) { return false;}
            if ( empty( $taxonomy ) ) { return true;}
            $queried_object = $this->get_queried_object();
            $tax_array      = array_intersect( array_keys( $this->_tp_taxonomies ), (array) $taxonomy );
            $term_array     = (array) $term;
            $tax_object = null;
            if( $queried_object instanceof TP_Post ){
                $tax_object = $queried_object->taxonomy;
            }
            if ( ! ( isset( $tax_object) && count( $tax_array ) && in_array( $queried_object->taxonomy, $tax_array, true ) ) ) {
                return false;
            }
            if ( empty( $term ) ) {return true;}
            $term_obj_id = null;
            if( $queried_object instanceof TP_Post ){
                $term_obj_id = $queried_object->term_id;
            }
            return isset( $term_obj_id ) && count( array_intersect( array( $queried_object->term_id, $queried_object->name, $queried_object->slug ),$term_array));
        }//3907
        public function is_date(): bool{
            return (bool) $this->is_date;
        }//3962
        public function is_day(): bool{
            return (bool) $this->is_day;
        }//3973
        public function is_feed( $feeds = '' ){
            if ( empty( $feeds ) || ! $this->is_feed )
                return (bool) $this->is_feed;
            $qv = $this->get( 'feed' );
            if ( 'feed' === $qv ) $qv = $this->_get_default_feed();
            return in_array( $qv, (array) $feeds, true );
        }//3986
        public function is_comment_feed(): bool{
            return (bool) $this->is_comment_feed;
        }//4006
        public function is_front_page(): bool{
            if ( 'posts' === $this->_get_option( 'show_on_front' ) && $this->is_home() )
                return true;
            elseif ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' )
                && $this->is_page( $this->_get_option( 'page_on_front' ) )
            ) return true;
            else return false;
        }//4026
        public function is_home(): bool{
            return (bool) $this->is_home;
        }//4055
        public function is_privacy_policy(): bool{
            if ( $this->_get_option( 'tp_page_for_privacy_policy' )
                && $this->is_page( $this->_get_option( 'tp_page_for_privacy_policy' ) )
            ) return true;
            else  return false;
        }//4072
        public function is_month(): bool{
            return (bool) $this->is_month;
        }//4089
        public function is_page( $page = '' ): bool{
            if ( ! $this->is_page ) return false;
            if ( empty( $page ) ) return true;
            $page_obj = $this->get_queried_object();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $page = array_map( 'string_val', (array) $page );
            if ( in_array( (string) $page_obj->ID, $page, true ) ) return true;
            elseif ( in_array( $page_obj->post_title, $page, true ) ) return true;
            elseif ( in_array( $page_obj->post_name, $page, true ) ) return true;
            else {
                foreach ( $page as $pagepath ) {
                    if ( ! strpos( $pagepath, '/' ) ) continue;
                    $pagepath_obj = $this->_get_page_by_path( $pagepath );

                    if ( ($pagepath_obj instanceof TP_Post) && ( $pagepath_obj->ID === $page_obj->ID ) ) return true;
                }
            }
            return false;
        }//4108
        public function is_paged(): bool{
            return (bool) $this->is_paged;
        }//4150
        public function is_preview(): bool{
            return (bool) $this->is_preview;
        }//4161
        public function is_robots(): bool{
            return (bool) $this->is_robots;
        }//4172
        public function is_favicon(): bool{
            return (bool) $this->is_favicon;
        }//4183
        public function is_search(): bool{
            return (bool) $this->is_search;
        }//4194
        public function is_single( $post = '' ): bool{
            if ( ! $this->is_single ) return false;
            if ( empty( $post ) ) return true;
            $post_obj = $this->get_queried_object();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $post = array_map( 'string_val', (array) $post );
            if ( in_array( (string) $post_obj->ID, $post, true ) ) return true;
            elseif ( in_array( $post_obj->post_title, $post, true ) ) return true;
            elseif ( in_array( $post_obj->post_name, $post, true ) ) return true;
            else {
                foreach ( $post as $postpath ) {
                    if ( ! strpos( $postpath, '/' ) ) continue;
                    $postpath_obj = $this->_get_page_by_path( $postpath, OBJECT, $post_obj->post_type );
                    if ( ($postpath_obj instanceof TP_Post) && ( $postpath_obj->ID === $post_obj->ID ) ) return true;
                }
            }
            return false;
        }//4215
        public function is_singular( $post_types = '' ){
            if ( empty( $post_types ) || ! $this->is_singular )
                return (bool) $this->is_singular;
            $post_obj = $this->get_queried_object();
            return in_array( $post_obj->post_type, (array) $post_types, true );
        }//4266
        public function is_time(): bool{
            return (bool) $this->is_time;
        }//4283
        public function is_trackback(): bool{
            return (bool) $this->is_trackback;
        }//4294
        public function is_year(): bool{
            return (bool) $this->is_year;
        }//4305
        public function is_404(): bool{
            return (bool) $this->is_404;
        }//4316
        public function is_embed(): bool{
            return (bool) $this->is_embed;
        }//4327
        public function is_main_query(): bool{
            return $this->tp_the_query === $this;
        }//4340
        public function setup_postdata( $post ): bool{
            $post = $this->_init_post( $post );
            if ( ! $post ) return false;
            $elements = $this->generate_postdata( $post );
            if ( false === $elements ) return false;
            $id           = $elements['id'];
            $author_data   = $elements['author_data'];
            $current_day   = $elements['current_day'];
            $current_month = $elements['current_month'];
            $page         = $elements['page'];
            $pages        = $elements['pages'];
            $multi_page    = $elements['multi_page'];
            $more         = $elements['more'];
            $num_pages     = $elements['num_pages'];
            $_elements = compact('id','author_data','current_day','current_month','page','pages','multi_page','more','num_pages');
            $this->_do_action_ref_array( 'the_post', array( &$post, &$this,$_elements ) );//todo let's see
            return true;
        }//4364
        public function generate_postdata($post ){
            if ( ! ( $post instanceof TP_Post ) ) $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $id = (int) $post->ID;
            $author_data = $this->_get_user_data( $post->post_author );
            $current_day   = $this->_mysql2date( 'd.m.y', $post->post_date, false );
            $current_month = $this->_mysql2date( 'm', $post->post_date, false );
            $page         = $this->get( 'page' );
            if ( ! $page ) $page = 1;
            if ( $this->_get_queried_object_id() === $post->ID && ( $this->is_page() || $this->is_single() ) )
                $more = 1;
            elseif ( $this->is_feed() )
                $more = 1;
            else $more = 0;
            $content = $post->post_content;
            if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
                $content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
                $content .= str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
                $content .= str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
                $content .= str_replace( '<!-- tp:nextpage -->', '', $content );
                $content .= str_replace( '<!-- /tp:nextpage -->', '', $content );
                if ( 0 === strpos( $content, '<!--nextpage-->' ) ) $content = substr( $content, 15 );
                $pages = explode( '<!--nextpage-->', $content );
            } else $pages = [$post->post_content];

            $pages = $this->_apply_filters( 'content_pagination', $pages, $post );
            $num_pages = count( $pages );
            if ( $num_pages > 1 ) {
                if ( $page > 1 ) $more = 1;
                $multi_page = 1;
            } else $multi_page = 0;
            $elements = compact('id','author_data','current_day','current_month','page','pages','multi_page','more','num_pages');
            return $elements;
        }//4412
        public function reset_postdata(): void{
            if ( ! empty( $this->post ) ) {
                $this->tp_post = $this->post;
                $this->setup_postdata( $this->post );
            }
        }//4503
    }
}else die;