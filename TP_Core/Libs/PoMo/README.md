### TP_Core/Libs/PoMo

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- Gettext_Translations.php: 	
	* protected $_gettext_select_plural_form,$_nplurals 
	* gettext_select_plural_form( $count ) -> mixed 
	* nplurals_and_expression_from_header( $header ):array 
	* make_plural_form_function( $nplurals = 2, $expression ):?array 
	* parenthesize_plural_expression( $expression ):string 
	* make_headers( $translation ):array 
	* set_header( $header, $value ):string 

- MO.php: 	
	* public $_nplurals 
	* private $__filename 
	* get_filename(): string 
	* import_from_file( $filename ) 
	* export_to_file( $filename ) 
	* export() 
	* is_entry_good_for_export( $entry ): bool 
	* export_to_file_handle( $fh ): bool 
	* export_original( $entry ) 
	* export_translations( $entry ) 
	* export_headers(): string 
	* get_byteorder( $magic ) 
	* import_from_reader(POMO_Reader $reader ): bool 
	* &make_entry( $original, $translation ): TP_Translation_Entry 
	* select_plural_form( $count ): int 
	* get_plural_forms_count(): int 

- NOOP_Translations.php: 	
	* public $entries, $headers 
	* add_entry( $entry ): bool 
	* set_header( $header, $value): bool 
	* set_headers( $headers ): bool 
	* get_header( $header ): bool 
	* translate_entry( &$entry ): bool 
	* translate( $singular, $context = null ) 
	* select_plural_form( $count ): int 
	* get_plural_forms_count(): int 
	* translate_plural( $singular, $plural, $count, $context = null ) 
	* merge_with( &$other ): bool 

- Plural_Forms.php: 	
	* public const OP_CHARS, NUM_CHARS 
	* protected static $_op_precedence 
	* protected $_tokens, $_cache 
	* __construct( $str ) 
	* _parse( $str ): void 
	* get( $num ) 
	* execute( $n ): int 

- PO.php: extends Gettext_Translations	
	* public $comments_before_headers 
	* __construct() 
	* export_headers() 
	* export_entries(): string 
	* export( $include_headers = true ): string 
	* export_to_file( $filename, $include_headers = true ) 
	* set_comment_before_headers( $text ): void 
	* po_ify( $string ) static 
	* un_po_ify( $string ) static 
	* prepend_each_line( $string, $with ): string static 
	* comment_block( $text, $char = ' ' ) static 
	* export_entry( $entry ) static 
	* match_begin_and_end_newlines( $translation, $original ) static 
	* import_from_file( $filename ): bool
	* _is_final( $context ): bool static 
	* read_entry( $f, $line_no = 0 ) 
	* read_line( $f, $action = 'read' ): bool
	* add_comment_to_entry( &$entry, $po_comment_line ): void
	* trim_quotes( $s ): string static 

- POMO_CachedFileReader.php: extends POMO_StringReader 	
	* __construct( $filename ) 

- POMO_CachedIntFileReader.php:  extends POMO_CachedFileReader 	

- POMO_FileReader.php: extends POMO_Reader 	
	* protected $_f_open 
	* __construct( $filename ) 
	* read( $bytes ) 
	* seekto( $pos ): bool 
	* is_resource():bool 
	* fe_of(): string 
	* close():bool 
	* read_all(): string 

- POMO_Reader.php: 	
	* public $endian, $_post 
	* __construct() 
	* setEndian( $endian ): void 
	* readint32($filename = '') 
	* readint32array( $count,$filename = '' ) 
	* substr( $string, $start, $length ) 
	* strlen( $string ) 
	* str_split( $string, $chunk_size ): array 
	* pos():bool 
	* is_resource():bool 
	* close():bool 

- POMO_StringReader.php: extends POMO_Reader 	
	* protected $_str 
	* __construct( $str = '' ) 
	* read( $bytes ) 
	* seekto( $pos ) 
	* length() 
	* read_all() 

- TP_Translation_Entry.php: 	
	* public $is_plural, $context, $singular, $plural, $translations 
	* public $translator_comments, $extracted_comments, $references, $flags 
	* __construct( $args = [] ) 
	* key() 
	* merge_with( &$other ): void 

- TP_Translations.php: 	
	* public $entries, $headers 
	* add_entry( $entry ): bool 
	* add_entry_or_merge( $entry ): bool 
	* set_header( $header, $value ): string 
	* set_headers( $headers ): void 
	* get_header( $header ): bool 
	* translate_entry(TP_Translation_Entry $entry ): bool 
	* translate( $singular, $context = null ) 
	* select_plural_form( $count ):int 
	* get_plural_forms_count():int 
	* translate_plural( $singular, $plural, $count, $context = null ) 
	* merge_with( &$other ): void 
	* merge_originals_with( &$other ): void 
