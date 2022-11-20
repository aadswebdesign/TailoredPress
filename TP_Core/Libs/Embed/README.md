### TP_Core/Libs/Embed

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- Embed_Base.php: 	extends Embed_Base
	* public $handlers, $post_ID, $use_cache, $link_if_unknown
	* public $last_attr, $last_url, $return_false_on_fail, $providers  
	* public static $early_providers

- TP_Embed.php: extends Embed_Base	
	* __construct() {} 
	* run_shortcode( $content ) 
	* maybe_run_ajax_cache(): void 
	* register_handler( $id, $regex, $callback, $priority = 10 ): void 
	* unregister_handler( $id, $priority = 10 ): void 
	* shortcode( $attr, $url = '' )	
	* delete_obj_embed_caches( $post_ID ): void 
	* cache_obj_embed( $post_ID ): void 
	* auto_embed( $content ) 
	* auto_embed_callback( $matches ): string 
	* maybe_make_link( $url ) 
	* find_obj_embed_post_id( $cache_key ) 

- TP_ObjEmbed.php: extends Embed_Base	
	* __construct() 
	* get_provider( $url,...$args) 
	* _add_provider_early( $format, $provider, $regex = false ): void 
	* _remove_provider_early( $format ): void 
	* get_data( $url, ...$args) 
	* get_html( $url, $args = '' ) 
	* discover( $url ) 
	* fetch( $provider, $url, ...$args) 
	* __fetch_with_format( $provider_url_with_args, $format ) 
	* __parse_json( $response_body ) 
	* __parse_xml( $response_body ) 
	* _parse_xml( $response_body ) 
	* __parse_xml_body( $response_body ) 
	* data2html( $data, $url )  
	* strip_newlines( $html ) 

- TP_ObjEmbed_Controller.php: extends Embed_Base		
	* register_routes():void 
	* get_item( $request ) 
	* get_proxy_item_permissions_check() 
	* get_proxy_item(TP_REST_Request $request ) 
