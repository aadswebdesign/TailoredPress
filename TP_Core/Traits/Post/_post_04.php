<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_post_type;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    trait _post_04{
        use _init_post_type;
        use _init_db;
        /**
         * @description Remove support for a feature from a post type.
         * @param $post_type
         * @param $feature
         */
        protected function _remove_post_type_support( $post_type, $feature ):void{
            unset( $this->tp_post_type_features[ $post_type ][ $feature ] );
        }//2157
        /**
         * @description Get all the post type features
         * @param $post_type
         * @return array
         */
        protected function _get_all_post_type_supports( $post_type ):array{
            if ( isset( $this->tp_post_type_features[ $post_type ] ) )
                return $this->tp_post_type_features[ $post_type ];
            return [];
        }//2173
        /**
         * @description Check a post type's support for a given feature.
         * @param $post_type
         * @param $feature
         * @return bool
         */
        protected function _post_type_supports( $post_type, $feature ):bool{
            return ( isset( $this->tp_post_type_features[ $post_type ][ $feature ] ) );
        }//2194
        /**
         * @description Retrieves a list of post type names that support a specific feature.
         * @param $feature
         * @param string $operator
         * @return array
         */
        protected function _get_post_types_by_support( $feature, $operator = 'and' ):array{
            $features = array_fill_keys( (array) $feature, true );
            return array_keys( $this->_tp_filter_object_list( $this->tp_post_type_features, $features, $operator ) );
        }//2214
        /**
         * @description Update the post type for the post ID.
         * @param int $post_id
         * @param mixed $post_type
         * @return string
         */
        protected function _set_post_type( $post_id = 0,$post_type = 'post' ):string{
            $tpdb = $this->_init_db();
            $post_type = $this->_sanitize_post_field( 'post_type', $post_type, $post_id, 'db' );
            $return    = $tpdb->update( $tpdb->posts, array( 'post_type' => $post_type ), array( 'ID' => $post_id ) );
            $this->_clean_post_cache( $post_id );
            return $return;
        }//2236
        /**
         * @description Determines whether a post type is considered "viewable".
         * @param $post_type
         * @return bool
         */
        protected function _is_post_type_viewable( $post_type ):bool{
            if ( is_scalar( $post_type ) ) {
                $post_type = $this->_get_post_type_object( $post_type );
                if ( ! $post_type ) return false;
            }
            if ( ! is_object( $post_type ) )  return false;
            $is_viewable = $post_type->publicly_queryable || ( $post_type->_builtin && $post_type->public );
            return true === $this->_apply_filters( 'is_post_type_viewable', $is_viewable, $post_type );
        }//2261
        /**
         * @description Determine whether a post status is considered "viewable".
         * @param $post_status
         * @return bool
         */
        protected function _is_post_status_viewable( $post_status ):bool{
            if ( is_scalar( $post_status ) ) {
                $post_status = $this->_get_post_status_object( $post_status );
                if ( ! $post_status ) return false;
            }
            if (! is_object( $post_status ) || $post_status->internal || $post_status->protected)
                return false;

            $is_viewable = $post_status->publicly_queryable || ( $post_status->_builtin && $post_status->public );
            return true === $this->_apply_filters( 'is_post_status_viewable', $is_viewable, $post_status );
        }//2304
        /**
         * @description Determine whether a post is publicly viewable.
         * @param null $post
         * @return bool
         */
        protected function _is_post_publicly_viewable( $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $post_type   = $this->_get_post_type( $post );
            $post_status = $this->_get_post_status( $post );
            return $this->_is_post_type_viewable( $post_type ) && $this->_is_post_status_viewable( $post_status );
        }//2350
        /**
         * @description Retrieves an array of the latest posts, or posts matching the given criteria.
         * @param null $args
         * @return array|mixed|null
         */
        protected function _get_posts( $args = null ){
            $defaults = ['numberposts' => 5,'category' => 0,'orderby' => 'date', 'order' => 'DESC','include' => [],
                'exclude' => [],'meta_key' => '','meta_value' => '','post_type' => 'post','suppress_filters' => true,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            if ( empty( $parsed_args['post_status'] ) )
                $parsed_args['post_status'] = ( 'attachment' === $parsed_args['post_type'] ) ? 'inherit' : 'publish';
            if ( ! empty( $parsed_args['numberposts'] ) && empty( $parsed_args['posts_per_page'] ) )
                $parsed_args['posts_per_page'] = $parsed_args['numberposts'];
            if ( ! empty( $parsed_args['category'] ) )
                $parsed_args['cat'] = $parsed_args['category'];
            if ( ! empty( $parsed_args['include'] ) ) {
                $included_posts                      = $this->_tp_parse_id_list( $parsed_args['include'] );
                $parsed_args['posts_per_page'] = count( $included_posts );  // Only the number of posts included.
                $parsed_args['post__in']       = $included_posts;
            } elseif ( ! empty( $parsed_args['exclude'] ) )
                $parsed_args['post__not_in'] = $this->_tp_parse_id_list( $parsed_args['exclude'] );
            $parsed_args['ignore_sticky_posts'] = true;
            $parsed_args['no_found_rows']       = true;
            $this->tp_get_posts = new TP_Query;
            return $this->tp_get_posts->query_main( $parsed_args );
        }//2394
        /**
         * @description Adds a meta field to the given post.
         * @param $post_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return mixed
         */
        protected function _add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ){
            $the_post = $this->_tp_is_post_revision( $post_id );
            if ( $the_post ) $post_id = $the_post;
            return $this->_add_metadata( 'post', $post_id, $meta_key, $meta_value, $unique );
        }//2452
    }
}else die;