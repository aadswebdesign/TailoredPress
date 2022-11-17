### TP_Core/Traits/Media

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _media_01.php: 	
	* _tp_get_additional_image_sizes():array 
	* _image_constrain_size_for_editor( $width, $height, $size = 'medium', $context = null ):string 
	* _image_hwstring( $width, $height ):string 
	* _image_downsize( $id, $size = 'medium' ) -> mixed 
	* _add_image_size( $name, $width = 0, $height = 0, $crop = false ):bool 
	* _has_image_size( $name ):bool 
	* _remove_image_size( $name ):bool 
	* _set_post_thumbnail_size( $width = 0, $height = 0, $crop = false ):bool 
	* _get_image_tag( $id, $alt, $title, $align, $size = 'medium' , $loading_lazy = false) -> mixed 
	* _tp_constrain_dimensions( $current_width, $current_height, $max_width = 0, $max_height = 0 ):array 

- _media_02.php: 	
	* _image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $crop = false ) -> mixed 
	* _image_make_intermediate_size( $file, $width, $height, $crop = false ) -> mixed 
	* _tp_image_matches_ratio( $source_width, $source_height, $target_width, $target_height ):bool 
	* _image_get_intermediate_size( $post_id, $size = 'thumbnail' ):bool 
	* _get_intermediate_image_sizes() -> mixed 
	* _tp_get_registered_image_sub_sizes():array 
	* _tp_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ) -> mixed 
	* _tp_get_attachment_image($attachment_id, $size='thumbnail',$icon = false, ...$attr) -> mixed 
	* _tp_get_attachment_image_url( $attachment_id, $size='thumbnail',$icon = false ):bool 
	* _tp_get_attachment_relative_path( $file ):string 

- _media_03.php: 	
	* _tp_get_image_size_from_meta( $size_name, $image_meta ) -> mixed  
	* _tp_get_attachment_image_srcset( $attachment_id, $size = 'medium', $image_meta = null ) -> mixed  
	* _tp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id = 0 ) -> mixed  
	* _tp_get_attachment_image_sizes( $attachment_id, $size = 'medium', $image_meta = null ) -> mixed  
	* _tp_calculate_image_sizes( $size, $image_src = null, $image_meta = null, $attachment_id = 0 ):bool 
	* _tp_image_file_matches_image_meta( $image_location, $image_meta, $attachment_id = 0 ) -> mixed  
	* _tp_image_src_get_dimensions( $image_src, $image_meta, $attachment_id = 0 ) -> mixed  
	* _tp_image_add_srcset_and_sizes( $image, $image_meta, $attachment_id ) -> mixed  
	* _tp_lazy_loading_enabled( $tag_name, $context ):bool 
	* _tp_filter_content_tags( $content, $context = null ) -> mixed  
- _media_04.php: 	
	* _tp_img_tag_add_loading_attr( $image, $context ) -> mixed 
	* _tp_img_tag_add_width_and_height_attr( $image, $context, $attachment_id ) -> mixed 
	* _tp_img_tag_add_srcset_and_sizes_attr( $image, $context, $attachment_id ) -> mixed 
	* _tp_iframe_tag_add_loading_attr( $iframe, $context ) -> mixed 
	* _tp_post_thumbnail_class_filter( $attr ) -> mixed 
	* _tp_post_thumbnail_class_filter_add( $attr ):void  
	* _tp_post_thumbnail_class_filter_remove( $attr ):void 
	* _get_img_caption_shortcode( $attr, $content = '' ):string 
	* _img_caption_shortcode( $attr, $content = '' ):string 
	* _get_gallery_shortcode( $attr ):string 
	* gallery_shortcode( $attr ):string 
	* _get_underscore_playlist_templates():string 
	* tp_underscore_playlist_templates():void 

- _media_05.php: 	
	* tp_playlist_scripts():void 
	* _get_playlist_shortcode( $attr ):string 
	* tp_playlist_shortcode( $attr ):string 
	* _tp_media_element_fallback( $url ) -> mixed 
	* _tp_get_audio_extensions() -> mixed 
	* _tp_get_attachment_id_3_keys( $attachment, $context = 'display' ) -> mixed 
	* _get_audio_shortcode( $attr, $content = '' ) -> mixed 
	* tp_audio_shortcode( $attr, $content = '' ) -> mixed 
	* _tp_get_video_extensions() -> mixed 
	* _get_video_shortcode( $attr, $content = '' ) -> mixed 
	* tp_video_shortcode( $attr, $content = '' ) -> mixed 

- _media_06.php: 	
	* _get_next_image_link( $size = 'thumbnail', $text = false ):string 
	* next_image_link( $size = 'thumbnail', $text = false ):void 
	* _get_adjacent_image_link( $prev = true, $size = 'thumbnail', $text = false ) -> mixed 
	* adjacent_image_link( $prev = true, $size = 'thumbnail', $text = false ):void 
	* _get_attachment_taxonomies( $attachment, $output = 'names' ):array 
	* _get_taxonomies_for_attachments( $output = 'names' ):array 
	* _is_gd_image( $image ):bool 
	* _tp_image_create_true_color( $width, $height ) -> mixed 
	* _tp_expand_dimensions( $example_width, $example_height, $max_width, $max_height ) -> mixed 
	* _tp_max_upload_size() -> mixed 

- _media_07.php: 	
	* _tp_get_image_editor( $path, $args = [] ) -> mixed 
	* _tp_image_editor_supports( $args = [] ) -> mixed 
	* _tp_image_editor_choose( $args = [] ) -> mixed 
	* _tp_plupload_default_settings():void 
	* _tp_prepare_attachment_for_js( $attachment ):bool  
	* _tp_enqueue_media(array ...$args):bool 
	* _get_attached_media( $type,object $post):array  
	* _get_media_embedded_in_content( $content, $types = null ):array  
	* _get_post_galleries( $post, $html = true ):array  
	*  _get_post_gallery( $post = 0, $html = true ) -> mixed 

- _media_08.php: 	
	* _get_post_galleries_images( $post = 0 ) -> mixed 
	* _get_post_gallery_images( $post = 0 ):array  
	* _tp_maybe_generate_attachment_metadata( $attachment ):void  
	* _attachment_url_to_post_id( $url ):int  
	* _tp_view_media_sandbox_styles():array  
	* _tp_register_media_personal_data_exporter( $exporters ) -> mixed  
	* _get_media_personal_data_exporter( $email_address, $page = 1 ):array  
	* tp_media_personal_data_exporter( $email_address, $page = 1 ):array  
	* tp_add_additional_image_sizes():void 
	* _tp_show_heic_upload_error( $upload_settings ) -> mixed  

- _media_09.php: 	
	* _tp_get_webp_info( $filename ):array 
	* _tp_get_loading_attr_default( $context ) -> mixed  
	* _tp_omit_loading_attr_threshold( $force = false ) -> mixed  
	* _tp_increase_content_media_count( $amount = 1 ):int  
	* _img_hooks():void  
