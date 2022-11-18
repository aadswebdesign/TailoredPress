<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
if(ABSPATH){
    trait _post_05 {
        /**
         * @description Deletes a post meta field for the given post ID.
         * @param $post_id
         * @param $meta_key
         * @param string $meta_value
         * @return mixed
         */
        protected function _delete_post_meta( $post_id, $meta_key, $meta_value = '' ){
            $the_post = $this->_tp_is_post_revision( $post_id );
            if ( $the_post ) $post_id = $the_post;
            return $this->_delete_metadata( 'post', $post_id, $meta_key, $meta_value );
        }//2478
        /**
         * @description Retrieves a post meta field for the given post ID.
         * @param $post_id
         * @param string $key
         * @param bool $single
         * @return mixed
         */
        protected function _get_post_meta($post_id,$key='',$single = false){
            return $this->_get_metadata( 'post', $post_id, $key, $single );
        }//2504
        /**
         * @description Updates a post meta field based on the given post ID.
         * @param $post_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return mixed
         */
        protected function _update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ){
            $the_post = $this->_tp_is_post_revision( $post_id );
            if ( $the_post ) $post_id = $the_post;
            return $this->_update_metadata( 'post', $post_id, $meta_key, $meta_value, $prev_value );
        }//2541
        /**
         * @description Deletes everything from post meta matching the given meta key.
         * @param $post_meta_key
         * @return mixed
         */
        protected function _delete_post_meta_by_key( $post_meta_key ){
            return $this->_delete_metadata( 'post', null, $post_meta_key, '', true );
        }//2548
        /**
         * @description Registers a meta key for posts.
         * @param $post_type
         * @param $meta_key
         * @param array $args
         * @return mixed
         */
        protected function _register_post_meta( $post_type, $meta_key, array $args ){
            $args['object_subtype'] = $post_type;
            return $this->_register_meta( 'post', $meta_key, $args );
        }//2564
        /**
         * @description Unregisters a meta key for posts.
         * @param $post_type
         * @param $meta_key
         * @return mixed
         */
        protected function _unregister_post_meta( $post_type, $meta_key ){
            return $this->_unregister_meta_key( 'post', $meta_key, $post_type );
        }//2581
        /**
         * @description Retrieve post meta fields, based on post ID.
         * @param mixed $post_id
         * @return mixed
         */
        protected function _get_post_custom($post_id = 0 ){
            $post_id = $this->_abs_int( $post_id );
            if ( ! $post_id ) $post_id = $this->_get_the_ID();
            return $this->_get_post_meta( $post_id );
        }//2596
        /**
         * @description Retrieve meta field names for a post.
         * @param int $post_id
         * @return array|bool
         */
        protected function _get_post_custom_keys( $post_id = 0 ){
            $custom = $this->_get_post_custom( $post_id );
            if ( ! is_array( $custom ) )return false;
            $keys = array_keys( $custom );
            if ( $keys ) return $keys;
            return true;
        }//2615
        /**
         * @description Retrieve values for a custom post field.
         * @param string $key
         * @param int $post_id
         * @return mixed
         */
        protected function _get_post_custom_values( $key = '', $post_id = 0 ){
            if ( ! $key ) return null;
            $custom = $this->_get_post_custom( $post_id );
            return $custom[ $key ] ?? null;
        }//2640
        /**
         * @description Determines whether a post is sticky.
         * @param mixed $post_id
         * @return mixed
         */
        protected function _is_sticky($post_id = 0 ){
            $post_id = $this->_abs_int( $post_id );
            if ( ! $post_id ) $post_id = $this->_get_the_ID();
            $stickies = $this->_get_option( 'sticky_posts' );
            if ( is_array( $stickies ) ) {
                $stickies  = array_map( 'intval', $stickies );
                $is_sticky = in_array( $post_id, $stickies, true );
            } else $is_sticky = false;
            return $this->_apply_filters( 'is_sticky', $is_sticky, $post_id );
        }//2665
    }
}else die;