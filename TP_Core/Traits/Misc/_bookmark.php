<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-5-2022
 * Time: 12:48
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _bookmark{
        use _init_db;
        use _init_error;
        /**
         * @description Retrieve Bookmark data
         * @param $bookmark
         * @param string $output
         * @param string $filter
         * @return array|mixed|null|string
         */
        protected function _get_bookmark( $bookmark, $output = OBJECT, $filter = 'raw' ){
            $this->tpdb = $this->_init_db();
            if ( empty( $bookmark ) ) {
                if ( isset( $this->tp_link ) )
                    $_bookmark = & $this->tp_link;
                else $_bookmark = null;
            } elseif ( is_object( $bookmark ) ) {
                $this->_tp_cache_add( $bookmark->link_id, $bookmark, 'bookmark' );
                $_bookmark = $bookmark;
            } else if ( isset( $this->tp_link ) && ( $this->tp_link->link_id === $bookmark ) ) {
                $_bookmark = & $this->tp_link;
            } else {
                $_bookmark =  $this->_tp_cache_get( $bookmark, 'bookmark' );
                if ( ! $_bookmark ) {
                    $_bookmark = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT ." * FROM $this->tpdb->links WHERE link_id = %d LIMIT 1", $bookmark ) );
                    if ( $_bookmark ) {
                        if($_bookmark instanceof TP_Post){
                            $_bookmark->link_category = array_unique(  $this->_tp_get_object_terms( $_bookmark->link_id, 'link_category', array( 'fields' => 'ids' ) ) );
                        }
                        $this->_tp_cache_add( $_bookmark->link_id, $_bookmark, 'bookmark' );
                    }
                }
            }
            if ( ! $_bookmark ) return $_bookmark;
            $_bookmark = $this->_sanitize_bookmark( $_bookmark, $filter );
            if ( OBJECT === $output ) return $_bookmark;
            elseif ( ARRAY_A === $output ) return get_object_vars( $_bookmark );
            elseif ( ARRAY_N === $output ) return array_values( get_object_vars( $_bookmark ) );
            else return $_bookmark;
        }//23
        /**
         * @description Retrieve single bookmark data item or field.
         * @param $field
         * @param $bookmark
         * @param string $context
         * @return array|int|mixed|null|string
         */
        protected function _get_bookmark_field( $field, $bookmark, $context = 'display' ){
            $bookmark = (int) $bookmark;
            $bookmark = $this->_get_bookmark( $bookmark );
            if ( $this->_init_error( $bookmark ) ) return $bookmark;
            if ( ! is_object( $bookmark ) ) return '';
            if ( ! isset( $bookmark->$field ) ) return '';
            return $this->_sanitize_bookmark_field( $field, $bookmark->$field, $bookmark->link_id, $context );
        }//77
        /**
         * @description Retrieves the list of bookmarks
         * @param string $args
         * @return mixed
         */
        protected function _get_bookmarks( $args = '' ){
            $this->tpdb = $this->_init_db();
            $defaults = [
                'orderby' => 'name','order' => 'ASC','limit' => -1,'category' => '','category_name' => '',
                'hide_invisible' => 1,'show_updated' => 0,'include' => '','exclude' => '', 'search' => '',
            ];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $key   = md5( serialize( $parsed_args ) );
            $cache = $this->_tp_cache_get( 'get_bookmarks', 'bookmark' );
            if ('rand' !== $parsed_args['orderby'] && $cache && is_array($cache) && isset($cache[$key])) {
                $bookmarks = $cache[ $key ];
                return $this->_apply_filters( 'get_bookmarks', $bookmarks, $parsed_args );
            }
            if ( ! is_array( $cache ) ) $cache = [];
            $inclusions = '';
            if ( ! empty( $parsed_args['include'] ) ) {
                $parsed_args['exclude']       = '';  // Ignore exclude, category, and category_name params if using include.
                $parsed_args['category']      = '';
                $parsed_args['category_name'] = '';
                $inc_links = $this->_tp_parse_id_list( $parsed_args['include'] );
                if ( count( $inc_links ) ) {
                    foreach ( $inc_links as $inc_link ) {
                        if ( empty( $inclusions ) )
                            $inclusions = ' AND ( link_id = ' . $inc_link . ' ';
                        else $inclusions .= ' OR link_id = ' . $inc_link . ' ';
                    }
                }
            }
            if ( ! empty( $inclusions ) ) $inclusions .= ')';
            $exclusions = '';
            if ( ! empty( $parsed_args['exclude'] ) ) {
                $ex_links = $this->_tp_parse_id_list( $parsed_args['exclude'] );
                if ( count( $ex_links ) ) {
                    foreach ( $ex_links as $ex_link ) {
                        if ( empty( $exclusions ) )
                            $exclusions = ' AND ( link_id <> ' . $ex_link . ' ';
                        else $exclusions .= ' AND link_id <> ' . $ex_link . ' ';
                    }
                }
            }
            if ( ! empty( $exclusions ) )  $exclusions .= ')';
            if ( ! empty( $parsed_args['category_name'] ) ) {
                $parsed_args['category'] = $this->_get_term_by( 'name', $parsed_args['category_name'], 'link_category' );
                if ( $parsed_args['category'] )
                    $parsed_args['category'] = $parsed_args['category']->term_id;
                else {
                    $cache[ $key ] = array();
                    $this->_tp_cache_set( 'get_bookmarks', $cache, 'bookmark' );
                    return $this->_apply_filters( 'get_bookmarks', array(), $parsed_args );
                }
            }
            $search = '';
            if ( ! empty( $parsed_args['search'] ) ) {
                $like   = '%' . $this->tpdb->esc_like( $parsed_args['search'] ) . '%';
                $search = $this->tpdb->prepare( ' AND ( (link_url LIKE %s) OR (link_name LIKE %s) OR (link_description LIKE %s) ) ', $like, $like, $like );
            }
            $category_query = '';
            $join           = '';
            if ( ! empty( $parsed_args['category'] ) ) {
                $in_categories = $this->_tp_parse_id_list( $parsed_args['category'] );
                if ( count( $in_categories ) ) {
                    foreach ( $in_categories as $in_cat ) {
                        if ( empty( $category_query ) )
                            $category_query = ' AND ( tt.term_id = ' . $in_cat . ' ';
                        else $category_query .= ' OR tt.term_id = ' . $in_cat . ' ';
                    }
                }
            }
            if ( ! empty( $category_query ) ) {
                $category_query .= ") AND taxonomy = 'link_category'";
                $join            = " INNER JOIN $this->tpdb->term_relationships AS tr ON ($this->tpdb->links.link_id = tr.object_id) INNER JOIN $this->tpdb->term_taxonomy as tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            }
            if ( $parsed_args['show_updated'] )
                $recently_updated_test = ', IF (DATE_ADD(link_updated, INTERVAL 120 MINUTE) >= NOW(), 1,0) as recently_updated ';
            else $recently_updated_test = '';
            $get_updated = ( $parsed_args['show_updated'] ) ? ', UNIX_TIMESTAMP(link_updated) AS link_updated_f ' : '';
            $orderby = strtolower( $parsed_args['orderby'] );
            $length  = '';
            switch ( $orderby ) {
                case 'length':
                    $length = ', CHAR_LENGTH(link_name) AS length';
                    break;
                case 'rand':
                    $orderby = 'rand()';
                    break;
                case 'link_id':
                    $orderby = "$this->tpdb->links.link_id";
                    break;
                default:
                    $order_params = [];
                    $keys        = array( 'link_id', 'link_name', 'link_url', 'link_visible', 'link_rating', 'link_owner', 'link_updated', 'link_notes', 'link_description' );
                    foreach ( explode( ',', $orderby ) as $ord_param ) {
                        $ord_param = trim( $ord_param );
                        if ( in_array( 'link_' . $ord_param, $keys, true ) )
                            $order_params[] = 'link_' . $ord_param;
                        elseif ( in_array( $ord_param, $keys, true ) )
                            $order_params[] = $ord_param;
                    }
                    $orderby = implode( ',', $order_params );
            }
            if ( empty( $orderby ) ) $orderby = 'link_name';
            $order = strtoupper( $parsed_args['order'] );
            if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ), true ) )
                $order = 'ASC';
            $visible = '';
            if ( $parsed_args['hide_invisible'] )
                $visible = "AND link_visible = 'Y'";
            $query  = TP_SELECT . " * $length $recently_updated_test $get_updated FROM $this->tpdb->links $join WHERE 1=1 $visible $category_query";
            $query .= " $exclusions $inclusions $search";
            $query .= " ORDER BY $orderby $order";
            if ( -1 !== $parsed_args['limit'] )
                $query .= ' LIMIT ' . $parsed_args['limit'];
            $results = $this->tpdb->get_results( $query );
            if ( 'rand()' !== $orderby ) {
                $cache[ $key ] = $results;
                $this->_tp_cache_set( 'get_bookmarks', $cache, 'bookmark' );
            }
            return $this->_apply_filters( 'get_bookmarks', $results, $parsed_args );
        }//135
        /**
         * @description Sanitizes all bookmark fields.
         * @param $bookmark
         * @param string $context
         * @return mixed
         */
        protected function _sanitize_bookmark( $bookmark, $context = 'display' ){
            $fields = [
                'link_id','link_url','link_name','link_image','link_target','link_category',
                'link_description','link_visible','link_owner','link_rating',
                'link_updated','link_rel','link_notes','link_rss',
            ];
            if ( is_object( $bookmark ) ) {
                $do_object = true;
                $link_id   = $bookmark->link_id;
            } else {
                $do_object = false;
                $link_id   = $bookmark['link_id'];
            }
            foreach ( $fields as $field ) {
                if ( $do_object ) {
                    if ( isset( $bookmark->$field ) )
                        $bookmark->$field = $this->_sanitize_bookmark_field( $field, $bookmark->$field, $link_id, $context );
                } else if ( isset( $bookmark[ $field ] ) )
                    $bookmark[ $field ] = $this->_sanitize_bookmark_field( $field, $bookmark[ $field ], $link_id, $context );
            }
            return $bookmark;
        }//333
        /**
         * @description Sanitizes a bookmark field.
         * @param $field
         * @param $value
         * @param $bookmark_id
         * @param $context
         * @return array|int|mixed|string
         */
        protected function _sanitize_bookmark_field( $field, $value, $bookmark_id, $context ){
            $int_fields = array( 'link_id', 'link_rating' );
            if ( in_array( $field, $int_fields, true ) )  $value = (int) $value;
            switch ( $field ) {
                case 'link_category': // array( ints )
                    $value = array_map( 'absint', (array) $value );
                    return $value;
                case 'link_visible': // bool stored as Y|N
                    $value = preg_replace( '/[^YNyn]/', '', $value );
                    break;
                case 'link_target': // "enum"
                    $targets = array( '_top', '_blank' );
                    if ( ! in_array( $value, $targets, true ) ) $value = '';
                     break;
            }
            if ( 'raw' === $context ) return $value;
            if ( 'edit' === $context ) {
                $value = $this->_apply_filters( "edit_{$field}", $value, $bookmark_id );
                if ( 'link_notes' === $field ) $value = $this->_esc_html( $value ); // textarea_escaped
                else $value = $this->_esc_attr( $value );
            } elseif ( 'db' === $context ) $value = $this->_apply_filters( "pre_{$field}", $value );
            else {
                $value = $this->_apply_filters("{(string)$field}", $value, $bookmark_id, $context );
                if ( 'attribute' === $context ) $value = $this->_esc_attr( $value );
                 elseif ( 'js' === $context ) $value = $this->_esc_js( $value );
            }
            if ( in_array( $field, $int_fields, true ) ) $value = (int) $value;
            return $value;
        }//398
        /**
         * @description Deletes the bookmark cache.
         * @param $bookmark_id
         */
        protected function _clean_bookmark_cache( $bookmark_id ):void{
            $this->_tp_cache_delete( $bookmark_id, 'bookmark' );
            $this->_tp_cache_delete( 'get_bookmarks', 'bookmark' );
            $this->_clean_object_term_cache( $bookmark_id, 'link' );
        }//464
    }
}else die;