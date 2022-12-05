### TP_Admin/Traits

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_bookmark.php: 	
	* _add_link():string 
	* _edit_link( $link_id = 0 ):string //todo  
	* _get_default_link_to_edit():\stdClass 
	* _tp_delete_link( $link_id ):bool 
	* _tp_get_link_cats( $link_id = 0 ):array 
	* _get_link_to_edit( $link ) 
	* _tp_insert_link( $link_data, $tp_error = false ):int 
	* _tp_set_link_cats( $link_id = 0, ...$link_categories):void 
	* _tp_update_link( $link_data ):int 
	* __tp_link_manager_disabled_message():void 
	* tp_link_manager_disabled_message():void 

- _adm_category.php: 	
	* _category_exists( $cat_name, $parent = null ):string 
	* _get_category_to_edit( $id ):string 
	* _tp_create_category( $cat_name, $parent = 0 ):string 
	* _tp_create_categories( $categories, $post_id = '' ):string 
	* _tp_insert_category( $cat_arr, $tp_error = false ):string 
	* _tp_update_category( $cat_arr ):string 
	* _tag_exists( $tag_name ):string 
	* _tp_create_tag( $tag_name ):string 
	* _get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ):string 
	* _get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ):string 
	* _tp_create_term( $tag_name, $taxonomy = 'post_tag' ):string 

- _adm_class_loaders.php: 	
	* get_adm_component_class($class_name,$args = null) 
	* get_adm_modules_class($class_name,$args = null) 
	* will grow 
	*  

- _adm_comment.php: 	
	* _comment_exists( $comment_author, $comment_date, $timezone = 'blog' ) 
	* _edit_comment() 
	* _get_comment_to_edit( $id ):bool 
	* _get_pending_comments_num( $post_id ) 
	* _floated_admin_avatar( $name ):string 
	* _enqueue_comment_hot_keys_js():void //todo make it return
	* _get_comment_footer_die( $msg ):string 

- _adm_filters.php: 	
	* _admin_filters():void //todo 

- _adm_install_db_helper.php: 	
	* _maybe_create_table( $table_name, $create_ddl ):bool 
	* _maybe_add_column( $table_name, $column_name, $create_ddl ):bool 
	* _maybe_drop_column( $table_name, $column_name, $drop_ddl ):bool 
	* _check_column( $table_name, $col_name, $col_type, $is_null = null, $key = null, $default = null, $extra = null ):bool 

- _adm_list_block.php: 	
	* _get_list_block($class = null,...$args) 
	* _get_register_column_headers( $screen, $columns ):string 
	* _get_the_column_headers( $screen, $with_id = true ):string 

- _adm_options.php: 	
	* get_options_index_stuff():string 
	* get_options_discussion_add_js():string 
	* get_options_general_add_js():string 
	* get_options_reading_add_js():string 
	* get_options_reading_blog_charset():string 

- _adm_privacy_tools.php: //todo	
	* _tp_privacy_resend_request( $request_id ):bool{return '';} 
	* _tp_privacy_completed_request( $request_id ):string{return '';} 
	* _tp_personal_data_handle_actions():void{} 
	* _tp_personal_data_cleanup_requests():void{} 
	* _tp_privacy_generate_personal_data_export_group_html( $group_data, $group_id = '', $groups_count = 1 ):string{return '';} 
	* _tp_privacy_generate_personal_data_export_file( $request_id ):void{} 
	* _tp_privacy_send_personal_data_export_email( $request_id ):string{return '';} 
	* _tp_privacy_process_personal_data_export_page( $response, $exporter_index, $email_address, $page, $request_id, $send_as_email, $exporter_key ):void{} 
	* _tp_privacy_process_personal_data_erasure_page( $response, $eraser_index, $email_address, $page, $request_id ):string{return '';} 

- _adm_screen.php: 	
	* _get_column_headers(Adm_Screen $screen ) 
	* _get_hidden_columns(Adm_Screen $screen ) 
	* _meta_box_prefers(Adm_Screen $screen ): void //todo make it return
	* _get_hidden_meta_boxes(Adm_Screen $screen ) 
	* _add_screen_option( $option,array ...$args): void //todo make it return 
	* _get_current_screen() 
	* _set_current_screen( $hook_name = '' ): void 

- _adm_taxonomy.php: //todo	
	* _category_exists( $cat_name, $parent = null ){return '';} 
	* _get_category_to_edit( $id ){return '';} 
	* _tp_create_category( $cat_name, $parent = 0 ){return '';} 
	* _tp_create_categories( $categories, $post_id = '' ){return '';} 
	* _tp_insert_category( $cat_arr, $tp_error = false ){return '';} 
	* _tp_update_category( $cat_arr ){return '';} 
	* _tag_exists( $tag_name ){return '';} 
	* _tp_create_tag( $tag_name ){return '';} 
	* _get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ){return '';} 
	* _get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ){return '';} 
	* _tp_create_term( $tag_name, $taxonomy = 'post_tag' ){return '';} 

- _adm_theme_install.php: 	
	* protected $_themes_allowedtags, $_theme_field_defaults 
	* _getTpListBlock() 
	* _get_install_theme_search_form($type_selector = true ):string 
	* _get_install_themes_dashboard():string 
	* _get_install_themes_upload():string 
	* _get_display_themes():string 
	* _get_install_theme_information():string 

- _adm_zipper.php: 	
	* _PclZipUtilPathReduction($p_dir) 
	* _PclZipUtilPathInclusion($p_dir, $p_path):int 
	* _PclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode=0):int 
	* _PclZipUtilRename($p_src, $p_dest):int 
	* _PclZipUtilOptionText($p_option) 
	* _PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter=true) 
