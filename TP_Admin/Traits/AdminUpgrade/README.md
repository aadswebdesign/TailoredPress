### TP_Admin/Traits/AdminUpgrade

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_upgrade_01.php: //todo	
	* _tp_install( $blog_title, $user_name, $user_email, $public, $deprecated = '', $user_password = '', $language = '' ){return '';} 
	* _tp_install_defaults( $user_id ){return '';} 
	* _tp_install_maybe_enable_pretty_permalinks(){return '';} 
	* _tp_new_blog_notification( $blog_title, $blog_url, $user_id, $password ){return '';} 
	* _tp_upgrade(){return '';}
	* _upgrade_all(){return '';} 
	* _upgrade_network(){return '';} 
	* _maybe_create_table( $table_name, $create_ddl ){return '';} 
	* _drop_index( $table, $index ){return '';} 
	* _add_clean_index( $table, $index ){return '';} 

- _adm_upgrade_02.php: //todo	
	* _maybe_add_column( $table_name, $column_name, $create_ddl ){return '';} 
	* _maybe_convert_table_to_utf8mb4( $table ){return '';} 
	* __get_option( $setting ){return '';} 
	* _deslash( $content ){return '';} 
	* _db_delta( $queries = '', $execute = true ){return '';} 
	* _make_db_current( $tables = 'all' ){return '';} 
	* _make_db_current_silent( $tables = 'all' ){return '';} 
	* _make_site_theme_from_oldschool( $theme_name, $template ){return '';} 
	* _make_site_theme_from_default( $theme_name, $template ){return '';} 
	* _make_site_theme(){return '';} 

- _adm_upgrade_03.php: //todo	
	* _translate_level_to_role( $level ){return '';} 
	* _tp_check_mysql_version(){return '';} 
	* _maybe_disable_link_manager(){return '';} 
	* _pre_schema_upgrade(){return '';} 
	* _install_global_terms(){return '';} 
	* _tp_should_upgrade_global_tables(){return '';} 
