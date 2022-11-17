### TP_Core/Traits/Inits

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _init_adminbar.php: 	
	* _init_adminbar():TP_Admin_Bar 

- _init_assets.php: 	
	* _init_scripts(): TP_Scripts 
	* _init_styles(): TP_LinkStyle 

- _init_block.php: 	
	* _init_block(...$block): TP_Block  
	* _init_block_parser(): TP_Block_Parser  
	* _init_block_type_registry(): TP_Block_Type_Registry 
	* _init_block_type_instance(): ?TP_Block_Type_Registry 
	* _init_block_style_registry():TP_Block_Styles_Registry 
	* _init_block_style_get_instance(): ?TP_Block_Styles_Registry 
	* _init_block_script_registry():TP_Block_Scripts_Registry 

- _init_blog.php: 	
	* _init_blog_id($id=null):array 

- _init_cache.php: 	
	* _init_object_cache():TP_Object_Cache 

- _init_comment.php: 	
	*_init_comment(...$comment):TP_Comment  

- _init_core.php: 	
	* _init_core():TP_Core 

- _init_custom.php: 	
	* _init_customize_manager(...$args):TP_Customize_Manager 
	* _init_customize_setting($manager = null,$id= null):TP_Customize_Setting 

- _init_db.php: 	
	* _init_db(): TP_Db 

- _init_editor.php: 	
	* _init_editor($content, $editor_id,array ...$settings):TP_Editor 

- _init_embed.php: 	
	* _init_embed():TP_Embed 
	* _init_obj_embed():TP_ObjEmbed 

- _init_error.php: 	
	* _init_error( $thing = '' ): TP_Error 

- _init_formats.php: 	
	* todo 

- _init_hasher.php: 	
	* _init_hasher($iteration_count_log2 = null, $portable_hashes = null):TP_PasswordHash 

- _init_hook.php: 	
	* _init_hook():TP_Hook 

- _init_http.php: 	
	* _init_http():TP_Http 

- _init_images.php: 	
	* _init_editor_gd($file = null):TP_Image_Editor_GD 
	* _init_editor_imagick($file = null):TP_Image_Editor_Imagick 

- _init_ixr.php: 	
	* _init_ixr_server($callbacks = false, $data = false, $wait = false ): IXR_Server 

- _init_json.php: 	
	* _init_theme_json( $theme_json = [], $origin = 'theme'):TP_Theme_JSON 

- _init_list_util.php: 	
	* _init_list_util($input):TP_List_Util 

- _init_locale.php: 	
	* _init_locale():TP_Locale 
	* _init_locale_switcher():TP_Locale_Switcher 

- _init_mailer.php: 	
	* _init_mailer():PHPMailer 

- _init_meta.php: 	
	* _init_meta_data_lazy_loader():TP_Metadata_Lazyloader 

- _init_nav_menus.php: 	
	* todo 

- _init_post.php: 	
	* _init_post(...$post): TP_Post 
	* _init_post_ID() 
	* todo 

- _init_post_type.php: 	
	* _init_post_types($post_type= null,...$args):TP_Post_Type 

- _init_queries.php: 	
	* _init_date_query($query = ''): TP_Date_Query 
	* _init_meta_query($meta_query = false): TP_Meta_Query 
	* _init_query($query = ''): TP_Query 
	* _init_network_query($query = ''):TP_Network_Query 
	* _init_site_query($query = ''):TP_Site_Query 

- _init_rest.php: 	
	* _init_rest_controller():TP_REST_Controller 
	* _init_rest_server():TP_REST_Server 
	* _init_rest_request($request):TP_REST_Request 

- _init_rewrite.php: 	
	* _init_rewrite():TP_Rewrite 

- _init_simplepie.php: 	
	* _init_simplepie():SimplePie 
	* _init_simplepieCache():SimplePie_Cache 

- _init_site.php: 	
	* _init_site($site=''):TP_Site 
	* _init_current_site() 

- _init_sitemap.php: 	
	* _init_sitemap():TP_Sitemaps 

- _init_taxonomy.php: 	
	* _init_taxonomy($taxonomy = '',$object_type =''): TP_Taxonomy 
	* todo 

- _init_theme.php: 	
	* _init_theme($theme_dir = '',$theme_root = ''):TP_Theme 

- _init_translate.php: 	
	* _init_translate():TP_Translations 
	* _init_noop_translations():NOOP_Translations 
	* _init_mo():MO 

- _init_user.php: 	
	* _init_user($id = 0, $name = '', $site_id = '' ):TP_User 
	* _init_roles($site_id = null): TP_Roles 

- _init_xmlrpc_server.php: 	
	* _init_xmlrpc_server():TP_XMLRPC_Server 
