### TP_Admin/Libs/AdmUtils

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 

- Adm_Debug_Data.php: todo	
	* check_for_updates(){return '';} static
	* debug_data(){return '';} static
	* get_mysql_var( $var ){return '';} static
	* format( $info_array, $type ){return '';} static
	* get_database_size(){return '';} static
	* get_sizes(){return '';} static

- Adm_Site_Health.php: //todo
	* __construct(){}  
	* show_site_health_tab( $tab ){return '';} 
	* get_instance(){return '';} 
	* sh_enqueue_scripts(){return '';} 
	* __perform_test( $callback ){return '';} 
	* __prepare_sql_data(){} 
	* check_tp_version_check_exists(){return '';} 
	* get_test_tailoredpress_version(){return '';}  
	* get_test_theme_version(){return '';} 
	* get_test_php_version(){return '';} 
	* __test_php_extension_availability( $extension_name = null, $function_name = null, $constant_name = null, $class_name = null ) 
	* get_test_php_extensions(){return '';} 
	* get_test_php_default_timezone(){return '';} 
	* get_test_php_sessions(){return '';} 
	* get_test_sql_server(){return '';}  
	* get_test_utf8mb4_support(){return '';} 
	* get_test_dot_org_communication(){return '';} 
	* get_test_is_in_debug_mode(){return '';} 
	* get_test_https_status(){return '';} 
	* get_test_ssl_support(){return '';} 
	* get_test_scheduled_events(){return '';} 
	* get_test_background_updates(){return '';}  
	* get_test_theme_auto_updates(){return '';} 
	* get_test_loopback_requests(){return '';} 
	* get_test_http_requests(){return '';} 
	* get_test_rest_availability(){return '';} 
	* get_test_file_uploads(){return '';} 
	* get_test_authorization_header(){return '';} 
	* get_tests(){return '';} static 
	* admin_body_class( $body_class ){return '';} 
	* __tp_schedule_test_init(){} 
	* __get_cron_tasks(){} 
	* has_missed_cron(){return '';} 
	* has_late_cron(){return '';} 
	* detect_theme_auto_update_issues(){return '';} 
	* can_perform_loopback(){return '';} 
	* maybe_create_scheduled_event(){return '';} 
	* tp_cron_scheduled_check(){return '';} 
	* is_development_environment(){return '';} 

- Zip_Base.php:
	* public $zip_name, $zip_fd, $error_code, $error_string, $magic_quotes_status, $g_pclzip_version
	* __construct()
	* _PclZipUtilArrayMerge(...$merges): array
	* _PclZipUtilArrayMergeRecursive(...$merges): array

- Adm_Zip.php: extends Zip_Base
	* private $__v_sort_flag
	* __construct($p_zipname)
	* createZip($p_filelist):string
	* _add($p_filelist):string
	* listZipContent():int
	* extractZip():string
	* extract_by_index($p_index):int
	* delete():string
	* delete_by_index($p_index):int
	* duplicate($p_archive):string
	* merge($p_archive_to_add):string
	* error_code():string
	* error_name($p_with_code=false)
	* error_info($p_full=false)
	* __privCheckFormat($p_level=null):bool
	* __privParseOptions(&$p_options_list, $p_size, &$v_result_list,array $v_requested_options=false)
	* __privOptionDefaultThreshold(&$p_options):int
	* __privFileDescrParseAtt(&$p_file_list, &$p_filedescr,array $v_requested_options=false)
	* __privFileDescrExpand(&$p_filedescr_list, &$p_options):int
	* __privCreate($p_filedescr_list, &$p_result_list, &$p_options):void
	* __privAdd($p_filedescr_list, &$p_result_list, &$p_options)
	* __privOpenFd($p_mode)
	* __privCloseFd():int
	* __privAddList($p_filedescr_list, &$p_result_list, &$p_options)
	* __privAddFileList($p_filedescr_list, &$p_result_list, &$p_options)
	* __privAddFile($p_filedescr, &$p_header, &$p_options)
	* __privAddFileUsingTempFile($p_filedescr, &$p_header)
	* __privCalculateStoredFilename(&$p_filedescr, &$p_options):int
	* __privWriteFileHeader(&$p_header):int
	* __privWriteCentralFileHeader(&$p_header):int
	* __privWriteCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment):int
	* __privList(&$p_list)
	* __privConvertHeader2FileInfo($p_header, &$p_info):int
	* __privExtractByRule(&$p_file_list, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
	* __privExtractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
	* __privExtractFileUsingTempFile(&$p_entry, &$v_file)
	* __privExtractFileInOutput(&$p_entry, &$p_options)
	* __privExtractFileAsString(&$p_entry, &$p_string, &$p_options)
	* __privReadFileHeader(&$p_header):int
	* __privReadCentralFileHeader(&$p_header):int
	* __privCheckFileHeaders(&$p_local_header, &$p_central_header):int
	* __privReadEndCentralDir(&$p_central_dir)
	* __privDeleteByRule(&$p_result_list, &$p_options)
	* __privDirCheck($p_dir, $p_is_dir=false)
	* __privMerge(self $p_archive_to_add)
	* __privDuplicate($p_archive_filename)
	* __privErrorLog($p_error_code=0, $p_error_string=''): void
	* __privErrorReset():void
	* __privDisableMagicQuotes():int
	* __privSwapBackMagicQuotes():int

- .php:
	*   
	*  
	*  
	*  
	*  
	*  
	*  

- .php:
	* 
	* 
	* 
	* 
	* 
	* 
	* 

- .php:
	*

- .php:
	*

- .php:
	*

- .php:
	*
