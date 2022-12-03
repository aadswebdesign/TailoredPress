<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _link_template_06 {
        use _init_rewrite;
        use _init_pages;
        use _init_queries;
        /**
         * @description Retrieves the next post link that is adjacent to the current post.
         * @param string $format
         * @param string $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         * @return mixed
         */
        protected function _get_next_post_link( $format = '%link &raquo;', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ){
            return $this->_get_adjacent_post_link( $format, $link, $in_same_term, $excluded_terms, false, $taxonomy );
        }//2228 from link-template
        /**
         * @description Displays the next post link that is adjacent to the current post.
         * @param string $format
         * @param string $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param string $taxonomy
         */
        protected function _next_post_link( $format = '%link &raquo;', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void{
            echo $this->_get_next_post_link( $format, $link, $in_same_term, $excluded_terms, $taxonomy );
        }//2245 from link-template
        /**
         * @description Retrieves the adjacent post link.
         * @param $format
         * @param $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param bool $previous
         * @param string $taxonomy
         * @return mixed
         */
        protected function _get_adjacent_post_link( $format, $link, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ){
            if ( $previous && $this->_is_attachment() ) $post = $this->_get_post( $this->_get_post()->post_parent );
            else $post = $this->_get_adjacent_post( $in_same_term, $excluded_terms, $previous, $taxonomy );
            if ( ! $post ) $output = '';
            else {
                $title = $post->post_title;
                if ( empty( $post->post_title ) ) $title = $previous ? $this->__( 'Previous Post' ) :$this->__( 'Next Post' );
                $title = $this->_apply_filters( 'the_title', $title, $post->ID );
                $date = $this->_mysql2date( $this->_get_option( 'date_format' ), $post->post_date );
                $rel  = $previous ? 'prev' : 'next';
                $string = "<a href='{$this->_get_permalink( $post )}' rel='$rel'>";
                $inlink = str_replace(array('%title', '%date'), array($title, $date), $link);
                $inlink = $string . $inlink . '</a>';
                $output = str_replace( '%link', $inlink, $format );
            }
            $adjacent = $previous ? 'previous' : 'next';
            return $this->_apply_filters( "{$adjacent}_post_link", $output, $format, $link, $post, $adjacent );
        }//2264 from link-template
        /**
         * @description Can be either next post link or previous.
         * @param $format
         * @param $link
         * @param bool $in_same_term
         * @param string $excluded_terms
         * @param bool $previous
         * @param string $taxonomy
         */
        protected function _adjacent_post_link( $format, $link, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):void{
            echo $this->_get_adjacent_post_link( $format, $link, $in_same_term, $excluded_terms, $previous, $taxonomy );
        }//2333 from link-template
        /**
         * @description Retrieves the link for a page number.
         * @param int $pagenum
         * @param bool $escape
         * @return mixed
         */
        protected function _get_page_num_link($pagenum = 1,$escape = true){
            $tp_rewrite = $this->_init_rewrite();
            $pagenum = (int) $pagenum;
            $request = $this->_remove_query_arg( 'paged' );
            $home_root = parse_url( $this->_home_url() );
            $home_root = $home_root['path'] ?? '';
            $home_root = preg_quote( $home_root, '|' );
            $request = preg_replace( '|^' . $home_root . '|i', '', $request );
            $request = ltrim($request, '/');
            if ( ! $tp_rewrite->using_permalinks() || $this->_is_admin() ) {
                $base = $this->_trailingslashit( $this->_get_bloginfo( 'url' ) );
                if ( $pagenum > 1 ) $result = $this->_add_query_arg( 'paged', $pagenum, $base . $request );
                else $result = $base . $request;
            } else {
                $qs_regex = '|\?.*?$|';
                preg_match( $qs_regex, $request, $qs_match );
                if ( ! empty( $qs_match[0] ) ) {
                    $query_string = $qs_match[0];
                    $request      = preg_replace( $qs_regex, '', $request );
                } else $query_string = '';
                $request = preg_replace( "|$tp_rewrite->pagination_base/\d+/?$|", '', $request );
                $request = preg_replace( '|^' . preg_quote( $tp_rewrite->index, '|' ) . '|i', '', $request );
                $request = ltrim( $request, '/' );
                $base = $this->_trailingslashit( $this->_get_bloginfo( 'url' ) );
                if (( $pagenum > 1 || '' !== $request ) && $tp_rewrite->using_index_permalinks()) $base .= $tp_rewrite->index . '/';
                if ( $pagenum > 1 ) $request = ( ( ! empty( $request ) ) ? $this->_trailingslashit( $request ) : $request ) . $this->_user_trailingslashit( $tp_rewrite->pagination_base . '/' . $pagenum, 'paged' );
                $result = $base . $request . $query_string;
            }
            $result = $this->_apply_filters( 'get_pagenum_link', $result, $pagenum );
            if ( $escape ) return $this->_esc_url( $result );
            else return $this->_esc_url_raw( $result );
        }//2349 from link-template
        /**
         *  @description Retrieves the next posts page link.
         * @param int $max_page
         * @return mixed
         */
        protected function _get_next_posts_page_link( $max_page = 0 ){
            if ( ! $this->_is_single() ) {
                if ( ! $this->tp_paged ) $this->tp_paged = 1;
                $nextpage = (int) $this->tp_paged + 1;
                if ( ! $max_page || $max_page >= $nextpage ) return $this->_get_page_num_link( $nextpage );
            }
            return false;
        }//2429 from link-template
        /**
         *  @description Displays or retrieves the next posts page link.
         * @param int $max_page
         * @return mixed
         */
        protected function _get_next_posts( $max_page = 0){
            return $this->_esc_url( $this->_get_next_posts_page_link( $max_page) );
        }//2452 from link-template
        protected function _next_posts( $max_page = 0 ):void{
            echo $this->_get_next_posts( $max_page);
        }//added
        /**
         * @description Retrieves the next posts page link.
         * @param null $label
         * @param int $max_page
         * @return string
         */
        protected function _get_next_posts_link( $label = null, $max_page = 0 ):string{
            $paged = $this->tp_paged;
            $link = '';
            $tp_query = $this->_init_query();
            if ( ! $max_page ) $max_page = $tp_query->max_num_pages;
            if ( ! $paged ) $paged = 1;
            $nextpage = $paged + 1;
            if ( null === $label ) $label = $this->__( 'Next Page &raquo;' );
            if (( $nextpage <= $max_page ) && ! $this->_is_single()) {
                $attr = $this->_apply_filters( 'next_posts_link_attributes', '' );
                $link = "<a href='{$$this->_get_next_posts( $max_page, false )}\{$attr}'>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) ."</a>";
            }
            return $link;
        }//2474 from link-template
        /**
         * @description Displays the next posts page link.
         * @param null $label
         * @param int $max_page
         */
        protected function _next_posts_link( $label = null, $max_page = 0 ):void{
            echo $this->_get_next_posts_link( $label, $max_page );
        }//2513 from link-template
        /**
         * @description Retrieves the previous posts page link.
         * @return mixed|string
         */
        protected function _get_previous_posts_page_link(){
            $paged = $this->tp_paged;
            $get_page_num_link = '';
            if ( ! $this->_is_single() ) {
                $nextpage = (int) $paged - 1;
                if ( $nextpage < 1 ) $nextpage = 1;
                $get_page_num_link = $this->_get_page_num_link( $nextpage );
            }
            return $get_page_num_link;
        }//2530 from link-template
    }
}else die;