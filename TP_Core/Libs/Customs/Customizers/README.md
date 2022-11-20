### TP_Core/Libs/Customs/Customizers

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- _date_time_control.php: 	
	* todo 
	*  
- _nav_menu_auto_add_control.php: 	
	* todo 
	*  
- _nav_menu_control.php: 	
	* todo 
	*  
- _nav_menu_item_control.php: 	
	* todo 
	*  
- _nav_menu_item_setting.php: 	
	* todo 
	*  
- _nav_menu_location_control.php: 	
	* todo 
	*  
- _nav_menu_locations_control.php: 	
	* todo 
	*  
- _nav_menu_name_control.php: 	
	* todo 
	*  
- _nav_menu_section.php: 	
	* todo 
	*  
- _nav_menu_setting.php: 	
	* todo 
	*  
- _nav_menus_panel.php: 	
	* todo 
	*  
- _new_menu_control.php: 	
	* todo 
	*  
- _new_menu_section.php: 	
	* todo 
	*  
- _sidebar_block_editor_control.php: 	
	* todo 
	*  
- _sidebar_section.php: 	
	* todo 
	*  
- _theme_control.php: 	
	* todo 
	*  
- TP_Customize_Background_Image_Control.php: extends TP_Customize_Image_Control	
	* public $type 
	* __construct( $manager ) 
	* enqueue():void{}//44 
- TP_Customize_Background_Image_Setting.php: 	
	* public $id 
	* update():void 
- TP_Customize_Background_Position_Control.php: 	
	* public $type 
	* render_content():void 
	* get_content_template():string //todo
- TP_Customize_Code_Editor_Control.php: 	
	* public $type, $code_type , $editor_settings 
	* enqueue():void //todo rename to avoid collisions 
	* json():array //todo rename to avoid collisions  
	* get_render_content():void 
	* _get_content_template():string //todo
- TP_Customize_Color_Control.php: 	
	* public $type, $statuses, $mode  
	* __construct( $manager, $id,array ...$args) 
	* enqueue():void //todo rename to avoid collisions  
	* to_json():void //todo
	* render_content():void //todo 
	* _get_content_template():string //todo 
- TP_Customize_Cropped_Image_Control.php: extends TP_Customize_Image_Control	
	* public $type, $width, $height, $flex_width, $flex_height
	* enqueue():void //todo rename to avoid collisions  
	* to_json():void //todo
- TP_Customize_Custom_CSS_Setting.php: 	
	* public $type, $transport, $capability, $stylesheet; 
	* __construct( $manager, $id, $args = array() ) 
	* preview():bool 
	* filter_previewed_tp_get_custom_css( $css, $stylesheet ) 
	* value() 
	* validate( $value ) 
	* update( $value ) 
- TP_Customize_Filter_Setting.php: 	
	* update( $value ):void {} 
	*  
- TP_Customize_Header_Image_Control.php: extends TP_Customize_Image_Control	
	* public $type, $uploaded_headers, $default_headers; 
	* __construct( $manager ) 
	* enqueue():void 
	* print_header_image_template():void 
	* _get_render_content():string 
	* render_content():void{} 
- TP_Customize_Header_Image_Setting.php: 	
	* public $id 
	* update( $value ):void  
	*  
- TP_Customize_Image_Control.php: 	
	* public $type, $mime_type 
	* prepare_control():void {}//38 
	*  
	*  
	*  
- TP_Customize_Media_Control.php: 	
	* public $type, $mime_type, $button_labels; 
	* __construct( $manager, $id, $args = array() 
	* enqueue():void 
	* to_json():void 
	* render_content():void 
	* _get_content_template():string 
	* get_default_button_labels():string 
- TP_Customize_Partial.php: 	
	* protected $_id_data 
	* public $component, $id, $type, $selector, $settings, $primary_setting 
	* public $capability, $render_callback,$container_inclusive,$fallback_refresh 
	* __construct(){} 
	* id_data():array 
	* render(array ...$container_context):array 
	* is_render_callback():bool 
	* json():void 
	* check_capabilities():void 
- TP_Customize_Selective_Refresh.php: 	
	* protected $_partials, $_triggered_errors, $_current_partial_id;  
	* public const RENDER_QUERY_VAR 
	* public $manager 
	* __construct(TP_Customize_Manager $manager) 
	* partials():array 
	* add_partial( $id,array ...$args):TP_Customize_Partial 
	* get_partial( $id ) 
	* remove_partial( $id ):void 
	* init_preview():void 
	* enqueue_preview_scripts():void 
	* get_export_preview_data():string 
	* export_preview_data():void 
	* add_dynamic_partials( $partial_ids ):string 
	* handle_error( $err_no, $err_str, $err_file = null, $err_line = null ):bool 
- TP_Customize_Site_Icon_Control.php: extends TP_Customize_Cropped_Image_Control	
	* public $type 
	* __construct( $manager, $id,array ...$args) 
	* _get_content_template():string 
	*  
- TP_Customize_Themes_Panel.php: extends TP_Customize_Panel	
	* public $type 
	* _get_render_template():string 
	* _get_content_template():string 
- TP_Customize_Themes_Section.php: extends TP_Customize_Section	
	* public $type, $action, $filter_type 
	* json():void 
	* _get_render_template():string 
	* _get_filter_bar_content_template():string 
	* _get_filter_drawer_content_template():string 
- TP_Customize_Upload_Control.php: 	
	* public $type, $mime_type, $button_labels; 
	* to_json():void 