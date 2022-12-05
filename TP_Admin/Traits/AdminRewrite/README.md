### TP_Admin/Traits/AdminRewrite

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_rewrite_01.php: 	
	* _get_mod_rewrite() 
	* _get_url_rewrite() 
	* _extract_from_markers( $filename, $marker ):array 
	* _insert_with_markers( $filename, $marker, $insertion ):bool 
	* _save_mod_rewrite_rules() 
	* _iis7_save_url_rewrite_rules() 
	* _update_recently_edited( $file ):void 
	* _tp_make_theme_file_tree( $allowed_files ):array 
	* _tp_print_theme_file_tree( $tree, $level = 2, $size = 1, $index = 1 ):void 
	* _tp_make_plugin_file_tree( $plugin_editable_files ){return '';} //not used

- _adm_rewrite_02.php: 	
	* _tp_print_plugin_file_tree //not used 
	* _update_home_siteurl():void 
	* _tp_reset_vars( $vars ):void 
	* _show_message($message ):void 
	* _tp_doc_link_parse( $content ):string 
	* _set_screen_options():void 
	* _iis7_rewrite_rule_exists( $filename ):bool 
	* _iis7_delete_rewrite_rule( $filename ):bool 
	* _iis7_add_rewrite_rule( $filename, $rewrite_rule ) 
	* _saveDomDocument(\DOMDocument $doc, $filename ):void 
