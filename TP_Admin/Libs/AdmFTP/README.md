### TP_Admin/Libs/AdmFTP

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 

- FTP_Base.php: 	
	* __construct($port_mode=false, $verbose=false, $local_echo=false) 
	* __ftp_Consts():void 

- FTP_Base_Trait.php: 	
	* ftp_base($port_mode=false):void 
	* parse_listing($line) 
	* send_msg($message = "", $crlf=true): bool 
	* set_type($mode=FTP_ASCII): bool 
	* passive($passive_value=null): bool 
	* set_server($host, $port=21, $reconnect=true): bool 
	* set_mask($mask= null): bool 
	* set_base_timeout($timeout): bool 
	* ftp_connect($server = null): bool 
	* ftp_quit($force): bool 
	* ftp_login($user=null, $pass=null): bool 
	* command_pwd() 
	* command_cdup():bool 
	* command_chdir($pathname):bool 
	* command_rmdir($pathname):bool 
	* command_mkdir($pathname):bool 
	* rename($from, $to):bool 
	* file_size($pathname) 
	* abort():bool 
	* command_mdtm($pathname) 
	* system_type() 
	* delete($pathname):bool 
	* site($command, $function="site"):bool 
	* chmod($pathname, $mode):bool 
	* restore($from):bool 
	* features():bool 
	* raw_list($pathname="", $arg="") 
	* is_exists($pathname): bool 
	* file_exists($pathname):bool 
	* file_get($fp, $remote_file, $rest=0): bool 
	* get($remote_file, $local_file=null, $rest=0): bool 
	* file_put($remote_file, $fp, $rest=0):bool 
	* put($local_file, $remote_file=null, $rest=0): bool 
	* mode_put($local=".", $remote=null, $continuous=false): bool 
	* mode_get($remote, $local=".", $continuous=false): bool 
	* mode_del($remote, $continuous=false): bool 
	* mode_mkdir($dir, $mode = 0777): bool 
	* ftp_glob($pattern, $handle=null) //todo sort this out
	* glob_pattern_match($pattern,$string) 
	* global_regexp($pattern,$probe): int 
	* dir_list($remote) 
	* check_code(): bool 
	* ftp_list($arg="", $cmd="LIST", $function="_list") 
	* push_error($function_name,$msg,$desc=false): int 
	* pop_error() 

- FTP_Pure.php: extends FTP_Base 	
	* public $LocalEcho, $Verbose, $OS_local, $OS_remote, $AutoAsciiExt, $AuthorizedTransferMode, $OS_FullName 
	* protected $_base, $_lastaction, $_errors, $_type, $_umask, $_timeout, $_passive, $_host, $_fullhost, $_port 
	* protected $_datahost, $_dataport, $_ftp_control_sock, $_ftp_data_sock, $_ftp_temp_sock, $_ftp_buff_size, $_login 
	* protected $_password, $_connected, $_ready, $_code, $_message, $_can_restore, $_port_available,$_curtype 
	* protected $_features, $_error_array, $_eol_code 
	* use FTP_Base_Trait 
	* __construct($verb=false, $le=false) 
	* set_timeout($sock):bool 
	* connect($host, $port) 
	* read_msg($function = "_read_msg"):bool 
	* exec($cmd, $function="_exec"):bool 
	* data_prepare($mode=FTP_ASCII):bool 
	* data_read($mode=FTP_ASCII, $fp=null) 
	* data_write($mode=FTP_ASCII, $fp=null):bool 
	* __data_write_block($mode, $block):bool 
	* data_close():bool 
	* quit($force= false): void 

- FTP_Sockets.php: extends FTP_Base	
	* public $LocalEcho, $Verbose, $OS_local, $OS_remote, $AutoAsciiExt, $AuthorizedTransferMode, $OS_FullName 
	* protected $_base, $_lastaction, $_errors, $_type, $_umask, $_timeout, $_passive, $_host, $_fullhost, $_port 
	* protected $_datahost, $_dataport, $_ftp_control_sock, $_ftp_data_sock, $_ftp_temp_sock, $_ftp_buff_size, $_login 
	* protected $_password, $_connected, $_ready, $_code, $_message, $_can_restore, $_port_available, $_curtype 
	* protected $_features, $_error_array, $_eol_code, $_stream, $_go 
	* use FTP_Base_Trait 
	* __construct($verb=false, $le=false) 
	* set_timeout($sock):bool 
	* connect($host ='', $port='') 
	* read_msg($function = "_read_msg"):bool 
	* exec($cmd, $function="__exec"):bool 
	* data_prepare($mode=FTP_ASCII):bool 
	* data_read($mode=FTP_ASCII, $fp=null) 
	* data_write($mode=FTP_ASCII, $fp=null):bool 
	* __data_write_block($mode, $block):bool 
	* data_close():bool 
	* quit(): void 

- TP_FTP.php: This is not a class but a setup to switch between FTP_Sockets and FTP_Pure on a condition.	
