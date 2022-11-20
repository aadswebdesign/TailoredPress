### TP_Core/Libs/Diff/Components

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- text_diff_engine_native.php: 	
	* protected $_flip, $_in_seq, $_lcs, $_seq, $_x_changed 
	* protected $_x_ind, $_x_value, $_y_changed, $_y_ind, $_y_value 
	* diff($from_lines, $to_lines): array 
	* _lcs_pos($y_pos):int 
	* _diagnose ($x_off, $x_lim, $y_off, $y_lim, $n_chunks):array 
	* _compare_seq ($x_off, $x_lim, $y_off, $y_lim):void 
	* _shift_boundaries($lines, &$changed, $other_changed): void 

- text_diff_engine_shell.php: 	
	* protected $_diff_command 
	* diff($from_lines, $to_lines):array 
	* __getLines(&$text_lines, &$line_no, $end = false):array 

- text_diff_engine_string.php: 	
	* diff($diff, $mode = 'autodetect') 
	* parseUnifiedDiff($diff):array 
	* parseContextDiff(&$diff):array 

- text_diff_engine_xdiff.php: 	
	* diff($from_lines, $to_lines):string 
- text_diff_renderer.php: 	
	* private $__x, $__y
	* protected $_leading_context_lines,$_trailing_context_lines 
	* __construct($params = []) 
	* getParams():array 
	* render(TextDiff $diff):string 
	* _block($x_beg, $x_len, $y_beg, $y_len, &$edits):string 
	* _startDiff():string //todo
	* _endDiff():string  //todo
	* _blockHeader($x_beg, $x_len, $y_beg, $y_len):string 
	* _startBlock($header):string 
	* _endBlock():string 
	* _lines($lines, $prefix = ' '):string 
	* _context($lines):string 
	* _added($lines):string 
	* _deleted($lines):string 
	* _changed($orig, $final):string 
	
- text_diff_renderer_inline.php: 	
	* protected const DEL_PREFIX, DEL_SUFFIX, INS_PREFIX, INS_SUFFIX 
	* protected $_block_header, $_leading_context_lines, $_split_characters, $_split_level, $_trailing_context_lines 
	* _blockHeader($x_beg, $x_len, $y_beg, $y_len):string 
	* _startBlock($header):string 
	* _lines($lines, $prefix = ' ', $encode = true):string 
	* _added($lines):string 
	* _deleted($lines, $words = false):string 
	* _changed($orig, $final):string 
	* _splitOnWords($string, $newlineEscape = "\n"):string 
	* _encode(&$string):string 
