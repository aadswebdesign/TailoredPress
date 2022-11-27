### TP_Admin/Traits/AdminTheme

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_theme_01.php: 	
	* _get_delete_theme( $stylesheet, $redirect = '' ):string 
	* _get_page_templates( $post = null, $post_type = 'page' ):string 
	* _get_template_edit_filename( $fullpath, $containingfolder ):string 
	* _get_theme_update_available( $theme ):string 
	* _get_theme_feature_list( $api = true ):string{return '';}  //todo 
	* _themes_api( $action, $args = array() ):string{return '';}  //todo 
	* _tp_prepare_themes_for_js( $themes = null ):array{return '';}  //todo 
	* _customize_themes_print_templates():string{return '';}  //todo 
	* _is_theme_paused( $theme ):string{return '';}  //todo

- _adm_theme_01.php: 	
	* _tp_get_theme_error( $theme ){return '';} 
	* _resume_theme( $theme, $redirect = '' ){return '';} 
	* _paused_themes_notice(){return '';} 
	
- _theme_install.php: 	
	* _themes_tags(){return '';} //added todo 
	* _install_theme_search_form( $type_selector = true ){return '';}  //todo  
	* _install_themes_dashboard(){return '';}  //todo  
	* _install_themes_upload(){return '';}  //todo  
	* _display_themes(){return '';}  //todo 
	* _install_theme_information(){return '';}  //todo  