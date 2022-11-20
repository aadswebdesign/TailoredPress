### TP_Core/Libs/Editor

**Note:** For what it is now and subject to change. 

**TODO:** Giving this package an overhaul 'prefix methods and more'

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- Editor_base.php: 	
	* public static $mce_locale; 
	* protected static $_mce_settings,$_qt_settings,$_plugins,$_qt_buttons,$_ext_plugins,$_baseurl,$_html
	* protected static $_first_init, $_this_tinymce, $_this_quicktags, $_has_tinymce, $_has_quicktags, $_has_medialib 
	* protected static $_editor_buttons_css, $_drag_drop_upload, $_editor_translation, $_tinymce_scripts_printed, $_link_dialog_printed 
	* protected $_tinymce_version, $_file, $_size, $_mime_type, $_output_mime_type, $_default_mime_type, $_quality

- TP_Editor.php: extends Editor_base	
	* __construct() 
	* parse_settings( $editor_id, $settings ):array 
	* get_editor( $content, $editor_id,array ...$settings):string 
	* editor( $content, $editor_id,array ...$settings):void 
	* get_editor_settings( $editor_id, $set ):string //todo 
	* editor_settings( $editor_id, $set ):void 
	* __parse_init( $init ) 
	* enqueue_scripts( $default_scripts = false ) 
	* enqueue_default_editor() 
	* print_default_editor_scripts() 
	* get_mce_locale() 
	* get_baseurl() 
	* __default_settings() 
	* __get_translation() 
	* force_uncompressed_tinymce() 
	* get_editor_js():string 
	* tp_link_query( $args = []) 
	* tp_get_link_dialog():string 
	* tp_link_dialog():void 

- TP_Image_Editor.php: extends Editor_base	
	* protected $_default_quality 
	* __construct( $file ) 
	* test( ... $args)  
	* supports_mime_type( $mime_type ) 
	* abstract load()
	* abstract save( $destfilename = null, $mime_type = null )
	* abstract resize( $max_w, $max_h, $crop = false )
	* abstract multi_resize( $sizes )
	* abstract crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false )
	* abstract rotate( $angle )
	* abstract flip( $horz, $vert )
	* abstract stream( $mime_type = null )
	* get_size() 
	* _update_size( $width = null, $height = null ):int 
	* get_quality(): bool 
	* set_quality( $quality = null ) 
	* _get_default_quality( $mime_type ): int 
	* _get_output_format( $filename = null, $mime_type = null ): array 
	* generate_filename( $suffix = null, $dest_path = null, $extension = null ): string 
	* get_suffix() 
	* maybe_exif_rotate() 
	* _make_image( $filename, $function, $arguments ) 
	* _get_mime_type( $extension = null ) 
	* _get_extension( $mime_type = null ) 

- TP_Image_Editor_GD.php: extends TP_Image_Editor	
	* protected $_image 
	* __destruct() 
	* test( ...$args) 
	* supports_mime_type( $mime_type ) 
	* load() 
	* _update_size( $width = null, $height = null ):int 
	* resize( $max_w, $max_h, $crop = false ) 
	* _resize( $max_w, $max_h, $crop = false ) 
	* multi_resize( $sizes ):array 
	* make_subsize( $size_data ) 
	* crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) 
	* rotate( $angle ) 
	* flip( $horz, $vert ) 
	* save( $destfilename = null, $mime_type = null ) 
	* _save( $image, $filename = null, $mime_type = null ) 
	* stream( $mime_type = null) 
	* _make_image( $filename, $function, $arguments ) 

- TP_Image_Editor_Imagick.php: extends TP_Image_Editor	
	* protected $_image 
	* __destruct() 
	* test(...$args) 
	* supports_mime_type( $mime_type ) 
	* load() 
	* set_quality( $quality = null ) 
	* _update_size( $width = null, $height = null ):int 
	* resize( $max_w, $max_h, $crop = false ) 
	* _thumbnail_image( $dst_w, $dst_h, $filter_name = 'FILTER_TRIANGLE', $strip_meta = true ) 
	* multi_resize( $sizes ):string 
	* make_subsize( $size_data ) 
	* crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) 
	* rotate( $angle ) 
	* flip( $horz, $vert ) 
	* maybe_exif_rotate() 
	* save( $destfilename = null, $mime_type = null ) 
	* _save( $image, $filename = null, $mime_type = null ) 
	* __write_image(\Imagick $image, $filename ) 
	* stream( $mime_type = null ) 
	* _strip_meta() 
	* _pdf_setup() 
	* _pdf_load_source() 
