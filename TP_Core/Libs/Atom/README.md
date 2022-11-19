### TP_Core/Libs/Atom

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 
- Entry.php: 	
	* public $links 
	* public $categories  
- Feed.php: 	
	* public $links 
	* public $categories 
	* public $entries 
- Parser.php: 	
	* public const NS 
	* public const ATOM_CONTENT_ELEMENTS 
	* public const ATOM_SIMPLE_ELEMENTS 
	* public const FILE 
	* protected $_debug, $_depth, $_indent 
	* protected static $_file, $_ns, $_ace, $_ase 
	* public $error,$content,$in_content,$ns_contexts,$ns_decls,$content_ns_decls 
	* public $content_ns_contexts, $is_xhtml, $is_html,$is_text,$skipped_div 
	* public $feed,$current,$map_attrs_func,$map_xmlns_func 
	* __construct() 
	* map_attrs($k, $v): string 
	* map_xmlns($n): string 
	* error_handler($log_level, $log_text, $error_file, $error_line): string 
	* parse(): string 
	* start_element($name, $attrs):string 
	* end_element($name) :string 
	* start_ns($prefix, $uri):string 
	* end_ns(/** @noinspection PhpUnusedParameterInspection */$parser, $prefix):string 
	* cdata(/** @noinspection PhpUnusedParameterInspection */$parser, $data):string 
	* ns_to_prefix($q_name, $attr=false) 
	* is_declared_content_ns($new_mapping):string 
	* xml_escape($content) 
	* _default($parser, $data):string 
	* _p($msg):string 

