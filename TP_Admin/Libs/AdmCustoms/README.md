### TP_Admin/Libs/AdmCustoms

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 
- TP_Custom_Background.php: //todo
	* private $__updated 
	* public $admin_header_callback, $admin_image_div_callback 
	* __construct( $admin_header_callback = '', $admin_image_div_callback = '' ) 
	* init(){} 
	* admin_load(){} 
	* take_action(){} 
	* admin_page(){} 
	* async_background_add(){}  
	* attachment_fields_to_edit( $form_fields ) 
	* filter_upload_tabs( $tabs )  
	* tp_set_background_image(){} 

- TP_Custom_Image_Header.php: //todo	
	* private $__updated 
	* public $admin_header_callback, $admin_image_div_callback 
	* __construct( $admin_header_callback = '', $admin_image_div_callback = '' )  
	* init(){} //todo
	* help(){} //todo 
	* step(){return '';}
	* js_includes(){}
	* css_includes(){}
	* take_action(){return '';} 
	* process_default_headers(){} 
	* show_header_selector( $type = 'default' ){}
	* js(){} 
	* js_1(){}
	* step_1(){}
	* step_2(){} 
	* step_2_manage_upload(){return '';} 
	* step_3(){return '';} 
	* finished() 
	* admin_page(){} 
	* attachment_fields_to_edit( $form_fields ){} 
	* filter_upload_tabs( $tabs ) 
	* set_header_image( $choice ){} 
	* remove_header_image() 
	* reset_header_image(){} 
	* get_header_dimensions( $dimensions ){return '';} 
	* create_attachment_object( $cropped, $parent_attachment_id ){return '';} 
	* insert_attachment( $object, $cropped ){return '';} 
	* async_header_crop(){} 
	* async_header_add(){} 
	* async_header_remove(){} 
	* customize_set_last_used( $tp_customize ){} 
	* get_default_header_images(){return '';} 
	* get_uploaded_header_images(){return '';} 
	* get_previous_crop( $object ){return '';} 
