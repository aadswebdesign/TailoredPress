### TP_Core/Libs/IXR

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- IXR_Base64.php: 	
	* protected $_data 
	* __construct($data) 
	* getXml(): string 

- IXR_Client.php: 	
	* public $server, $port, $path, $user_agent, $response 
	* public $message, $debug, $timeout, $headers, $error
	* __construct( $server, $path = false, $port = 80, $timeout = 15 ) 
	* query( ...$args ):string 
	* getResponse(): string 
	* isError(): string 
	* getErrorCode() 
	* getErrorMessage(): string 

- IXR_ClientMulticall.php: 	
	* public $calls 
	* __construct( $server, $path = false, $port = 80 ) 
	* addCall( ...$args ): void 
	* query( ...$args ): string 

- IXR_Date.php: 	
	* protected $_year, $_month, $_day, $_hour, $_minute, $_second, $_timezone 
	* __construct( $time ) 
	* _parse_timestamp($timestamp): void 
	* _parse_iso($iso): void 
	* getIso(): string 
	* getXml(): string 
	* getTimestamp() 

- IXR_Error.php: 	
	* private $__html 
	* public $code, $message 
	* __construct( $code, $message ) 
	* __to_string(): string 
	* __toString( 
	* getXml(): string 

- IXR_IntrospectionServer.php: extends IXR_Server	
	* public $signatures, $help 
	* __construct() 
	* addCallback($method, $callback, $help, ...$args): void 
	* call($methodname, $args) 
	* methodSignature($method)
	* methodHelp($method) 

- IXR_Message.php: 	
	* private $__array_structure, $__array_structure_types, $__current_structure_name 
	* //not used anywhere private $__param; 
	* private $__value, $__current_tag, $__current_tag_contents, $__parser 
	* protected $_message, $_message_type, $_fault_code, $_fault_string 
	* public $method_name, $params, $message_type, $fault_code, $fault_string 
	* __construct( $message ) 
	* parse_messages(): bool 
	* tag_open($tag): void 
	* cdata($cdata): void 
	* tag_close($tag): void 

- IXR_Request.php: 	
	* private $__html 
	* protected $_method, $_args, $_xml 
	* __construct($method, $args) 
	* __to_string(): string 
	* __toString() 
	* getLength(): string 
	* getXml(): string 

- IXR_Server.php: 	
	* public $data, $callbacks, $message, $capabilities 
	* __construct( $callbacks = false, $data = false, $wait = false ) 
	* serve($data = null, $data_status = false): void 
	* call($methodname, $args) 
	* error($error, $message = false): void 
	* output($xml): void 
	* hasMethod($method): bool 
	* setCapabilities(): void 
	* getCapabilities($args) 
	* setCallbacks(): void 
	* listMethods($args): array 
	* multiCall(array $methodcalls): array 

- IXR_Value.php: 	
	* protected $_data, $_type 
	* __construct( $data, $type = false ) 
	* calculateType(): ?string 
	* getXml() 
	* __is_structure($array):bool 
