### TP_Admin/Traits/AdminTemplates

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_template_01.php: 	
	* _tp_category_checklist( $post_id = 0, ...$cat_args ):void 
	* _tp_get_terms_checklist( $post_id = 0, ...$terms_args):string 
	* _tp_get_popular_terms_checklist( $taxonomy, $number = 10) 
	* _tp_get_link_category_checklist( $link_id = 0 ) 
	* _get_inline_data( $post ):string 
	* _tp_get_comment_reply( $position = 1, $checkbox = false, $mode = 'single'):string 
	* _tp_get_comment_trash_notice():string 
	* _get_list_meta( $meta ):string 
	* _list_meta_block( $entry, &$count ):string 
	* _get_meta_form( $post = null ):string 
	* _get_touch_time( $edit = 1, $for_post = 1, $tab_index = 0, $multi = 0 ):string 

- _adm_template_02.php: 	
	* _get_page_template_dropdown( $default = '', $post_type = 'page' ):string 
	* _get_parent_dropdown( $default = 0, $parent = 0, $level = 0, $post = null ):string 
	* _tp_get_dropdown_roles( $selected = '' ):string 
	* _tp_get_import_upload_form( $action ):string 
	* _get_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args =null ) 
	* _add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ):void 
	* _do_block_editor_incompatible_meta_box( $object, $box ){return '';} //todo 
	* _get_meta_boxes( $screen, $context, $object ):string 
	* _remove_meta_box( $id, $screen, $context ):void 
	* _get_accordion_sections( $screen, $context, $object ):string
	* _add_settings_section( $id, $title, $callback, $page ):void 
	
- _adm_template_03.php: 	
	* _add_settings_field( $id, $title, $callback, $page, $section = 'default',$args = null):void 
	* _get_settings_sections( $page ):string 
	* _get_settings_fields( $page, $section ):string 
	* _add_settings_error( $setting, $code, $message, $type = 'error' ):void 
	* _get_settings_errors( $setting = '', $sanitize = false ):array 
	* _settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ):string 
	* _get_posts_div( $found_action = '' ):string 
	* _get_the_post_password():string 
	* _draft_or_post_title( $post = 0 ) 
	* _get_admin_search_query():string
	
- _adm_template_04.php: 	
	* _get_iframe_header( $title = ''):string 
	* _get_iframe_footer():string 
	* _post_states( $post ):string 
	* _get_printed_media_states( $post):string 
	* _print_media_states( $post):void 
	* _get_media_states( $post ):array 
	* _get_compression_test():string 
	* _get_submit_button( $text = '', $type = 'primary large', $name = 'submit', $wrap = true, $other_attributes = null ):string 
	* _tp_get_admin_html_begin():string 
	* _convert_to_screen( $hook_name ):Adm_Screen
	
- _adm_template_05.php: 	
	* _get_local_storage_notice():string 
	* _tp_get_star_rating($args = null):string 
	* _tp_get_posts_page_notice():string 
	* _tp_get_block_editor_posts_page_notice():string 
	* _get_plugin_from_callback( $callback ){return '';} //not used 
