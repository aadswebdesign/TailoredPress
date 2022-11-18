<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_post;
if(ABSPATH){
    trait _post_02{
        use _init_post;
        /**
         * @description Retrieve the post status based on the post ID.
         * @param null $post
         * @return bool
         */
        protected function _get_post_status( $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! is_object( $post ) ) return false;
            $post_status = $post->post_status;
            if ($post->ID === $post->post_parent || ('attachment' === $post->post_type && 'inherit' === $post_status)) {
                if ( 0 === $post->post_parent || ! $this->_get_post( $post->post_parent ))
                    $post_status = 'publish';
                elseif ( 'trash' === $this->_get_post_status( $post->post_parent ) ) {
                    $post_status = $this->_get_post_meta( $post->post_parent, '_tp_trash_meta_status', true );
                    if ( ! $post_status ) $post_status = 'publish';
                } else $post_status = $this->_get_post_status( $post->post_parent );
            } elseif ('attachment' === $post->post_type && ! in_array( $post_status, array( 'private', 'trash', 'auto-draft' ), true ))
                 $post_status = 'publish';
            return $this->_apply_filters( 'get_post_status', $post_status, $post );
        }//1122
        /**
         * @description Retrieve all of the TailoredPress supported post statuses.
         * @return array
         */
        protected function _get_post_statuses():array{
            $status = ['draft' => $this->__( 'Draft' ),'pending' => $this->__( 'Pending Review' ),
                'private' => $this->__( 'Private' ), 'publish' => $this->__( 'Published' ),];
            return $status;
        }//1188
        /**
         * @description Retrieve all of the TailoredPress support page statuses.
         * @return array
         */
        protected function _get_page_statuses():array{
            $status = ['draft'   => $this->__( 'Draft' ),'private' => $this->__( 'Private' ), 'publish' => $this->__( 'Published' ),];
            return $status;
        }//1209
        /**
         * @description Return statuses for privacy requests.
         * @return array
         */
        protected function _tp_privacy_statuses():array{
            return [
                'request-pending'   => $this->_x( 'Pending', 'request status' ),      // Pending confirmation from user.
                'request-confirmed' => $this->_x( 'Confirmed', 'request status' ),    // User has confirmed the action.
                'request-failed'    => $this->_x( 'Failed', 'request status' ),       // User failed to confirm the action.
                'request-completed' => $this->_x( 'Completed', 'request status' ),    // Admin has handled the request.
            ];
        }//1227
        /**
         * @description Register a post status. Do not use before init.
         * @param $post_status
         * @param \array[] ...$args
         * @return \array[]|object
         */
        protected function _register_post_status( $post_status, array ...$args){
            if ( ! is_array( $this->tp_post_statuses ) ) $this->tp_post_statuses = [];
            $defaults = ['label' => false,'label_count' => false, 'exclude_from_search' => null,
                '_builtin' => false,'public' => null,'internal' => null,'protected' => null,'private' => null,
                'publicly_queryable' => null,'show_in_admin_status_list' => null,'show_in_admin_all_list' => null,'date_floating' => null,];
            $args = $this->_tp_parse_args( $args, $defaults );
            $args= (array) $args;
            $post_status = $this->_sanitize_key( $post_status );
            $args->name  = $post_status;
            if ( null === $args->public && null === $args->internal && null === $args->protected && null === $args->private )
                $args->internal = true;
            if ( null === $args->public ) $args->public = false;
            if ( null === $args->private ) $args->private = false;
            if ( null === $args->protected ) $args->protected = false;
            if ( null === $args->internal ) $args->internal = false;
            if ( null === $args->publicly_queryable ) $args->publicly_queryable = $args->public;
            if ( null === $args->exclude_from_search ) $args->exclude_from_search = $args->internal;
            if ( null === $args->show_in_admin_all_list ) $args->show_in_admin_all_list = ! $args->internal;
            if ( null === $args->show_in_admin_status_list ) $args->show_in_admin_status_list = ! $args->internal;
            if ( null === $args->date_floating ) $args->date_floating = false;
            if ( false === $args->label ) $args->label = $post_status;
            if ( false === $args->label_count ) $args->label_count = $this->_n_noop( $args->label, $args->label );
            $this->tp_post_statuses[ $post_status ] = $args;
            return $args;
        }//1283

        /**
         * @description Retrieve a post status object by name.
         * @param $post_status
         * @return bool
         */
        protected function _get_post_status_object( $post_status ):bool{
            if ( empty( $this->tp_post_statuses[ $post_status ] ) ) return false;
            return $this->tp_post_statuses[ $post_status ];
        }//1378
        /**
         * @description Get a list of post statuses.
         * @param string $output
         * @param string $operator
         * @param \array[] ...$args
         * @return mixed
         */
        protected function _get_post_stati( $output = 'names', $operator = 'and', array ...$args ){
            $field = ( 'names' === $output ) ? 'name' : false;
            return $this->_tp_filter_object_list( $this->tp_post_statuses, $args, $operator, $field );
        }//1405
        /**
         * @description Whether the post type is hierarchical.
         * @param $post_type
         * @return bool
         */
        protected function _is_post_type_hierarchical( $post_type ):bool{
            if ( ! $this->_post_type_exists( $post_type ) ) return false;
            $post_type = $this->_get_post_type_object( $post_type );
            return $post_type->hierarchical;
        }//1425
        /**
         * @description Determines whether a post type is registered.
         * @param $post_type
         * @return mixed
         */
        protected function _post_type_exists( $post_type ){
            if(isset($post_type)){
                return $this->_get_post_type_object( $post_type );
            }
            return false;
        }//1448
        /**
         * @description Retrieves the post type of the current post or of a given post.
         * @param null $post
         * @return bool
         */
        protected function _get_post_type( $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( $post ) return $post->post_type;
            return false;
        }//1460
    }
}else die;
