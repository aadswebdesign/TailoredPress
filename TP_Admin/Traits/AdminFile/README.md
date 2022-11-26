### TP_Admin/Traits/AdminFile

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_file_01.php: 	
	* _get_file_description():string //todo
	* _get_home_path():string 
	* _list_files( $folder = '', $levels = 100, $exclusions = []):string 
	* _tp_get_theme_file_editable_extensions( $theme ):string 
	* _tp_print_file_editor_templates() 
	* _tp_edit_theme_file( $args =null ) 
	* _tp_temp_name( $filename = '', $dir = '' ):string 
	* _validate_file_to_edit( $file, $allowed_files = [] ):string 
	* _tp_handle_upload_error( &$file, $message ):array 
	* _tp_handle_upload_action( &$file, $overrides, $time, $action ) 

- _adm_file_02.php: 	
	* _tp_handle_upload( &$file, $overrides = false, $time = null) 
	* _tp_handle_sideload( &$file, $overrides = false, $time = null ) 
	* _download_url( $url, $timeout = 300, $signature_verification = false ) 
	* _verify_file_md5( $filename, $expected_md5 ) 
	* _verify_file_signature( $filename, $signatures, $filename_for_errors = false ) 
	* _tp_trusted_keys() 
	* _unzip_file( $file, $to ) 
	* __unzip_file_ziparchive( $file, $to, $needed_dirs = array() ) 
	* __unzip_file_pclzip( $file, $to, $needed_dirs = array() ) 
	* _copy_dir( $from, $to, $skip_list = array() ) 

- _adm_file_03.php: 	
	* _tp_get_filesystem( $args = false, $context = false, $allow_relaxed_file_ownership = false ):bool 
	* _get_filesystem_method( $args = null, $context = null, $allow_relaxed_file_ownership = false ) 
	* _get_request_filesystem_credentials( $form_post, $type = '', $error = false, $context = '', $extra_fields = null, $allow_relaxed_file_ownership = false ):string 
	* _tp_get_request_filesystem_credentials_modal():string 
	* _tp_opcache_invalidate( $filepath, $force = false ):bool 
