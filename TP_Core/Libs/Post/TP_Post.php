<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 18:25
 */
namespace TP_Core\Libs\Post;
if(ABSPATH){
    final class TP_Post extends Post_Base {
        public static function get_instance( $post_id ): ?mixed {
            $tpdb = (new self($post_id))->_init_db();
            $post_id = (int) $post_id;
            if ( ! $post_id ){return null;}
            $_post = (new self((object)$post_id))->_tp_cache_get( $post_id, 'posts' );
            if ( ! $_post ) {
                $_post = $tpdb->get_row( $tpdb->prepare( TP_SELECT . " * FROM $tpdb->posts WHERE ID = %d LIMIT 1", $post_id ) );
                if ( ! $_post ) return null;
                $_post = (new self((object)$post_id))->_sanitize_post( $_post, 'raw' );
                if($_post  instanceof self ){
                    (new self($_post))->_tp_cache_add( $_post->ID, $_post, 'posts' );
                }
            }elseif ( empty( $_post->filter ) ) {
                $_post = (new self((object)$post_id))->_sanitize_post( $_post, 'raw' );
            }
            return new TP_Post( $_post );
        }

        /**
         * TP_Post constructor.
         * @param object $post
         */
        public function __construct( $post ) {
            if($post !== null)
            foreach ( get_object_vars((object) $post ) as $key => $value ) $this->$key = $value;
        }//264
        public function __isset( $key ){
            if ( 'ancestors' === $key ) return true;
            if ( 'page_template' === $key ) return true;
            if ( 'post_category' === $key ) return true;
            if ( 'tags_input' === $key ) return true;
            return $this->_metadata_exists( 'post', $this->ID, $key );
        }//278
        public function __get( $key ){
            if ('page_template' === $key && $this->__isset($key))
                return $this->_get_post_meta($this->ID, '_tp_page_template', true);
            if ('post_category' === $key) {
                if ($this->_is_object_in_taxonomy($this->post_type, 'category'))
                    $terms = $this->_get_the_terms($this, 'category');
                if (empty($terms)) return array();
                return $this->_tp_list_pluck($terms, 'term_id');
            }
            if ('tags_input' === $key) {
                if ($this->_is_object_in_taxonomy($this->post_type['type'], 'post_tag'))
                    $terms = $this->_get_the_terms($this, 'post_tag');
                if (empty($terms)) return [];
                return $this->_tp_list_pluck($terms, 'name');
            }
            if ( 'ancestors' === $key )$value = $this->_get_post_ancestors( $this );
            else $value = $this->_get_post_meta( $this->ID, $key, true );
            if ( $this->filter )  $value = $this->_sanitize_post_field( $key, $value, $this->ID, $this->filter );
            return $value;
        }//306
        public function post_filter( $filter ) {
            if ( $this->filter === $filter ) return $this;
            if ( 'raw' === $filter ) return self::get_instance( $this->ID );
            return $this->_sanitize_post( $this, $filter );
        }//357
        public function to_array() {
            $post = get_object_vars( $this );
            foreach ( array( 'ancestors', 'page_template', 'post_category', 'tags_input' ) as $key )
                if ( $this->__isset( $key ) ) $post[ $key ] = $this->__get( $key );
            return $post;
        }//376
    }
}else die;