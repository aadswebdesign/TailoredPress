### TP_Admin/Libs/AdmFilesystem

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 
- Adm_Filesystem_Base.php: 	
	* protected $_params, $_verbose, $_cache, $_method, $_errors, $_options 
	* abspath():string 
	* tp_content_dir():string 
	* tp_library_dir():string 
	* tp_themes_dir( $theme = false ):string 
	* tp_lang_dir():string 
	* find_folder( $folder ):string 
	* search_for_folder( $folder, $base = '.', $loop = false ):string 
	* get_head_ch_mods( $file ):string 
	* get_ch_mod( $file ):string 
	* get_num_chmod_from_head( $mode ):string 
	* _is_binary( $text ):bool 
	* ch_own( $file, $owner, $recursive = false ) 
	* connect():bool 
	* get_contents( $file ) 
	* get_contents_array( $file ) 
	* put_contents( $file, $contents, $mode = false ) 
	* cwd() 
	* chdir( $dir ) 
	* ch_grp( $file, $group, $recursive = false ) 
	* chmod( $file, $mode = false, $recursive = false ) 
	* owner( $file ) 
	* group( $file ) 
	* copy( $source, $destination, $overwrite = false, $mode = false ) 
	* move( $source, $destination, $overwrite = false ) 
	* delete( $file, $recursive = false, $type = false ) 
	* exists( $file ) 
	* is_file( $file ) 
	* is_dir( $path ) 
	* is_readable( $file ) 
	* is_writable( $file ) 
	* a_time( $file ) 
	* mtime( $file ) 
	* size( $file ) 
	* touch( $file, $time = 0, $a_time = 0 ) 
	* mkdir( $path, $chmod = false, $ch_own = false, $ch_group = false ) 
	* rmdir( $path, $recursive = false ):bool 
	* dirlist( $path, $include_hidden = true, $recursive = false ) 
	* _tp_ssh2_sftp_chmod($link, $path, $chmod)//added method 

- Adm_Filesystem_Direct.php: extends Adm_Filesystem_Base 	
	* __construct( $arg ) 
	* get_contents( $file ) 
	* get_contents_array( $file ) 
	* put_contents( $file, $contents, $mode = false ):bool 
	* cwd() 
	* chdir( $dir ) 
	* ch_grp( $file, $group, $recursive = false ) 
	* chmod( $file, $mode = false, $recursive = false ) 
	* ch_own( $file, $owner, $recursive = false ) 
	* owner( $file ) 
	* get_chmod( $file ) 
	* copy( $source, $destination, $overwrite = false, $mode = false ) 
	* move( $source, $destination, $overwrite = false ) 
	* delete( $file, $recursive = false, $type = false ) 
	* exists( $file ) 
	* is_file( $file ) 
	* is_dir( $path ) 
	* is_readable( $file ) 
	* is_writable( $file ) 
	* a_time( $file ) 
	* mtime( $file ) 
	* size( $file ) 
	* touch( $file, $time = 0, $a_time = 0 ) 
	* mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) 
	* rm_dir( $path, $recursive = false ) 
	* dirlist( $path, $include_hidden = true, $recursive = false ) 

- Adm_Filesystem_FTP_Ext.php: extends Adm_Filesystem_Base	
	* public $link 
	* __construct( $opt = '' ) 
	* connect():bool 
	* get_contents( $file ) 
	* get_contents_array( $file ) 
	* put_contents( $file, $contents, $mode = false ) 
	* cwd() 
	* chdir( $dir ) 
	* chmod( $file, $mode = false, $recursive = false ) 
	* owner( $file ) 
	* get_ch_mod( $file ):string 
	* group( $file ) 
	* copy( $source, $destination, $overwrite = false, $mode = false ) 
	* move( $source, $destination, $overwrite = false ) 
	* delete( $file, $recursive = false, $type = false ) 
	* exists( $file ) 
	* is_file( $file ) 
	* is_dir( $path ) 
	* is_readable( $file ) 
	* is_writable( $file ) 
	* a_time( $file ) 
	* mtime( $file ) 
	* size( $file ) 
	* touch( $file, $time = 0, $a_time = 0 ) 
	* mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) 
	* rm_dir( $path, $recursive = false ) 
	* parse_listing( $line ):array 
	* dirlist( $path = '.', $include_hidden = true, $recursive = false ) 
	* __destruct() 

- Adm_Filesystem_FTP_Socket.php: extends Adm_Filesystem_Base	
	* public $ftp 
	* __construct( $opt = '' ) 
	* connect():bool 
	* get_contents( $file ) 
	* get_contents_array( $file ) 
	* put_contents( $file, $contents, $mode = false ) 
	* cwd() 
	* chdir( $dir ) 
	* chmod( $file, $mode = false, $recursive = false ) 
	* owner( $file ) 
	* get_chmod( $file ) 
	* group( $file ) 
	* copy( $source, $destination, $overwrite = false, $mode = false ) 
	* move( $source, $destination, $overwrite = false ) 
	* delete( $file, $recursive = false, $type = false ) 
	* exists( $file ) 
	* is_file( $file ) 
	* is_dir( $path ) 
	* is_readable( $file ) 
	* is_writable( $file ) 
	* a_time( $file ) 
	* mtime( $file ) 
	* size( $file )
	* touch( $file, $time = 0, $a_time = 0 ) 
	* mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) 
	* rmdir( $path, $recursive = false ) 
	* dirlist( $path = '.', $include_hidden = true, $recursive = false ) 
	* __destruct() 

- Adm_Filesystem_SSH_Two.php: extends Adm_Filesystem_Base	
	* public $link, $sftp_link, $keys 
	* __construct( $opt = '' ) 
	* connect():bool 
	* sftp_path( $path ) :string 
	* run_command( $command, $return_bool = false ) 
	* get_contents( $file ) 
	* get_contents_array( $file ) 
	* put_contents( $file, $contents, $mode = false ) 
	* cwd() 
	* chdir( $dir ) 
	* ch_grp( $file, $group, $recursive = false ) 
	* chmod( $file, $mode = false, $recursive = false ) 
	* ch_own( $file, $owner, $recursive = false ) 
	* owner( $file ) 
	* get_ch_mod( $file ) //todo
	* group( $file ) 
	* copy( $source, $destination, $overwrite = false, $mode = false ) 
	* move( $source, $destination, $overwrite = false ) 
	* delete( $file, $recursive = false, $type = false ) 
	* exists( $file ) 
	* is_file( $file ) 
	* is_dir( $path ) 
	* is_readable( $file ) 
	* is_writable( $file ) 
	* a_time( $file ) 
	* mtime( $file ) 
	* size( $file )
	* touch( $file, $time = 0, $a_time = 0 ) 
	* mkdir( $path, $chmod = false, $chown = false, $chgrp = false ) 
	* rmdir( $path, $recursive = false ):bool 
	* dirlist( $path, $include_hidden = true, $recursive = false ) 
