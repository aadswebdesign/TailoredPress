### TP_Core/Traits/Multisite/Sites

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _ms_site_01.php: 	
	* _tp_insert_site( array $data ) -> todo
	* _tp_update_site( $site_id, array ...$data ) -> todo
	* _tp_delete_site( $site_id ) -> todo 
	* _get_site( $site = null ) -> todo 
	* _prime_site_caches( $ids, $update_meta_cache = true ):void 
	* _update_site_cache( $sites, $update_meta_cache = true ):void 
	* _update_sitemeta_cache( $site_ids ) -> mixed 
	* _get_sites( ...$args) -> todo 
	* _tp_prepare_site_data( $data, $defaults,$old_site = null ) -> todo 
	* _tp_normalize_site_data( $data ) -> todo 
	* _tp_validate_site_data(TP_Error $errors, $data,TP_Network  $old_site = null ): void 

- _ms_site_02.php: 	
	* _tp_initialize_site( $site_id, array ...$args):bool 
	* _tp_un_initialize_site( $site_id ) -> bool|TP_Error  
	* _tp_is_site_initialized( $site_id ):bool  
	* _clean_blog_cache( $blog ):bool  
	* _add_site_meta( $site_id, $meta_key, $meta_value, $unique = false ) -> mixed   
	* _delete_site_meta( $site_id, $meta_key, $meta_value = '' ) -> mixed   
	* _get_site_meta( $site_id, $key = '', $single = false ) -> mixed   
	* _update_site_meta( $site_id, $meta_key, $meta_value, $prev_value = '' ) -> mixed   
	* _delete_site_meta_by_key( $meta_key ) -> mixed   
	* _tp_maybe_update_network_site_counts_on_update( $new_site, TP_Site $old_site = null ):void 

- _ms_site_03.php: 	
	* _tp_maybe_transition_site_statuses_on_update( $new_site,$old_site = null ):void  
	* _tp_maybe_clean_new_site_cache_on_update( $new_site, $old_site ):void  
	* _tp_update_blog_public_option_on_site_update( $site_id, $public ):void  
	* _tp_cache_set_sites_last_changed():void  
	* _tp_check_site_meta_support_prefilter( $check ):bool  
