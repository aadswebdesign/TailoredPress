<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_post_type;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _post_01{
        use _post_objects;
        use _init_post_type;
        /**
         * @description Creates the initial post types when 'init' action is fired.
         */
        protected function _create_initial_post_types():void{
            $type = $this->_post_types();
            $this->_register_post_type('post', array_merge($type->shared,$type->post));
            $this->_register_post_type('page', array_merge($type->shared,$type->page));
            $this->_register_post_type('attachment', $type->attachment);
            $this->_register_post_type('revision', $type->revision);
            $this->_register_post_type('nav_menu_item', $type->nav_menu_item);
            $this->_register_post_type('custom_css', $type->custom_css);
            $this->_register_post_type('customize_changeset', $type->customize_changeset);
            $this->_register_post_type('oembed_cache', $type->oembed_cache);
            $this->_register_post_type('user_request', $type->user_request);
            $this->_register_post_type('tp_block', $type->tp_block);
            $this->_register_post_type('tp_template', $type->tp_template);
            $this->_register_post_type('tp_template_part', $type->tp_template_part);
            $this->_register_post_type('tp_global_styles', $type->tp_global_styles);
            $this->_register_post_type('tp_navigation', $type->tp_navigation);
            $status = $this->_post_status_levels();
            $this->_register_post_status('publish', $status->publish);
            $this->_register_post_status('future', $status->future);
            $this->_register_post_status('draft', $status->draft);
            $this->_register_post_status('pending', $status->pending);
            $this->_register_post_status('private', $status->private);
            $this->_register_post_status('trash', $status->trash);
            $this->_register_post_status('auto_draft', $status->auto_draft);
            $this->_register_post_status('inherit', $status->inherit);
            $this->_register_post_status('request_pending', $status->request_pending);
            $this->_register_post_status('request_confirmed', $status->request_confirmed);
            $this->_register_post_status('request_failed', $status->request_completed);
            //$this->_register_post_status('', $status->);
            //$this->_register_post_status('', $status->);
            //$this->_register_post_status('', $status->);
        }//20
        /**
         * @description Retrieve attached file path based on attachment ID.
         * @param $attachment_id
         * @param bool $unfiltered
         * @return string
         */
        protected function _get_attached_file( $attachment_id, $unfiltered = false ):string{
            $file = $this->_get_post_meta( $attachment_id, '_tp_attached_file', true );
            if ( $file && 0 !== strpos( $file, '/' ) && ! preg_match( '|^.:\\\|', $file ) ) {
                $uploads = $this->_tp_get_upload_dir();
                if ( false === $uploads['error'] ) $file = $uploads['basedir'] . "/$file";
            }
            if ( $unfiltered ) return $file;
            return $this->_apply_filters( 'get_attached_file', $file, $attachment_id );
        }//721
        /**
         * @description Update attachment file path based on attachment ID.
         * @param $attachment_id
         * @param $file
         * @return bool
         */
        protected function _update_attached_file( $attachment_id, $file ):bool{
            if ( ! $this->_get_post( $attachment_id ) ) return false;
            $file = $this->_apply_filters( 'update_attached_file', $file, $attachment_id );
            $file = $this->_tp_relative_upload_path( $file );
            if ( $file ) return $this->_update_post_meta( $attachment_id, '_tp_attached_file', $file );
            else return $this->_delete_post_meta( $attachment_id, '_tp_attached_file' );
        }//759
        /**
         * @description Return relative path to an uploaded file.
         * @param $path
         * @return mixed
         */
        protected function _tp_relative_upload_path( $path ){
            $new_path = $path;
            $uploads = $this->_tp_get_upload_dir();
            if ( 0 === strpos( $new_path, $uploads['basedir'] ) ) {
                $new_path = str_replace( $uploads['basedir'], '', $new_path );
                $new_path = ltrim( $new_path, '/' );
            }
            return $this->_apply_filters( '_tp_relative_upload_path', $new_path, $path );
        }//793
        /**
         * @description Retrieve all children of the post parent ID.
         * @param $args
         * @param string $output
         * @return array
         */
        protected function _get_children($output = OBJECT,TP_Post ...$args ):array{
            $kids = [];
            if ( empty( $args ) ) {
                if ( isset( $this->tp_post['post'] ) )
                    $args = array( 'post_parent' => (int) $this->tp_post['post']->post_parent );
                else return $kids;
            } elseif ($args  instanceof TP_Post && is_object((object) $args) )
                $args = array( 'post_parent' => (int) $args->post_parent );
            elseif ( is_numeric((int) $args ) )$args = array( 'post_parent' => (int) $args );
            $defaults = ['numberposts' => -1,'post_type' => 'any','post_status' => 'any','post_parent' => 0,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $children = $this->_get_posts( $parsed_args );
            if ( ! $children ) return $kids;
            if ( ! empty( $parsed_args['fields'] ) ) return $children;
            $this->_update_post_cache( $children );
            foreach ( $children as $key => $child ) $kids[ $child->ID ] = $children[ $key ];
            if (OBJECT === $output) {
                return $kids;
            }
            if (ARRAY_A === $output) {
                $weens = [];
                foreach ($kids as $kid )
                    $weens[ $kid->ID ] = get_object_vars( $kids[ $kid->ID ] );
                return $weens;
            }
            if (ARRAY_N === $output) {
                $babes = [];
                foreach ($kids as $kid )
                    $babes[ $kid->ID ] = array_values( get_object_vars( $kids[ $kid->ID ] ) );
                return $babes;
            } else return $kids;
        }//864
        /**
         * @description Get extended entry info (<!--more-->).
         * @param $post
         * @return array
         */
        protected function _get_extended( $post ):array{
            if ( preg_match( '/<!--more(.*?)?-->/', $post, $matches ) ) {
                @list($main, $extended) = explode( $matches[0], $post, 2 );
                $more_text             = $matches[1];
            } else {
                $main      = $post;
                $extended  = '';
                $more_text = '';
            }
            $main      = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $main );
            $extended  = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $extended );
            $more_text = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $more_text );
            return ['main' => $main,'extended' => $extended,'more_text' => $more_text,];
        }//944

        /**
         * @description get_extended( $post )
         * @param string $output
         * @param string $filter
         * @param object $post
         * @return mixed
         */
        protected function _get_post($output = OBJECT, $filter = 'raw',object $post=null ){
            if ( empty( $post ) && isset( $this->tp_post['post'] ) )
                $post = $this->tp_post['post'];
            if ( $post instanceof TP_Post ) $_post = $post;
            elseif ( is_object( $post ) ) {
                if ( empty( $post->filter ) ) {
                    $_post = $this->_sanitize_post( $post, 'raw' );
                    $_post = new TP_Post( $_post );
                }elseif ( 'raw' === $post->filter )
                    $_post = new TP_Post( $post );
                else $_post = TP_Post::get_instance( $post->ID );
            }else $_post = TP_Post::get_instance( $post );
            if ( ! $_post ) return null;
            $_post = $_post->filter( $filter );
            if (($_post instanceof TP_Post) && ARRAY_A === $output ) return $_post->to_array();
            elseif ( ARRAY_N === $output) return array_values( $_post->to_array() );
            return $_post;
        }//988
        /**
         * @description Retrieves the IDs of the ancestors of a post.
         * @param $post
         * @return array
         */
        protected function _get_post_ancestors( $post ):array{
            $post = $this->_get_post( $post );
            if ( ! $post || empty( $post->post_parent ) || $post->post_parent === $post->ID )
                return [];
            $ancestors = [];
            $id          = $post->post_parent;
            $ancestors[] = $id;
            while ( $ancestor = $this->_get_post( $id ) ) {
                if ( empty( $ancestor->post_parent ) || ( $ancestor->post_parent === $post->ID ) || in_array( $ancestor->post_parent, $ancestors, true ) )
                    break;
                $id= $ancestor->post_parent;
                $ancestors[] = $id;
            }
            return $ancestors;
        }//1031
        /**
         * @description Retrieve data from a post field based on Post ID.
         * @param $field
         * @param null $post
         * @param string $context
         * @return string
         */
        protected function _get_post_field( $field, $post = null, $context = 'display' ):string{
            $post = $this->_get_post( $post );
            if ( ! $post ) return '';
            if ( ! isset( $post->$field ) )return '';
            return $this->_sanitize_post_field( $field, $post->$field, $post->ID, $context );
        }//1076
        /**
         * @description Retrieve the mime type of an attachment based on the ID.
         * @param null $post
         * @return bool|string
         */
        protected function _get_post_mime_type( $post = null ){
            $post = $this->_get_post( $post );
            if ( is_object( $post ) ) return $post->post_mime_type;
            return false;
        }//1101
    }
}else die;