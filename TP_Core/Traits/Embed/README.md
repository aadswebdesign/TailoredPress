### TP_Core/Traits/Embed

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _embed_01.php: 	
	- _tp_embed_register_handler( $id, $regex, $callback, $priority = 10 ):void 
	- _tp_embed_unregister_handler( $id, $priority = 10 ):void 
	- _tp_embed_defaults( $url = '' ) -> mixed
	- _tp_obj_embed_get( $url, $args = '' ) -> mixed 
	- _tp_obj_embed_get_object(): ?TP_ObjEmbed 
	- _tp_obj_embed_add_provider( $format, $provider, $regex = false ):void 
	- _tp_obj_embed_remove_provider( $format ):bool 
	- _tp_maybe_load_embeds():void 
	- _tp_embed_handler_youtube( $matches, $attr, $url, $rawattr ) -> mixed  
	- _tp_embed_handler_audio( $attr, $url, $rawattr ) -> mixed  
- _embed_02.php: 	
	- _tp_embed_handler_video( $attr, $url, $rawattr ) -> mixed  
	- _tp_obj_embed_register_route():void 
	- _tp_obj_embed_get_discovery_links() -> mixed  
	- _tp_maybe_enqueue_oembed_host_js( $html ) -> mixed  
	- _get_post_embed_url( $post = null ):bool 
	- _get_obj_embed_endpoint_url( $permalink = '', $format = 'json' ) -> mixed  
	- _get_post_embed_html( $width, $height, $post = null ):bool 
	- _get_obj_embed_response_data( $post, $width ):bool 
	- _get_obj_embed_response_data_for_url( $url, $args ) ->  bool|object 
	- _get_obj_embed_response_data_rich( $data, $post, $width, $height ) -> mixed 
- _embed_03.php: 	
	- _tp_obj_embed_ensure_format( $format ):string 
	- _obj_embed_rest_pre_serve_request( $served, $result,TP_REST_Request $request, TP_REST_Server $server ):bool 
	- _obj_embed_create_xml( $data, $node = null ) -> mixed   
	- _tp_filter_obj_embed_iframe_title_attribute( $result, $data, $url ) -> mixed   
	- _tp_filter_obj_embed_result( $result, $data, $url ) -> mixed   
	- _tp_embed_excerpt_more( $more_string ):string 
	- the_excerpt_embed():void 
	- _tp_embed_excerpt_attachment( $content ) -> mixed   
	- _print_get_embed_styles():string 
	- print_embed_styles():void 
- _embed_04.php: 	
	- print_embed_scripts():void 
	- _obj_embed_filter_feed_content( $content ) -> mixed    
	- _get_embed_comments_button() -> bool|string
	- print_embed_comments_button():void 
	- _get_embed_sharing_button() -> bool|string 
	- print_embed_sharing_button():void 
	- _get_embed_sharing_dialog():string 
	- print_embed_sharing_dialog():void 
	- _get_the_embed_site_title() -> bool|string 
	- the_embed_site_title():void
	- _tp_filter_pre_obj_embed_result( $result, $url, $args ) -> mixed   
