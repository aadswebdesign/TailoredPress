### TP_Core/Traits/HTTP

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _http_01.php: 	
	* _tp_safe_remote_request( $url, $args = [] ) -> mixed  
	* _tp_safe_remote_get( $url, $args = [] ) -> string|TP_Error 
	* _tp_safe_remote_post( $url, $args = [] ) -> string|TP_Error 
	* _tp_safe_remote_head( $url, $args = [] ) -> string|TP_Error 
	* _tp_remote_request( $url, $args = [] ) -> string|TP_Error 
	* _tp_remote_get( $url, $args = [] ) -> string|object 
	* _tp_remote_post( $url, $args = [] ) -> mixed 
	* _tp_remote_head( $url, $args = [] ):string 
	* _tp_remote_retrieve_headers( $response ):array 

- _http_02.php: 	
	* _tp_remote_retrieve_header( $response, $header ):string 
	* _tp_remote_retrieve_response_code( $response ):string 
	* _tp_remote_retrieve_response_message( $response ):string 
	* _tp_remote_retrieve_body( $response ):string 
	* _tp_remote_retrieve_cookies( $response ):array 
	* _tp_remote_retrieve_cookie( $response, $name ):string 
	* _tp_remote_retrieve_cookie_value( $response, $name ):string 
	* _tp_http_supports( $capabilities = array(), $url = null ):bool 
	* _get_http_origin() -> mixed 
	* _get_allowed_http_origins() -> mixed 
- _http_03.php: 	
	* _is_allowed_http_origin( $origin = null ) -> mixed  
	* _send_origin_headers():bool 
	* _tp_http_validate_url( $url ):bool 
	* _allowed_http_request_hosts( $is_external, $host ):bool 
	* _ms_allowed_http_request_hosts( $is_external, $host ) -> mixed  
	* _tp_parse_url( $url, $component = -1 ) -> mixed  
	* _get_component_from_parsed_url_array( $url_parts, $component = -1 ) -> mixed  
	* _tp_translate_php_url_constant_to_key( $constant ) -> mixed 
