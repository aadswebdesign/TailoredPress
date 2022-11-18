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
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Server;
if(ABSPATH){
    trait _rest_api_07{
        use _init_error;
        /**
         * @description Validates a null value based on a schema.
         * @param $value
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_null_value_from_schema( $value, $param ){
            if ( null !== $value )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'null' ),
                    ['param' => $param]);
            return true;
        }//2207
        /**
         * @description Validates a boolean value based on a schema.
         * @param $value
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_boolean_value_from_schema( $value, $param ){
            if ( ! $this->_rest_is_boolean( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'boolean' ),
                    ['param' => $param]);
            return true;
        }//2229
        /**
         * @description Validates an object value based on a schema.
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_object_value_from_schema( $value, $args, $param ){
            if ( ! $this->_rest_is_object( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'object' ),
                    ['param' => $param]);
            $value = $this->_rest_sanitize_object( $value );
            if ( isset( $args['required'] ) && is_array( $args['required'] ) ) { // schema version 4
                foreach ( $args['required'] as $name ) {
                    if ( ! array_key_exists( $name, $value ) )
                        return new TP_Error('rest_property_required',
                            sprintf( $this->__( '%1$s is a required property of %2$s.' ), $name, $param ));
                }
            } elseif ( isset( $args['properties'] ) ) { // schema version 3
                foreach ( $args['properties'] as $name => $property ) {
                    if ( isset( $property['required'] ) && true === $property['required'] && ! array_key_exists( $name, $value ) )
                        return new TP_Error('rest_property_required',
                            sprintf( $this->__( '%1$s is a required property of %2$s.' ), $name, $param ));
                }
            }
            foreach ( $value as $property => $v ) {
                if ( isset( $args['properties'][ $property ] ) ) {
                    $is_valid = $this->_rest_validate_value_from_schema( $v, $args['properties'][ $property ], $param . '[' . $property . ']' );
                    if ( $this->_init_error( $is_valid ) ) return $is_valid;
                    continue;
                }
                $pattern_property_schema = $this->_rest_find_matching_pattern_property_schema( $property, $args );
                if ( null !== $pattern_property_schema ) {
                    $is_valid = $this->_rest_validate_value_from_schema( $v, $pattern_property_schema, $param . '[' . $property . ']' );
                    if ( $this->_init_error( $is_valid ) ) return $is_valid;
                    continue;
                }
                if ( isset( $args['additionalProperties'] ) ) {
                    if ( false === $args['additionalProperties'] )
                        return new TP_Error('rest_additional_properties_forbidden',
                            sprintf( $this->__( '%1$s is not a valid property of Object.' ), $property ));
                    if ( is_array( $args['additionalProperties'] ) ) {
                        $is_valid = $this->_rest_validate_value_from_schema( $v, $args['additionalProperties'], $param . '[' . $property . ']' );
                        if ( $this->_init_error( $is_valid ) ) return $is_valid;
                    }
                }
            }
            if ( isset( $args['minProperties'] ) && count( $value ) < $args['minProperties'] ) {
                return new TP_Error(
                    'rest_too_few_properties',
                    sprintf(
                        $this->_n(
                            '%1$s must contain at least %2$s property.',
                            '%1$s must contain at least %2$s properties.',
                            $args['minProperties']
                        ),
                        $param, $this->_number_format_i18n( $args['minProperties'] )
                    )
                );
            }
            if ( isset( $args['maxProperties'] ) && count( $value ) > $args['maxProperties'] )
                return new TP_Error('rest_too_many_properties',
                    sprintf($this->_n('%1$s must contain at most %2$s property.',
                            '%1$s must contain at most %2$s properties.',$args['maxProperties']),
                        $param,$this->_number_format_i18n( $args['maxProperties'] )));
            return true;
        }//2252
        /**
         * @description Validates an array value based on a schema.
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_array_value_from_schema( $value, $args, $param ){
            if ( ! $this->_rest_is_array( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'array' ),
                    ['param' => $param]);
            $value = $this->_rest_sanitize_array( $value );
            if ( isset( $args['items'] ) ) {
                foreach ( $value as $index => $v ) {
                    $is_valid = $this->_rest_validate_value_from_schema( $v, $args['items'], $param . '[' . $index . ']' );
                    if ( $this->_init_error( $is_valid ) ) return $is_valid;
                }
            }
            if ( isset( $args['minItems'] ) && count( $value ) < $args['minItems'] )
                return new TP_Error('rest_too_few_items',
                    sprintf($this->_n('%1$s must contain at least %2$s item.',
                            '%1$s must contain at least %2$s items.',$args['minItems']
                        ),$param, $this->_number_format_i18n( $args['minItems'] )));
            if ( isset( $args['maxItems'] ) && count( $value ) > $args['maxItems'] )
                return new TP_Error('rest_too_many_items',
                    sprintf($this->_n('%1$s must contain at most %2$s item.',
                            '%1$s must contain at most %2$s items.',$args['maxItems'] ),
                        $param,$this->_number_format_i18n( $args['maxItems'] )));
            if ( ! empty( $args['uniqueItems'] ) && ! $this->_rest_validate_array_contains_unique_items( $value ) )
                return new TP_Error( 'rest_duplicate_items', sprintf( $this->__( '%s has duplicate items.' ), $param ) );
            return true;
        }//2367
        /**
         * @description Validates a number value based on a schema.
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_number_value_from_schema( $value, $args, $param ){
            if ( ! is_numeric( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, $args['type'] ),
                    ['param' => $param]);
            if ( isset( $args['multipleOf'] ) && fmod( $value, $args['multipleOf'] ) !== 0.0 )
                return new TP_Error('rest_invalid_multiple',
                    sprintf( $this->__( '%1$s must be a multiple of %2$s.' ), $param, $args['multipleOf'] ));
            if ( isset( $args['minimum'] ) && ! isset( $args['maximum'] ) ) {
                if ( ! empty( $args['exclusiveMinimum'] ) && $value <= $args['minimum'] )
                    return new TP_Error('rest_out_of_bounds',
                        sprintf( $this->__( '%1$s must be greater than %2$d' ), $param, $args['minimum'] ));
                if ( empty( $args['exclusiveMinimum'] ) && $value < $args['minimum'] )
                    return new TP_Error('rest_out_of_bounds',
                        sprintf( $this->__( '%1$s must be greater than or equal to %2$d' ), $param, $args['minimum'] ));
            }
            if ( isset( $args['maximum'] ) && ! isset( $args['minimum'] ) ) {
                if ( ! empty( $args['exclusiveMaximum'] ) && $value >= $args['maximum'] )
                    return new TP_Error('rest_out_of_bounds',
                        sprintf( $this->__( '%1$s must be less than %2$d' ), $param, $args['maximum'] ));
                if ( empty( $args['exclusiveMaximum'] ) && $value > $args['maximum'] )
                    return new TP_Error('rest_out_of_bounds',
                        sprintf( $this->__( '%1$s must be less than or equal to %2$d' ), $param, $args['maximum'] ));
            }
            if ( isset( $args['minimum'], $args['maximum'] ) ) {
                if ( ! empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value >= $args['maximum'] || $value <= $args['minimum'] )
                        return new TP_Error('rest_out_of_bounds',
                            sprintf($this->__( '%1$s must be between %2$d (exclusive) and %3$d (exclusive)' ),
                                $param,$args['minimum'],$args['maximum']));
                }
                if ( ! empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value > $args['maximum'] || $value <= $args['minimum'] )
                        return new TP_Error('rest_out_of_bounds',
                            sprintf($this->__( '%1$s must be between %2$d (exclusive) and %3$d (inclusive)' ),
                                $param,$args['minimum'], $args['maximum']));
                }
                if ( ! empty( $args['exclusiveMaximum'] ) && empty( $args['exclusiveMinimum'] ) ) {
                    if ( $value >= $args['maximum'] || $value < $args['minimum'] )
                        return new TP_Error('rest_out_of_bounds',
                            sprintf($this->__( '%1$s must be between %2$d (inclusive) and %3$d (exclusive)' ),
                                $param,$args['minimum'],$args['maximum']));
                }
                if ( empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                    if ( $value > $args['maximum'] || $value < $args['minimum'] )
                        return new TP_Error('rest_out_of_bounds',
                            sprintf($this->__( '%1$s must be between %2$d (inclusive) and %3$d (inclusive)' ),
                                $param,$args['minimum'],$args['maximum']));
                }
            }
            return true;
        }//2438
        /**
         * @description Validates a string value based on a schema.
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_string_value_from_schema( $value, $args, $param ){
            if ( ! is_string( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'string' ),
                    ['param' => $param]);
            if ( isset( $args['minLength'] ) && mb_strlen( $value ) < $args['minLength'] )
                return new TP_Error('rest_too_short',
                    sprintf($this->_n('%1$s must be at least %2$s character long.',
                            '%1$s must be at least %2$s characters long.',$args['minLength']),
                        $param,$this->_number_format_i18n( $args['minLength'] )));
            if ( isset( $args['maxLength'] ) && mb_strlen( $value ) > $args['maxLength'] )
                return new TP_Error('rest_too_long',
                    sprintf($this->_n('%1$s must be at most %2$s character long.',
                            '%1$s must be at most %2$s characters long.', $args['maxLength']),
                        $param,$this->_number_format_i18n( $args['maxLength'] )));
            if ( isset( $args['pattern'] ) && ! $this->_rest_validate_json_schema_pattern( $args['pattern'], $value ) )
                return new TP_Error('rest_invalid_pattern',
                    sprintf( $this->__( '%1$s does not match pattern %2$s.' ), $param, $args['pattern'] )
                );
            return true;
        }//2567
        /**
         * @description Validates an integer value based on a schema.
         * @param $value
         * @param $args
         * @param $param
         * @return bool|TP_Error
         */
        protected function _rest_validate_integer_value_from_schema( $value, $args, $param ){
            $is_valid_number = $this->_rest_validate_number_value_from_schema( $value, $args, $param );
            if ( $this->_init_error( $is_valid_number ) ) return $is_valid_number;
            if ( ! $this->_rest_is_integer( $value ) )
                return new TP_Error('rest_invalid_type',
                    sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, 'integer' ),
                    ['param' => $param]);
            return true;
        }//2630
        /**
         * @description Sanitize a value based on a schema.
         * @param $value
         * @param $args
         * @param string $param
         * @return bool|string|TP_Error
         */
        protected function _rest_sanitize_value_from_schema( $value, $args, $param = '' ){
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
            $allowed_types = array( 'array', 'object', 'string', 'number', 'integer', 'boolean', 'null' );
            if ( ! isset( $args['type'] ) )
                $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The "type" schema keyword for %s is required.' ), $param ), '5.5.0' );
            if ( is_array( $args['type'] ) ) {
                $best_type = $this->_rest_handle_multi_type_schema( $value, $args, $param );
                if ( ! $best_type )
                    return new TP_Error('rest_invalid_type',
                        sprintf( $this->__( '%1$s is not of type %2$s.' ), $param, implode( ',', $args['type'] ) ),
                        [ 'param' => $param ]);
                $args['type'] = $best_type;
            }
            if ( ! in_array( $args['type'], $allowed_types, true ) ) {
                $this->_doing_it_wrong( __FUNCTION__, /* translators: 1: Parameter, 2: The list of allowed types. */
                    $this->_tp_sprintf( $this->__( 'The "type" schema keyword for %1$s can only be one of the built-in types: %2$l.' ), $param, $allowed_types ),
                    '0.0.1'
                );
            }
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
            if ( isset( $args['format'] ) && (!isset( $args['type'])||'string' === $args['type']||!in_array( $args['type'], $allowed_types, true))) {
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
                        if ( ! $this->_rest_is_ip_address( $value ) )  /* translators: %s: IP address. */
                            return new TP_Error( 'rest_invalid_ip', sprintf( $this->__( '%s is not a valid IP address.' ), $param ) );
                        break;
                    case 'uuid':
                        if ( ! $this->_tp_is_uuid( $value ) ) { /* translators: %s: The name of a JSON field expecting a valid UUID. */
                            return new TP_Error( 'rest_invalid_uuid', sprintf( $this->__( '%s is not a valid UUID.' ), $param ) );
                        }
                        break;
                }
            }
            return true;

        }//2661
        /**
         * @description Append result of internal request to REST API for purpose of pre-loading data to be attached to a page.
         * @param $memo
         * @param $path
         * @return array
         */
        protected function _rest_preload_api_request( $memo, $path ):array{
            if ( ! is_array( $memo ) ) $memo = [];
            if ( empty( $path ) ) return $memo;
            $method = 'GET';
            if ( is_array( $path ) && 2 === count( $path ) ) {
                $method = end( $path );
                $path = reset( $path );
                if ( ! in_array( $method, array( 'GET', 'OPTIONS' ), true ) )
                    $method = 'GET';
            }
            $path = $this->_untrailingslashit( $path );
            if ( empty( $path ) ) $path = '/';
            $path_parts = parse_url( $path );
            if ( false === $path_parts ) return $memo;
            $request = new TP_REST_Request( $method, $path_parts['path'] );
            if ( ! empty( $path_parts['query'] ) ) {
                parse_str( $path_parts['query'], $query_params );
                $request->set_query_params( $query_params );
            }
            $response = $this->_rest_do_request( $request );
            if ( 200 === $response->status ) {
                $server = $this->_rest_get_server();
                $embed = null;
                if($server instanceof TP_REST_Server ){
                    $embed  = $request->has_param( '_embed' ) ? $this->_rest_parse_embed_param( $request['_embed'] ) : false;

                }
                $data   = (array) $server->response_to_data( $response, $embed );
                if ( 'OPTIONS' === $method ) {
                    $response = $this->_rest_send_allow_header( $response, $server, $request );
                    $memo[ $method ][ $path ] = ['body' => $data,'headers' => $response->headers,];
                } else $memo[ $path ] = ['body'=> $data,'headers' => $response->headers,];
            }
            return $memo;
        }//2823
        /**
         * @description Parses the "_embed" parameter into the list of resources to embed.
         * @param $embed
         * @return bool
         */
        protected function _rest_parse_embed_param( $embed ):bool{
            if ( ! $embed || 'true' === $embed || '1' === $embed ) return true;
            $rels = $this->_tp_parse_list( $embed );
            if ( ! $rels ) return true;
            return $rels;
        }//2892
    }
}else die;