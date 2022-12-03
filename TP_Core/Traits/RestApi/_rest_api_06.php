<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _rest_api_06{
        use _init_error;
        /**
         * @description Validates if the JSON Schema pattern matches a value.
         * @param $pattern
         * @param $value
         * @return bool
         */
        protected function _rest_validate_json_schema_pattern( $pattern, $value ):bool{
            $escaped_pattern = str_replace( '#', '\\#', $pattern );
            return 1 === preg_match( '#' . $escaped_pattern . '#u', $value );
        }//1696
        /**
         * @description Finds the schema for a property using the patternProperties keyword.
         * @param $property
         * @param $args
         * @return null
         */
        protected function _rest_find_matching_pattern_property_schema( $property, $args ){
            if ( isset( $args['patternProperties'] ) ) {
                foreach ( $args['patternProperties'] as $pattern => $child_schema ) {
                    if ( $this->_rest_validate_json_schema_pattern( $pattern, $property ) ) return $child_schema;
                }
            }
            return null;
        }//1711
        /**
         * @description Formats a combining operation error into a TP_Error object.
         * @param $param
         * @param $error
         * @return TP_Error
         */
        protected function _rest_format_combining_operation_error( $param,$error ):TP_Error{
            $position = $error['index'];
            $err_obj = $error['error_object'];
            $reason = null;
            if($err_obj  instanceof  TP_Error){
                $reason   = $err_obj->get_error_message();
            }
            if ( isset( $error['schema']['title'] ) ) {
                $title = $error['schema']['title'];
                return new TP_Error('rest_no_matching_schema',
                    sprintf( $this->__( '%1$s is not a valid %2$s. Reason: %3$s' ), $param, $title, $reason ),
                    ['position' => $position]);
            }
            return new TP_Error('rest_no_matching_schema',
                sprintf( $this->__( '%1$s does not match the expected format. Reason: %2$s' ), $param, $reason ),
                ['position' => $position]
            );
        }//1732
        /**
         * @description Gets the error of combining operation.
         * @param $value
         * @param $param
         * @param $errors
         * @return TP_Error
         */
        protected function _rest_get_combining_operation_error( $value, $param, $errors ):TP_Error{
            if ( 1 === count( $errors ) )
                return $this->_rest_format_combining_operation_error( $param, $errors[0] );
            $filtered_errors = [];
            foreach ((array) $errors as $error ) {
                $err_obj = $error['error_object'];
                $error_code = null;
                if($err_obj  instanceof  TP_Error){
                    $error_code = $err_obj->get_error_code();
                    $error_data = $err_obj->get_error_data();
                }
                if ( 'rest_invalid_type' !== $error_code || ( isset( $error_data['param'] ) && $param !== $error_data['param'] ) )
                    $filtered_errors[] = $error;
            }
            if ( 1 === count( $filtered_errors ) )
                return $this->_rest_format_combining_operation_error( $param, $filtered_errors[0] );
            if ( count( $filtered_errors ) > 1 && 'object' === $filtered_errors[0]['schema']['type'] ) {
                $result = null;
                $number = 0;
                foreach ( $filtered_errors as $error ) {
                    if ( isset( $error['schema']['properties'] ) ) {
                        $n = count( array_intersect_key( $error['schema']['properties'], $value ) );
                        if ( $n > $number ) {
                            $result = $error;
                            $number = $n;
                        }
                    }
                }
                if ( null !== $result )
                    return $this->_rest_format_combining_operation_error( $param, $result );
            }
            $schema_titles = array();
            foreach ( $errors as $error ) {
                if ( isset( $error['schema']['title'] ) ) $schema_titles[] = $error['schema']['title'];
            }
            if ( count( $schema_titles ) === count( $errors ) )
                return new TP_Error( 'rest_no_matching_schema', $this->_tp_sprintf( $this->__( '%1$s is not a valid %2$l.' ), $param, $schema_titles ) );
            return new TP_Error( 'rest_no_matching_schema', sprintf( $this->__( '%s does not match any of the expected formats.' ), $param ) );
        }//1765
        /**
         * @description Finds the matching schema among the "anyOf" schemas.
         * @param $value
         * @param $args
         * @param $param
         * @return mixed
         */
        protected function _rest_find_any_matching_schema( $value, $args, $param ){
            $errors = [];
            foreach ( $args['anyOf'] as $index => $schema ) {
                if ( ! isset( $schema['type'] ) && isset( $args['type'] ) ) $schema['type'] = $args['type'];
                $is_valid = $this->_rest_validate_value_from_schema( $value, $schema, $param );
                if ( ! $this->_init_error( $is_valid ) ) { return $schema;}
                $errors[] = ['error_object' => $is_valid,'schema' => $schema,'index' => $index,];
            }
            return $this->_rest_get_combining_operation_error( $value, $param, $errors );
        }//1834
        /**
         * @description Finds the matching schema among the "oneOf" schemas.
         * @param $value
         * @param $args
         * @param $param
         * @param bool $stop_after_first_match
         * @return mixed|TP_Error
         */
        protected function _rest_find_one_matching_schema( $value, $args, $param, $stop_after_first_match = false ){
            $matching_schemas = [];
            $errors           = [];
            foreach ( $args['oneOf'] as $index => $schema ) {
                if ( ! isset( $schema['type'] ) && isset( $args['type'] ) )
                    $schema['type'] = $args['type'];
                $is_valid = $this->_rest_validate_value_from_schema( $value, $schema, $param );
                if ( ! $this->_init_error( $is_valid ) ) {
                    if ( $stop_after_first_match ) return $schema;
                    $matching_schemas[] = ['schema_object' => $schema, 'index' => $index,];
                } else $errors[] = ['error_object' => $is_valid,'schema' => $schema,'index' => $index,];
            }
            if ( ! $matching_schemas ) return $this->_rest_get_combining_operation_error( $value, $param, $errors );
            if ( count( $matching_schemas ) > 1 ) {
                $schema_positions = [];
                $schema_titles    = [];
                foreach ( $matching_schemas as $schema ) {
                    $schema_positions[] = $schema['index'];
                    if ( isset( $schema['schema_object']['title'] ) )
                        $schema_titles[] = $schema['schema_object']['title'];
                }
                if ( count( $schema_titles ) === count( $matching_schemas ) )
                    return new TP_Error('rest_one_of_multiple_matches',
                        $this->_tp_sprintf( $this->__( '%1$s matches %2$l, but should match only one.' ), $param, $schema_titles ),
                        ['positions' => $schema_positions]
                    );
                return new TP_Error('rest_one_of_multiple_matches',
                    sprintf( $this->__( '%s matches more than one of the expected formats.' ), $param ),
                    ['positions' => $schema_positions]);
            }
            return $matching_schemas[0]['schema_object'];
        }//1868
        /**
         * @description Checks the equality of two values, following JSON Schema semantics.
         * @param $value1
         * @param $value2
         * @return bool
         */
        protected function _rest_are_values_equal( $value1, $value2 ):bool{
            if ( is_array( $value1 ) && is_array( $value2 ) ) {
                if ( count( $value1 ) !== count( $value2 ) ) return false;
                foreach ( $value1 as $index => $value ) {
                    if ( ! array_key_exists( $index, $value2 ) || ! $this->_rest_are_values_equal( $value, $value2[ $index ] ) )
                        return false;
                }
                return true;
            }
            if ( (is_int($value1) && is_float($value2)) || (is_float($value1) && is_int($value2)))
                return (float) $value1 === (float) $value2;
            return $value1 === $value2;
        }//1946
        /**
         * @description Validates that the given value is a member of the JSON Schema "enum".
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_enum( $value, $args, $param ){
            $sanitized_value = $this->_rest_sanitize_value_from_schema( $value, $args, $param );
            if ( $this->_init_error( $sanitized_value ) )  return $sanitized_value;
            foreach ( $args['enum'] as $enum_value ) {
                if ( $this->_rest_are_values_equal( $sanitized_value, $enum_value ) ) return true;
            }
            $encoded_enum_values = [];
            foreach ( $args['enum'] as $enum_value )
                $encoded_enum_values[] = is_scalar( $enum_value ) ? $enum_value : $this->_tp_json_encode( $enum_value );
            if ( count( $encoded_enum_values ) === 1 )
                return new TP_Error( 'rest_not_in_enum', $this->_tp_sprintf( $this->__( '%1$s is not %2$s.' ), $param, $encoded_enum_values[0] ) );
            return new TP_Error( 'rest_not_in_enum', $this->_tp_sprintf( $this->__( '%1$s is not one of %2$l.' ), $param, $encoded_enum_values ) );
        }//1980
        /**
         * @description Get all valid JSON schema properties.
         * @return array
         */
        protected function _rest_get_allowed_schema_keywords():array{
            return ['title','description','default','type','format',
                'enum','items','properties','additionalProperties',
                'patternProperties','minProperties','maxProperties',
                'minimum','maximum','exclusiveMinimum','exclusiveMaximum',
                'multipleOf','minLength','maxLength','pattern','minItems',
                'maxItems','uniqueItems','anyOf','oneOf',];
        }//2013
        /**
         * @description Validate a value based on a schema.
         * @param $value
         * @param $args
         * @param string $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_value_from_schema( $value, $args, $param = '' ){
            if ( isset( $args['anyOf'] ) ) {
                $matching_schema = $this->_rest_find_any_matching_schema( $value, $args, $param );
                if ( $this->_init_error( $matching_schema ) ) return $matching_schema;
                if ( ! isset( $args['type'] ) && isset( $matching_schema['type'] ) )
                    $args['type'] = $matching_schema['type'];
            }
            if ( isset( $args['oneOf'] ) ) {
                $matching_schema = $this->_rest_find_one_matching_schema( $value, $args, $param );
                if ( $this->_init_error( $matching_schema ) ) return $matching_schema;
                if ( ! isset( $args['type'] ) && isset( $matching_schema['type'] ) )
                    $args['type'] = $matching_schema['type'];
            }
            $allowed_types = ['array', 'object', 'string', 'number', 'integer', 'boolean', 'null' ];
            if ( ! isset( $args['type'] ) )
                $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The "type" schema keyword for %s is required.' ), $param ), '5.5.0' );
            if ( is_array( $args['type'] ) ) {
                $best_type = $this->_rest_handle_multi_type_schema( $value, $args, $param );
                if ( ! $best_type )
                    return new TP_Error('rest_invalid_type',
                        sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, implode( ',', $args['type'] ) ),
                        ['param' => $param]);
                $args['type'] = $best_type;
            }
            if ( ! in_array( $args['type'], $allowed_types, true ) )
                $this->_doing_it_wrong(__FUNCTION__,
                    $this->_tp_sprintf( $this->__( 'The "type" schema keyword for %1$s can only be one of the built-in types: %2$l.' ), $param, $allowed_types ),
                    '0.0.1'
                );
            switch ( $args['type'] ) {
                case 'null':
                    $is_valid = $this->_rest_validate_null_value_from_schema( $value, $param );
                    break;
                case 'boolean':
                    $is_valid = $this->_rest_validate_boolean_value_from_schema( $value, $param );
                    break;
                case 'object':
                    $is_valid = $this->_rest_validate_object_value_from_schema( $value, $args, $param );
                    break;
                case 'array':
                    $is_valid = $this->_rest_validate_array_value_from_schema( $value, $args, $param );
                    break;
                case 'number':
                    $is_valid = $this->_rest_validate_number_value_from_schema( $value, $args, $param );
                    break;
                case 'string':
                    $is_valid = $this->_rest_validate_string_value_from_schema( $value, $args, $param );
                    break;
                case 'integer':
                    $is_valid = $this->_rest_validate_integer_value_from_schema( $value, $args, $param );
                    break;
                default:
                    $is_valid = true;
                    break;
            }
            if ( $this->_init_error( $is_valid ) ) return $is_valid;
            if ( ! empty( $args['enum'] ) ) {
                $enum_contains_value = $this->_rest_validate_enum( $value, $args, $param );
                if ( $this->_init_error( $enum_contains_value ) ) return $enum_contains_value;
            }
            if ( isset( $args['format'] )
                && ( ! isset( $args['type'] ) || 'string' === $args['type'] || ! in_array( $args['type'], $allowed_types, true ) )
            ) {
                switch ( $args['format'] ) {
                    case 'hex-color':
                        if ( ! $this->_rest_parse_hex_color( $value ) )
                            return new TP_Error( 'rest_invalid_hex_color', $this->__( 'Invalid hex color.' ) );
                        break;
                    case 'date-time':
                        if ( ! $this->_rest_parse_date( $value ) )
                            return new TP_Error( 'rest_invalid_date', $this->__( 'Invalid date.' ) );
                        break;
                    case 'email':
                        if ( ! $this->_is_email( $value ) )
                            return new TP_Error( 'rest_invalid_email', $this->__( 'Invalid email address.' ) );
                        break;
                    case 'ip':
                        if ( ! $this->_rest_is_ip_address( $value ) )
                            return new TP_Error( 'rest_invalid_ip', sprintf( $this->__( '%s is not a valid IP address.' ), $param ) );
                        break;
                    case 'uuid':
                        if ( ! $this->_tp_is_uuid( $value ) )
                            return new TP_Error( 'rest_invalid_uuid', sprintf( $this->__( '%s is not a valid UUID.' ), $param ) );
                        break;
                }
            }
            return true;
        }//2065
    }
}else die;