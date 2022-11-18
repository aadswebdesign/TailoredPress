<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
if(ABSPATH){
    trait _rest_api_05{
        /**
         * @description Determines if a given value is boolean-like.
         * @param $maybe_bool
         * @return bool
         */
        protected function _rest_is_boolean( $maybe_bool ):bool{
            if ( is_bool( $maybe_bool ) ) return true;
            if ( is_string( $maybe_bool ) ) {
                $maybe_bool = strtolower( $maybe_bool );
                $valid_boolean_values = ['false','true','0','1',];
                return in_array( $maybe_bool, $valid_boolean_values, true );
            }
            if ( is_int( $maybe_bool ) ) return in_array( $maybe_bool, array( 0, 1 ), true );
            return false;
        }//1429
        /**
         * @description Determines if a given value is integer-like.
         * @param $maybe_integer
         * @return bool
         */
        protected function _rest_is_integer( $maybe_integer ):bool{
            return is_numeric( $maybe_integer ) && round( (float) $maybe_integer ) === (float) $maybe_integer;
        }//1462
        /**
         * @description Determines if a given value is array-like.
         * @param $maybe_array
         * @return mixed
         */
        protected function _rest_is_array( $maybe_array ){
            if ( is_scalar( $maybe_array ) )
                $maybe_array = $this->_tp_parse_list( $maybe_array );
            return $this->_tp_is_numeric_array( $maybe_array );
        }//1474
        /**
         * @description Converts an array-like value to an array.
         * @param $maybe_array
         * @return array
         */
        protected function _rest_sanitize_array( $maybe_array ):array{
            if ( is_scalar( $maybe_array ) ) return $this->_tp_parse_list( $maybe_array );
            if ( ! is_array( $maybe_array ) ) return [];
            return array_values( $maybe_array );
        }//1490
        /**
         * @description Determines if a given value is object-like.
         * @param $maybe_object
         * @return bool
         */
        protected function _rest_is_object( $maybe_object ):bool{
            if ( '' === $maybe_object ) return true;
            if ( $maybe_object instanceof \stdClass ) return true;
            if ( $maybe_object instanceof \JsonSerializable )
                $maybe_object = $maybe_object->jsonSerialize();
            return is_array( $maybe_object );
        }//1511
        /**
         * @description Converts an object-like value to an object.
         * @param $maybe_object
         * @return string
         */
        protected function _rest_sanitize_object( $maybe_object ):string{
            if ( '' === $maybe_object ) return [];
            if ( $maybe_object instanceof \stdClass ) return (array) $maybe_object;
            if ( $maybe_object instanceof \JsonSerializable )
                $maybe_object = $maybe_object->jsonSerialize();
            if ( ! is_array( $maybe_object ) ) return [];
            return $maybe_object;
        }//1535
        /**
         * @description Gets the best type for a value.
         * @param $value
         * @param $types
         * @return string
         */
        protected function _rest_get_best_type_for_value( $value, $types ):string{
            static $checks = [
                'array' => 'rest_is_array', 'object' => 'rest_is_object', 'integer' => 'rest_is_integer',
                'number' => 'is_numeric', 'boolean' => 'rest_is_boolean', 'string' => 'is_string', 'null' => 'is_null',
            ];
            if ( '' === $value && in_array( 'string', $types, true ) ) return 'string';
            foreach ( $types as $type ) {
                if ( isset( $checks[ $type ] ) && $checks[ $type ]( $value ) ) return $type;
            }
            return '';
        }//1564
        /**
         * @description Handles getting the best type for a multi-type schema.
         * @param $value
         * @param $args
         * @param string $param
         * @return string
         */
        protected function _rest_handle_multi_type_schema( $value, $args, $param = '' ):string{
            $allowed_types = array( 'array', 'object', 'string', 'number', 'integer', 'boolean', 'null' );
            $invalid_types = array_diff( $args['type'], $allowed_types );
            if ( $invalid_types )
                $this->_doing_it_wrong( __FUNCTION__,
                    $this->_tp_sprintf( $this->__( 'The "type" schema keyword for %1$s can only contain the built-in types: %2$l.' ), $param, $allowed_types ),
                    '0.0.1');
            $best_type = $this->_rest_get_best_type_for_value( $value, $args['type'] );
            if (!$best_type && !$invalid_types) return '';
            return $best_type;
        }//1603
        /**
         * @description Checks if an array is made up of unique items.
         * @param $array
         * @return bool
         */
        protected function _rest_validate_array_contains_unique_items( $array ):bool{
            $seen = [];
            foreach ( $array as $item ) {
                $stabilized = $this->_rest_stabilize_value( $item );
                $key        = serialize( $stabilized );
                if ( ! isset( $seen[ $key ] ) ) {
                    $seen[ $key ] = true;
                    continue;
                }
                return false;
            }
            return true;
        }//1638
        /**
         * @description Stabilizes a value following JSON Schema semantics.
         * @param $value
         * @return mixed
         */
        protected function _rest_stabilize_value( $value ){
            if ( is_scalar( $value ) || is_null( $value ) ) return $value;
            if ( is_object( $value ) ) {
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Cannot stabilize objects. Convert the object to an array first.' ), '5.5.0' );
                return $value;
            }
            ksort( $value );
            foreach ( $value as $k => $v ) $value[ $k ] = $this->_rest_stabilize_value( $v );
            return $value;
        }//1667
    }
}else die;