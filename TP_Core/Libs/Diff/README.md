### TP_Core/Libs/Diff

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- TextDiff.php: 	
	* protected $_edits  
	* __construct( $engine, $params ) 
	* getDiff() 
	* countAddedLines(): int 
	* countDeletedLines(): int 
	* reverse(): TextDiff 
	* isEmpty(): bool 
	* lcs(): int 
	* getOriginal(): array 
	* getFinal(): array 
	* trimNewlines(&$line): void static
	* getTempDir() static
	* _check($from_lines, $to_lines): bool 

- TP_TextDiff_Renderer_Table.php: 	
	* protected $_diff_threshold, $_inline_diff_renderer, $_show_split_view, $_compat_fields, $_count_cache, $_difference_cache
	* public $leading_context_lines, $trailing_context_lines
	* __construct( $params = array() ) 
	* startBlock( $header ) 
	* lines( $lines, $prefix = ' ' ):void 
	* addedLine( $line ): string //todo change to ul/li
	* deletedLine( $line ): string 
	* contextLine( $line ): string 
	* emptyLine(): string 
	* addedLines( $lines, $encode = true ): string 
	* deletedLines( $lines, $encode = true ): string 
	* contextLines( $lines, $encode = true ): string 
	* changed( $orig, $final ): string 
	* interleave_changed_lines( $orig, $final ): array 
	* compute_string_distance( $string1, $string2 ) 
	* difference( $a, $b ) 
