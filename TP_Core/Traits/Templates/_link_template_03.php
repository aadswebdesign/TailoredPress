<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _link_template_03 {
        use _init_error;
        use _init_rewrite;
        /**
         * @description Retrieves the permalink for a tag feed.
         * @param $tag
         * @param string $feed
         * @return mixed
         */
        protected function _get_tag_feed_link( $tag,$feed ='' ){
            return $this->_get_term_feed_link( $tag, 'post_tag', $feed );
         }//1015 from link-template
        /**
         * @description Retrieves the edit link for a tag.
         * @param $tag
         * @param string $taxonomy
         * @return mixed
         */
        protected function _get_edit_tag_link( $tag, $taxonomy = 'post_tag' ){
            return $this->_apply_filters( 'get_edit_tag_link', $this->_get_edit_term_link( $tag, $taxonomy ) );
        }//1028 from link-template
        /**
         * @description Displays or retrieves the edit link for a tag with formatting.
         * @param string $link
         * @param string $before
         * @param string $after
         * @param null $tag
         */
        protected function _edit_tag_link( $link = '', $before = '', $after = '', $tag = null ):void{
            $link = (string)$this->_edit_term_link( $link, '', '', $tag );
            echo $before . $this->_apply_filters( 'edit_tag_link', $link ) . $after;
        }//1050 from link-template
        /**
         * @description Retrieves the URL for editing a given term.
         * @param $term
         * @param string $taxonomy
         * @param string $object_type
         * @return bool
         */
        protected function _get_edit_term_link( $term, $taxonomy = '', $object_type = '' ):bool{
            $term = $this->_get_term( $term, $taxonomy );
            if ( ! $term || $this->_init_error( $term ) ) return false;
            $tax     = $this->_get_taxonomy( $term->taxonomy );
            $term_id = $term->term_id;
            if ( ! $tax || ! $this->_current_user_can( 'edit_term', $term_id ) ) return false;
            $args = ['taxonomy' => $taxonomy,'tag_ID' => $term_id,];
            if ( $object_type ) $args['post_type'] = $object_type;
            elseif ( ! empty( $tax->object_type ) ) $args['post_type'] = reset( $tax->object_type );
            if ( $tax->show_ui ) $location = $this->_add_query_arg( $args, $this->_admin_url( 'term.php' ) );
            else  $location = '';
            return $this->_apply_filters( 'get_edit_term_link', $location, $term_id, $taxonomy, $object_type );
        }//1077 from link-template
        /**
         * @description Displays or retrieves the edit term link with formatting.
         * @param string $link
         * @param string $before
         * @param string $after
         * @param null $term
         * @return bool|string
         */
        protected function _edit_term_link( $link = '', $before = '', $after = '', $term = null){
            if ( is_null( $term ) ) $term = $this->_get_queried_object();
            else $term = $this->_get_term( $term );
            if ( ! $term )  return false;
            $tax = $this->_get_taxonomy( $term->taxonomy );
            if ( ! $this->_current_user_can( 'edit_term', $term->term_id ) ) return false;
            if ( empty( $link ) )
                $link = $this->__( 'Edit This' );
            $link = "<a href='{$this->_get_edit_term_link( $term->term_id, $tax )}'>$link</a>";
            $link = $before . $this->_apply_filters( 'edit_term_link', $link, $term->term_id ) . $after;
            return $link;
        }//1131 from link-template
        protected function _the_edit_term_link( $link = '', $before = '', $after = '', $term = null):void{
            echo $this->_edit_term_link( $link, $before, $after, $term);
        }//added
        /**
         * @description Retrieves the permalink for a search.
         * @param string $query
         * @return mixed
         */
        protected function _get_search_link( $query = '' ){
            $tp_rewrite = $this->_init_rewrite();
            if ( empty( $query ) )  $search = $this->_get_search_query( false );
            else $search = stripslashes( $query );
            $permastruct = $tp_rewrite->get_search_permanent_structure();
            if ( empty( $permastruct ) ) $link = $this->_home_url( '?s=' . urlencode( $search ) );
            else {
                $search = urlencode( $search );
                $search = str_replace( '%2F', '/', $search ); // %2F(/) is not valid within a URL, send it un-encoded.
                $link   = str_replace( '%search%', $search, $permastruct );
                $link   = $this->_home_url( $this->_user_trailingslashit( $link, 'search' ) );
            }
            return $this->_apply_filters( 'search_link', $link, $search );
        }//1180 from link-template
        /**
         * @description Retrieves the permalink for the search results feed.
         * @param string $search_query
         * @param string $feed
         * @return mixed
         */
        protected function _get_search_feed_link($search_query = '',$feed = ''){
            $tp_rewrite = $this->_init_rewrite();
            $link = $this->_get_search_link( $search_query );
            if ( empty( $feed ) ) $feed = $this->_get_default_feed();
            $permastruct = $tp_rewrite->get_search_permanent_structure();
            if ( empty( $permastruct ) ) $link = $this->_add_query_arg( 'feed', $feed, $link );
            else {
                $link  = $this->_trailingslashit( $link );
                $link .= "feed/$feed/";
            }
            return $this->_apply_filters( 'search_feed_link', $link, $feed, 'posts' );
        }//1223 from link-template
        /**
         * @description Retrieves the permalink for the search results comments feed.
         * @param string $search_query
         * @param string $feed
         * @return mixed
         */
        protected function _get_search_comments_feed_link( $search_query = '', $feed = '' ){
            $tp_rewrite = $this->_init_rewrite();
            if ( empty( $feed ) ) $feed = $this->_get_default_feed();
            $link = $this->_get_search_feed_link( $search_query, $feed );
            $permastruct = $tp_rewrite->get_search_permanent_structure();
            if ( empty( $permastruct ) ) $link = $this->_add_query_arg( 'feed', 'comments-' . $feed, $link );
            else $link = $this->_add_query_arg( 'with_comments', 1, $link );
            return $this->_apply_filters( 'search_feed_link', $link, $feed, 'comments' );
        }//1264 from link-template
        /**
         * @description Retrieves the permalink for a post type archive.
         * @param $post_type
         * @return bool
         */
        protected function _get_post_type_archive_link( $post_type ):bool{
            $tp_rewrite = $this->_init_rewrite();
            $post_type_obj = $this->_get_post_type_object( $post_type );
            if ( ! $post_type_obj ) return false;
            if ( 'post' === $post_type ) {
                $show_on_front  = $this->_get_option( 'show_on_front' );
                $page_for_posts = $this->_get_option( 'page_for_posts' );
                if ( 'page' === $show_on_front && $page_for_posts )
                    $link = $this->_get_permalink( $page_for_posts );
                else $link = $this->_get_home_url();
                return $this->_apply_filters( 'post_type_archive_link', $link, $post_type );
            }
            if ( ! $post_type_obj->has_archive ) return false;
            if (is_array( $post_type_obj->rewrite ) && $this->_get_option( 'permalink_structure' )) {
                $struct = ( true === $post_type_obj->has_archive ) ? $post_type_obj->rewrite['slug'] : $post_type_obj->has_archive;
                if ( $post_type_obj->rewrite['with_front'] ) $struct = $tp_rewrite->front . $struct;
                else $struct = $tp_rewrite->root . $struct;
                $link = $this->_home_url( $this->_user_trailingslashit( $struct, 'post_type_archive' ) );
            } else $link = $this->_home_url( '?post_type=' . $post_type );
            return $this->_apply_filters( 'post_type_archive_link', $link, $post_type );
        }//1297 from link-template
        /**
         * @description Retrieves the permalink for a post type archive feed.
         * @param $post_type
         * @param string $feed
         * @return bool
         */
        protected function _get_post_type_archive_feed_link( $post_type, $feed ='' ):bool{
            $default_feed = $this->_get_default_feed();
            if ( empty( $feed ) ) $feed = $default_feed;
            $link = $this->_get_post_type_archive_link( $post_type );
            if ( ! $link ) return false;
            $post_type_obj = $this->_get_post_type_object( $post_type );
            if ( is_array( $post_type_obj->rewrite ) && $post_type_obj->rewrite['feeds'] && $this->_get_option( 'permalink_structure' )) {
                $link  = $this->_trailingslashit( $link );
                $link .= 'feed/';
                if ( $feed !== $default_feed ) $link .= "$feed/";
            } else $link = $this->_add_query_arg( 'feed', $feed, $link );
            return $this->_apply_filters( 'post_type_archive_feed_link', $link, $feed );
        }//1356 from link-template
    }
}else die;