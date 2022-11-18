### TP_Core/Traits/Multisite/Methods

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _ms_methods_01.php: 	
	* _get_sitestats():array 
	* _get_active_blog_for_user( $user_id ) -> mixed 
	* _get_user_count( $network_id = null ) -> mixed 
	* _get_blog_count( $network_id = null ) -> mixed 
	* _get_blog_post( $blog_id, $post_id ) -> mixed 
	* _add_user_to_blog( $blog_id, $user_id, $role ) -> bool|TP_Error 
	* _remove_user_from_blog( $user_id, $blog_id = 0, $reassign = 0 ) -> bool|TP_Error  
	* _get_blog_permalink( $blog_id, $post_id ) -> mixed 
	* _get_blog_id_from_url( $domain, $path = '/') -> mixed 
	* _is_email_address_unsafe( $user_email ) -> mixed 

- _ms_methods_02.php: 	
	* _tp_mu_validate_user_signup( $user_name, $user_email ) -> mixed 
	* _tp_mu_validate_blog_signup( $blogname, $blog_title,TP_User $user = null ) -> mixed 
	* _tp_mu_signup_blog( $domain, $path, $title, $user, $user_email, ...$meta):void 
	* _tp_mu_signup_user( $user, $user_email, ...$meta):void 
	* _tp_mu_signup_blog_notification( $domain, $path, $title, $user_login, $user_email, $key, $meta = [] ):bool 
	* _tp_mu_signup_user_notification( $user_login, $user_email, $key, $meta = [] ):bool 
	* _tp_mu_activate_signup( $key ) -> array|string|TP_Error 
	* _tp_delete_signup_on_user_delete( $id, $reassign, $user ):void 
	* _tp_mu_create_user( $user_name, $password, $email ):bool 
	* _tp_mu_create_blog( $domain, $path, $title, $user_id, $network_id = 1, ...$options):TP_Error 

- _ms_methods_03.php: 	
	* _new_blog_notify_site_admin( $blog_id):bool 
	* _new_user_notify_site_admin( $user_id ):bool 
	* _domain_exists($domain, $path, $network_id = 1) -> mixed  
	* _tp_mu_welcome_notification( $blog_id, $user_id, $password, $title, ...$meta):bool  
	* _tp_mu_new_site_admin_notification( $site_id, $user_id ):bool  
	* _tp_mu_welcome_user_notification( $user_id, $password, ...$meta):bool  
	* _get_current_site() -> mixed  
	* _get_most_recent_post_of_user( $user_id ):array  
	* _check_upload_mimes( $mimes ):array  
	* _update_posts_count():void  

- _ms_methods_04.php: 	
	* _tp_maybe_update_network_user_counts( $network_id = null ):void 
	* _tp_mu_log_new_registrations( $blog_id, $user_id ):void 
	* _global_terms( $term_id):int 
	* _redirect_this_site():array 
	* _upload_is_file_too_big( $upload ):string 
	* _signup_nonce_fields():void 
	* _signup_nonce_check($result ) -> mixed  
	* _maybe_redirect_404():void 
	* _maybe_add_existing_user_to_blog():void 
	* _add_existing_user_to_blog( $details = false ):bool  

- _ms_methods_05.php: 	
	* _fix_php_mailer_msg_id( $php_mailer ):void 
	* _is_user_spammy( $user = null ):bool 
	* _update_blog_public($value ):void 
	* _users_can_register_signup_filter():bool 
	* _welcome_user_msg_filter( $text ) -> mixed 
	* _force_ssl_content( $force = '' ) -> mixed 
	* _filter_SSL( $url ) -> mixed 
	* _tp_schedule_update_network_counts():void 
	* _tp_update_network_counts( $network_id = null ):void 
	* _tp_maybe_update_network_site_counts( $network_id = null ):void 

- _ms_methods_06.php: 	
	* _tp_update_network_site_counts( $network_id = null ):void 
	* _tp_update_network_user_counts( $network_id = null ):void 
	* _get_space_used() 
	* _get_space_allowed() 
	* _get_upload_space_available() 
	* _is_upload_space_available():bool 
	* _upload_size_limit_filter( $size ) 
	* _tp_is_large_network( $using = 'sites', $network_id = null ) 
	* _get_subdirectory_reserved_names()  
	* _update_network_option_new_admin_email($value ):void  
	* _tp_network_admin_email_change_notification( $new_email, $old_email, $network_id ):void