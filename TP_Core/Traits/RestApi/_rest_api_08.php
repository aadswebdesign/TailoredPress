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
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\TP_Term;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    trait _rest_api_08{
        use _init_error;
        /**
         * @description  Filters the response to remove any fields not available in the given context.
         * @param $data
         * @param $schema
         * @param $context
         * @return array
         */
        protected function _rest_filter_response_by_context( $data, $schema, $context ):array{
            if ( isset( $schema['anyOf'] ) ) {
                $matching_schema = $this->_rest_find_any_matching_schema( $data, $schema, '' );
                if ( ! $this->_init_error( $matching_schema ) ) {
                    if ( ! isset( $schema['type'] ) ) $schema['type'] = $matching_schema['type'];
                    $data = $this->_rest_filter_response_by_context( $data, $matching_schema, $context );
                }
            }
            if ( isset( $schema['oneOf'] ) ) {
                $matching_schema = $this->_rest_find_one_matching_schema( $data, $schema, '', true );
                if ( ! $this->_init_error( $matching_schema ) ) {
                    if ( ! isset( $schema['type'] ) ) $schema['type'] = $matching_schema['type'];
                    $data = $this->_rest_filter_response_by_context( $data, $matching_schema, $context );
                }
            }
            if ( ! is_array( $data ) && ! is_object( $data ) ) return $data;
            if ( isset( $schema['type'] ) ) $type = $schema['type'];
            else return $data;
            $is_array_type  = 'array' === $type || ( is_array( $type ) && in_array( 'array', $type, true ) );
            $is_object_type = 'object' === $type || ( is_array( $type ) && in_array( 'object', $type, true ) );
            if ( $is_array_type && $is_object_type ) {
                if ( $this->_rest_is_array( $data ) ) $is_object_type = false;
                else $is_array_type = false;
            }
            $has_additional_properties = $is_object_type && isset( $schema['additionalProperties'] ) && is_array( $schema['additionalProperties'] );
            foreach ( $data as $key => $value ) {
                $check = [];
                if ( $is_array_type ) $check = $schema['items'] ?? array();
                elseif ( $is_object_type ) {
                    if ( isset( $schema['properties'][ $key ] ) )  $check = $schema['properties'][ $key ];
                    else {
                        $pattern_property_schema = $this->_rest_find_matching_pattern_property_schema( $key, $schema );
                        if ( null !== $pattern_property_schema ) $check = $pattern_property_schema;
                        elseif ( $has_additional_properties ) $check = $schema['additionalProperties'];
                    }
                }
                if ( ! isset( $check['context'] ) ) continue;
                if ( ! in_array( $context, $check['context'], true ) ) {
                    if ( $is_array_type ) {
                        $data = [];
                        break;
                    }
                    if ( is_object( $data ) ) unset( $data->$key );
                    else unset( $data[ $key ] );
                } elseif ( is_array( $value ) || is_object( $value ) ) {
                    $new_value = $this->_rest_filter_response_by_context( $value, $check, $context );
                    if ( is_object( $data ) ) $data->$key = $new_value;
                    else $data[ $key ] = $new_value;
                }
            }
            return $data;
        }//2918
        /**
         * @description Sets the "additionalProperties" to false by default for all object definitions in the schema.
         * @param $schema
         * @return mixed
         */
        protected function _rest_default_additional_properties_to_false( $schema ){
            $type = (array) $schema['type'];
            if ( in_array( 'object', $type, true ) ) {
                if ( isset( $schema['properties'] ) ) {
                    foreach ( $schema['properties'] as $key => $child_schema )
                        $schema['properties'][ $key ] = $this->_rest_default_additional_properties_to_false( $child_schema );
                }
                if ( isset( $schema['patternProperties'] ) ) {
                    foreach ( $schema['patternProperties'] as $key => $child_schema )
                        $schema['patternProperties'][ $key ] = $this->_rest_default_additional_properties_to_false( $child_schema );
                }
                if ( ! isset( $schema['additionalProperties'] ) ) $schema['additionalProperties'] = false;
            }
            if (isset($schema['items']) && in_array('array', $type, true)) $schema['items'] = $this->_rest_default_additional_properties_to_false( $schema['items'] );
            return $schema;
        }//3023
        /**
         * @description Gets the REST API route for a post.
         * @param $post
         * @return string
         */
        protected function _rest_get_route_for_post( $post ):string{
            $post = $this->_get_post( $post );
            if ( ! $post instanceof TP_Post ) return '';
            $post_type_route = $this->_rest_get_route_for_post_type_items( $post->post_type );
            if ( ! $post_type_route ) return '';
            $route = sprintf( '%s/%d', $post_type_route, $post->ID );
            return $this->_apply_filters( 'rest_route_for_post', $route, $post );
        }//3062
        /**
         * @description Gets the REST API route for a post type.
         * @param $post_type
         * @return string
         */
        protected function _rest_get_route_for_post_type_items( $post_type ):string{
            $post_type = $this->_get_post_type_object( $post_type );
            if ( ! $post_type ) return '';
            if ( ! $post_type->show_in_rest ) return '';
            $namespace = ! empty( $post_type->rest_namespace ) ? $post_type->rest_namespace : 'wp/v2';
            $rest_base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;
            $route     = sprintf( '/%s/%s', $namespace, $rest_base );
            return $this->_apply_filters( 'rest_route_for_post_type_items', $route, $post_type );
        }//3096
        /**
         * @description Gets the REST API route for a term.
         * @param $term
         * @return string
         */
        protected function _rest_get_route_for_term( $term ):string{
            $term = $this->_get_term( $term );
            if ( ! $term instanceof TP_Term ) return '';
            $taxonomy_route = $this->_rest_get_route_for_taxonomy_items( $term->taxonomy );
            if ( ! $taxonomy_route ) return '';
            $route = sprintf( '%s/%d', $taxonomy_route, $term->term_id );
            return $this->_apply_filters( 'rest_route_for_term', $route, $term );
        }//3130
        /**
         * @description Gets the REST API route for a taxonomy.
         * @param $taxonomy
         * @return string
         */
        protected function _rest_get_route_for_taxonomy_items( $taxonomy ):string{
            $taxonomy = $this->_get_taxonomy( $taxonomy );
            if ( ! $taxonomy ) return '';
            if ( ! $taxonomy->show_in_rest ) return '';
            $namespace = ! empty( $taxonomy->rest_namespace ) ? $taxonomy->rest_namespace : 'tp/v1';
            $rest_base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
            $route     = sprintf( '/%s/%s', $namespace, $rest_base );
            return $this->_apply_filters( 'rest_route_for_taxonomy_items', $route, $taxonomy );
        }//3163
        /**
         * @description Gets the REST route for the currently queried object.
         * @return mixed
         */
        protected function _rest_get_queried_resource_route(){
            if ( $this->_is_singular() )
                $route = $this->_rest_get_route_for_post( $this->_get_queried_object() );
            elseif ( $this->_is_category() || $this->_is_tag() || $this->_is_tax() )
                $route = $this->_rest_get_route_for_term( $this->_get_queried_object() );
            elseif ( $this->_is_author() ) $route = '/tp/v1/users/' . $this->_get_queried_object_id();
            else $route = '';
            return $this->_apply_filters( 'rest_queried_resource_route', $route );
        }//3195
        /**
         * @description Retrieves an array of endpoint arguments from the item schema and endpoint method.
         * @param $schema
         * @param string $method
         * @return array
         */
        protected function _rest_get_endpoint_args_for_schema( $schema, $method = ''):array{
            $schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : [];
            $endpoint_args = [];
            $valid_schema_properties = $this->_rest_get_allowed_schema_keywords();
            $valid_schema_properties = array_diff( $valid_schema_properties, array( 'default', 'required' ) );
            foreach ( $schema_properties as $field_id => $params ) {
                if ( ! empty( $params['readonly'] ) ) continue;
                $endpoint_args[ $field_id ] = ['validate_callback' => 'rest_validate_request_arg', 'sanitize_callback' => 'rest_sanitize_request_arg',];
                if (TP_POST === $method && isset( $params['default'] ) )
                    $endpoint_args[ $field_id ]['default'] = $params['default'];
                if ( TP_POST === $method && ! empty( $params['required'] ) )
                    $endpoint_args[ $field_id ]['required'] = true;
                foreach ( $valid_schema_properties as $schema_prop ) {
                    if ( isset( $params[ $schema_prop ] ) )
                        $endpoint_args[ $field_id ][ $schema_prop ] = $params[ $schema_prop ];
                }
                if ( isset( $params['arg_options'] ) ) {
                    if ( TP_POST !== $method )
                        $params['arg_options'] = array_diff_key($params['arg_options'],['required' => '','default' => '',] );
                    $endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
                }
            }
            return $endpoint_args;
        }//3217
        /**
         * @description Converts an error to a response object.
         * @param TP_Error $error
         * @return TP_REST_Response
         */
        protected function _rest_convert_error_to_response(TP_Error $error ):TP_REST_Response{
            $status = array_reduce( $error->get_all_error_data(),
                static function ( $status, $error_data ) {
                    return is_array( $error_data ) && isset( $error_data['status'] ) ? $error_data['status'] : $status;
                },500);
            $errors = [];
            foreach ( (array) $error->errors as $code => $messages ) {
                $all_data  = $error->get_all_error_data( $code );
                $last_data = array_pop( $all_data );
                foreach ( (array) $messages as $message ) {
                    $formatted = ['code' => $code, 'message' => $message,'data' => $last_data,];
                    if ( $all_data ) $formatted['additional_data'] = $all_data;
                    $errors[] = $formatted;
                }
            }
            $data = $errors[0];
            if ( count( $errors ) > 1 ) {
                array_shift( $errors );
                $data['additional_errors'] = $errors;
            }
            return new TP_REST_Response( $data, $status );
        }//3295
    }
}else die;