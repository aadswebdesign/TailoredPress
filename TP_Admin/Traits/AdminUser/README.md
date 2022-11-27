### TP_Admin/Traits/AdminUser

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_user_01.php: 	
	* _add_user():string 
	* _edit_user( $user_id = 0 ) 
	* _get_editable_roles() 
	* _get_user_to_edit( $user_id ) 
	* _get_users_drafts( $user_id ):?array 
	* _tp_delete_user( $id, $reassign = null ):bool 
	* _tp_revoke_user(int $id ):void 
	* _default_password_nag_handler():void 
	* _default_password_nag_edit_user( $user_ID, $old_data ):void 
	* _get_default_password_nag():string 

- _adm_user_02.php: 	
	* _get_delete_users_add_js():string 
	* _get_use_ssl_preference( $user ):string 
	* _admin_created_user_email( $text ):string 
	* _tp_is_authorize_application_password_request_valid( $request, $user ):bool 
