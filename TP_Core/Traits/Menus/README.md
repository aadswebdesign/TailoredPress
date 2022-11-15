### TP_Core/Traits/Menus

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _nav_menu_01.php: 	
	* _tp_get_nav_menu_object( $menu ) 
	* _is_nav_menu( $menu ):bool 
	* _register_nav_menus(array $locations):array 
	* _unregister_nav_menu( $location ):bool 
	* _register_nav_menu( $location, $description ):void  
	* _get_registered_nav_menus():string 
	* _get_nav_menu_locations():array 
	* _has_nav_menu( $location )  
	* _tp_get_nav_menu_name( $location ) 
	* _is_nav_menu_item( $menu_item_id = 0 ):bool 

- _nav_menu_02.php: 	
	* _tp_create_nav_menu( $menu_name ):string 
	* _tp_delete_nav_menu( $menu ):bool 
	* _tp_update_nav_menu_object( $menu_id = 0, array ...$menu_data) -> mixed 
	* _tp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0, $menu_item_data = [], $fire_after_hooks = true ):int 
	* _tp_get_nav_menus( $args = [] ) -> mixed  
	* _is_valid_nav_menu_item( $item ):bool  
	* _tp_get_nav_menu_items( $menu,array $args):bool  
	* _tp_setup_nav_menu_item( $menu_item ) -> mixed  
	* _tp_get_associated_nav_menu_items(_tp_get_associated_nav_menu_items(int $object_id = 0, $object_type = 'post_type',array $taxonomy):array 
	* _tp_delete_post_menu_item( $object_id ):void 
- _nav_menu_03.php: 	
	* _tp_delete_tax_menu_item(int $object_id, $taxonomy ):void 
	* _tp_auto_add_pages_to_menu( $new_status, $old_status, $post ):void 
	* _tp_delete_customize_changeset_dependent_auto_drafts( $post_id ):void 
	* _tp_menus_changed():void 
	* _tp_map_nav_menu_locations( $new_nav_menu_locations, $old_nav_menu_locations ):array 
