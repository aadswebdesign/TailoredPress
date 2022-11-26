### TP_Core/Libs/RestApi

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- TP_REST_Request.php:  implements \ArrayAccess	
	* protected $_params, $_headers, $_body, $_route, $_attributes, $_parsed_json, $_parsed_body, $_method 
	* __construct( $method = '', $route = '', $attributes = array() ) 
	* get_method() 
	* set_method( $method ): void 
	* get_headers(): array 
	* canonicalize_header_name(string $key ) static  
	* get_header(string $key ) 
	* get_header_as_array(string $key ) 
	* set_header(string $key, array $value ): void 
	* add_header(string $key, array $value ): void 
	* remove_header(string $key ): void 
	* set_headers(array $headers, $override = true ): void 
	* get_content_type() 
	* is_json_content_type(): bool 
	* get_param(string $key ) 
	* has_param(string $key ): bool 
	* set_param(string $key, $value): void 
	* get_params(): array 
	* get_url_params() 
	* set_url_params( $params ): void 
	* get_query_params() 
	* set_query_params( $params ): void 
	* get_body_params() 
	* set_body_params( $params ): void 
	* get_file_params() 
	* set_file_params( $params ): void 
	* get_default_params() 
	* set_default_params( $params ): void 
	* get_body() 
	* set_body( $data ): void 
	* get_json_params() 
	* get_route() 
	* set_route( $route ): void 
	* get_attributes(): array 
	* set_attributes( $attributes ): void 
	* sanitize_params() 
	* has_valid_params() 
	* offsetExists( $offset ):bool 
	* offsetGet( $offset ) 
	* offsetSet( $offset, $value ):string 
	* offsetUnset( $offset ):string 
	* from_url( $url ) 
	* _get_parameter_order() 
	* _parse_json_params() 
	* _parse_body_params(): void 

- TP_REST_Response.php:  extends TP_HTTP_Response	
	* protected $_links, $_matched_route, $_matched_handler 
	* add_link(string $rel,string $href, array $attributes = [] ): void 
	* remove_link( string $rel, string $href = null ): void 
	* add_links(array $links ): void 
	* get_links(): array 
	* link_header( $rel, $link, $other = array() ): void 
	* get_matched_route(): string 
	* set_matched_route( $route ): void 
	* get_matched_handler() 
	* set_matched_handler( $handler ): void 
	* is_error(): bool 
	* as_error() 
	* get_curies() 

- TP_REST_Server.php: 	
	* protected $_namespaces, $_endpoints, $_route_options, $_embed_cache 
	* protected static $_HTTP_RAW_POST_DATA 
	* __construct() 
	* check_authentication() 
	* _error_to_response($error ): TP_REST_Response 
	* _json_error( $code, $message, $status = null ) 
	* serve_request( $path = null ) 
	* response_to_data(TP_REST_Response $response, $embed ) 
	* get_response_links(TP_REST_Response  $response ): array static 
	* get_compact_response_links(TP_REST_Response $response ): array static 
	* _embed_links( $data, $embed = true ) 
	* envelope_response(TP_REST_Response $response, $embed ): TP_REST_Response 
	* register_route( $namespace, $route, $route_args, $override = false ): void 
	* get_routes( $namespace = '' ) 
	* get_namespaces(): array 
	* get_route_options( $route ) 
	* dispatch(TP_REST_Request $request ) 
	* _match_request_to_handler(TP_REST_Request $request ) 
	* _respond_to_request( $request, $route, $handler, $response ) 
	* _get_json_last_error() 
	* get_index( $request ) 
	* _add_active_theme_link_to_index( TP_REST_Response $response ): void 
	* _add_site_logo_to_index( TP_REST_Response $response ): void 
	* _add_site_icon_to_index( TP_REST_Response $response ): void 
	* _add_image_to_index( TP_REST_Response $response, $image_id, $type ): void 
	* get_namespace_index( $request ) 
	* get_data_for_routes( $routes, $context = 'view' ) 
	* get_data_for_route( $route, $callbacks, $context = 'view' ): ?array 
	* _get_max_batch_size() 
	* serve_batch_request_v1( TP_REST_Request $batch_request ): TP_REST_Response 
	* _set_status( $code ): void 
	* send_header( $key, $value ): void 
	* send_headers( $headers ): void 
	* remove_header( $key ): void 
	* get_raw_data(): string static 
	* get_headers(array $server ): array 

- EndPoints/Endpoints_Base.php:
	* protected $_fake, $_namespace, $_rest_base, $_schema, $_tp_rest_additional_fields 
	* protected $_parent_post_type, $_parent_controller, $_parent_base, $_revisions_controller 
	* public $request,$item 

- EndPoints/TP_REST_Application_Passwords_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items( $request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item( $request ):string 
	* update_item_permissions_check( $request ):string 
	* update_item( $request ):string 
	* delete_items_permissions_check( $request ) 
	* delete_items( $request ) 
	* delete_item_permissions_check($request ):string 
	* delete_item( $request ):string 
	* get_current_item_permissions_check( $request ) 
	* get_current_item( $request ) 
	* _prepare_item_for_database( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( TP_User $user, $item ): array 
	* _get_user( $request ) 
	* _get_application_password( $request ) 
	* get_collection_params():array 

- EndPoints/TP_REST_Attachments_Controller.php: extends TP_REST_Controller 	
	* protected $_allow_batch 
	* register_routes():void 
	* _prepare_items_query( $prepared_args = array(), $request = null ):string 
	* create_item_permissions_check( $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* _insert_attachment(TP_REST_Request $request ) 
	* update_item(TP_REST_Request $request ):string 
	* post_process_item( $request ) 
	* post_process_item_permissions_check( $request ) 
	* edit_media_item_permissions_check( $request ) 
	* edit_media_item( $request ) 
	* _prepare_item_for_database( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* _upload_from_data( $data, $headers ) 
	* get_filename_from_disposition( $disposition_header ) 
	* get_collection_params():array 
	* _upload_from_file( $files, $headers ) 
	* _get_media_types(): array 
	* _check_upload_size( $file ) 
	* get_edit_media_item_args(): array 

- EndPoints/TP_REST_Auto_Saves_Controller.php: extends TP_REST_Controller 	
	* __construct($parent_post_type ) 
	* register_routes(): void 
	* _get_parent( $parent_id ) 
	* get_items_permissions_check( $request ):string 
	* create_item_permissions_check(TP_REST_Request $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* get_item(TP_REST_Request $request ):string 
	* get_items($request ):string 
	* get_item_schema() 
	* create_post_autosave( $post_data ) 
	* prepare_item_for_response( $item, $request ):string 
	* get_collection_params():array 

- EndPoints/TP_REST_Block_Directory_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( $what_ever ): void 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Block_Renderer_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_item(TP_REST_Request $request ):string 
	* get_item_schema() 

- EndPoints/TP_REST_Block_Types_Controller.php: extends TP_REST_Controller 	
	* protected $_block_registry, $_style_registry 
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* _check_read_permission() 
	* _get_block( $name ) 
	* get_item( $request ):string 
	* prepare_item_for_response(TP_Block_Type $item, $request ):string 
	* _prepare_links(TP_Block_Type $block_type ): array 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Blocks_Controller.php: extends TP_REST_Controller 	
	* check_read_permission( $post ):bool 
	* filter_response_by_context( $data, $context ) 
	* get_item_schema() 

- EndPoints/TP_REST_Comments_Controller.php: extends TP_REST_Controller 	
	* protected $meta 
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items(TP_REST_Request $request ):string 
	* _get_comment( $id ):TP_Error 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* update_item_permissions_check( $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item(TP_REST_Request $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links(TP_Comment $comment ): array 
	* _normalize_query_param( $query_param ): string 
	* _prepare_status_response( $comment_approved ) 
	* _prepare_item_for_database(TP_REST_Request $request ):string 
	* get_item_schema() 
	* get_collection_params():array 
	* _handle_status_param( $new_status, $comment_id ) 
	* _check_read_post_permission( $post, $request ) 
	* _check_read_permission( $comment, $request ) 
	* _check_edit_permission( $comment ) 
	* check_comment_author_email( $value, $request, $param ) 
	* _check_is_comment_content_allowed( $prepared_comment ): bool 

- EndPoints/TP_REST_Controller.php: extends Endpoints_Base 	
	* register_routes():void 
	* get_items_permissions_check($request):string 
	* get_items( $request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item( $request ):string 
	* update_item_permissions_check( $request ):string 
	* update_item( $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item( $request ):string 
	* _prepare_item_for_database( $request):string 
	* prepare_item_for_response( $item, $request ):string 
	* prepare_response_for_collection( $response ) 
	* filter_response_by_context( $data, $context ) 
	* get_item_schema() 
	* get_public_item_schema() 
	* get_collection_params():array 
	* get_context_param( ...$args) 
	* get_fields_for_response( $request ) 
	* get_endpoint_args_for_item_schema( $method = TP_POST ): array 
	* sanitize_slug( $slug ) 
	* _add_additional_fields_to_object( $prepared, $request ) 
	* _update_additional_fields_for_object( $object, $request ) 
	* _add_additional_fields_schema( $schema ) 
	* _get_additional_fields( $object_type = null ) 
	* _get_object_type() 

- EndPoints/TP_REST_Edit_Site_Export_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* permissions_check() 
	* export() 

- EndPoints/TP_REST_Global_Styles_Controller.php: extends TP_REST_Controller 	
	* protected $_post_type 
	* __construct() 
	* register_routes():void 
	* _sanitize_global_styles_callback( $id_or_stylesheet ) 
	* get_item_permissions_check( $request ):string 
	* _check_read_permission( $post ) 
	* get_item( $request ):string 
	* update_item_permissions_check( $request ):string 
	* _check_update_permission( $post ) 
	* update_item( $request ):string 
	* prepare_item_for_database( $request ): \stdClass 
	* prepare_item_for_response( $post, $request ):string 
	* _get_post( $id ) 
	* _prepare_links( $id ): array 
	* _get_available_actions(): array 
	* protected_title_format(): string 
	* get_collection_params():array 
	* get_item_schema() 
	* get_theme_item_permissions_check( $request ) 
	* get_theme_item( $request ) 

- EndPoints/TP_REST_Menu_Items_Controller.php: extends TP_REST_Controller 	
	* _get_nav_menu_item( $id ) 
	* get_items_permissions_check( $request ):string 
	* get_item_permissions_check( $request ):string 
	* _check_has_read_only_access( $request ): mixed 
	* create_item(TP_REST_Request $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* delete_item( $request ):string 
	* _prepare_item_for_database( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( $post ):string 
	* _get_schema_links():string 
	* get_item_schema() 
	* get_collection_params():array 
	* _prepare_items_query( $prepared_args = array(), $request = null ):string 
	* _get_menu_id( $menu_item_id ) 

- EndPoints/TP_REST_Menu_Locations_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 
	* _prepare_links( $location  ): array 

- EndPoints/TP_REST_Menus_Controller.php: extends TP_REST_Controller 	
	* get_items_permissions_check( $request ):string 
	* get_item_permissions_check( $request ):string 
	* _get_menu_term( $id ) 
	* _check_has_read_only_access( $request ) 
	* prepare_item_for_response( $term, $request ):string 
	* _prepare_links( $term ):string 
	* _prepare_item_for_database( $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* delete_item(TP_REST_Request $request ):string 
	* _get_menu_auto_add( $menu_id ) 
	* _handle_auto_add( $menu_id, $request ) 
	* _get_menu_locations( $menu_id ): array 
	* _handle_locations( $menu_id, $request ) 
	* get_item_schema() 

- EndPoints/TP_REST_Pattern_Directory_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Post_Statuses_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* get_item_permissions_check( $request ):string 
	* _check_read_permission( $status ): bool 
	* get_item( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Post_Types_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* get_item( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Posts_Controller.php: extends TP_REST_Controller 	
	* protected $_post_type, $_meta, $_password_check_passed, $_allow_batch 
	* __construct( $post_type ) 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* check_password_required( $required, $post ) 
	* get_items(TP_REST_Request $request ):string 
	* _get_rest_post( $id ) 
	* get_item_permissions_check( $request ):string 
	* can_access_password_content( $post, $request ) 
	* get_item( $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item( TP_REST_Request $request ):string 
	* update_item_permissions_check( $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item( $request ):string 
	* _prepare_items_query($request = null,...$prepared_args):string 
	* _prepare_date_response( $date_gmt, $date = null ) 
	* _prepare_item_for_database(TP_REST_Request $request ):string 
	* check_status( $status,TP_REST_Request $request, $param ) 
	* _handle_status_param( $post_status, $post_type ) 
	* _handle_featured_media( $featured_media, $post_id ) 
	* check_template( $template, $request ) 
	* handle_template( $template, $post_id, $validate = false ): void 
	* _handle_terms( $post_id, $request ) 
	* _check_assign_terms_permission( $request ): bool 
	* _check_is_post_type_allowed( $post_type ): bool 
	* check_read_permission( $post ):bool 
	* _check_update_permission( $post ) 
	* _check_create_permission( $post ) 
	* _check_delete_permission( $post ) 
	* prepare_item_for_response( $item,TP_REST_Request $request ):string 
	* protected_title_format(): string 
	* _prepare_links( $post ):string 
	* _get_available_actions( $post, $request ):string 
	* get_item_schema() 
	* _get_schema_links():string 
	* get_collection_params():array 
	* sanitize_post_statuses( $statuses,TP_REST_Request $request, $parameter ) 
	* __prepare_tax_query( array $args, TP_REST_Request $request ):string 
	* __prepare_taxonomy_limit_schema( array $query_params ): array 

- EndPoints/TP_REST_Revisions_Controller.php: extends TP_REST_Controller 	
	* __construct($parent_post_type ) 
	* register_routes():void 
	* _get_parent( $parent ) 
	* get_items_permissions_check( $request ):string 
	* _get_revision( $id ) 
	* get_items(TP_REST_Request $request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item( $request ):string 
	* _prepare_items_query( $prepared_args = array(), $request = null ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_date_response( $date_gmt, $date = null ) 
	* get_item_schema() 
	* get_collection_params():array 
	* _prepare_excerpt_response( $excerpt, $post ) 

- EndPoints/TP_REST_Search_Controller.php: extends TP_REST_Controller 	
	* public const PROP_ID, PROP_TITLE, PROP_URL, PROP_TYPE, PROP_SUBTYPE, TYPE_ANY 
	* protected $_search_handlers 
	* __construct( array $search_handlers ) 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items(TP_REST_Request $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 
	* sanitize_subtypes( $subtypes, $request, $parameter ) 
	* _get_search_handler(TP_REST_Request $request ) 

- EndPoints/TP_REST_Settings_Controller.php: extends TP_REST_Controller 	
	* __construct() 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_item( $request ):string 
	* _prepare_value( $value, $schema ) 
	* update_item(TP_REST_Request $request ):string 
	* _get_registered_options():string 
	* get_item_schema() 
	* sanitize_callback( $value, $request, $param ) 
	* _set_additional_properties_to_false( $schema ) 

- EndPoints/TP_REST_Sidebars_Controller.php: extends TP_REST_Controller	
	* //todo for later 
	*  

- EndPoints/TP_REST_Site_Health_Controller.php: extends TP_REST_Controller	
	* private $__site_health 
	* __construct( $site_health ) 
	* register_routes():void 
	* _validate_request_permission( $check ):string 
	* test_background_updates():string 
	* test_dot_org_communication() 
	* test_loopback_requests() 
	* test_https_status() 
	* test_authorization_header() 
	* get_directory_sizes() 
	* _load_admin_textdomain(): void 
	* get_item_schema() 

- EndPoints/TP_REST_Taxonomies_Controller.php:  extends TP_REST_Controller	
	* __construct()  
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_Templates_Controller.php:  extends TP_REST_Controller	
	* protected $_post_type 
	* __construct( $post_type ) 
	* register_routes():void 
	* _permissions_check( $request ) 
	* sanitize_template_id( $id ) 
	* get_items_permissions_check( $request ):string 
	* get_items($request ):string 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* update_item_permissions_check( $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item( $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item(TP_REST_Request $request ):string 
	* _prepare_item_for_database( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( $id ): array 
	* _get_available_actions(): array 
	* get_collection_params():array 
	* get_item_schema() 

- EndPoints/TP_REST_Terms_Controller.php:  extends TP_REST_Controller	
	* protected $_taxonomy, $_meta, $_sort_column, $_total_terms, $_allow_batch 
	* __construct( $taxonomy ) 
	* register_routes():void 
	* get_items_permissions_check( $request ):string 
	* get_items(TP_REST_Request $request ):string 
	* _get_term_term( $id ) 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* create_item_permissions_check( $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* update_item_permissions_check($request ):string 
	* update_item(TP_REST_Request $request ):string 
	* delete_item_permissions_check( $request ):string 
	* delete_item(TP_REST_Request $request ):string 
	* _prepare_item_for_database( $request ):string 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( $term ):string 
	* get_item_schema() 
	* get_collection_params():array 
	* check_is_taxonomy_allowed( $taxonomy ):string 

- EndPoints/TP_REST_Themes_Controller.php: 	
	* public const PATTERN 
	* __construct() 
	* register_routes():void 
	* sanitize_stylesheet_callback( $stylesheet ) 
	* get_items_permissions_check( $request ):string 
	* get_item_permissions_check( $request ):string 
	* _check_read_active_theme_permission() 
	* get_item( $request ):string 
	* get_items($request ):string 
	* prepare_item_for_response(TP_Theme $item, $request ):stringprepare_item_for_response(TP_Theme $item, $request ):string 
	* _prepare_links(TP_Theme $theme ): array 
	* _is_same_theme(TP_Theme $theme_a,TP_Theme $theme_b ): bool 
	* _prepare_theme_support( $support,array ...$args) 
	* get_item_schema() 
	* get_collection_params():array 

- EndPoints/TP_REST_URL_Details_Controller.php:  extends TP_REST_Controller	
	*  __construct()
	*  register_routes():void
	*  get_item_schema()
	*  parse_url_details( $request )
	*  permissions_check()
	*  __get_remote_url( $url )
	*  __get_title( $html )
	*  __get_icon( $html, $url )
	*  __get_description( $meta_elements )
	*  __get_image( $meta_elements, $url )
	*  __prepare_metadata_for_output( $metadata )
	*  __build_cache_key_for_url( $url ): string
	*  __get_cache( $key )
	*  __set_cache( $key, $data = '' )
	*  __get_document_head( $html )
	*  __get_meta_with_content_elements( $html )

- EndPoints/TP_REST_Users_Controller.php:  extends TP_REST_Controller	
	* protected $_meta 
	* __construct() 
	* register_routes():void 
	* check_reassign( $value ) //not used , $request, $param
	* get_items_permissions_check( $request ):string 
	* get_items(TP_REST_Request $request ):string 
	* _get_user( $id ) 
	* get_item_permissions_check( $request ):string 
	* get_item( $request ):string 
	* get_current_item( $request ) 
	* create_item_permissions_check( $request ):string 
	* create_item(TP_REST_Request $request ):string 
	* update_item_permissions_check(TP_REST_Request $request ):string 
	* update_item(TP_REST_Request $request ):string 
	* update_current_item_permissions_check( $request ) 
	* update_current_item( $request ) 
	* delete_item_permissions_check( $request ):string 
	* delete_item(TP_REST_Request $request ):string 
	* delete_current_item_permissions_check( $request ) 
	* delete_current_item( $request ) 
	* prepare_item_for_response( $item, $request ):string 
	* _prepare_links( $user ):string 
	* _prepare_item_for_database( $request ):string 
	* _check_role_update( $user_id, $roles ) 
	* check_username( $value ) //not used , $request, $param
	* check_user_password( $value ) //not used , $request, $param
	* get_item_schema() 
	* get_collection_params():array 

- Fields/TP_REST_Comment_Meta_Fields.php:  extends TP_REST_Meta_Fields	
	* _get_meta_type():string 
	* _get_meta_subtype():string 
	* _get_rest_field_type():string 

- Fields/TP_REST_Meta_Fields.php: 	
	* _get_meta_type(); abstract
	* _get_meta_subtype(); abstract
	* _get_rest_field_type(); abstract
	* get_value( $object_id, $request ): array 
	* _prepare_value_for_response( $value, $request, $args ) 
	* update_value( $meta, $object_id ) 
	* _delete_meta_value( $object_id, $meta_key, $name ) 
	* _update_multi_meta_value( $object_id, $meta_key, $name, $values ) 
	* _update_meta_value( $object_id, $meta_key, $name, $value ) 
	* _is_meta_value_same_as_stored_value( $meta_key, $subtype, $stored_value, $user_value ): bool 
	* _get_registered_fields(): array 
	* get_field_schema(): array 
	* prepare_value( $value, $args ): void static 
	* check_meta_is_array( $value) 
	* _get_empty_value_for_type( $type ) 

- Fields/TP_REST_Post_Meta_Fields.php:  extends TP_REST_Meta_Fields	
	* protected $_post_type 
	* __construct( $post_type ) 
	* _get_meta_type(): string 
	* _get_meta_subtype() 
	* _get_rest_field_type() 

- Fields/TP_REST_Term_Meta_Fields.php: extends TP_REST_Meta_Fields	
	* protected $_taxonomy 
	* __construct( $taxonomy ) 
	* _get_meta_type(): string 
	* _get_meta_subtype() 
	* _get_rest_field_type():string 

- Fields/TP_REST_User_Meta_Fields.php: extends TP_REST_Meta_Fields	
	* _get_meta_type(): string 
	* _get_meta_subtype(): string 
	* _get_rest_field_type(): string 

- Search/TP_REST_Post_Format_Search_Handler.php: extends TP_REST_Search_Handler	
	* __construct() 
	* search_items( TP_REST_Request $request ):array 
	* prepare_item( $id, array $fields ):array 
	* prepare_item_links( $id ):array  

- Search/TP_REST_Post_Search_Handler.php: extends TP_REST_Search_Handler 	
	* __construct() 
	* search_items( TP_REST_Request $request ):array 
	* prepare_item( $id, array $fields ):array 
	* prepare_item_links( $id ):array 
	* protected_title_format():string 

- Search/TP_REST_Search_Handler.php: 	
	* public const RESULT_IDS, RESULT_TOTAL 
	* protected $_type, $_subtypes 
	* get_type():string 
	* get_subtypes():array 
	* search_items( TP_REST_Request $request ); abstract
	* prepare_item( $id, array $fields ); abstract
	* prepare_item_links( $id ); abstract

- Search/TP_REST_Term_Search_Handler.php: extends TP_REST_Search_Handler	
	* __construct()
	* search_items( TP_REST_Request $request ):array
	* prepare_item( $id, array $fields ):array 
	* prepare_item_links( $id ):array 