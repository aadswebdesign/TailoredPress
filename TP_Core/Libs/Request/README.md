### TP_Core/Libs/Request

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- Requests_Auth.php: interface	
	* register(Requests_Hooks $hooks); 

- Requests_Cookie.php: 	
	* public $attributes, $name, $flags, $reference_time, $value 
	* __construct($name, $value, $attributes = array(), $flags = array(), $reference_time = null) 
	* is_expired(): bool 
	* uri_matches(Requests_IRI $uri): bool 
	* domain_matches($string): bool 
	* path_matches($request_path): bool 
	* normalize(): bool 
	* _normalize_attribute($name, $value) 
	* format_for_header(): string 
	* formatForHeader(): string 
	* format_for_set_cookie(): string 
	* formatForSetCookie(): string 
	* __toString() 
	* parse($string, $name = '', $reference_time = null): Requests_Cookie static
	* parse_from_headers(Requests_Response_Headers $headers, Requests_IRI $origin = null, $time = null): array static 
	* parseFromHeaders(Requests_Response_Headers $headers): array static 

- Requests_Hooker.php: 	
	* public $attributes, $name, $flags, $reference_time, $value 
	* __construct($name, $value, $attributes = array(), $flags = array(), $reference_time = null) 
	* is_expired(): bool 
	* uri_matches(Requests_IRI $uri): bool 
	* domain_matches($string): bool 
	* path_matches($request_path): bool 
	* normalize(): bool 
	* _normalize_attribute($name, $value) 
	* format_for_header(): string 
	* formatForHeader(): string 
	* format_for_set_cookie(): string 
	* formatForSetCookie(): string 
	* __toString() 
	* parse($string, $name = '', $reference_time = null): Requests_Cookie static 
	* parse_from_headers(Requests_Response_Headers $headers, Requests_IRI $origin = null, $time = null): array static
	* parseFromHeaders(Requests_Response_Headers $headers): array static 

- Requests_Hooks.php: 	
	* register($hook, $callback, $priority = 0) 
	* dispatch($hook, $parameters) 

- Requests_IDNAEncoder.php: 	
	* public const ACE_PREFIX, BOOTSTRAP_BASE, BOOTSTRAP_TMIN, BOOTSTRAP_TMAX ,BOOTSTRAP_SKEW
	* public const BOOTSTRAP_DAMP, BOOTSTRAP_INITIAL_BIAS, BOOTSTRAP_INITIAL_N  
	* encode($string): string static 
	* to_ascii($string) static 
	* _is_ascii($string): bool static 
	* _name_prep($string) static 
	* puny_code_encode($input): string static 
	* _digit_to_char($digit) static 
	* _adapt($delta, $num_points, $first_time): int static 

- Requests_IPv6.php:
	* un_compress($ip) static 
	* compress($ip) static 
	* _split_v6_v4($ip): ?array static 
	* check_ipv6($ip): ?bool static 

- Requests_IRI.php: 	
	* private $__character, $__length, $__start, $__valid 
	* protected $_i_fragment, $_i_host, $_i_path, $_i_query		
	* protected $_i_user_info, $_normalization, $_port, $_scheme 
	* public $i_host
	* __toString() 
	* __set($name, $value) 
	* __get($name) 
	* __isset($name) 
	* __unset($name) 
	* __construct($iri = null) 
	* absolutize($base, $relative) static 
	* _parse_iri($iri
	* _remove_dot_segments($input): string
	* _replace_invalid_with_pct_encoding($string, $extra_chars, $i_private = false) 
	* _remove_i_unreserved_percent_encoded($match): string 
	* _scheme_normalization(): void 
	* is_valid(): bool 
	* _set_iri($iri): bool 
	* _set_scheme($scheme): bool 
	* _set_authority($authority): bool 
	* _set_user_info($i_user_info): bool 
	* _set_host($i_host): bool 
	* _set_port($port): bool 
	* _set_path($i_path): bool 
	* _set_query($i_query): bool 
	* _set_fragment($i_fragment): bool 
	* _to_uri($string) 
	* _get_iri() 
	* _get_uri() 
	* get_uri() 

- Requests_Proxy.php: 	
	* register(Requests_Hooks $hooks) 

- Requests_Response.php: 	
	* public $body, $cookies, $headers, $history, $protocol_version 
	* public $raw, $redirects, $success, $status_code, $url 
	* __construct() 
	* is_redirect(): bool 
	* throw_for_status($allow_redirects = true): void 

- Requests_Session.php: 	
	* public $data, $headers, $options, $url 
	* __construct($url = null, $headers = array(), $data = array(), $options = array()) 
	* __get($key) 
	* __set($key, $value)
	* __isset($key) 
	* __unset($key) 
	* get($url, $headers = [],array ...$options): Requests_Response 
	* head($url, $headers = [],array ...$options): Requests_Response 
	* delete($url, $headers = [],array ...$options): Requests_Response 
	* post($url, $headers = [], $data = [],array ...$options): Requests_Response 
	* put($url, $headers = [], $data = [], array ...$options): Requests_Response 
	* patch($url, $headers, $data = array(), $options = []): Requests_Response 
	* request($url, $headers = [], $data = [], $type = TP_GET, array ...$options): Requests_Response 
	* request_multiple($requests, $options = []) 
	* _merge_request($request, $merge_options = true) 

- Requests_SSL.php: 	
	* verify_certificate($host, $cert): bool static 
	* verify_reference_name($reference): bool static 
	* match_domain($host, $reference): bool static 

- Requests_Transport.php: interface	
	*  request($url, $headers = array(), $data = array(), $options = array());
	*  request_multiple($requests, $options); 
	*  test(); static 

- TP_Requests.php: 	
	* protected static $_certificate_path, $_transports 
	* public static $transport 
	* add_transport($transport): void static 
	* get_transport($capabilities = array()) static 
	* get($url, $headers = array(), $options = array()): Requests_Response static 
	* head($url, $headers = array(), $options = array()): Requests_Response static 
	* delete($url, $headers = array(), $options = array()): Requests_Response static 
	* trace($url, $headers = array(), $options = array()): Requests_Response static 
	* post($url, $headers = array(), $data = array(), $options = array()): Requests_Response static 
	* put($url, $headers = array(), $data = array(), $options = array()): Requests_Response static 
	* options($url, $headers = array(), $data = array(), $options = array()): Requests_Response static 
	* patch($url, $headers, $data = array(), $options = array()): Requests_Response static 
	* request($url, $headers = array(), $data = array(), $type = TP_GET, $options = array()): Requests_Response static 
	* request_multiple($requests, $options = array()) static 
	* get_default_options($multi_request = false):array static 
	* get_certificate_path(): string static 
	* set_certificate_path($path): void static 
	* set_defaults(&$url, &$headers, &$data, &$type, &$options): void static 
	* parse_response($headers, $url, $req_headers, $req_data, $options): Requests_Response static 
	* parse_multiple(&$response, $request): void static 
	* decode_chunked($data): ?string static 
	* flatten($array): array static 
	* flattern($array): array static 
	* decompress($data) static 
	* compatible_gz_inflate($gz_data) static 
	* match_domain($host, $reference): bool static 

- Auth/Requests_Auth_Basic.php: 	
	* public $pass, $user 
	* __construct($args = null) 
	* register(Requests_Hooks $hooks):void 
	* curl_before_send(&$handle):void 
	* fsockopen_header(&$out):void 
	* getAuthString():string 

- Cookie/Requests_Cookie_Jar.php: 	
	* protected $_cookies 
	* __construct($cookies = array()) 
	* normalize_cookie($cookie, $key = null) 
	* normalizeCookie($cookie, $key = null) 
	* offsetExists($key):bool 
	* offsetGet($key) 
	* offsetSet($key, $value):void 
	* offsetUnset($key):void 
	* getIterator() 
	* register(Requests_Hooker $hooks) 
	* before_request($url, &$headers,&$data, &$type, &$options):void 
	* before_redirect_check(Requests_Response $return) 

- Exception/Requests_Exception.php: \Exception	
	* protected $_type, $_data 
	* __construct($message, $type, $data = null, $code = 0) 
	* getType(): int 
	* getData() 

- Exception/Requests_Exception_HTTP.php: extends Requests_Exception	
	* protected $_code, $_reason 
	* __construct($reason = null, $data = null) 
	* getReason() 
	* get_class($code) 

- Exception/Requests_Exception_Transport.php: extends Requests_Exception	

- Exception/Requests_Exception_Transport_cURL.php:  extends Requests_Exception_Transport	
	* public const EASY, MULTI, SHARE 
	* protected $_code, $_reason, $_type 
	* __construct($message, $type, $data = null, $code = 0) 
	* getReason(): string 

- Exception/HTTP.php: 	
	* error code methods. 

- Proxy/Requests_Proxy_HTTP.php: 	
	* public $proxy, $user, $pass, $use_authentication 
	* __construct($args = null) 
	* register(Requests_Hooks $hooks): void 
	* curl_before_send(&$handle): void 
	* fsockopen_remote_socket(&$remote_socket): void 
	* fsockopen_remote_host_path(&$path, $url): void 
	* fsockopen_header(&$out):void 
	* get_auth_string():string 

- Response/Requests_Response_Headers.php: extends Requests_Utility_CaseInsensitiveDictionary	
	* offsetGet($key) 
	* offsetSet($key, $value):bool 
	* getValues($key) 
	* flatten($value) 
	* getIterator(): \ArrayIterator 

- Transport/Requests_Transport_cURL.php: implements Requests_Transport	
	* public const CURL_7_10_5, CURL_7_16_2 
	* protected $_done_headers, $_handle, $_hooks, $_response_bytes, $_response_byte_limit, $_stream_handle 
	* public $headers, $info, $response_data, $version 
	* __construct() 
	* __destruct() 
	* request($url, $headers = array(), $data = array(), $options = array()):string 
	* request_multiple($requests, $options):array 
	* &get_sub_request_handle($url, $headers, $data, $options) 
	* _setup_handle($url, $headers, $data, $options):void 
	* process_response($response, $options) 
	* stream_headers($handle, $headers) 
	* stream_body($handle, $data) 
	* _format_get($url, $data) static 
	* test($capabilities = array()):bool static 
	* _get_expect_header($data):string 

- Transport/Requests_Transport_FSockOpen.php: implements Requests_Transport 	
	* public const SECOND_IN_MICROSECONDS 
	* protected $_max_bytes, $_connect_error 
	* public $headers, $info 
	* request($url, $headers = array(), $data = array(), $options = array()) 
	* request_multiple($requests, $options):array 
	* _accept_encoding() static 
	* _format_get($url_parts, $data) static 
	* connect_error_handler($err_no, $err_str):bool 
	* verify_certificate_from_context($host, $context) 
	* test($capabilities = array()):bool static

- Utility/Requests_Utility_CaseInsensitiveDictionary.php: 	
	* protected $_data 
	* __construct(array $data = array()) 
	* offsetExists($key): bool 
	* offsetGet($key) 
	* offsetSet($key, $value):bool 
	* offsetUnset($key): void 
	* getIterator(): \ArrayIterator 
	* getAll(): array 

- Utility/Requests_Utility_FilteredIterator.php: 	
	* protected $_callback 
	* current() 
	* unserialize($serialized) //todo
	* __unserialize($serialized): void //todo
	* __wake_up(): void 