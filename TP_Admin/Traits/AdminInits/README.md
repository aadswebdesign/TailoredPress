### TP_Admin/Traits/AdminInits

**Note:** This is an attempt to get rid of globals and has to be tested. 

**Files:** 
- README.md

**Files/Methods:** Traits

- _adm_init_files.php: 	
	* protected $_adm_file_system 
	* _init_files($arg = null):Adm_Filesystem_Base 

- _adm_init_screen.php: 	
	* _init_get_screen($hook_name = ''):Adm_Screen 
	* _init_set_screen($hook_name = ''):Adm_Screen 

- _init_admin.php: 	
	* protected $_adm_page_hooks, $_adm_library_page, $_adm_menu, $_adm_pagenow, $_adm_parent_file, $_adm_parent_pages, $_adm_registered_pages 
	* protected $_adm_submenu, $_adm_submenu_file, $_adm_tp_menu_nopriv, $_adm_real_parent_file, $_adm_submenu_nopriv, $_adm_type_now, $_adm_title 
	* protected $_adm_hook_suffix, $_adm_current_screen, $_adm_locale, $_adm_update_title, $_adm_total_update_count, $_adm_self 
	* getAdminPageHooks() 
	* getLibraryPage() 
	* getMenu() 
	* getPagenow() 
	* getParentFile() 
	* getParentPages() 
	* getRegisteredPages() 
	* getSubmenu() 
	* getTpMenuNopriv() 
	* getTpRealParentFile() 
	* getTypeNow() 
	* getTitle() 
	* getHookSuffix() 
	* getCurrentScreen() 
	* getTpLocale() 
	* getUpdateTitle() 
	* getTotalUpdateCount() 

- _init_list_blocks.php: 	
	* protected $_adm_list_block_compat, $_adm_theme_list_block_install 
	* _init_block_compat( $screen, $with_id = true ):Adm_Partial_Compats 
	* _init_theme_block_install():Adm_Partial_Themes_Install_Block 

- _init_zip.php: 	
	* protected $_adm_zip 
	* _init_zip($p_zipname = null):Adm_Zip 
