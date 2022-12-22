<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 21:22
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _author_template_01 {
        /**
         * @description Retrieve the author of the current post.
         * @return mixed
         */
        protected function _get_the_author(){
            return $this->_apply_filters( 'the_author', is_object( $this->tp_author_data ) ? $this->tp_author_data->display_name : null );
        }//23 from author-template
        /**
         * @description Retrieve the author who last edited the current post.
         * @return null
         */
        protected function _get_the_modified_author(){
            $last_id = $this->_get_post_meta( $this->_get_post()->ID, '_edit_last', true );
            $filter = null;
            if ( $last_id ) {
                $last_user = $this->_get_user_data( $last_id );
                $filter = $this->_apply_filters( 'the_modified_author', $last_user->display_name );
            }
            return $filter;
        }//91 from author-template
        /**
         * @description Retrieves the requested data of the author of the current post.
         * @param string $field
         * @param bool $user_id
         * @return mixed
         */
        protected function _get_the_author_meta($field = '',$user_id = false){
            $original_user_id = $user_id;
            if ( ! $user_id ) $user_id = $this->tp_author_data->ID ?? 0;
            else  $this->tp_author_data = $this->_get_user_data( $user_id );
            if ( in_array( $field, array( 'login', 'pass', 'nicename', 'email', 'url', 'registered', 'activation_key', 'status' ), true ) )
                $field = 'user_' . $field;
            $value = $this->tp_author_data->$field ?? '';
            return $this->_apply_filters( "get_the_author_{$field}", $value, $user_id, $original_user_id );
        }// from author-template
        /**
         * @description Retrieve either author's link or author's name.
         * @return mixed|string
         */
        protected function _get_the_author_link(){
            if ( $this->_get_the_author_meta( 'url' ) )
                return sprintf(
                    "<a href='%1\$s' title='%2\$s' rel='author external'>%3\$s</a>",
                    $this->_esc_url( $this->_get_the_author_meta( 'url' ) ),
                    $this->_esc_attr( sprintf( $this->__( 'Visit %s&#8217;s website' ), $this->_get_the_author() ) ),
                    $this->_get_the_author()
                );
            else  return $this->_get_the_author();
        }//239 from author-template
        /**
         * @description Retrieve the number of posts by the author of the current post.
         * @return int
         */
        protected function _get_the_author_posts():int{
            $post = $this->_get_post();
            if ( ! $post ) return 0;
            return $this->_count_user_posts( $post->post_author, $post->post_type );
        }//265 from author-template
    }
}else die;