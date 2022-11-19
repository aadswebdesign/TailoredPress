<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _link_template_05 {
        use _init_db;
        /**
         * @description  Retrieves the next post that is adjacent to the current post.
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         * @return null|string
         */
        protected function _get_next_post( $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):string{
            return $this->_get_adjacent_post( $in_same_term, $excluded_terms, false, $taxonomy );
        }//1767 todo from link-template
        /**
         * @description Retrieves the adjacent post.
         * @param bool $in_same_term
         * @param array|string $excluded_terms
         * @param bool $previous
         * @param string $taxonomy
         * @return null|string
         */
        protected function _get_adjacent_post( $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):string{
            $tpdb = $this->_init_db();
            $post = $this->_get_post();
            if ( ! $post || ! $this->_taxonomy_exists( $taxonomy ) ) return null;
            $current_post_date = $post->post_date;
            $join     = '';
            $where    = '';
            $adjacent = $previous ? 'previous' : 'next';
            if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
                $excluded_terms = explode( ',', $excluded_terms );
                $excluded_terms = array_map( 'intval', $excluded_terms );
            }
            $excluded_terms = $this->_apply_filters( "get_{$adjacent}_post_excluded_terms", $excluded_terms );
            if ( $in_same_term || ! empty( $excluded_terms ) ) {
                if ( $in_same_term ) {
                    $join  .= " INNER JOIN $tpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $tpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                    $where .= $tpdb->prepare( 'AND tt.taxonomy = %s', $taxonomy );
                    if ( ! $this->_is_object_in_taxonomy( $post->post_type, $taxonomy ) ) return '';
                    $term_array = $this->_tp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                    $term_array = array_map( 'intval', $term_array );
                    if ( ! $term_array || $this->_init_error( $term_array ) ) return '';
                    $where .= ' AND tt.term_id IN (' . implode( ',', $term_array ) . ')';
                }
                if ( ! empty( $excluded_terms ) )
                    $where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $tpdb->term_relationships tr LEFT JOIN $tpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', array_map( 'intval', $excluded_terms ) ) . ') )';
            }
            if ( $this->_is_user_logged_in() ) {
                $user_id = $this->_get_current_user_id();
                $post_type_object = $this->_get_post_type_object( $post->post_type );
                if ( empty( $post_type_object ) ) {
                    $post_type_cap    = $post->post_type;
                    $read_private_cap = 'read_private_' . $post_type_cap . 's';
                } else $read_private_cap = $post_type_object->cap->read_private_posts;
                $private_states = $this->_get_post_stati( array( 'private' => true ) );
                $where         .= " AND ( p.post_status = 'publish'";
                foreach ( $private_states as $state ) {
                    if ( $this->_current_user_can( $read_private_cap ) ) $where .= $tpdb->prepare( ' OR p.post_status = %s', $state );
                    else $where .= $tpdb->prepare( ' OR (p.post_author = %d AND p.post_status = %s)', $user_id, $state );
                }
                $where .= ' )';
            } else $where .= " AND p.post_status = 'publish'";
            $op    = $previous ? '<' : '>';
            $order = $previous ? 'DESC' : 'ASC';
            $join = $this->_apply_filters( "get_{$adjacent}_post_join", $join, $in_same_term, $excluded_terms, $taxonomy, $post );
            $where = $this->_apply_filters( "get_{$adjacent}_post_where", $tpdb->prepare( "WHERE p.post_date $op %s AND p.post_type = %s $where", $current_post_date, $post->post_type ), $in_same_term, $excluded_terms, $taxonomy, $post );
            $sort = $this->_apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1", $post, $order );
            $query     = TP_SELECT . " p.ID FROM $tpdb->posts AS p $join $where $sort";
            $query_key = 'adjacent_post_' . md5( $query );
            $result    = $this->_tp_cache_get( $query_key, 'counts' );
            if ( false !== $result ) {
                if ( $result ) $result = $this->_get_post( $result );
                return $result;
            }
            $result = $tpdb->get_var( $query );
            if ( null === $result ) $result = '';
            $this->_tp_cache_set( $query_key, $result, 'counts' );
            if ( $result ) $result = $this->_get_post( $result );
            return $result;
        }//1787 from link-template
        /**
         * @description Retrieves the adjacent post relational link.
         * @param string $title
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param bool $previous
         * @param string $taxonomy
         * @return bool
         */
        protected function _get_adjacent_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):bool{
            $post = $this->_get_post();
            if ( $post && $previous && $this->_is_attachment()) $post = $this->_get_post( $post->post_parent );
            else $post = $this->_get_adjacent_post( $in_same_term, $excluded_terms, $previous, $taxonomy );
            if ( empty( $post ) ) return false;
            $post_title = $this->_the_title_attribute(['echo' => false, 'post' => $post,]);
            if ( empty( $post_title ) ) $post_title = $previous ? $this->__( 'Previous Post' ) : $this->__( 'Next Post' );
            $date = $this->_mysql2date( $this->_get_option( 'date_format' ), $post->post_date );
            $title = str_replace(array('%title', '%date'), array($post_title, $date), $title);
            $link  = $previous ? "<link rel='prev' title='" : "<link rel='next' title='";
            $link .= $this->_esc_attr( $title );
            $link .= "' href='{get_permalink( $post )}' />\n";
            $adjacent = $previous ? 'previous' : 'next';
            return $this->_apply_filters( "{$adjacent}_post_rel_link", $link );
        }//2000 from link-template
        /**
         * @description Displays the relational links for the posts adjacent to the current post.
         * @param string $title
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         */
        protected function _adjacent_posts_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void{
            echo $this->_get_adjacent_post_rel_link( $title, $in_same_term, $excluded_terms, true, $taxonomy );
            echo $this->_get_adjacent_post_rel_link( $title, $in_same_term, $excluded_terms, false, $taxonomy );
        }//2062 from link-template
        /**
         * @description Displays relational links for the posts adjacent to the current post for single post pages.
         */
        protected function _adjacent_posts_rel_link_tp_head():void{
            if ( ! $this->_is_single() || $this->_is_attachment() ) return;
            $this->_adjacent_posts_rel_link();
        }//2078 from link-template
        /**
         * @description Displays the relational link for the next post adjacent to the current post.
         * @param string $title
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         */
        protected function _next_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void{
            echo $this->_get_adjacent_post_rel_link( $title, $in_same_term, $excluded_terms, false, $taxonomy );
        }//2097 from link-template
        /**
         * @description Displays the relational link for the previous post adjacent to the current post.
         * @param string $title
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         */
        protected function _prev_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void{
            echo $this->_get_adjacent_post_rel_link( $title, $in_same_term, $excluded_terms, true, $taxonomy );
        }//2123 from link-template
        /**
         * @description Retrieves the boundary post.
         * @param bool $in_same_term
         * @param array|string $excluded_terms
         * @param bool $start
         * @param string $taxonomy
         * @return string
         */
        protected function _get_boundary_post( $in_same_term = false, $excluded_terms = '', $start = true, $taxonomy = 'category' ):string{
            $post = $this->_get_post();
            if ( ! $post || ! $this->_is_single() || $this->_is_attachment() || ! $this->_taxonomy_exists( $taxonomy ) )
                return null;
            $query_args = ['posts_per_page' => 1,'order' => $start ? 'ASC' : 'DESC','update_post_term_cache' => false,'update_post_meta_cache' => false,];
            $term_array = [];
            if ( ! is_array( $excluded_terms ) ) {
                if ( ! empty( $excluded_terms ) ) $excluded_terms = explode( ',', $excluded_terms );
                else $excluded_terms = array();
            }
            if ( $in_same_term || ! empty( $excluded_terms ) ) {
                if ( $in_same_term ) $term_array = $this->_tp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
                if ( ! empty( $excluded_terms ) ) {
                    $excluded_terms = array_map( 'intval', $excluded_terms );
                    $excluded_terms = array_diff( $excluded_terms, $term_array );
                    $inverse_terms = [];
                    foreach ( $excluded_terms as $excluded_term ) $inverse_terms[] = $excluded_term * -1;
                    $excluded_terms = $inverse_terms;
                }
                $query_args['tax_query'] = [['taxonomy' => $taxonomy,'terms' => array_merge( $term_array, $excluded_terms ),],];
            }
            return $this->_get_posts( $query_args );
        }// 2133from link-template
        /**
         * @description Retrieves the previous post link that is adjacent to the current post.
         * @param string $format
         * @param string $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         * @return mixed
         */
        protected function _get_previous_post_link( $format = '&laquo; %link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ){
            return $this->_get_adjacent_post_link( $format, $link, $in_same_term, $excluded_terms, true, $taxonomy );
        }//2195 from link-template
        /**
         * @description Displays the previous post link that is adjacent to the current post.
         * @param string $format
         * @param string $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         */
        protected function _previous_post_link( $format = '&laquo; %link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void{
            echo $this->_get_previous_post_link( $format, $link, $in_same_term, $excluded_terms, $taxonomy );
        }//2212 from link-template
    }
}else die;