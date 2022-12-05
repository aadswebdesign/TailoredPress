### TP_Admin/Traits/AdminImage

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_image_01.php: 	
	* _tp_crop_image($src, $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h, $src_abs = false, $dst_file = false ) 
	* _tp_get_missing_image_subsizes( $attachment_id ):array 
	* _tp_update_image_subsizes( $attachment_id ) 
	* _tp_image_meta_replace_original( $saved_data, $original_file, $image_meta, $attachment_id ) 
	* _tp_create_image_subsizes( $file, $attachment_id ) 
	* _tp_make_subsizes( $new_sizes, $file, $image_meta, $attachment_id ):array 
	* _tp_generate_attachment_metadata( $attachment_id, $file ) 
	* _tp_exif_frac2dec( $str ) 
	* _tp_exif_date2ts( $str ) 
	* _tp_read_image_metadata( $file ):bool 

- _adm_image_02.php: 	
	* _file_is_valid_image( $path ):bool 
	* _file_is_displayable_image( $path ) 
	* _load_image_to_edit( $attachment_id, $mime_type, $size = 'full' ) 
	* _load_image_to_edit_path( $attachment_id, $size = 'full' ) 
	* _copy_image_file( $attachment_id ) 

- _adm_image_edit.php: 	
	* _tp_get_image_editor( $post_id, $msg = false ):string 
	* _tp_image_editor( $post_id, $msg = false ):void 
	* _tp_stream_image( $image, $mime_type, $attachment_id ):bool 
	* _tp_save_image_file( $filename, $image, $mime_type, $post_id ) 
	* __image_get_preview_ratio( $w, $h ) 
	* __crop_image_resource( $img, $x, $y, $w, $h ) 
	* _image_edit_apply_changes( $image, $changes ) 
	* _stream_preview_image( $post_id ):bool 
	* _tp_restore_image( $post_id ):\stdClass 
	* _tp_save_image( $post_id ):\stdClass 
