<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-5-2022
 * Time: 23:02
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    trait _rewrite {
        use _init_rewrite,_init_core,_init_queries;
        /**
         * @param $regex
         * @param $query
         * @param string $after
         */
        protected function _add_rewrite_rule( $regex, $query, $after = 'bottom' ):void {
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_rewrite->add_rule( $regex, $query, $after );
        }//140 //rewrite
        /**
         * @param $tag
         * @param $regex
         * @param string $query
         */
        protected function _add_rewrite_tag( $tag, $regex, $query = '' ):void{
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_core = $this->_init_core();
            if ( strlen( $tag ) < 3 || '%' !== $tag[0] || '%' !== $tag[ strlen( $tag ) - 1 ] ) {
                return;
            }
            if ( empty( $query ) ) {
                $qv = trim( $tag, '%' );
                $this->tp_core->add_query_var( $qv );
                $query = $qv . '=';
            }
            $this->tp_rewrite->add_rewrite_tag( $tag, $regex, $query );
        } //rewrite
        protected function _remove_rewrite_tag( $tag ):void{
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_rewrite->remove_rewrite_tag( $tag );
        } //rewrite
        protected function _add_permastruct( $name, $structure, ...$args):void {
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_rewrite->add_permanent_structure( $name, $structure, $args );
        } //rewrite
        protected function _remove_permastruct( $name ):void {
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_rewrite->remove_permanent_structures( $name );
        } //rewrite
        /**
         * @param $feed_name
         * @param $function
         * @return string
         */
        protected function _add_feed( $feed_name, $function ):string{
            $this->tp_rewrite = $this->_init_rewrite();
            if ( ! in_array( $feed_name, $this->tp_rewrite->feeds, true ) )
                 $this->tp_rewrite->feeds[] = $feed_name;
            $hook = 'do_feed_' . $feed_name;
            $this->_remove_action( $hook, $hook );
            $this->_add_action( $hook, $function, 10, 2 );
            return $hook;
        } //rewrite
        protected function _flush_rewrite_rules( $hard = true ):void{
            $this->tp_rewrite = $this->_init_rewrite();
           if ( is_callable( [$this->tp_rewrite, 'flush_rules'] ) )
                $this->tp_rewrite->flush_rules( $hard );
        } //rewrite
        /**
         * @param $name
         * @param $places
         * @param bool $query_var
         */
        protected function _add_rewrite_endpoint( $name, $places, $query_var = true ):void {
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_rewrite->add_endpoint( $name, $places, $query_var );
        } //rewrite
        /**
         * @param $base
         * @return mixed
         */
        protected function _tp_filter_taxonomy_base( $base ){
            if ( ! empty( $base ) ) {
                $base = preg_replace( '|^/index\.php/|', '', $base );
                $base = trim( $base, '/' );
            }
            return $base;
        } //rewrite
        protected function _tp_resolve_numeric_slug_conflicts( $query_vars =[]):array {
            if ( ! isset( $query_vars['year'] ) && ! isset( $query_vars['monthnum'] ) && ! isset( $query_vars['day'] ) )
                return $query_vars;
            $permastructs   = array_values( array_filter( explode( '/', $this->_get_option( 'permalink_structure' ) ) ) );
            $postname_index = array_search( '%postname%', $permastructs, true );
            if ( false === $postname_index ) return $query_vars;
            $compare = '';
            if ( 0 === $postname_index && ( isset( $query_vars['year'] ) || isset( $query_vars['monthnum'] ) ) )
                $compare = 'year';
            elseif ( $postname_index && '%year%' === $permastructs[ $postname_index - 1 ] && ( isset( $query_vars['monthnum'] ) || isset( $query_vars['day'] ) ) )
                $compare = 'monthnum';
            elseif ( $postname_index && '%monthnum%' === $permastructs[ $postname_index - 1 ] && isset( $query_vars['day'] ) )
                $compare = 'day';
            if ( ! $compare ) return $query_vars;
            $value = $query_vars[ $compare ];
            $post = $this->_get_page_by_path( $value, OBJECT, 'post' );
            if ( ! ( $post instanceof TP_Post ) ) {
                return $query_vars;
            }
            if ( isset( $query_vars['year'] ) && ( 'monthnum' === $compare || 'day' === $compare ) && preg_match( '/^(\d{4})\-(\d{2})/', $post->post_date, $matches )) {
                // $matches[1] is the year the post was published.
                if ( (int) $query_vars['year'] !== (int) $matches[1] )  return $query_vars;
                if ( 'day' === $compare && isset( $query_vars['monthnum'] ) && (int) $query_vars['monthnum'] !== (int) $matches[2] )
                    return $query_vars;
            }
            $maybe_page = '';
            if ( 'year' === $compare && isset( $query_vars['monthnum'] ) )
                $maybe_page = $query_vars['monthnum'];
            elseif ( 'monthnum' === $compare && isset( $query_vars['day'] ) )
                $maybe_page = $query_vars['day'];
            $maybe_page = (int) trim( $maybe_page, '/' );
            $post_page_count = substr_count( $post->post_content, '<!--nextpage-->' ) + 1;
            if ( 1 === $post_page_count && $maybe_page ) return $query_vars;
            if ( $post_page_count > 1 && $maybe_page > $post_page_count ) return $query_vars;
            if ( '' !== $maybe_page ) $query_vars['page'] = $maybe_page;
            unset( $query_vars['year'], $query_vars['monthnum'], $query_vars['day'] );
            $query_vars['name'] = $post->post_name;
            return $query_vars;
        } //rewrite
        protected function _url_to_postid( $url ):int{
            $url = $this->_apply_filters( 'url_to_postid', $url );
            $url_host      = str_replace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
            $home_url_host = str_replace( 'www.', '', parse_url( $this->_home_url(), PHP_URL_HOST ) );
            if ( $url_host && $url_host !== $home_url_host ) return 0;
            if ( preg_match( '#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values ) ) {
                $id = $this->_abs_int( $values[2] );
                if ( $id ) return $id;
            }
            $url_split = explode( '#', $url );
            $url       = $url_split[0];
            $url_split = explode( '?', $url );
            $url       = $url_split[0];
            $scheme = parse_url( $this->_home_url(), PHP_URL_SCHEME );
            $url    = $this->_set_url_scheme( $url, $scheme );
            if (false === strpos( $url, '://www.' ) && false !== strpos( $this->_home_url(), '://www.' ))
                $url = str_replace( '://', '://www.', $url );
            if ( false === strpos( $this->_home_url(), '://www.' ) )
                $url = str_replace( '://www.', '://', $url );
            if ('page' === $this->_get_option( 'show_on_front' ) && trim( $url, '/' ) === $this->_home_url()) {
                $page_on_front = $this->_get_option( 'page_on_front' );
                if ( $page_on_front && $this->_get_post( $page_on_front ) instanceof TP_Post )
                    return (int) $page_on_front;
            }
            $this->tp_rewrite = $this->_init_rewrite();
            $rewrite = $this->tp_rewrite->tp_rewrite_rules();
            if ( empty( $rewrite ) ) return 0;
            if ( ! $this->tp_rewrite->using_index_permalinks() )
                $url = str_replace( $this->tp_rewrite->index . '/', '', $url );
            if ( false !== strpos( $this->_trailingslashit( $url ), $this->_home_url( '/' ) ) ) {
                // Chop off http://domain.com/[path].
                $url = str_replace( $this->_home_url(), '', $url );
            } else {
                $home_path = parse_url( $this->_home_url( '/' ) );
                $home_path = $home_path['path'] ?? '';
                $url       = preg_replace( sprintf( preg_quote('#^%s#', $home_path ) ), '', $this->_trailingslashit( $url ) );
            }
            $url = trim( $url, '/' );
            $request              = $url;
            $post_type_query_vars = array();
            foreach ( $this->_get_post_types( array(), 'objects' ) as $post_type => $t ) {
                if ( ! empty( $t->query_var ) ) $post_type_query_vars[ $t->query_var ] = $post_type;
            }
            $request_match = $request;
            foreach ( (array) $rewrite as $match => $query ) {
                if ( ! empty( $url ) && ( $url !== $request ) && ( strpos( $match, $url ) === 0 ) )
                    $request_match = $url . '/' . $request;
                 if ( preg_match( "#^$match#", $request_match, $matches ) ) {
                    if ( $this->tp_rewrite->use_verbose_page_rules && preg_match( '/pagename=\$matches\[(\d+)\]/', $query, $varmatch ) ) {
                        // This is a verbose page match, let's check to be sure about it.
                        $page = $this->_get_page_by_path( $matches[ $varmatch[1] ] );
                        if ( ! $page ) continue;
                        $post_status_obj = $this->_get_post_status_object( $page->post_status );
                        if ( ! $post_status_obj->public && ! $post_status_obj->protected
                            && ! $post_status_obj->private && $post_status_obj->exclude_from_search ) {
                            continue;
                        }
                    }
                    $query = preg_replace( '!^.+\?!', '', $query );
                    parse_str( $query, $query_vars );
                    $query = [];
                    foreach ( (array) $query_vars as $key => $value ) {
                        if ( in_array( (string) $key,  $this->tp_core->public_query_vars, true ) ) {
                            $query[ $key ] = $value;
                            if ( isset( $post_type_query_vars[ $key ] ) ) {
                                $query['post_type'] = $post_type_query_vars[ $key ];
                                $query['name']      = $value;
                            }
                        }
                    }
                    $query = $this->_tp_resolve_numeric_slug_conflicts( $query );
                    $query = new TP_Query( $query );
                    if ( ! empty( $query->posts ) && $query->is_singular )
                        return $query->post->ID;
                    else return 0;
                }
            }
            return 0;
        } //rewrite
    }
}else die;