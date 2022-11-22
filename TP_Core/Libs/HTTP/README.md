### TP_Core/Libs/HTTP

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- TP_Http.php: 	
	* request( $url, $args = array() ) 
	* normalize_cookies( $cookies ): Requests_Cookie_Jar 
	* browser_redirect_compatibility( $location, $headers, $data, &$options, $original ): void 
	* validate_redirects( $location ): void  static
	* get_first_available_transport( $args, $url = null ) 
	* private function __dispatch_request( $url, $args ) 
	* dispatch_request( $url, $args ) 
	* post( $url, $args = [] ) 
	* get( $url, $args = [] ) 
	* head( $url, $args = [] ) 
	* processResponse( $response ): array static 
	* processHeaders( $headers, $url = '' ): array static
	* buildCookieHeader( &$r ): void static
	* chunkTransferDecode( $body ) static 
	* block_request( $uri ): ?bool  
	* make_absolute_url( $maybe_relative_path, $url ): string static 
	* handle_redirects( $url, $args, $response ) static 
	* is_ip_address( $maybe_ip ) static 

- TP_Http_Cookie.php: 	
	* public $domain, $expires, $host_only, $name, $path, $port, $value 
	* public function __construct( $data, $requested_url = '' ) 
	* test( $url ): bool 
	* getHeaderValue(): string 
	* getFullHeader(): string 
	* get_attributes(): array 

- TP_Http_Curl.php: 	
	* private $__bytes_written_total, $__body, $__handle, $__headers, $__max_body_length, $__stream_handle 
	* request( $url, $args = array() ) 
	* __stream_headers( $handle, $headers ): int 
	* stream_headers( $handle = null, $headers ): int 
	* __stream_body( $handle, $data ) 
	* stream_body( $handle, $data ) 
	* test( $args = array() ) static 

- TP_Http_Encoding.php: 	
	* compress( $raw, $level = 9, $supports = null ): string static 
	* decompress( $compressed, $length = null ) static
	* compatible_gzinflate( $gz_data ) static
	* accept_encoding( $url, $args ): string static 
	* content_encoding(): string static 
	* should_decode( $headers ): bool static
	* is_available(): bool static

- TP_HTTP_IXR_Client.php: 	
	* protected $_error, $_scheme
	* __construct( $server, $path = false, $port = false, $timeout = 15 ) 
	* query( ...$args ):string 

- TP_HTTP_Proxy.php: 	
	* is_enabled(): bool 
	* use_authentication(): bool 
	* host() 
	* port() 
	* username() 
	* password() 
	* authentication(): string 
	* authentication_header(): string 
	* send_through_proxy( $uri ): ?bool 

- TP_HTTP_Requests_Hooks.php: 	
	* protected $_request, $_url 
	* __construct( $url, $request ) 
	* dispatch( $hook, $parameters) 

- TP_HTTP_Requests_Response.php: 	
	* protected $_filename, $_response
	* __construct( Requests_Response $response, $filename = '' ) 
	* get_response_object(): Requests_Response 
	* get_headers(): Requests_Utility_CaseInsensitiveDictionary 
	* set_headers( $headers ): void 
	* header( $key, $value, $replace = true ): void 
	* get_status(): bool 
	* set_status( $code ): void 
	* get_data(): string 
	* set_data( $data ): void 
	* get_cookies():array 
	* to_array():array 

- TP_HTTP_Response.php: 	
	* public $data, $headers, $status 
	* __construct( $data = null, $status = 200, $headers = array() ) 
	* get_headers() 
	* set_headers( $headers ):void 
	* header( $key, $value, $replace = true ):void 
	* get_status() 
	* set_status( $code ) :void 
	* get_data() 
	* set_data( $data ):void 
	* jsonSerialize() 

- TP_Http_Streams.php: 	
	* private $__processed_response, $__error_reporting 
	* request( $url, $args = [] ) 
	* verify_ssl_certificate( $stream, $host ):bool static
	* test( $args = array() ) static 