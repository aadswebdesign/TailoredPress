### TP_Admin/Traits/AdminPost

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_post_01.php: 	
	* _tp_translate_postdata( $update = false, $post_data = null ):?TP_Error 
	* _tp_get_allowed_postdata( $post_data = null ):array 
	* _edit_post( $post_data = null ):int 
	* _bulk_edit_posts( $post_data = null ):array 
	* _get_default_post_to_edit( $post_type = 'post', $create_in_db = false ) 
	* _post_exists( $title, $content = '', $date = '', $type = '', $status = '' ):int 
	* _tp_write_post() 
	* _write_post() 
	* _add_meta( $post_ID ):bool 
	* _delete_meta( $mid ) 

- _adm_post_02.php: 	
	* _get_meta_keys():array 
	* _get_post_meta_by_id( $mid ) 
	* _has_meta( $postid ):array 
	* _update_meta( $meta_id, $meta_key, $meta_value ) 
	* _fix_attachment_links( $post ) 
	* _get_available_post_statuses( $type = 'post' ):array 
	* _tp_edit_posts_query( $q = false ):array 
	* _tp_edit_attachments_query_vars( $q = false ):bool 
	* _tp_edit_attachments_query( $q = false ):array 
	* _postbox_classes( $box_id, $screen_id ):string 

- _adm_post_03.php: 	
	* _get_sample_permalink( $id, $title = null, $name = null ):array 
	* _get_sample_permalink_html( $id, $new_title = null, $new_slug = null ):string 
	* _tp_post_thumbnail_html( $thumbnail_id = null, $post = null ) 
	* _tp_check_post_lock( $post_id ):bool 
	* _tp_set_post_lock( $post_id ) 
	* _get_admin_notice_post_locked() 
	* _tp_create_post_autosave( $post_data ):int 
	* _post_preview() 
	* _tp_autosave( $post_data ) 
	* _redirect_post( $post_id = '' ):void 

- _adm_post_04.php: 	
	* _taxonomy_meta_box_sanitize_cb_checkboxes( $terms ):array 
	* _taxonomy_meta_box_sanitize_cb_input( $taxonomy, $terms ):array 
	* _use_block_editor_for_post( $post ):bool 
	* _use_block_editor_for_post_type( $post_type ):bool 
	* _get_block_editor_server_block_settings():array 
	* _get_block_editor_meta_boxes():string 
	* _get_block_editor_meta_box_post_form_hidden_fields( $post ):string 
	* _disable_block_editor_for_navigation_post_type( $value, $post_type ):bool 
	* _disable_content_editor_for_navigation_post_type( $post ):void 
	* _enable_content_editor_for_navigation_post_type( $post ):void 
