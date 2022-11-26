### TP_Core/Libs

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- Core_Base.php: 	
	* protected $_private_query_vars 
	* public $did_permalink, $extra_query_vars, $matched_query, $matched_rule, $public_query_vars, $query_string, $query_vars, $request 

- Rewrite_Base.php: 	
	* public $author_base, $author_structure, $comment_feed_structure, $comments_base, $comments_pagination_base, $date_structure, $endpoints 
	* public $extra_permanent_structures, $extra_rules, $extra_rules_top, $feed_base, $feed_structure, $feeds, $front, $index, $matches 
	* public $non_tp_rules, $page_structure, $pagination_base, $permalink_structure, $query_replace, $search_base, $search_structure 
	* public $rewrite_code, $rewrite_replace, $root, $rules, $use_trailing_slashes, $use_verbose_page_rules, $use_verbose_rules		

- TP_Admin_Bar.php: 	
	* private $__nodes, $__bound 
	* public $user 
	* initialize():void //todo might make it a return method
	* add_menu( $node ):void 
	* remove_menu( $id ):void 
	* add_node( $args ):void 
	* _set_node( $args ):void final
	* get_node( $id ) final 
	* _get_node( $id ) final 
	* get_nodes() final 
	* _get_nodes() final 
	* add_group( $args ):void final
	* remove_node( $id ):void 
	* _unset_node( $id ):void final  
	* render():void 
	* get_render(){}//305 todo instead of render()
	* set_render(){}//305 todo instead of render() 
	* _bind() final 
	* _get_render( $root ):string final 
	* _get_render_container( $node ):string final 
	* _get_render_group( $node ):string final 
	* _get_render_item( $node ):string final  
	* add_menus():void final 

- TP_Application_Passwords.php: 	
	* public const USERMETA_KEY_APPLICATION_PASSWORDS, OPTION_KEY_IN_USE, PW_LENGTH 
	* is_in_use(): bool static 
	* create_new_application_password( $user_id,array ...$args) static 
	* get_user_application_passwords( $user_id ) static 
	* get_user_application_password( $user_id, $uuid ) static 
	* application_name_exists_for_user( $user_id, $name ): bool static 
	* update_application_password( $user_id, $uuid, array ...$update) static 
	* record_application_password_usage( $user_id, $uuid ) static 
	* delete_application_password( $user_id, $uuid ) static 
	* delete_all_application_passwords( $user_id ) static 
	* _set_user_application_passwords( $user_id, $passwords ) static 
	* chunk_password( $raw_password ): string static 

- TP_Comment.php: 	
	* public static $tpdb 
	* public $comment_ID, $comment_post_ID, $comment_author, $, $comment_author_url, $comment_author_IP 
	* public $comment_date, $comment_date_gmt, $comment_content, $comment_karma, $comment_approved 
	* public $comment_agent, $comment_type, $comment_parent, $user_id 
	* protected $_children, $_populated_children, $_post_fields 
	* get_instance( $id ) static 
	* __construct( $comment ) 
	* to_array(): array 
	* get_children( $args = []) 
	* add_child( TP_Comment $child ): void 
	* get_child( $child_id ): bool 
	* populated_children( $set ): void 
	* __isset( $name ) 
	* __get( $name ) 

- TP_Core.php: 	
	* add_query_var($qv): void 
	* remove_query_var($name): void 
	* set_query_var($key, $value): void 
	* parse_request(array ...$extra_query_vars): bool 
	* send_headers(): void 
	* build_query_string(): void 
	* init(): void 
	* query_posts(): void 
	* handle_404(): void 
	* main(array ...$query_args): void 

- TP_Error.php: 	
	* public $errors, $error_data 
	* protected $_additional_data 
	* __construct( $code = '', $message = '', $data = '' ) 
	* get_error_codes(): array 
	* get_error_code() 
	* get_error_messages( $code = '' ): ?array 
	* get_error_message( $code = '' ): string 
	* get_error_data( $code = '' ) 
	* has_errors(): bool 
	* add( $code, $message, $data = '' ): void 
	* add_data( $data, $code = '' ): void 
	* get_all_error_data( $code = '' ): array 
	* remove( $code ): void 
	* merge_from( TP_Error $error ): void 
	* export_to( TP_Error $error ): void 
	* copy_errors( TP_Error $from, TP_Error $to ): void static

- TP_Fatal_Error_Handler.php: 	
	* handle(): void 
	* _detect_error() 
	* _should_handle_error( $error ): bool 
	* _display_error_template( $error, $handled ): void 
	* _display_default_error_template( $error, $handled ): void //todo 

- TP_Feed_Cache_Transient.php: 	
	* $name, $mod_name, $lifetime 
	* __construct( $location, $filename, $extension ) 
	* save( $data ): bool 
	* load(): string 
	* mtime(): string 
	* touch(): string 
	* unlink(): string 

- TP_Hook.php: 	
	* private $__iterations, $__current_priority, $__nesting_level, $__doing_action 
	* public $callbacks 
	* _resort_active_iterations( $new_priority = false, $priority_existed = false ) 
	* remove_filter( $hook_name, $callback, $priority ) 
	* has_filter( $hook_name = '', $callback = false ) 
	* has_filters() 
	* remove_all_filters( $priority = false ) 
	* apply_filters( $value, ...$args ) 
	* do_action( ...$args) 
	* get_action(...$args) 
	* do_all_hook( &$args ) 
	* current_priority() 
	* build_pre_initialized_hooks( $filters ) 
	* offsetExists( $offset ): bool 
	* offsetGet( $offset ) 
	* offsetSet( $offset, $value ): void 
	* offsetUnset( $offset ): void 
	* current() 
	* next() 
	* key() 
	* valid(): bool 
	* rewind(): void 

- TP_List_Util.php: 	
	* private $__input, $__output, $__orderby 
	* __construct( $input ) 
	* get_input(): array 
	* get_output(): array 
	* filter( $args = array(), $operator = 'AND' ): array 
	* pluck( $field, $index_key = null ): array 
	* sort( $orderby = array(), $order = 'ASC', $preserve_keys = false ): array 
	* __sort_callback( $a, $b ) 

- TP_Locale.php: 	
	* public $meridiem, $month, $month_abbrev, $month_genitive, $number_format, $text_direction 
	* public $weekday, $weekday_abbrev, $weekday_initial, $year 
	* __construct() 
	* init():void 
	* get_weekday( $weekday_number ) 
	* get_weekday_initial( $weekday_name ) 
	* get_weekday_abbrev( $weekday_name ) 
	* get_month( $month_number ) 
	* get_month_abbrev( $month_name ) 
	* get_meridiem( $meridiem ) 
	* is_rtl():bool 
	* _strings_for_pot():void 

- TP_Locale_Switcher.php: 	
	* private $__locales, $__original_locale, $__available_languages 
	* __construct() 
	* init():void 
	* switch_to_locale( $locale ):bool 
	* restore_previous_locale() 
	* restore_current_locale() 
	* is_switched():bool 
	* filter_locale( $locale ) 
	* __load_translations( $locale ):void 
	* __change_locale( $locale ):void 

- TP_MatchesMapRegex.php: 	
	* private $__matches, $__subject 
	* public $output, $pattern 
	* __construct( $subject, $matches ) 
	* apply( $subject, $matches ) static 
	* __map() 
	* callback( $matches ): string 

- TP_Metadata_Lazyloader.php: 	
	* protected $_pending_objects, $_settings 
	* __construct() 
	* queue_objects( $object_type, $object_ids ) 
	* reset_queue( $object_type ) 
	* lazyload_term_meta( $check ) 
	* lazyload_comment_meta( $check ) 

- TP_Network.php: 	
	* private $__id, $__blog_id 
	* public $domain, $path, $network_id, $cookie_domain, $site_name 
	* get_instance( $network_id ) static 
	* __construct( $network ) 
	* __get( $key ) 
	* __isset( $key ) 
	* __set( $key, $value ) 
	* __get_main_site_id(): int 
	* __set_site_name(): void 
	* __set_cookie_domain(): void 
	* get_by_path( $domain = '', $path = '', $segments = null ) static 

- TP_Object_Cache.php: 	
	* private $__blog_prefix, $__cache, $__multisite, $__expire, $__force 
	* public $global_groups, $cache_hits, $cache_misses 
	* __construct() 
	* add( $key, $data, $group = 'default', $expire = 0 ): bool 
	* add_multiple( array $data, $group = '', $expire = 0 ): array 
	* replace( $key, $data, $group = 'default', $expire = 0 ): bool 
	* set( $key, $data, $group = 'default', $expire = 0 ): bool 
	* set_multiple( array $data, $group = '', $expire = 0 ): array 
	* get( $key, $group = 'default', &$found = null, $force = false ) 
	* get_multiple( $keys, $group = 'default', $force = false ): array 
	* delete_multiple( array $keys, $group = '' ): arraydelete( $key, $group = 'default' ): bool 
	* increase( $key, $offset = 1, $group = 'default' ): bool 
	* decrease( $key, $offset = 1, $group = 'default' ): bool 
	* flush(): bool 
	* add_global_groups( $groups ): void 
	* switch_to_blog( $blog_id ): void
	* stats(): void //todo make return  
	* _exists( $key, $group ): bool 

- TP_PasswordHash.php: 	
	* private $__itoa64, $__iteration_count_log2, $__portable_hashes, $__random_state 
	* __construct($iteration_count_log2, $portable_hashes) 
	* PasswordHash($iteration_count_log2, $portable_hashes): void 
	* __get_random_bytes($count) 
	* __encode64($input, $count): string 
	* __generate_salt_private($input): string 
	* __crypt_private($password, $setting): string 
	* __generate_salt_blowfish($input): string 
	* HashPassword($password): string 
	* CheckPassword($password, $stored_hash): bool 

- TP_Paused_Extensions_Storage.php: 	
	* protected $_type 
	* __construct( $extension_type ) 
	* set( $extension, $error ) 
	* delete( $extension ) 
	* get( $extension ) 
	* get_all() 
	* delete_all() 
	* _get_option_name(): string 

- TP_Rewrite.php: 	
	* using_permalinks(): bool 
	* using_index_permalinks() 
	* using_mod_rewrite_permalinks(): bool 
	* preg_index( $number ): string 
	* page_uri_index(): array 
	* page_rewrite_rules(): array 
	* __get_date_permanent_structure() 
	* get_year_permanent_structure() 
	* get_month_permanent_structure() 
	* get_day_permanent_structure() 
	* get_category_permanent_structure() 
	* get_tag_permanent_structure() 
	* get_extra_permanent_structure( $name ): bool 
	* get_author_permanent_structure() 
	* get_search_permanent_structure() 
	* get_page_permanent_structure() 
	* get_feed_permanent_structure() 
	* get_comment_feed_permanent_structure() 
	* add_rewrite_tag( $tag, $regex, $query ): void 
	* remove_rewrite_tag( $tag ): void 
	* generate_rewrite_rules( $permalink_structure, $ep_mask = EP_NONE, $paged = true, $feed = true, $for_comments = false, $walk_dirs = true, $endpoints = true ): array 
	* generate_rewrite_rule( $permalink_structure, $walk_dirs = false ): array 
	* rewrite_rules() 
	* tp_rewrite_rules() 
	* mod_rewrite_rules(): string 
	* iis7_url_rewrite_rules( $add_parent_tags = false ) 
	* add_rule( $regex, $query, $after = 'bottom' ): void 
	* add_external_rule( $regex, $query ): void 
	* add_endpoint( $name, $places, $query_var = true ): void 
	* add_permanent_structure( $name, $structure, $args = array()): void 
	* remove_permanent_structures( $name ): void 
	* flush_rules( $hard = true ): void 
	* init(): void 
	* set_permalink_structure( $permalink_structure ): void 
	* set_category_base( $category_base ): void 
	* set_tag_base( $tag_base ): void 
	* __construct() 

- TP_Session_Tokens.php: abstract	
	*  __construct( $user_id ) protected
	*  get_instance( $user_id ) final static
	* __hash_token( $token ) 
	* get( $token ) final 
	* verify( $token ): bool final 
	* create( $expiration ) final 
	* update( $token, $session ): void final 
	* destroy( $token ): void final 
	* _is_still_valid( $session ): bool final 
	* destroy_all(): void final 
	* destroy_all_for_all_users(): void final static
	* get_all(): array final 
	* _get_sessions(); abstract
	* _get_session( $verifier ); abstract
	* _update_session( $verifier, $session = null ); abstract
	* _destroy_other_sessions( $verifier ); abstract
	* _destroy_all_sessions(); abstract
	* drop_sessions(): void static 

- TP_Site.php: final	
	* public $blog_id, $domain, $path, $site_id, $registered, $last_updated 
	* public $public, $archived, $mature, $spam, $deleted, $lang_id 
	* get_instance( $site_id ) static 
	* __construct( $site ) 
	* to_array(): array 
	* __get( $key ){} 
	* __isset( $key ) 
	* __set( $key, $value ) 
	* __get_details() 

- TP_Taxonomy.php: 	
	* public $name, $label, $labels, $description, $public, $publicly_queryable, $hierarchical 
	* public $show_ui, $show_in_menu, $show_in_nav_menus, $show_tag_cloud, $show_in_quick_edit, $show_admin_column 
	* public $meta_box_cb, $meta_box_sanitize_cb, $object_type, $cap, $rewrite, $query_var, $update_count_callback 
	* public $show_in_rest, $rest_base, $rest_namespace, $rest_controller_class, $rest_controller 
	* public $default_term, $sort, $args, $_builtin 
	* __construct( $taxonomy, $object_type,array ...$args) 
	* set_props( $object_type, array ...$args ): void 
	* add_rewrite_rules(): void 
	* remove_rewrite_rules(): void 
	* add_hooks(): void 
	* remove_hooks(): void 
	* get_rest_controller(): string 

- TP_TemplateLoader.php: 	
	* protected $_themes, $_template 
	* __construct() 

- TP_Term.php: 	
	* public $count, $description, $filter, $name, $parent, $slug 
	* public $taxonomy, $term_group, $term_id, $term_taxonomy_id 
	* get_instance( $term_id, $taxonomy = null ) static 
	* __construct( $term ) 
	* filter( $filter ): void 
	* to_array():array 
	* __get( $key ) 

- .php: 	
	* __construct( $theme_dir, $theme_root ) 
	* __toString() 
	* __isset( $offset ) 
	* __get( $offset ) 
	* offsetGet( $offset ) 
	* errors() 
	* exists() 
	* parent() 
	* __cache_add( $key, $data ) 
	* __cache_get( $key ) 
	* get_theme( $header ) 
	* display( $header, $markup = true, $translate = true ) 
	* __sanitize_header( $header, $value ) 
	* __markup_header( $header, $value, $translate ) 
	* __translate_header( $header, $value ) 
	* get_stylesheet() 
	* get_template() 
	* get_stylesheet_directory() 
	* get_template_directory() 
	* get_stylesheet_directory_uri() 
	* get_template_directory_uri() 
	* get_theme_root() 
	* get_theme_root_uri() 
	* get_screenshot( $uri = 'uri' ) 
	* get_files( $type = null, $depth = 0, $search_parent = false ) 
	* get_post_templates() 
	* get_page_templates($post = null, $post_type = 'page' ) 
	* __scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) static 
	* load_textdomain() 
	* is_allowed( $check = 'both', $blog_id = null ) 
	* is_block_theme() 
	* get_file_path( $file = '' ) 
	* get_core_default_theme() static 
	* get_allowed( $blog_id = null ) static
	* get_allowed_on_network() static
	* get_allowed_on_site( $blog_id = null ) static
	* network_enable_theme( $stylesheets ) static
	* network_disable_theme( $stylesheets ) static
	* sort_by_name( &$themes ) static
	* _name_sort( $a, $b ) static
	* _name_sort_i18n( $a, $b ) static

- .php: 	
	* protected $_auth_failed 
	* private $__is_enabled 
	* public $methods, $blog_options, $error 
	* __construct() 
	* __set_is_enabled(): void 
	* serve_request(): void 
	* sayHello():string 
	* addTwoNumbers(int $args) 
	* login( $username, $password ) 
	* login_pass_ok( $username, $password ): bool 
	* escape( &$data ) 
	* ixr_error( $error, $message = false ): void 
	* get_custom_fields( $post_id ): array 
	* set_custom_fields( $post_id, $fields ): void 
	* get_term_custom_fields( $term_id ): array 
	* set_term_custom_fields( $term_id, $fields ): void 
	* initialise_blog_option_info(): void 
	* tp_getUsersBlogs(array ...$args): array 
	* _minimum_args( $args, $count ): bool 
	* _prepare_taxonomy( $taxonomy, $fields ) 
	* _prepare_term( $term ) 
	* _convert_date( $date ): IXR_Date 
	* _convert_date_gmt( $date_gmt, $date ) 
	* _prepare_post( $post, $fields ) 
	* _prepare_post_type( $post_type, $fields ) 
	* _prepare_media_item( $media_item, $thumbnail_size = 'thumbnail' ) 
	* _prepare_page( $page ) 
	* _prepare_comment( $comment ) 
	* _prepare_user( $user, $fields ) 
	* tp_newPost(array ...$args) 
	* __toggle_sticky($post_data, $update = false ) 
	* _insert_post( $user, $content_struct ) 
	* tp_editPost( array ...$args ) 
	* tp_deletePost( array ...$args ) 
	* tp_getPost( array ...$args ) 
	* tp_getPosts( array ...$args ): array 
	* tp_newTerm( array ...$args ) 
	* tp_editTerm( array ...$args ) 
	* tp_deleteTerm($args ) 
	* tp_getTerm($args ) 
	* tp_getTerms( array ...$args ) 
	* tp_getTaxonomy($args ) 
	* tp_getTaxonomies( array ...$args ): array 
	* tp_getUser( $args ) 
	* tp_getUsers( $args ) 
	* tp_getProfile( $args ) 
	* tp_editProfile( $args ) 
	* tp_getPage($args ) 
	* tp_getPages( array ...$args ) 
	* tp_newPage( $args ) 
	* tp_deletePage( $args ) 
	* tp_editPage( $args ) 
	* tp_getPageList( $args ) 
	* tp_getAuthors( $args ) 
	* tp_getTags( $args ) 
	* tp_newCategory( $args ) 
	* tp_deleteCategory( $args ) 
	* tp_suggestCategories( $args ) 
	* tp_getComment( $args ) 
	* tp_getComments( $args ) 
	* tp_deleteComment( $args ) 
	* tp_editComment( $args ) 
	* tp_newComment( $args ) 
	* tp_getCommentStatusList( $args ) 
	* tp_getCommentCount( $args ) 
	* tp_getPostStatusList( $args ) 
	* tp_getPageStatusList( $args ) 
	* tp_getPageTemplates( $args ) 
	* tp_getOptions( $args ) 
	* getListOptions( $options ): array 
	* tp_setOptions( $args ) 
	* tp_getMediaItem( $args ) 
	* tp_getMediaLibrary( $args ) 
	* tp_getPostFormats( $args ) 
	* tp_getPostType( $args ) 
	* tp_getPostTypes( $args ): array 
	* tp_getRevisions( $args ) 
	* tp_restoreRevision( $args ) 
	* blogger_getUsersBlogs( $args ) 
	* __multisite_getUsersBlogs( $args ) 
	* blogger_getUserInfo( $args ) 
	* blogger_getPost( $args ) 
	* blogger_getRecentPosts( $args ) 
	* blogger_newPost( $args ) 
	* blogger_editPost( $args ) 
	* blogger_deletePost( $args ) 
	* mw_newPost( $args ) 
	* add_enclosure_if_new( $post_ID, $enclosure ): void 
	* attach_uploads( $post_ID, $post_content ): void 
	* mw_editPost( $args ) 
	* mw_getPost( $args ) 
	* mw_getRecentPosts( $args ): array 
	* mw_getCategories( $args ): array  
	* mw_newMediaObject( $args ) 
	* mt_getRecentPostTitles( $args ) 
	* mt_getCategoryList( $args ) 
	* mt_getPostCategories( $args ) 
	* mt_setPostCategories( $args ) 
	* mt_supportedMethods() 
	* mt_supportedTextFilters() 
	* mt_getTrackbackPings( $post_ID ) 
	* mt_publishPost( ...$args ) 
	* pingback_ping( $args ) 
	* pingback_extensions_getPingbacks( $url ) 
	* _pingback_error( $code, $message ) 
