### TP_Admin/Traits/AdminMisc

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _misc_01.php: 	
	* _get_admin_color_scheme_picker( $user_id ):callable 
	* _tp_get_color_scheme_settings():string 
	* _tp_get_admin_viewport_meta():string 
	* _customizer_mobile_viewport_meta( $viewport_meta ):string 
	* _tp_check_locked_posts( $response, $data, $screen_id ) 
	* _tp_refresh_post_lock( $response, $data, $screen_id) 
	* _tp_refresh_post_nonces( $response, $data, $screen_id ) 
	* _tp_refresh_heartbeat_nonces( $response ) 
	* _tp_heartbeat_set_suspension( $settings ) 
	* _heartbeat_autosave( $response, $data ) 

- _misc_02.php: 	
	* _tp_get_admin_canonical_url() 
	* _tp_admin_headers():void 
	* _tp_get_page_reload_on_back_button_js():string 
	* _update_option_new_admin_email( $old_value, $value ):void 
	* _tp_privacy_settings_filter_draft_page_titles( $title, $page ):string 
	* _tp_check_php_version():bool 
	* _tp_add_privacy_policy_content( $module_name, $policy_text ):void 
