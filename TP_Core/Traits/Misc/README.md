### TP_Core/Traits/Misc

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _assets_base.php: 	
	* _tp_scripts_maybe_doing_it_wrong( $function, $handle = '' ):void 

- _bookmark.php: 	
	* _get_bookmark( $bookmark, $output = OBJECT, $filter = 'raw' ) -> mixed 
	* _get_bookmark_field( $field, $bookmark, $context = 'display' ) -> mixed 
	* _get_bookmarks( $args = '' ) -> mixed 
	* _sanitize_bookmark( $bookmark, $context = 'display' ) -> mixed 
	* _sanitize_bookmark_field( $field, $value, $bookmark_id, $context ) -> mixed 
	* _clean_bookmark_cache( $bookmark_id ):void 

- _canonical.php: 	
	* _redirect_canonical( $requested_url = null, $do_redirect = true ) -> mixed  
	* __remove_qs_args_if_not_in_url( $query_string, array $args_to_check, $url ) -> mixed  
	* _strip_fragment_from_url( $url ):string 
	* _redirect_guess_404_permalink() -> bool|string
	* _tp_redirect_admin_locations():void 

- _error_protection.php: 	
	* _tp_paused_themes():TP_Paused_Extensions_Storage 
	* _tp_get_extension_error_description( $error ):string 
	* _tp_register_fatal_error_handler():void 
	* _tp_is_fatal_error_handler_enabled() 
	* _tp_recovery_mode():TP_Recovery_Mode 

- _global_settings.php: 	
	* _tp_get_global_settings( $path = [], $context = [] ) -> mixed 
	* _tp_get_global_styles( $path = [], $context = [] ) -> mixed 
	* _tp_get_global_stylesheet( $types=[]):string 
	* _tp_get_global_styles_svg_filters():string 
	* _tp_is_mobile() -> mixed 

- _rewrite.php: 	
	* _add_rewrite_rule( $regex, $query, $after = 'bottom' ):void 
	* _add_rewrite_tag( $tag, $regex, $query = '' ):void 
	* _remove_rewrite_tag( $tag ):void 
	* _add_permastruct( $name, $structure, ...$args):void 
	* _remove_permastruct( $name ):void 
	* _add_feed( $feed_name, $function ):string 
	* _flush_rewrite_rules( $hard = true ):void 
	* _add_rewrite_endpoint( $name, $places, $query_var = true ):void 
	* _tp_filter_taxonomy_base( $base ) -> mixed 
	* _tp_resolve_numeric_slug_conflicts( $query_vars =[]):array 
	* _url_to_postid( $url ):int 

- _sitemap.php: 	
	* _tp_sitemaps_get_server():TP_Sitemaps 
	* _tp_get_sitemap_providers():TP_Sitemaps 
	* _tp_register_sitemap_provider( $name, TP_Sitemaps_Provider $provider ):bool 
	* _tp_sitemaps_get_max_urls( $object_type ) -> mixed 
	* _get_sitemap_url( $name, $subtype_name = '',$page = 1 ) -> mixed 

- _update.php: 	
	* todo 
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  

- tp_link_styles.php: 	
	* tp_print_styles( $handles = false ): array 
	* tp_add_inline_style( $handle, $data ):bool 
	* tp_register_style( $handle, $href, $deps = [], $ver = false, $rel = null, $media = null, $crossorigin= null, $integrity= null, $extra_atts =null ): bool 
	* tp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $rel = null, $media = null, $crossorigin= null, $integrity= null, $extra_atts =null ): void 
	* tp_dequeue_style( $handle ): void 
	* tp_style_is($handle, $list = 'enqueued' ):bool 
	* tp_deregister_style( $handle ) -> mixed 

- tp_script.php: 	
	* tp_print_scripts( $handles = false ): array 
	* tp_add_inline_script( $handle, $data, $position = 'after' )//todo 
	* tp_register_script( $handle, $src, $deps = [], $ver = false, $type = null, $loading_type = null, $crossorigin = null, $integrity = null, $extra_atts = null, $in_footer = false ): bool 
	* tp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $type = null, $loading_type = null, $crossorigin = null, $integrity = null, $extra_atts = null, $in_footer = false ): void 
	* tp_dequeue_script( $handle ): void 
	* tp_script_is( $handle, $list = 'enqueued' ): bool 
	* tp_script_add_data($handle, $key, $value ): bool 
	* tp_localize_script( $handle, $object_name, $l10n ) 
	* tp_set_script_translations( $handle, $domain = 'default', $path = null ) 
	* tp_deregister_script( $handle ): void 
