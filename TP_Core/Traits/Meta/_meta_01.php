<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 15:31
 */
namespace TP_Core\Traits\Meta;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\Queries\TP_Comment_Query;
if(ABSPATH){
    trait _meta_01 {
        use _init_db;
        /**
         * @description Adds metadata for the specified object.
         * @param $meta_type
         * @param $object_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return bool|int
         */
        protected function _add_metadata( $meta_type, $object_id, $meta_key, $meta_value, $unique = false ){
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) return false;
            $object_id = $this->_abs_int( $object_id );
            if ( ! $object_id ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $meta_subtype = $this->_get_object_subtype( $meta_type, $object_id );
            $column = $this->_sanitize_key( $meta_type . '_id' );
            $meta_key   = $this->_tp_unslash( $meta_key );
            $meta_value = $this->_tp_unslash( $meta_value );
            $meta_value = $this->_sanitize_meta( $meta_key, $meta_value, $meta_type, $meta_subtype );
            $check = $this->_apply_filters( "add_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $unique );
            if ( null !== $check ) return $check;
            if ( $unique && $tpdb->get_var($tpdb->prepare(TP_SELECT . " COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d",$meta_key, $object_id)))
                return false;
            $_meta_value = $meta_value;
            $meta_value  = $this->_maybe_serialize( $meta_value );
            $this->_do_action( "add_{$meta_type}_meta", $object_id, $meta_key, $_meta_value );
            $result = $tpdb->insert( $table,[ $column => $object_id,'meta_key' => $meta_key,'meta_value' => $meta_value,]);
            if ( ! $result ) return false;
            $mid = (int) $tpdb->insert_id;
            $this->_tp_cache_delete( $object_id, $meta_type . '_meta' );
            $this->_do_action( "added_{$meta_type}_meta", $mid, $object_id, $meta_key, $_meta_value );
            return $mid;
        }//30
        /**
         * @description Updates metadata for the specified object. If no value already exists for the specified object
         * @param $meta_type
         * @param $object_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return bool
         */
        protected function _update_metadata( $meta_type, $object_id, $meta_key, $meta_value, $prev_value = '' ):bool{
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) return false;
            $object_id = $this->_abs_int( $object_id );
            if ( ! $object_id ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $meta_subtype = $this->_get_object_subtype( $meta_type, $object_id );
            $column    = $this->_sanitize_key( $meta_type . '_id' );
            $id_column = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $raw_meta_key = $meta_key;
            $meta_key     = $this->_tp_unslash( $meta_key );
            $passed_value = $meta_value;
            $meta_value   = $this->_tp_unslash( $meta_value );
            $meta_value   = $this->_sanitize_meta( $meta_key, $meta_value, $meta_type, $meta_subtype );
            $check = $this->_apply_filters( "update_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $prev_value );
            if ( null !== $check ) return (bool) $check;
            if ( empty( $prev_value ) ) {
                $old_value = $this->_get_metadata_raw( $meta_type, $object_id, $meta_key );
                if ($this->_is_countable((int)$old_value) && count($old_value) === 1 && $old_value[0] === $meta_value) return false;
            }
            $meta_ids = $tpdb->get_col( $tpdb->prepare(TP_SELECT . " $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
            if ( empty( $meta_ids ) ) return $this->_add_metadata( $meta_type, $object_id, $raw_meta_key, $passed_value );
            $_meta_value = $meta_value;
            $meta_value  = $this->_maybe_serialize( $meta_value );
            $data  = compact( 'meta_value' );
            $where = [$column  => $object_id,'meta_key' => $meta_key,];
            if ( ! empty( $prev_value ) ) {
                $prev_value          = $this->_maybe_serialize( $prev_value );
                $where['meta_value'] = $prev_value;
            }
            foreach ( $meta_ids as $meta_id ) {
                $this->_do_action( "update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
                if ( 'post' === $meta_type ) $this->_do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
            }
            $result = $tpdb->update( $table, $data, $where );
            if ( ! $result ) return false;
            $this->_tp_cache_delete( $object_id, $meta_type . '_meta' );
            foreach ( $meta_ids as $meta_id ) {
                $this->_do_action( "updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
                if ( 'post' === $meta_type ) $this->_do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
            }
            return true;
        }//180
        /**
         * @description Deletes metadata for the specified object.
         * @param $meta_type
         * @param $object_id
         * @param $meta_key
         * @param string $meta_value
         * @param bool $delete_all
         * @return bool
         */
        protected function _delete_metadata( $meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false ):bool{
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! $meta_key || (! is_numeric( $object_id ) && ! $delete_all) ) return false;
            $object_id = $this->_abs_int( $object_id );
            if ( ! $object_id && ! $delete_all ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $type_column = $this->_sanitize_key( $meta_type . '_id' );
            $id_column   = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $meta_key   = $this->_tp_unslash( $meta_key );
            $meta_value = $this->_tp_unslash( $meta_value );
            $check = $this->_apply_filters( "delete_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $delete_all );
            if ( null !== $check ) return (bool) $check;
            $_meta_value = $meta_value;
            $meta_value  = $this->_maybe_serialize( $meta_value );
            $query = $tpdb->prepare( TP_SELECT . " $id_column FROM $table WHERE meta_key = %s", $meta_key );
            if ( ! $delete_all )  $query .= $tpdb->prepare( " AND $type_column = %d", $object_id );
            if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value )
                $query .= $tpdb->prepare( ' AND meta_value = %s', $meta_value );
            $meta_ids = $tpdb->get_col( $query );
            if ( ! count( $meta_ids ) ) return false;
            $object_ids = null;
            if ( $delete_all ) {
                if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value )
                    $object_ids = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " $type_column FROM $table WHERE meta_key = %s AND meta_value = %s", $meta_key, $meta_value ) );
                else $object_ids = $tpdb->get_col( $tpdb->prepare( TP_SELECT . " $type_column FROM $table WHERE meta_key = %s", $meta_key ) );
            }
            $this->_do_action( "delete_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );
            if ( 'post' === $meta_type ) $this->_do_action( 'delete_postmeta', $meta_ids );
            $query = TP_DELETE . " FROM $table WHERE $id_column IN( " . implode( ',', $meta_ids ) . ' )';
            $count = $tpdb->query( $query );
            if ( ! $count ) return false;
            if ( $delete_all ) {
                foreach ( (array) $object_ids as $o_id )
                    $this->_tp_cache_delete( $o_id, $meta_type . '_meta' );
            } else $this->_tp_cache_delete( $object_id, $meta_type . '_meta' );
            $this->_do_action( "deleted_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );
            if ( 'post' === $meta_type ) $this->_do_action( 'deleted_postmeta', $meta_ids );
            return true;
        }//377
        /**
         * @description Retrieves the value of a metadata field for the specified object type and ID.
         * @param $meta_type
         * @param $object_id
         * @param string $meta_key
         * @param bool $single
         * @return string
         */
        protected function _get_metadata( $meta_type, $object_id, $meta_key = '', $single = false ):string{
            $value = $this->_get_metadata_raw( $meta_type, $object_id, $meta_key, $single );
            if ( ! is_null( $value ) ) return $value;
            return $this->_get_metadata_default( $meta_type, $object_id, $meta_key, $single );
        }//571
        /**
         * @description Retrieves raw metadata value for the specified object.
         * @param $meta_type
         * @param $object_id
         * @param string $meta_key
         * @param bool $single
         * @return mixed
         */
        protected function _get_metadata_raw( $meta_type, $object_id, $meta_key = '', $single = false ){
            if ( ! $meta_type || ! is_numeric( $object_id ) )
                return false;
            $object_id = $this->_abs_int( $object_id );
            if ( ! $object_id ) return false;
            $check = $this->_apply_filters( "get_{$meta_type}_metadata", null, $object_id, $meta_key, $single, $meta_type );
            if ( null !== $check ) {
                if ( $single && is_array( $check ) ) return $check[0];
                else return $check;
            }
            $meta_cache = $this->_tp_cache_get( $object_id, $meta_type . '_meta' );
            if ( ! $meta_cache ) {
                $meta_cache = $this->_update_meta_cache( $meta_type, array( $object_id ) );
                if ( isset( $meta_cache[ $object_id ] ) ) $meta_cache = $meta_cache[ $object_id ];
                else $meta_cache = null;
            }
            if ( ! $meta_key ) return $meta_cache;
            if ( isset( $meta_cache[ $meta_key ] ) ) {
                if ( $single ) return $this->_maybe_unserialize( $meta_cache[ $meta_key ][0] );
                else return array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
            }
            return null;
        }//598
        /**
         * @description Retrieves default metadata value for the specified meta key and object.
         * @param $meta_type
         * @param $object_id
         * @param $meta_key
         * @param bool $single
         * @return array|string
         */
        protected function _get_metadata_default( $meta_type, $object_id, $meta_key, $single = false ){
            if ( $single )  $value = '';
            else  $value = [];
            $value = $this->_apply_filters( "default_{$meta_type}_metadata", $value, $object_id, $meta_key, $single, $meta_type );
            if ( ! $single && ! $this->_tp_is_numeric_array( $value ) )  $value = [$value];
            return $value;
        }//685
        /**
         * @description Determines if a meta field with the given key exists for the given object ID.
         * @param $meta_type
         * @param $object_id
         * @param $meta_key
         * @return bool
         */
        protected function _metadata_exists( $meta_type, $object_id, $meta_key ):bool{
            if ( ! $meta_type || ! is_numeric( $object_id ) ) return false;
            $object_id = $this->_abs_int( $object_id );
            if ( ! $object_id ) return false;
            $check = $this->_apply_filters( "get_{$meta_type}_metadata", null, $object_id, $meta_key, true, $meta_type );
            if ( null !== $check ) return (bool) $check;
            $meta_cache = $this->_tp_cache_get( $object_id, $meta_type . '_meta' );
            if ( ! $meta_cache ) {
                $meta_cache = $this->_update_meta_cache( $meta_type, array( $object_id ) );
                $meta_cache = $meta_cache[ $object_id ];
            }
            if ( isset( $meta_cache[ $meta_key ] ) ) return true;
            return false;
        }//735
        /**
         * @description Retrieves metadata by meta ID.
         * @param $meta_type
         * @param $meta_id
         * @return array|bool|null
         */
        protected function _get_metadata_by_mid( $meta_type, $meta_id ){
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! is_numeric( $meta_id ) || floor( $meta_id ) !== $meta_id ) return false;
            $meta_id = (int) $meta_id;
            if ( $meta_id <= 0 ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $check = $this->_apply_filters( "get_{$meta_type}_metadata_by_mid", null, $meta_id );
            if ( null !== $check ) return $check;
            $id_column = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $meta = $tpdb->get_row( $tpdb->prepare( TP_SELECT . " * FROM $table WHERE $id_column = %d", $meta_id ) );
            if ( empty( $meta ) ) return false;
            if ( isset( $meta->meta_value ) ) $meta->meta_value = $this->_maybe_unserialize( $meta->meta_value );
            return $meta;
        }//788
        /**
         * @description Updates metadata by meta ID.
         * @param $meta_type
         * @param $meta_id
         * @param $meta_value
         * @param bool $meta_key
         * @return bool
         */
        protected function _update_metadata_by_mid( $meta_type, $meta_id, $meta_value, $meta_key = false ):bool{
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! is_numeric( $meta_id ) || floor( $meta_id ) !== $meta_id ) return false;
            $meta_id = (int) $meta_id;
            if ( $meta_id <= 0 ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $column    = $this->_sanitize_key( $meta_type . '_id' );
            $id_column = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $check = $this->_apply_filters( "update_{$meta_type}_metadata_by_mid", null, $meta_id, $meta_value, $meta_key );
            if ( null !== $check ) return (bool) $check;
            $meta = $this->_get_metadata_by_mid( $meta_type, $meta_id );
            if ($meta instanceof \stdClass && $meta ) {
                $original_key = $meta->meta_key;
                $object_id    = $meta->{$column};
                if ( false === $meta_key ) $meta_key = $original_key;
                elseif ( ! is_string((string)$meta_key ) ) return false;
                $meta_subtype = $this->_get_object_subtype( $meta_type, $object_id );
                $_meta_value = $meta_value;
                $meta_value  = $this->_sanitize_meta( $meta_key, $meta_value, $meta_type, $meta_subtype );
                $meta_value  = $this->_maybe_serialize( $meta_value );
                $data = ['meta_key' => $meta_key, 'meta_value' => $meta_value,];
                $where               = [];
                $where[ $id_column ] = $meta_id;
                $this->_do_action( "update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
                if ( 'post' === $meta_type ) $this->_do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
                $result = $tpdb->update( $table, $data, $where, '%s', '%d' );
                if ( ! $result ) return false;
                $this->_tp_cache_delete( $object_id, $meta_type . '_meta' );
                $this->_do_action( "updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
                if ( 'post' === $meta_type ) $this->_do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
                return true;
            }
            return false;
        }//858
        /**
         * @description Deletes metadata by meta ID.
         * @param $meta_type
         * @param $meta_id
         * @return bool
         */
        protected function _delete_metadata_by_mid( $meta_type, $meta_id ):bool{
            $tpdb = $this->_init_db();
            if ( ! $meta_type || ! is_numeric( $meta_id ) || floor( $meta_id ) !== $meta_id ) return false;
            $meta_id = (int) $meta_id;
            if ( $meta_id <= 0 ) return false;
            $table = $this->_get_meta_table( $meta_type );
            if ( ! $table ) return false;
            $column    = $this->_sanitize_key( $meta_type . '_id' );
            $id_column = ( 'user' === $meta_type ) ? 'umeta_id' : 'meta_id';
            $check = $this->_apply_filters( "delete_{$meta_type}_metadata_by_mid", null, $meta_id );
            if ( null !== $check ) return (bool) $check;
            $meta = $this->_get_metadata_by_mid( $meta_type, $meta_id );
            if ($meta  instanceof TP_Comment_Query && $meta ) {
                $object_id = (int) $meta->{$column};
                $this->_do_action( "delete_{$meta_type}_meta", (array) $meta_id, $object_id, $meta->meta_key, $meta->meta_value );
                if ( 'post' === $meta_type || 'comment' === $meta_type )  $this->_do_action( "delete_{$meta_type}meta", $meta_id );
                $result = $tpdb->delete( $table, [$id_column => $meta_id] );
                $this->_tp_cache_delete( $object_id, $meta_type . '_meta' );
                $this->_do_action( "deleted_{$meta_type}_meta", (array) $meta_id, $object_id, $meta->meta_key, $meta->meta_value );
                if ( 'post' === $meta_type || 'comment' === $meta_type )
                    $this->_do_action( "deleted_{$meta_type}meta", $meta_id );
                return $result;
            }
            return false;
        }//980
    }
}else die;