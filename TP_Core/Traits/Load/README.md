### TP_Core/Traits/Load

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _load_01.php: 	
	- _tp_get_server_protocol():string 
	- _tp_fix_server_vars():void 
	- _tp_populate_basic_auth_from_authorization_header():void 
	- _tp_check_php_mysql_versions():void 
	- _tp_get_environment_type():string 
	- _tp_favicon_request():void 
	- _maintenance_class():string 
	- _tp_maintenance():void 
	- _timer_float() -> mixed 
	- _timer_start():bool 
- _load_02.php: 	
	- _get_timer_stop($precision = 3 ):string 
	- _timer_stop( $precision = 3 ):void 
	- _tp_debug_mode():void 
	- _tp_set_lang_dir():void 
	- _require_tp_db():void 
	- _tp_set_tpdb_vars():void 
	- _tp_using_ext_object_cache( $using = null ) -> mixed  
	- _external_cache_class($path = null,$class = null) -> mixed 
	- _tp_start_object_cache():void 
	- _tp_not_installed():void 
	- _tp_get_active_and_valid_themes():array 
	- _tp_skip_paused_themes( array $themes ):array 
- _load_03.php: 	
	- _tp_is_recovery_mode() -> mixed 
	- _is_protected_endpoint():bool 
	- _is_protected_async_action():bool 
	- _tp_set_internal_encoding():void 
	- _tp_magic_quotes():void 
	- _shutdown_action_hook():void 
	- _tp_clone($object) -> mixed 
	- _is_admin():bool 
	- _is_blog_admin():bool 
	- _is_network_admin():bool 
- _load_04.php: 	
	- _is_user_admin():bool 
	- _is_multisite():bool 
	- _get_current_blog_id() -> mixed 
	- _get_current_network_id():int 
	- _tp_load_translations_early():void 
	- _tp_installing( $is_installing = null ):bool 
	- _is_ssl():bool 
	- _tp_convert_hr_to_bytes( $value ) -> mixed 
	- _tp_is_ini_value_changeable( $setting ):bool 
	- _tp_doing_async() -> mixed 
- _load_05.php: 	
	- _tp_using_themes() -> mixed 
	- _tp_doing_cron() -> mixed 
	- _tp_is_file_mod_allowed( $context ) -> mixed 
	- _tp_start_scraping_edited_file_errors():void 
	- _tp_finalize_scraping_edited_file_errors( $scrape_key ):void 
	- _tp_is_json_request():bool 
	- _tp_is_jsonp_request():bool 
	- _tp_is_json_media_type( $media_type ) -> mixed 
	- _tp_is_xml_request():bool 
	- _tp_is_site_protected_by_basic_auth( $context = '' ) -> mixed
