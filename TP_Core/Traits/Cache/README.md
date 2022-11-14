### TP_Core/Traits/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _cache_01.php: 	
	* _tp_cache_init():void  
	* _tp_cache_add( $key, $data, $group = '', $expire = 0 ): bool 
	* _tp_cache_add_multiple( array $data, $group = '', $expire = 0 ): array 
	* _tp_cache_replace( $key, $data, $group = '', $expire = 0 ): bool 
	* _tp_cache_set( $key, $data, $group = '', $expire = 0 ): bool 
	* _tp_cache_set_multiple( array $data, $group = '', $expire = 0 ): array 
	* _tp_cache_get( $key, $group = '', $force = false, &$found = null ) -> mixed
	* _tp_cache_get_multiple( $keys, $group = '', $force = false ): array 
	* _tp_cache_delete( $key, $group = '' ): bool 
	* _tp_cache_delete_multiple( array $keys, $group = '' ): array 

- _cache_02.php: 	
	* _tp_cache_increase( $key, $offset = 1, $group = '' ): bool  
	* _tp_cache_decrease( $key, $offset = 1, $group = '' ): bool  
	* _tp_cache_flush(): bool  
	* _tp_cache_flush_runtime(): bool  
	* _tp_cache_close():bool  
	* _tp_cache_add_global_groups( $groups ): void  
	* _tp_cache_add_non_persistent_groups( $groups ): void  
	* _tp_cache_add_non_persistent_groups( $groups ): void  
