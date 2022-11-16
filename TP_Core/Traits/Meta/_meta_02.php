<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 15:31
 */
namespace TP_Core\Traits\Meta;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_meta;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\TP_Metadata_Lazyloader;
if(ABSPATH){
    trait _meta_02 {
        use _init_db;
        use _init_meta;
        use _init_error;
        use _init_queries;
        /**
         * @description Updates the metadata cache for the specified objects.
         * @param $meta_type
         * @param $object_ids
         * @return array|bool
         */
        protected function _update_meta_cache( $meta_type, $object_ids ){
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! $object_ids ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $column = $this->_sanitize_key( $meta_type . '_id' );
            if ( ! is_array( $object_ids ) ) {
                $object_ids = preg_replace( '|[^0-9,]|', '', $object_ids );
                $object_ids = explode( ',', $object_ids );
            }
            $object_ids = array_map( 'intval', $object_ids );
            $check = $this->_apply_filters( "update_{$meta_type}_metadata_cache", null, $object_ids );
            if ( null !== $check )
                return (bool) $check;
            $cache_key      = $meta_type . '_meta';
            $non_cached_ids = [];
            $cache          = [];
            $cache_values   = $this->_tp_cache_get_multiple( $object_ids, $cache_key );
            foreach ( $cache_values as $id => $cached_object ) {
                if ( false === $cached_object ) $non_cached_ids[] = $id;
                else $cache[ $id ] = $cached_object;
            }
            if ( empty( $non_cached_ids ) ) return $cache;
            $id_list   = implode( ',', $non_cached_ids );
            $id_column = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $meta_list = $tpdb->get_results( TP_SELECT . " $column, meta_key, meta_value FROM $table WHERE $column IN ($id_list) ORDER BY $id_column ASC", ARRAY_A );
            if ( ! empty( $meta_list ) ) {
                foreach ( $meta_list as $metarow ) {
                    $mpid = (int) $metarow[ $column ];
                    $mkey = $metarow['meta_key'];
                    $mval = $metarow['meta_value'];
                    if ( ! isset( $cache[ $mpid ] ) || ! is_array( $cache[ $mpid ] ) ) $cache[ $mpid ] = [];
                    if ( ! isset( $cache[ $mpid ][ $mkey ] ) || ! is_array( $cache[ $mpid ][ $mkey ] ) ) $cache[ $mpid ][ $mkey ] = array();
                    $cache[ $mpid ][ $mkey ][] = $mval;
                }
            }
            foreach ( $non_cached_ids as $id ) {
                if ( ! isset( $cache[ $id ] ) ) $cache[ $id ] = [];
                $this->_tp_cache_add( $id, $cache[ $id ], $cache_key );
            }
            return $cache;
        }//1107
        /**
         * @description Retrieves the queue for lazy-loading metadata.
         * @return TP_Metadata_Lazyloader
         */
        protected function _tp_metadata_lazy_loader(): TP_Metadata_Lazyloader{
            static $tp_metadata_lazyloader;
            if ( null === $tp_metadata_lazyloader )
                $tp_metadata_lazyloader = new TP_Metadata_Lazyloader();
            return $tp_metadata_lazyloader;
        }//1211
        /**
         * @description Given a meta query, generates SQL clauses to be appended to a main query.
         * @param $meta_query
         * @param $type
         * @param $primary_table
         * @param $primary_id_column
         * @param null $context
         * @return mixed
         */
        protected function _get_meta_sql( $meta_query, $type, $primary_table, $primary_id_column, $context = null ){
            if($meta_query !== null){
                $meta_query = $this->_init_meta_query();
            }
            return $meta_query->get_sql( $type, $primary_table, $primary_id_column, $context );
        }//1235
        /**
         * @description Retrieves the name of the metadata table for the specified object type.
         * @param $type
         * @return bool
         */
        protected function _get_meta_table( $type ):bool{
           $tpdb = $this->_init_db();
           $table_name = $type . 'meta';
           if ( empty( $tpdb->$table_name ) ) return false;
           return $tpdb->$table_name;
       }//1251
        /**
         * @description Determines whether a meta key is considered protected.
         * @param $meta_key
         * @param string $meta_type
         * @return mixed
         */
        protected function _is_protected_meta( $meta_key, $meta_type = '' ){
            $sanitized_key = preg_replace( "/[^\x20-\x7E\/p{L}]/", '', $meta_key );
            $protected     = $sanitized_key !== '' && ( '_' === $sanitized_key[0] );
            return $this->_apply_filters( 'is_protected_meta', $protected, $meta_key, $meta_type );
        }//1273
        /**
         * @description Sanitizes meta value.
         * @param $meta_key
         * @param $meta_value
         * @param $object_type
         * @param string $object_subtype
         * @return mixed
         */
        protected function _sanitize_meta( $meta_key, $meta_value, $object_type, $object_subtype = '' ){
            if ( ! empty( $object_subtype ) && $this->_has_filter( "sanitize_{$object_type}_meta_{$meta_key}_for_{$object_subtype}" ) )
                return $this->_apply_filters( "sanitize_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $meta_value, $meta_key, $object_type, $object_subtype );
            return $this->_apply_filters( "sanitize_{$object_type}_meta_{$meta_key}", $meta_value, $meta_key, $object_type );
        }//1303
        /**
         * @description Registers a meta key.
         * @param $object_type
         * @param $meta_key
         * @param array ...$args
         * @return bool
         */
        protected function _register_meta( $object_type, $meta_key, ...$args):bool{
            $defaults = ['object_subtype' => '','type' => 'string','description' => '','default' => '',
                'single' => false,'sanitize_callback' => null,'auth_callback' => null,'show_in_rest' => false,];
            $has_old_sanitize_cb = false;
            $has_old_auth_cb     = false;
            if ( is_callable( $args ) ) {
                $args = ['sanitize_callback' => $args,];
                $has_old_sanitize_cb = true;
            }
            $args = $this->_apply_filters( 'register_meta_args', $args, $defaults, $object_type, $meta_key );
            unset( $defaults['default'] );
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( false !== $args['show_in_rest'] && 'array' === $args['type'] ) {
                if ( ! is_array( $args['show_in_rest'] ) || ! isset( $args['show_in_rest']['schema']['items'] ) ) {
                    $this->_doing_it_wrong( __FUNCTION__, $this->__( 'When registering an "array" meta type to show in the REST API, you must specify the schema for each array item in "show_in_rest.schema.items".' ), '0.0.1' );
                    return false;
                }
            }
            $object_subtype = ! empty( $args['object_subtype'] ) ? $args['object_subtype'] : '';
            if ( empty( $args['auth_callback'] ) ) {
                if ( $this->_is_protected_meta( $meta_key, $object_type ) ) $args['auth_callback'] = '__return_false';
                else $args['auth_callback'] = '__return_true';
            }
            if ( is_callable( $args['auth_callback'] ) ) {
                if ( ! empty( $object_subtype ) )
                    $this->_add_filter( "auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $args['auth_callback'], 10, 6 );
                else $this->_add_filter( "auth_{$object_type}_meta_{$meta_key}", $args['auth_callback'], 10, 6 );
            }
            if ( array_key_exists( 'default', $args ) ) {
                $schema = $args;
                if ( is_array( $args['show_in_rest'] ) && isset( $args['show_in_rest']['schema'] ) )
                    $schema = array_merge( $schema, $args['show_in_rest']['schema'] );
                $check = $this->_rest_validate_value_from_schema( $args['default'], $schema );
                if ( $this->_init_error( $check ) ) {
                    $this->_doing_it_wrong( __FUNCTION__, $this->__( 'When registering a default meta value the data must match the type provided.' ), '5.5.0' );
                    return false;
                }
                if ( ! $this->_has_filter( "default_{$object_type}_metadata", 'filter_default_metadata' ) )
                    $this->_add_filter( "default_{$object_type}_metadata", 'filter_default_metadata', 10, 5 );
            }
            if ( ! $has_old_auth_cb && ! $has_old_sanitize_cb ) {
                unset( $args['object_subtype'] );
                $this->tp_meta_keys[ $object_type ][ $object_subtype ][ $meta_key ] = $args;
                return true;
            }
            return false;
        }//1389
        /**
         * @description Filters into default_{$object_type}_metadata and adds in default value.
         * @param $value
         * @param $object_id
         * @param $meta_key
         * @param $single
         * @param $meta_type
         * @return array
         */
        protected function _filter_default_metadata( $value, $object_id, $meta_key, $single, $meta_type ):array{
            if ( $this->_tp_installing() ) return $value;
            if ( ! is_array( $this->tp_meta_keys ) || ! isset( $this->tp_meta_keys[ $meta_type ] ) )
                return $value;
            $defaults = [];
            foreach ( $this->tp_meta_keys[ $meta_type ] as $sub_type => $meta_data ) {
                foreach ( $meta_data as $_meta_key => $args ) {
                    if ( $_meta_key === $meta_key && array_key_exists( 'default', $args ) )
                        $defaults[ $sub_type ] = $args;
                }
            }
            if ( ! $defaults ) return $value;
            if ( isset( $defaults[''] ) ) $metadata = $defaults[''];
            else {
                $sub_type = $this->_get_object_subtype( $meta_type, $object_id );
                if ( ! isset( $defaults[ $sub_type ] ) ) return $value;
                $metadata = $defaults[ $sub_type ];
            }
            if ( $single ) $value = $metadata['default'];
            else $value = array( $metadata['default'] );
            return $value;
        }//1523
        /**
         * @description Checks if a meta key is registered.
         * @param $object_type
         * @param $meta_key
         * @param string $object_subtype
         * @return bool
         */
        protected function _registered_meta_key_exists( $object_type, $meta_key, $object_subtype = '' ):bool{
            $meta_keys = $this->_get_registered_meta_keys( $object_type, $object_subtype );
            return isset( $meta_keys[ $meta_key ] );
        }//1580
        /**
         * @description Unregisters a meta key from the list of registered keys.
         * @param $object_type
         * @param $meta_key
         * @param string $object_subtype
         * @return bool
         */
        protected function _unregister_meta_key( $object_type, $meta_key, $object_subtype = '' ):bool{
            if ( ! $this->_registered_meta_key_exists( $object_type, $meta_key, $object_subtype ) )
                return false;
            $args = $this->tp_meta_keys[ $object_type ][ $object_subtype ][ $meta_key ];
            if ( isset( $args['sanitize_callback'] ) && is_callable( $args['sanitize_callback'] ) ) {
                if ( ! empty( $object_subtype ) )
                    $this->_remove_filter( "sanitize_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $args['sanitize_callback'] );
                else $this->_remove_filter( "sanitize_{$object_type}_meta_{$meta_key}", $args['sanitize_callback'] );
            }
            if ( isset( $args['auth_callback'] ) && is_callable( $args['auth_callback'] ) ) {
                if ( ! empty( $object_subtype ) )
                    $this->_remove_filter( "auth_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $args['auth_callback'] );
                else $this->_remove_filter( "auth_{$object_type}_meta_{$meta_key}", $args['auth_callback'] );
            }
            unset( $this->tp_meta_keys[ $object_type ][ $object_subtype ][ $meta_key ] );
            if ( empty( $this->tp_meta_keys[ $object_type ][ $object_subtype ] ) )
                unset( $this->tp_meta_keys[ $object_type ][ $object_subtype ] );
            if ( empty( $this->tp_meta_keys[ $object_type ] ) )
                unset( $this->tp_meta_keys[ $object_type ] );
            return true;
        }//1598
    }
}else die;