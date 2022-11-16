<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _methods_03{
        use _init_db;
        /**
         * @description Check content for video and audio links to add as enclosures.
         * @param $content
         * @param $post
         * @return bool
         */
        protected function _do_enclose( $content, $post ):bool{
            $post = $this->_get_post( $post );
            $this->tpdb = $this->_init_db();
            if ( ! $post ) return false;
            if ( null === $content )
                $content = $post->post_content;
            $post_links = [];
            $pung = $this->_get_enclosed( $post->ID );
            $post_links_temp = $this->_tp_extract_urls( $content );
            foreach ( $pung as $link_test ) {
                if ( ! in_array( $link_test, $post_links_temp, true ) ) {
                    $meta_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " meta_id FROM " . $this->tpdb->post_meta . " WHERE post_id = %d AND meta_key = 'enclosure' AND meta_value LIKE %s", $post->ID, $this->tpdb->esc_like( $link_test ) . '%' ) );
                    foreach ( $meta_ids as $meta_id )$this->_delete_metadata_by_mid( 'post', $meta_id );
                }
            }
            foreach ( (array) $post_links_temp as $link_test ) {
                // If we haven't pung it already.
                if ( ! in_array( $link_test, $pung, true ) ) {
                    $test = parse_url( $link_test );
                    if ( false === $test ) continue;
                    if ( isset( $test['query'] ) ) $post_links[] = $link_test;
                    elseif ( isset( $test['path'] ) && ( '/' !== $test['path'] ) && ( '' !== $test['path'] ) )
                        $post_links[] = $link_test;
                }
            }
            $post_links = $this->_apply_filters( 'enclosure_links', $post_links, $post->ID );
            foreach ( (array) $post_links as $url ) {
                $url = $this->_strip_fragment_from_url( $url );
                if ( '' !== $url && ! $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " post_id FROM ". $this->tpdb->post_meta ." WHERE post_id = %d AND meta_key = 'enclosure' AND meta_value LIKE %s", $post->ID, $this->tpdb->esc_like( $url ) . '%' ) ) ) {
                    $headers = $this->_tp_get_http_headers( $url );
                    if ( $headers ) {
                        $len           = isset( $headers['content-length'] ) ? (int) $headers['content-length'] : 0;
                        $type          = $headers['content-type'] ?? '';
                        $allowed_types = array( 'video', 'audio' );
                        $url_parts = parse_url( $url );
                        if ( false !== $url_parts && ! empty( $url_parts['path'] ) ) {
                            $extension = pathinfo( $url_parts['path'], PATHINFO_EXTENSION );
                            if ( ! empty( $extension ) ) {
                                foreach ( $this->_tp_get_mime_types() as $exts => $mime ) {
                                    if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
                                        $type = $mime;
                                        $this->tp_mime[] = $mime;
                                        break;
                                    }
                                }
                            }
                        }
                        if ( in_array( substr( $type, 0, strpos( $type, '/' ) ), $allowed_types, true ) )
                            $this->_add_post_meta( $post->ID, 'enclosure', "$url\n$len\n$this->tp_mime\n" );
                    }
                }
            }
            return true;
        }//860
        /**
         * @description Retrieve HTTP Headers from URL.
         * @param $url
         * @return bool
         */
        protected function _tp_get_http_headers( $url):bool{
            $response = $this->_tp_safe_remote_head( $url );
            if ( $this->_init_error( $response ) ) return false;
            return $this->_tp_remote_retrieve_headers( $response );
        }//961
        /**
         * @description Determines whether the publish date of the current post in the loop is different
         * @description . from the publish date of the previous post in the loop.
         * @return int
         */
        protected function _is_new_day():int{
            if ( $this->tp_current_day !== $this->tp_previous_day ) return 1;
            else return 0;
        }//990
        /**
         * @description Build URL query based on an associative and, or indexed array.
         * @param $data
         * @return mixed
         */
        protected function _build_query( $data ){
            return $this->_http_build_query( $data, null, '&', '', false );
        }//1015
        /**
         * @description From php.net (modified by Mark Jaquith to behave like the native PHP5 function).
         * @param $data
         * @param null $prefix
         * @param null $sep
         * @param string $key
         * @param bool $url_encode
         * @return string
         */
        protected function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $url_encode = true ):string{
            $ret = [];
            foreach ( (array) $data as $k => $v ) {
                if ( $url_encode )  $k = urlencode( $k );
                if ( is_int( $k ) && null !== $prefix ) $k = $prefix . $k;
                if ( ! empty( $key ) ) $k = $key . '%5B' . $k . '%5D';
                if ( null === $v ) continue;
                elseif ( false === $v ) $v = '0';
                if ( is_array( $v ) || is_object( $v ) )
                    $ret[] = $this->_http_build_query($v, '', $sep, $k, $url_encode);
                elseif ( $url_encode ) $ret[] = $k . '=' . urlencode($v);
                else $ret[] = $k . '=' . $v;
            }
            if ( null === $sep ) $sep = ini_get( 'arg_separator.output' );
            return implode( $sep, $ret );
        }//1036
        /**
         * @description Retrieves a modified URL query string.
         * @param array ...$args
         * @return mixed
         */
        protected function _add_query_arg( ...$args ){
            if ( is_array( $args[0] ) ) {
                if ( count( $args ) < 2 || false === $args[1] )
                    $uri = $_SERVER['REQUEST_URI'];
                else $uri = $args[1];
            } else if ( count( $args ) < 3 || false === $args[2] )
                $uri = $_SERVER['REQUEST_URI'];
            else $uri = $args[2];
            $frag = strstr( $uri, '#' );
            if ( $frag ) $uri = substr( $uri, 0, -strlen( $frag ) );
            else $frag = '';
            if ( 0 === stripos( $uri, 'http://' ) ) {
                $protocol = 'http://';
                $uri      = substr( $uri, 7 );
            } elseif ( 0 === stripos( $uri, 'https://' ) ) {
                $protocol = 'https://';
                $uri      = substr( $uri, 8 );
            } else $protocol = '';
            if ( strpos( $uri, '?' ) !== false ) {
                @list( $base, $query ) = explode( '?', $uri, 2 );
                $base                .= '?';
            } elseif ( $protocol || strpos( $uri, '=' ) === false ) {
                $base  = $uri . '?';
                $query = '';
            } else {
                $base  = '';
                $query = $uri;
            }
            $this->_tp_parse_str( $query, $qs );
            $qs = $this->_url_encode_deep( $qs );
            if ( is_array( $args[0] ) ) {
                foreach ( $args[0] as $k => $v ) $qs[ $k ] = $v;
            } else $qs[ $args[0] ] = $args[1];
            foreach ( $qs as $k => $v ) {
                if ( false === $v ) unset( $qs[ $k ] );
            }
            $ret = $this->_build_query( $qs );
            $ret = trim( $ret, '?' );
            $ret = preg_replace( '#=(&|$)#', '$1', $ret );
            $ret = $protocol . $base . $ret . $frag;
            $ret = rtrim( $ret, '?' );
            $ret = str_replace( '?#', '#', $ret );
            return $ret;

        }//1108
        /**
         * @description Removes an item or items from a query string.
         * @param $key
         * @param bool $query
         * @return mixed
         */
        protected function _remove_query_arg( $key, $query = false ){
            if ( is_array( $key ) ) { // Removing multiple keys.
                foreach ( $key as $k ) $query = (bool)$this->_add_query_arg( $k, false, $query );
                return $query;
            }
            return $this->_add_query_arg( $key, false, $query );
        }//1185
        /**
         * @description Returns an array of single-use query variable names that can be removed from a URL.
         * @return mixed
         */
        protected function _tp_removable_query_args(){
            $removable_query_args = [
                'activate','activated','admin_email_remind_later','approved','core-major-auto-updates-saved',
                'deactivate','delete_count','deleted','disabled','doing_wp_cron','enabled','error',
                'hot_keys_highlight_first','hot_keys_highlight_last','ids','locked','message','same',
                'saved','settings-updated','skipped','spammed','trashed','un_spammed','un_trashed',
                'update','updated','tp-post-new-reload',
            ];
            return $this->_apply_filters( 'removable_query_args', $removable_query_args );
        }//1202
        /**
         * @description  Walks the array while sanitizing the contents.
         * @param $array
         * @return mixed
         */
        protected function _add_magic_quotes( $array ){
            foreach ( (array) $array as $k => $v ) {
                if ( is_array( $v ) ) $array[ $k ] = $this->_add_magic_quotes( $v );
                elseif ( is_string( $v ) ) $array[ $k ] = addslashes( $v );
                else continue;
            }
            return $array;
        }//1253
        /**
         * @description HTTP request for URI to retrieve content.
         * @param $uri
         * @return bool
         */
        protected function _tp_remote_fopen( $uri ):bool{
            $parsed_url = parse_url( $uri );
            if ( ! $parsed_url || ! is_array( $parsed_url ) ) return false;
            $options = [];
            $options['timeout'] = 10;
            $response = $this->_tp_safe_remote_get( $uri, $options );
            if ( $this->_init_error( $response ) ) return false;
            return $this->_tp_remote_retrieve_body( $response );
        }//1277
    }
}else die;