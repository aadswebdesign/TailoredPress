### TP_Core/Libs/Recovery

**Note:** For what it is now and subject to change. 

**Files/ClassMethods and Vars:**  

- Recovery_Base.php: 	
	- protected $_cookie_service, $_key_service, $_link_service, $_email_service 
	- protected $_is_initialized, $_is_active, $_session_id, $_tp_version 
- TP_Recovery_Mode.php: extends Recovery_Base	
	- __construct() 
	- initialize(): void 
	- is_active(): bool 
	- get_session_id(): string 
	- is_initialized(): bool 
	- handle_error( array $error ) 
	- exit_recovery_mode(): bool 
	- handle_exit_recovery_mode(): void 
	- clean_expired_keys(): void 
	- _handle_cookie(): void 
	- _get_email_rate_limit() 
	- _get_link_ttl() 
	- _get_extension_for_error( $error ) 
	- _store_error( $error ) 
	- _redirect_protected(): void 
	- __is_ssl(): bool 
- TP_Recovery_Mode_Cookie_Service.php: extends Recovery_Base	
	- is_cookie_set(): bool 
	- set_cookie():string 
	- clear_cookie():string 
	- validate_cookie( $cookie = '' ) 
	- get_session_id_from_cookie( $cookie = '' ) 
	- __parse_cookie( $cookie ) 
	- __generate_cookie() 
	- __recovery_mode_hash( $data ) 
- TP_Recovery_Mode_Email_Service.php: extends Recovery_Base	
	- public const RATE_LIMIT_OPTION 
	- maybe_send_recovery_mode_email( $rate_limit, $error, $extension ) 
	- clear_rate_limit() 
	- __send_recovery_mode_email( $rate_limit, $error, $extension ): bool 
	- __get_recovery_mode_email_address() 
	- __get_cause( $extension ) 
	- __get_debug(): array 
- TP_Recovery_Mode_Key_Service.php: extends Recovery_Base	
	- private $__option_name, $__tp_hasher 
	- generate_recovery_mode_token() 
	- generate_and_store_recovery_mode_key( $token ) 
	- validate_recovery_mode_key( $token, $key, $ttl ) 
	- clean_expired_keys( $ttl ): void 
	- __remove_key( $token ): void 
	- __get_keys(): array 
	- __update_keys( array $keys ) 
- TP_Recovery_Mode_Link_Service.php: extends Recovery_Base	
	- public const LOGIN_ACTION_ENTER, LOGIN_ACTION_ENTERED 
	- generate_url() 
	- handle_begin_link( $ttl ): void 
	- get_recovery_mode_begin_url( $token, $key ) 
