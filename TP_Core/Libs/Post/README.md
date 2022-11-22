### TP_Core/Libs/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- Post_Base.php: 	
	* public $comment_count, $comment_status, $description, $filter, $guid, $ID,$label,$labels 
	* public $menu_order, $name, $ping_status, $pinged, $post_author, $post_content, $post_content_filtered 
	* public $post_date, $post_date_gmt, $post_excerpt, $post_mime_type, $post_modified, $post_modified_gmt 
	* public $post_name, $post_parent, $post_password, $post_status, $post_title, $post_type, $public, $to_ping 

- PostType_Base.php: 	
	* public $tp_post_type_features, $post_type_meta_caps, $builtin, $can_export, $cap, $capability_type 
	* public $delete_with_user, $description, $edit_link, $exclude_from_search, $has_archive, $hierarchical
	* public $label, $labels , $map_meta_cap, $menu_icon, $menu_position, $name, $public, $publicly_queryable 
	* public $query_var, $register_meta_box_cb, $rest_base, $rest_controller, $rest_controller_class 
	* public $rest_namespace, $rewrite, $show_in_menu, $show_ui, $show_in_admin_bar 
	* public $show_in_nav_menus, $show_in_rest, $supports, $taxonomies, $template, $template_lock 

- TP_Post.php: 	
	* get_instance( $post_id ) -> mixed static
	* __construct( $post ) 
	* __isset( $key ) 
	* __get( $key ) 
	* post_filter( $filter ) 
	* to_array() 

- TP_Post_Type.php: 	
	* __construct( $post_type = null, ...$args) 
	* set_properties( $args ): void 
	* add_supports(): void 
	* add_rewrite_rules(): void 
	* register_meta_boxes(): void 
	* add_hooks(): void 
	* register_taxonomies(): void 
	* remove_supports(): void 
	* remove_rewrite_rules(): void 
	* unregister_meta_boxes(): void 
	* unregister_taxonomies(): void 
	* remove_hooks(): void 
	* get_rest_controller() 
