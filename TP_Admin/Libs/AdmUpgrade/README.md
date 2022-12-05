### TP_Admin/Libs/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 
- Automatic_Upgrader_Skin.php: extends TP_Upgrader_Skin 	
	* protected $_messages 
	* request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ):string 
	* get_upgrade_messages():string 
	* feedback(TP_Error $feedback, ...$args ):void 
	* get_header() 
	* get_footer() 
- Language_Pack_Upgrader.php: extends TP_Upgrader //todo	
	* public $result, $bulk 
	* async_upgrade( $upgrader = false ):void{} static 
	* upgrade_strings():void{} 
	* upgrade( $update = false,array $args):void{} 
	* bulk_upgrade(array $language_updates,array $args):void{} 
	* check_package( $source, $remote_source ):void{} 
	* get_name_for_update( $update ):void{} 
	* clear_destination( $remote_destination ):bool{} 
- TP_Upgrader.php: //todo	
	* public $strings, $skin, $result, $update_count, $update_current 
	* __construct( $skin = null ){} 
	* init():void{} 
	* generic_strings():void{} 
	* fs_connect(array $directories, $allow_relaxed_file_ownership = false ):bool{} 
	* unpack_package( $package, $delete_package = true ):string{} 
	* _flatten_dir_list( $nested_files, $path = '' ):string{} 
	* clear_destination( $remote_destination ):bool{} 
	* install_package(array $args):string{} 
	* run( $options ):string{} 
	* maintenance_mode( $enable = false ):void{} 
	* create_lock( $lock_name, $release_timeout = null ):bool static 
	* release_lock( $lock_name ):string static 
- TP_Upgrader_Skin.php: //todo	
	* public $upgrader, $done_header, $done_footer, $result, $options 
	* __construct( array $args) 
	* set_upgrader( &$upgrader ):void 
	* add_strings():string{} 
	* set_result( $result ):void 
	* request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ):string 
	* get_header() 
	* get_footer() 
	* get_error(TP_Error $errors ):void 
	* feedback( $feedback, ...$args ):void
	* before():string {}
	* after():string {}
	* get_decrement_update_count( $type )
	* bulk_header():string{}
	* bulk_footer():string {}
	* hide_process_failed( $tp_error ):bool
	