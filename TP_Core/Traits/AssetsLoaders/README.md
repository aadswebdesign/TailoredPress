### TP_Core/Traits/AssetsLoaders

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _assets_loader_01.php: 	
	* _print_head_scripts(): array 
	* _print_footer_scripts(): array 
	* _print_scripts($type = null): string 
	* _tp_print_head_scripts(): array 
	* _tp_footer_assets(): void 
	* tp_print_footer_assets(): void 
	* tp_enqueue_assets(): void 
	* _print_admin_styles(): array 
	* _print_late_styles(): ?array 
	* _print_styles($rel = null, $media = null): void 

- _assets_loader_02.php: 	
	* _assets_concat_settings():void 
	* _tp_common_block_assets():void 
	* _tp_enqueue_global_styles(): void 
	* _get_global_styles_render_svg_filters(): string 
	* _tp_global_styles_render_svg_filters():void 
	* _tp_should_load_block_editor_assets() -> mixed 
	* _tp_should_load_separate_core_block_assets():bool 
	* _tp_enqueue_registered_block_assets():void 
	* _enqueue_block_styles_assets(): void 
	* _enqueue_editor_block_styles_assets():void 
	* _tp_enqueue_editor_block_directory_assets(): void 

- _assets_loader_03.php: 	
	* _tp_enqueue_editor_format_library_assets():void 
	* _tp_sanitize_script_attributes( $attributes ):string 
	* _tp_get_script_tag( $attributes, $type = null ):string 
	* tp_print_script_tag( $attributes, $type = null ):void 
	* _tp_get_inline_script_tag( $javascript,$attributes=[], $type = null):string 
	* tp_print_inline_script_tag( $javascript, $attributes = [], $type = null ):void 
	* _tp_maybe_inline_styles(): void 
	* _tp_normalize_relative_css_links($css, $stylesheet_url) -> mixed 
	* _tp_enqueue_global_styles_css_custom_properties():void 
	* _tp_enqueue_block_support_styles( $style ):void 

- _assets_loader_04.php: 	
	* _tp_enqueue_block_style( $block_name, $args ):void 
	* _tp_theme_json_webfonts_handler():void 
	* _loader_hooks():void 
	* tp_enqueue_block_assets():void //todo 

- _assets_loader_05.php: 	
	* _tp_add_i_framed_editor_assets_html():string 
	* get_i_framed_editor_assets_html():void 
	* _tp_scripts_get_suffix($type = '') ->? 
