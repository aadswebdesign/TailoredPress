<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _post_10{
        use _init_db;
        use _init_pages;
        /**
         * @description Traverse and return all the nested children post names of a root page.
         * @param $page_id
         * @param $children
         * @param $result
         */
        protected function _page_traverse_name( $page_id, &$children, &$result ):void{
            if ( isset( $children[ $page_id ] ) ) {
                foreach ( (array) $children[ $page_id ] as $child ) {
                    $result[ $child->ID ] = $child->post_name;
                    $this->_page_traverse_name( $child->ID, $children, $result );
                }
            }
        }//5867
        /**
         * @description Build the URI path for a page.
         * @param mixed $page
         * @return bool
         */
        protected function _get_page_uri( $page = 0 ):bool{
            if ( ! $page instanceof TP_Post )  $page = $this->_get_post( $page );
            if ( ! $page ) return false;
            $uri = $page->post_name;
            foreach ( $page->ancestors as $parent ) {
                $parent = $this->_get_post( $parent );
                if ( $parent && $parent->post_name )  $uri = $parent->post_name . '/' . $uri;
            }
            return $this->_apply_filters( 'get_page_uri', $uri, $page );
        }//5887
        /**
         * @description Retrieve an array of pages (or hierarchical post type items).
         * @param array $args
         */
        protected function _get_pages( $args = [] ){
            $this->tpdb = $this->_init_db();
            $defaults = ['child_of' => 0,'sort_order' => 'ASC','sort_column' => 'post_title',
                'hierarchical' => 1,'exclude' => [],'include' => [],'meta_key' => '', 'meta_value' => '',
                'authors' => '','parent' => -1,'exclude_tree' => [],'number' => '','offset' => 0,
                'post_type' => 'page','post_status' => 'publish',
            ];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $number       = (int) $parsed_args['number'];
            $offset       = (int) $parsed_args['offset'];
            $child_of     = (int) $parsed_args['child_of'];
            $hierarchical = $parsed_args['hierarchical'];
            $exclude      = $parsed_args['exclude'];
            $meta_key     = $parsed_args['meta_key'];
            $meta_value   = $parsed_args['meta_value'];
            $parent       = $parsed_args['parent'];
            $post_status  = $parsed_args['post_status'];
            $hierarchical_post_types = $this->_get_post_types( array( 'hierarchical' => true ) );
            if ( ! in_array( $parsed_args['post_type'], $hierarchical_post_types, true ) )
                return false;
            if ( $parent > 0 && ! $child_of ) $hierarchical = false;
            if ( ! is_array( $post_status ) ) $post_status = explode( ',', $post_status );
            if ( array_diff( $post_status, $this->_get_post_stati() ) ) return false;
            $key          = md5( serialize( $this->_tp_array_slice_assoc( $parsed_args, array_keys( $defaults ) ) ) );
            $last_changed = $this->_tp_cache_get_last_changed( 'posts' );
            $cache_key = "get_pages:$key:$last_changed";
            $cache     = $this->_tp_cache_get( $cache_key, 'posts' );
            if ( false !== $cache ) {
                $this->_prime_post_caches( $cache, false, false );
                $pages = array_map( 'get_post', $cache );
                $pages = $this->_apply_filters( 'get_pages', $pages, $parsed_args );
                return $pages;
            }
            $inclusions = '';
            if ( ! empty( $parsed_args['include'] ) ) {
                $child_of     = 0; // Ignore child_of, parent, exclude, meta_key, and meta_value params if using include.
                $parent       = -1;
                $exclude      = '';
                $meta_key     = '';
                $meta_value   = '';
                $hierarchical = false;
                $incpages     = $this->_tp_parse_id_list( $parsed_args['include'] );
                if ( ! empty( $incpages ) ) $inclusions = ' AND ID IN (' . implode( ',', $incpages ) . ')';
            }
            $exclusions = '';
            if ( ! empty( $exclude ) ) {
                $expages = $this->_tp_parse_id_list( $exclude );
                if ( ! empty( $expages ) )
                    $exclusions = ' AND ID NOT IN (' . implode( ',', $expages ) . ')';
            }
            $author_query = '';
            if ( ! empty( $parsed_args['authors'] ) ) {
                $post_authors = $this->_tp_parse_list( $parsed_args['authors'] );
                if ( ! empty( $post_authors ) ) {
                    foreach ( $post_authors as $post_author ) {
                        if ( 0 === (int) $post_author ) {
                            $post_author = $this->_get_user_by( 'login', $post_author );
                            if ( empty( $post_author ) ) continue;
                            if ( empty( $post_author->ID ) ) continue;
                            $post_author = $post_author->ID;
                        }
                        if ( '' === $author_query ) $author_query = $this->tpdb->prepare( ' post_author = %d ', $post_author );
                        else $author_query .= $this->tpdb->prepare( ' OR post_author = %d ', $post_author );
                    }
                    if ( '' !== $author_query ) $author_query = " AND ($author_query)";
                }
            }
            $join  = '';
            $where = "$exclusions $inclusions ";
            if ( '' !== $meta_key || '' !== $meta_value ) {
                $join = " LEFT JOIN $this->tpdb->post_meta ON ( $this->tpdb->posts.ID = $this->tpdb->post_meta.post_id )";
                $meta_key   = $this->_tp_unslash( $meta_key );
                $meta_value = $this->_tp_unslash( $meta_value );
                if ( '' !== $meta_key ) {
                    $where .= $this->tpdb->prepare( " AND $this->tpdb->post_meta.meta_key = %s", $meta_key );
                }
                if ( '' !== $meta_value ) {
                    $where .= $this->tpdb->prepare( " AND $this->tpdb->post_meta.meta_value = %s", $meta_value );
                }
            }
            if ( is_array( $parent ) ) {
                $post_parent__in = implode( ',', array_map( 'absint', (array) $parent ) );
                if ( ! empty( $post_parent__in ) ) $where .= " AND post_parent IN ($post_parent__in)";
            } elseif ( $parent >= 0 ) $where .= $this->tpdb->prepare( ' AND post_parent = %d ', $parent );
            if ( 1 === count( $post_status ) ) {
                $where_post_type = $this->tpdb->prepare( 'post_type = %s AND post_status = %s', $parsed_args['post_type'], reset( $post_status ) );
            } else {
                $post_status     = implode( "', '", $post_status );
                $where_post_type = $this->tpdb->prepare( "post_type = %s AND post_status IN ('$post_status')", $parsed_args['post_type'] );
            }
            $orderby_array = [];
            $allowed_keys  = ['author','post_author','date','post_date','title','post_title','name','post_name','modified',
                'post_modified','modified_gmt','post_modified_gmt','menu_order','parent','post_parent','ID', 'rand','comment_count',];
            foreach ( explode( ',', $parsed_args['sort_column'] ) as $orderby ) {
                $orderby = trim( $orderby );
                if ( ! in_array( $orderby, $allowed_keys, true ) ) continue;
                switch ( $orderby ) {
                    case 'menu_order':
                        break;
                    case 'ID':
                        $orderby = "$this->tpdb->posts.ID";
                        break;
                    case 'rand':
                        $orderby = 'RAND()';
                        break;
                    case 'comment_count':
                        $orderby = "$this->tpdb->posts.comment_count";
                        break;
                    default:
                        if ( 0 === strpos( $orderby, 'post_' ) ) $orderby = "$this->tpdb->posts." . $orderby;
                        else  $orderby = "$this->tpdb->posts.post_" . $orderby;
                }
                $orderby_array[] = $orderby;
            }
            $sort_column = ! empty( $orderby_array ) ? implode( ',', $orderby_array ) : "$this->tpdb->posts.post_title";
            $sort_order = strtoupper( $parsed_args['sort_order'] );
            if ( '' !== $sort_order && ! in_array( $sort_order, array( 'ASC', 'DESC' ), true ) )
                $sort_order = 'ASC';
            $query  = TP_SELECT . " * FROM $this->tpdb->posts $join WHERE ($where_post_type) $where ";
            $query .= $author_query;
            $query .= ' ORDER BY ' . $sort_column . ' ' . $sort_order;
            if ( ! empty( $number ) ) $query .= ' LIMIT ' . $offset . ',' . $number;
            $pages = $this->tpdb->get_results( $query );
            if ( empty( $pages ) ) {
                $this->_tp_cache_set( $cache_key, [], 'posts' );
                $pages = $this->_apply_filters( 'get_pages', [], $parsed_args );
                return $pages;
            }
            $num_pages = count( $pages );
            for ( $i = 0; $i < $num_pages; $i++ )
                $pages[ $i ] = $this->_sanitize_post( $pages[ $i ], 'raw' );
            $this->_update_post_cache( $pages );
            if ( $child_of || $hierarchical )
                $pages = $this->_get_page_children( $child_of, $pages );
            if ( ! empty( $parsed_args['exclude_tree'] ) ) {
                $exclude = $this->_tp_parse_id_list( $parsed_args['exclude_tree'] );
                foreach ( $exclude as $id ) {
                    $children = $this->_get_page_children( $id, $pages );
                    foreach ( $children as $child )  $exclude[] = $child->ID;
                }
                $num_pages = count( $pages );
                for ( $i = 0; $i < $num_pages; $i++ ) {
                    if ( in_array( $pages[ $i ]->ID, $exclude, true ) ) unset( $pages[ $i ] );
                }
            }
            $page_structure = [];
            foreach ( $pages as $page ) $page_structure[] = $page->ID;
            $this->_tp_cache_set( $cache_key, $page_structure, 'posts' );
            $pages = array_map( 'get_post', $pages );
            return $this->_apply_filters( 'get_pages', $pages, $parsed_args );
        }//5960
        /**
         * @description Determines whether an attachment URI is local and really an attachment.
         * @param $url
         * @return bool
         */
        protected function _is_local_attachment( $url ):bool{
            if ( strpos( $url, $this->_home_url() ) === false ) return false;
            if ( strpos( $url, $this->_home_url( '/?attachment_id=' ) ) !== false ) return true;
            $id = $this->_url_to_postid( $url );
            if ( $id ) {
                $post = $this->_get_post( $id );
                if ( 'attachment' === $post->post_type ) return true;
            }
            return false;
        }//6256
        /**
         * @description Insert an attachment.
         * @param $args
         * @param bool $file
         * @param int $parent
         * @param bool $tp_error
         * @param bool $fire_after_hooks
         * @return mixed
         */
        protected function _tp_insert_attachment( $args, $file = false, $parent = 0, $tp_error = false, $fire_after_hooks = true ){
            $defaults = ['file' => $file,'post_parent' => 0,];
            $data = $this->_tp_parse_args( $args, $defaults );
            if ( ! empty( $parent ) ) $data['post_parent'] = $parent;
            $data['post_type'] = 'attachment';
            return $this->_tp_insert_post( $data, $tp_error, $fire_after_hooks );
        }//6301
        /**
         * @description Trash or delete an attachment.
         * @param $post_id
         * @param bool $force_delete
         * @return array|bool|null
         */
        protected function _tp_delete_attachment( $post_id, $force_delete = false ){
            $this->tpdb = $this->_init_db();
            $post = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->posts WHERE ID = %d", $post_id ) );
            if ( ! $post )  return $post;
            $post = $this->_get_post( $post );
            if ( 'attachment' !== $post->post_type ) return false;
            if ( ! $force_delete && EMPTY_TRASH_DAYS && __MEDIA_TRASH && 'trash' !== $post->post_status )
                return $this->_tp_trash_post( $post_id );
            $check = $this->_apply_filters( 'pre_delete_attachment', null, $post, $force_delete );
            if ( null !== $check ) return $check;
            $this->_delete_post_meta( $post_id, '_tp_trash_meta_status' );
            $this->_delete_post_meta( $post_id, '_tp_trash_meta_time' );
            $meta         = $this->_tp_get_attachment_metadata( $post_id );
            $backup_sizes = $this->_get_post_meta( $post->ID, '_tp_attachment_backup_sizes', true );
            $file         = $this->_get_attached_file( $post_id );
            if ( is_string( $file ) && ! empty( $file ) && $this->_is_multisite())
                $this->_clean_dir_size_cache( $file );
            $this->_do_action( 'delete_attachment', $post_id, $post );
            $this->_tp_delete_object_term_relationships( $post_id, array( 'category', 'post_tag' ) );
            $this->_tp_delete_object_term_relationships( $post_id, $this->_get_object_taxonomies( $post->post_type ) );
            $this->_delete_metadata( 'post', null, '_thumbnail_id', $post_id, true );
            $this->_tp_defer_comment_counting( true );
            $comment_ids = $this->tpdb->get_col( $this->tpdb->prepare(  TP_SELECT . " comment_ID FROM $this->tpdb->comments WHERE comment_post_ID = %d ORDER BY comment_ID DESC", $post_id ) );
            foreach ( $comment_ids as $comment_id ) $this->_tp_delete_comment( $comment_id, true );
            $this->_tp_defer_comment_counting( false );
            $post_meta_ids = $this->tpdb->get_col( $this->tpdb->prepare(  TP_SELECT . "  meta_id FROM $this->tpdb->post_meta WHERE post_id = %d ", $post_id ) );
            foreach ( $post_meta_ids as $mid ) $this->_delete_metadata_by_mid( 'post', $mid );
            $this->_do_action( 'delete_post', $post_id, $post );
            $result = $this->tpdb->delete( $this->tpdb->posts, array( 'ID' => $post_id ) );
            if ( ! $result ) return false;
            $this->_do_action( 'deleted_post', $post_id, $post );
            $this->_tp_delete_attachment_files( $post_id, $meta, $backup_sizes, $file );
            $this->_clean_post_cache( $post );
            return $post;
        }//6337
        /**
         * @description Deletes all files that belong to the given attachment.
         * @param $post_id
         * @param $meta
         * @param $backup_sizes
         * @param $file
         * @return bool
         */
        protected function _tp_delete_attachment_files( $post_id, $meta, $backup_sizes, $file ):bool{
            $this->tpdb = $this->_init_db();
            $uploadpath = $this->_tp_get_upload_dir();
            $deleted    = true;
            if (!empty($meta['thumb']) && !$this->tpdb->get_row($this->tpdb->prepare(TP_SELECT . " meta_id FROM $this->tpdb->post_meta WHERE meta_key = '_tp_attachment_metadata' AND meta_value LIKE %s AND post_id <> %d", '%' . $this->tpdb->esc_like($meta['thumb']) . '%', $post_id))) {
                $thumbfile = str_replace( $this->_tp_basename( $file ), $meta['thumb'], $file );
                if ( ! empty( $thumbfile ) ) {
                    $thumbfile = $this->_path_join( $uploadpath['basedir'], $thumbfile );
                    $thumbdir  = $this->_path_join( $uploadpath['basedir'], dirname( $file ) );
                    if ( ! $this->_tp_delete_file_from_directory( $thumbfile, $thumbdir ) )
                        $deleted = false;
                }
            }
            if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
                $intermediate_dir = $this->_path_join( $uploadpath['basedir'], dirname( $file ) );
                foreach ( $meta['sizes'] as $size => $sizeinfo ) {
                    $intermediate_file = str_replace( $this->_tp_basename( $file ), $sizeinfo['file'], $file );
                    if ( ! empty( $intermediate_file ) ) {
                        $intermediate_file = $this->_path_join( $uploadpath['basedir'], $intermediate_file );
                        if ( ! $this->_tp_delete_file_from_directory( $intermediate_file, $intermediate_dir ) )  $deleted = false;
                    }
                }
            }
            if ( ! empty( $meta['original_image'] ) ) {
                if ( empty( $intermediate_dir ) )
                    $intermediate_dir = $this->_path_join( $uploadpath['basedir'], dirname( $file ) );
                $original_image = str_replace( $this->_tp_basename( $file ), $meta['original_image'], $file );
                if ( ! empty( $original_image ) ) {
                    $original_image = $this->_path_join( $uploadpath['basedir'], $original_image );
                    if ( ! $this->_tp_delete_file_from_directory( $original_image, $intermediate_dir ) )
                        $deleted = false;
                }
            }
            if ( is_array( $backup_sizes ) ) {
                $del_dir = $this->_path_join( $uploadpath['basedir'], dirname( $meta['file'] ) );
                foreach ( $backup_sizes as $size ) {
                    $del_file = $this->_path_join( dirname( $meta['file'] ), $size['file'] );
                    if ( ! empty( $del_file ) ) {
                        $del_file = $this->_path_join( $uploadpath['basedir'], $del_file );
                        if ( ! $this->_tp_delete_file_from_directory( $del_file, $del_dir ) )
                            $deleted = false;
                    }
                }
            }
            if ( ! $this->_tp_delete_file_from_directory( $file, $uploadpath['basedir'] ) ) {
                $deleted = false;
            }
            return $deleted;

        }//6441
        /**
         * @description Retrieves attachment metadata for attachment ID.
         * @param int $attachment_id
         * @param bool $unfiltered
         * @return bool
         */
        protected function _tp_get_attachment_metadata( $attachment_id = 0, $unfiltered = false ):bool{
            $attachment_id = (int) $attachment_id;
            if ( ! $attachment_id ) {
                $post = $this->_get_post();
                if ( ! $post ) return false;
                $attachment_id = $post->ID;
            }
            $data = $this->_get_post_meta( $attachment_id, '_tp_attachment_metadata', true );
            if ( ! $data ) return false;
            if ( $unfiltered ) return $data;
            return $this->_apply_filters( 'tp_get_attachment_metadata', $data, $attachment_id );
        }//6537
        /**
         * @description Updates metadata for an attachment.
         * @param $attachment_id
         * @param $data
         * @return bool
         */
        protected function _tp_update_attachment_metadata( $attachment_id, $data ):bool{
            $attachment_id = (int) $attachment_id;
            $post = $this->_get_post( $attachment_id );
            if ( ! $post ) return false;
            $data = $this->_apply_filters( 'tp_update_attachment_metadata', $data, $post->ID );
            if ( $data )  return $this->_update_post_meta( $post->ID, '_tp_attachment_metadata', $data );
            else return $this->_delete_post_meta( $post->ID, '_tp_attachment_metadata' );
        }//6580
        /**
         * @description Retrieve the URL for an attachment.
         * @param int $attachment_id
         * @return mixed
         */
        protected function _tp_get_attachment_url( $attachment_id = 0 ){
            $attachment_id = (int) $attachment_id;
            $post = $this->_get_post( $attachment_id );
            if ( ! $post ) return false;
            if ( 'attachment' !== $post->post_type ) return false;
            $url = '';
            $file = $this->_get_post_meta( $post->ID, '_tp_attached_file', true );
            if ( $file ) {
                $uploads = $this->_tp_get_upload_dir();
                if ( $uploads && false === $uploads['error'] ) {
                    // Check that the upload base exists in the file location.
                    if ( 0 === strpos( $file, $uploads['basedir'] ) )
                        $url = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
                    else $url = $uploads['baseurl'] . "/$file";
                }
            }
            if ( ! $url ) $url = $this->_get_the_guid( $post->ID );
            if ('tp-login.php' !== $this->tp_pagenow && $this->_is_ssl() && ! $this->_is_admin())
                $url = $this->_set_url_scheme( $url );
            $url = $this->_apply_filters( 'tp_get_attachment_url', $url, $post->ID );
            if ( ! $url ) return false;
            return $url;
        }//6615
        /**
         * @description Retrieves the caption for an attachment.
         * @param int $post_id
         * @return bool
         */
        protected function _tp_get_attachment_caption( $post_id = 0 ):bool{
            $post_id = (int) $post_id;
            $post    = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            if ( 'attachment' !== $post->post_type ) return false;
            $caption = $post->post_excerpt;
            return $this->_apply_filters( 'tp_get_attachment_caption', $caption, $post->ID );
        }//6689
    }
}else die;