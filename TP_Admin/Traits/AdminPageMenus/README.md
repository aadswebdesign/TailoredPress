### TP_Admin/Traits/AdminPageMenus

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_page_menu_01.php: 	
	* _add_adm_menu_page( $page_title, $menu_title, $capability, $menu_slug, $class = null, $method = null, $icon_url = '', $position = null, $args=null) 
	* _add_adm_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $class = null, $method = null, $position = null,$args=null ):bool 
	* _add_adm_management_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_options_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_theme_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_module_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_users_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_dashboard_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_posts_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_media_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 

- _adm_page_menu_02.php: 	
	* _add_adm_links_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null,$args=null ):bool 
	* _add_adm_pages_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null, $args=null ):bool 
	* _add_adm_comments_page( $page_title, $menu_title, $capability, $menu_slug, $class = '', $method = '', $position = null, $args=null ):bool 
	* _remove_adm_menu_page( $menu_slug ):bool 
	* _remove_adm_submenu_page( $menu_slug, $submenu_slug ):bool 
	* _get_adm_menu_page_url( $menu_slug) 
	* _get_adm_page_parent( $parent = '' ):string 
	* _get_adm_page_title() 
	* _get_adm_library_page_hook( $libs_page, $parent_page ) 

- _adm_page_menu_03.php: 	
	* _get_adm_library_page_hookname( $libs_page, $parent_page ):string 
	* _user_can_access_admin_page():bool 
	* _get_hidden_input_settings_fields( $option_group ):string 
