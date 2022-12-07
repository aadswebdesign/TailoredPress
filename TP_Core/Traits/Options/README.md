### TP_Core/Traits/Options

**Note:** For what it is now and subject to change. The most methods in here are dummy methods and will be replaced by their real methods when the time is right.

**Files/Methods:** 
- _option_01.php: 	
	- _get_option($option, $default = false)//dummi 
	- _tp_protect_special_option( $option )//dummi 
	- _form_option( $option )//dummi  
	- _tp_load_all_options( $force_cache = false )//dummi  
	- _tp_load_core_site_options( $network_id = null )//dummi 
	- _update_option( $option, $value, $autoload = 'yes')//dummi  
	- _add_option( $option, $value, $autoload = 'yes')//dummi  
	- _delete_option( $option )//dummi 
	- _delete_transient( $transient )//dummi 
	- _get_transient( $transient )//dummi  
- _option_02.php: 	
	- _set_transient( $transient, $value, $expiration = 0 )//dummi  
	- _delete_expired_transients( $force_db = false )//dummi 
	- _tp_get_user_settings():string 
	- _tp_user_settings():void 
	- _get_user_setting( $name, $default = false )//dummi 
	- _set_user_setting( $name, $value )//dummi 
	- _delete_user_setting( $names )//dummi 
	- _get_all_user_settings()//dummi  
	- _tp_set_all_user_settings( $user_settings )//dummi  
	- _get_site_option( $option, $default = false, $deprecated = true )//dummi  
- _option_03.php: 	
	- _add_site_option( $option, $value )//dummi 
	- _delete_site_option( $option )//dummi  
	- _update_site_option( $option, $value )//dummi  
	- _get_network_option( $network_id, $option, $default = false )//dummi  
	- _add_network_option( $network_id, $option, $value )//dummi 
	- _delete_network_option( $network_id, $option )//dummi  
	- _update_network_option( $network_id, $option, $value )//dummi 
	- _delete_site_transient( $transient )//dummi 
	- _get_site_transient( $transient )//dummi 
	- _set_site_transient( $transient, $value, $expiration = 0 )//dummi 
- _option_04.php: 	
	- _register_initial_settings() //dummi  
	- _register_setting( $option_group, $option_name,array ...$args) //dummi   
	- _get_registered_settings() //dummi  
	- _option_update_filter( $options ):string 
	- _add_allowed_options( $new_options, $options = '' ):string 
	- _remove_allowed_options( $del_options, $options = '' ):string 
