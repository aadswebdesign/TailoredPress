### TP_Core/Traits/Block

**Note:** For what it is now and subject to change. Also one of my habbits is to give classes/methods a name that speaks for itself.

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods
- _block_category_patterns.php: 	
	* _register_core_block_patterns_and_categories():void 
	* _load_remote_block_patterns():void 
	* _load_remote_featured_patterns():void 
	* _register_remote_theme_patterns():void 
	* _register_theme_block_patterns():void 
	* _block_category_patterns_hooks():void 

- _block_duotone_01.php: 	
	* _tp_tinycolor_bound01( $n, $max ): float 
	* _tp_tinycolor_bound_alpha( $n ) -> float|int 
	* _tp_tinycolor_rgb_to_rgb( $rgb_color ): array 
	* _tp_tinycolor_hue_to_rgb( $p, $q, $t ) -> mixed 
	* _tp_tinycolor_hsl_to_rgb( $hsl_color ): array 
	* _tp_tinycolor_string_to_rgb( $color_str ): ?array 
	* _tp_get_duotone_filter_id( $preset ): string 
	* _tp_get_duotone_filter_property( $preset ): string 
	* _tp_get_duotone_filter_svg( $preset ) -> mixed  
	* _tp_register_duotone_support( $block_type ): void 

- _block_duotone_02.php: 	
	* _tp_render_duotone_support( $block_content, $block ) -> mixed  
	* _duotone_hooks(): void 

- _block_registries.php: 	
	* _register_block_pattern( $pattern_name, $pattern_properties ): bool 
	* _unregister_block_pattern( $pattern_name ): bool 
	* _register_block_pattern_category( $category_name, $category_properties ): bool 
	* _unregister_block_pattern_category( $category_name ): bool 

- _blocks_01.php: 	
	* _remove_block_asset_path_prefix( $asset_handle_or_path ): string  
	* _generate_block_asset_handle( $block_name, $field_name ) -> mixed   
	* _register_block_script_handle( $metadata, $field_name ): bool  
	* _register_block_style_handle( $metadata, $field_name ) -> mixed   
	* _get_block_metadata_i18n_schema() -> mixed   
	* _register_block_type_from_metadata( $file_or_folder, $args = [] ): bool  
	* _register_block_type( $block_type, $args = array() ): bool  
	* _unregister_block_type( $name ): bool  
	* _has_blocks( $post = null ): bool  
	* _has_block( $block_name, $post = null ): bool  

- _blocks_02.php: 	
	* _get_dynamic_block_names(): array  
	* _serialize_block_attributes( $block_attributes )  
	* _strip_core_block_namespace( $block_name = null ): ?string  
	* _get_comment_delimited_block_content( $block_name, $block_attributes, $block_content ): string  
	* _serialize_block( $block ): string  
	* _serialize_blocks( $blocks ): string  
	* _filter_block_content( $text, $allowed_html = 'post', $allowed_protocols = [] ): string  
	* _filter_block_kses( $block, $allowed_html, $allowed_protocols = array() ) -> mixed     
	* _filter_block_kses_value( $value, $allowed_html, $allowed_protocols = [] ): array  
	* _excerpt_remove_blocks( $content ): string  

- _blocks_03.php: 	
	* _excerpt_render_inner_blocks( $parsed_block, $allowed_blocks ): string  
	* _render_block( $parsed_block ): string  
	* _parse_blocks( $content ) -> mixed     
	* _do_blocks( $content ): string  
	* _restore_tp_autop_hook( $content ) -> mixed     
	* _block_version( $content ) -> mixed     
	* _register_block_style( $block_name, $style_properties ): bool  
	* _unregister_block_style( $block_name, $block_style_name ): bool  
	* _block_has_support( $block_type, $feature, $default = false ): bool  
	* _build_query_vars_from_query_block( $block, $page ): array  

- _blocks_04.php: 	
	* _get_query_pagination_arrow( $block, $is_next ):?string  
	* _tp_multiple_block_styles( $metadata ) -> mixed  
	* _build_comment_query_vars_from_block( $block ):array  
	* _get_comments_pagination_arrow( $block, $pagination_type = 'next' ):?string  
	* _comments_hooks():void  

- _blocks_editor.php: 	
	* _get_default_block_categories():array  
	* _get_block_categories( $post_or_block_editor_context ):array  
	* _get_allowed_block_types( $block_editor_context ):bool  
	* _get_default_block_editor_settings():array  
	* _get_block_editor_settings( array $custom_settings, $block_editor_context ): array  
	* _block_editor_rest_api_preload( array $preload_paths, $block_editor_context ): void  
	* _get_block_editor_theme_styles():array  
