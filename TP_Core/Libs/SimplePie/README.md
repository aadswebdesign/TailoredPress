### TP_Core/Libs/SimplePie

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- SimplePie.php: 	
	* __construct() 
	* __toString() 
	* __destruct() 
	* sp_force_feed($enable = false): void 
	* sp_set_feed_url($url): void 
	* sp_set_file(&$file): bool 
	* sp_set_raw_data($data): void 
	* sp_set_timeout($timeout = 10): void 
	* sp_set_curl_options(array $curl_options = []): void 
	* sp_set_force_fsockopen($enable = false): void 
	* sp_set_enable_cache($enable = true): void 
	* sp_force_cache_fallback($enable = false): void 
	* sp_set_cache_duration($seconds = 3600): void 
	* sp_set_autodiscovery_cache_duration($seconds = 604800): void 
	* sp_set_cache_location($location = './cache'): void 
	* sp_get_cache_filename($url) 
	* sp_enable_order_by_date($enable = true): void 
	* sp_set_input_encoding($encoding = false): void 
	* sp_set_autodiscovery_level($level = SP_LOCATOR_ALL): void 
	* &sp_get_registry(): SimplePie_Registry 
	* sp_set_cache_class($class = 'SimplePie_Cache'): bool 
	* sp_set_locator_class($class = 'SimplePie_Locator'): bool 
	* sp_set_parser_class($class = 'SimplePie_Parser'): bool 
	* sp_set_file_class($class = 'SimplePie_File'): bool 
	* sp_set_sanitize_class($class = 'SimplePie_Sanitize'): bool 
	* sp_set_item_class($class = 'SimplePie_Item'): bool 
	* sp_set_author_class($class = 'SimplePie_Author'): bool 
	* sp_set_category_class($class = 'SimplePie_Category'): bool 
	* sp_set_enclosure_class($class = 'SimplePie_Enclosure'): bool 
	* sp_set_caption_class($class = 'SimplePie_Caption'): bool 
	* sp_set_copyright_class($class = 'SimplePie_Copyright'): bool 
	* sp_set_credit_class($class = 'SimplePie_Credit'): bool 
	* sp_set_rating_class($class = 'SimplePie_Rating'): bool 
	* sp_set_restriction_class($class = 'SimplePie_Restriction'): bool 
	* sp_set_content_type_sniffer_class($class = 'SimplePie_Content_Type_Sniffer'): bool 
	* sp_set_source_class($class = 'SimplePie_Source'): bool 
	* sp_set_useragent($ua = SP_USERAGENT): void 
	* sp_set_cache_name_function($function = 'md5'): void 
	* sp_set_stupidly_fast($set = false): void 
	* sp_set_max_checked_feeds($max = 10): void 
	* sp_set_remove_div($enable = true): void 
	* sp_set_strip_html_tags($tags = '', $encode = null): mixed 
	* sp_set_encode_instead_of_strip($enable = true): void 
	* sp_set_strip_attributes($atts = ''): void 
	* sp_set_add_attributes($atts = ''): void 
	* sp_set_output_encoding($encoding = 'UTF-8'): void 
	* sp_set_strip_comments($strip = false): void 
	* sp_set_url_replacements($element_attribute = null): void 
	* sp_set_https_domains($domains = []): void 
	* sp_set_image_handler($page = false, $qs = 'i'): void 
	* sp_set_item_limit($limit = 0): void 
	* sp_enable_exceptions($enable = true): void 
	* sp_init(): bool 
	* _sp_fetch_data(&$cache) 
	* sp_errors() 
	* sp_status_code() 
	* sp_get_raw_data() 
	* sp_get_encoding() 
	* sp_handle_content_type($mime = 'text/html'): void 
	* sp_get_type() 
	* sp_subscribe_url($permanent = false) 
	* sp_get_feed_tags($namespace, $tag) 
	* sp_get_channel_tags($namespace, $tag) 
	* sp_get_image_tags($namespace, $tag) 
	* sp_get_base($element = []) 
	* sp_sanitized($data, $type, $base = ''): string 
	* sp_get_title(): ?string 
	* sp_get_category($key = 0) 
	* sp_get_author($key = 0) 
	* sp_get_authors() 
	* sp_get_contributor($key = 0) 
	* sp_get_contributors() 
	* sp_get_link($key = 0, $rel = 'alternate') 
	* sp_get_permalink() 
	* sp_get_links(/** @noinspection NotOptimalRegularExpressionsInspection */ $rel = 'alternate') 
	* sp_get_all_discovered_feeds(): array 
	* sp_get_description(): ?string 
	* sp_get_copyright(): ?string 
	* sp_get_language(): ?string 
	* sp_get_latitude(): ?float 
	* sp_get_longitude(): ?float 
	* sp_get_image_title(): ?string 
	* sp_get_image_url(): ?string 
	* sp_get_image_link(): ?string 
	* sp_get_image_width() 
	* sp_get_image_height() 
	* sp_get_item_quantity($max = 0): int 
	* sp_get_item($key = 0) 
	* sp_get_items($start = 0, $end = 0): array 

- Depedencies/idna_convert.php: 	
	* __construct(...$options) 
	* set_parameter($option, $value = false):bool 
	* decode($input, $one_time_encoding = false) 
	* encode($decoded, $one_time_encoding = false) 
	* encode_uri($uri) 
	* get_last_error():bool 
	* _decode($encoded) 
	* _encode($decoded):string 
	* _adapt($delta, $new_points, $is_first):int 
	* _encode_digit($d):string
	* _decode_digit($cp):int 
	* _error($error = ''):void 
	* _hangul_decompose($char): array 
	* _hangul_compose($input):array 
	* _get_combining_class($char) 
	* _apply_canonical_ordering($input) 
	* _combine($input) 
	* _utf8_to_ucs4($input) 
	* _ucs4_to_utf8($input) 
	* _ucs4_to_ucs4_string($input):string 
	* _ucs4_string_to_ucs4($input) 
	* _byte_length($string) 
	* getInstance($params = array()): idna_convert 
	* singleton($params = array()) 
	
- Depedencies/Encoding/_encodings.php: 	trait
	* use _encoding_01,_encoding_02,_encoding_03,_encoding_04,_encoding_05 

- Depedencies/Encoding/Factory/_encoding_01.php: trait 	
	* sp_time_hms($seconds):string 
	* sp_absolutize_url($relative, $base) 
	* sp_get_element($real_name, $string):array 
	* sp_element_implode($element):string 
	* sp_error($message, $level, $file, $line) 
	* sp_fix_protocol($url, $https = 1) 
	* sp_array_merge_recursive($array1, $array2) 
	* sp_parse_url($url):array 
	* sp_compress_parse_url($scheme = '', $authority = '', $path = '', $query = '', $fragment = ''):string 
	* sp_normalize_url($url):string 

- Depedencies/Encoding/Factory/_encoding_02.php: trait 	
	* sp_percent_encoding_normalization($match) 
	* sp_change_encoding($data, $input, $output) 
	* sp_get_curl_version() 
	* sp_strip_comments($data):string 
	* sp_parse_date($dt) 
	* sp_entities_decode($data): ?array 
	* __sp_windows_1252_to_utf8($string):string 
	* __sp_change_encoding_mb_string($data, $input, $output) 
	* __sp_change_encoding_icon_v($data, $input, $output):string 
	* __sp_change_encoding_u_converter($data, $input, $output):string 

- Depedencies/Encoding/Factory/_encoding_03.php: trait 	
	* sp_uncomment_rfc822($string):string 
	* sp_parse_mime($mime): string 
	* sp_atom_03_construct_type($atts): int 
	* sp_atom_10_construct_type($atts) 
	* sp_atom_10_content_construct_type($atts) 
	* sp_is_i_segment_nz_nc($string):bool 
	* sp_space_separated_tokens($string):array 
	* sp_code_point_to_utf8($code_point):string 
	* sp_parse_str($str):array 
	* sp_xml_encoding($data, $registry):array 

- Depedencies/Encoding/Factory/_encoding_04.php: trait 	
	* protected $_sp_prepare, $_sp_quote 
	* sp_output_javascript():string 
	* sp_get_build():int 
	* sp_debug(&$sp):string 
	* sp_silence_errors($num, $str):void 
	* sp_url_remove_credentials($url) 
	* get_date_object($date):int 
	* iri_absolutize($base, $relative) 
	* sp_sort_items($a, $b):int 
	* sp_merge_items($urls, $start = 0, $end = 0, $limit = 0):array 
	* __store_links(&$file, $hub, $self):void 
	* sp_prepare(\PDO $pdo, $value): \PDOStatement 
	* sp_quote(\PDO $pdo, $value):string 

- Depedencies/Encoding/Factory/_encoding_05.php: 	
	* __sp_encoding($charset):string 
	* _sp_encoding($charset):string 
	
- Depedencies/Masterminds/HTML5.php: 	
	* __construct(array $added_options = []) 
	* getOptions():array 
	* load($file, array $options = []): \DOMDocument 
	* loadHTML($string, array $options = []): \DOMDocument 
	* loadHTMLFile($file, array $options = []): \DOMDocument 
	* loadHTMLFragment($string, array $options = []): \DOMDocumentFragment 
	* getErrors() 
	* hasErrors():bool 
	* parse($input, array $options = []): \DOMDocument 
	* parseFragment($input, array $options = []): \DOMDocumentFragment 
	* save($dom,$file, $options = []):void 
	* saveHTML($dom, $options = []):string 

- Depedencies/Masterminds/HTML_5/Elements.php: 	
	* public const KNOWN_ELEMENT, TEXT_RAW, TEXT_RCDATA, VOID_TAG 
	* public const AUTOCLOSE_P, TEXT_PLAINTEXT, BLOCK_TAG, BLOCK_ONLY_INLINE
	* isA($name, $mask):bool static 
	* isHtml5Element($name):bool static 
	* isMathMLElement($name):bool static 
	* isSvgElement($name):bool static 
	* isElement($name):bool static 
	* element($name) static 
	* normalizeSvgElement($name) static 
	* normalizeSvgAttribute($name) static 
	* normalizeMathMlAttribute($name):string static 

- Depedencies/Masterminds/HTML_5/Exception.php:  extends \Exception	

- Depedencies/Masterminds/HTML_5/InstructionProcessor.php: 	
	* process(\DOMElement $element, $name, $data); interface

- Depedencies/Masterminds/HTML_5/Parser/CharacterReference.php: 	
	* protected static $numeric_mask 
	* lookupName($name) static  
	* lookupDecimal($int):string static  
	* lookupHex($hexdec):string static  

- Depedencies/Masterminds/HTML_5/Parser/DOMTreeBuilder.php: 	
	* public const OPT_DISABLE_HTML_NS, OPT_TARGET_DOC, OPT_IMPLICIT_NS, IM_INITIAL, IM_BEFORE_HTML 
	* public const IM_BEFORE_HEAD, IM_IN_HEAD, IM_IN_HEAD_NOSCRIPT, IM_AFTER_HEAD, IM_IN_BODY, IM_TEXT 
	* public const IM_IN_TABLE, IM_IN_TABLE_TEXT, IM_IN_CAPTION, IM_IN_COLUMN_GROUP, IM_IN_TABLE_BODY
	* public const IM_IN_ROW, IM_IN_CELL, IM_IN_SELECT, IM_IN_SELECT_IN_TABLE, IM_AFTER_BODY,IM_IN_FRAMESET 
	* public const IM_AFTER_FRAMESET, IM_AFTER_AFTER_BODY, IM_AFTER_AFTER_FRAMESET, IM_IN_SVG, IM_IN_MATHML 
	* __construct($isFragment = false, array $options = array()) 
	* get_document(): \DOMDocument 
	* get_fragment(): \DOMDocumentFragment 
	* setInstructionProcessor(InstructionProcessor $process):void 
	* doctype($name, $idType = 0, $id = null, $quirks = false):void 
	* startTag($name, $attributes = array(), $self_closing = false) 
	* endTag($name):void 
	* comment($cdata):void 
	* text($data):void 
	* eof():void 
	* parseError($msg, $line = 0, $col = 0):void 
	* getErrors():array 
	* cdata($data):void
	* processingInstruction($name, $data = null):void 
	* _normalizeTagName($tagName)
	* _quirksTreeResolver($name):void  
	* _auto_close($tagName):bool 
	* _is_ancestor($tagName):bool  
	* _is_parent($tagName):bool 

- Depedencies/Masterminds/HTML_5/Parser/EventHandler.php: Interface	
	* public const DOCTYPE_NONE, DOCTYPE_PUBLIC, DOCTYPE_SYSTEM 
	* doctype($name, $idType = 0, $id = null, $quirks = false); 
	* startTag($name, $attributes = array(), $selfClosing = false); 
	* endTag($name); 
	* comment($cdata); 
	* text($cdata); 
	* eof(); 
	* parseError($msg, $line, $col); 
	* cdata($data);
	* processingInstruction($name, $data = null);

- Depedencies/Masterminds/HTML_5/Parser/InputStream.php:  Interface 	
	* currentLine(); 
	* columnOffset(); 
	* remainingChars(); 
	* charsUntil($bytes, $max = null); 
	* charsWhile($bytes, $max = null); 
	* unconsume($howMany = 1);
	* peek(); 

- Depedencies/Masterminds/HTML_5/Parser/ParseError.php: extends \Exception	

- Depedencies/Masterminds/HTML_5/Parser/Scanner.php: 	
	* public const CHARS_HEX, CHARS_ALNUM, CHARS_ALPHA 
	* __construct($data, $encoding = 'UTF-8') 
	* sequenceMatches($sequence, $caseSensitive = true):bool 
	* position():int 
	* peek():bool 
	* next():bool  
	* current():bool 
	* consume($count = 1):void  
	* un_consume($howMany = 1):void  
	* getHex()
	* getAsciiAlpha()  
	* getAsciiAlphaNum() 
	* getNumeric() 
	* whitespace() 
	* currentLine():int 
	* charsUntil($mask) 
	* charsWhile($mask) 
	* columnOffset() 
	* remainingChars():string 
	* __replaceLineFeeds($data):string 
	* __doCharsUntil($bytes, $max = null) 
	* __doCharsWhile($bytes, $max = null) 

- Depedencies/Masterminds/HTML_5/Parser/Tokenizer.php: 	
	* public const CONFORMANT_XML, CONFORMANT_HTML 
	* __construct(Scanner $scanner,EventHandler $eventHandler, $mode = self::CONFORMANT_HTML) 
	* parse():bool 
	* setTextMode($text_mode, $until_tag = null):void 
	* _consumeData():bool 
	* _characterData() 
	* _text($tok):bool
	* _rawText($tok):bool
	* _rc_data($tok):bool  
	* _eof():void 
	* _markupDeclaration():bool
	* _endTag():bool  
	* _tagName():bool  
	* _isTagEnd(&$self_close):bool  
	* _attribute(&$attributes):bool 
	* _attributeValue()
	* _quotedAttributeValue($quote):string  
	* _unquotedAttributeValue():string 
	* _bogusComment($leading = ''):bool  
	* _comment():bool  
	* _isCommentEnd():bool 
	* _doctype():bool 
	* _quotedString($stop_chars) 
	* _cdataSection():bool 
	* _processingInstruction():bool 
	* _readUntilSequence($sequence):string 
	* _buffer($str):void 
	* _parseError($msg):bool 
	* _decodeCharacterReference($inAttribute = false) 

- Depedencies/Masterminds/HTML_5/Parser/TreeBuildingRules.php: 	
	* protected static $_tags 
	* hasRules($tag_name):bool 
	* evaluate($new, $current) 
	* handleLI($ele, $current) 
	* handleDT($ele, $current) 
	* handleRT($ele, $current)  
	* closeIfCurrentMatches($ele, \DOMDocument $current, $match) 

- Depedencies/Masterminds/HTML_5/Parser/UTF8Utils.php: 	
	* public const FFFD 
	* countChars($string) static  
	* convertToUTF8($data, $encoding = 'UTF-8') static  
	* checkForIllegalCodepoints($data):array static  

- Depedencies/Masterminds/HTML_5/Serializer/OutputRules.php: 	
	* public const IM_IN_HTML, IM_IN_SVG, IM_IN_MATHML, DOCTYPE 
	* addRule(array $rule):void 
	* setTraverser(Traverser $traverser):string 
	* unsetTraverser():string 
	* document(\DOMDocument $dom,Traverser $traverser):void 
	* doctype():void 
	* element(\DOMElement $ele,Traverser $traverser):void 
	* text(\DOMElement $ele):void 
	* cdata($ele):void 
	* comment($ele):void 
	* processorInstruction($ele):void 
	* namespaceAttrs($ele):void 
	* openTag(\DOMElement $ele,Traverser $traverser):void protected
	* attrs(\DOMElement $ele,Traverser $traverser) protected 
	* nonBooleanAttribute(\DOMAttr $attr):bool protected 
	* getXPath(\DOMNode $node): \DOMXPath protected 
	* closeTag(\DOMElement $ele,Traverser $traverser):void protected 
	* wr(string $text) 
	* nl()
	* enc($text, $attribute = false) 
	* escape($text, $attribute = false):string 

- Depedencies/Masterminds/HTML_5/Serializer/RulesInterface.php: Interface	
	* __construct($output, $options = array()) 
	* setTraverser(Traverser $traverser); 
	* document($dom); 
	* element($ele); 
	* text($ele); 
	* cdata($ele); 
	* comment($ele); 
	* processorInstruction($ele); 

- Depedencies/Masterminds/HTML_5/Serializer/Traverser.php: 	
	* __construct($dom, $out, RulesInterface $rules, $options = array()) 
	* walk() 
	* node($node):void 
	* children($nl):void 
	* isLocalElement($ele):bool 
	
- Depedencies/MicroFormats/_parser_1.php: trait	
	* private $__hh 
	* mf_parse($input, $url = null, $convertClassic = true):string 
	* fetch($url, $convert_classic = true, &$curl_info=null):string 
	* unicodeToHtmlEntities($input):string 
	* unicodeTrim($str):string 
	* classHasMf2RootClassname($class):bool 
	* mfNamesFromElement(\DOMElement $e, $prefix = 'h-'):array 
	* nestedMfPropertyNamesFromElement(\DOMElement $e):string 
	* convertTimeFormat($time):?string 
	* normalizeOrdinalDate($dtValue) 
	* normalizeTimezoneOffset(&$dtValue) 
	* applySrcsetUrlTransformation($srcset, $transformation): string 
	* __mfNamesFromClass($class, $prefix='h-'):array 
	* __nestedMfPropertyNamesFromClass($class):array 
	
- Depedencies/MicroFormats/_parser_2.php: trait	 	
	* __parseUriToComponents($uri):array 
	* __resolveUrl($baseURI, $reference_URI):string 
	* __mergePaths($base, $reference):string 
	* __removeLeadingDotSlash(&$input):void 
	* __removeLeadingSlashDot(&$input):void 
	* __removeLoneDotDot(&$input):void 
	* __moveOneSegmentFromInput(&$input, &$output):void 
	* __removeDotSegments($path):string 
	
- Depedencies/MicroFormats/Parse.php: 	
	* public $mf_baseurl, $mf_convert_classic, $mf_doc, $mf_enable_alternates
    * public $mf_json_mode, $mf_lang, $mf_xpath, $mf_classic_root_map,$mf_classic_property_map 
	* __construct($input, $url = null, $jsonMode = false)
	* textContent(\DOMElement $element, $implied=false) 
	* parseImg(\DOMElement $el) 
	* language(\DOMElement $el):string 
	* resolveUrl($url) 
	* parseValueClassTitle(\DOMElement $e, $separator = ''):?string 
	* parseP(\DOMElement $p) 
	* parseU(\DOMElement $u) 
	* parseDT(\DOMElement $dt, &$dates = array(), &$impliedTimezone = null) 
	* parseE(\DOMElement $e) 
	* parseH(\DOMElement $e, $is_backcompat = false, $has_nested_mf = false):array 
	* parseImpliedPhoto(\DOMElement $e) 
	* parseRelsAndAlternates():array 
	* upgradeRelTagToCategory(\DOMElement $el):void 
	* parse($convert_classic = true, \DOMElement $context = null):array 
	* parse_recursive(\DOMElement $context = null, $depth = 0):array 
	* parseFromId($id, $convert_classic=true):array 
	* getRootMF(\DOMElement $context = null): \DOMNodeList 
	* backcompat(\DOMElement $el, $context = '', $is_parent_mf2 = false):void 
	* addUpgraded(\DOMElement $el, $property):void 
	* addMfClasses(\DOMElement $el, $classes):void 
	* hasRootMf2(\DOMElement $el):bool 
	* convertLegacy():string 
	* query($expression, $context = null): \DOMNodeList 
	* __elementPrefixParsed(\DOMElement $e, $prefix):bool 
	* __isElementParsed(\DOMElement $e, $prefix):bool 
	* __isElementUpgraded(\DOMElement $el, $property):bool 
	* __resolveChildUrls(\DOMElement $el):void 
	* __elementToString(\DOMElement $input, $implied = false):string 
	* __removeTags(\DOMElement $e, $tagName):\DOMElement 
	* _removeTags(\DOMElement $e, $tagName):\DOMElement 

- Factory/_idna_vars.php: trait	
	* protected $_decoded, $_puny_code_prefix, $_invalid_ucs, $_max_ucs, $_base, $_tmin, $_tmax, $_skew 
	* protected $_damp, $_initial_bias, $_initial_n, $_sbase, $_lbase, $_vbase, $_tbase, $_lcount, $_vcount, $_tcount 
	* protected $_ncount, $_scount, $_error, $_set_last, $_api_encoding, $_allow_overlong, $_strict_mode, $_idn_version 
	* protected static $_idna_chars, $_mb_string_overload 

- Factory/_mm_vars.php: trait	
	* private $__char, $__data, $__default_options, $__eof 
	* protected $_xpath, $_carry_on, $_current, $_doc, $_dom, $_encode, $_errors, $_events, $_frag, $_has_html_5 
	* protected $_implicit_namespaces, $_insert_mode, $_mode, $_ns_roots, $_ns_stack, $_non_boolean_attributes 
	* protected $_only_inline, $_options, $_out, $_output_mode, $_parent_current, $_processor, $_pushes 
	* protected $_quirks, $_rules, $_scanner, $_stack, $_tok, $_text, $_text_mode, $_traverser, $_until_tag 
	* protected static $_local_ns 
	* public static $html5, $mathml, $svg, $svgCaseSensitiveAttributeMap. $svgCaseSensitiveElementMap 

- Factory/_object_for_cache.php: trait	
	* _prepare_simplepie_object_for_cache($data): array 

- Factory/_sp_chars.php: trait	
	* public static $chars_to_names, $names_to_chars 

- Factory/_sp_consts.php: trait	
	* simple_pie_hooks(): void 

- Factory/_sp_vars.php: trait	
	* private $__sp_add_attributes, $__sp_all_discovered_feeds, $__sp_autodiscovery, $__sp_autodiscovery_cache_duration, $__sp_built_in, $__sp_cache, $__sp_cache_duration 
	* private $__sp_cache_handlers, $__sp_cache_location, $__sp_cache_name_function, $__sp_check_modified, $__sp_compressed_data, $__sp_compressed_size, $__sp_config_settings 
	* private $__sp_consumed, $__sp_curl_options, $__sp_data, $__sp_data_length, $__sp_email, $__sp_enable_exceptions, $__sp_error, $__sp_feed, $__sp_feed_url, $__sp_file 
	* private $__sp_flags, $__sp_force_cache_fallback, $__sp_force_feed, $__sp_force_fsock_open, $__sp_image_handler, $__sp_input_encoding, $__sp_item_limit, $__sp_link 
	* private $__sp_max_checked_feeds, $__sp_min_compressed_size, $__sp_multi_feed_objects, $__sp_multi_feed_url, $__sp_name, $__sp_order_by_date, $__sp_permanent_url 
	* private $__sp_position, $__sp_raw_data, $__sp_sanitize, $__sp_state, $__sp_status_code, $__sp_strip_attributes, $__sp_strip_html_tags, $__sp_timeout, $__sp_user, $__sp_useragent 
	* protected $_sp_cache, $_sp_classes, $_sp_data, $_sp_data_length, $_sp_database_ids, $_sp_date, $_sp_default, $_sp_extension, $_sp_filename, $_sp_i_fragment,$_sp_i_host, $_sp_i_path 
	* protected $_sp_i_query, $_sp_i_user_info, $_sp_id, $_sp_legacy, $_sp_location, $_sp_mysql, $_sp_name, $_sp_normalization, $_sp_options, $_sp_port, $_sp_position, $_sp_prepare, 
	* protected $_sp_quote, $_sp_registry, $_sp_sanitize, $_sp_scheme, $_sp_state, $_sp_value 
	* public $sp_add_attributes, $sp_base, $sp_base_location, $sp_bitrate, $sp_body, $sp_cache, $sp_cache_class, $sp_cache_location, $sp_cache_name_function, $sp_cached_entities 
	* public $sp_captions, $sp_categories, $sp_channels, $sp_check_modified, $sp_checked_feeds, $sp_child, $sp_comment, $sp_copyright, $sp_credits, $sp_curl_options 
	* public $sp_current_byte, $sp_current_column, $sp_current_line, $sp_current_xhtml_construct, $sp_data, $sp_data_s, $sp_description, $sp_dom, $sp_dom_atts, $sp_dom_element 
	* public $sp_dom_name, $sp_elements, $sp_elsewhere, $sp_enable_cache, $sp_encode_instead_of_strip, $sp_encoding, $sp_error, $sp_error_code, $sp_error_string, $sp_extension 
	* public $sp_extra_field, $sp_extra_flags, $sp_duration, $sp_expression, $sp_file, $sp_file_class, $sp_file_class_args, $sp_filename, $sp_force_fsockopen, $sp_framerate 
	* public $sp_handler, $sp_hashes, $sp_headers, $sp_height, $sp_http_base, $sp_http_version, $sp_https_domains, $sp_image_handler, $sp_item, $sp_javascript, $sp_keywords 
	* public $sp_label, $sp_lang, $sp_length, $sp_link, $sp_local, $sp_location, $sp_max_checked_feeds, $sp_medium, $sp_method, $sp_micro_time, $sp_name, $sp_namespace 
	* public $sp_object, $sp_os, $sp_output_encoding, $sp_pcre, $sp_permanent_url, $sp_placeholder, $sp_player, $sp_position, $sp_ratings, $sp_reason, $sp_redirects, $sp_registry 
	* public $sp_relationship, $sp_remove_div, $sp_replace_url_attributes, $sp_restrictions, $sp_role, $sp_samplingrate, $sp_sanitize, $sp_scheme, $sp_separator, $sp_standalone 
	* public $sp_status_code, $sp_strip_attributes, $sp_strip_comments, $sp_strip_html_tags, $sp_sub_id1, $sp_sub_id2, $sp_success, $sp_term, $sp_text, $sp_thumbnails, $sp_time 
	* public $sp_timeout, $sp_title, $sp_type, $sp_url, $sp_useragent, $sp_val, $sp_value, $sp_version, $sp_width, $sp_xml_base, $sp_xml_base_explicit, $sp_xml_lang 

- SP_Components/_html_entities.php: trait	
	* protected $_entities 
	* protected $_windows_1252_specials 
	* prepare_headers($headers, $count = 1): string 

- SP_Components/_parse_date.php: trait	
	* protected $_sp_day,$_sp_month, $_sp_month_pcre, $_sp_timezone  

- SP_Components/_parse_headers.php: 	
	* prepareHeaders($headers, $count = 1) 

- SP_Components/SimplePie_Author.php: 	
	* __construct($name = null, $link = null, $email = null) 
	* __toString() 
	* get_name() 
	* get_link() 
	* get_email() 

- SP_Components/SimplePie_Cache.php: 	
	* protected static $_handlers 
	* get_handler($location, $filename, $extension): SP_Cache_File static 
	* register($type, $class):void static  
	* parse_URL($url) static  

- SP_Components/SimplePie_Caption.php: 	
	* __construct($type = null, $lang = null, $startTime = null, $endTime = null, $text = null) 
	* __toString() 
	* get_end_time() 
	* get_language() 
	* get_start_time() 
	* get_text() 
	* get_type() 

- SP_Components/SimplePie_Category.php: 	
	* __construct($term = null, $scheme = null, $label = null, $type = null) 
	* __toString() 
	* get_term() 
	* get_scheme() 
	* get_label($strict = false) 
	* get_type() 

- SP_Components/SimplePie_Content_Type_Sniffer.php: 	
	* protected $_sp_body, $_sp_file, $_sp_headers 
	* __construct($file) 
	* get_type() 
	* text_or_binary():string 
	* unknown():string 
	* image() 
	* feed_or_html():string 

- SP_Components/SimplePie_Copyright.php: 	
	* __construct($url = null, $label = null) 
	* __toString() 
	* get_url() 
	* get_attribution() 

- SP_Components/SimplePie_Credit.php: 	
	* __construct($role = null, $scheme = null, $name = null) 
	* __toString() 
	* get_role() 
	* get_scheme() 
	* get_name() 

- SP_Components/SimplePie_Decode_HTML_Entities.php: 	
	* __construct($data) 
	* parse():array 
	* __consume():bool 
	* __consume_range($chars) 
	* __un_consume():void 
	* __entity():void 

- SP_Components/SimplePie_Enclosure.php: 	
	* __construct($link = null, $type = null, $length = null, $javascript = null, $bitrate = null, $captions = null, $categories = null, $channels = null, $copyright = null, $credits = null, $description = null, $duration = null, $expression = null, $framerate = null, $hashes = null, $height = null, $keywords = null, $lang = null, $medium = null, $player = null, $ratings = null, $restrictions = null, $samplingrate = null, $thumbnails = null, $title = null, $width = null) 
	* __toString() 
	* get_bitrate() 
	* get_caption($key = 0) 
	* get_captions() 
	* get_category($key = 0) 
	* get_categories() 
	* get_channels() 
	* get_copyright() 
	* get_credit($key = 0) 
	* get_credits() 
	* get_description() 
	* get_duration($convert = false) 
	* get_expression():string 
	* get_extension() 
	* get_framerate() 
	* get_handler() 
	* get_hash($key = 0) 
	* get_hashes() 
	* get_height() 
	* get_language() 
	* get_keyword($key = 0) 
	* get_keywords() 
	* get_length() 
	* get_link() 
	* get_medium() 
	* get_player() 
	* get_rating($key = 0) 
	* get_ratings() 
	* get_restriction($key = 0) 
	* get_restrictions() 
	* get_sampling_rate() 
	* get_size() 
	* get_thumbnail($key = 0) 
	* get_thumbnails() 
	* get_title() 
	* get_type() 
	* get_width() 
	* native_embed(...$options):string 
	* embed($native = false, ...$options):string 

- SP_Components/SimplePie_Exception.php:  extends \Exception	

- SP_Components/SimplePie_File.php: 	
	* __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false, $curl_options = []) 
	* prepareHeaders($headers, $count = 1) 

- SP_Components/SimplePie_GZ_Decode.php: 	
	* public $M_TIME, $E_F, $O_S, $Sub_field_ID_1, $Sub_field_ID_2 
	* __set($name, $value) 
	* __construct($data) 
	* parse():bool 

- SP_Components/SimplePie_HTTP_Parser.php: 	
	* __construct($data) 
	* parse():bool 
	* _has_data():bool 
	* _is_linear_whitespace():bool 
	* _http_version():void 
	* _status():void 
	* _reason():void 
	* _new_line():void 
	* _name():void 
	* _linear_whitespace():void 
	* _value():void 
	* _value_char():void 
	* _quote():void 
	* _quote_char():void 
	* _quote_escaped():void 
	* _body():void 
	* _chunked():void 

- SP_Components/SimplePie_IRI.php: 	
	* __toString() 
	* __set($name, $value) 
	* __get($name) 
	* __isset($name) 
	* __unset($name) 
	* __construct($iri = null) 
	* __destruct() 
	* parse_iri($iri):array 
	* _remove_dot_segments($input):string 
	* remove_dot_segments($input):string 
	* _replace_invalid_with_pct_encoding($string, $extra_chars, $i_private = false):string 
	* _remove_i_unreserved_percent_encoded($match):string 
	* scheme_normalization():void 
	* is_valid():bool 
	* set_iri($iri, $clear_cache = false):bool 
	* set_scheme($scheme):bool 
	* set_authority($authority, $clear_cache = false):bool 
	* set_user_info($i_user_info):bool 
	* set_host($i_host):bool 
	* set_port($port):bool 
	* set_path($i_path, $clear_cache = false):bool 
	* set_query($i_query):bool 
	* set_fragment($i_fragment):bool 
	* to_uri($string):string 
	* get_iri():string 
	* get_uri():string 
	* _get_i_authority():string 
	* _get_authority():string 

- SP_Components/SimplePie_Item.php: 	
	* __construct($feed, $data) 
	* set_registry(SimplePie_Registry $registry):void 
	* __toString() 
	* __destruct() 
	* get_item_tags($namespace, $tag) 
	* get_base($element = array()) 
	* sanitize($data, $type, $base = '') 
	* get_feed() 
	* get_id($hash = false, $fn = 'md5') 
	* get_title() 
	* get_description($description_only = false) 
	* get_content($content_only = false) 
	* get_thumbnail() 
	* get_category($key = 0) 
	* get_categories() 
	* get_author($key = 0) 
	* get_contributor($key = 0) 
	* get_contributors() 
	* get_authors() 
	* get_copyright() 
	* get_date($date_format = 'j F Y, g:i a') 
	* get_updated_date($date_format = 'j F Y, g:i a') 
	* get_local_date($date_format = '%c') 
	* get_gmdate($date_format = 'j F Y, g:i a') 
	* get_updated_gmdate($date_format = 'j F Y, g:i a') 
	* get_permalink() 
	* get_link($key = 0, $rel = 'alternate') 
	* get_links($rel = 'alternate') 
	* get_enclosure($key = 0, $prefer = null) 
	* get_enclosures() 
	* get_latitude():?float 
	* get_longitude():?float 
	* get_source() 

- SP_Components/SimplePie_Locator.php: 	
	* __construct(SimplePie_File $file, $timeout = 10, $useragent = null, $max_checked_feeds = 10, $force_fsockopen = false, $curl_options = []) 
	* set_registry(SimplePie_Registry $registry):void 
	* find($type = SP_LOCATOR_ALL, &$working = null) 
	* is_feed($file, $check_html = false):bool 
	* get_base():void 
	* autodiscovery() 
	* _search_elements_by_tag($name, &$done, $feeds) 
	* get_links():bool 
	* get_rel_link($rel) 
	* extension(&$array):?array 
	* body(&$array):?array 

- SP_Components/SimplePie_Net_IPv6.php: 	
	* private $__c1,$__c2 
	* un_compress($ip) 
	* compress($ip):string 
	* __split_v6_v4($ip):array 
	* check_ipv6($ip):bool 

- SP_Components/SimplePie_Parse_Date.php: 	
	* __construct() 
	* parse($date):int 
	* add_callback($callback):void 
	* date_w3c_dtf($date):int 
	* _remove_rfc2822_comments($string):string 
	* _date_rfc2822($date):int 
	* _date_rfc850($date):int 
	* _date_asc_time($date):int 
	* date_str_to_time($date):int 

- SP_Components/SimplePie_Parser.php: 	
	* __construct() 
	* set_registry(SimplePie_Registry $registry):void 
	* parse(&$data, $encoding, $url = ''):bool 
	* get_error_code() 
	* get_error_string() 
	* get_current_line() 
	* get_current_column() 
	* get_current_byte() 
	* get_data():array 
	* tag_open($tag, $attributes):void 
	* cdata($cdata):void 
	* tag_close($tag):void 
	* split_ns($string) 
	* __parse_h_card($data, $category = false):string 
	* __parse_micro_formats(&$data, $url):bool 
	* __declare_html_entities():string 

- SP_Components/SimplePie_Rating.php: 	
	* __construct($scheme = null, $value = null) 
	* __toString() 
	* get_scheme():?string 
	* get_value():?string 

- SP_Components/SimplePie_Registry.php: 	
	* __construct() 
	* register($type, $class, $legacy = false):bool 
	* get_class($type) 
	* &create($type, $parameters = array()) 
	* &call($type, $method, $parameters = []) 

- SP_Components/SimplePie_Restriction.php: 	
	* __construct($relationship = null, $type = null, $value = null) 
	* __toString() 
	* get_relationship():?string 
	* get_type():?string 
	* get_value():?string 

- SP_Components/SimplePie_Sanitize.php: 	
	* __construct() 
	* remove_div($enable = true):void 
	* set_image_handler($page = false):void 
	* set_registry(SimplePie_Registry $registry):void 
	* pass_cache_data($enable_cache = true, $cache_location = './cache', $cache_name_function = 'md5', $cache_class = '_SimplePie_Cache'):void 
	* pass_file_data($file_class = '_SimplePie_File', $timeout = 10, $useragent = '', $force_fsockopen = false, ...$file_args):void 
	* strip_html_tags($tags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style')):void 
	* encode_instead_of_strip($encode = false):void 
	* strip_attributes($atts = array('bgsound', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc')):void 
	* add_attributes($atts = array('audio' => array('preload' => 'none'), 'iframe' => array('sandbox' => 'allow-scripts allow-same-origin'), 'video' => array('preload' => 'none'))):void 
	* strip_comments($strip = false):void 
	* set_output_encoding($encoding = 'UTF-8'):void 
	* set_url_replacements($element_attribute = null):void 
	* set_https_domains($domains):void 
	* _is_https_domain($domain):bool 
	* https_url($url) 
	* sanitize($data, $type, $base = '') 
	* _pre_process($html):string 
	* replace_urls($document, $tag, $attributes):void 
	* do_strip_html_tags($match):?string 
	* _strip_tag($tag, $document, $xpath, $type):void 
	* _strip_attr($att, $xpath):void 
	* _add_attr($tag, $valuePairs, $document):void 

- SP_Components/SimplePie_Source.php: 	
	* __construct($item, $data) 
	* set_registry(SimplePie_Registry $registry):void 
	* __toString() 
	* get_source_tags($namespace, $tag) 
	* get_base($element = []) 
	* sanitize($data, $type, $base = '') 
	* get_item() 
	* get_title() 
	* get_category($key = 0) 
	* get_categories() 
	* get_author($key = 0) 
	* get_authors() 
	* get_contributor($key = 0) 
	* get_contributors() 
	* get_link($key = 0, $rel = 'alternate') 
	* get_permalink() 
	* get_links($rel = 'alternate') 
	* get_description() 
	* get_copyright() 
	* get_language() 
	* get_latitude():?float 
	* get_longitude():?float 
	* get_image_url() 

- SP_Components/SimplePie_XML_Declaration_Parser.php: 	
	* __construct($data) 
	* parse():bool 
	* has_data():bool 
	* skip_whitespace():int 
	* get_value() 
	* before_version_name():void 
	* version_name():void 
	* version_equals():void 
	* version_value():void 
	* encoding_name():void 
	* encoding_equals():void 
	* encoding_value():void 
	* standalone_name():void 
	* standalone_equals():void 
	* standalone_value():void 

- SP_Components/TP_SimplePie_File.php: 	
	* __construct( $url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false ) 

- SP_Components/TP_SimplePie_Sanitize_KSES.php: 	
	* sanitize( $data, $type, $base = '' ) 

- SP_Components/Cache/SP_Cache_Base.php: interface	
	* public const TYPE_FEED, TYPE_IMAGE 
	* __construct($location, $name, $type); 
	* save($data); 
	* load(); 
	* micro_time(); 
	* touch(); 
	* unlink(); 

- SP_Components/Cache/SP_Cache_DB.php: 	abstract,  implements SP_Cache_Base
	* prepare_sp_object_for_cache($data):array 

- SP_Components/Cache/SP_Cache_File.php: implements SP_Cache_Base 	
	* __construct($location, $name, $type) 
	* save($data):bool 
	* load() 
	* micro_time():int 
	* touch():bool 
	* unlink():bool 

- SP_Components/Cache/SP_Cache_Memcache.php: implements SP_Cache_Base 	
	* __construct($location, $name, $type) 
	* save($data):bool 
	* load() 
	* micro_time():int 
	* touch():bool 
	* unlink():bool 

- SP_Components/Cache/SP_Cache_Memcached.php: implements SP_Cache_Base 	
	* __construct($location, $name, $type) 
	* save($data):bool 
	* load() 
	* micro_time():int 
	* touch():bool 
	* unlink():bool 
	* __set_data($data) 

- SP_Components/Cache/SP_Cache_MySQL.php: implements SP_Cache_Base 	
	* __construct($location, $name, $type) 
	* save($data):bool 
	* load() 
	* micro_time():int 
	* touch():bool 
	* unlink():bool 

- SP_Components/Cache/SP_Cache_Redis.php.dist: 	