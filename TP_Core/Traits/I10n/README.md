### TP_Core/Traits/I10n

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _I10n_01.php: 	
	- __( $text, $domain = 'default' ) -> mixed 
	- _before_last_bar( $string ):string 
	- _determine_locale():string 
	- _e( $text, $domain = 'default' ):void 
	- _esc_attr__( $text, $domain = 'default' ) -> mixed 
	- _esc_attr_e( $text, $domain = 'default' ):void 
	- _esc_attr_x( $text, $context, $domain = 'default' ) -> mixed 
	- _esc_html__( $text, $domain = 'default' ) -> mixed 
	- _esc_html_e( $text, $domain = 'default' ):void 
	- _esc_html_x( $text, $context, $domain = 'default' ) -> mixed 

- _I10n_02.php: 	
	- _ex( $text, $context, $domain = 'default' ):void 
	- _get_available_languages( $dir = null ) -> mixed  
	- _get_locale() -> mixed  
	- _get_path_to_translation( $domain, $reset = false ) -> mixed  
	- _get_path_to_translation_from_lang_dir( $domain ) -> mixed  
	- _get_translations_for_domain($domain ):NOOP_Translations 
	- _get_user_locale( $user_id = 0 ) -> mixed  
	- get_user_locale( $user_id = 0 ) -> mixed 
	- _is_locale_switched():bool 
	- _is_rtl():bool 

- _I10n_03.php: 	
	- _is_textdomain_loaded( $domain ):bool 
	- _load_default_textdomain( $locale = null ):bool 
	- _load_script_textdomain( $handle, $domain = 'default', $path = null ) 
	- _load_script_translations( $file, $handle, $domain ) 
	- _load_textdomain( $domain, $mo_file ):bool 
	- _load_textdomain_just_in_time( $domain ):bool 
	- _load_theme_textdomain( $domain, $path = false ):bool 
	- _n( $single, $plural, $number, $domain = 'default' ) -> mixed 
	- _n_noop( $singular, $plural, $domain = null ):array 
	- _nx( $single, $plural, $number, $context, $domain = 'default' ) 

- _I10n_04.php: 	
	- _nx_noop( $singular, $plural, $context, $domain = null ):array 
	- _switch_to_locale( $locale ):bool 
	- _restore_current_locale() -> mixed 
	- _restore_previous_locale() -> mixed 
	- _tp_get_dropdown_languages( ...$args) -> mixed 
	- tp_dropdown_languages( ...$args):void 
	- _tp_get_installed_translations( $type ):array 
	- _tp_get_pomo_file_data( $po_file ) -> mixed 
	- _translate( $text, $domain = 'default' ) -> mixed 
	- _translate_nooped_plural( $nooped_plural, $count, $domain = 'default' ) -> mixed 
	- _translate_settings_using_i18n_schema( $i18n_schema, $settings, $textdomain ):array 

- _I10n_05.php: 	
	- _translate_user_role( $name, $domain = 'default' ) -> mixed  
	- _translate_with_get_text_context( $text, $context, $domain = 'default' ) -> mixed  
	- _unload_textdomain( $domain ):bool 
	- _x( $text, $context, $domain = 'default' ) -> mixed  
	- _tp_dropdown_languages(array ...$args ):void