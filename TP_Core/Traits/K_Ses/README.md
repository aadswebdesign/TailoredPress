### TP_Core/Traits/K_Ses

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _k_ses_01.php: 	
	- _tp_kses( $string, $allowed_html, $allowed_protocols = [] ) -> mixed 
	- _tp_kses_one_attr( $string, $element ):string 
	- _tp_kses_allowed_html(mixed ...$context):?array 
	- _tp_kses_hook( $string, $allowed_html, $allowed_protocols ) -> mixed  
	- _tp_kses_version():string 
	- _tp_kses_split( $string, $allowed_html, $allowed_protocols ) -> mixed  
	- _tp_kses_uri_attributes():string 
	- _tp_kses_split_callback( $match ) -> mixed  
- _k_ses_02.php: 	
	- _tp_kses_split2( $string, $allowed_html, $allowed_protocols ):string  
	- _tp_kses_attr( $element, $attr, $allowed_html, $allowed_protocols ):string 
	- _tp_kses_attr_check( &$name, &$value, &$whole, $vless, $element, $allowed_html ):bool 
	- _tp_kses_hair( $attr, $allowed_protocols ):array 
	- _tp_kses_attr_parse($element) -> mixed 
	- _tp_kses_hair_parse($attr) -> mixed 
	- _tp_kses_check_attr_val( $value, $vless, $check_name, $check_value ):bool 
	- _tp_kses_bad_protocol( $string, $allowed_protocols ) -> mixed 
	- _tp_kses_no_null( $string, $options = null ) -> mixed 
	- _tp_kses_strip_slashes( $string ) -> mixed 
- _k_ses_03.php: 	
	- _tp_kses_array_lc( $in_array ):array 
	- _tp_kses_html_error( $string ) -> mixed 
	- _tp_kses_bad_protocol_once( $string, $allowed_protocols, $count = 1 ) -> mixed 
	- _tp_kses_bad_protocol_once2( $string, $allowed_protocols ):?string 
	- _tp_kses_normalize_entities( $string, $context = 'html' ) -> mixed 
	- _tp_kses_named_entities( $matches ):string 
	- _tp_kses_xml_named_entities( $matches ):string 
	- _tp_kses_normalize_entities2( $matches ):string 
	- _tp_kses_normalize_entities3( $matches ):string 
	- _valid_unicode( $i ):bool 
- _k_ses_04.php: 	
	- _tp_kses_decode_entities( $string ):string 
	- _tp_kses_decode_entities_chr( $match ):string 
	- _tp_kses_decode_entities_chr_hex_dec( $match ):string 
	- _tp_filter_kses( $data ):string 
	- _tp_kses_data( $data ) -> mixed  
	- _tp_filter_post_kses( $data ):string 
	- _tp_filter_global_styles_post($data)  -> mixed 
	- _tp_kses_post($data) -> mixed  
	- _tp_kses_post_deep($data) -> mixed  
	- _tp_filter_no_html_kses( $data ):string 
- _k_ses_05.php: 	
	- _kses_init_filters():void 
	- _kses_remove_filters():void 
	- _kses_init():void 
	- _safe_css_filter_attr( $css )  -> mixed  
	- _tp_add_global_attributes( $value ):array 
	- _tp_kses_allow_pdf_objects( $url ):bool 
