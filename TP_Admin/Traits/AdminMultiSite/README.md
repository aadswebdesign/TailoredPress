### TP_Admin/Traits/AdminMultiSite

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_multisite_01.php: 	
	* _check_upload_size( $file ) 
	* _tp_mu_delete_blog( $blog_id, $drop = false ):void 
	* _tp_mu_delete_user( $id ):bool 
	* _upload_is_user_over_quota( $echo = true ):bool 
	* _get_display_space_usage():string 
	* _fix_import_form_size( $size ) 
	* _get_upload_space_setting( $id ):string 
	* _refresh_user_details( $id ) 
	* _format_code_lang( $code = '' ):string 
	* _sync_category_tag_slugs( $term, $taxonomy ) 

- _adm_multisite_02.php: 	
	* _access_denied_splash():void 
	* _check_import_new_users( $permission = true):bool 
	* _mu_get_dropdown_languages( $lang_files = [], $current = '' ):string 
	* get_site_admin_notice():string 
	* _avoid_blog_page_permalink_collision( $data) 
	* _get_primary_blog():string 
	* _can_edit_network( $network_id ) 
	* get_thickbox_path_admin_subfolder():string 
	* _get_confirm_delete_users( $users ):string 
	* _get_network_settings_add_js():string 

- _adm_multisite_03.php: 	
	* _get_network_edit_site_nav( $args =[]):string 
	* _get_site_screen_help_tab_args():array 
	* _get_site_screen_help_sidebar_content():string //todo 

- _adm_multisite_hooks.php: 	
	* _ms_admin_filters_hooks():void 