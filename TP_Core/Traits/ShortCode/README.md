### TP_Core/Traits/ShortCode

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _short_code_01.php: 	
	* _add_shortcode( $tag, $callback ):void
	* _remove_shortcode( $tag ):void 
	* _remove_all_shortcodes():void 
	* _shortcode_exists( $tag ):bool 
	* _has_shortcode( $content, $tag ):bool 
	* _apply_shortcodes( $content, $ignore_html = false ) -> mixed 
	* _do_shortcode( $content, $ignore_html = false ) 
	* _get_shortcode_regex( $tagnames = null ):string 
	* _do_shortcode_tag( $m ):string 
	* _do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames ):string 

- _short_code_02.php: 	
	* _un_escape_invalid_shortcodes( $content ):string 
	* _get_shortcode_atts_regex():string 
	* _shortcode_parse_atts( $text ):array 
	* _shortcode_atts( $pairs, $atts, $shortcode = '' ):array 
	* _strip_shortcodes( $content ) -> mixed  
	* _strip_shortcode_tag( $m ):string 
