### TP_Admin/Libs/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 

- Adm_Privacy_Policy_Content.php: final 
	* private static $__policy_content 
	* __construct() 
	* add( $library_name, $policy_text ):void static 
	* text_change_check():bool static 
	* get_policy_text_changed_notice():string static 
	* _policy_page_updated( $post_id ):void static 
	* public static function get_suggested_policy_text():array static 
	* get_notice( $post = null ) static 
	* get_privacy_policy_guide():string static 
	* get_default_content( $description = false, $blocks = true ):string static 
	* add_suggested_content():void static 

- Adm_Screen.php: 	
	* private $__columns, $__help_sidebar, $__help_tabs, $__options, $__show_screen_options, $__screen_reader_content, $__screen_settings 
	* private static $__old_compat_help, $__registry 
	* protected $in_admin;
	* public $action, $base, $id, $is_block_editor, $is_network, $is_user 
	* public $parent_base, $parent_class, $parent_file, $post_type, $taxonomy 
	* __construct() 
	* get_screen( $hook_name = '' ) static
	* set_screen(): void 
	* get_in_admin( $admin = null ): bool 
	* is_block_editor( $set = null ): bool 
	* add_old_compat_help( $screen, $help ): void static 
	* set_parent_page(string $parent_file): void 
	* set_parent_class( $parent_class,$parent_path = null,...$args ): void //todo 
	* add_screen_option( $option,array ...$args): void 
	* remove_screen_option( $option ): void 
	* remove_screen_options(): void 
	* get_screen_options(): array 
	* get_screen_option( $option, $key = false ) 
	* get_help_tabs(): array 
	* get_help_tab( $id ) 
	* add_help_tab($args = null ): void 
	* remove_help_tab( $id ): void 
	* get_help_sidebar(): string 
	* set_help_sidebar( $content ): void 
	* get_columns(): int 
	* get_screen_reader_content(): array 
	* get_screen_reader_text( $key ) 
	* set_screen_reader_content(array ...$content): void 
	* remove_screen_reader_content(): void 
	* get_render_screen_meta(): string 
	* show_screen_options() 
	* get_render_screen_options(array ...$options): string 
	* get_render_meta_boxes_preferences() 
	* get_render_list_table_columns_preferences() //todo rename 
	* get_render_screen_layout() 
	* get_render_per_page_options(): //todo 
	* get_render_view_mode() 
	* get_render_screen_reader_content( $key = '', $tag = 'h2' ): string 

- .php: 	
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  

- .php: 	
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  

- .php: 	
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  
	*  

