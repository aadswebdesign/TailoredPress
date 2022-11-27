### TP_Admin/Traits/AdminNavMenu

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_nav_menu_01.php: 	
	* _get_async_menu_quick_search( ...$request) 
	* _tp_nav_menu_setup():void 
	* _tp_initial_nav_menu_meta_boxes():void 
	* _tp_nav_menu_post_type_meta_boxes():void 
	* _tp_nav_menu_taxonomy_meta_boxes():void 
	* _tp_get_nav_menu_disabled_check( $nav_menu_selected_id):bool 
	* _tp_get_nav_menu_item_link_meta_box():string 
	* _tp_get_nav_menu_item_post_type_meta_box($box ):string 
	* _tp_get_nav_menu_item_taxonomy_meta_box( $box ):string 
	* _tp_save_nav_menu_items( $menu_id = 0, ...$menu_data):string 

- _adm_nav_menu_02.php: 	
	* _tp_nav_menu_meta_box_object( $object = null ) //todo
	* _tp_get_nav_menu_to_edit( $menu_id = 0 ):string 
	* _tp_nav_menu_manage_columns():array 
	* _tp_delete_orphaned_draft_menu_items():void 
	* _tp_nav_menu_update_menu_items( $nav_menu_selected_id, $nav_menu_selected_title ):array 
	* _tp_expand_nav_menu_post_data():void 
