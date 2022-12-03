<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _post_09 {
        use _init_db;
        /**
         * @description Add a URL to those already pinged.
         * @param $post_id
         * @param $uri
         * @return bool
         */
        protected function _add_ping( $post_id, $uri ):bool{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $pung = trim( $post->pinged );
            $pung = preg_split( '/\s/', $pung );
            if ( is_array( $uri ) ) $pung = array_merge( $pung, $uri );
            else  $pung[] = $uri;
            $new = implode( "\n", $pung );
            $new = $this->_apply_filters( 'add_ping', $new );
            $return = $this->tpdb->update( $this->tpdb->posts, array( 'pinged' => $new ), array( 'ID' => $post->ID ) );
            $this->_clean_post_cache( $post->ID );
            return $return;
        }//5427
        /**
         * @description Retrieve enclosures already enclosed for a post.
         * @param $post_id
         * @return array
         */
        protected function _get_enclosed( $post_id ):array{
            $custom_fields = $this->_get_post_custom( $post_id );
            $pung = [];
            if ( ! is_array( $custom_fields ) ) return $pung;
            foreach ( $custom_fields as $key => $val ) {
                if ( 'enclosure' !== $key || ! is_array( $val ) ) continue;
                foreach ( $val as $enc ) {
                    $enclosure = explode( "\n", $enc );
                    $pung[]    = trim( $enclosure[0] );
                }
            }
            return $this->_apply_filters( 'get_enclosed', $pung, $post_id );
        }//5468
        /**
         * @description Retrieve URLs already pinged for a post.
         * @param $post_id
         * @return bool
         */
        protected function _get_pung( $post_id ):bool{
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $pung = trim( $post->pinged );
            $pung = preg_split( '/\s/', $pung );
            return $this->_apply_filters( 'get_pung', $pung );
        }//5506
        /**
         * @description Retrieve URLs that need to be pinged.
         * @param $post_id
         * @return bool
         */
        protected function _get_to_ping( $post_id ):bool{
            $post = $this->_get_post( $post_id );
            if ( ! $post )  return false;
            $to_ping = $this->_sanitize_trackback_urls( $post->to_ping );
            $to_ping = preg_split( '/\s/', $to_ping, -1, PREG_SPLIT_NO_EMPTY );
            return $this->_apply_filters( 'get_to_ping', $to_ping );
        }//5535
        /**
         * @description Do trackbacks for a list of URLs.
         * @param $tb_list
         * @param $post_id
         */
        protected function _trackback_url_list( $tb_list, $post_id ):void{
            if ( ! empty( $tb_list ) ) {
                $postdata = $this->_get_post( $post_id, ARRAY_A );
                $excerpt = strip_tags($postdata['post_excerpt'] ?: $postdata['post_content']);
                if ( strlen( $excerpt ) > 255 ) $excerpt = substr( $excerpt, 0, 252 ) . '&hellip;';
                $trackback_urls = explode( ',', $tb_list );
                foreach ($trackback_urls as $tb_url ) {
                    $tb_url = trim( $tb_url );
                    $this->_trackback( $tb_url, $this->_tp_unslash( $postdata['post_title'] ), $excerpt, $post_id );
                }
            }
        }//5563
        /**
         * @description Get a list of page IDs.
         * @return array
         */
        protected function _get_all_page_ids():array{
            $this->tpdb = $this->_init_db();
            $page_ids = $this->_tp_cache_get( 'all_page_ids', 'posts' );
            if ( ! is_array( $page_ids ) ) {
                $page_ids = $this->tpdb->get_col( TP_SELECT . " ID FROM $this->tpdb->posts WHERE post_type = 'page'" );
                $this->_tp_cache_add( 'all_page_ids', $page_ids, 'posts' );
            }
            return $page_ids;
        }//5596
        /**
         * @description Retrieves a page given its path.
         * @param $page_path
         * @param string $output
         * @param mixed $post_type
         * @return mixed
         */
        protected function _get_page_by_path( $page_path, $output = OBJECT, $post_type = 'page' ){
            $this->tpdb = $this->_init_db();
            $last_changed = $this->_tp_cache_get_last_changed( 'posts' );
            $hash      = md5( $page_path . serialize( $post_type ) );
            $cache_key = "get_page_by_path:$hash:$last_changed";
            $cached    = $this->_tp_cache_get( $cache_key, 'posts' );
            if ( false !== $cached ) {
                // Special case: '0' is a bad `$page_path`.
                if ( '0' === $cached || 0 === $cached ) return null;
                else return $this->_get_post( $cached, $output );
            }
            $page_path     = rawurlencode( urldecode( $page_path ) );
            $page_path     = str_replace( '%2F', '/', $page_path );
            $page_path     .= str_replace( '%20', ' ', $page_path );
            $parts         = explode( '/', trim( $page_path, '/' ) );
            $parts         = array_map( 'sanitize_title_for_query', $parts );
            $escaped_parts = $this->_esc_sql( $parts );
            $in_string = "'" . implode( "','", $escaped_parts ) . "'";
            if ( is_array( $post_type ) ) $post_types = $post_type;
            else $post_types = array( $post_type, 'attachment' );
            $post_types          = $this->_esc_sql( $post_types );
            $post_type_in_string = "'" . implode( "','", $post_types ) . "'";
            $sql = TP_SELECT . "ID, post_name, post_parent, post_type FROM $this->tpdb->posts WHERE post_name IN ($in_string) AND post_type IN ($post_type_in_string)";
            $pages = $this->tpdb->get_results( $sql, OBJECT_K );
            $rev_parts = array_reverse( $parts );
            $found_id = 0;
            foreach ( (array) $pages as $page ) {
                if ( $page->post_name === $rev_parts[0] ){
                    $count = 0;
                    $p     = $page;
                    while ( 0 !== $p->post_parent && isset( $pages[ $p->post_parent ] ) ) {
                        $count++;
                        $parent = $pages[ $p->post_parent ];
                        if ( ! isset( $rev_parts[ $count ] ) || $parent->post_name !== $rev_parts[ $count ] ) break;
                        $p = $parent;
                    }
                    if ( 0 === $p->post_parent && count( $rev_parts ) === $count + 1 && $p->post_name === $rev_parts[ $count ] ) {
                        $found_id = $page->ID;
                        if ( $page->post_type === $post_type ) break;
                    }
                }
            }
            $this->_tp_cache_set( $cache_key, $found_id, 'posts' );
            if ( $found_id ) return $this->_get_post( $found_id, $output );
            return null;
        }//5642
        /**
         * @description Retrieve a page given its title.
         * @param $page_title
         * @param string $output
         * @param mixed $post_type
         * @return null
         */
        protected function _get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ){
            $this->tpdb = $this->_init_db();
            if ( is_array( $post_type ) ) {
                $post_type           = $this->_esc_sql( $post_type );
                $post_type_in_string = "'" . implode( "','", $post_type ) . "'";
                $sql = $this->tpdb->prepare( TP_SELECT . " ID	FROM $this->tpdb->posts WHERE post_title = %s	AND post_type IN ($post_type_in_string)", $page_title);
            } else $sql = $this->tpdb->prepare( TP_SELECT . " ID	FROM $this->tpdb->posts WHERE post_title = %s	AND post_type = %s ", $page_title, $post_type);
            $page = $this->tpdb->get_var( $sql );
            if ( $page )  return $this->_get_post( $page, $output );
            return null;
        }//5747
        /**
         * @description Identify descendants of a given page ID in a list of page objects.
         * @param $page_id
         * @param $pages
         * @return array
         */
        protected function _get_page_children( $page_id, $pages ):array{
            $children = [];
            foreach ( (array) $pages as $page )
                $children[ (int) $page->post_parent ][] = $page;
            $page_list = [];
            if ( isset( $children[ $page_id ] ) ) {
                $to_look = array_reverse( $children[ $page_id ] );
                while ( $to_look ) {
                    $p           = array_pop( $to_look );
                    $page_list[] = $p;
                    if ( isset( $children[ $p->ID ] ) ) {
                        foreach ( array_reverse( $children[ $p->ID ] ) as $child ) {
                            // Append to the `$to_look` stack to descend the tree.
                            $to_look[] = $child;
                        }
                    }
                }
            }
            return $page_list;
        }//5795
        /**
         * @description Order the pages with children under parents in a flat list.
         * @param $pages
         * @param int $page_id
         * @return array
         */
        protected function _get_page_hierarchy( &$pages, $page_id = 0 ):array{
            if ( empty( $pages ) ) return array();
            $children = array();
            foreach ( (array) $pages as $p ) {
                $parent_id = (int) $p->post_parent;
                $children[ $parent_id ][] = $p;
            }
            $result = [];
            $this->_page_traverse_name( $page_id, $children, $result );
            return $result;
        }//5836
    }
}else die;