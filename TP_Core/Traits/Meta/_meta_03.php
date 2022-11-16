<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 15:31
 */
namespace TP_Core\Traits\Meta;
use TP_Core\Traits\Inits\_init_meta;
use TP_Core\Libs\TP_Term;
if(ABSPATH){
    trait _meta_03 {
        use _init_meta;
        /**
         * @description Retrieves a list of registered meta keys for an object type.
         * @param $object_type
         * @param string $object_subtype
         * @return array
         */
        protected function _get_registered_meta_keys( $object_type, $object_subtype = '' ):array{
            if ( ! is_array( $this->tp_meta_keys ) || ! isset($this->tp_meta_keys[ $object_type ][ $object_subtype ] ) )
                return [];
            return $this->tp_meta_keys[ $object_type ][ $object_subtype ];
        }//1647
        /**
         * @description Retrieves registered metadata for a specified object.
         * @param $object_type
         * @param $object_id
         * @param string $meta_key
         * @return array|bool
         */
        protected function _get_registered_metadata( $object_type, $object_id, $meta_key = '' ){
            $object_subtype = $this->_get_object_subtype( $object_type, $object_id );
            if ( ! empty( $meta_key ) ) {
                if ( ! empty( $object_subtype ) && ! $this->_registered_meta_key_exists( $object_type, $meta_key, $object_subtype ) )
                    $object_subtype = '';
                if ( ! $this->_registered_meta_key_exists( $object_type, $meta_key, $object_subtype ) )
                    return false;
                $meta_keys     = $this->_get_registered_meta_keys( $object_type, $object_subtype );
                $meta_key_data = $meta_keys[ $meta_key ];
                $data = $this->_get_metadata( $object_type, $object_id, $meta_key, $meta_key_data['single'] );
                return $data;
            }
            $data = $this->_get_metadata( $object_type, $object_id );
            if ( ! $data ) return [];
            $meta_keys = $this->_get_registered_meta_keys( $object_type );
            if ( ! empty( $object_subtype ) )
                $meta_keys = array_merge( $meta_keys, $this->_get_registered_meta_keys( $object_type, $object_subtype ) );
            return array_intersect_key( $data, $meta_keys );
        }//1673
        /**
         * @description Filters out `register_meta()` args based on an allowed list.
         * @param $default_args
         * @param array ...$args
         * @return array
         */
        protected function _tp_register_meta_args_allowed_list( $default_args, ...$args ):array{
            return array_intersect_key( $default_args, $args );
        }//1719
        /**
         * @description Returns the object subtype for a given object ID of a specific type.
         * @param $object_type
         * @param $object_id
         * @return mixed
         */
        protected function _get_object_subtype( $object_type, $object_id ){
            $object_id      = (int) $object_id;
            $object_subtype = '';
            switch ( $object_type ) {
                case 'post':
                    $post_type = $this->_get_post_type( $object_id );
                    if ( ! empty( $post_type ) ) $object_subtype = $post_type;
                    break;
                case 'term':
                    $term = $this->_get_term( $object_id );
                    if ( ! $term instanceof TP_Term ) break;
                    $object_subtype = $term->taxonomy;
                    break;
                case 'comment':
                    $comment = $this->_get_comment( $object_id );
                    if ( ! $comment ) break;
                    $object_subtype = 'comment';
                    break;
                case 'user':
                    $user = $this->_get_user_by( 'id', $object_id );
                    if ( ! $user ) break;
                    $object_subtype = 'user';
                    break;
            }
            return $this->_apply_filters( "get_object_subtype_{$object_type}", $object_subtype, $object_id );
        }//1733
    }
}else die;