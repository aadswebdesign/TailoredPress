### TP_Core/Traits/Multisite/Blog

**Note:** For what it is now and subject to change. 

**Files/Methods:** 
- _ms_blog_01.php: 	
	- _tp_mu_update_blogs_date():void 
	- _get_blog_address_by_id( $blog_id ):string 
	- _get_blog_address_by_name( $blogname ) -> mixed 
	- _get_id_from_blogname( $slug ) -> mixed  
	- _get_blog_details($fields = null, $get_all = true ) -> bool|TP_Site
	- _refresh_blog_details( $blog_id = 0 ):void 
	- _update_blog_details( $blog_id,array $details):bool 
	- _clean_site_details_cache( $site_id = 0 ):void 
	- _get_blog_option( $id, $option, $default = false ) -> mixed 
	- _add_blog_option( $id, $option, $value ) -> mixed  
- _ms_blog_02.php: 	
	- _delete_blog_option( $id, $option ) -> mixed
	- _update_blog_option( $id, $option, $value)
	- _switch_to_blog( $new_blog_id):bool
	- _restore_current_blog():bool
	- _tp_switch_roles_and_user( $new_site_id, $old_site_id ):void
	- _ms_is_switched() -> mixed
	- _is_archived( $id ):string
	- _update_archived( $id, $archived ) -> mixed
	- _update_blog_status( $blog_id, $pref, $value):bool
	- _get_blog_status( $id, $pref ) todo?
- _ms_blog_03.php: 	
	- _get_last_updated($start = 0, $quantity = 40 ):array
	- _update_blog_date_on_post_publish( $new_status, $old_status, $post ):void
	- _update_blog_date_on_post_delete( $post_id ):void
	- _update_posts_count_on_delete( $post_id ):void
	- _update_posts_count_on_transition_post_status( $new_status, $old_status, $post = null ):void
	- _tp_count_sites( $network_id = null ):array
