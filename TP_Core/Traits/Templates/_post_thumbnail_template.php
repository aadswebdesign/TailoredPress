<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 04:24
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _post_thumbnail_template {
        use _init_queries;
        /**
         * @description Determines whether a post has an image attached.
         * @param null $post
         * @return bool
         */
        protected function _has_post_thumbnail( $post = null ):bool{
            $thumbnail_id  = $this->_get_post_thumbnail_id( $post );
            $has_thumbnail = (bool) $thumbnail_id;
            return (bool) $this->_apply_filters( 'has_post_thumbnail', $has_thumbnail, $post, $thumbnail_id );
        }//25
        /**
         * @description Retrieves the post thumbnail ID.
         * @param null $post
         * @return bool|int
         */
        protected function _get_post_thumbnail_id( $post = null ){
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $thumbnail_id = (int) $this->_get_post_meta( $post->ID, '_thumbnail_id', true );
            return (int) $this->_apply_filters( 'post_thumbnail_id', $thumbnail_id, $post );
        }//53
        /**@description Displays the post thumbnail.
         * @param string $size
         * @param string $attr
         */
        protected function _the_post_thumbnail( $size = 'post-thumbnail', $attr = '' ):void{
            echo $this->_get_the_post_thumbnail( null, $size, $attr );
        }//91
        /**
         * @description Updates cache for thumbnails in the current loop.
         * @param null $tp_query
         */
        protected function _update_post_thumbnail_cache( $tp_query = null ):void{
            if ( ! $tp_query ) $tp_query = $this->_init_query();
            if ($tp_query instanceof \stdClass && $tp_query->thumbnails_cached ) return;
            $thumb_ids = [];//todo std class
            foreach ( $tp_query->posts as $post ) {
                $id = $this->_get_post_thumbnail_id( $post->ID );
                if ( $id ) $thumb_ids[] = $id;
            }
            if ( ! empty( $thumb_ids ) ) $this->_prime_post_caches( $thumb_ids, false, true );
            $tp_query->thumbnails_cached = true;
        }//104
        /**
         * @description Retrieves the post thumbnail.
         * @param null $post
         * @param string $size
         * @param array|string $attr
         * @return string
         */
        protected function _get_the_post_thumbnail( $post = null, $size = 'post-thumbnail', ...$attr):string{
            $post = $this->_get_post( $post );
            if ( ! $post ) return '';
            $post_thumbnail_id = $this->_get_post_thumbnail_id( $post );
            $size = $this->_apply_filters( 'post_thumbnail_size', $size, $post->ID );
            if ( $post_thumbnail_id ) {
                $this->_do_action( 'begin_fetch_post_thumbnail_html', $post->ID, $post_thumbnail_id, $size );
                if ( $this->_in_the_loop() ) $this->_update_post_thumbnail_cache();
                $loading = $this->_tp_get_loading_attr_default( 'the_post_thumbnail' );
                if ( empty( $attr ) ) $attr = ['loading' => $loading];
                elseif ( is_array( $attr ) && ! array_key_exists( 'loading', $attr ) ) $attr['loading'] = $loading;
                elseif ( is_string( $attr ) && ! preg_match( '/(^|&)loading=/', $attr ) ) $attr .= '&loading=' . $loading;
                $html = $this->_tp_get_attachment_image( $post_thumbnail_id, $size, false, $attr );
                $this->_do_action( 'end_fetch_post_thumbnail_html', $post->ID, $post_thumbnail_id, $size );

            } else $html = '';
            return $this->_apply_filters( 'post_thumbnail_html', $html, $post->ID, $post_thumbnail_id, $size, $attr );
        }//148
        /**
         * @description Returns the post thumbnail URL.
         * @param null $post
         * @param string $size
         * @return bool
         */
        protected function _get_the_post_thumbnail_url( $post = null, $size = 'post-thumbnail' ):bool{
            $post_thumbnail_id = $this->_get_post_thumbnail_id( $post );
            if ( ! $post_thumbnail_id ) return false;
            $thumbnail_url = $this->_tp_get_attachment_image_url( $post_thumbnail_id, $size );
            return $this->_apply_filters( 'post_thumbnail_url', $thumbnail_url, $post, $size );
        }//246
        /**
         * @description Displays the post thumbnail URL.
         * @param string $size
         */
        protected function _the_post_thumbnail_url( $size = 'post-thumbnail' ):void{
            $url = $this->_get_the_post_thumbnail_url( null, $size );
            if ( $url ) echo $this->_esc_url( $url );
        }//277
        /**
         * @description Returns the post thumbnail caption.
         * @param null $post
         * @return string
         */
        protected function _get_the_post_thumbnail_caption( $post = null ):string{
            $post_thumbnail_id = $this->_get_post_thumbnail_id( $post );
            if ( ! $post_thumbnail_id ) return '';
            $caption = $this->_tp_get_attachment_caption( $post_thumbnail_id );
            if ( ! $caption ) $caption = '';
            return $caption;
        }//293
        /**
         * @description Displays the post thumbnail caption.
         * @param null $post
         */
        protected function _the_post_thumbnail_caption( $post = null ):void{
            echo $this->_apply_filters( 'the_post_thumbnail_caption', $this->_get_the_post_thumbnail_caption( $post ) );
        }//316
    }
}else die;