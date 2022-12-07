### TP_Core/Traits/Multisite

**Note:** For what it is now and subject to change. 

**Files/Methods:** 
- _ms_load.php: 	
	- _is_subdomain_install():bool 
	- //protected function _tp_get_active_network_plugins(){return '';}//37 
	- _ms_site_check() -> bool|string 
	- _get_network_by_path( $domain, $path, $segments = null ) 
	- _get_site_by_path( $domain, $path, $segments = null ) 
	- _ms_load_current_site_and_network( $domain, $path, $subdomain = false ) -> bool|string
	- _ms_not_installed( $domain, $path ):void todo
- _ms_network.php: 	
	- _get_network( $network = null ) -> bool|null|TP_Network 
	- _get_networks(array ...$args) -> array|int 
	- _clean_network_cache( $ids ):void 
	- _update_network_cache( $networks ):void 
	- _prime_network_caches( $network_ids ):void 
