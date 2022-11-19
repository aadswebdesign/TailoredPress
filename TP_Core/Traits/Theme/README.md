### TP_Core/Traits/Theme

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _theme_01.php: 	
	* _tp_get_themes( $args = [] ):array 
	* _tp_get_theme( $stylesheet = '', $theme_root = '' ):TP_Theme 
	* _tp_clean_themes_cache( $clear_update_cache = true ):void 
	* _get_stylesheet() -> mixed
	* _get_stylesheet_directory() -> mixed 
	* _get_stylesheet_directory_uri() -> mixed 
	* _get_stylesheet_uri() -> mixed 
	* _get_locale_stylesheet_uri() -> mixed 
	* _get_template() -> mixed 
	* _get_template_directory() -> mixed //todo make it class based

- _theme_02.php: 	
	* _get_template_directory_uri() -> mixed 
	* _get_theme_roots() -> mixed 
	* _register_theme_directory( $directory ):bool 
	* _search_theme_directories( $force = false ) -> array|bool|null
	* _get_theme_root( $stylesheet_or_template = '' ) -> mixed 
	* _get_theme_root_uri( $stylesheet_or_template = '', $theme_root = '' ) -> mixed 
	* _get_raw_theme_root( $stylesheet_or_template, $skip_cache = false ) -> mixed 
	* _locale_stylesheet():void 
	* _switch_theme( $stylesheet ):void 
	* _validate_current_theme():bool 

- _theme_03.php: 	
	* _validate_theme_requirements( $stylesheet ) -> mixed 
	* _get_theme_mods():array 
	* _get_theme_mod( $name, $default = false ):bool 
	* _set_theme_mod( $name, $value ) -> mixed 
	* _remove_theme_mod( $name ):void 
	* _remove_theme_mods():void 
	* _get_header_textcolor() -> mixed 
	* header_textcolor():void 
	* _display_header_text():bool 
	* _has_header_image():bool 

- _theme_04.php: 	
	* _get_header_image():bool 
	* _get_header_image_tag( $attr = [] ):string 
	* the_header_image_tag( $attr = [] ):void 
	* _get_random_header_data() -> null|object|\stdClass
	* _get_random_header_image():string 
	* _is_random_header_image( $type = 'any' ):bool 
	* header_image():void 
	* _get_uploaded_header_images():array 
	* _get_custom_header() -> object 
	* _register_default_headers( $headers ):void 

- _theme_05.php: 	
	* _unregister_default_headers( $header ):bool 
	* _has_header_video():bool 
	* _get_header_video_url():bool 
	* the_header_video_url():void 
	* _get_header_video_settings() -> mixed  
	* _has_custom_header():bool 
	* _is_header_video_active():bool 
	* _get_custom_header_markup():string 
	* the_custom_header_markup():void 
	* _get_background_image() -> mixed  

- _theme_06.php: 	
	* background_image():void 
	* _get_background_color() -> mixed 
	* background_color():void 
	* _custom_background_cb():void 
	* tp_custom_css_cb():void 
	* _tp_get_custom_css_post( $stylesheet = '' ) -> mixed 
	* _tp_get_custom_css( $stylesheet = '' ):string 
	* _tp_update_custom_css_post( $css,array ...$args) -> mixed 
	* _add_editor_style($stylesheet = 'editor-style.css' ):void 
	* _remove_editor_styles():bool 

- _theme_07.php: 	
	* _get_editor_stylesheets():string 
	* _get_theme_starter_content() -> mixed 
	* _add_theme_support( $feature,array ...$args ) -> mixed 
	* _custom_header_background_just_in_time():void 
	* _custom_logo_header_styles():void 
	* _get_theme_support($feature, ...$args):string 
	* _remove_theme_support( $feature ) -> bool|string 
	* _remove_theme_support_internally( $feature ) -> mixed 
	* _current_theme_supports( $feature, ...$args):bool 
	* _require_if_theme_supports( $feature, $include ):bool 

- _theme_08.php: 	
	* _register_theme_feature( $feature, $args = [] ) -> bool|TP_Error
	* _get_registered_theme_features():array 
	* _get_registered_theme_feature( $feature ):bool 
	* _delete_attachment_theme_mod( $id ):void 
	* _check_theme_switched():void 
	* _tp_customize_include():void 
	* _tp_customize_publish_changeset( $new_status, $old_status, $changeset_post ):void 
	* _tp_customize_changeset_filter_insert_post_data( $post_data, $supplied_post_data ) -> mixed 
	* _tp_customize_loader_settings():void 
	* _tp_customize_url( $stylesheet = '' ) -> mixed 

- _theme_09.php: 	
	* _tp_get_customize_support_script():string 
	* _tp_customize_support_script():void 
	* _is_customize_preview():bool 
	* _tp_keep_alive_customize_changeset_dependent_auto_drafts( $new_status, $old_status, $post ):void 
	* _create_initial_theme_features():void 
	* _tp_is_block_theme():bool 
	* _add_default_theme_supports():void 