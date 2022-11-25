<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 17:12
 */
namespace TP_Core\Libs\RestApi\Fields;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\Meta\_meta_02;
use TP_Core\Traits\Meta\_meta_03;
use TP_Core\Traits\RestApi\_rest_api_04;
use TP_Core\Traits\RestApi\_rest_api_06;
use TP_Core\Traits\RestApi\_rest_api_07;
use TP_Core\Traits\RestApi\_rest_api_08;
if(ABSPATH){
    abstract class TP_REST_Meta_Fields{
        use _init_error;
        use _I10n_01;
        use _meta_01;
        use _meta_03;
        use _formats_11;
        use _rest_api_04;
        use _rest_api_06;
        use _rest_api_07;
        use _rest_api_08;
        use _capability_01;
        use _meta_02;
        abstract protected function _get_meta_type();//25
        abstract protected function _get_meta_subtype();//34
        abstract protected function _get_rest_field_type();//45
        public function get_value( $object_id, $request ): array{
            $fields   = $this->_get_registered_fields();
            $response = [];
            foreach ($fields as $meta_key => $args ) {
                $name       = $args['name'];
                $all_values = $this->_get_metadata( $this->_get_meta_type(), $object_id, $meta_key, false );
                if ( $args['single'] ) {
                    if ( empty( $all_values ) ) $value = $args['schema']['default'];
                    else $value = $all_values[0];
                    $value = $this->_prepare_value_for_response( $value, $request, $args );
                } else {
                    $value = [];
                    if ( is_array( $all_values ) ) {
                        foreach ( $all_values as $row ) $value[] = $this->_prepare_value_for_response( $row, $request, $args );
                    }
                }
                $response[ $name ] = $value;
            }
            return $response;
        }
        protected function _prepare_value_for_response( $value, $request, $args ) {
            if ( ! empty( $args['prepare_callback'] ) )
                $value = call_user_func( $args['prepare_callback'], $value, $request, $args );
            return $value;
        }//124
        public function update_value( $meta, $object_id ){
            $fields = $this->_get_registered_fields();
            foreach ($fields as $meta_key => $args ){
                $name = $args['name'];
                if ( ! array_key_exists( $name, $meta ) )  continue;
                $value = $meta[ $name ];
                if ( is_null( $value ) || ( [] === $value && ! $args['single'] ) ) {
                    $args = $this->_get_registered_fields()[ $meta_key ];
                    if ( $args['single'] ) {
                        $current = $this->_get_metadata( $this->_get_meta_type(), $object_id, $meta_key, true );
                        if ( $this->_init_error( $this->_rest_validate_value_from_schema( $current, $args['schema'] ) ) ) {
                            return new TP_Error('rest_invalid_stored_value',/* translators: %s: Custom field key. */
                                sprintf( $this->__( 'The %s property has an invalid stored value, and cannot be updated to null.' ), $name ),
                                ['status' => INTERNAL_SERVER_ERROR]
                            );
                        }
                    }
                    $result = $this->_delete_meta_value( $object_id, $meta_key, $name );
                    if ( $this->_init_error( $result ) ) return $result;
                }
                if ( ! $args['single'] && is_array( $value ) && count( array_filter( $value, 'is_null' ) ) ) {
                    return new tP_Error(
                        'rest_invalid_stored_value', /* translators: %s: Custom field key. */
                        sprintf( $this->__( 'The %s property has an invalid stored value, and cannot be updated to null.' ), $name ),
                        ['status' => INTERNAL_SERVER_ERROR]
                    );
                }
                $is_valid = $this->_rest_validate_value_from_schema( $value, $args['schema'], 'meta.' . $name );
                if ( ($is_valid instanceof TP_Error) && $this->_init_error( $is_valid ) ) {
                    $is_valid->add_data( ['status' => BAD_REQUEST] );
                    return $is_valid;
                }
                $value = $this->_rest_sanitize_value_from_schema( $value, $args['schema'] );
                if ( $args['single'] ) $result = $this->_update_meta_value( $object_id, $meta_key, $name, $value );
                else $result = $this->_update_multi_meta_value( $object_id, $meta_key, $name, $value );
                if ( $this->_init_error( $result ) ) return $result;
            }
            return null;
        }//141
        protected function _delete_meta_value( $object_id, $meta_key, $name ){
            $meta_type = $this->_get_meta_type();
            if ( ! $this->_current_user_can( "delete_{$meta_type}_meta", $object_id, $meta_key ) )
                return new TP_Error('rest_cannot_delete',/* translators: %s: Custom field key. */
                    sprintf( $this->__( 'Sorry, you are not allowed to edit the %s custom field.' ), $name ),
                    ['key' => $name,'status' => $this->_rest_authorization_required_code(),]
                );
            if ( null === $this->_get_metadata_raw( $meta_type, $object_id, $this->_tp_slash( $meta_key ) ) )
                return true;
            if ( ! $this->_delete_metadata( $meta_type, $object_id, $this->_tp_slash( $meta_key ) ) ) {
                return new TP_Error('rest_meta_database_error',
                    $this->__( 'Could not delete meta value from database.' ),
                    ['key'    => $name,'status' => INTERNAL_SERVER_ERROR,]
                );
            }
            return true;
        }//222
        protected function _update_multi_meta_value( $object_id, $meta_key, $name, $values ){
            $meta_type = $this->_get_meta_type();
            if ( ! $this->_current_user_can( "edit_{$meta_type}_meta", $object_id, $meta_key ) ) {
                return new TP_Error('rest_cannot_update',/* translators: %s: Custom field key. */
                    sprintf( $this->__( 'Sorry, you are not allowed to edit the %s custom field.' ), $name ),
                    ['key' => $name,'status' => $this->_rest_authorization_required_code(),]
                );
            }
            $current_values = $this->_get_metadata( $meta_type, $object_id, $meta_key, false );
            $subtype        = $this->_get_object_subtype( $meta_type, $object_id );
            if ( ! is_array( $current_values ) ) $current_values = [];
            $to_remove = $current_values;
            $to_add    = $values;
            foreach ( $to_add as $add_key => $value ) {
                $remove_keys = array_keys(
                    array_filter($current_values,
                        function ( $stored_value ) use ( $meta_key, $subtype, $value ) {
                            return $this->_is_meta_value_same_as_stored_value( $meta_key, $subtype, $stored_value, $value );
                        }
                    )
                );
                if ( empty( $remove_keys ) ) continue;
                if ( count( $remove_keys ) > 1 ) continue;
                $remove_key = $remove_keys[0];
                unset( $to_remove[ $remove_key ], $to_add[ $add_key ] );
            }
            $to_remove = array_map( 'maybe_unserialize', array_unique( array_map( 'maybe_serialize', $to_remove ) ) );
            foreach ( $to_remove as $value ) {
                if ( ! $this->_delete_metadata( $meta_type, $object_id, $this->_tp_slash( $meta_key ), $this->_tp_slash( $value ) ) ) {
                    return new TP_Error('rest_meta_database_error',/* translators: %s: Custom field key. */
                        sprintf( $this->__( 'Could not update the meta value of %s in database.' ), $meta_key ),
                        ['key' => $name,'status' => INTERNAL_SERVER_ERROR,]
                    );
                }
            }
            foreach ( $to_add as $value ) {
                if ( ! $this->_add_metadata( $meta_type, $object_id, $this->_tp_slash( $meta_key ), $this->_tp_slash( $value ) ) )
                    return new TP_Error('rest_meta_database_error',/* translators: %s: Custom field key. */
                        sprintf( $this->__( 'Could not update the meta value of %s in database.' ), $meta_key ),
                        [ 'key'=> $name, 'status' => INTERNAL_SERVER_ERROR,]
                    );
            }
            return true;
        }//268
        protected function _update_meta_value( $object_id, $meta_key, $name, $value ){
            $meta_type = $this->_get_meta_type();
            if ( ! $this->_current_user_can( "edit_{$meta_type}_meta", $object_id, $meta_key ) )
                return new TP_Error('rest_cannot_update',/* translators: %s: Custom field key. */
                    sprintf( $this->__( 'Sorry, you are not allowed to edit the %s custom field.' ), $name ),
                    ['key' => $name,'status' => $this->_rest_authorization_required_code(),]
                );
            $old_value = $this->_get_metadata( $meta_type, $object_id, $meta_key );
            $subtype   = $this->_get_object_subtype( $meta_type, $object_id );
            if ( is_array( $old_value ) && 1 === count( $old_value )
                && $this->_is_meta_value_same_as_stored_value( $meta_key, $subtype, $old_value[0], $value )
            ) return true;
            if ( ! $this->_update_metadata( $meta_type, $object_id, $this->_tp_slash( $meta_key ), $this->_tp_slash( $value ) ) ) {
                return new TP_Error('rest_meta_database_error',/* translators: %s: Custom field key. */
                    sprintf( $this->__( 'Could not update the meta value of %s in database.' ), $meta_key ),
                    ['key' => $name,'status' => INTERNAL_SERVER_ERROR,]
                );
            }
            return true;
        }//367
        protected function _is_meta_value_same_as_stored_value( $meta_key, $subtype, $stored_value, $user_value ): bool{
            $args      = $this->_get_registered_fields()[ $meta_key ];
            $sanitized = $this->_sanitize_meta( $meta_key, $user_value, $this->_get_meta_type(), $subtype );
            if ( in_array( $args['type'], array( 'string', 'number', 'integer', 'boolean' ), true ) )
                $sanitized = (string) $sanitized;
            return $sanitized === $stored_value;
        }//418
        protected function _get_registered_fields(): array{
            $registered = [];
            $rest_item = [];
            $rest_args = null;
            $meta_type    = $this->_get_meta_type();
            $meta_subtype = $this->_get_meta_subtype();
            $meta_keys = $this->_get_registered_meta_keys( $meta_type );
            if ( ! empty( $meta_subtype ) )
                $meta_keys = array_merge( $meta_keys, $this->_get_registered_meta_keys( $meta_type, $meta_subtype ) );
            foreach ( $meta_keys as $name => $args ) {
                if ( empty( $args['show_in_rest'] ) ) continue;
                $rest_args = array();
                if ( is_array( $args['show_in_rest'] ) ) $rest_args = $args['show_in_rest'];
                $default_args = [
                    'name' => $name, 'single' => $args['single'],
                    'type' => ! empty( $args['type'] ) ? $args['type'] : null,
                    'schema' => [], 'prepare_callback' => [ $this, 'prepare_value'],
                ];
                $default_schema = [
                    'type'        => $default_args['type'],
                    'description' => empty( $args['description'] ) ? '' : $args['description'],
                    'default'     => $args['default'] ?? null,
                ];
                $rest_item[1] = $default_args;
                $rest_item[2] = $default_schema;
                $type = ! empty( $rest_args['type'] ) ? $rest_args['type'] : null;
                $type = ! empty( $rest_args['schema']['type'] ) ? $rest_args['schema']['type'] : $type;
                if ( null === $rest_args['schema']['default'] )
                    $rest_args['schema']['default'] = static::_get_empty_value_for_type( $type );
                $rest_args['schema'] = $this->_rest_default_additional_properties_to_false( $rest_args['schema'] );
                if ( ! in_array( $type, array( 'string', 'boolean', 'integer', 'number', 'array', 'object' ), true ) )
                    continue;
                if ( empty( $rest_args['single'] ) )
                    $rest_args['schema'] = ['type'  => 'array','items' => $rest_args['schema'],];
                $rest_item['name'] = $name;
            }
            $rest_args           = array_merge( $rest_item[1], $rest_args );
            $rest_args['schema'] = array_merge( $rest_item[2], $rest_args['schema'] );
            $registered[$rest_item['name']] = $rest_args;
            return $registered;
        }//437
        public function get_field_schema(): array{
            $fields = $this->_get_registered_fields();
            $schema = ['description' => $this->__( 'Meta fields.' ),
                'type' => 'object','context' => ['view', 'edit'],'properties' => [],
                'arg_options' => ['sanitize_callback' => null,'validate_callback' => [$this, 'check_meta_is_array'], ],];
            foreach ( $fields as $args ) $schema['properties'][ $args['name'] ] = $args['schema'];
            return $schema;
        }//509
        public static function prepare_value( $value, $args ): void {//not used, $request
            if ( $args['single'] ) $schema = $args['schema'];
            else $schema = $args['schema']['items'];
            if ( '' === $value && in_array( $schema['type'], array( 'boolean', 'integer', 'number' ), true ) )
                $value = static::_get_empty_value_for_type( $schema['type'] );
            if ((new static)->_init_error( (new static)->_rest_validate_value_from_schema( $value, $schema ))) return null;
            return (new static)->_rest_sanitize_value_from_schema( $value, $schema );
        }//543
        public function check_meta_is_array( $value) { //not used , $request, $param
            if ( ! is_array($value)) return false;
            return $value;
        }//571
        protected static function _get_empty_value_for_type( $type ){
            switch ( $type ) {
                case 'string':
                    return '';
                case 'boolean':
                    return false;
                case 'integer':
                    return 0;
                case 'number':
                    return 0.0;
                case 'array':
                case 'object':
                    return [];
                default:
                    return null;
            }
        }//607
    }
}else die;