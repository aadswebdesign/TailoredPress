### TP_Core/Traits/Formats

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _format_post_01.php: 	
	* _get_post_format( $post = null ) -> mixed 
	* _has_post_format( $format = [], $post = null ) 
	* _set_post_format( $post, $format ):TP_Error 
	* _get_post_format_strings():array 
	* _get_post_format_slugs():array 
	* _get_post_format_string( $slug ):?string 
	* _get_post_format_link( $format ):bool 
	* _post_format_request( $qvs ) -> mixed 
	* _post_format_link( $link, $term, $taxonomy ) -> mixed 
	* _post_format_get_term(TP_Term $term ):TP_Term 

- _format_post_02.php: 	
	* _post_format_get_terms( TP_Term $terms, $taxonomies, $args ):TP_Term 
	* _post_format_tp_get_object_terms( TP_Term $terms ):TP_Term 

- _formats_01.php: 	
	* _tp_texturize( $text, $reset = false ):string 
	* _tp_texturize_primes( $haystack, $needle, $prime, $open_quote, $close_quote ):string 
	* _tp_texturize_push_pop_element( $text, &$stack, $disabled_elements ):void 
	* _tp_autop( $pee, $br = true ) -> mixed 
	* _tp_html_split( $input ):array 
	* _get_html_split_regex():string 
	* _get_tp_texturize_split_regex( $shortcode_regex = '' ):string 
	* _get_tp_texturize_shortcode_regex( $tag_names ):string 
	* _tp_replace_in_html_tags( $haystack, $replace_pairs ): string 
	* _autop_newline_preservation_helper( $matches ) -> mixed 

- _formats_02.php: 	
	* _shortcode_un_autop( $pee )  -> mixed
	* _seems_utf8( $str ):bool 
	* _tp_special_chars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ): string 
	* _tp_special_chars_decode( $string, $quote_style = ENT_NOQUOTES ) -> mixed 
	* _tp_check_invalid_utf8( $string, $strip = false ):string 
	* _utf8_uri_encode( $utf8_string, $length = 0, $encode_ascii_characters = false ):string 
	* _remove_accents( $string ) -> mixed 
	* _sanitize_file_name( $filename ) -> mixed 
	* _sanitize_user( $username, $strict = false ) -> mixed 
	* _sanitize_key( $key ) -> mixed 

- _formats_03.php: 	
	* _sanitize_title( $title, $fallback_title = '', $context = 'save' ) -> mixed  
	* _sanitize_title_for_query( $title ) -> mixed  
	* _sanitize_title_with_dashes( $title, $context = 'display' ) -> mixed  
	* _sanitize_sql_orderby( $orderby ):bool 
	* _sanitize_html_class( $class, $fallback = '' ) -> mixed  
	* _convert_chars( $content, $deprecated = '' ) -> mixed  
	* _convert_invalid_entities( $content ):string 
	* _balanceTags( $text, $force = false ) -> mixed  
	* _force_balance_tags( $text ) -> mixed  
	* _format_to_edit( $content, $rich_text = false ) -> mixed  

- _formats_04.php: 	
	* _zero_ise( $number, $threshold ):string 
	* _backslashit( $string ):string 
	* _trailingslashit( $string ):string 
	* _untrailingslashit( $string ):string 
	* _add_slashes_gpc( $gpc ) -> mixed 
	* _strip_slashes_deep( $value ) -> mixed 
	* _strip_slashes_from_strings_only( $value ):string 
	* _url_encode_deep( $value ) -> mixed 
	* _raw_url_encode_deep( $value ) -> mixed 
	* _url_decode_deep( $value ) -> mixed 

- _formats_05.php: 	
	* _make_url_clickable_cb( $matches ):string 
	* _make_web_ftp_clickable_cb( $matches ):string 
	* _make_email_clickable_cb( $matches ):string 
	* _make_clickable( $text ) -> mixed 
	* _split_str_by_whitespace( $string, $goal ):array 
	* _tp_rel_callback( $matches, $rel ):string 
	* _tp_rel_nofollow( $text ) -> mixed 
	* _tp_rel_ugc( $text ) -> mixed 
	* _tp_targeted_link_rel( $text ):string 
	* _tp_targeted_link_rel_callback( $matches ):string 

- _formats_06.php: 	
	* _tp_init_targeted_link_rel_filters():void 
	* _tp_remove_targeted_link_rel_filters():void 
	* _translate_smiley( $matches ):string 
	* _convert_smilies( $text ):string 
	* _is_email( $email ) -> mixed  
	* _tp_iso_descrambler( $string ) -> mixed  
	* _tp_iso_convert( $match ):string 
	* _get_gmt_from_date( $string, $format = 'Y-m-d H:i:s' ) -> mixed  
	* _get_date_from_gmt( $string, $format = 'Y-m-d H:i:s' ) -> mixed  
	* _iso8601_timezone_to_offset( $timezone ):int 

- _formats_07.php: 	
	* _iso8601_to_datetime( $date_string, $timezone = 'user' ) -> mixed 
	* _sanitize_email( $email ) -> mixed 
	* _human_time_diff( $from, $to = 0 ) -> mixed 
	* _tp_trim_excerpt( $text = '', $post = null ) -> mixed 
	* _tp_trim_words( $text, $num_words = 55, $more = null ) -> mixed 
	* _ent2ncr( $text ) -> mixed 
	* _format_for_editor( $text, $default_editor = null ) -> mixed 
	* _deep_replace( $search, $subject ) -> mixed 
	* _esc_sql( $data ) -> mixed 
	* _esc_url( $url, $protocols = null, $_context = 'display' ) -> mixed 

- _formats_08.php: 	
	* _esc_url_raw( $url, $protocols = null ) -> mixed 
	* _sanitize_url( $url, $protocols = null ) -> mixed 
	* _html_entities2( $my_html ) -> mixed 
	* _esc_js( $text ) -> mixed 
	* _esc_html( $text ) -> mixed 
	* _esc_attr( $text ) -> mixed 
	* _esc_textarea( $text ) -> mixed 
	* _esc_xml( $text ) -> mixed 
	* _tag_escape( $tag_name ) -> mixed 
	* _tp_make_link_relative( $link ): string 

- _formats_09.php: 	
	* _sanitize_option( $option, mixed $value ) -> mixed 
	* _map_deep( $value, $callback ) -> mixed 
	* _tp_parse_str( $string, &$array ):void 
	* _tp_pre_kses_less_than( $text ) -> mixed 
	* _tp_pre_kses_less_than_callback( $matches ) -> mixed 
	* _tp_pre_kses_block_attributes( $string, $allowed_html, $allowed_protocols ) -> mixed 
	* _tp_sprintf( $pattern, ...$args ):string 
	* _tp_sprintf_list( $pattern, $args ):string 
	* _tp_html_excerpt( $str, $count, $more = null ) -> mixed 
	* _links_add_base_url( $content, $base, $attrs = ['src', 'href'] ) -> mixed 

- _formats_10.php: 	
	* _links_add_base( $m ):string 
	* _links_add_target_key( $content, $target = '_blank',$tags = ['a'] ) -> mixed  
	* _links_add_target_value( $m ):string 
	* _normalize_whitespace( $str ) -> mixed  
	* _tp_strip_all_tags( $string, $remove_breaks = false ):string 
	* _sanitize_text_field( $str ) -> mixed  
	* _sanitize_textarea_field( $str ) -> mixed  
	* _sanitize_text_fields( $str, $keep_newlines = false ) -> mixed  
	* _tp_basename( $path, $suffix = '' ):string 
	* _sanitize_mime_type( $mime_type ) -> mixed  

- _formats_11.php: 	
	* _sanitize_trackback_urls( $to_ping ) -> mixed  
	* _tp_slash( $value ) -> mixed  
	* _tp_unslash( $value ) -> mixed  
	* _get_url_in_content( $content ):bool 
	* _tp_spaces_regexp() -> mixed  
	* _url_shorten( $url, $length = 35 ):string 
	* _sanitize_hex_color( $color ) -> mixed  
	* _sanitize_hex_color_no_hash( $color ):string 
	* _maybe_hash_hex_color( $color ):string 
