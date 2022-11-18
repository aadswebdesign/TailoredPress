### TP_Core/Traits/RestApi

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _rest_api_01.php: 	
	* _register_rest_route( $namespace, $route, $args = [], $override = false ):bool 
	* _register_rest_field( $object_type, $attribute, $args = [] ):void 
	* _rest_api_init():void 
	* _rest_api_register_rewrites():void 
	* _rest_api_default_filters():void 
	* _create_initial_rest_routes():void 
	* _rest_api_loaded():bool 
	* _rest_get_url_prefix() -> mixed 
	* _get_rest_url( $blog_id = null, $path = '/', $scheme = 'rest' ) -> mixed 
	* _rest_url( $path = '', $scheme = 'rest' ) -> mixed 

- _rest_api_02.php: 	
	* _rest_do_request( $request ) -> mixed 
	* _rest_get_server():TP_REST_Server 
	* _rest_ensure_request( $request ): TP_REST_Request 
	* _rest_ensure_response( $response ):TP_REST_Response 
	* _rest_send_cors_headers( $value ) -> mixed 
	* _rest_handle_options_request( $response,TP_REST_Server $handler,TP_REST_Request $request ):TP_REST_Response 
	* _rest_send_allow_header(TP_REST_Response $response,TP_REST_Server $server,TP_REST_Request $request ):TP_REST_Response 
	* _rest_array_intersect_key_recursive( $array1, $array2 ):array 
	* _rest_filter_response_fields(TP_REST_Response $response, $request ):TP_REST_Response 
	* _rest_is_field_included( $field, $fields ):bool 

- _rest_api_03.php: 	
	* _get_rest_output_rsd() -> bool|string 
	* _rest_output_link_tp_head():void 
	* _rest_output_link_header():void 
	* _rest_cookie_check_errors( $result ) -> mixed 
	* _rest_cookie_collect_status():void 
	* _rest_application_password_collect_status( $user_or_error, $app_password = [] ):void 
	* _rest_get_authenticated_app_password() -> mixed 
	* _rest_application_password_check_errors( $result ) -> mixed 
	* _rest_add_application_passwords_to_index( $response ) -> mixed 
	* _rest_get_avatar_urls( $id_or_email ):array 

- _rest_api_04.php: 	
	* _rest_get_avatar_sizes() -> mixed  
	* _rest_parse_date( $date, $force_utc = false ) -> mixed  
	* _rest_parse_hex_color( $color ):bool 
	* _rest_get_date_with_gmt( $date, $is_utc = false ):array 
	* _rest_authorization_required_code():int 
	* _rest_validate_request_arg( $value, TP_REST_Request $request, $param ):bool 
	* _rest_sanitize_request_arg( $value,TP_REST_Request $request, $param ) -> mixed  
	* _rest_parse_request_arg( $value, $request, $param ) -> mixed  
	* _rest_is_ip_address( $ip ):bool 
	* _rest_sanitize_boolean( $value ):bool 

- _rest_api_05.php: 	
	* _rest_is_boolean( $maybe_bool ):bool 
	* _rest_is_integer( $maybe_integer ):bool 
	* _rest_is_array( $maybe_array ) -> mixed  
	* _rest_sanitize_array( $maybe_array ):array 
	* _rest_is_object( $maybe_object ):bool 
	* _rest_sanitize_object( $maybe_object ):string 
	* _rest_get_best_type_for_value( $value, $types ):string 
	* _rest_handle_multi_type_schema( $value, $args, $param = '' ):string 
	* _rest_validate_array_contains_unique_items( $array ):bool 
	* _rest_stabilize_value( $value ) -> mixed  

- _rest_api_06.php: 	
	* _rest_validate_json_schema_pattern( $pattern, $value ):bool 
	* _rest_find_matching_pattern_property_schema( $property, $args ) 
	* _rest_format_combining_operation_error( $param,$error ):TP_Error 
	* _rest_get_combining_operation_error( $value, $param, $errors ):TP_Error 
	* _rest_find_any_matching_schema( $value, $args, $param ) 
	* _rest_find_one_matching_schema( $value, $args, $param, $stop_after_first_match = false ) 
	* _rest_are_values_equal( $value1, $value2 ):bool 
	* _rest_validate_enum( $value, $args, $param ) -> bool|TP_Error 
	* _rest_get_allowed_schema_keywords():array 
	* _rest_validate_value_from_schema( $value, $args, $param = '' ) -> bool|TP_Error  
- _rest_api_07.php: 	
	* _rest_validate_null_value_from_schema( $value, $param ) -> bool|TP_Error 
	* _rest_validate_boolean_value_from_schema( $value, $param ) -> bool|TP_Error 
	* _rest_validate_object_value_from_schema( $value, $args, $param ) -> bool|TP_Error 
	* _rest_validate_array_value_from_schema( $value, $args, $param ) -> bool|TP_Error 
	* _rest_validate_number_value_from_schema( $value, $args, $param ) -> bool|TP_Error 
	* _rest_validate_string_value_from_schema( $value, $args, $param ) -> bool|TP_Error 
	* _rest_validate_integer_value_from_schema( $value, $args, $param ) -> bool|TP_Error 
	* _rest_sanitize_value_from_schema( $value, $args, $param = '' ) -> bool|string|TP_Error 
	* _rest_preload_api_request( $memo, $path ):array 
	* _rest_parse_embed_param( $embed ):bool 
- _rest_api_08.php: 	
	* _rest_filter_response_by_context( $data, $schema, $context ):array 
	* _rest_default_additional_properties_to_false( $schema ) -> mixed 
	* _rest_get_route_for_post( $post ):string 
	* _rest_get_route_for_post_type_items( $post_type ):string 
	* _rest_get_route_for_term( $term ):string 
	* _rest_get_route_for_taxonomy_items( $taxonomy ):string 
	* _rest_get_queried_resource_route() -> mixed 
	* _rest_get_endpoint_args_for_schema( $schema, $method = ''):array 
	* _rest_convert_error_to_response(TP_Error $error ):TP_REST_Response 
