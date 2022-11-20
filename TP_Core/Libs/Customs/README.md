### TP_Core/Libs/Customs

**Note:** For what it is now and subject to change. 
**TODO** This whole package is a mess and needs an overhaul 'methods prefixed, an interface and more'.

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- Customize_Base.php: 	
	* protected static $_instance_count 
	* public $instance_number, $manager, $id 
- TP_Customize_Control.php: extends Customize_Base	
	* public $settings, $setting, $capability, $priority, $section
	* public $label, $description, $choices, $input_attrs, $allow_addition; 
	* public $json, $type, $active_callback; 
	* __construct(TP_Customize_Manager $manager, $id, $args = array() ) 
	* enqueue(): void 
	* active_callback():bool 
	* value( $setting_key = 'default' ) 
	* to_json(): void 
	* json(): array 
	* check_capabilities(): bool 
	* get_content() 
	* maybe_render(): void 
	* _render(): void 
	* get_link( $setting_key = 'default' ): ?string 
	* link( $setting_key = 'default' ): void 
	* input_attrs(): void 
	* _get_render_content():string 
	* _render_content():void 
	* print_template():void 
	* _get_content_template():string 
	* _content_template():void
- TP_Customize_Manager.php: extends Customize_Base	
	* private $__post_values, $__changeset_uuid, $__changeset_post_id, $__changeset_data,$_theme, $_original_stylesheet 
	* protected $_previewing, $_settings, $_containers, $_panels,$_components, $_sections, $_controls,$_registered_panel_types 
	* protected $_registered_section_types, $_registered_control_types, $_preview_url, $_return_url, $_autofocus, $_messenger_channel, $_autosaved 
	* protected $_branching, $_settings_previewed, $_saved_starter_content_changeset, $_pending_starter_content_settings_ids, $_store_changeset_revision 
	* public $selective_refresh 
	* __construct(...$args){} 
	* doing_async( $action = null ):bool 
	* _tp_die( $async_message, $message = null ):void 
	* setup_theme():void 
	* establish_loaded_changeset():void 
	* after_setup_theme():void 
	* start_previewing_theme():void 
	* stop_previewing_theme():void 
	* settings_previewed():bool 
	* autosaved():bool 
	* branching() 
	* changeset_uuid():void 
	* settings():array 
	* controls():array 
	* containers():array 
	* panels():array 
	* is_theme_active():bool 
	* tp_loaded():void 
	* find_changeset_post_id( $uuid ) 
	* _get_changeset_posts(array $args) 
	* _dismiss_user_auto_draft_changesets():int 
	* changeset_post_id() 
	* _get_changeset_post_data( $post_id ) 
	* changeset_data() 
	* import_theme_starter_content(array $starter_content):void 
	* _prepare_starter_content_attachments( $attachments ):string 
	* _save_starter_content_changeset():void 
	* unsanitized_post_values(array ...$args) 
	* post_value(TP_Customize_Setting $setting, $default = null ) 
	* set_post_value( $setting_id, $value ):void 
	* customize_preview_init():void 
	* filter_iframe_security_headers( $headers ) 
	* add_state_query_params( $url ) 
	* get_customize_preview_loading_style():string 
	* customize_preview_loading_style():void 
	* get_remove_frameless_preview_messenger_channel():string 
	* remove_frameless_preview_messenger_channel():void 
	* get_customize_preview_settings():string 
	* customize_preview_settings():void 
	* is_preview():bool 
	* get_template() 
	* get_stylesheet() 
	* get_template_root() 
	* get_stylesheet_root() 
	* current_theme() 
	* validate_setting_values( $setting_values,array ...$options):string 
	* prepare_setting_validity_for_js(TP_Error $validity ) 
	* save():void 
	* save_changeset_post(array $args):string 
	* preserve_insert_changeset_post_content( $data, $unsanitized_postarr ):string 
	* trash_changeset_post( $post ):string 
	* handle_changeset_trash_request():void 
	* grant_edit_post_capability_for_changeset( $caps, $cap, $user_id, $args ):string 
	* set_changeset_lock( $changeset_post_id, $take_over = false ):void 
	* refresh_changeset_lock( $changeset_post_id ):void 
	* add_customize_screen_to_heartbeat_settings( $settings ):string  
	* _get_lock_user_data( $user_id ):array 
	* check_changeset_lock_with_heartbeat( $response, $data, $screen_id ):string 
	* handle_override_changeset_lock_request():void 
	* _filter_revision_post_has_changed( $post_has_changed, $last_revision, $post ):bool 
	* _publish_changeset_values( $changeset_post_id ):bool 
	* publish_changeset_values( $changeset_post_id ):bool 
	* _update_stashed_theme_mod_settings( $inactive_theme_mod_settings ):string 
	* refresh_nonces():void 
	* handle_dismiss_autosave_or_lock_request():void 
	* add_setting( $id,array ...$args):array 
	* add_dynamic_settings( $setting_ids ):array 
	* get_setting( $id ) 
	* remove_setting( $id ):void 
	* add_panel( $id,array ...$args):string 
	* get_panel( $id ):bool 
	* remove_panel( $id ):void 
	* register_panel_type( $panel ):void 
	* render_panel_templates():void 
	* add_section( $id,array ...$args):string 
	* get_section( $id ):bool 
	* remove_section( $id ):void 
	* register_section_type( $section ):void 
	* render_section_templates():void 
	* add_control( $id,array ...$args):string 
	* get_control( $id ) 
	* register_control_type( $control ):void 
	* get_render_control_templates():string 
	* render_control_templates():void 
	* prepare_controls():void 
	* enqueue_control_scripts():void 
	* is_ios():bool 
	* get_document_title_template():string 
	* set_preview_url( $preview_url ):void 
	* get_preview_url():string 
	* is_cross_domain():string 
	* get_allowed_urls():array 
	* get_messenger_channel() 
	* set_return_url( $return_url ):void 
	* get_return_url():string 
	* set_autofocus( $autofocus ):void 
	* get_autofocus():array 
	* get_nonces():array 
	* get_customize_pane_settings():string 
	* customize_pane_settings():void 
	* register_controls():void 
	* has_published_pages():int 
	* register_dynamic_settings():void 
	* handle_load_themes_request():void 
	* _sanitize_header_textcolor( $color ):string 
	* _sanitize_background_setting( $value, $setting ):string 
	* export_header_video_settings( $response, $partials ):string 
	* _validate_header_video(TP_Error $validity, $value ):string 
	* _validate_external_header_video(TP_Error $validity, $value ):string 
	* _sanitize_external_header_video( $value ):string 
	* _render_custom_logo_partial():string 
- TP_Customize_Nav_Menus.php:  extends Customize_Base	
	* protected $_original_nav_menu_locations 
	* public $preview_nav_menu_instance_args 
	* __construct( $manager ){} 
	* filter_nonces( $nonces ) 
	* async_load_available_items() 
	* load_available_items_query( $type = 'post_type', $object = 'page', $page = 0 ) 
	* async_search_available_items() 
	* search_available_items_query(array ...$args) 
	* enqueue_scripts() 
	* filter_dynamic_setting_args( $setting_args, $setting_id ) 
	* filter_dynamic_setting_class( $setting_class, $setting_id, $setting_args ) 
	* customize_register() 
	* intval_base10( $value ) 
	* available_item_types() 
	* insert_auto_draft_post( $postarr ) 
	* async_insert_auto_draft_post() 
	* print_templates() 
	* available_items_template() 
	* _print_post_type_container( $available_item_type ) 
	* _print_custom_links_available_menu_item() 
	* customize_dynamic_partial_args( $partial_args, $partial_id ) 
	* customize_preview_init() 
	* make_auto_draft_status_previewable() 
	* sanitize_nav_menus_created_posts( $value ) 
	* save_nav_menus_created_posts( $setting ) 
	* filter_tp_nav_menu_args( $args ) 
	* filter_wp_nav_menu( $nav_menu_content, $args ) 
	* hash_nav_menu_args( $args ):string 
	* customize_preview_enqueue_deps():void 
	* export_preview_data():void 
	* export_partial_rendered_nav_menu_instances( $response ) 
	* render_nav_menu_partial( $partial, $nav_menu_args ) 
- TP_Customize_Panel.php: 	
	* public $priority, $capability, $theme_supports, $title, $description
	* public $auto_expand_sole_section, $sections, $type, $active_callback
	* __construct( $manager, $id, array ...$args) 
	* active() 
	* active_callback():bool 
	* json():array 
	* check_capabilities():bool 
	* get_content() 
	* maybe_render():void 
	* _render():void 
	* _render_content() 
	* get_print_template() 
	* print_template():void 
	* _get_render_template() 
	* _render_template():void 
	* _get_content_template():string 
	* _content_template():void 
- TP_Customize_Section.php: 	
	* public $priority, $panel, $capability, $theme_supports, $title  
	* public $description, $controls, $type, $active_callback, $description_hidden
	* __construct( $manager, $id, array ...$args) 
	* active():bool 
	* active_callback():bool 
	* json():void 
	* maybe_render() :void 
	* _render():void 
	* get_print_template() 
	* print_template():void 
	* _get_render_template() 
	* _render_template():void 
- TP_Customize_Setting.php: 	
	* protected $_id_data, $_is_previewed
    * protected static $_aggregated_multi_dimensionals
	* protected $_is_multidimensional_aggregated, $_previewed_blog_id, $_original_value
	* public $type, $capability, $theme_supports, $default, $transport
	* public $validate_callback ,$sanitize_callback, $sanitize_js_callback, $dirty
	* __construct( $manager, $id,array ...$args) 
	* id_data():array 
	* _aggregate_multidimensional():void 
	* reset_aggregated_multi_dimensionals():void 
	* is_current_blog_previewed():bool 
	* preview():bool 
	* _clear_aggregated_multidimensional_preview_applied_flag():void 
	* _preview_filter( $original ) 
	* _multidimensional_preview_filter( $original ) 
	* save():bool 
	* post_value( $default = null ) 
	* sanitize( $value ) 
	* validate( $value ) 
	* _get_root_value( $default = null ) 
	* _set_root_value( $value ) 
	* _update( $value ) 
	* value() 
	* js_value() 
	* json():array 
	* check_capabilities():bool 
	* _multidimensional( &$root, $keys, $create = false ) 
	* _multidimensional_get( $root, $keys, $default = null ) 
	* multidimensional_isset( $root, $keys ):bool 
