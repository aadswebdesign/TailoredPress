<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-5-2022
 * Time: 00:17
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    trait _format_post_01{
        use _init_post;
        use _init_error;
        use _init_rewrite;
        /**
         * @description Retrieve the format slug for a post
         * @param null $post
         * @return bool|mixed
         */
        protected function _get_post_format( $post = null ){
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            if ( ! $this->_post_type_supports( $post->post_type, 'post-formats' ) )
                return false;
            $_format = $this->_get_the_terms( $post->ID, 'post_format' );
            if ( empty( $_format ) ) return false;
            $format = reset( $_format );
            return str_replace( 'post-format-', '', $format->slug );
        }//17
        /**
         * @description Check if a post has any of the given formats, or any format.
         * @param array $format
         * @param null $post
         * @return mixed
         */
        protected function _has_post_format( $format = [], $post = null ){
            $prefixed = [];
            if ( $format ) {
                foreach ( (array) $format as $single )
                    $prefixed[] = 'post-format-' . $this->_sanitize_key( $single );
            }
            return $this->_has_term( $prefixed, 'post_format', $post );
        }//49
        /**
         * @description Assign a format to a post
         * @param $post
         * @param $format
         * @return TP_Error
         */
        protected function _set_post_format( $post, $format ):TP_Error{
            $post = $this->_get_post( $post );
            if ( ! $post ) return new TP_Error( 'invalid_post', $this->__( 'Invalid post.' ) );
            if ( ! empty( $format ) ) {
                $format = $this->_sanitize_key( $format );
                if ( 'standard' === $format || ! in_array( $format, $this->_get_post_format_slugs(), true ) )
                    $format = '';
                else $format = 'post-format-' . $format;
            }
            return $this->_tp_set_post_terms( $post->ID, $format, 'post_format' );
        }//70
        /**
         * @description Returns an array of post format slugs to their translated and pretty display versions
         * @return array
         */
        protected function _get_post_format_strings():array{
            $strings = [
                'standard' => $this->_x( 'Standard', 'Post format' ),'aside' => $this->_x( 'Aside', 'Post format' ),
                'chat' => $this->_x( 'Chat', 'Post format' ),'gallery' => $this->_x( 'Gallery', 'Post format' ),
                'link' => $this->_x( 'Link', 'Post format' ),'image' => $this->_x( 'Image', 'Post format' ),
                'quote' => $this->_x( 'Quote', 'Post format' ),'status' => $this->_x( 'Status', 'Post format' ),
                'video' => $this->_x( 'Video', 'Post format' ),'audio' => $this->_x( 'Audio', 'Post format' ),
            ];
            return $strings;
        }//96
        /**
         * @description Retrieves the array of post format slugs.
         * @return array
         */
        protected function _get_post_format_slugs():array{
            $slugs = array_keys(  $this->_get_post_format_strings() );
            return array_combine( $slugs, $slugs );
        }//119
        /**
         * @description Returns a pretty, translated version of a post format slug
         * @param $slug
         * @return string
         */
        protected function _get_post_format_string( $slug ):?string{
            $strings = $this->_get_post_format_strings();
            if ( ! $slug ) return $strings['standard'];
            else return $strings[ $slug ] ?? '';
        }//132
        /**
         * @description Returns a link to a post format index.
         * @param $format
         * @return bool
         */
        protected function _get_post_format_link( $format ):bool{
            $term = $this->_get_term_by( 'slug', 'post-format-' . $format, 'post_format' );
            if ( ! $term || $this->_init_error( $term ) ) return false;
            return $this->_get_term_link( $term );
        }//149
        /**
         * @description Filters the request to allow for the format prefix.
         * @param $qvs
         * @return mixed
         */
        protected function _post_format_request( $qvs ){
            if ( ! isset( $qvs['post_format'] ) ) return $qvs;
            $slugs = $this->_get_post_format_slugs();
            if ( isset( $slugs[ $qvs['post_format'] ] ) )
                $qvs['post_format'] = 'post-format-' . $slugs[ $qvs['post_format'] ];
            $tax = $this->_get_taxonomy( 'post_format' );
            if ( ! $this->_is_admin() )
                $qvs['post_type'] = $tax->object_type;
            return $qvs;
        }//166
        /**
         * @description Filters the post format term link to remove the format prefix.
         * @param $link
         * @param $term
         * @param $taxonomy
         * @return mixed
         */
        protected function _post_format_link( $link, $term, $taxonomy ){
            $tp_rewrite = $this->_init_rewrite();
            if ( 'post_format' !== $taxonomy ) return $link;
            if ( $tp_rewrite->get_extra_permanent_structure( $taxonomy ) )
                return str_replace( "/{$term->slug}", '/' . str_replace( 'post-format-', '', $term->slug ), $link );
            else {
                $link = $this->_remove_query_arg( 'post_format', $link );
                return $this->_add_query_arg( 'post_format', str_replace( 'post-format-', '', $term->slug ), $link );
            }
        }//194
        /**
         * @description Remove the post format prefix from the name property of the term object created by get_term().
         * @param TP_Term $term
         * @return TP_Term
         */
        protected function _post_format_get_term(TP_Term $term ):TP_Term{
            if ( isset( $term->slug ) )
                $term->name = $this->_get_post_format_string( str_replace( 'post-format-', '', $term->slug ) );
            return $term;
        }//216
    }
}else die;