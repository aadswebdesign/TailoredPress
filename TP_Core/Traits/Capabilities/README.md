### TP_Core/Traits/Capabilities

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 

- _capability_01.php: 	
	- _map_meta_cap( $cap, $user_id, ...$args )  
	- _current_user_can( $capability, ...$args ):string 
	- _current_user_can_for_blog( $blog_id, $capability, ...$args ):string 
	- _author_can( $post, $capability, ...$args ):bool
	- _user_can( $user, $capability, ...$args ): bool
	- _tp_roles():TP_Roles 
	- _get_role( $role ) 
	- _add_role( $role, $display_name, $capabilities = [] ) 
	- _remove_role( $role ):void 
	- _get_super_admins()  

- _capability_02.php: 	
	- _is_super_admin( $user_id = false ):bool 
	- _grant_super_admin( $user_id ):bool 
	- _revoke_super_admin( $user_id ):bool 
	- _tp_maybe_grant_install_languages_cap( $all_caps ) 
	- _tp_maybe_grant_resume_extensions_caps( $all_caps )  
	- _tp_maybe_grant_site_health_caps( $all_caps, $user)  
	- dummy_user_roles():void 
	- _capability_hooks():void 
