### TP_Core/Libs/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- .php: 	
	* protected $_checking_collation //todo added 
	* __construct( $db_user = '', $db_password = '', $db_name = '', $db_host = '' ) 
	* init_charset(): void 
	* determine_charset( $charset, $collate ): array 
	* set_charset( $dbh, $charset = null, $collate = null ): void 
	* set_sql_mode( $modes = [] ): void 
	* set_prefix( $prefix, $set_table_names = true ) -> string|TP_Error
	* set_blog_id( $blog_id, $network_id = 0 ) -> mixed
	* get_blog_prefix( $blog_id = null ): ?string 
	* tables( $scope = 'all', $prefix = true, $blog_id = 0 ): array 
	* select( $db, $dbh = null ): void 
	* _real_escape( $string ): string 
	* escape( $data ) -> array|string
	* escape_by_ref( &$string ): void 
	* prepare( $query, ...$args ): ?string 
	* esc_like( $text ): string 
	* print_error( $str = '' ): bool 
	* show_errors( $show = true ) -> mixed 
	* hide_errors() -> mixed 
	* suppress_errors( $suppress = true ) -> mixed 
	* flush(): void 
	* db_connect( $allow_bail = true): bool 
	* parse_db_host( $host ) -> array|bool
	* check_connection( $allow_bail = true ): bool 
	* query( $query ): int //todo rename to db_query
	* log_query( $query, $query_time, $query_call_stack, $query_start, $query_data ): void 
	* placeholder_escape(): string 
	* add_placeholder_escape( $query ) -> mixed  
	* __do_query( $query ): void 
	* remove_placeholder_escape( $query ) -> mixed  
	* insert( $table, $data, $format = null ): bool 
	* replace( $table, $data, $format = null ): bool 
	* __insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ): bool 
	* insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ): bool 
	* update( $table, $data, $where, $format = null, $where_format = null ): bool 
	* delete( $table, $where, $where_format = null ): bool 
	* _process_fields( $table, $data, $format ) -> mixed  
	* _process_field_formats( $data, $format ) -> mixed  
	* _process_field_charsets( $data, $table ): bool 
	* _process_field_lengths( $data, $table ): bool 
	* get_var( $query = null, $x = 0, $y = 0 ) 
	* get_row( $query = null, $output = OBJECT, $y = 0 ): ?array 
	* get_col( $query = null, $x = 0 ): array 
	* get_results( $query = null, $output = OBJECT ): ?array 
	* _get_table_charset( $table ) -> mixed 
	* _check_ascii( $string ): bool 
	* _check_safe_collation( $query ): bool 
	* get_col_charset( $table, $column ) -> mixed 
	* get_col_length( $table, $column ) -> array|bool
	* strip_invalid_text_for_column( $table, $column, $value ) -> mixed 
	* close_db_connection(): ?bool 
	* _strip_invalid_text( $data ): TP_Error 
	* _strip_invalid_text_from_query( $query ) -> mixed 
	* _strip_invalid_text_for_column( $table, $column, $value ) -> mixed 
	* _get_table_from_query( $query ) -> mixed  
	* _load_col_info(): void 
	* get_col_info( $info_type = 'name', $col_offset = -1 ): ?array 
	* timer_start(): bool 
	* timer_stop() -> mixed  
	* bail( $message, $error_code = '500' ): bool 
	* check_database_version() -> bool|TP_Error 
	* get_charset_collate() -> mixed  
	* has_cap( $db_cap ) -> mixed  
	* get_caller() -> mixed  
	* db_version() -> mixed  
	* db_server_info(): string 