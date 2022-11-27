### TP_Admin/Traits/AdminMedia

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_media_01.php: 	
	* _media_upload_tabs() 
	* _update_gallery_tab( $tabs ) 
	* _get_media_upload_tabs():string 
	* _get_image_send_to_editor( $id, $caption, $title, $align, $url = '', $rel = false, $size = 'medium', $alt = '' ):string 
	* _image_add_caption( $html, $id, $caption, $align, $url, $size, $alt = '' ):string 
	* _cleanup_image_add_caption( $matches ) 
	* _get_media_send_to_editor( $html ):string 
	* _media_handle_upload( $file_id, $post_id, $overrides = ['test_form' => false],...$post_data):string 
	* _media_handle_sideload( $file_array, $post_id = 0, $desc = null,array $post_data):string 
	* _tp_get_iframe( $content_func, ...$args ):string 

- _adm_media_02.php: 	
	* _get_media_buttons( $editor_id = 'content' ):string 
	* _get_upload_iframe_src( $type = null, $post_id = null, $tab = null ) 
	* _media_upload_form_handler():?string 
	* _tp_media_upload_handler():?string 
	* _media_sideload_image( $file, $post_id = 0, $desc = null, $return = 'html' ) 
	* _media_upload_gallery():?string 
	* _media_upload_library():?string 
	* _image_align_input_fields( $post, $checked = '' ):string 
	* _image_size_input_fields( $post, $check = '' ):array 
	* _image_link_input_fields( $post, $url_type = '' ):string 

- _adm_media_03.php: 	
	* _tp_caption_input_textarea($edit_post):string 
	* _image_attachment_fields_to_edit($form_fields, $post) 
	* _media_single_attachment_fields_to_edit($form_fields, $post) 
	* _media_post_single_attachment_fields_to_edit($form_fields, $post) 
	* _image_attachment_fields_to_save($post, $attachment) 
	* _image_media_send_to_editor($html, $attachment_id, $attachment) 
	* _get_attachment_fields_to_edit($post, $errors = null):array 
	* _get_media_items($post_id, $errors):string 
	* _get_media_item($attachment_id, $args = null):string 
	* _get_compat_media_markup($attachment_id, $args = null):array 

- _adm_media_04.php: 	
	* _get_media_upload_header():string 
	* _get_media_upload_form( $errors = null ):string 
	* _get_media_upload_type_form( $type = 'file', $errors = null, $id = null ):string 
	* _get_media_upload_type_url_form( $type = null, $errors = null, $id = null ):string 
	* _get_media_upload_gallery_form( $errors ):string 
	* _get_media_upload_library_form( $errors ):string 
	* _tp_get_media_insert_url_form( $default_view = 'image' ):string 
	* _get_media_upload_flash_bypass():string 
	* _get_media_upload_html_bypass():string 
	* _get_media_upload_max_image_resize():string 

- _adm_media_05.php: 	
	* _get_multisite_over_quota_message():string 
	* _get_edit_form_image_editor( $post ):string 
	* _get_attachment_submitbox_metadata():string 
	* _tp_add_id3_tag_data( &$metadata, $data ):void 
	* _tp_read_video_metadata( $file ):string 
	* _tp_read_audio_metadata( $file ):string 
	* _tp_get_media_creation_timestamp( $metadata ):bool 
	* _tp_media_attach_action( $parent_id, $action = 'attach' ):void 
