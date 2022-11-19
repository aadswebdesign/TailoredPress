### TP_Core/Traits/User

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _user_01.php: 	
	* _tp_sign_on($secure_cookie = '',array ...$credentials) -> mixed 
	* _tp_authenticate_username_password($user, $username, $password ) -> TP_Error|TP_User 
	* _tp_authenticate_email_password( $user, $email, $password ) -> TP_Error|TP_User 
	* _tp_authenticate_cookie( $user, $username, $password ) -> TP_Error|TP_User 
	* _tp_authenticate_application_password( $input_user, $username, $password ) -> mixed 
	* _tp_validate_application_password( $input_user ):int 
	* _tp_authenticate_spam_check( $user ) -> TP_Error|TP_User 
	* _tp_validate_logged_in_cookie( $user_id ):bool 
	* _count_user_posts( $userid, $post_type = 'post', $public_only = false ) -> mixed 
	* _count_many_users_posts( $users, $post_type = 'post', $public_only = false ):array 

- _user_02.php: 	
	* _get_current_user_id():int 
	* _get_user_option($option, $user=0):bool 
	* _update_user_option( $user_id, $option_name, $new_value, $global = false ) -> mixed 
	* _delete_user_option( $user_id, $option_name, $global = false ):string 
	* _get_users(...$args):array 
	* _tp_get_list_users(...$args):string 
	* _tp_list_users(array ...$args):void 
	* _get_blogs_of_user( $user_id, $all = false ):array 
	* _is_user_member_of_blog( $user_id = 0, $blog_id = 0 ):bool 
	* _add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ) -> mixed 
	* _delete_user_meta( $user_id, $meta_key, $meta_value = '' ) -> mixed 

- _user_03.php: 	
	* _get_user_meta( $user_id, $key = '', $single = false ) -> mixed 
	* _update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) -> mixed 
	* _count_users( $strategy = 'time', $site_id = null ):array 
	* _setup_user_data( $for_user_id = 0 ):void 
	* _tp_get_dropdown_users(...$args) -> mixed  
	* _tp_dropdown_users(...$args):void 
	* _sanitize_user_field( $field, $value, $user_id, $context ):int 
	* _update_user_caches( $user ):void 
	* _clean_user_cache( $user ):void 
	* _username_exists( $username ) -> mixed  
	* _email_exists( $email ) -> mixed  

- _user_04.php: 	
	* _validate_username( $username ) -> mixed 
	* _tp_insert_user( $userdata ) -> int|TP_Error
	* _tp_update_user( $userdata ) -> mixed 
	* _tp_create_user( $username, $password, $email = '' ):int 
	* _get_additional_user_keys( $user ):array 
	* _tp_get_user_contact_methods( $user = null ) -> mixed  
	* _tp_get_password_hint() -> mixed 
	* _get_password_reset_key( $user ) -> bool|int|TP_Error 
	* _check_password_reset_key( $key, $login ):TP_Error 
	* _retrieve_password( $user_login = null ) -> bool|TP_Error 

- _user_05.php: 	
	* _reset_password( $user, $new_pass ):void 
	* _register_new_user( $user_login, $user_email ):TP_Error 
	* _tp_send_new_user_notifications( $user_id, $notify = 'both' ):void 
	* _tp_get_session_token():string 
	* _tp_get_all_sessions() -> mixed  
	* _tp_destroy_current_session():void 
	* _tp_destroy_other_sessions():void	
	* _tp_destroy_all_sessions():void 
	* _tp_get_users_with_no_role( $site_id = null ):array 
	* _tp_get_current_user():?TP_User 

- _user_06.php: 	
	* _send_confirmation_on_profile_email():bool 
	* _get_new_user_email_admin_notice():string 
	* _new_user_email_admin_notice():void 
	* _tp_privacy_action_request_types():array 
	* _tp_register_user_personal_data_exporter( $exporters ) -> mixed  
	* _tp_user_personal_data_exporter( $email_address ):array 
	* _tp_privacy_account_request_confirmed( $request_id ):void 
	* _tp_privacy_send_request_confirmation_notification( $request_id ):void 
	* _tp_privacy_send_erasure_fulfillment_notification( $request_id ):void 
	* _tp_privacy_account_request_confirmed_message( $request_id ):string 
	* _tp_create_user_request( $email_address = '', $action_name = '', $status = 'pending',array ...$request_data):TP_Error 

- _user_07.php: 	
	* _tp_user_request_action_description( $action_name ) -> mixed 
	* _tp_send_user_request( $request_id ) -> mixed 
	* _tp_generate_user_request_key( $request_id ) 
	* _tp_validate_user_request_key( $request_id, $key ) -> bool|TP_Error 
	* _tp_get_user_request( $request_id ) -> bool|TP_User_Request
	* _tp_is_application_passwords_supported():bool 
	* _tp_is_application_passwords_available() -> mixed 
	* _tp_is_application_passwords_available_for_user( $user ):bool 