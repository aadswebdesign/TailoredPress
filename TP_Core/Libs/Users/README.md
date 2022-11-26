### TP_Core/Libs/Users

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- TP_Roles.php: 	
	* private $__role_key, $__tp_user_roles 
	* protected $_site_id 
	* public $roles, $role_names, $role_objects, $use_db  
	* __construct( $site_id = null ) 
	* add_role( $role, $display_name,array $capabilities) 
	* remove_role( $role ): void 
	* add_capability( $role, $cap, $grant = true ): void 
	* remove_capability( $role, $cap ): void 
	* get_role( $role ) 
	* get_names(): array 
	* is_role( $role ): bool 
	* init_roles(): void 
	* for_site( $site_id = null ): void 
	* get_site_id(): int 
	* _get_roles_data() 

- TP_User.php: 	
	* public static $tp_db 
	* public $description, $user_description, $first_name, $user_firstname, $last_name, $user_lastname, $user_login, $user_pass, $user_nicename 
	* public $display_name, $spam, $deleted , $user_email, $user_url, $user_registered, $user_activation_key, $user_status, $user_level, $locale
	* public $rich_editing, $syntax_highlighting, $use_ssl, $all_caps, $caps, $cap_key, $data, $filter, $ID, $roles, $site_id  
	* __construct( $id = 0, $name = '', $site_id = '' )
	* init( $data, $site_id = '' ): void 
	* get_data_by( $field, $value ) static 
	* __isset( $key ) 
	* __get( $key ) 
	* __set( $key, $value ) 
	* __unset( $key ) 
	* exists(): bool 
	* get( $key ) 
	* has_property( $key ): bool 
	* to_array(): array 
	* __call( $name, $arguments ) 
	* _init_caps( $cap_key = '' ) 
	* get_role_caps(): array 
	* add_role( $role ): void 
	* remove_role( $role ): void 
	* set_role( $role ): void 
	* level_reduction( $max, $item ) 
	* update_user_level_from_caps(): void 
	* add_cap( $cap, $grant = true ): void 
	* remove_cap( $cap ): void 
	* remove_all_caps(): void 
	* has_cap( $cap, ...$args ): bool 
	* translate_level_to_cap( $level ): string 
	* for_site( $site_id = '' ): void 
	* get_site_id() 
	* __get_caps_data() 

- TP_User_Meta_Session_Tokens.php: extends TP_Session_Tokens	
	* protected $_user_id //todo
	* __construct($user_id = null) 
	* _get_sessions():array 
	* _prepare_session( $session ) 
	* _get_session( $verifier ) 
	* _update_session( $verifier, $session = null ): void 
	* _update_sessions( $sessions ): void 
	* _destroy_other_sessions( $verifier ): void 
	* _destroy_all_sessions(): void 
	* drop_sessions(): void static 

- TP_User_Request.php: 	
	* public $ID, $user_id, $email, $action_name, $status, $created_timestamp, $modified_timestamp 
	* public $confirmed_timestamp, $completed_timestamp, $request_data, $confirm_key 
	* __construct( $post ) 
